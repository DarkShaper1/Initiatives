<?php

$Manager = new Rcl_Fields_Manager( 'orderform', array(
	'option_name'   => 'rcl_cart_fields',
	'empty_field'   => true,
	'field_options' => array(
		array(
			'type'  => 'textarea',
			'slug'  => 'notice',
			'title' => esc_html__( 'field description', 'wp-recall' )
		),
		array(
			'type'   => 'radio',
			'slug'   => 'required',
			'title'  => esc_html__( 'required field', 'wp-recall' ),
			'values' => array(
				esc_html__( 'No', 'wp-recall' ),
				esc_html__( 'Yes', 'wp-recall'
				)
			)
		)
	)
) );

$content = '<h2>' . esc_html__( 'Fields Manager of order form', 'wp-recall' ) . '</h2>';

$content .= $Manager->get_manager();

echo wp_kses( $content, rcl_kses_allowed_html() );