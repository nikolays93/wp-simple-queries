<?php

/*
Plugin Name: WP Simple Queries Shortcode and Widget
Description:
Plugin URI: http://#
Version: 0.0.1
Author: NikolayS93
Author URI: https://vk.com/nikolays_93
Author EMAIL: nikolayS93@ya.ru
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/**
 * Хуки плагина:
 * $pageslug . _after_title (default empty hook)
 * $pageslug . _before_form_inputs (default empty hook)
 * $pageslug . _inside_page_content
 * $pageslug . _inside_side_container
 * $pageslug . _inside_advanced_container
 * $pageslug . _after_form_inputs (default empty hook)
 * $pageslug . _after_page_wrap (default empty hook)
 *
 * Фильтры плагина:
 * "get_{DOMAIN}_option_name" - имя опции плагина
 * "get_{DOMAIN}_option" - значение опции плагина
 * "load_{DOMAIN}_file_if_exists" - информация полученная с файла
 * "get_{DOMAIN}_plugin_dir" - Дирректория плагина (доступ к файлам сервера)
 * "get_{DOMAIN}_plugin_url" - УРЛ плагина (доступ к внешним файлам)
 *
 * $pageslug . _form_action - Аттрибут action формы на странице настроек плагина
 * $pageslug . _form_method - Аттрибут method формы на странице настроек плагина
 *
 * wp-queries-post-type-list
 *  wp-queries-status-list
 * wp-queries-order-by-postlist
 */

namespace CDevelopers\Queries;

if ( ! defined( 'ABSPATH' ) )
  exit; // disable direct access

const PLUGIN_DIR = __DIR__;
const DOMAIN = '_plugin';

// Нужно подключить заранее для активации и деактивации плагина @see activate(), uninstall();
require __DIR__ . '/utils.php';

class Plugin
{
    private static $initialized;
    private function __construct() {}

    static function activate() { add_option( Utils::get_option_name(), array() ); }
    static function uninstall() { delete_option( Utils::get_option_name() ); }

    public static function initialize()
    {
        if( self::$initialized )
            return false;

        load_plugin_textdomain( DOMAIN, false, basename(PLUGIN_DIR) . '/languages/' );
        self::include_required_files();
        self::_actions();
        self::_filters();

        self::$initialized = true;
    }

    /**
     * Подключение файлов нужных для работы плагина
     */
    private static function include_required_files()
    {
        $include = Utils::get_plugin_dir('includes');
        $libs    = Utils::get_plugin_dir('libs');

        $classes = array(
            __NAMESPACE__ . '\WP_Admin_Page'  => $libs . '/wp-admin-page.php',
            __NAMESPACE__ . '\WP_Admin_Forms' => $libs . '/wp-admin-forms.php',

            __NAMESPACE__ . '\Simple_Queries_Public' => $include . '/abstract/simple-queries-public.php',
            __NAMESPACE__ . '\Simple_Posts_Queries_Public' => $include . '/simple-posts-queries-public.php',
            __NAMESPACE__ . '\Simple_Terms_Queries_Public' => $include . '/simple-terms-queries-public.php',
        );

        foreach ($classes as $classname => $path) {
            if( ! class_exists($classname) ) {
                Utils::load_file_if_exists( $path );
            }
            else {
                Utils::write_debug(__('Duplicate class ' . $classname, DOMAIN), __FILE__);
            }
        }

        // includes
        // Utils::load_file_if_exists( $include . '/admin-settings-page.php' );
        // Utils::load_file_if_exists( $include . '/public-queries.php' );
    }

    private static function _actions()
    {
        add_action( 'admin_init', array( __CLASS__, 'init_mce_plugin' ), 20 );
        add_action( 'admin_head', array( __CLASS__, 'enqueue_mce_script' ));

        // add_action('widgets_init',
        //     array(__NAMESPACE__ . '\Simple_Terms_Queries_Widget', 'register_himself'));
        // add_action('widgets_init',
        //     array(__NAMESPACE__ . '\Simple_Posts_Queries_Widget', 'register_himself'));

        add_shortcode( Utils::get_shortcode_name( 'terms' ),
            array(new Simple_Terms_Queries_Public(), 'init') );
        add_shortcode( Utils::get_shortcode_name( 'posts' ),
            array(new Simple_Posts_Queries_Public(), 'init') );

        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
        add_action( 'customize_controls_enqueue_scripts', array( __CLASS__, 'admin_enqueues' ) );
    }

    private static function _filters(){}

    /**
     * Подключаем нужные скрипты
     */
    public static function admin_enqueues( $hook )
    {
        global $pagenow;

        $enqueue = false;
        if( 'customize.php' == $pagenow || 'widgets.php' == $pagenow || 'widgets.php' == $hook ){
            $enqueue = true;
        };

        wp_enqueue_style(
            'widget-panels',
            Utils::get_plugin_url('assets/widget-panels.css'),
            array(),
            '1.0.0',
            'all'
        );

        if( ! $enqueue ){
            return;
        };

        wp_enqueue_script( 'widget-panels', Utils::get_plugin_url('assets/widget-panels.js'), array( 'jquery' ), '', true );

        #wp_enqueue_script( 'acatw-admin-scripts', self::get_plugin_url() . 'js/admin.js', array( 'widget-panels' ), '', true );
    }

    static function init_mce_plugin()
    {
        /** MCE Editor */
        if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }

        add_filter("mce_external_plugins", function( $plugin_array ) {
            $plugin_array['query_shortcode'] = Utils::get_plugin_url( '/js/query_button.js' );
            return $plugin_array;
        });

        add_filter("mce_buttons", function( $buttons ) {
            $buttons[] = 'query_shortcode';
            return $buttons;
        });
    }

    static function enqueue_mce_script()
    {
        if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
            return;
        }

        wp_enqueue_script( 'query_shortcode', Utils::get_plugin_url('/js/query_shortcode.js'),
            array( 'shortcode', 'wp-util', 'jquery' ), false, true );
        wp_localize_script( 'query_shortcode',
            'qOpt',
            array(
                'nonce'     => '',
                'shortcode' => Utils::get_shortcode_name(),
                'types'     => Utils::get_post_type_list(),
                'categories' => '',
                'pages' => '',
                'taxanomies' => '',
                'terms' => '',
                'statuses'  => Utils::get_status_list(),
                'orderby'   => Utils::get_order_by_list(),
                ) );
    }
}



register_activation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'activate' ) );
register_uninstall_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'uninstall' ) );
// register_deactivation_hook( __FILE__, array( __NAMESPACE__ . '\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Plugin', 'initialize' ), 10 );
