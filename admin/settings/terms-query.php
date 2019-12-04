<?php

namespace NikolayS93\Queries;

$data = array(
	array(
		'field_id'    => 'orderby',
		'id'          => $args['widget']->get_field_id( 'orderby' ),
		'name'        => $args['widget']->get_field_name( 'orderby' ),
		'type'        => 'select',
		'options'     => array(
			'id'    => __( 'ID', DOMAIN ),
			'name'  => __( 'Category Name', DOMAIN ),
			'count' => __( 'Post Count', DOMAIN ),
		),
		'label'       => __( 'Order by:', DOMAIN ),
		'input_class' => 'widefat',
	),
	array(
		'field_id'    => 'order',
		'id'          => $args['widget']->get_field_id( 'order' ),
		'name'        => $args['widget']->get_field_name( 'order' ),
		'type'        => 'select',
		'options'     => array(
			'desc' => __( 'Descending', DOMAIN ),
			'asc'  => __( 'Ascending', DOMAIN ),
		),
		'label'       => __( 'Order:', DOMAIN ),
		'input_class' => 'widefat',
	),
	array(
		'field_id' => 'show_empty',
		'id'       => $args['widget']->get_field_id( 'show_empty' ),
		'name'     => $args['widget']->get_field_name( 'show_empty' ),
		'type'     => 'checkbox',
		'label'    => __( 'Show empty terms', DOMAIN ),
	),
	array(
		'field_id'    => 'number',
		'id'          => $args['widget']->get_field_id( 'number' ),
		'name'        => $args['widget']->get_field_name( 'number' ),
		'type'        => 'number',
		'label'       => __( 'Max terms:', DOMAIN ),
		'input_class' => 'small-text',
		'desc'        => 'set -1 for no limited',
	),
);

return $data;
