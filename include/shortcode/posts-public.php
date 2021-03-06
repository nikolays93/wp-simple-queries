<?php

namespace Nikolays93\Queries;

use Nikolays93\Queries\Simple_Queries_Public;

if ( ! defined( 'ABSPATH' ) ) exit; // disable direct access

class Posts_Public extends Simple_Queries_Public
{
    function sanitize_atts( $atts )
    {
        if( is_array($atts['parent']) ) {
            $atts['parent'] = explode(',', $atts['parent']);
        }
        elseif( in_array($atts['parent'], array('this', '(this)', '$this')) ) {
            $atts['parent'] = array( get_the_id() );
        }

        if( "alltime" == $atts['status'] ) {
            $atts['status'] = array('publish', 'future');
        }

        switch ($atts['container']) {
            case 'true': $atts['container'] = 'container'; break;
            case 'false': $atts['container'] = false; break;
        }

        if( $atts['template'] ) {
            $this->custom_tpl = true;
        }
        elseif( 'post' !== $atts['type'] ) {
            $atts['template'] = $atts['type'];
        }

        $this->atts = $atts;
    }

    function get_query_args()
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
                $this->atts['tax'] = ('product' == $this->atts['type']) ? 'product_cat' : 'category';
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

    public static function query( $atts, $args, $custom_tpl )
    {
        $query = new \WP_Query($args);

        // шаблон
        ob_start();
        if ( $query->have_posts() ) {
            echo self::get_container_part('start', $atts);

            while ( $query->have_posts() ) {
                $query->the_post();

                $tempalte_dir = apply_filters( 'wp-simple-query-template_dir', 'template-parts' );

                self::get_template( $tempalte_dir . '/content', $atts['template'], array(
                    'post_type' => $args['post_type'],
                    'query'   => $args,
                    'columns' => $atts['columns'],
                ), $custom_tpl );
            }

            echo self::get_container_part('end', $atts);
        }
        else {
            if( defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ) {
                echo "<h4>Режим отладки:</h4>";
                echo 'Не найдено записей по данному запросу<hr>';
                var_dump($args);
                echo '<hr>template: ', $atts['template'], '<br>';
                echo 'container: ', $atts['container'], '<br>';
                echo 'columns: ', $atts['columns'], '<br>';
            }
        }

        self::reset_query_variables();
        wp_reset_postdata();

        return ob_get_clean();
    }

    function shortcode( $atts = array(), $instance = array() ) {
        $atts = shortcode_atts( Utils::__post_defaults(), $atts, Utils::get_posts_shortcode_name() );
        $this->sanitize_atts( $atts );
        $this->set_query_variables();

        return self::query( $this->atts, $this->get_query_args(), $this->custom_tpl );
    }
}
