<?php
namespace CDevelopers\Query\Terms;

if ( ! defined( 'ABSPATH' ) )
    exit; // disable direct access

$data = array();
$data[] = array(
    'field_id'    => 'show_thumb',
    'id'          => $args['widget']->get_field_id( 'show_thumb' ),
    'name'        => $args['widget']->get_field_name( 'show_thumb' ),
    'type'        => 'checkbox',
    'label'       => __('Display Thumbnail', DOMAIN),
);

if( $sizes = false ) {
    $data[] = array(
        'field_id'     => 'thumb_size',
        'id'          => $args['widget']->get_field_id( 'thumb_size' ),
        'name'        => $args['widget']->get_field_name( 'thumb_size' ),
        'type'        => 'select',
        'options'     => $sizes,
        'label'       => __('Custom sizes:', DOMAIN),
        'input_class' => 'widefat',
    );
    $data[] = array(
        'id' => 'thumb_size_label',
        'type' => 'html',
        'value' => __('Set Custom Size:') . "<br>\n",
    );
}
$data[] = array(
    'field_id'     => 'thumb_size_w',
    'id'          => $args['widget']->get_field_id( 'thumb_size_w' ),
    'name'        => $args['widget']->get_field_name( 'thumb_size_w' ),
    'default'     => 55,
    'type'        => 'number',
    'label'       => __('Width:', DOMAIN),
    'input_class' => 'small-text',
);

$data[] = array(
    'field_id'     => 'thumb_size_h',
    'id'          => $args['widget']->get_field_id( 'thumb_size_h' ),
    'name'        => $args['widget']->get_field_name( 'thumb_size_h' ),
    'default'     => 55,
    'type'        => 'number',
    'label'       => __('Height:', DOMAIN),
    'input_class' => 'small-text',
);

$data[] = array(
    'id' => 'thumbnail-preview',
    'type' => 'html',
    'value' => __('Preview Custom Size:') . '<br>
    <span class="widget-panel-thumbnail-preview" style="font-size: 55px; height: 55px; width: 55px">
    <i class="widget-panel-preview-image dashicons dashicons-format-image"></i>
    </span>',
);

return $data;
