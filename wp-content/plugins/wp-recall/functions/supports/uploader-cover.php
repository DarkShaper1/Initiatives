<?php

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_support_cover_uploader_scripts', 10 );
endif;
function rcl_support_cover_uploader_scripts() {
	global $user_ID;
	if ( rcl_is_office( $user_ID ) ) {
		rcl_fileupload_scripts();
		rcl_crop_scripts();
		rcl_enqueue_script( 'cover-uploader', RCL_URL . 'functions/supports/js/uploader-cover.js', false, true );
	}
}

add_filter( 'rcl_init_js_variables', 'rcl_init_js_cover_variables', 10 );
function rcl_init_js_cover_variables( $data ) {
	global $user_ID;

	if ( rcl_is_office( $user_ID ) ) {
		$data['cover_size']                  = rcl_get_option( 'cover_weight', 1024 );
		$data['local']['upload_size_cover']  = sprintf( __( 'Exceeds the maximum image size! Max. %s Kb', 'wp-recall' ), rcl_get_option( 'cover_weight', 1024 ) );
		$data['local']['title_image_upload'] = __( 'Image being loaded', 'wp-recall' );
	}

	return $data;
}

add_action( 'rcl_area_top', 'rcl_add_cover_uploader_button', 10 );
function rcl_add_cover_uploader_button() {
	global $user_ID;
	if ( rcl_is_office( $user_ID ) ) {

		$uploder = new Rcl_Uploader( 'rcl_cover', array(
			'multiple'    => 0,
			'filetitle'   => 'rcl-user-cover-' . $user_ID,
			'filename'    => $user_ID,
			'dir'         => '/uploads/rcl-uploads/covers',
			'crop'        => array(
				'ratio' => 0
			),
			'image_sizes' => array(
				array(
					'height' => 9999,
					'width'  => 9999,
					'crop'   => 0
				)
			),
			'resize'      => array( 1500, 1500 ),
			'min_height'  => 300,
			'min_width'   => 600,
			'max_size'    => rcl_get_option( 'cover_weight', 1024 )
		) );

		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<span class="rcl-cover-icon" title="' . esc_html__( 'Upload background', 'wp-recall' ) . '">
                <i class="rcli fa-image"></i>
                ' . $uploder->get_input() . '
            </span>';
		//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}

add_action( 'rcl_upload', 'rcl_cover_upload', 10, 2 );
function rcl_cover_upload( $upload, $class ) {
	global $user_ID;

	if ( $class->uploader_id != 'rcl_cover' ) {
		return;
	}

	$oldCoverId = get_user_meta( $user_ID, 'rcl_cover', 1 );

	wp_delete_attachment( $oldCoverId );

	update_user_meta( $user_ID, 'rcl_cover', intval( $upload['id'] ) );

	do_action( 'rcl_cover_upload' );
}
