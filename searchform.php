<form method="get" class="searchform themeform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div>
		<input type="text" class="search" name="s" placeholder="<?php esc_attr_e('To search type and hit enter','grayzone'); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
	</div>
</form>