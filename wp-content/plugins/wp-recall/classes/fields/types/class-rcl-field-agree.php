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
class Rcl_Field_Agree extends Rcl_Field_Abstract {

	public $text_confirm;
	public $url_agreement;

	function __construct( $args ) {

		if ( isset( $args['text-confirm'] ) ) {
			$args['text_confirm'] = $args['text-confirm'];
		}

		if ( isset( $args['url-agreement'] ) ) {
			$args['url_agreement'] = $args['url-agreement'];
		}

		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'        => 'icon',
				'default'     => 'fa-check-square-o',
				'placeholder' => 'fa-check-square-o',
				'class'       => 'rcl-iconpicker',
				'type'        => 'text',
				'title'       => __( 'Icon class of  font-awesome', 'wp-recall' )
			),
			array(
				'slug'    => 'url_agreement',
				'default' => $this->url_agreement,
				'type'    => 'url',
				'title'   => __( 'Agreement URL', 'wp-recall' )
			),
			array(
				'slug'    => 'text_confirm',
				'default' => $this->text_confirm,
				'type'    => 'textarea',
				'title'   => __( 'Consent confirmation text', 'wp-recall' )
			)
		);
	}

	function get_title() {

		if ( ! $this->title ) {
			$this->title = __( 'Agreement', 'wp-recall' );
		}

		if ( $this->url_agreement ) {
			return '<a href="' . $this->url_agreement . '" target="_blank">' . $this->title . ( $this->required ? ' <span class="required">*</span>' : '' ) . '</a>';
		}

		return $this->title . ( $this->required ? ' <span class="required">*</span>' : '' );
	}

	function get_value() {

		if ( $this->value ) {
			return __( 'Accepted', 'wp-recall' );
		}

		return false;
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '" target="_blank">' . $this->value . '</a>';
	}

	function get_input() {

		$text = $this->text_confirm ? $this->text_confirm : __( 'I agree with the text of the agreement', 'wp-recall' );

		$input = '<span class="rcl-checkbox-box">';
		$input .= '<input type="checkbox" ' . checked( $this->value, 1, false ) . ' ' . $this->get_required() . ' name="' . $this->input_name . '" id="' . $this->input_id . $this->rand . '" value="1"/> ';
		$input .= '<label class="block-label" for="' . $this->input_id . $this->rand . '">' . $text . '</label>';
		$input .= '</span>';

		return $input;
	}

}
