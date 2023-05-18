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
class Rcl_Field_Hidden extends Rcl_Field_Abstract {

	public $required;
	public $values = array();
	public $placeholder;
	public $maxlength;
	public $pattern;
	public $class;

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
			),
			array(
				'slug'    => 'maxlength',
				'default' => $this->maxlength,
				'type'    => 'number',
				'title'   => __( 'Maxlength', 'wp-recall' ),
				'notice'  => __( 'maximum number of symbols per field', 'wp-recall' )
			),
			array(
				'slug'    => 'pattern',
				'default' => $this->pattern,
				'type'    => 'text',
				'title'   => __( 'Pattern', 'wp-recall' )
			)
		);
	}

	function get_field_input() {
		return $this->get_input();
	}

	function get_field_html( $args = false ) {
		return $this->get_field_input();
	}

	function get_input() {

		if ( $this->values && is_array( $this->values ) ) {

			$content = '';
			foreach ( $this->values as $value ) {
				$content .= '<input type="' . $this->type . '" ' . $this->get_class() . ' name="' . $this->input_name . '[]" value=\'' . $value . '\'/>';
			}

			return $content;
		}

		return '<input type="' . $this->type . '" ' . $this->get_pattern() . ' ' . $this->get_maxlength() . ' ' . $this->get_required() . ' ' . $this->get_placeholder() . ' ' . $this->get_class() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value=\'' . $this->value . '\'/>';
	}

}
