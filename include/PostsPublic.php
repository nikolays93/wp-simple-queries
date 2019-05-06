<?php

namespace NikolayS93\Queries;

use NikolayS93\Queries\Types\Shortcode;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class PostsPublic // extends Shortcode
{
    /**
     * @var array
     */
    private $originalQueryVars = array();

    /**
     * @var boolean
     */
    private $isCustomTpl = false;

    public static function __shortcodeName()
    {
        $shortcode = 'posts';

        return apply_filters( "get_{DOMAIN}_posts_shortcode_name", $shortcode );
    }

    static function __defaults()
    {
        $defaults = array(
            'id'        => false,
            'max'       => '4', /* count show */
            'type'      => 'post', // page, product..
            'cat'       => '', /* category ID */
            'slug'      => '', // category slug
            'parent'    => '',
            'status'    => 'publish', // publish, future, alltime (publish+future) //
            'order'     => 'DESC', // ASC || DESC
            'orderby'   => 'menu_order date',
            'wrap_tag'  => 'div',
            'container' => 'container-fluid', //true=container, false=noDivContainer, string=custom container
            'tax'       => false,
            'terms'     => false,
            // template attrs
            'columns'   => '4', // 1 | 2 | 3 | 4 | 10 | 12
            'template'  => '', // for custom template
        );

        return $defaults;
    }

    function sanitizeShortcodeAtts( $atts )
    {
        if( !empty($atts['parent']) ) {
            /**
             * Multiple parent
             */
            if( is_array($atts['parent']) ) {
                $atts['parent'] = explode(',', $atts['parent']);
            }

            /**
             * Current parrent
             */
            elseif( in_array($atts['parent'], array('this', '(this)', '$this')) ) {
                $atts['parent'] = array( get_the_id() );
            }
        }

        /**
         * Already published and will be published form.. date..
         */
        if( "alltime" == $atts['status'] ) {
            $atts['status'] = array('publish', 'future');
        }

        /**
         * Need class container for .row element in bootstrap (need default)
         * @var $atts['container']  String  class of wrapper
         */
        if( 'true' === $atts['container'] )  $atts['container'] = 'container';
        if( 'false' === $atts['container'] ) $atts['container'] = '';

        /**
         * Own template for custom type if is not defined
         */
        if( empty($atts['template']) && 'post' !== $atts['type'] ) {
            $atts['template'] = $atts['type'];
        }

        return $atts;
    }

    function sanitizeQueryArgs()
    {
        $args = array(
            'p'               => $this->atts['id'],
            'cat'             => $this->atts['cat'],
            'post_type'       => $this->atts['type'],
            'posts_per_page'  => $this->atts['max'],
            'category_name'   => $this->atts['slug'],
            'post_parent__in' => $this->atts['parent'],
            'order'           => $this->atts['order'],
            'orderby'         => $this->atts['orderby'],
            'post_status'     => $this->atts['status'],
        );

        if( $this->atts['terms'] ) {
            if( ! $this->atts['tax'] ) {
                $this->atts['tax'] = ('product' === $this->atts['type']) ? 'product_cat' : 'category';
            }

            $this->atts['terms'] = array_filter(explode(',', $this->atts['terms']), 'absint');

            if(sizeof($this->atts['terms']) >= 1) {
                $args['tax_query'] = array(
                  array(
                    'taxonomy' => sanitize_text_field( $this->atts['tax'] ),
                    'terms'    => $this->atts['terms'],
                ),
              );
            }
        }

        return $args;
    }

    /**
     * Starts from here ;D
     */
    function shortcode( $atts = array() )
    {
        /**
         * Wordpress builtin parse with escape and shortcode filter
         */
        $atts = shortcode_atts( static::__defaults(), $atts, static::__shortcodeName() );

        /**
         * Force template name (defined in shortcode)
         */
        if( $atts['template'] ) $this->isCustomTpl = true;

        $this->atts = $this->sanitizeShortcodeAtts( $atts );

        /**
         * Insert WP_Query variables
         */
        $this->setQueryVars();

        $result = $this->executeQuery( $this->sanitizeQueryArgs() );

        /**
         * Return original vars
         */
        $this->resetQueryVars();

        return $result;
    }

    /******************************* Build DOM ********************************/

    function executeQuery( $args )
    {
        ob_start();

        $Query = new \WP_Query($args);

        if ( $query->have_posts() ) {

            $containerClasses = $this->getContainerClasses();

            echo $this->getContainerPart('start', $containerClasses);

            while ( $query->have_posts() )
            {
                $query->the_post();

                $tempalte_dir = apply_filters( 'wp-simple-query-template_dir', 'template-parts' );

                $this->getQueriedTemplate( $tempalte_dir . '/content', $this->atts['template'], array(
                    'post_type' => $args['post_type'],
                    'query'   => $args,
                    'columns' => $this->atts['columns'],
                ) );
            }

            echo $this->getContainerPart('end', $containerClasses);
        }
        else {
            if( defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ) {
                echo "<h4>Режим отладки:</h4>";
                echo 'Не найдено записей по данному запросу<hr>';

                var_dump($args);

                echo '<hr>template: ', $this->atts['template'],  '<br>';
                echo 'container: ',    $this->atts['container'], '<br>';
                echo 'columns: ',      $this->atts['columns'],   '<br>';
            }
        }

        return ob_get_clean();
    }

    private function getQueriedTemplate( $template, $slug = false, $template_args = array() )
    {
        extract($template_args);


        if( $slug ) {
            if( 'product' === $this->atts['type'] ) {
                if( !$this->isCustomTpl ) {
                    $templates[] = 'woocommerce/content-'.$slug.'-query.php';
                }

                $templates[] = 'woocommerce/content-'.$slug.'.php';
            }

            if( !$this->isCustomTpl ) {
                $templates[] = $template.'-'.$slug.'-query.php';
            }

            $templates[] = $template.'-'.$slug.'.php';
        }

        if( !$this->isCustomTpl ) {
            $templates[] = $template.'-query.php';
        }

        $templates[] = $template.'.php';

        if($req = locate_template($templates)) {
            require $req;
            return true;
        }

        if( defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ) {
            echo "<pre>";
            echo "Шаблон не найден по адресу: <br>" . get_template_directory() . '/<br>';
            print_r($templates);
            echo "</pre>";
        }

        return false;
    }

    private function getContainerClasses()
    {
        $classes = array();
        $classes[] = $this->atts['container'];
        $classes[] = 'custom-query';

        if( 'product' === $this->atts['type'] ) {
            $classes[] = 'products';
        }

        $classes = array_filter($classes, 'esc_attr');

        return apply_filters( 'wp-simple-queries-container-class', $classes );
    }

    private function getContainerPart( $part = false, $classes = array() )
    {
        $result = "";

        // do not display for empty container class
        if( ! $this->atts['container'] ) return $result;

        $containers = array('container', 'container-fluid');

        switch ( $part ) {
            case 'start':
                if( 'product' === $this->atts['type'] ) {
                    // add .woocommerce for css
                    $result .= '<section class="woocommerce">';
                }

                $result .= sprintf('<%s class="%s">',
                    esc_attr( $this->atts['wrap_tag'] ),
                    implode(' ', $classes)
                );

                if( in_array( $this->atts['container'], $containers ) && $this->atts['wrap_tag'] !== 'ul' ) {
                    $result .= '<div class="row">';
                }
            break;

            case 'end':
                if( in_array( $this->atts['container'], $containers ) && $this->atts['wrap_tag'] !== 'ul' ) {
                    $result.= '</div><!-- .row -->';
                }

                $result .= sprintf('</%s><!-- .%s -->',
                    esc_attr( $this->atts['wrap_tag'] ),
                    implode('.', $classes)
                );

                if( 'product' === $this->atts['type'] ) {
                    $result .= '</section>';
                }
            break;
        }

        return $result;
    }


    /************************* WP_Query manipulations *************************/

    private function setQueryVars()
    {
        global $wp_query;

        foreach (get_object_vars( $wp_query ) as $key => $value)
        {
            $this->originalQueryVars[ $key ] = $value;
        }

        if( $this->atts['max'] != 1 )       $wp_query->is_singular = false;
        if( $this->atts['type'] != 'page' ) $wp_query->is_page = false;
    }

    private function resetQueryVars()
    {
        global $wp_query;

        foreach ($this->originalQueryVars as $key => $value)
        {
            if( property_exists($wp_query, $key) ) {
                $wp_query->$key = $value;
            }
        }

        wp_reset_postdata();
    }

    protected function getOriginalQueryVars()
    {
        return $this->originalQueryVars;
    }
}
