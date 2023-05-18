<?php

class Rcl_Payment_Core {

	public $page_result;
	public $page_success;
	public $page_fail;
	public $page_successfully;

	function __construct( $args = array() ) {

		if ( $args ) {
			$this->init_properties( $args );
		}

		if ( ! $this->page_result ) {
			$this->page_result = rcl_get_commerce_option( 'page_result_pay' );
		}

		if ( ! $this->page_success ) {
			$this->page_success = rcl_get_commerce_option( 'page_success_pay' );
		}

		if ( ! $this->page_fail ) {
			$this->page_fail = rcl_get_commerce_option( 'page_fail_pay' );
		}

		if ( ! $this->page_successfully ) {
			$this->page_successfully = rcl_get_commerce_option( 'page_successfully_pay' );
		}
	}

	function init_properties( $args ) {
		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

}
