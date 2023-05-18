<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-rcl-sub-tabs
 *
 * @author Андрей
 */
class Rcl_Sub_Tabs {

	public $subtabs;
	public $active_tab;
	public $parent_id;
	public $parent_tab;
	public $callback;

	function __construct( $subtabs, $parent_id = false ) {

		$this->subtabs    = $subtabs;
		$this->parent_id  = $parent_id;
		$this->parent_tab = rcl_get_tab( $parent_id );

		if ( isset( $_GET['subtab'] ) ) {

			foreach ( $subtabs as $k => $subtab ) {

				if ( $_GET['subtab'] == $subtab['id'] ) {
					$this->active_tab = $subtab['id'];
				}
			}
		}

		if ( ! $this->active_tab ) {

			$this->active_tab = $this->subtabs[0]['id'];
		}
	}

	function get_sub_content( $master_id ) {
		$content = $this->get_submenu( $master_id );
		$content .= $this->get_subtab( $master_id );

		return $content;
	}

	function get_submenu( $master_id ) {

		$content = '<div class="rcl-subtab-menu">';

		foreach ( $this->subtabs as $key => $tab ) {

			if ( ! isset( $tab['name'] ) || ! $tab['name'] ) {
				continue;
			}

			$classes = array( 'rcl-subtab-button' );

			if ( isset( $this->parent_tab['supports'] ) ) {
				if ( in_array( 'ajax', $this->parent_tab['supports'] ) ) {
					$classes[] = 'rcl-ajax';
				}
			}

			$button_args = array(
				'class'  => implode( ' ', $classes ),
				'label'  => $tab['name'],
				'href'   => $this->url_string( $master_id, $tab['id'] ),
				'status' => $this->active_tab == $tab['id'] ? 'active' : false
			);

			if ( isset( $tab['icon'] ) ) {
				$button_args['icon'] = $tab['icon'];
			}

			$button_args['data'] = [
				'post' => rcl_encode_post( [
						'tab_id'    => $this->parent_id,
						'subtab_id' => $tab['id'],
						'master_id' => $master_id
					]
				)
			];

			$content .= rcl_get_button( $button_args );
		}

		$content .= '</div>';

		return $content;
	}

	function get_subtab( $master_id ) {

		foreach ( $this->subtabs as $key => $tab ) {
			if ( isset( $tab['id'] ) && $this->active_tab == $tab['id'] ) {
				$this->callback = ( isset( $tab['callback'] ) ) ? $tab['callback'] : false;
			}
		}

		$content = '<div id="subtab-' . $this->active_tab . '" class="rcl-subtab-content">';

		if ( $this->callback ) {

			if ( isset( $this->callback['args'] ) ) {
				$args = $this->callback['args'];
			} else {
				$args = array( $master_id );
			}

			$funcContent = call_user_func_array( $this->callback['name'], $args );

			if ( ! $funcContent ) {
				rcl_add_log(
					'get_subtab: ' . __( 'Failed to load tab content', 'wp-recall' ), $this->callback
				);
			}

			$content .= $funcContent;
		}

		$content .= '</div>';

		return $content;
	}

	function url_string( $master_id, $subtab_id ) {

		return rcl_format_url( rcl_get_user_url( $master_id ), $this->parent_id, $subtab_id );
	}

}
