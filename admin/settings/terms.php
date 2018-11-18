<?php

namespace NikolayS93\Queries;

$terms = array();
$data = array();

if( empty($args['instance']['taxonomy']) ) {
    return $data;
}

$terms = get_terms( array(
    'taxonomy' => $args['instance']['taxonomy'],
    'hide_empty' => false,
) );

foreach ($terms as $term) {
    $data[] = array(
        'type'     => 'checkbox',
        'field_id' => 'show_empty',
        'id'       => $args['widget']->get_field_id( 'exclude-' .$args['instance']['taxonomy'] . '-' . $term->term_id),
        // '['.$args['instance']['taxonomy'].']['
        // {$term->term_id}
        'name'     => $args['widget']->get_field_name( 'exclude' ) . "[]",
        'label'    => __( "{$term->name} ({$term->count})", DOMAIN ),
        'value'    => $term->term_id
        );
}

// $checked = (  ! empty( $args['instance']['exclude'][$term->taxonomy][$term->term_id] )) ? 'checked="checked"' : '' ;
return $data;
