<?php

class Rcl_Payment_Process extends Rcl_Payment_Core {

	public $post_id = 0;
	public $ids = array();

	function __construct() {
		global $post;

		parent::__construct();

		$this->post_id = $post && isset( $post->ID ) ? $post->ID : 0;

		$this->ids = rcl_get_commerce_option( 'payment_gateways', rcl_get_commerce_option( 'connect_sale' ) );

		if ( ! is_array( $this->ids ) ) {
			$this->ids = array_map( 'trim', explode( ',', $this->ids ) );
		}
	}

	function get_id_is_payment() {

		if ( ! rcl_gateways()->gateways ) {
			return false;
		}

		foreach ( rcl_gateways()->gateways as $id => $className ) {

			if ( ! in_array( $id, $this->ids ) ) {
				continue;
			}

			if ( isset( $_REQUEST[ rcl_gateways()->gateway( $id )->request ] ) ) {
				return $id;
			}
		}

		return false;
	}

	function payment_process( $gateway_id ) {

		switch ( $this->post_id ) {
			case $this->page_result:
				do_action( 'rcl_pre_payment_result', $gateway_id, $this );
				rcl_gateways()->gateway( $gateway_id )->result( $this );
				break;
			case $this->page_success:
				do_action( 'rcl_pre_payment_success', $gateway_id, $this );
				rcl_gateways()->gateway( $gateway_id )->success( $this );
				break;
		}
	}

}
