<?php

function rcl_ajax_action( $function_name, $guest_access = false ) {

	add_action( 'wp_ajax_' . $function_name, $function_name );

	if ( $guest_access ) {
		add_action( 'wp_ajax_nopriv_' . $function_name, $function_name );
	}
}

function rcl_is_ajax() {
	return ( defined( 'DOING_AJAX' ) && DOING_AJAX || isset( $GLOBALS['wp']->query_vars['rest_route'] ) );
}

//загрузка вкладки ЛК через AJAX
rcl_ajax_action( 'rcl_ajax_tab', true );
function rcl_ajax_tab() {
	global $user_LK;

	rcl_verify_ajax_nonce();

	if ( ! isset( $_POST['post'] ) ) {
		wp_send_json( array( 'error' => __( 'Data of the requested tab was not found.', 'wp-recall' ) ) );
	}

	$post = rcl_decode_post( sanitize_text_field( wp_unslash( $_POST['post'] ) ) );

	do_action( 'rcl_init_ajax_tab', $post->tab_id );

	$tab = rcl_get_tab( $post->tab_id );

	if ( ! $tab ) {
		wp_send_json( array( 'error' => esc_html__( 'Data of the requested tab was not found.', 'wp-recall' ) ) );
	}

	$ajax = ( in_array( 'ajax', $tab['supports'] ) || in_array( 'dialog', $tab['supports'] ) ) ? 1 : 0;

	if ( ! $ajax ) {
		wp_send_json( array( 'error' => esc_html__( 'Perhaps this add-on does not support ajax loading', 'wp-recall' ) ) );
	}

	$user_LK = intval( $post->master_id );

	$content = rcl_get_tab_content( $post->tab_id, $post->master_id, isset( $post->subtab_id ) ? $post->subtab_id : '' );

	if ( ! $content ) {
		wp_send_json( array( 'error' => esc_html__( 'Unable to obtain content of the requested tab', 'wp-recall' ) ) );
	}

	$content = apply_filters( 'rcl_ajax_tab_content', $content );

	if ( isset( $_POST['tab_url'] ) ) {
		$tab_url = sanitize_text_field( wp_unslash( $_POST['tab_url'] ) );
	}

	if ( isset( $_POST['tab'] ) ) {
		$tab_url .= '&tab=' . sanitize_key( $_POST['tab'] );
	}

	$result = apply_filters( 'rcl_ajax_tab_result', array(
		'result' => $content,
		'post'   => array(
			'tab_id'    => $post->tab_id,
			'subtab_id' => isset( $post->subtab_id ) ? $post->subtab_id : '',
			'tab_url'   => $tab_url,
			'supports'  => $tab['supports'],
			'master_id' => intval( $post->master_id )
		)
	) );

	wp_send_json( $result );
}

//регистрируем биение плагина
rcl_ajax_action( 'rcl_beat', true );
function rcl_beat() {
	rcl_verify_ajax_nonce();
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$databeat = isset( $_POST['databeat'] ) ? rcl_recursive_map( 'sanitize_text_field', json_decode( wp_unslash( $_POST['databeat'] ) ) ) : [];
	$return   = [];

	if ( $databeat ) {
		foreach ( $databeat as $data ) {
			if ( ! rcl_beat_action_exist( $data->beat_name, $data->action ) ) {
				continue;
			}

			$result = [];

			$callback            = $data->action;
			$result['result']    = $callback( $data->data );
			$result['success']   = $data->success;
			$result['beat_name'] = $data->beat_name;
			$return[]            = $result;
		}
	}

	wp_send_json( $return );
}

rcl_ajax_action( 'rcl_manage_user_black_list', false );
function rcl_manage_user_black_list() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;

	if ( ! $user_id ) {
		wp_send_json( array(
			'error' => esc_html__( 'Error', 'wp-recall' )
		) );
	}

	$user_block = get_user_meta( $user_ID, 'rcl_black_list:' . $user_id );

	if ( $user_block ) {
		delete_user_meta( $user_ID, 'rcl_black_list:' . $user_id );
		do_action( 'remove_user_blacklist', $user_id );
	} else {
		add_user_meta( $user_ID, 'rcl_black_list:' . $user_id, 1 );
		do_action( 'add_user_blacklist', $user_id );
	}

	$new_status = $user_block ? 0 : 1;

	wp_send_json( array(
		'label' => ( $new_status ) ? esc_html__( 'Unblock', 'wp-recall' ) : esc_html__( 'Blacklist', 'wp-recall' )
	) );
}

rcl_ajax_action( 'rcl_get_smiles_ajax', false );
function rcl_get_smiles_ajax() {
	global $wpsmiliestrans;

	rcl_verify_ajax_nonce();

	$content = array();

	$smilies = array();
	foreach ( $wpsmiliestrans as $emo => $smilie ) {
		$smilies[ $smilie ] = $emo;
	}

	foreach ( $smilies as $smilie => $emo ) {
		if ( ! $emo ) {
			continue;
		}
		$content[] = str_replace( 'style="height: 1em; max-height: 1em;"', '', convert_smilies( $emo ) );
	}

	if ( ! $content ) {
		wp_send_json( array(
			'error' => esc_html__( 'Failed to load emoticons', 'wp-recall' )
		) );
	}

	wp_send_json( array(
		'content' => implode( '', $content )
	) );
}

/* new uploader */
rcl_ajax_action( 'rcl_upload', true );
function rcl_upload() {

	rcl_verify_ajax_nonce();
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$options = isset( $_POST['options'] ) ? rcl_recursive_map( 'sanitize_text_field', ( array ) json_decode( wp_unslash( $_POST['options'] ) ) ) : [];

	if ( ! isset( $options['class_name'] ) || ! $options['class_name'] ) {
		wp_send_json( [
			'error' => esc_html__( 'Error', 'wp-recall' )
		] );
	}

	$className = $options['class_name'];

	if ( $className == 'Rcl_Uploader' ) {
		$uploader = new $className( $options['uploader_id'], $options );
	} else if ( is_subclass_of( $className, 'Rcl_Uploader' ) ) {
		$uploader = new $className( $options );
	} else {
		wp_send_json( [
			'error' => esc_html__( 'Error', 'wp-recall' )
		] );
	}

	$secret       = isset( $_POST['sk'] ) ? sanitize_text_field( wp_unslash( $_POST['sk'] ) ) : false;
	$secret_check = md5( json_encode( $uploader ) . rcl_get_option( 'security-key' ) );

	if ( ! $secret || $secret_check != $secret ) {
		wp_send_json( [
			'error' => esc_html__( 'Error of security', 'wp-recall' )
		] );
	}

	$files = $uploader->upload();

	if ( $files ) {
		wp_send_json( $files );
	} else {
		wp_send_json( array(
			'error' => esc_html__( 'Something has been wrong', 'wp-recall' )
		) );
	}
}

//удаление фото приложенных к публикации через загрузчик плагина
rcl_ajax_action( 'rcl_ajax_delete_attachment', true );
function rcl_ajax_delete_attachment() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$attachment_id = isset( $_POST['attach_id'] ) ? intval( $_POST['attach_id'] ) : 0;
	$post_id       = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

	if ( ! $attachment_id ) {
		wp_send_json( array(
			'error' => esc_html__( 'The data has been wrong!', 'wp-recall' )
		) );
	}

	if ( $post_id ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_send_json( array(
				'error' => esc_html__( 'You can`t delete this file!', 'wp-recall' )
			) );
		}
	} else {

		$media = RQ::tbl( new Rcl_Temp_Media() )->where( [ 'media_id' => $attachment_id ] )->get_row();

		if ( ! $user_ID ) {
			$sess_id = isset( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : '';
			if ( ! $sess_id || $media->session_id != $sess_id ) {
				wp_send_json( array(
					'error' => esc_html__( 'You can`t delete this file!', 'wp-recall' )
				) );
			}
		} else {
			if ( ! current_user_can( 'edit_post', $attachment_id ) ) {
				wp_send_json( array(
					'error' => esc_html__( 'You can`t delete this file!', 'wp-recall' )
				) );
			}
		}

		rcl_delete_temp_media( $attachment_id );
	}

	wp_delete_attachment( $attachment_id, true );

	wp_send_json( array(
		'success' => esc_html__( 'The file has been successfully deleted!', 'wp-recall' )
	) );
}

/* new uploader end */
