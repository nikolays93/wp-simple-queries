<?php

namespace NikolayS93\Queries;

$taxes      = array();
$taxonomies = get_taxonomies( array( 'public' => true ), 'objects' );
foreach ( $taxonomies as $tax_slug => $tax ) {
	$taxes[ $tax_slug ] = $tax->label;
}

/** Exclude WC staff */
unset( $taxes['product_shipping_class'] );

$data = array(
	array(
		'field_id'    => 'title',
		'id'          => $args['widget']->get_field_id( 'title' ),
		'name'        => $args['widget']->get_field_name( 'title' ),
		'type'        => 'text',
		'label'       => __( 'Title:', DOMAIN ),
		'input_class' => 'widefat',
	),
	array(
		'field_id'    => 'taxonomy',
		'id'          => $args['widget']->get_field_id( 'taxonomy' ),
		'name'        => $args['widget']->get_field_name( 'taxonomy' ),
		'default'     => 'category',
		'type'        => 'select',
		'options'     => $taxes,
		'label'       => __( 'taxonomy:', DOMAIN ),
		'input_class' => 'widefat',
	),
	array(
		'field_id'    => 'list_style',
		'id'          => $args['widget']->get_field_id( 'list_style' ),
		'name'        => $args['widget']->get_field_name( 'list_style' ),
		'type'        => 'select',
		'options'     => array(
			'div' => __( 'Blocks (div)' ),
			'ul'  => __( 'Unordered List (ul)' ),
			'ol'  => __( 'Ordered List (ol)' ),
		),
		'label'       => __( 'List Format:', DOMAIN ),
		'input_class' => 'widefat',
	),
);

return $data;
