<?php

namespace NikolayS93\Queries;

class Posts_MCE
{
    static function init_mce_plugin()
    {
        /** MCE Editor */
        if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
            return;
        }

        add_filter("mce_external_plugins", function( $plugin_array ) {
            $plugin_array['query_shortcode'] = Utils::get_plugin_url( '/admin/assets/posts-query-button.js' );
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

        wp_enqueue_script( 'query_shortcode', Utils::get_plugin_url('/admin/assets/posts-query-shortcode.js'),
            array( 'shortcode', 'wp-util', 'jquery' ), false, true );
        wp_localize_script( 'query_shortcode',
        	'queryPosts',
        	array(
        		'nonce'     => '',
        		'shortcode' => Utils::get_posts_shortcode_name(),
        		'types'     => Utils::get_post_type_list(),
        		'categories' => '',
        		'pages' => '',
        		'taxonomies' => '',
        		'terms' => '',
        		'statuses'  => Utils::get_status_list(),
        		'orderby'   => Utils::get_order_by_postlist(),
        	)
        );
    }
}
