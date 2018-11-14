<?php

namespace Nikolays93\Queries;

if ( ! defined( 'ABSPATH' ) )
    exit; // disable direct access

abstract class Simple_Queries_Public
{
    protected static $tmp;
    protected $custom_tpl = false;
    protected $atts = array();

    // static function _defaults();
    // static function query( $atts, $args, $custom_tpl );
   /* Данный метод должен быть определён в дочернем классе */
    abstract function sanitize_atts( $atts );
    abstract function init( $atts = array(), $instance = array() );

    protected static function get_template( $template, $slug = false, $template_args = array(), $custom_tpl = true, $type = 'posts' )
    {
        extract($template_args);

        if( 'product' == $type ) {
            if( ! $custom_tpl )
                $templates[] = 'woocommerce/content-'.$slug.'-query.php';

            $templates[] = 'woocommerce/content-'.$slug.'.php';
        }

        if( $slug ) {
            if( ! $custom_tpl )
                $templates[] = $template.'-'.$slug.'-query.php';

            $templates[] = $template.'-'.$slug.'.php';
        }

        if( ! $custom_tpl )
            $templates[] = $template.'-query.php';

        $templates[] = $template.'.php';

        if($req = locate_template($templates)) {
            require $req;
            return true;
        }

        if( ! is_admin() && defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY) {
            echo "<pre>";
            echo "Шаблон не найден по адресу: <br>" . get_template_directory() . '/<br>';
            print_r($templates);
            echo "</pre>";
        }

        return false;
    }

    private static function get_container_classes( $class, $post_type = 'posts' )
    {
        $classes[] = $class;
        $classes[] = 'custom-query';

        if( 'product' === $post_type ) {
            // $tag = 'ul';
            $classes[] = 'products';
        }

        $classes = array_filter($classes, 'esc_attr');
        reset( $classes );

        return apply_filters( 'wp-simple-queries-container-class', $classes );
    }

    static function get_container_part( $part = false, $atts = array() )
    {
        $result = "";

        // do not display for empty container class
        if( ! $atts['container'] ) return $result;
        $classes = self::get_container_classes( $atts['container'], $atts['type'] );
        reset( $classes );

        switch ( $part ) {
            case 'start':
                if( 'product' === $atts['type'] ) {
                    // add .woocommerce for css
                    $result .= '<section class="woocommerce">';
                }

                $result .= sprintf('<%s class="%s">',
                    esc_attr( $atts['wrap_tag'] ),
                    implode(' ', $classes)
                    );

                if( in_array( current( $classes ), array('container', 'container-fluid') )
                    && $atts['wrap_tag'] !== 'ul' ) {
                    $result .= '<div class="row">';
                }
            break;

            case 'end':
                if( in_array( current( $classes ), array('container', 'container-fluid') )
                    && $atts['wrap_tag'] !== 'ul' ) {
                    $result.= '</div><!-- .row -->';
                }

                $result .= sprintf('</%s><!-- .%s -->',
                    esc_attr( $atts['wrap_tag'] ),
                    implode('.', $classes)
                    );

                if( 'product' === $atts['type'] ) {
                    $result .= '</section>';
                }
            break;
        }

        return $result;
    }

    protected static function insert_query_variable($var, $value)
    {
        global $wp_query;

        self::$tmp[ $var ] = $wp_query->$var;

        $wp_query->$var = $value;
    }

    protected function set_query_variables()
    {
        if( $this->atts['max'] != 1 ) self::insert_query_variable('is_singular', '');
        if( $this->atts['type'] != 'page' ) self::insert_query_variable('is_page', '');
    }

    protected static function reset_query_variables()
    {
        global $wp_query;

        if( sizeof(self::$tmp) !== 0 ){
            foreach (self::$tmp as $key => $value) {
                $wp_query->$key = $value;
            }
        }
    }
}