<?php

namespace NikolayS93\Queries;

use NikolayS93\Queries\Types\Shortcode;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class TermsPublic // extends Shortcode
{
    private static function __defaults()
    {
        $defaults = array(
            'id'            => false,

            'taxonomy'      => 'category',
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => true,

            'include'       => array(),
            'exclude'       => array(),
            'exclude_tree'  => array(),

            'number'        => -1, // max

            'slug'          => '',
            'parent'        => '', // только прямые потомки
            'hierarchical'  => true,
            'child_of'      => 0, // все дети

            'name__like'    => '', // в названии которых есть
            'description__like' => '',

            'name'          => '', // str/arr поле name для получения термина по нему. C 4.2.
            'childless'     => false, // Термины, без дочерних термнов с 4.2.
            'update_term_meta_cache' => true, // подгружать метаданные в кэш

            'wrap_tag'  => 'div',
            'container' => 'container-fluid',

            'columns'   => '4', // 1 | 2 | 3 | 4 | 10 | 12
            'template'  => '',

            // 'show_thumb'     => 0,
            // 'thumb_size'     => 0,
            // 'thumb_size_w'   => 55,
            // 'thumb_size_h'   => 55,
            'show_desc'      => 0,
            'desc_length'    => 15,
            'list_style'     => 'ul',
            'show_count'     => 0,
            'show_empty'    => false,
        );

        return apply_filters( 'wp-simple-queries-posts-defaults', $defaults );
    }

    public static function __shortcodeName()
    {
        $shortcode = 'terms';

        return apply_filters( "get_{DOMAIN}_terms_shortcode_name", $shortcode );
    }

    function sanitizeShortcodeAtts( $atts )
    {
        return $atts;
    }

    function getContainerClasses()
    {
        $classes = array();
        $classes[] = 'st-widget';
        $classes[] = 'st-wrap';

        return array_filter( array_map('esc_attr', $classes) );
    }

    function getContainerPart($part, $containerClasses = '')
    {
        switch ($part) {
            case 'start':
                echo '<div class="'. implode(' ', $containerClasses) .'">';
                break;

            case 'end':
                echo '</div><!-- /.'. implode('.', $containerClasses) .' -->';
                break;
        }

        return '';
    }

    function shortcode( $atts = array() )
    {
        $this->atts = $this->sanitizeShortcodeAtts( $atts );

        /**
         * Wordpress builtin parse with escape and shortcode filter
         */
        $atts = shortcode_atts( static::__defaults(), $atts, static::__shortcodeName() );

        $categories = static::getCategories( $atts );

        $containerClasses = $this->getContainerClasses();

        ob_start();
        $this->getContainerPart('start', $containerClasses);

        if( ! empty( $categories ) ) {
            self::recursive_list_item( $categories, $atts );
        }

        $this->getContainerPart('end', $containerClasses);

        return ob_get_clean();
    }

    public static function widget( $widget, $title, $instance, $args )
    {
        $categories = static::getCategories( $instance );

        echo $args['before_widget'];

        if( $title && $instance['show_title'] ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        echo '<div class="st-widget st-wrap">';

            if( ! empty( $categories ) )
                self::recursive_list_item( $categories, $instance );

        echo '</div><!-- /.st-widget.st-wrap -->';

        echo $args['after_widget'];
    }

    public static function recursive_list_item( $categories, $instance, $level = 0 )
    {

        /**
         * at 1 level
         */
        $level++;

        /**
         * Sanitize tag list wrapper
         */
        if( ! in_array($instance['list_style'], array('div', 'ol', 'ul')) ) {
            $instance['list_style'] = 'ul';
        }

        /**
         * Sanitize tag list item
         */
        if( 'div' !== ($list_item = $instance['list_style']) ) {
            $list_item = 'li';
        }

        do_action('TermsPublic::before_start_list', $level);

        /**
         * Open list wrapper
         */
        printf( '<%s class="simple-terms-list level-%d">',
            esc_html($instance['list_style']),
            $level
        );

        do_action('TermsPublic::after_start_list', $level);


        /**
         * Each all categories in level
         */
        foreach( $categories as $obTerm )
        {
            if( empty($obTerm->term_id) ) continue;

            $item_id    = self::get_item_id( $obTerm, $instance );
            $item_class = self::get_item_class( $obTerm, $instance );

            /**
             * Open list item
             */
            printf( '<%s id="%s" class="%s">', esc_html($list_item), esc_attr($item_id), esc_attr($item_class) );


            // $args = wp_parse_args( $instance, array(
            //  'item_desc'  => self::get_term_excerpt( $term, $instance ),
            //           'show_desc'  => '',
            //  ) );

            //       $args['show_thumb'] = ''; // $instance['show_thumb'] ?
                // self::the_item_thumbnail_div( $term, $instance, false ) : '';

            $args['show_count'] = $instance['show_count'] ? '<small class="count">(<span>'. $obTerm->count .'</span>)</small>' : '';

            // $result[] = sprintf('<div id="%s" class="%s">', $args['item_id'], $args['item_class']);
            // $result[] = $args['show_thumb'];
            printf( '<a href="%s" rel="bookmark">%s</a>%s',
                esc_url( get_term_link( $obTerm ) ),
                $obTerm->name,
                $args['show_count']
            );

            // if( $args['show_desc'] ) {
            //  $result[] = '<span class="term-description">';
            //  $result[] = $args['item_desc'];
            //  $result[] = '</span><!-- /.term-summary -->';
            // }

            // $result[] = "</div>";


            if( !empty($obTerm->children) ) {
                /** @recursive */
                self::recursive_list_item( $obTerm->children, $instance, $level );
            }
            // else {
            //     $level = 0;
            // }

            /**
             * Close list item
             */
            printf( '</%s>', esc_html($list_item) );
        }

        do_action('TermsPublic::before_end_list', $level);

        printf( '</%s>', esc_html($instance['list_style']) );

        do_action('TermsPublic::after_end_list', $level);
    }

    public static function getCategories( $instance )
    {
        $result = array();
        $args = array(
            'taxonomy'      => $instance['taxonomy'],
            'orderby'       => $instance['orderby'],
            'order'         => $instance['order'],
            'hide_empty'    => ! $instance['show_empty'] ? true : false,
            // 'object_ids'    => null,
            // 'include'       => array(),
            'exclude'       => $instance['exclude'],
            // 'exclude_tree'  => array(),
            'number'        => intval($instance['number']),
            // 'fields'        => 'all',
            // 'count'         => false,
            // 'slug'          => '',
            // 'parent'         => '',
            'hierarchical'  => 1, // $instance['hierarchical'],
            // 'child_of'      => 0,
            // 'get'           => '',
            // 'name__like'    => '',
            // 'pad_counts'    => false,
            // 'offset'        => '',
            // 'search'        => '',
            // 'cache_domain'  => 'core',
            // 'name'          => '',
            // 'childless'     => false,
            // 'update_term_meta_cache' => true,
            // 'meta_query'    => '',
        );

        if( 0 < $args['number'] ) {
            $args['hierarchical'] = 0;
            $instance['hierarchical'] = 0;
        }
        else {
            unset($args['number']);
        }

        $terms = get_terms( $args );

        if ( ! is_wp_error( $terms ) ) {
            if( $instance['hierarchical'] ) {
                Utils::sort_terms_hierarchicaly($terms, $result);
            }
            else {
                foreach ($terms as $term) {
                    $result[ $term->term_id ] = $term;
                }
            }
        }

        return $result;
    }


    private static function get_item_id( $term = 0, $instance = array() )
    {

        return ( !empty($term->term_id) ) ? $instance['taxonomy'] . '-term-' . $term->term_id : '';
    }

    private static function get_item_class( $term = 0, $instance = array() )
    {
        if( ! $term ) return 'undefined term';

        $classes   = array('stqt-item');
        $classes[] = 'stqt-' . $term->taxonomy . '-item';
        $classes[] = 'stqt-item-term-' . $term->term_id;

        if( $term->term_id == get_queried_object_id() ) {
            $classes[] = 'active';
        }

        if ( $term->parent > 0 ) {
            $classes[] = 'stqt-child-term';
            $classes[] = 'stqt-parent-' . $term->parent;
        }

        $classes = array_map( 'sanitize_html_class',
            apply_filters( 'stqt-item-term-class', $classes, $term ) );

        return implode( ' ', $classes );
    }

    // public static function get_term_excerpt( $term = 0, $instance = array(), $trim = 'words' )
    // {
    //     if ( empty( $term ) ) return '';

    //     $_text = $term->description;

    //     if( '' === $_text ) {
    //         return '';
    //     }

    //     $_text = strip_shortcodes( $_text );
    //     $_text = str_replace(']]>', ']]&gt;', $_text);

    //     $text = apply_filters( 'acatw_term_excerpt', $_text, $term, $instance );

    //     $_length = ( ! empty( $instance['desc_length'] ) ) ? absint( $instance['desc_length'] ) : 55 ;
    //     $length = apply_filters( 'acatw_term_excerpt_length', $_length );

    //     $_aposiopesis = ( ! empty( $instance['excerpt_more'] ) ) ? $instance['excerpt_more'] : '&hellip;' ;
    //     $aposiopesis = apply_filters( 'acatw_term_excerpt_more', $_aposiopesis );

    //     if( 'chars' === $trim ){
    //         $text = wp_html_excerpt( $text, $length, $aposiopesis );
    //     } else {
    //         $text = wp_trim_words( $text, $length, $aposiopesis );
    //     }

    //     return $text;
    // }

    // public static function the_item_thumbnail_div( $term = 0, $instance = array(), $echo = true )
    // {
    //     if ( empty( $term ) ) {
    //         return '';
    //     }

    //     $html = '';
    //     $thumb = Utils::get_term_thumbnail( $term, $instance );

    //     // $class_str = implode( ' ', $classes );

    //     if( '' !== $thumb ) {
    //         $html .= sprintf('<span class="term-thumbnail"><a href="%s">%2$s</a></span>',
    //             esc_url( get_term_link( $term ) ),
    //             $thumb
    //         );
    //     };

    //     if( ! $echo )
    //         return $html;

    //     echo $html;
    // }
}
