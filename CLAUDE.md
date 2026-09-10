# Grayzone (theme personal)

Este repo ES el theme de WordPress (los archivos viven en la raíz, no en una subcarpeta). Es un fork personalizado del theme "Grayzone" de AlxMedia, traducido completamente al español y con varios bugs corregidos respecto al original.

## Convención de versión y releases

El theme se auto-actualiza contra este mismo repo usando el header `Update URI` en `style.css` (ver `functions/updater.php`). Para que eso funcione, **cada vez que se termine un cambio significativo en el theme hay que**:

1. Subir el número de `Version:` en `style.css` (semver informal: bugfix -> patch, feature -> minor).
2. Agregar una entrada en `== Changelog ==` de `readme.txt`.
3. Mergear a `main`.

El paso de crear el tag y el GitHub Release es **automático**: `.github/workflows/release.yml` corre en cada push a `main`, lee el `Version:` de `style.css`, y si todavía no existe un Release con ese tag (`vX.Y.Z`), lo crea con `gh release create` usando el `GITHUB_TOKEN` de la corrida. No hace falta (ni conviene) crear el tag a mano.

Nota: la sesión de Claude Code que trabaja en este repo normalmente solo tiene permiso de push sobre su propia rama de feature, no sobre `main` ni para crear tags/releases directamente vía git o la API de GitHub — por eso el release se resuelve en CI y no con un `git push --tags` manual.

El auto-actualizador arma la URL del paquete como `https://github.com/NasastaXD/Blog/archive/refs/tags/vX.Y.Z.zip`, así que el tag tiene que existir como Release (no alcanza con un simple git tag sin publicar, ya que se consulta `GET /repos/NasastaXD/Blog/releases/latest`).

Para que la actualización se instale bien, la carpeta del theme en `wp-content/themes/` debe llamarse exactamente `grayzone` (es lo que compara `functions/updater.php`).

## Alcance de la traducción

La traducción al español (`languages/es_ES.po` / `languages/grayzone-es_ES.mo`) cubre el textdomain propio `grayzone` (plantillas del theme, Personalizador, meta boxes). No se tradujeron las librerías vendored (`functions/kirki/`, `functions/class-tgm-plugin-activation.php`): son de terceros y ya traen su propio sistema de i18n. Para que el propio wp-admin (núcleo de WordPress) se vea en español hace falta configurar Ajustes > Generales > Idioma del sitio = Español, algo que no controla el theme.
