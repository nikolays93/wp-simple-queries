<?php
/**
 * Plugin Name: Post's and Term's queries
 * Plugin URI: https://github.com/nikolays93
 * Description: Shortcode works with classic edittor only (But we try to be better ;).
 * Version: 0.4
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: queries
 * Domain Path: /languages/
 *
 * @package Newproject.WordPress.plugin
 */

namespace NikolayS93\Queries;

if ( ! defined( 'ABSPATH' ) ) {
	exit( 'You shall not pass' );
}

if ( ! defined( __NAMESPACE__ . '\DOMAIN' ) ) {
	define( __NAMESPACE__ . '\DOMAIN', 'queries' );
}

if ( ! defined( __NAMESPACE__ . '\PLUGIN_DIR' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once PLUGIN_DIR . 'include/utils.php';

if( ! include_plugin_file( 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ) ) {
	array_map(
		__NAMESPACE__ . '\include_plugin_file',
		array(
			'include/class/Creational/Singleton.php',
			'include/class/Creational/Shortcode.php',
			'include/class/Plugin.php',
			'include/class/ShortcodePost.php',
            'include/class/ShortcodeTerms.php',
		)
	);
}

/**
 * Returns the single instance of this plugin, creating one if needed.
 *
 * @return Plugin
 */
function plugin() {
	return Plugin::get_instance();
}

/**
 * Initialize this plugin once all other plugins have finished loading.
 */
add_action( 'plugins_loaded', __NAMESPACE__ . '\plugin', 10 );

/**
 * Register shortcodes
 */
add_shortcode( ShortcodePost::get_name(), array( new ShortcodePost(), 'build' ) );
// add_shortcode( TermsPublic::get_name(), array( new TermsPublic(), 'build' ) );

add_shortcode( 'posts_pagination', array( __NAMESPACE__ . '\ShortcodePost', 'pagination' ) );

/**
 * Set to enqueue TiniMCE plugins
 */
add_action( 'admin_init', array( __NAMESPACE__ . '\ShortcodePost', 'init_mce_plugin' ), 20 );
add_action( 'admin_head', array( __NAMESPACE__ . '\ShortcodePost', 'enqueue_mce_script' ) );
