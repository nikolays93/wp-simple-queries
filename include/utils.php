<?php

namespace NikolayS93\Queries;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class Utils extends Plugin
{
    /**
     * Получает типы записей для выбора пользователем (объекты для MCE)
     */
    public static function get_post_type_list()
    {
        $post_types = get_post_types( array('public' => true) );
        $types = array();
        foreach ($post_types as $value => $text) {
            $types[] = (object) array(
                'value' => $value,
                'text' => __( ucfirst($text) )
            );
        }

        return apply_filters( 'wp-queries-post-type-list', $types );
    }

    /**
     * Получает статусы записей для выбора пользователем (объекты для MCE)
     */
    public static function get_status_list()
    {
        $statuses = array(
            (object) array(
                'text' => __( 'Published' ),
                'value' => 'publish'
                ),
            (object) array(
                'text' => __( 'Scheduled' ),
                'value' => 'future'
                ),
            (object) array(
                'text' => __( 'За все время' ),
                'value' => 'alltime'
                ),
            (object) array(
                'text' => __( 'Any' ),
                'value' => 'any',
                ),
            );

        return apply_filters( 'wp-queries-status-list', $statuses );
    }

    /**
     * Получает варианты сортировки для выбора пользователем (объекты для MCE)
     */
    public static function get_order_by_postlist()
    {
        $order_by = array(
            (object) array(
                'text' => __( 'None' ),
                'value' => 'none'
                ),
            (object) array(
                'text' => __('ID'),
                'value' => 'ID'
                ),
            (object) array(
                'text' => __('Author'),
                'value' => 'author'
                ),
            (object) array(
                'text' => __('Title'),
                'value' => 'title'
                ),
            (object) array(
                'text' => __('Name'),
                'value' => 'name'
                ),
            (object) array(
                'text' => __('Type'),
                'value' => 'type'
                ),
            (object) array(
                'text' => __('Date'),
                'value' => 'date'
                ),
            (object) array(
                'text' => __('Modified'),
                'value' => 'modified'
                ),
            (object) array(
                'text' => __('Parent'),
                'value' => 'parent'
                ),
            (object) array(
                'text' => __('Random'),
                'value' => 'rand'
                ),
            (object) array(
                'text' => __('Comment'),
                'value' => 'comment_count'
                ),
            (object) array(
                'text' => __('Relevance'),
                'value' => 'relevance'
                ),
            (object) array(
                'text' => __('Menu'),
                'value' => 'menu_order date'
                ),
            );

        return apply_filters( 'wp-queries-order-by-postlist', $order_by );
    }

    /**
     * Sanitize option values (escape html) and native wordpress sanitize keys.
     * @param  Array   $options list of options
     * @param  boolean $sort    need sorts?
     * @return Array   $options results
     */
    public static function sanitize_select_array( $options, $sort = false )
    {
        $options = ( ! is_array( $options ) ) ? (array) $options : $options ;

        // Clean the values (since it can be filtered by other plugins)
        $options = array_map( 'esc_html', $options );

        // Flip to clean the keys (used as <option> values in <select> field on form)
        $options = array_flip( $options );
        $options = array_map( 'sanitize_key', $options );

        // Flip back
        $options = array_flip( $options );

        if( $sort ) {
            asort( $options );
        };

        return $options;
    }

    /**
     * Recursively sort an array of taxonomy terms hierarchically. Child categories will be
     * placed under a 'children' member of their parent term.
     * @param Array   $cats     taxonomy term objects to sort
     * @param Array   $into     result array to put them in
     * @param integer $parentId the current parent ID to put them in
     */
    static function sort_terms_hierarchicaly(Array &$cats, Array &$into, $parentId = 0)
    {
        foreach ($cats as $i => $cat) {
            if ($cat->parent == $parentId) {
                $into[$cat->term_id] = $cat;
                unset($cats[$i]);
            }
        }

        foreach ($into as $topCat) {
            $topCat->children = array();
            self::sort_terms_hierarchicaly($cats, $topCat->children, $topCat->term_id);
        }
    }
}
