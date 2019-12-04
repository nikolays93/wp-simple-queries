<?php

namespace NikolayS93\Queries\Creational;

abstract class Shortcode {
	/**
	 * @var array Sanitized shortcode atts
	 */
	private $atts = array();

	/**
	 * @return string Get shortcode name default + filter
	 */
	abstract public static function get_name();

	/**
	 * @return array Shortcode atts default properties for escape waste
	 */
	abstract public static function get_defaults();

	/**
	 * Fix typos, fix var's types, fix them appropriately
	 *
	 * @param array $atts
	 *
	 * @return ...
	 */
	abstract protected function sanitize_attrs( $atts );

	abstract protected function sanitize_query_args();

	/**
	 * Register shortcode function
	 *
	 * @param array $atts [description]
	 *
	 * @return [type]           [description]
	 */
	abstract public function build( $atts = array() );
}
