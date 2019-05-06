<?php

/*
 * Plugin Name: WP Simple Queries Shortcode and Widget
 * Plugin URI: https://github.com/nikolays93
 * Description:
 * Version: 0.1.3
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: queries
 * Domain Path: /languages/
 */

namespace NikolayS93\Queries;

use NikolayS93\WPAdminPage as Admin;

if ( !defined( 'ABSPATH' ) ) exit('You shall not pass');
if (version_compare(PHP_VERSION, '5.4') < 0) {
    throw new \Exception('Plugin requires PHP 5.4 or above');
}

if( !defined(__NAMESPACE__ . '\PLUGIN_DIR') ) define(__NAMESPACE__ . '\PLUGIN_DIR', __DIR__);
if( !defined(__NAMESPACE__ . '\PLUGIN_FILE') ) define(__NAMESPACE__ . '\PLUGIN_FILE', __FILE__);

require_once ABSPATH . "wp-admin/includes/plugin.php";
require_once PLUGIN_DIR . '/vendor/autoload.php';

/**
 * Uniq prefix
 */
if(!defined(__NAMESPACE__ . '\DOMAIN')) define(__NAMESPACE__ . '\DOMAIN', Plugin::get_plugin_data('TextDomain'));

/**
 * Register widgets
 */
// add_action('widgets_init', array(__NAMESPACE__ . '\Posts_Widget', 'register_himself'));
// add_action('widgets_init', array(__NAMESPACE__ . '\Terms_Widget', 'register_himself'));

/**
 * Register shortcodes
 */
add_shortcode( PostsPublic::__shortcodeName(), array(new PostsPublic(), 'shortcode') );
add_shortcode( TermsPublic::__shortcodeName(), array(new TermsPublic(), 'shortcode') );


/**
 * Set to enqueue TiniMCE plugins
 */
// add_action( 'admin_init', array( __NAMESPACE__ . '\Posts_MCE', 'init_mce_plugin' ), 20 );
// add_action( 'admin_head', array( __NAMESPACE__ . '\Posts_MCE', 'enqueue_mce_script' ));

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

/**
 * Do not.. Set admin menu page with base settings
 */
// add_action( 'plugins_loaded', __NAMESPACE__ . '\__init', 10 );
function __init() {

    /** @var Admin\Page */
    $Page = new Admin\Page( Plugin::get_option_name(), __('New Plugin name Title', DOMAIN), array(
        'parent'      => '', // woocommerce
        'menu' => __('Example', DOMAIN),
        // 'validate'    => array($this, 'validate_options'),
        'permissions' => 'manage_options',
        'columns'     => 2,
    ) );

    // $Page->set_assets( function() {} );

    $Page->set_content( function() {
        Plugin::get_admin_template('menu-page', false, $inc = true);
    } );

    $Page->add_section( new Admin\Section(
        'Section',
        __('Section'),
        function() {
            Plugin::get_admin_template('section', false, $inc = true);
        }
    ) );

    $metabox = new Admin\Metabox(
        'metabox',
        __('metabox', DOMAIN),
        function() {
            Plugin::get_admin_template('metabox', false, $inc = true);
        },
        $position = 'side',
        $priority = 'high'
    );

    $Page->add_metabox( $metabox );
}

// register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'activate' ) );
// register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'deactivate' ) );
