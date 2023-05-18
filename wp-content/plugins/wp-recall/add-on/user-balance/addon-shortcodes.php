<?php

add_shortcode( 'rcl-pay-form', 'rcl_get_pay_form' );
function rcl_get_pay_form( $args ) {
	global $user_ID;

	if ( ! $user_ID ) {
		return rcl_get_notice( [
			'text' => apply_filters( 'rcl_pay_form_guest_notice', __( 'You need to login to make a payment', 'wp-recall' ) )
		] );
	}

	$args = wp_parse_args( $args, array(
		'pay_summ'     => 0,
		'pre_form'     => 1,
		'ids'          => '',
		'ids__not_in'  => '',
		'submit_value' => '',
		'description'  => '',
		'pay_type'     => 'any',
		'amount_type'  => 'number',
		'amount_min'   => 1,
		'amount_max'   => false,
		'amount_step'  => 1,
		'default'      => 1,
		'icon'         => 1
	) );

	$gateWays = new Rcl_Payment_Form( $args );

	if ( $args['pay_summ'] ) {
		return $gateWays->get_form();
	} else {
		return $gateWays->get_custom_amount_form();
	}
}

add_shortcode( 'rcl-form-balance', 'rcl_form_user_balance' );
function rcl_form_user_balance( $args = array() ) {
	global $user_ID;

	if ( ! $user_ID ) {
		return rcl_get_notice( [
			'text' => apply_filters( 'rcl_pay_form_guest_notice', __( 'You need to login to make a payment', 'wp-recall' ) )
		] );
	}

	$gateWays = new Rcl_Payment_Form( apply_filters( 'rcl_user_balance_form_args', wp_parse_args( $args, array(
		'ids__not_in' => 'user_balance',
		'pay_type'    => 'user-balance',
		'description' => __( 'Adding funds to your personal account', 'wp-recall' ) . ' ' . get_the_author_meta( 'user_email', $user_ID )
	) ) ) );

	if ( ! count( $gateWays->gateways ) ) {
		return rcl_get_notice( [
			'type' => 'error',
			'text' => __( 'Perhaps you haven`t configured any connect to a payment system yet', 'wp-recall' )
		] );
	}

	return $gateWays->get_custom_amount_form();
}

add_shortcode( 'rcl-usercount', 'rcl_shortcode_usercount' );
function rcl_shortcode_usercount() {
	return rcl_get_html_usercount();
}
