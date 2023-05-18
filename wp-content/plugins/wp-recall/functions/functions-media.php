<?php

function rcl_get_image_gallery( $args ) {
	require_once RCL_PATH . 'classes/class-rcl-image-gallery.php';
	$gallery = new Rcl_Image_Gallery( $args );

	return $gallery->get_gallery();
}

function rcl_add_temp_media( $args ) {
	global $wpdb, $user_ID;

	$args = wp_parse_args( $args, array(
		'media_id'    => '',
		'user_id'     => $user_ID,
		'uploader_id' => '',
		'session_id'  => $user_ID ? '' : ( ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : 'none' ),
		'upload_date' => current_time( 'mysql' )
	) );

	if ( ! $args['media_id'] ) {
		return false;
	}

	if ( ! $wpdb->insert( RCL_PREF . 'temp_media', $args ) ) {
		return false;
	}

	do_action( 'rcl_add_temp_media', $args['media_id'] );

	return $args['media_id'];
}

function rcl_update_temp_media( $update, $where ) {
	global $wpdb;

	return $wpdb->update( RCL_PREF . 'temp_media', $update, $where );
}

function rcl_delete_temp_media( $media_id ) {
	global $wpdb;

	return $wpdb->delete( RCL_PREF . "temp_media", [ 'media_id' => intval( $media_id ) ] );
}

function rcl_delete_temp_media_by_args( $args ) {

	$medias = rcl_get_temp_media( $args );

	if ( ! $medias ) {
		return false;
	}

	foreach ( $medias as $media ) {
		rcl_delete_temp_media( $media->media_id );
	}
}

function rcl_get_temp_media( $args = false ) {
	return RQ::tbl( new Rcl_Temp_Media() )->parse( $args )->get_results();
}

add_action( 'delete_attachment', 'rcl_delete_attachment_temp_gallery', 10 );
function rcl_delete_attachment_temp_gallery( $attachment_id ) {
	rcl_delete_temp_media( $attachment_id );
}

add_action( 'rcl_cron_twicedaily', 'rcl_delete_daily_old_temp_attachments', 10 );
function rcl_delete_daily_old_temp_attachments() {

	$medias = rcl_get_temp_media( array(
		'date_query' => array(
			array(
				'last' => '1 DAY'
			)
		)
	) );

	if ( ! $medias ) {
		return false;
	}

	foreach ( $medias as $media ) {
		wp_delete_attachment( $media->media_id, true );
	}
}

//кроп изображений
function rcl_crop( $filesource, $width, $height, $file ) {

	$image = wp_get_image_editor( $filesource );

	if ( ! is_wp_error( $image ) ) {
		$image->resize( $width, $height, true );
		$image->save( $file );
	}

	return $image;
}
