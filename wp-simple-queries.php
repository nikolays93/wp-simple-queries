<?php

/*
 * Plugin Name: WP Simple Queries Shortcode and Widget
 * Plugin URI: https://github.com/nikolays93
 * Description: 
 * Version: 0.1.2
 * Author: NikolayS93
 * Author URI: https://vk.com/nikolays_93
 * Author EMAIL: NikolayS93@ya.ru
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: queries
 * Domain Path: /languages/
 */

namespace Nikolays93\Queries;

use NikolayS93\WPAdminPage as Admin;

if ( !defined( 'ABSPATH' ) ) exit('You shall not pass');

require_once ABSPATH . "wp-admin/includes/plugin.php";

if (version_compare(PHP_VERSION, '5.3') < 0) {
    throw new \Exception('Plugin requires PHP 5.3 or above');
}

class Plugin
{
    protected static $data;
    protected static $options;

    private function __construct() {}
    private function __clone() {}

    /**
     * Get option name for a options in the Wordpress database
     */
    public static function get_option_name()
    {
        return apply_filters("get_{DOMAIN}_option_name", DOMAIN);
    }

    /**
     * Define required plugin data
     */
    public static function define()
    {
        self::$data = get_plugin_data(__FILE__);

        if( !defined(__NAMESPACE__ . '\DOMAIN') )
            define(__NAMESPACE__ . '\DOMAIN', self::$data['TextDomain']);

        if( !defined(__NAMESPACE__ . '\PLUGIN_DIR') )
            define(__NAMESPACE__ . '\PLUGIN_DIR', __DIR__);
    }

    /**
     * include required files
     */
    public static function initialize()
    {
        load_plugin_textdomain( DOMAIN, false, basename(PLUGIN_DIR) . '/languages/' );

        require PLUGIN_DIR . '/include/utils.php';

        $autoload = PLUGIN_DIR . '/vendor/autoload.php';
        if( file_exists($autoload) ) include $autoload;

        $classes = array(
            'Simple_Queries_Public' => 'abstract/public.php',

            'Posts_Public' => 'shortcode/posts-public.php',
            'Posts_MCE'    => 'shortcode/posts-mce.php',
            'Terms_Public' => 'shortcode/terms-public.php',
            // 'Posts_MCE'    => 'shortcode/terms-mce.php',
            
            // 'Posts_Widget' => 'widget/posts.php',
            'Terms_Widget' => 'widget/terms.php',
        );

        foreach ($classes as $classname => $path) {
            require PLUGIN_DIR . '/include/' . $path;
        }

        self::__actions();
    }

    static function activate() { add_option( self::get_option_name(), array() ); }
    static function uninstall() { delete_option( self::get_option_name() ); }

    // public static function _admin_assets()
    // {
    // }

    public static function __actions()
    {
        add_action( 'admin_init', array( __NAMESPACE__ . '\Posts_MCE', 'init_mce_plugin' ), 20 );
        add_action( 'admin_head', array( __NAMESPACE__ . '\Posts_MCE', 'enqueue_mce_script' ));

        // add_action('widgets_init', array(__NAMESPACE__ . '\Posts_Widget', 'register_himself'));
        add_action('widgets_init', array(__NAMESPACE__ . '\Terms_Widget', 'register_himself'));

        add_shortcode( Utils::get_posts_shortcode_name(), array(new Posts_Public(), 'shortcode') );
        add_shortcode( Utils::get_terms_shortcode_name(), array(new Terms_Public(), 'shortcode') );

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
        add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
    }

    static function admin_enqueues( $hook )
    {
        global $pagenow;

        wp_enqueue_style(
            'widget-panels',
            Utils::get_plugin_url('/admin/assets/widget-panels.css'),
            array(),
            '1.0.0',
            'all'
        );

        if( 'customize.php' == $pagenow || 'widgets.php' == $pagenow || 'widgets.php' == $hook ) {
            wp_enqueue_script( 'widget-panels', Utils::get_plugin_url('/admin/assets/widget-panels.js'), array( 'jquery' ), '', true );

            #wp_enqueue_script( 'admin-scripts', self::get_plugin_url() . 'js/admin.js', array( 'widget-panels' ), '', true );
        };
    }
}

Plugin::define();

// register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'activate' ) );
// register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'initialize' ), 10 );
