<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 *  * 
 * ⚠️ IMPORTANTE:
 * Este archivo contiene dos configuraciones:
 * 
 * 1. ⚙️ CONFIGURACIÓN PARA INSTALACIÓN LOCAL (activar por defecto)
 * 2. 🌐 CONFIGURACIÓN DE PRODUCCIÓN (comentada para que NO se ejecute)
 * 
 * Si vas a usar el sitio localmente:
 *   - Deja activa la sección "LOCAL"
 * 
 * Si vas a subirlo a tu servidor:
 *   - Comenta la sección "LOCAL"
 *   - Descomenta la sección "PRODUCCIÓN"
 */

// ===========================
// 🌐 CONFIGURACIÓN DE PRODUCCIÓN (NO USAR EN LOCAL)
// ===========================
/*
 * define( 'DB_NAME', 'nombre_bd_local' );
*define( 'DB_USER', 'usuario_local' );
*define( 'DB_PASSWORD', 'contraseña_local' );
*define( 'DB_HOST', 'localhost' );
* */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "nonstop_taniz_db" );

/** Database username */
define( 'DB_USER', "nonstop-taniz" );

/** Database password */
define( 'DB_PASSWORD', "4qE_I-aD67q61D(Nun" );

/** Database hostname */
define( 'DB_HOST', "localhost" );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '$=p5TL$NQ?uW:GTVE)YH?6|.O#9=0v.4AmJrY;Ci/yYnG@G!Ac]wZq5v$#^cnFpt' );
define( 'SECURE_AUTH_KEY',  '+7U7b@6M;fj:j=?^}{fjn?D8cv*`6q#c=Iv0^uKoI$2bj?,dl>lsMe._p55OG]il' );
define( 'LOGGED_IN_KEY',    'u;:!U{R?A2W$)v%<-~kLPBE0-=k+l3KE?7`S5F}?GT8uxWBg-wLsX>#,(.U(Yt?V' );
define( 'NONCE_KEY',        'rpsWoo!mb[V?h3}M`3UyR2)Gs3Y@`lSW`T$/Gx-,ZpB$0Jh*Pgk.H8~d^)QTL^uY' );
define( 'AUTH_SALT',        '*QB{Sb*f)Rh+z4on54AY0Qcknu0RQ?3ND1hD9#OA+AQobt5qp0?ltC,o.T(=nj:W' );
define( 'SECURE_AUTH_SALT', '?[vb9Lq$=L!kGQ)[][5&NN>cbP)V7L;|a9os^%^go9Q?|k~3y0b,^?VH7sY`+Y$z' );
define( 'LOGGED_IN_SALT',   ',S@Fv_QgE5 AStdghj9oIC{MDz#JIJB~Bcym^b}89?o9X|&[(CM*1lzK(PW2/D;W' );
define( 'NONCE_SALT',       'jpzUuzh1P6$8x{BaGgFv[7416TnHm<)&Ns@lLaBzCvg3w?U`c`fLRg-cL|TS(EfT' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

//Si no funciona
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true); // o false para evitar mostrar en pantalla
@ini_set('display_errors', 1);
//borrar este bloque de codigo

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
