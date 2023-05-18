<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Rcl_Custom_Field_Abstract
 *
 * @author Андрей
 */
class Rcl_Field_Abstract {

	public $id;
	public $slug;
	public $type;
	public $icon;
	public $title;
	public $value = null;
	public $default = null;
	public $notice;
	public $filter;
	public $input_id;
	public $input_name;
	public $parent;
	public $rand;
	public $help;
	public $class;
	public $required;
	public $maxlength;
	public $childrens;
	public $unique_id = false;
	public $value_in_key = null;
	public $must_delete = true;
	public $_new;

	function __construct( $args ) {

		if ( ! isset( $args['type'] ) ) {
			$args['type'] = 'custom';
		}

		if ( ! isset( $args['slug'] ) ) {
			if ( $args['type'] == 'custom' ) {
				$args['slug'] = md5( current_time( 'mysql' ) );
			} else {
				return false;
			}
		}

		if ( isset( $args['name'] ) ) {
			$args['input_name'] = $args['name'];
		}

		if ( isset( $args['req'] ) ) {
			$args['public_value'] = $args['req'];
		}

		$this->id = $args['slug'];

		$this->init_properties( $args );

		$this->rand = rand( 0, 1000 );

		if ( ! $this->input_name ) {
			$this->input_name = $this->id;
		}

		if ( ! $this->input_id ) {
			$this->input_id = $this->id;
		}

		if ( $this->unique_id ) {
			$this->input_id .= $this->rand;
		}
	}

	function get_options() {
		return array();
	}

	function init_properties( $args ) {

		foreach ( $args as $key => $val ) {
			$this->$key = $val;
		}

		if ( ! isset( $this->value ) && isset( $this->default ) ) {
			$this->value = $this->default;
		}
	}

	function get_prop( $propName ) {
		return $this->isset_prop( $propName ) ? $this->$propName : false;
	}

	function isset_prop( $propName ) {
		return isset( $this->$propName );
	}

	function set_prop( $propName, $value ) {
		$this->$propName = $value;
	}

	function get_title() {

		if ( ! $this->title ) {
			return false;
		}

		return '<span class="rcl-field-title">'
		       . $this->title . ( $this->required ? ' <span class="required">*</span>' : '' )
		       . '</span>';
	}

	function get_icon() {

		if ( ! $this->icon ) {
			return false;
		}

		$content = '<span class="rcl-field-icon">';
		$content .= '<i class="rcli ' . $this->icon . '" aria-hidden="true"></i> ';
		$content .= '</span>';

		return $content;
	}

	function get_notice() {

		if ( ! $this->notice ) {
			return false;
		}

		return '<span class="rcl-field-notice">'
		       . '<i class="rcli fa-info" aria-hidden="true"></i>'
		       . $this->notice
		       . '</span>';
	}

	function is_new() {
		return $this->_new;
	}

	function get_field_input() {

		if ( ! $this->type ) {
			return false;
		}

		$classes = array( 'type-' . $this->type . '-input' );

		$classes[] = 'rcl-field-input';

		$inputField = $this->get_input();

		if ( ! $this->title && $this->required ) {
			$inputField .= '<span class="required">*</span>';
		}

		if ( $this->maxlength ) {
			$inputField .= '<script>rcl_init_field_maxlength("' . $this->input_id . '");</script>';
		}

		return '<div id="rcl-field-' . $this->id . '" class="' . implode( ' ', $classes ) . '">'
		       . '<div class="rcl-field-core">'
		       . $inputField
		       . '</div>'
		       . $this->get_notice()
		       . '</div>';
	}

	function get_field_html( $args = false ) {

		if ( $this->type == 'hidden' ) {
			return $this->get_field_input();
		}

		$classes = array( 'rcl-field', 'type-' . $this->type . '-field' );

		if ( isset( $args['classes'] ) ) {
			$classes = array_merge( $classes, $args['classes'] );
		}

		if ( $this->childrens ) {
			$classes[] = 'rcl-parent-field';
		}

		if ( $this->parent ) {
			$classes[] = 'rcl-children-field';
		}

		$content = '<div class="' . implode( ' ', $classes ) . '" ' . ( $this->parent ? 'data-parent="' . esc_attr( $this->parent['id'] ) . '" data-parent-value="' . esc_attr( $this->parent['value'] ) . '"' : '' ) . '>';

		$content .= $this->get_title();

		$content .= $this->get_help();

		$content .= $this->get_field_input();

		$content .= '</div>';

		return $content;
	}

	function get_help() {

		if ( ! $this->help ) {
			return;
		}

		return '<span class="help-option" onclick="return rcl_get_option_help(this);"><i class="dashicons dashicons-editor-help"></i><span class="help-content">' . $this->help . '</span></span>';


		/* $content = '<span class="rcl-balloon-hover rcl-field-help">';
		  $content .= '<i class="rcli fa-question-circle-o" aria-hidden="true"></i>';
		  $content .= '<span class="rcl-balloon help-content">';
		  $content .= $this->help;
		  $content .= '</span>';
		  $content .= '</span>';

		  return $content; */
	}

	function get_childrens() {
		return $this->childrens;
	}

	function isset_childrens() {
		return $this->childrens ? true : false;
	}

	protected function get_required() {
		return $this->required ? 'required="required"' : '';
	}

	protected function get_placeholder() {
		return $this->placeholder !== '' ? 'placeholder="' . $this->placeholder . '"' : '';
	}

	protected function get_maxlength() {
		return $this->maxlength ? 'maxlength="' . $this->maxlength . '"' : '';
	}

	protected function get_pattern() {
		return $this->pattern ? 'pattern="' . $this->pattern . '"' : '';
	}

	protected function get_min() {
		return $this->value_min !== '' ? 'min="' . $this->value_min . '"' : '';
	}

	protected function get_max() {
		return $this->value_max !== '' ? 'max="' . $this->value_max . '"' : '';
	}

	protected function get_input_id() {
		return $this->input_id ? 'id="' . $this->input_id . '"' : '';
	}

	function get_class() {

		$class = array( $this->type . '-field' );

		if ( $this->class ) {
			$class[] = $this->class;
		}

		return 'class="' . implode( ' ', $class ) . '"';
	}

	function get_value() {

		if ( ! isset( $this->value ) || $this->value == '' ) {
			return false;
		}

		return $this->value;
	}

	function get_field_value( $title = false ) {

		$value = $this->get_value();

		if ( ! $value || ! $this->type ) {
			return false;
		}

		$content = '<div class="rcl-field type-' . $this->type . '-value">';

		//$content .= $this->get_icon();

		if ( $title ) {
			$content .= '<span class="rcl-field-title">'
			            . $this->title
			            . '</span>'
			            . '<span class="title-colon">: </span>';
		}

		$content .= '<span class="rcl-field-value">';

		$content .= $this->filter ? $this->get_filter_value() : $value;

		$content .= '</span>';

		$content .= '</div>';

		return $content;
	}

	function get_filter_value() {
		return '<a href="' . $this->get_filter_url() . '">' . $this->get_value() . '</a>';
	}

	function get_filter_url( $val = false ) {

		if ( ! rcl_get_option( 'users_page_rcl' ) ) {
			return false;
		}

		if ( ! $val ) {
			$val = $this->value;
		}

		return rcl_format_url( get_permalink( rcl_get_option( 'users_page_rcl' ) ) ) . 'usergroup=' . $this->slug . ':' . urlencode( $val );
	}

}
