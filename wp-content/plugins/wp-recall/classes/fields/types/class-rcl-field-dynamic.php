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
class Rcl_Field_Dynamic extends Rcl_Field_Abstract {

	public $required;
	public $placeholder;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'    => 'placeholder',
				'default' => $this->placeholder,
				'type'    => 'text',
				'title'   => __( 'Placeholder', 'wp-recall' )
			)
		);
	}

	function get_input() {

		if ( ! $this->default ) {
			$this->default = '';
		}

		$content = '<span class="dynamic-values">';

		if ( $this->value && is_array( $this->value ) ) {
			$cnt = count( $this->value );
			foreach ( $this->value as $k => $val ) {

				$key = is_string( $k ) ? $k : '';

				$content .= '<span class="dynamic-value">';
				$content .= '<input type="text" ' . $this->get_required() . ' ' . $this->get_placeholder() . ' name="' . $this->input_name . '[' . $key . ']" value="' . $val . '"/>';
				if ( ! is_string( $k ) ) {
					if ( $cnt == ++ $k ) {
						$content .= '<a href="#" onclick="rcl_add_dynamic_field(this);return false;"><i class="rcli fa-plus" aria-hidden="true"></i></a>';
					} else {
						$content .= '<a href="#" onclick="rcl_remove_dynamic_field(this);return false;"><i class="rcli fa-minus" aria-hidden="true"></i></a>';
					}
				}
				$content .= '</span>';
			}
		} else {
			$content .= '<span class="dynamic-value">';
			$content .= '<input type="text" ' . $this->get_required() . ' ' . $this->get_placeholder() . ' name="' . $this->input_name . '[]" value="' . $this->default . '"/>';
			$content .= '<a href="#" onclick="rcl_add_dynamic_field(this);return false;"><i class="rcli fa-plus" aria-hidden="true"></i></a>';
			$content .= '</span>';
		}

		$content .= '</span>';

		return $content;
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		return implode( ', ', $this->value );
	}

}
