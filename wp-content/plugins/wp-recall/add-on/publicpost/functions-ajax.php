<?php

//удаление фото приложенных к публикации через загрузчик плагина
rcl_ajax_action( 'rcl_ajax_delete_post', true );
function rcl_ajax_delete_post() {
	global $user_ID;
	/**
	 * todo check
	 */
	rcl_verify_ajax_nonce();

	$user_id = ( $user_ID ) ? $user_ID : ( ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : 0 );

	$temps    = get_site_option( 'rcl_tempgallery' );
	$temp_gal = $temps[ $user_id ];

	$attach_to_remove = 0;

	if ( $temp_gal ) {
		$new_temp = false;
		foreach ( ( array ) $temp_gal as $key => $gal ) {
			if ( isset( $_POST['post_id'] ) && $gal['ID'] == absint( $_POST['post_id'] ) ) {
				unset( $temp_gal[ $key ] );
				//post_id should be in user temp
				$attach_to_remove = $gal['ID'];
			}
		}
		foreach ( ( array ) $temp_gal as $t ) {
			$new_temp[] = $t;
		}

		if ( $new_temp ) {
			$temps[ $user_id ] = $new_temp;
		} else {
			unset( $temps[ $user_id ] );
		}
	}

	update_site_option( 'rcl_tempgallery', $temps );

	$post = get_post( $attach_to_remove );

	if ( ! $attach_to_remove || ! $post ) {
		$log['success']   = __( 'Material successfully removed!', 'wp-recall' );
		$log['post_type'] = 'attachment';
	} else {

		$res = wp_delete_post( $post->ID );

		if ( $res ) {
			$log['success']   = __( 'Material successfully removed!', 'wp-recall' );
			$log['post_type'] = $post->post_type;
		} else {
			$log['error'] = __( 'Deletion failed!', 'wp-recall' );
		}
	}

	wp_send_json( $log );
}

//вызов быстрой формы редактирования публикации
rcl_ajax_action( 'rcl_get_edit_postdata' );
function rcl_get_edit_postdata() {

	rcl_verify_ajax_nonce();

	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

	if ( $post_id && current_user_can( 'edit_post', $post_id ) ) {

		$post = get_post( $post_id );

		$log['result']  = 100;
		$log['content'] = "
        <form id='rcl-edit-form' method='post'>
                <label>" . __( "Name", 'wp-recall' ) . ":</label>
                 <input type='text' name='post_title' value='$post->post_title'>
                 <label>" . __( "Description", 'wp-recall' ) . ":</label>
                 <textarea name='post_content' rows='10'>$post->post_content</textarea>
                 <input type='hidden' name='post_id' value='$post_id'>
        </form>";
	} else {
		$log['error'] = __( 'Failed to get the data', 'wp-recall' );
	}

	wp_send_json( $log );
}

//сохранение изменений в быстрой форме редактирования
rcl_ajax_action( 'rcl_edit_postdata' );
function rcl_edit_postdata() {
	global $wpdb;

	rcl_verify_ajax_nonce();

	$post_id                    = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
	$post_array                 = array();
	$post_array['post_title']   = isset( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
	$post_array['post_content'] = isset( $_POST['post_content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['post_content'] ) ) : '';

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_send_json( [ 'error' => __( 'Error', 'wp-recall' ) ] );
	}

	$post_array = apply_filters( 'rcl_pre_edit_post', $post_array );
	/**
	 * todo wp_update_post
	 */
	$result = $wpdb->update(
		$wpdb->posts, $post_array, array( 'ID' => $post_id )
	);

	if ( ! $result ) {
		wp_send_json( array( 'error' => __( 'Changes to be saved not found', 'wp-recall' ) ) );
	}

	wp_send_json( array(
		'success' => __( 'Publication updated', 'wp-recall' ),
		'dialog'  => array( 'close' )
	) );
}

//выборка меток по введенным значениям
rcl_ajax_action( 'rcl_get_like_tags', true );
function rcl_get_like_tags() {
	rcl_verify_ajax_nonce();

	if ( empty( $_POST['query'] ) || empty( $_POST['taxonomy'] ) ) {
		wp_send_json( array( array( 'id' => '' ) ) );
	}

	$query    = sanitize_text_field( wp_unslash( $_POST['query'] ) );
	$taxonomy = sanitize_key( $_POST['taxonomy'] );

	$terms = get_terms( $taxonomy, array( 'hide_empty' => false, 'name__like' => $query ) );

	$tags = array();
	foreach ( $terms as $key => $term ) {
		$tags[ $key ]['id']   = $term->name;
		$tags[ $key ]['name'] = $term->name;
	}

	wp_send_json( $tags );
}

add_filter( 'rcl_preview_post_content', 'rcl_add_registered_scripts' );
rcl_ajax_action( 'rcl_preview_post', true );
function rcl_preview_post() {

	rcl_verify_ajax_nonce();
	rcl_reset_wp_dependencies();

	$data_post_id    = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$data_user_email = ! empty( $_POST['email-user'] ) ? sanitize_email( wp_unslash( $_POST['email-user'] ) ) : '';
	$data_user_login = ! empty( $_POST['name-user'] ) ? sanitize_user( wp_unslash( $_POST['name-user'] ) ) : '';
	$data_post_type  = ! empty( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';
	$data_post_title = ! empty( $_POST['post_title'] ) ? sanitize_text_field( wp_unslash( $_POST['post_title'] ) ) : '';
	$data_form_id    = ! empty( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 1;

	if ( $data_post_id ) {
		if ( ! current_user_can( 'administrator' ) ) {
			$post = get_post( $data_post_id );
			if ( ! $post || $post->post_author != get_current_user_id() ) {
				wp_send_json( [ 'error' => __( 'Error', 'wp-recall' ) ] );
			}
		}
	}

	if ( ! rcl_get_option( 'public_access' ) && ! is_user_logged_in() ) {

		if ( ! $data_user_email ) {
			wp_send_json( [ 'error' => __( 'Enter your e-mail!', 'wp-recall' ) ] );
		}

		if ( ! $data_user_login ) {
			wp_send_json( [ 'error' => __( 'Enter your name!', 'wp-recall' ) ] );
		}

		if ( ! $data_user_email || ! is_email( $data_user_email ) ) {
			wp_send_json( [ 'error' => __( 'You have entered an invalid email!', 'wp-recall' ) ] );
		}
		if ( ! $data_user_login || ! validate_username( $data_user_login ) ) {
			wp_send_json( [ 'error' => __( 'You have entered an invalid name!', 'wp-recall' ) ] );
		}

		if ( email_exists( $data_user_email ) ) {
			wp_send_json( [ 'error' => __( 'This email is already used!', 'wp-recall' ) . '<br>' . __( 'If this is your email, then log in and publish your post', 'wp-recall' ) ] );
		}

		if ( username_exists( $data_user_login ) ) {
			wp_send_json( [ 'error' => __( 'This name is already used!', 'wp-recall' ) ] );
		}
	}

	if ( ! $data_post_type || ! $data_form_id ) {
		wp_send_json( [ 'error' => __( 'Error', 'wp-recall' ) ] );
	}

	$formFields = new Rcl_Public_Form_Fields( $data_post_type, array(
		'form_id' => $data_form_id
	) );

	foreach ( $formFields->fields as $field ) {

		if ( $field->type == 'runner' ) {

			$value = isset( $_POST[ $field->id ] ) && is_numeric( $_POST[ $field->id ] ) ? sanitize_key( $_POST[ $field->id ] ) : 0;
			$min   = $field->value_min;
			$max   = $field->value_max;

			if ( $value < $min || $value > $max ) {
				wp_send_json( array( 'error' => __( 'Incorrect values of some fields, enter the correct values!', 'wp-recall' ) ) );
			}
		}
	}

	if ( $formFields->is_active_field( 'post_thumbnail' ) ) {

		$thumbnail_id = ( isset( $_POST['post_thumbnail'] ) ) ? absint( $_POST['post_thumbnail'] ) : 0;

		$field = $formFields->get_field( 'post_thumbnail' );

		if ( $field->get_prop( 'required' ) && ! $thumbnail_id ) {
			wp_send_json( array( 'error' => __( 'Upload or specify an image as a thumbnail', 'wp-recall' ) ) );
		}
	}

	$post_content = '';

	if ( $formFields->is_active_field( 'post_content' ) ) {

		$postContent = isset( $_POST['post_content'] ) ? wp_kses_post( wp_unslash( $_POST['post_content'] ) ) : '';

		$field = $formFields->get_field( 'post_content' );

		if ( $field->get_prop( 'required' ) && ! $postContent ) {
			wp_send_json( [ 'error' => __( 'Add contents of the publication!', 'wp-recall' ) ] );
		}

		$post_content = wpautop( do_shortcode( $postContent ) );
	}

	do_action( 'rcl_preview_post', [
		'post_id'   => $data_post_id,
		'post_type' => $data_post_type,
		'form_id'   => $data_form_id
	] );

	if ( ! empty( $_POST['publish'] ) ) {
		wp_send_json( [
			'submit' => true
		] );
	}

	$customFields = $formFields->get_custom_fields();

	if ( rcl_get_option( 'pm_rcl' ) && $customFields ) {

		$types = rcl_get_option( 'pm_post_types' );

		if ( ! $types || in_array( $data_post_type, $types ) ) {

			$fieldsBox = '<div class="rcl-custom-fields">';

			foreach ( $customFields as $field_id => $field ) {
				//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$data_field_val = isset( $_POST[ $field_id ] ) ? wp_unslash( $_POST[ $field_id ] ) : false;

				if ( $field->type == 'editor' ) {
					$data_field_val = wp_kses_post( $data_field_val );
				} else {
					$data_field_val = rcl_recursive_map( 'sanitize_text_field', $data_field_val );
				}

				$field->set_prop( 'value', $data_field_val );

				$fieldsBox .= $field->get_field_value( true );

			}

			$fieldsBox .= '</div>';

			if ( rcl_get_option( 'pm_place' ) == 1 ) {
				$post_content .= $fieldsBox;
			} else {
				$post_content = $fieldsBox . $post_content;
			}
		}
	}

	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$post_gallery_ids = ! empty( $_POST['rcl-post-gallery'] ) ? rcl_recursive_map( 'intval', wp_unslash( $_POST['rcl-post-gallery'] ) ) : [];

	if ( $post_gallery_ids ) {

		$post_gallery_ids = array_unique( $post_gallery_ids );
		foreach ( $post_gallery_ids as $key => $attachment_id ) {
			if ( ! $attachment_id ) {
				unset( $post_gallery_ids[ $key ] );
			}
		}

		if ( $post_gallery_ids ) {
			$post_content = '<div id="primary-preview-gallery">' . rcl_get_post_gallery( 'preview', $post_gallery_ids ) . '</div>' . $post_content;
		}
	}

	$preview = apply_filters( 'rcl_preview_post_content', $post_content );

	$preview .= rcl_get_notice( [
		'text' => __( 'If everything is correct – publish it! If not, you can go back to editing.', 'wp-recall' )
	] );

	do_action( 'rcl_pre_send_preview_post', [
		'post_id'   => $data_post_id,
		'post_type' => $data_post_type,
		'form_id'   => $data_form_id
	] );

	wp_send_json( array(
		'title'   => $data_post_title,
		'content' => $preview
	) );
}

rcl_ajax_action( 'rcl_set_post_thumbnail', true );
function rcl_set_post_thumbnail() {

	$thumbnail_id = isset( $_POST['thumbnail_id'] ) ? intval( $_POST['thumbnail_id'] ) : 0;
	$parent_id    = isset( $_POST['parent_id'] ) ? intval( $_POST['parent_id'] ) : 0;
	$form_id      = isset( $_POST['form_id'] ) ? intval( $_POST['form_id'] ) : 0;
	$post_type    = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';

	if ( $parent_id && ! current_user_can( 'administrator' ) ) {
		$post = get_post( $parent_id );
		if ( ! $post || $post->post_author != get_current_user_id() ) {
			wp_send_json( [ 'error' => __( 'Error', 'wp-recall' ) ] );
		}
	}

	$formFields = new Rcl_Public_Form_Fields( $post_type, array(
		'form_id' => $form_id ?: 1
	) );

	if ( ! $formFields->is_active_field( 'post_thumbnail' ) ) {
		wp_send_json( [
			'error' => __( 'The field of the thumbnail is inactive!', 'wp-recall' )
		] );
	}

	if ( $parent_id ) {
		update_post_meta( $parent_id, '_thumbnail_id', $thumbnail_id );
	}

	$field = $formFields->get_field( 'post_thumbnail' );

	$field->set_prop( 'uploader_props', array(
		'post_parent' => $parent_id,
		'form_id'     => $form_id,
		'post_type'   => $post_type,
		'multiple'    => 0,
		'crop'        => 1
	) );

	$result = array(
		'html' => $field->get_uploader()->gallery_attachment( $thumbnail_id ),
		'id'   => $thumbnail_id
	);

	wp_send_json( $result );
}

add_action( 'rcl_upload', 'rcl_upload_post_thumbnail', 10, 2 );
function rcl_upload_post_thumbnail( $uploads, $uploader ) {

	if ( $uploader->uploader_id != 'post_thumbnail' ) {
		return false;
	}

	$thumbnail_id = $uploads['id'];

	if ( $uploader->post_parent ) {

		update_post_meta( $uploader->post_parent, '_thumbnail_id', $thumbnail_id );
	} else {

		rcl_add_temp_media( array(
			'media_id'    => $thumbnail_id,
			'uploader_id' => $uploader->uploader_id
		) );
	}

	do_action( 'rcl_upload_post_thumbnail', $thumbnail_id, $uploader );

	$uploader->uploader_id  = 'post_uploader';
	$uploader->input_attach = 'post_uploader';
	$uploader->multiple     = 1;

	wp_send_json( [
		'thumbnail' => $uploads,
		'postmedia' => $uploader->gallery_attachment( $thumbnail_id )
	] );
}
