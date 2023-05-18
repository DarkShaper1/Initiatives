<?php

class Prime_Form_Manager extends Rcl_Fields_Manager {

	public $group_id;
	public $forum_id;

	function __construct( $args ) {

		$this->group_id = $args['group_id'];
		$this->forum_id = $args['forum_id'];

		if ( $this->forum_id ) {
			$form_slug = 'pfm_forum_' . $this->forum_id;
		} else {
			$form_slug = 'pfm_group_' . $this->group_id;
		}

		parent::__construct( $form_slug, array(
			'meta_delete'   => array(
				RCL_PREF . "pforum_meta" => 'meta_key'
			),
			'field_options' => array(
				array(
					'type'  => 'textarea',
					'slug'  => 'notice',
					'title' => __( 'field description', 'wp-recall' )
				),
				array(
					'type'   => 'radio',
					'slug'   => 'required',
					'title'  => __( 'required field', 'wp-recall' ),
					'values' => array(
						__( 'No', 'wp-recall' ),
						__( 'Yes', 'wp-recall' )
					)
				)
			)
		) );
	}

	function form_navi() {

		$groups = pfm_get_groups( array(
			'order'   => 'ASC',
			'orderby' => 'group_seq',
			'number'  => - 1,
			'fields'  => array(
				'group_id',
				'group_name'
			)
		) );

		$groupsList = array();
		foreach ( $groups as $group ) {
			$groupsList[ $group->group_id ] = $group->group_name;
		}

		$forums = pfm_get_forums( array(
			'order'    => 'ASC',
			'orderby'  => 'forum_name',
			'group_id' => $this->group_id,
			'number'   => - 1,
			'fields'   => array(
				'forum_id',
				'forum_name'
			)
		) );

		$forumsList = array();
		foreach ( $forums as $forum ) {
			$forumsList[ $forum->forum_id ] = $forum->forum_name;
		}

		$content = '<div class="rcl-custom-fields-navi">';

		$content .= '<ul class="rcl-types-list">';

		foreach ( $groupsList as $group_id => $name ) {

			$class = ( $this->group_id == $group_id ) ? 'class="current-item"' : '';

			$content .= '<li ' . $class . '><a href="' . admin_url( 'admin.php?page=manage-topic-form&group-id=' . $group_id ) . '">' . $name . '</a></li>';
		}

		$content .= '</ul>';

		$content .= '</div>';

		$content .= '<div class="rcl-custom-fields-navi">';

		$content .= '<form method="get" action="' . admin_url( 'admin.php' ) . '">';

		$content .= '<input type="hidden" name="page" value="manage-topic-form">';
		$content .= '<input type="hidden" name="group-id" value="' . absint( $this->group_id ) . '">';

		$content .= '<select name="forum-id">';

		$content .= '<option value="">' . __( 'All the group`s forums', 'wp-recall' ) . '</option>';

		foreach ( $forumsList as $forum_id => $name ) {
			$content .= '<option value="' . $forum_id . '" ' . selected( $forum_id, $this->forum_id, false ) . '>' . __( 'Forum', 'wp-recall' ) . ': ' . $name . '</option>';
		}

		$content .= '</select>';

		$content .= ' <input type="submit" class="button" value="' . __( 'Go to the form`s settings', 'wp-recall' ) . '">';

		$content .= '</form>';

		$content .= '</div>';

		$title = __( 'Setup topic`s form for', 'wp-recall' ) . ' ';

		$title .= $this->forum_id ? __( 'forum', 'wp-recall' ) . ' "' . pfm_get_forum_field( $this->forum_id, 'forum_name' ) . '"' : __( 'group', 'wp-recall' ) . ' "' . pfm_get_group_field( $this->group_id, 'group_name' ) . '"';

		$content .= '<div class="rcl-custom-fields-navi">';

		$content .= '<h3>' . $title . '</h3>';

		$content .= '</div>';

		return $content;
	}

}
