<?php

require_once 'options/class-rcl-option.php';
require_once 'options/class-rcl-options-box.php';
require_once 'options/class-rcl-options-group.php';

class Rcl_Options_Manager {

	public $boxes = array();
	public $extends = false;
	public $extend_options = false;
	public $nonce = 'update-options';
	public $page_options = '';
	public $onclick = 'rcl_update_options();return false;';
	public $action = 'options.php';
	public $method = 'post';
	public $option_name;

	function __construct( $args = false ) {

		if ( $args ) {
			$this->init_properties( $args );
		}

		if ( $this->extends ) {
			$this->extend_options = isset( $_COOKIE['rcl_extends'] ) ? intval($_COOKIE['rcl_extends']) : 0;
		}
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) & ! empty( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function isset_box( $box_id ) {
		return isset( $this->boxes[ $box_id ] );
	}

	function add_box( $box_id, $args ) {
		$this->boxes[ $box_id ] = new Rcl_Options_Box( $box_id, $args, $this->option_name );

		return $this->box( $box_id );
	}

	function box( $box_id ) {
		return $this->boxes[ $box_id ];
	}

	function get_menu() {

		if ( ! $this->boxes ) {
			return false;
		}

		$items = [];

		foreach ( $this->boxes as $box ) {

			$items[] = rcl_get_button( array(
				'data'    => array(
					'options' => $box->box_id
				),
				'label'   => $box->title,
				'href'    => admin_url( 'admin.php?page=' . $this->page_options . '&rcl-options-box=' . $box->box_id ),
				'onclick' => 'rcl_onclick_options_label(this);return false;',
				'icon'    => $box->icon,
				'type'    => 'simple',
				'status'  => $box->active ? 'active' : ''
			) );
		}

		$content = '<div class="rcl-menu menu-items">';

		foreach ( $items as $item ) {
			$content .= $item;
		}

		$content .= '</div>';

		return $content;
	}

	function get_content() {

		if ( ! isset( $_GET['rcl-options-box'] ) ) {
			foreach ( $this->boxes as $id => $box ) {
				$this->boxes[ $id ]->active = true;
				break;
			}
		}

		$content = '<div class="rcl-options-manager rcl-options ' . ( $this->extend_options ? 'show-extends-options' : 'hide-extends-options' ) . '">';

		$content .= '<form method="post" class="rcl-options-form" action="' . $this->action . '">';

		$content .= '<input type="hidden" name="page_options" value="' . $this->page_options . '">';

		$content .= wp_nonce_field( $this->nonce, '_wpnonce', true, false );

		$content .= '<div class="options-menu-boxes">';

		if ( $this->extends ) {
			$content .=
				'<label class="rcl-option-extend-switch">'
				. '<input type="checkbox" name="extend_options" ' . checked( $this->extend_options, 1, false ) . ' onclick="return rcl_enable_extend_options(this);" value="1"> '
				. __( 'Advanced settings', 'wp-recall' )
				. '</label>';
		}

		foreach ( $this->boxes as $box ) {

			if ( ! $box->active ) {
				continue;
			}

			$content .= rcl_get_button( array(
					'label'      => $box->title,
					'onclick'    => 'rcl_show_options_menu(this);return false;',
					'icon'       => 'fa-chevron-down', //$box->icon,
					'icon_align' => 'right',
					'type'       => 'clear',
					'style'      => 'text-align: center;',
					'fullwidth'  => true,
					'class'      => array( 'button button-primary button-large active-menu-item' )
					//'status'	 => $box->active ? 'active' : ''
				)
			);
		}

		$content .= $this->get_menu();

		$content .= rcl_get_button( array(
			'label'   => __( 'Save settings', 'wp-recall' ),
			'onclick' => $this->onclick ? $this->onclick : false,
			'submit'  => $this->onclick ? false : true,
			'icon'    => 'fa-floppy-o',
			'type'    => 'clear',
			'style'   => 'text-align: center;',
			'class'   => array( 'button button-primary button-large rcl-submit-options' )
		) );

		$content .= '</div>';

		$content .= '<div class="options-form-boxes">';

		foreach ( $this->boxes as $box ) {

			$content .= $box->get_content();
		}

		$content .= '</div>';

		$content .= '</form>';

		$content .= '</div>';

		return $content;
	}

}
