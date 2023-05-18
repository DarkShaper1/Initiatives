<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-rcl-custom-field-text
 *
 * @author Андрей
 */
class Rcl_Field_Runner extends Rcl_Field_Abstract {

	public $value_min = 0;
	public $value_max = 100;
	public $value_step = 1;
	public $unit;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'        => 'unit',
				'default'     => $this->unit,
				'placeholder' => __( 'For example: km or pcs', 'wp-recall' ),
				'type'        => 'text',
				'title'       => __( 'Unit', 'wp-recall' )
			),
			array(
				'slug'    => 'value_min',
				'default' => $this->value_min,
				'type'    => 'number',
				'title'   => __( 'Min', 'wp-recall' ),
			),
			array(
				'slug'    => 'value_max',
				'default' => $this->value_max,
				'type'    => 'number',
				'title'   => __( 'Max', 'wp-recall' ),
			),
			array(
				'slug'    => 'value_step',
				'default' => $this->value_step,
				'type'    => 'number',
				'title'   => __( 'Step', 'wp-recall' ),
			)
		);
	}

	function get_input() {

		rcl_slider_scripts();

		$content = '<div id="rcl-runner-' . $this->rand . '" class="rcl-runner rcl-runner-' . $this->rand . '">';

		$content .= '<span class="rcl-runner-value"><span></span>';
		if ( $this->unit ) {
			$content .= ' ' . $this->unit;
		}
		$content .= '</span>';

		$content .= '<div class="rcl-runner-box"></div>';
		$content .= '<input type="hidden" class="rcl-runner-field" id="' . $this->input_id . '" data-idrand="' . $this->rand . '" name="' . $this->input_name . '" value="' . $this->value_min . '">';
		$content .= '</div>';

		$init = 'rcl_init_runner(' . json_encode( array(
				'id'    => $this->rand,
				'value' => $this->value ? $this->value : 0,
				'min'   => $this->value_min,
				'max'   => $this->value_max,
				'step'  => $this->value_step
			) ) . ');';

		if ( ! rcl_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $init . '});</script>';
		} else {
			$content .= '<script>' . $init . '</script>';
		}

		return $content;
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		if ( $this->unit ) {
			$this->value .= ' ' . $this->unit;
		}

		return $this->value;
	}

	function get_filter_value() {
		$value = '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';

		if ( $this->unit ) {
			$value .= ' ' . $this->unit;
		}

		return $value;
	}

}
