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
class Rcl_Field_Checkbox extends Rcl_Field_Abstract {

	public $required;
	public $values;
	public $display = 'inline';
	public $value_in_key;
	public $check_all = false;

	function __construct( $args ) {
		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'    => 'values',
				'default' => $this->values,
				'type'    => 'dynamic',
				'title'   => __( 'Specify options', 'wp-recall' ),
				'notice'  => __( 'specify each option in a separate field', 'wp-recall' )
			)
		);
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		return implode( ', ', $this->value );
	}

	function get_filter_value() {

		$links = array();

		foreach ( $this->value as $val ) {

			if ( ! $val ) {
				continue;
			}

			$links[] = '<a href="' . $this->get_filter_url( $val ) . '" target="_blank">' . $val . '</a>';
		}

		return implode( ', ', $links );
	}

	function get_input() {

		if ( ! $this->values ) {
			return false;
		}

		$currentValues = ( is_array( $this->value ) ) ? $this->value : array();

		$this->class = ( $this->required ) ? 'required-checkbox' : '';

		$content = '';

		if ( $this->check_all ) {

			$content .= '<div class="checkbox-manager">';

			$content .= rcl_get_button( array(
				'label'   => __( 'To mark all', 'wp-recall' ),
				'onclick' => 'return rcl_check_all_actions_manager("' . $this->input_name . '[]",this);return false;',
			) );

			$content .= rcl_get_button( array(
				'label'   => __( 'To delete all marks', 'wp-recall' ),
				'onclick' => 'return rcl_uncheck_all_actions_manager("' . $this->input_name . '[]",this);return false;',
			) );

			$content .= '</div>';
		}

		foreach ( $this->values as $k => $value ) {

			if ( $this->value_in_key ) {
				$k = $value;
			}

			$checked = checked( in_array( $k, $currentValues ), true, false );

			$content .= '<span class="rcl-checkbox-box checkbox-display-' . $this->display . '">';
			$content .= '<input ' . $this->get_required() . ' ' . $checked . ' id="' . $this->input_id . '_' . $k . $this->rand . '" type="checkbox" ' . $this->get_class() . ' name="' . $this->input_name . '[]" value="' . trim( $k ) . '"> ';
			$content .= '<label class="block-label" for="' . $this->input_id . '_' . $k . $this->rand . '">';
			$content .= $value;
			$content .= '</label>';
			$content .= '</span>';
		}

		return $content;
	}

	function get_field_input() {
		$content  = parent::get_field_input();
		$function = 'rcl_init_update_requared_checkbox();';
		if ( ! rcl_is_ajax() ) {
			$content .= '<script>jQuery(window).on("load", function() {' . $function . '});</script>';
		} else {
			$content .= '<script>' . $function . '</script>';
		}

		return $content;
	}

}
