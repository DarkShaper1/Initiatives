<?php

class Rcl_Payment_Form extends Rcl_Payment_Core {

	public $ids = array();
	public $ids__not_in;
	public $pay_systems = array(); //old
	public $pay_systems_not_in; //old
	public $gateways = array();
	public $pay_id;
	public $pay_type;
	public $user_id;
	public $pay_summ = 0;
	public $baggage_data = array();
	public $description;
	public $currency;
	public $submit_value;
	public $amount_type = 'number';
	public $amount_min = 1;
	public $amount_max = false;
	public $amount_step = 1;
	public $default = 1;
	public $icon = 1;
	public $pre_form = 1;
	public $return_url = '';
	public $recur_pay = 0;

	function __construct( $args = array() ) {
		global $user_ID;

		rcl_dialog_scripts();

		if ( isset( $args['pay_type'] ) && $args['pay_type'] == 1 ) {
			$args['pay_type'] = 'user-balance';
		}

		parent::__construct( $args );

		if ( $this->pay_systems ) {
			$this->ids = $this->pay_systems;
		}

		if ( $this->pay_systems_not_in ) {
			$this->ids__not_in = $this->pay_systems_not_in;
		}

		$connects = rcl_get_commerce_option( 'payment_gateways', rcl_get_commerce_option( 'connect_sale' ) );

		$checkSystems   = is_array( $connects ) ? $connects : array( $connects );
		$checkSystems[] = 'user_balance';

		if ( ! $this->ids ) {

			$this->ids = $checkSystems;
		} else {

			if ( ! is_array( $this->ids ) ) {
				$this->ids = array_map( 'trim', explode( ',', $this->ids ) );
			}

			foreach ( $this->ids as $k => $typeConnect ) {
				if ( ! in_array( $typeConnect, $checkSystems ) ) {
					unset( $this->ids[ $k ] );
				}
			}
		}

		if ( $this->ids__not_in ) {

			if ( ! is_array( $this->ids__not_in ) ) {
				$this->ids__not_in = array_map( 'trim', explode( ',', $this->ids__not_in ) );
			}

			foreach ( $this->ids as $k => $typeConnect ) {
				if ( in_array( $typeConnect, $this->ids__not_in ) ) {
					unset( $this->ids[ $k ] );
				}
			}
		}

		if ( ! $this->pay_id ) {
			$this->pay_id = current_time( 'timestamp' );
		}

		if ( ! $this->currency ) {
			$this->currency = rcl_get_commerce_option( 'primary_cur', 'RUB' );
		}

		if ( ! $this->user_id ) {
			$this->user_id = $user_ID;
		}

		if ( ! $this->description ) {
			$this->description = __( 'The payment from', 'wp-recall' ) . ' ' . get_the_author_meta( 'user_email', $this->user_id );
		}

		$this->pay_summ = rcl_commercial_round( str_replace( ',', '.', $this->pay_summ ) );

		$this->baggage_data['pay_type']   = $this->pay_type;
		$this->baggage_data['user_id']    = $this->user_id;
		$this->baggage_data['return_url'] = $this->return_url;

		$this->baggage_data = base64_encode( json_encode( $this->baggage_data ) );

		$this->setup_gateways();
	}

	function setup_gateways() {
		global $rcl_gateways;

		if ( ! $rcl_gateways ) {
			return false;
		}

		if ( ! $this->ids ) {
			$this->gateways = $rcl_gateways;
		} else {

			foreach ( $rcl_gateways->gateways as $id => $gateWay ) {

				if ( ! in_array( $id, $this->ids ) ) {
					continue;
				}

				$this->gateways[ $id ] = rcl_gateways()->gateway( $id );
			}
		}
	}

	function get_form() {

		if ( ! $this->gateways ) {
			return false;
		}

		$content = '<div class="rcl-payment-forms rcl-payment-buttons">';

		$styles = '';
		$k      = 0;
		foreach ( $this->gateways as $id => $gateway ) {

			if ( $gateway->handle_forms ) {
				continue;
			}

			$form = false;

			if ( $this->pre_form ) {
				$form = $gateway->get_pre_form( $this );
			}

			if ( ! $form ) {
				$form = $gateway->get_form( $this );
			}

			if ( ! $form ) {
				continue;
			}

			$content .= '<div class="rcl-payment-form rcl-payment-form-type-' . $this->pay_type . ' fixed-sum ' . ( $k ? '' : 'display-form' ) . '" data-gateway-id="' . $id . '">';
			$content .= '<div class="title-form" onclick="rcl_show_payment_form(\'' . $id . '\');return false;">' . $gateway->label . '<i class="rcli" aria-hidden="true"></i></div>';
			$content .= $form;
			$content .= '</div>';

			if ( $this->icon && $gateway->icon ) {
				$styles .= '.rcl-payment-form[data-gateway-id="' . $id . '"] .title-form{'
				           . 'background-image:url(' . $gateway->icon . ');'
				           . '}';
			}

			$k ++;
		}

		if ( $k == 1 ) {
			$styles .= '.rcl-payment-form.display-form .title-form .rcli:before {content: none;}';
		}

		$content .= '<style>' . $styles . '</style>';

		$content .= '</div>';

		return $content;
	}

	function get_custom_amount_form() {

		$fields = array(
			array(
				'type'        => $this->amount_type,
				'slug'        => 'pay_summ',
				'title'       => __( 'The sum of payment', 'wp-recall' ) . ', ' . rcl_get_primary_currency( 1 ),
				'required'    => true,
				'value_min'   => $this->amount_min,
				'value_max'   => $this->amount_type == 'runner' && ! $this->amount_max ? 100 : false,
				'value_step'  => $this->amount_step,
				'placeholder' => 0
			)
		);

		$styles = '';
		if ( $this->gateways ) {
			$values = array();

			foreach ( $this->gateways as $id => $gateway ) {

				if ( $gateway->handle_forms ) {
					continue;
				}

				$values[ $id ] = $gateway->label;

				if ( $gateway->icon ) {
					$styles .= '#rcl-field-gateway_id .rcl-radio-box[data-value="' . $id . '"] .block-label{'
					           . 'background-image:url(' . $gateway->icon . ');'
					           . '}';
				}
			}

			$keys    = array_keys( $values );
			$default = $keys[0];

			$fields[] = array(
				'type'    => 'radio',
				'slug'    => 'gateway_id',
				'display' => 'block',
				'title'   => __( 'The system of payment', 'wp-recall' ),
				'default' => $default,
				'values'  => $values
			);
		}

		$fields[] = array(
			'type'  => 'hidden',
			'slug'  => 'pay_type',
			'value' => $this->pay_type
		);

		$fields[] = array(
			'type'  => 'hidden',
			'slug'  => 'description',
			'value' => $this->description
		);

		$content = '<div class="rcl-payment-form rcl-payment-form-type-' . $this->pay_type . ' custom-sum">';

		$content .= rcl_get_form( array(
			'method'  => 'post',
			'fields'  => $fields,
			'submit'  => rcl_get_commerce_option( 'submit_choose', __( 'Continue' ) ),
			'onclick' => 'rcl_send_form_data("rcl_load_payment_form",this);return false;'
		) );

		$content .= '<div class="rcl-payment-form-content"></div>';
		$content .= '<style>' . $styles . '</style>';
		$content .= '</div>';

		return $content;
	}

}
