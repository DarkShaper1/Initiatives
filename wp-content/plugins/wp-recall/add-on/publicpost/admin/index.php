<?php

require_once 'addon-settings.php';

add_action( 'admin_init', 'rcl_public_admin_scripts' );
function rcl_public_admin_scripts() {
	wp_enqueue_style( 'rcl_public_admin_style', rcl_addon_url( 'admin/assets/style.css', __FILE__ ), false, VER_RCL );
}

add_action( 'admin_menu', 'rcl_admin_page_publicform', 30 );
function rcl_admin_page_publicform() {
	add_submenu_page( 'manage-wprecall', __( 'Form of publication', 'wp-recall' ), __( 'Form of publication', 'wp-recall' ), 'manage_options', 'manage-public-form', 'rcl_public_form_manager' );
}

function rcl_public_form_manager() {

	$post_type = ( isset( $_GET['post-type'] ) ) ? sanitize_key( $_GET['post-type'] ) : 'post';
	$form_id   = ( isset( $_GET['form-id'] ) ) ? intval( $_GET['form-id'] ) : 1;

	$shortCode = 'public-form post_type="' . $post_type . '"';

	if ( $form_id > 1 ) {
		$shortCode .= ' form_id="' . $form_id . '"';
	}

	$formManager = new Rcl_Public_Form_Manager( $post_type, array(
		'form_id' => $form_id
	) );

	$content = '<h2>' . esc_html__( 'Manage publication forms', 'wp-recall' ) . '</h2>';

	$content .= '<p>' . esc_html__( 'On this page you can manage the creation of publications for registered record types. Create custom fields for the form of publication of various types and manage', 'wp-recall' ) . '</p>';

	$content .= '<div id="rcl-public-form-manager">';

	$content .= $formManager->form_navi();

	$content .= rcl_get_notice( [ 'text' => esc_html__( 'Use shortcode for publication form', 'wp-recall' ) . ' [' . $shortCode . ']' ] );

	$content .= $formManager->get_manager();

	$content .= '</div>';

	echo $content;//phpcs:ignore
}

add_action( 'add_meta_boxes', 'custom_fields_editor_post_rcl', 1, 2 );
function custom_fields_editor_post_rcl( $post_type, $post ) {
	add_meta_box( 'custom_fields_editor_post', __( 'Arbitrary fields of  publication', 'wp-recall' ), 'custom_fields_list_posteditor_rcl', $post->post_type, 'normal', 'high' );
}

function custom_fields_list_posteditor_rcl( $post ) {
	$form_id = 1;

	if ( $post->ID && $post->post_type == 'post' ) {
		$form_id = get_post_meta( $post->ID, 'publicform-id', 1 );
	}

	$content = rcl_get_custom_fields_edit_box( $post->ID, $post->post_type, $form_id );

	if ( ! $content ) {
		return false;
	}

	echo $content;//phpcs:ignore

	echo '<input type="hidden" name="custom_fields_nonce_rcl" value="' . esc_attr( wp_create_nonce( __FILE__ ) ) . '" />';
}

add_action( 'save_post', 'rcl_custom_fields_update', 0 );
function rcl_custom_fields_update( $post_id ) {
	if ( ! isset( $_POST['custom_fields_nonce_rcl'] ) ) {
		return false;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['custom_fields_nonce_rcl'] ), __FILE__ ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	rcl_update_post_custom_fields( $post_id );

	if ( ! empty( $_POST['post_uploader'] ) ) {
		global $user_ID;

		$editPost = new Rcl_EditPost( $post_id );

		$editPost->rcl_add_attachments_in_temps( $user_ID );

		$editPost->update_post_gallery();

		rcl_delete_temp_media_by_args( array(
			'user_id'         => $user_ID,
			'uploader_id__in' => array( 'post_uploader', 'post_thumbnail' )
		) );
	}

	return $post_id;
}

add_action( 'admin_init', 'rcl_public_form_admin_actions', 10 );
function rcl_public_form_admin_actions() {

	if ( ! isset( $_GET['page'] ) || $_GET['page'] != 'manage-public-form' ) {
		return false;
	}

	if ( ! isset( $_GET['form-action'] ) || ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rcl-form-action' ) ) {
		return false;
	}

	if ( ! isset( $_GET['post-type'] ) ) {
		return false;
	}

	switch ( $_GET['form-action'] ) {

		case 'new-form':

			$newFormId = isset( $_GET['form-id'] ) ? intval( $_GET['form-id'] ) : 0;

			add_option( 'rcl_fields_' . sanitize_key( $_GET['post-type'] ) . '_' . $newFormId, array() );

			wp_safe_redirect( admin_url( 'admin.php?page=manage-public-form&post-type=' . sanitize_key( $_GET['post-type'] ) . '&form-id=' . $newFormId ) );
			exit;

			break;

		case 'delete-form':

			$delFormId = intval( $_GET['form-id'] );

			delete_site_option( 'rcl_fields_' . sanitize_key( $_GET['post-type'] ) . '_' . $delFormId );

			wp_safe_redirect( admin_url( 'admin.php?page=manage-public-form&post-type=' . sanitize_key( $_GET['post-type'] ) ) );
			exit;

			break;
	}
}

add_action( 'rcl_add_dashboard_metabox', 'rcl_add_publicpost_metabox' );
function rcl_add_publicpost_metabox( $screen ) {
	add_meta_box( 'rcl-publicpost-metabox', __( 'Posts awaiting approval', 'wp-recall' ), 'rcl_publicpost_metabox', $screen->id, 'column3' );
}

function rcl_publicpost_metabox() {

	$posts = get_posts( array( 'numberposts' => - 1, 'post_type' => 'any', 'post_status' => 'pending' ) );

	if ( ! $posts ) {
		echo '<p>' . esc_html__( 'No posts under moderation', 'wp-recall' ) . '</p>';

		return;
	}

	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<tr>'
	     . '<th>' . esc_html__( 'Header', 'wp-recall' ) . '</th>'
	     . '<th>' . esc_html__( 'Author', 'wp-recall' ) . '</th>'
	     . '<th>' . esc_html__( 'Type', 'wp-recall' ) . '</th>'
	     . '</tr>';
	foreach ( $posts as $post ) {
		echo '<tr>'
		     . '<td><a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '" target="_blank">' . esc_html( $post->post_title ) . '</a></td>'
		     . '<td>' . esc_html( $post->post_author ) . ': ' . esc_html( get_the_author_meta( 'user_login', $post->post_author ) ) . '</td>'
		     . '<td>' . esc_html( $post->post_type ) . '</td>'
		     . '</tr>';
	}
	echo '</table>';
}
