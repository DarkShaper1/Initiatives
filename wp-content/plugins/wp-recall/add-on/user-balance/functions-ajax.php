<?php

/* * ***********************************************
  Пополнение личного счета пользователя
 * *********************************************** */
rcl_ajax_action( 'rcl_add_count_user', false );
function rcl_add_count_user() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	if ( empty( $_POST['pay_summ'] ) ) {
		wp_send_json( array( 'error' => esc_html__( 'Enter the amount', 'wp-recall' ) ) );
	}

	if ( $user_ID ) {

		$pay_summ      = abs( floatval( $_POST['pay_summ'] ) );
		$pay_type      = ( isset( $_POST['pay_type'] ) ) ? sanitize_text_field( wp_unslash( $_POST['pay_type'] ) ) : 'user-balance';
		$description   = ( isset( $_POST['description'] ) ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
		$merchant_icon = ( isset( $_POST['merchant_icon'] ) ) ? intval( $_POST['merchant_icon'] ) : 1;
		$submit_value  = ( isset( $_POST['submit_value'] ) ) ? sanitize_text_field( wp_unslash( $_POST['submit_value'] ) ) : esc_html__( 'Make payment', 'wp-recall' );

		$args = array(
			'pay_summ'           => $pay_summ,
			'pay_type'           => $pay_type,
			'description'        => $description,
			'merchant_icon'      => $merchant_icon,
			'submit_value'       => $submit_value,
			'pay_systems_not_in' => array( 'user_balance' ),
		);

		$args = apply_filters( 'rcl_ajax_pay_form_args', $args );

		$log['redirectform'] = rcl_get_pay_form( $args );
		$log['otvet']        = 100;
	} else {

		$log['error'] = esc_html__( 'Error', 'wp-recall' );
	}

	wp_send_json( $log );
}

rcl_ajax_action( 'rcl_pay_order_user_balance', false );
function rcl_pay_order_user_balance() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$pay_id       = isset( $_POST['pay_id'] ) ? intval( $_POST['pay_id'] ) : 0;
	$pay_type     = isset( $_POST['pay_type'] ) ? sanitize_text_field( wp_unslash( $_POST['pay_type'] ) ) : '';
	$pay_summ     = isset( $_POST['pay_summ'] ) ? abs( floatval( $_POST['pay_summ'] ) ) : 0;
	$description  = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
	$baggage_data = isset( $_POST['baggage_data'] ) ? (object) rcl_recursive_map( 'sanitize_text_field', (array) json_decode( base64_decode( wp_unslash( $_POST['baggage_data'] ) ) ) ) : [];

	if ( ! $pay_summ ) {
		wp_send_json( array( 'error' => __( 'Error', 'wp-recall' ) ) );
	}
	if ( ! $pay_id ) {
		wp_send_json( array( 'error' => __( 'Order not found!', 'wp-recall' ) ) );
	}

	$data = array(
		'user_id'         => $user_ID,
		'pay_type'        => $pay_type,
		'pay_id'          => $pay_id,
		'pay_summ'        => $pay_summ,
		'current_connect' => 'user_balance',
		'baggage_data'    => $baggage_data
	);

	do_action( 'rcl_pre_pay_balance', ( object ) $data );

	$userBalance = rcl_get_user_balance();

	$newBalance = $userBalance - $pay_summ;

	if ( ! $userBalance || $newBalance < 0 ) {
		wp_send_json( array( 'error' => sprintf( __( 'Insufficient funds in your personal account!<br>Order price: %d %s', 'wp-recall' ), $pay_summ, rcl_get_primary_currency( 1 ) ) ) );
	}

	rcl_update_user_balance( $newBalance, $user_ID, $description );

	do_action( 'rcl_success_pay_balance', ( object ) $data );

	wp_send_json( array(
		'redirect' => rcl_format_url( get_permalink( rcl_get_commerce_option( 'page_successfully_pay' ) ) ) . 'payment-type=' . $pay_type
	) );
}

rcl_ajax_action( 'rcl_load_payment_form' );
function rcl_load_payment_form() {

	$form = rcl_get_pay_form( array(
		'pay_summ'    => isset( $_POST['pay_summ'] ) ? abs( floatval( $_POST['pay_summ'] ) ) : 0,
		'ids'         => isset( $_POST['gateway_id'] ) ? [ sanitize_text_field( wp_unslash( $_POST['gateway_id'] ) ) ] : [],
		'pay_type'    => isset( $_POST['pay_type'] ) ? sanitize_text_field( wp_unslash( $_POST['pay_type'] ) ) : '',
		'description' => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '',
		'pre_form'    => isset( $_POST['pre_form'] ) ? intval( $_POST['pre_form'] ) : 0,
	) );

	$content = '<div class="rcl-pre-payment-data">';

	$content .= rcl_get_notice( [
		'text' => '<b>' . esc_html__( 'The sum of payment', 'wp-recall' ) . '</b>: ' . abs( floatval( $_POST['pay_summ'] ) ) . ' ' . rcl_get_primary_currency( 1 )
	] );

	$content .= $form;

	$content .= '</div>';

	wp_send_json( array(
		'dialog'   => array(
			'content' => $content,
			'size'    => 'medium',
			'title'   => isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : ''
		),
		'pay_type' => isset( $_POST['pay_type'] ) ? sanitize_text_field( wp_unslash( $_POST['pay_type'] ) ) : ''
	) );
}
