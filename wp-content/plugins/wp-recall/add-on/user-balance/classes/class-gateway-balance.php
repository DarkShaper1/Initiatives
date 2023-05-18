<?php

add_action( 'rcl_payments_gateway_init', 'rcl_gateway_balance_init', 10 );
function rcl_gateway_balance_init() {
	rcl_gateway_register( 'user_balance', 'Rcl_Gateway_Balance' );
}

class Rcl_Gateway_Balance extends Rcl_Gateway_Core {
	function __construct() {

		parent::__construct( array(
			'name'   => 'Лицевой счет',
			'label'  => rcl_get_commerce_option( 'balance_label', __( 'The personal account', 'wp-recall' ) ),
			'submit' => rcl_get_commerce_option( 'balance_submit', __( 'Payment with the personal account', 'wp-recall' ) ),
			'icon'   => rcl_addon_url( 'assets/img/wallet.jpg', __FILE__ )
		) );
	}

	function get_options() {

		return array(
			array(
				'type'        => 'text',
				'slug'        => 'balance_label',
				'placeholder' => __( 'The personal account', 'wp-recall' ),
				'title'       => __( 'The title of a way of a payment', 'wp-recall' )
			),
			array(
				'type'        => 'text',
				'slug'        => 'balance_submit',
				'placeholder' => __( 'Payment with the personal account', 'wp-recall' ),
				'title'       => __( 'The caption on a button of a payment', 'wp-recall' )
			)
		);
	}

	function get_form( $data ) {
		global $user_ID;

		if ( ! $user_ID ) {
			return;
		}

		$fields = array(
			array(
				'slug'  => 'pay_summ',
				'type'  => 'hidden',
				'value' => $data->pay_summ
			),
			array(
				'slug'  => 'pay_id',
				'type'  => 'hidden',
				'value' => $data->pay_id
			),
			array(
				'slug'  => 'description',
				'type'  => 'hidden',
				'value' => $data->description
			),
			array(
				'slug'  => 'user_id',
				'type'  => 'hidden',
				'value' => $data->user_id
			),
			array(
				'slug'  => 'pay_type',
				'type'  => 'hidden',
				'value' => $data->pay_type
			),
			array(
				'slug'  => 'baggage_data',
				'type'  => 'hidden',
				'value' => $data->baggage_data
			)
		);

		return parent::construct_form( array(
			'method'  => 'post',
			'fields'  => $fields,
			'onclick' => 'rcl_send_form_data("rcl_pay_order_user_balance",this);return false;'
		) );
	}

}
