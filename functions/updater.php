<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Self-hosted theme updates, checked against GitHub Releases on our own repo.
 *
 * Uses WordPress' native "Update URI" mechanism (since WP 5.8) instead of a
 * full third-party updater framework: we only need to answer the
 * `update_themes_{hostname}` filter with the latest release, and core
 * handles the rest (the "Update available" notice, downloading the package
 * and installing it).
 */

namespace Grayzone;

if ( ! class_exists( __NAMESPACE__ . '\\Updater' ) ) {

	class Updater {

		/**
		 * The "owner/repo" this theme's updates are published to.
		 *
		 * @var string
		 */
		const REPO = 'NasastaXD/Blog';

		/**
		 * Hostname from the style.css "Update URI" header. Must match for the
		 * `update_themes_{hostname}` filter to be the right one to hook.
		 *
		 * @var string
		 */
		const HOSTNAME = 'github.com';

		/**
		 * Transient key used to cache the GitHub API response.
		 *
		 * @var string
		 */
		const CACHE_KEY = 'grayzone_github_release';

		/**
		 * How long to cache a successful API response for.
		 *
		 * @var int
		 */
		const CACHE_TTL = 6 * HOUR_IN_SECONDS;

		/**
		 * How long to cache a failed API response for, so a temporary GitHub
		 * outage doesn't block update checks for hours.
		 *
		 * @var int
		 */
		const CACHE_TTL_ERROR = 15 * MINUTE_IN_SECONDS;

		/**
		 * Hook into WordPress' update checks.
		 *
		 * @return void
		 */
		public function init() {
			add_filter( 'update_themes_' . self::HOSTNAME, [ $this, 'check_update' ], 10, 3 );
		}

		/**
		 * Answers the `update_themes_{hostname}` filter with the latest
		 * release for this theme, if any. WordPress core compares the
		 * returned 'new_version' against the theme's own Version header, so
		 * we don't need to do that comparison ourselves.
		 *
		 * @param array|false $update     Update data to send back, or false to defer to core.
		 * @param array       $theme_data Theme headers of the installed theme.
		 * @param string      $stylesheet Directory name of the installed theme.
		 *
		 * @return array|false
		 */
		public function check_update( $update, $theme_data, $stylesheet ) {
			// Only handle our own theme; ignore any other theme pointing at github.com.
			if ( 'grayzone' !== $stylesheet ) {
				return $update;
			}

			$release = $this->get_latest_release();

			if ( ! $release || empty( $release['tag_name'] ) ) {
				return $update;
			}

			$version = ltrim( $release['tag_name'], 'v' );

			return [
				'theme'       => $stylesheet,
				'new_version' => $version,
				'url'         => ! empty( $release['html_url'] ) ? $release['html_url'] : 'https://github.com/' . self::REPO . '/releases',
				'package'     => 'https://github.com/' . self::REPO . '/archive/refs/tags/' . $release['tag_name'] . '.zip',
			];
		}

		/**
		 * Fetch (and cache) the latest release from the GitHub API.
		 *
		 * @return array|false The decoded release data, or false on failure.
		 */
		protected function get_latest_release() {
			$cached = get_site_transient( self::CACHE_KEY );
			if ( false !== $cached ) {
				return $cached;
			}

			$response = wp_remote_get(
				'https://api.github.com/repos/' . self::REPO . '/releases/latest',
				[
					'timeout' => 10,
					'headers' => [
						'Accept'     => 'application/vnd.github+json',
						'User-Agent' => 'Grayzone-Theme-Updater',
					],
				]
			);

			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				set_site_transient( self::CACHE_KEY, false, self::CACHE_TTL_ERROR );
				return false;
			}

			$release = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $release['tag_name'] ) ) {
				set_site_transient( self::CACHE_KEY, false, self::CACHE_TTL_ERROR );
				return false;
			}

			set_site_transient( self::CACHE_KEY, $release, self::CACHE_TTL );

			return $release;
		}
	}

}
