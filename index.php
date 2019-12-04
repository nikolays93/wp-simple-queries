<?php
/**
 * Plugin Name: Post's and Term's queries (Shortcode and Widget)
 * Plugin URI: https://github.com/nikolays93
 * Description:
 * Version: 0.3
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

if ( ! defined( __NAMESPACE__ . '\PLUGIN_DIR' ) ) {
	define( __NAMESPACE__ . '\PLUGIN_DIR', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );
}

if ( ! function_exists( 'include_plugin_file' ) ) {
	/**
	 * Safe dynamic expression include.
	 *
	 * @param string $path relative path.
	 */
	function include_plugin_file( $path ) {
		if ( 0 !== strpos( $path, PLUGIN_DIR ) ) {
			$path = PLUGIN_DIR . $path;
		}
		if ( is_file( $path ) && is_readable( $path ) ) {
			return include $path; // phpcs:ignore
		}

		return false;
	}
}

require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! include_once PLUGIN_DIR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ) {
	array_map(
		__NAMESPACE__ . '\include_plugin_file',
		array(
			'include/class/Creational/Singleton.php',
			'include/class/Creational/Shortcode.php',
			'include/class/Plugin.php',
			'include/class/Utils.php',
			'include/class/Register.php',
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
add_action( 'plugins_loaded', __NAMESPACE__ . '\Plugin', 10 );
add_action(
	'plugins_loaded',
	function () {
		$register = new Register();
		$register->register_plugin_page();
	},
	20
);

/**
 * Register shortcodes
 */
add_shortcode( ShortcodePost::get_name(), array( new ShortcodePost(), 'execute' ) );
// add_shortcode( TermsPublic::get_name(), array( new TermsPublic(), 'execute' ) );

add_shortcode( 'posts_pagination', array( __NAMESPACE__ . '\ShortcodePost', 'pagination' ) );

/**
 * Register widgets
 */
// add_action('widgets_init', array(__NAMESPACE__ . '\Posts_Widget', 'register_himself'));
// add_action('widgets_init', array(__NAMESPACE__ . '\Terms_Widget', 'register_himself'));

/**
 * Set to enqueue TiniMCE plugins
 */
add_action( 'admin_init', array( __NAMESPACE__ . '\ShortcodePost', 'init_mce_plugin' ), 20 );
add_action( 'admin_head', array( __NAMESPACE__ . '\ShortcodePost', 'enqueue_mce_script' ) );

/**
 * Register widget's sltyles
 */
// add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\admin_enqueues' );
// add_action( 'customize_controls_enqueue_scripts', __NAMESPACE__ . '\admin_enqueues' );
// function admin_enqueues( $hook )
// {
//     global $pagenow;

//     wp_enqueue_style(
//         'widget-panels',
//         Utils::get_plugin_url('/admin/assets/widget-panels.css'),
//         array(),
//         '1.0.0',
//         'all'
//     );

//     if( 'customize.php' == $pagenow || 'widgets.php' == $pagenow || 'widgets.php' == $hook ) {
//         wp_enqueue_script( 'widget-panels', Utils::get_plugin_url('/admin/assets/widget-panels.js'), array( 'jquery' ), '', true );

//             #wp_enqueue_script( 'admin-scripts', self::get_plugin_url() . 'js/admin.js', array( 'widget-panels' ), '', true );
//     };
// }

// register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Register', 'activate' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Register', 'deactivate' ) );
// register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Register', 'uninstall' ) );
