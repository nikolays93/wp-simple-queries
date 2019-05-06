<?php

namespace NikolayS93\Queries\Types;

if ( ! defined( 'ABSPATH' ) )
    exit; // disable direct access

abstract class Shortcode
{
    /**
     * @var array Sanitized shortcode atts
     */
    protected $atts = array();

    /**
     * @return array Shortcode atts default properties for escape waste
     */
    abstract function __defaults();

    /**
     * @return string Get shortcode name default + filter
     */
    abstract static function __shortcodeName();

    /**
     * Fix typos, fix var's types, fix them appropriately
     * @param  array $atts
     * @return ...
     */
    abstract function sanitizeShortcodeAtts( $atts );

    /**
     * Register shortcode function
     * @param  array  $atts     [description]
     * @return [type]           [description]
     */
    abstract function shortcode( $atts = array() );
}