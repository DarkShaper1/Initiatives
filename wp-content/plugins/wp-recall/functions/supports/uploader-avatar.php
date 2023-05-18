<?php

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_support_avatar_uploader_scripts', 10 );
endif;
function rcl_support_avatar_uploader_scripts() {
	global $user_ID;
	if ( rcl_is_office( $user_ID ) ) {
		rcl_fileupload_scripts();
		rcl_crop_scripts();
		rcl_enqueue_script( 'avatar-uploader', RCL_URL . 'functions/supports/js/uploader-avatar.js', false, true );
	}
}

add_filter( 'rcl_init_js_variables', 'rcl_init_js_avatar_variables', 10 );
function rcl_init_js_avatar_variables( $data ) {
	global $user_ID;

	if ( rcl_is_office( $user_ID ) ) {
		$data['avatar_size']                  = rcl_get_option( 'avatar_weight', 1024 );
		$data['local']['upload_size_avatar']  = sprintf( __( 'Exceeds the maximum image size! Max. %s Kb', 'wp-recall' ), rcl_get_option( 'avatar_weight', 1024 ) );
		$data['local']['title_image_upload']  = __( 'Image being loaded', 'wp-recall' );
		$data['local']['title_webcam_upload'] = __( 'Image from camera', 'wp-recall' );
	}

	return $data;
}

add_filter( 'rcl_avatar_icons', 'rcl_button_avatar_upload', 10 );
function rcl_button_avatar_upload( $icons ) {
	global $user_ID;

	if ( ! rcl_is_office( $user_ID ) ) {
		return false;
	}

	$uploder = new Rcl_Uploader( 'rcl_avatar', array(
		'multiple'    => 0,
		'crop'        => 1,
		'filetitle'   => 'rcl-user-avatar-' . $user_ID,
		'filename'    => $user_ID,
		'dir'         => '/uploads/rcl-uploads/avatars',
		'image_sizes' => array(
			array(
				'height' => 70,
				'width'  => 70,
				'crop'   => 1
			),
			array(
				'height' => 150,
				'width'  => 150,
				'crop'   => 1
			),
			array(
				'height' => 300,
				'width'  => 300,
				'crop'   => 1
			)
		),
		'resize'      => array( 1000, 1000 ),
		'min_height'  => 150,
		'min_width'   => 150,
		'max_size'    => rcl_get_option( 'avatar_weight', 1024 )
	) );

	$icons['avatar-upload'] = array(
		'icon'    => 'fa-download',
		'content' => $uploder->get_input(),
		'atts'    => array(
			'title' => __( 'Avatar upload', 'wp-recall' ),
			'url'   => '#'
		)
	);

	if ( get_user_meta( $user_ID, 'rcl_avatar', 1 ) ) {

		$icons['avatar-delete'] = array(
			'icon' => 'fa-times',
			'atts' => array(
				'title' => __( 'Delete avatar', 'wp-recall' ),
				'href'  => wp_nonce_url( rcl_format_url( rcl_get_user_url( $user_ID ) ) . 'rcl-action=delete_avatar', $user_ID )
			)
		);
	}

	if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == 'on' ) {

		/* rcl_webcam_scripts();

		  $icons['webcam-upload'] = array(
		  'icon'	 => 'fa-camera',
		  'atts'	 => array(
		  'title'	 => __( 'Webcam screen', 'wp-recall' ),
		  'id'	 => 'webcamupload',
		  'url'	 => '#'
		  )
		  ); */
	}

	return $icons;
}

add_action( 'rcl_pre_upload', 'rcl_avatar_pre_upload', 10 );
function rcl_avatar_pre_upload( $uploader ) {
	global $user_ID;

	if ( $uploader->uploader_id != 'rcl_avatar' ) {
		return;
	}

	if ( $oldAvatarId = get_user_meta( $user_ID, 'rcl_avatar', 1 ) ) {
		wp_delete_attachment( $oldAvatarId );
	}
}

add_action( 'rcl_upload', 'rcl_avatar_upload', 10, 2 );
function rcl_avatar_upload( $uploads, $uploader ) {
	global $user_ID;

	if ( $uploader->uploader_id != 'rcl_avatar' ) {
		return;
	}

	update_user_meta( $user_ID, 'rcl_avatar', intval( $uploads['id'] ) );

	do_action( 'rcl_avatar_upload' );
}

add_action( 'wp', 'rcl_delete_avatar_action' );
function rcl_delete_avatar_action() {
	global $user_ID;
	if ( ! isset( $_GET['rcl-action'] ) || $_GET['rcl-action'] != 'delete_avatar' ) {
		return false;
	}
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), $user_ID ) ) {
		wp_die( 'Error' );
	}

	if ( $AvatarId = get_user_meta( $user_ID, 'rcl_avatar', 1 ) ) {
		wp_delete_attachment( $AvatarId );
	}

	delete_user_meta( $user_ID, 'rcl_avatar' );

	do_action( 'rcl_delete_avatar' );

	wp_safe_redirect( rcl_format_url( rcl_get_user_url( $user_ID ) ) . 'rcl-avatar=deleted' );
	exit;
}

add_action( 'wp', 'rcl_notice_avatar_deleted' );
function rcl_notice_avatar_deleted() {
	if ( isset( $_GET['rcl-avatar'] ) && $_GET['rcl-avatar'] == 'deleted' ) {
		add_action( 'rcl_area_notice', function () {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo rcl_get_notice( [
				'type' => 'success',
				'text' => esc_html__( 'Your avatar has been deleted', 'wp-recall' )
			] );
		} );
	}
}

// disabling caching in chrome
add_filter( 'get_avatar_data', 'rcl_add_avatar_time_creation', 10, 2 );
function rcl_add_avatar_time_creation( $args, $id_or_email ) {
	$dataUrl  = wp_parse_url( $args['url'] );
	$ava_path = untrailingslashit( ABSPATH ) . $dataUrl['path'];
	if ( ! file_exists( $ava_path ) ) {
		return $args;
	}
	$args['url'] = $args['url'] . '?ver=' . filemtime( $ava_path );

	return $args;
}
