<?php

namespace NikolayS93\Queries;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class Utils extends Plugin
{
    private function __construct() {}
    private function __clone() {}

    /**
     * Получает настройку из parent::$options || из кэша || из базы данных
     * @param  mixed  $default Что вернуть если опции не существует
     * @return mixed
     */
    private static function get_option( $default = array() )
    {
        if( ! parent::$options ) {
            parent::$options = get_option( parent::get_option_name(), $default );
        }

        return apply_filters( "get_{DOMAIN}_option", parent::$options );
    }

    public static function get_posts_shortcode_name()
    {
    	$shortcode = 'posts';

    	return apply_filters( "get_{DOMAIN}_posts_shortcode_name", $shortcode );
    }

    public static function get_terms_shortcode_name()
    {
    	$shortcode = 'terms';

    	return apply_filters( "get_{DOMAIN}_terms_shortcode_name", $shortcode );
    }

    /**
     * Получает url (адресную строку) до плагина
     * @param  string $path путь должен начинаться с / (по аналогии с __DIR__)
     * @return string
     */
    public static function get_plugin_url( $path = '' )
    {
        $url = plugins_url( basename(PLUGIN_DIR) ) . $path;

        return apply_filters( "get_{DOMAIN}_plugin_url", $url, $path );
    }

    public static function get_template( $template, $slug = false, $data = array() )
    {
        if ($slug) $templates[] = PLUGIN_DIR . '/' . $template . '-' . $slug;
        $templates[] = PLUGIN_DIR . '/' . $template;

        if ($tpl = locate_template($templates)) {
            return $tpl;
        }

        return false;
    }

    public static function get_settings( $filename, $args = array() )
    {
        $path = PLUGIN_DIR . '/admin/settings/' . $filename;

        if ( ! is_readable( $path ) ) {
            return false;
        }

        return include( $path );
    }

    public static function get_admin_template( $tpl = '', $data = array(), $include = false )
    {
        $filename = PLUGIN_DIR . '/admin/template/' . $tpl;
        if( !file_exists($filename) ) $filename = false;

        if( $filename && $include ) {
            include $filename;
        }

        return $filename;
    }

    /**
     * Получает параметр из опции плагина
     * @todo Добавить фильтр
     *
     * @param  string  $prop_name Ключ опции плагина или 'all' (вернуть опцию целиком)
     * @param  mixed   $default   Что возвращать, если параметр не найден
     * @return mixed
     */
    public static function get( $prop_name, $default = false )
    {
        $option = self::get_option();
        if( 'all' === $prop_name ) {
            if( is_array($option) && count($option) ) {
                return $option;
            }

            return $default;
        }

        return isset( $option[ $prop_name ] ) ? $option[ $prop_name ] : $default;
    }

    /**
     * Установит параметр в опцию плагина
     * @todo Подумать, может стоит сделать $autoload через фильтр, а не параметр
     *
     * @param mixed  $prop_name Ключ опции плагина || array(параметр => значение)
     * @param string $value     значение (если $prop_name не массив)
     * @param string $autoload  Подгружать опцию автоматически @see update_option()
     * @return bool             Совершились ли обновления @see update_option()
     */
    public static function set( $prop_name, $value = '', $autoload = null )
    {
        $option = self::get_option();
        if( ! is_array($prop_name) ) $prop_name = array($prop_name => $value);

        foreach ($prop_name as $prop_key => $prop_value) {
            $option[ $prop_key ] = $prop_value;
        }

        return update_option( parent::get_option_name(), $option, $autoload );
    }

        static function __post_defaults()
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

    static function __terms_defaults()
    {
        $def = array(
            'title'          => __( 'Terms' ),
            'orderby'        => 'name',
            'order'          => 'asc',
            // 'include'        => array(),
            'exclude'        => array(),
            // 'show_thumb'     => 0,
            // 'thumb_size'     => 0,
            // 'thumb_size_w'   => 55,
            // 'thumb_size_h'   => 55,
            'show_desc'      => 0,
            'desc_length'    => 15,
            'list_style'     => 'ul',
            'show_count'     => 0,
            'taxonomy'       => 'category',
            'number'         => -1,
            // 'show_title'     => 0,
            'hierarchical'   => 1,
        );

        return $def;
    }

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
