<?php

namespace NikolayS93\Queries\Creational;

if ( ! defined( 'ABSPATH' ) )
    exit; // disable direct access

abstract class Shortcode
{
    /**
     * @var array Sanitized shortcode atts
     */
    protected $atts = array();

    /**
     * @return string Get shortcode name default + filter
     */
    abstract function get_name();

    /**
     * @return array Shortcode atts default properties for escape waste
     */
    abstract function get_defaults();

    /**
     * Fix typos, fix var's types, fix them appropriately
     *
     * @param  array $atts
     * @return ...
     */
    abstract function sanitize_attrs( $atts );

    /**
     * Register shortcode function
     *
     * @param  array  $atts     [description]
     * @return [type]           [description]
     */
    abstract function build( $atts = array() );
}
