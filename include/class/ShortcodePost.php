<?php

namespace NikolayS93\Queries;

use NikolayS93\Queries\Creational\Shortcode;

if ( ! defined( 'ABSPATH' ) ) { // disable direct access.
	exit;
}

class ShortcodePost extends Shortcode {

	const NAME = 'posts';

	/**
	 * @var array
	 */
	private $orign_query_vars = array();

	/**
	 * @var boolean
	 */
	private $is_custom_template = false;

	/**
	 * @var array
	 */
	private $atts;

	public static function get_name() {
		return apply_filters( Plugin::PREFIX . 'get_posts_shortcode_name', static::NAME );
	}

	public static function get_defaults() {
		return apply_filters( Plugin::PREFIX . 'get_posts_shortcode_defaults', array(
			'id'        => false,
			'max'       => '4', /* count show */
			'type'      => 'post', // page, product..
			'cat'       => '', /* category ID */
			'slug'      => '', // category slug
			'parent'    => '',
			'status'    => 'publish', // publish, future, alltime (publish+future)
			'order'     => 'DESC', // ASC || DESC
			'orderby'   => 'menu_order date',
			'wrap_tag'  => 'div',
			'container' => 'container-fluid', // true=container, false=noDivContainer, string=custom container
			'tax'       => false,
			'terms'     => false,
			// template attrs
			'columns'   => '4', // 1 | 2 | 3 | 4 | 10 | 12
			'template'  => '', // for custom template
		) );
	}

	private function sanitize_attrs( $atts, $defaults = array(), $shortcode_name = null ) {
		/** @var array Wordpress built in shortcode attributes */
		$atts = shortcode_atts( $defaults, $atts, $shortcode_name );

		/**
		 * Query by parent
		 */
		if ( ! empty( $atts['parent'] ) ) {
			// Multiple parent.
			if ( is_array( $atts['parent'] ) ) {
				$atts['parent'] = implode( ',', $atts['parent'] );
			} // Current parrent
			elseif ( in_array( $atts['parent'], array( 'this', '(this)', '$this' ) ) ) {
				$atts['parent'] = array( get_the_id() );
			}
		}

		/**
		 * Already published and will be published form.. date..
		 */
		if ( "alltime" == $atts['status'] ) {
			$atts['status'] = array( 'publish', 'future' );
		}

		/**
		 * Need class container for .row element in bootstrap (need default)
		 */
		if ( 'true' === $atts['container'] ) {
			$atts['container'] = 'container';
		}
		if ( 'false' === $atts['container'] ) {
			$atts['container'] = '';
		}

		/**
		 * Own template for custom type if is not defined
		 */
		if ( empty( $atts['template'] ) && 'post' !== $atts['type'] ) {
			$atts['template'] = $atts['type'];
		}

		/**
		 * Force template name (defined in shortcode)
		 */
		if ( $atts['template'] ) {
			$this->is_custom_template = true;
		}

		return $atts;
	}

	private function sanitize_query_args() {
		$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;

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
			'paged'           => $paged,
		);

		if ( $this->atts['terms'] ) {
			if ( empty( $this->atts['tax'] ) ) {
				$this->atts['tax'] = ( 'product' === $this->atts['type'] ) ? 'product_cat' : 'category';
			}

			$this->atts['terms'] = array_filter( explode( ',', $this->atts['terms'] ), 'absint' );

			if ( sizeof( $this->atts['terms'] ) >= 1 ) {
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

	private function is_row( $class ) {
		return in_array( $class, array( 'container', 'container-fluid' ), true );
	}

	function build( $atts = array() ) {
		// Required for pagination shortcode.
		global $last_max_num_pages;

		$defaults = static::get_defaults();

		$this->atts = $this->sanitize_attrs( $atts, $defaults, static::get_name() );

		/**
		 * Insert WP_Query variables
		 */
		$this->replace_query_vars();

		$args = $this->sanitize_query_args();

		ob_start();

		$query              = new \WP_Query( $args );
		$last_max_num_pages = $query->max_num_pages;

		if ( $query->have_posts() ) {

			$container_classes = $this->get_container_classes();

			echo $this->get_container_start( $container_classes );

			while ( $query->have_posts() ) {
				$query->the_post();

				$tempalte_dir = apply_filters( 'wp-simple-query-template_dir', 'template-parts' );
				$this->get_queried_template( $tempalte_dir . '/content', $this->atts['template'], array(
					'post_type' => $args['post_type'],
					'query'     => $args,
					'columns'   => $this->atts['columns'],
				) );
			}

			echo $this->get_container_end( $container_classes );
		} else {
			if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
				echo "<h4>Режим отладки:</h4>";
				echo 'Не найдено записей по данному запросу<hr>';

				var_dump( $args );

				echo '<hr>';
				echo "template: {$this->atts['template']}<br>";
				echo "container: {$this->atts['container']}<br>";
				echo "columns: {$this->atts['columns']}<br>";
			}
		}

		/**
		 * Return original vars
		 */
		$this->reset_query_vars();

		return ob_get_clean();
	}

	function pagination( $args = array() ) {
		global $last_max_num_pages;

		$last_max_num_pages = $last_max_num_pages ? intval( $last_max_num_pages ) : 1;

		$args = shortcode_atts( array(
			'show_all'  => false,
			'end_size'  => 1,
			'mid_size'  => 1,
			'prev_next' => true,
			'prev_text' => '« Пред.',
			'next_text' => 'След. »',
			'add_args'  => false,
			'total'     => $last_max_num_pages
		), $args, 'posts_pagination' );

		/**
		 * get_the_posts_pagination() wp-include/link-template.php:2656
		 */
		$navigation = '';

		// Don't print empty markup if there's only one page.
		if ( $last_max_num_pages > 1 ) {
			$args = wp_parse_args(
				$args,
				array(
					'mid_size'           => 1,
					'prev_text'          => _x( 'Previous', 'previous set of posts' ),
					'next_text'          => _x( 'Next', 'next set of posts' ),
					'screen_reader_text' => __( 'Posts navigation' ),
				)
			);

			// Make sure we get a string back. Plain is the next best thing.
			if ( isset( $args['type'] ) && 'array' == $args['type'] ) {
				$args['type'] = 'plain';
			}

			// Set up paginated links.
			$links = paginate_links( $args );

			if ( $links ) {
				$navigation = _navigation_markup( $links, 'pagination', $args['screen_reader_text'] );
			}
		}

		return $navigation;
	}


	/******************************* Build DOM ********************************/
	private function get_queried_template( $template, $slug = '', $template_args = array() ) {
		extract( $template_args );

		if ( $slug ) {
			if ( 'product' === $this->atts['type'] ) {
				if ( ! $this->is_custom_template ) {
					$templates[] = "woocommerce/content-$slug-query.php";
				}

				$templates[] = "woocommerce/content-$slug.php";
			}

			if ( ! $this->is_custom_template ) {
				$templates[] = "$template-$slug-query.php";
			}

			$templates[] = "$template-$slug.php";
		}

		if ( ! $this->is_custom_template ) {
			$templates[] = "$template-query.php";
		}

		$templates[] = "$template.php";

		if ( $r = locate_template( $templates ) ) {
			require $r;

			return true;
		}

		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			echo "<pre>";
			echo "Шаблон не найден по адресу: <br>" . get_template_directory() . '/<br>';
			print_r( $templates );
			echo "</pre>";
		}

		return false;
	}

	private function get_container_classes() {
		$classes   = array();
		$classes[] = $this->atts['container'];
		$classes[] = 'custom-query';

		if ( 'product' === $this->atts['type'] ) {
			$classes[] = 'products';
		}

		return apply_filters( 'wp-simple-queries-container-class', $classes );
	}

	private function get_container_start( $classes = array() ) {
		$result = '';
		// do not display for empty container class.
		if ( empty( $this->atts['container'] ) ) {
			return $result;
		}

		if ( 'product' === $this->atts['type'] ) {
			// add woocommerce class for stylesheets.
			$result .= '<section class="woocommerce">';
		}

		$result .= sprintf( ' <%s class="%s">',
			esc_attr( $this->atts['wrap_tag'] ),
			implode( ' ', $classes )
		);

		if ( $this->is_row( $this->atts['container'] ) && 'ul' !== $this->atts['wrap_tag'] ) {
			$result .= '<div class="row">';
		}
	}

	private function get_container_end( $classes = array() ) {
		$result = '';
		// do not display for empty container class.
		if ( empty( $this->atts['container'] ) ) {
			return $result;
		}

		if ( $this->is_row( $this->atts['container'] ) && 'ul' !== $this->atts['wrap_tag'] ) {
			$result .= '</div><!-- .row -->';
		}

		$result .= sprintf( '</%s><!-- .%s -->',
			esc_attr( $this->atts['wrap_tag'] ),
			implode( '.', $classes )
		);

		if ( 'product' === $this->atts['type'] ) {
			$result .= '</section>';
		}
	}

	/************************* WP_Query manipulations *************************/
	private function replace_query_vars() {
		global $wp_query;

		foreach ( get_object_vars( $wp_query ) as $key => $value ) {
			$this->orign_query_vars[ $key ] = $value;
		}

		if ( 1 != $this->atts['max'] ) {
			$wp_query->is_singular = false;
		}
		if ( 'page' !== $this->atts['type'] ) {
			$wp_query->is_page = false;
		}
	}

	private function reset_query_vars() {
		global $wp_query;

		foreach ( $this->orign_query_vars as $key => $value ) {
			if ( property_exists( $wp_query, $key ) ) {
				$wp_query->$key = $value;
			}
		}

		wp_reset_postdata();
	}

	/********************************* TinyMCE ********************************/
	static function init_mce_plugin() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		add_filter( "mce_external_plugins", function ( $plugin_array ) {
			$plugin_array['query_shortcode'] = Utils::get_plugin_url( '/admin/assets/posts-query-button.js' );

			return $plugin_array;
		} );

		add_filter( "mce_buttons", function ( $buttons ) {
			$buttons[] = 'query_shortcode';

			return $buttons;
		} );
	}

	static function enqueue_mce_script() {
		if ( ! isset( get_current_screen()->id ) || get_current_screen()->base != 'post' ) {
			return;
		}

		wp_enqueue_script( 'query_shortcode', Utils::get_plugin_url( '/admin/assets/posts-query-shortcode.js' ),
			array( 'shortcode', 'wp-util', 'jquery' ), false, true );
		wp_localize_script( 'query_shortcode',
			'queryPosts',
			array(
				'nonce'      => '',
				'shortcode'  => self::__shortcodeName(),
				'types'      => Utils::get_post_type_list(),
				'categories' => '',
				'pages'      => '',
				'taxonomies' => '',
				'terms'      => '',
				'statuses'   => Utils::get_status_list(),
				'orderby'    => Utils::get_order_by_postlist(),
			)
		);
	}
}
