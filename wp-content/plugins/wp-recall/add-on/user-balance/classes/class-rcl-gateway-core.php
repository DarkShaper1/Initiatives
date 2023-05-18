<?php

class Rcl_Gateway_Core {

	public $id;
	public $request;
	public $name;
	public $label;
	public $icon;
	public $submit;
	public $handle_activate = false;
	public $handle_options = false;
	public $handle_forms = false;

	function __construct( $args = false ) {

		$this->init_properties( $args );

		if ( ! $this->label ) {
			$this->label = $this->name;
		}

		if ( ! $this->submit ) {
			$this->submit = __( 'Pay', 'wp-recall' );
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

	function options_init() {
		add_filter( 'rcl_commerce_options', ( array( $this, 'add_gateway_options' ) ) );
	}

	function get_options() {
		return false;
	}

	function add_gateway_options( $optionsManager ) {

		if ( $this->handle_options || ! $options = $this->get_options() ) {
			return $optionsManager;
		}

		$optionsManager->add_box( $this->id, array(
			'title' => $this->name
		) )->add_group( $this->id, array(
			'title' => __( 'Settings', 'wp-recall' ) . ' ' . $this->name
		) )->add_options( $options );

		return $optionsManager;
	}

	function get_pre_form_fields() {
		return array();
	}

	function get_pre_form( $data ) {

		$preFields = $this->get_pre_form_fields();

		if ( ! $preFields ) {
			return false;
		}

		$preFields[] = [
			'slug'  => 'gateway_id',
			'type'  => 'hidden',
			'value' => $this->id
		];

		$preFields[] = [
			'slug'  => 'pay_summ',
			'type'  => 'hidden',
			'value' => $data->pay_summ
		];

		$preFields[] = [
			'slug'  => 'pay_type',
			'type'  => 'hidden',
			'value' => $data->pay_type
		];

		$preFields[] = [
			'slug'  => 'description',
			'type'  => 'hidden',
			'value' => $data->description
		];

		$preFields[] = [
			'slug'  => 'pre_form',
			'type'  => 'hidden',
			'value' => 0
		];

		return $this->construct_form( [
			'onclick' => 'rcl_send_form_data("rcl_load_payment_form",this);return false;',
			'fields'  => $preFields
		] );
	}

	function construct_form( $args ) {

		if ( ! isset( $args['submit'] ) ) {
			$args['submit'] = $this->submit;
		}

		$args['submit_args'] = [
			'size'      => 'medium',
			'fullwidth' => 1
		];

		if ( $args['fields'] ) {

			$fields = array();
			foreach ( $args['fields'] as $field_name => $value ) {

				if ( ! is_array( $value ) ) {
					$fields[] = array(
						'type'  => 'hidden',
						'slug'  => $field_name,
						'value' => $value
					);
				} else {
					$fields[] = $value;
				}
			}

			$args['fields'] = $fields;
		}

		return rcl_get_form( $args );
	}

	function result( $data ) {
		return false;
	}

	function success( $data ) {
		return false;
	}

	function get_payment( $pay_id ) {
		global $wpdb;
		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . RMAG_PREF . "pay_results WHERE payment_id = %s", $pay_id ) );
	}

	function insert_payment( $args ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'time_action' => current_time( 'mysql' ),
			'pay_system'  => $this->id
		) );

		$args['baggage_data'] = ( $args['baggage_data'] ) ? json_decode( base64_decode( $args['baggage_data'] ) ) : false;

		$args = apply_filters( 'rcl_pre_insert_payment_args', $args );

		$pay_status = $wpdb->insert( RMAG_PREF . 'pay_results', array(
				'payment_id'  => $args['pay_id'],
				'user_id'     => $args['user_id'],
				'pay_amount'  => $args['pay_summ'],
				'time_action' => $args['time_action'],
				'pay_system'  => $args['pay_system'],
				'pay_type'    => $args['pay_type']
			)
		);

		if ( ! $pay_status ) {

			rcl_add_log(
				'insert_pay: ' . __( 'Failed to add user payment', 'wp-recall' ), $args
			);

			exit;
		}

		$object = ( object ) $args;

		do_action( 'rcl_success_pay_system', $object );
	}

}
