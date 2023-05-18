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
class Rcl_Field_Date extends Rcl_Field_Abstract {

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

		rcl_datepicker_scripts();

		$this->classes = 'rcl-datepicker';

		return '<input type="text" ' . $this->get_class() . ' autocomplete="off" onclick="rcl_show_datepicker(this);" title="' . __( 'Use the format', 'wp-recall' ) . ': yyyy-mm-dd" pattern="(\d{4}-\d{2}-\d{2})" ' . $this->get_required() . ' ' . $this->get_placeholder() . ' name="' . $this->input_name . '" id="' . $this->input_id . '" value="' . $this->value . '"/>';
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
	}

}
