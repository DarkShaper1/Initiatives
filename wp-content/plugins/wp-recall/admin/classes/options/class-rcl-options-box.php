<?php

class Rcl_Options_Box {

	public $box_id;
	public $title;
	public $icon = 'fa-cog';
	public $groups;
	public $option_name;
	public $active = false;

	function __construct( $box_id, $args, $option_name ) {

		$this->box_id = $box_id;

		$this->option_name = $option_name;

		$this->init_properties( $args );

		if ( isset( $_GET['rcl-options-box'] ) ) {
			$this->active = $this->box_id == $_GET['rcl-options-box'] ? true : false;
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

	function add_group( $group_id, $args = false ) {
		$this->groups[ $group_id ] = new Rcl_Options_Group( $group_id, $this->option_name, $args );

		return $this->group( $group_id );
	}

	function isset_group( $group_id ) {
		return isset( $this->groups[ $group_id ] );
	}

	function group( $group_id ) {
		return $this->groups[ $group_id ];
	}

	function add_options( $options ) {

		if ( ! $this->isset_group( 'general' ) ) {
			$this->add_group( 'general', [
				'title' => __( 'General settings', 'wp-recall' )
			] );
		}

		$this->group( 'general' )->add_options( $options );
	}

	function get_content() {

		$content = '<div id="' . $this->box_id . '-options-box" class="options-box ' . ( $this->active ? 'active' : '' ) . '" data-box="' . $this->box_id . '">';

		foreach ( $this->groups as $group ) {

			$content .= $group->get_content();
		}

		$content .= '</div>';

		return $content;
	}

}
