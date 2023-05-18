<?php

require_once 'classes/class-rcl-form-fields.php';
require_once 'classes/class-rcl-edit-terms-list.php';
require_once 'classes/class-rcl-list-terms.php';
require_once 'classes/class-rcl-public-form-fields.php';
require_once 'classes/class-rcl-public-form.php';
require_once 'classes/class-rcl-post-list.php';
require_once 'classes/class-rcl-edit-post.php';
require_once 'core.php';
require_once 'shortcodes.php';
require_once 'functions-ajax.php';
require_once 'init.php';

if ( is_admin() ) {
	require_once 'classes/class-rcl-public-form-manager.php';
	require_once 'admin/index.php';
}

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_publics_scripts', 10 );
endif;
function rcl_publics_scripts() {
	rcl_enqueue_style( 'rcl-publics', rcl_addon_url( 'style.css', __FILE__ ) );
	rcl_enqueue_script( 'rcl-publics', rcl_addon_url( 'js/scripts.js', __FILE__ ) );
}

function rcl_autocomplete_scripts() {
	rcl_enqueue_style( 'magicsuggest', rcl_addon_url( 'js/magicsuggest/magicsuggest-min.css', __FILE__ ) );
	rcl_enqueue_script( 'magicsuggest', rcl_addon_url( 'js/magicsuggest/magicsuggest-min.js', __FILE__ ) );
}

add_filter( 'rcl_init_js_variables', 'rcl_public_add_js_locale', 10 );
function rcl_public_add_js_locale( $data ) {
	$data['errors']['cats_important'] = __( 'Choose a category', 'wp-recall' );

	return $data;
}

//выводим в медиабиблиотеке только медиафайлы текущего автора
add_action( 'pre_get_posts', 'rcl_restrict_media_library' );
function rcl_restrict_media_library( $wp_query_obj ) {
	global $current_user, $pagenow;

	if ( ! is_a( $current_user, 'WP_User' ) ) {
		return;
	}

	if ( 'admin-ajax.php' != $pagenow || ! isset( $_REQUEST['action'] ) || $_REQUEST['action'] != 'query-attachments' ) {
		return;
	}

	if ( rcl_check_access_console() ) {
		return;
	}

	if ( ! current_user_can( 'manage_media_library' ) ) {
		$wp_query_obj->set( 'author', $current_user->ID );
	}

	return;
}

add_filter( 'pre_update_postdata_rcl', 'rcl_update_postdata_excerpt' );
function rcl_update_postdata_excerpt( $postdata ) {
	if ( ! isset( $_POST['post_excerpt'] ) ) {
		return $postdata;
	}
	$postdata['post_excerpt'] = sanitize_text_field( wp_unslash( $_POST['post_excerpt'] ) );

	return $postdata;
}

//формируем галерею записи
add_filter( 'the_content', 'rcl_post_gallery', 10 );
function rcl_post_gallery( $content ) {
	global $post;

	if ( ! is_single() || $post->post_type == 'products' ) {
		return $content;
	}

	$oldSlider = get_post_meta( $post->ID, 'recall_slider', 1 );
	$gallery   = get_post_meta( $post->ID, 'rcl_post_gallery', 1 );

	if ( ! $gallery && $oldSlider ) {

		$args      = array(
			'post_parent'    => $post->ID,
			'post_type'      => 'attachment',
			'numberposts'    => - 1,
			'post_status'    => 'any',
			'post_mime_type' => 'image'
		);
		$childrens = get_children( $args );
		if ( $childrens ) {
			$gallery = array();
			foreach ( ( array ) $childrens as $children ) {
				$gallery[] = $children->ID;
			}
		}
	}

	if ( ! $gallery ) {
		return $content;
	}

	return rcl_get_post_gallery( $post->ID, $gallery ) . $content;
}

//Выводим инфу об авторе записи в конце поста
add_filter( 'the_content', 'rcl_author_info', 70 );
function rcl_author_info( $content ) {

	if ( ! rcl_get_option( 'info_author_recall' ) ) {
		return $content;
	}

	if ( ! is_single() ) {
		return $content;
	}

	global $post;

	if ( $post->post_type == 'page' ) {
		return $content;
	}

	if ( rcl_get_option( 'post_types_authbox' ) ) {

		if ( ! in_array( $post->post_type, rcl_get_option( 'post_types_authbox' ) ) ) {
			return $content;
		}
	}

	$content .= rcl_get_author_block();

	return $content;
}

add_filter( 'the_content', 'rcl_concat_post_meta', 10 );
function rcl_concat_post_meta( $content ) {
	global $post;

	if ( doing_filter( 'get_the_excerpt' ) ) {
		return $content;
	}

	$option = rcl_get_option( 'pm_rcl' );

	if ( ! $option ) {
		return $content;
	}

	if ( $types = rcl_get_option( 'pm_post_types' ) ) {
		if ( ! in_array( $post->post_type, $types ) ) {
			return $content;
		}
	}

	$pm = rcl_get_post_custom_fields_box( $post->ID );

	if ( rcl_get_option( 'pm_place' ) == 1 ) {
		$content .= $pm;
	} else {
		$content = $pm . $content;
	}

	return $content;
}

function rcl_get_post_custom_fields_box( $post_id ) {

	$formFields = new Rcl_Public_Form_Fields( get_post_type( $post_id ), array(
		'form_id' => get_post_meta( $post_id, 'publicform-id', 1 )
	) );

	$customFields = $formFields->get_custom_fields();

	$customFields = apply_filters( 'rcl_post_custom_fields', $customFields, $post_id );

	if ( ! $customFields ) {
		return false;
	}

	$content = '<div class="rcl-custom-fields">';

	foreach ( $customFields as $field_id => $field ) {
		$field->set_prop( 'value', get_post_meta( $post_id, $field_id, 1 ) );
		$content .= $field->get_field_value( true );
	}

	$content .= '</div>';

	return $content;
}

add_action( 'init', 'rcl_delete_post_activate' );
function rcl_delete_post_activate() {
	if ( isset( $_POST['rcl-delete-post'], $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'rcl-delete-post' ) ) {
		add_action( 'wp', 'rcl_delete_post' );
	}
}

function rcl_delete_post() {
	global $user_ID;

	if ( empty( $_POST['post_id'] ) ) {
		return false;
	}

	$post_id = intval( $_POST['post_id'] );

	$post = get_post( $post_id );

	if ( $post->post_type == 'post-group' ) {

		if ( ! rcl_can_user_edit_post_group( $post_id ) ) {
			return false;
		}
	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}
	}

	$post_id = wp_update_post( array(
		'ID'          => $post_id,
		'post_status' => 'trash'
	) );

	do_action( 'after_delete_post_rcl', $post_id );

	wp_safe_redirect( rcl_format_url( rcl_get_user_url( $user_ID ) ) . '&public=deleted' );
	exit;
}

add_action( 'after_delete_post_rcl', 'rcl_delete_notice_author_post' );
function rcl_delete_notice_author_post( $post_id ) {

	if ( empty( $_POST['reason_content'] ) ) {
		return false;
	}

	$post          = get_post( $post_id );
	$delete_reason = sanitize_textarea_field( wp_unslash( $_POST['reason_content'] ) );

	$subject  = __( 'Your post has been deleted', 'wp-recall' );
	$textmail = '<h3>' . __( 'Post', 'wp-recall' ) . ' "' . $post->post_title . '" ' . __( 'has been deleted', 'wp-recall' ) . '</h3>
    <p>' . __( 'Notice of a moderator', 'wp-recall' ) . ': ' . $delete_reason . '</p>';
	rcl_mail( get_the_author_meta( 'user_email', $post->post_author ), $subject, $textmail );
}

if ( ! is_admin() ) {
	add_filter( 'get_edit_post_link', 'rcl_edit_post_link', 100, 2 );
}
function rcl_edit_post_link( $admin_url, $post_id ) {
	global $user_ID;

	$frontEdit = rcl_get_option( 'front_editing', array( 0 ) );

	$user_info = get_userdata( $user_ID );

	if ( array_search( $user_info->user_level, $frontEdit ) !== false || $user_info->user_level < rcl_get_option( 'consol_access_rcl', 7 ) ) {
		$edit_url = rcl_format_url( get_permalink( rcl_get_option( 'public_form_page_rcl' ) ) );

		return $edit_url . 'rcl-post-edit=' . $post_id;
	} else {
		return $admin_url;
	}
}

add_action( 'rcl_post_bar_setup', 'rcl_setup_edit_post_button', 10 );
function rcl_setup_edit_post_button() {
	global $post, $user_ID, $current_user;

	if ( ! is_user_logged_in() || ! $post ) {
		return false;
	}

	if ( is_front_page() || is_tax( 'groups' ) || $post->post_type == 'page' ) {
		return false;
	}

	if ( ! current_user_can( 'edit_post', $post->ID ) ) {
		return false;
	}

	$user_info = get_userdata( $current_user->ID );

	if ( $post->post_author != $user_ID ) {
		$author_info = get_userdata( $post->post_author );
		if ( $user_info->user_level < $author_info->user_level ) {
			return false;
		}
	}

	$frontEdit = rcl_get_option( 'front_editing', array( 0 ) );

	if ( false !== array_search( $user_info->user_level, $frontEdit ) || $user_info->user_level >= rcl_get_option( 'consol_access_rcl', 7 ) ) {

		if ( $user_info->user_level < 10 && rcl_is_limit_editing( $post->post_date ) ) {
			return false;
		}

		rcl_post_bar_add_item( 'rcl-edit-post', array(
				'url'   => get_edit_post_link( $post->ID ),
				'icon'  => 'fa-pencil-square-o',
				'title' => __( 'Edit', 'wp-recall' )
			)
		);

		return true;
	}

	return false;
}

add_filter( 'pre_update_postdata_rcl', 'rcl_add_taxonomy_in_postdata', 50, 2 );
function rcl_add_taxonomy_in_postdata( $postdata, $data ) {

	$post_type = get_post_types( array( 'name' => $data->post_type ), 'objects' );

	if ( ! $post_type ) {
		return false;
	}

	if ( $data->post_type == 'post' ) {

		$post_type['post']->taxonomies = array( 'category' );

		if ( ! empty( $_POST['tags'] ) ) {
			$postdata['tags_input'] = rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['tags']['post_tag'] ) );//phpcs:ignore
		}
	}

	if ( ! empty( $_POST['cats'] ) && ! empty( $_POST['form_id'] ) ) {

		$FormFields = new Rcl_Public_Form_Fields( $data->post_type, array(
			'form_id' => intval( $_POST['form_id'] )
		) );

		foreach ( $_POST['cats'] as $taxonomy => $terms ) {//phpcs:ignore

			if ( ! isset( $FormFields->taxonomies[ $taxonomy ] ) ) {
				continue;
			}

			if ( ! $FormFields->get_field_prop( 'taxonomy-' . $taxonomy, 'only-child' ) ) {

				$allCats = get_terms( $taxonomy );

				$RclTerms = new Rcl_Edit_Terms_List();
				$terms    = $RclTerms->get_terms_list( $allCats, $terms );
			}

			$postdata['tax_input'][ $taxonomy ] = $terms;
		}
	}

	return $postdata;
}

add_action( 'update_post_rcl', 'rcl_update_postdata_product_tags', 10, 2 );
function rcl_update_postdata_product_tags( $post_id, $postdata ) {

	if ( ! isset( $_POST['tags'] ) || $postdata['post_type'] == 'post' ) {
		return false;
	}

	foreach ( $_POST['tags'] as $taxonomy => $terms ) {//phpcs:ignore
		wp_set_object_terms( $post_id, $terms, $taxonomy );
	}
}

add_action( 'update_post_rcl', 'rcl_unset_postdata_tags', 20, 2 );
function rcl_unset_postdata_tags( $post_id, $postdata ) {

	if ( ! isset( $_POST['tags'] ) ) {

		if ( $taxonomies = get_object_taxonomies( $postdata['post_type'], 'objects' ) ) {

			foreach ( $taxonomies as $taxonomy_name => $obj ) {

				if ( $obj->hierarchical ) {
					continue;
				}

				wp_set_object_terms( $post_id, null, $taxonomy_name );
			}
		}
	}
}

add_action( 'update_post_rcl', 'rcl_set_object_terms_post', 10, 3 );
function rcl_set_object_terms_post( $post_id, $postdata, $update ) {

	if ( $update || ! isset( $postdata['tax_input'] ) || ! $postdata['tax_input'] ) {
		return false;
	}

	foreach ( $postdata['tax_input'] as $taxonomy_name => $terms ) {
		wp_set_object_terms( $post_id, array_map( 'intval', $terms ), $taxonomy_name );
	}
}

add_filter( 'pre_update_postdata_rcl', 'rcl_register_author_post', 10 );
function rcl_register_author_post( $postdata ) {
	global $user_ID;

	if ( rcl_get_option( 'user_public_access_recall' ) || $user_ID ) {
		return $postdata;
	}

	if ( ! $postdata['post_author'] ) {

		$email_new_user = isset( $_POST['email-user'] ) ? sanitize_email( wp_unslash( $_POST['email-user'] ) ) : '';

		if ( $email_new_user ) {

			$random_password = wp_generate_password( $length = 12, $include_standard_special_chars = false );

			$userdata = array(
				'user_pass'    => $random_password,
				'user_login'   => $email_new_user,
				'user_email'   => $email_new_user,
				'display_name' => isset( $_POST['name-user'] ) ? sanitize_user( wp_unslash( $_POST['name-user'] ) ) : ''
			);

			$user_id = rcl_insert_user( $userdata );

			if ( $user_id ) {

				//переназначаем временный массив изображений от гостя юзеру
				rcl_update_temp_media( [ 'user_id' => $user_id ], [
					'user_id'    => 0,
					'session_id' => ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : 'none'
				] );

				//Сразу авторизуем пользователя
				if ( ! rcl_get_option( 'confirm_register_recall' ) ) {
					$creds                  = array();
					$creds['user_login']    = $email_new_user;
					$creds['user_password'] = $random_password;
					$creds['remember']      = true;
					$user                   = wp_signon( $creds );
					$user_ID                = $user_id;
				}

				$postdata['post_author'] = $user_id;
				$postdata['post_status'] = 'pending';
			}
		}
	}

	return $postdata;
}

//удаляем папку с изображениями при удалении поста
add_action( 'delete_post', 'rcl_delete_tempdir_attachments' );
function rcl_delete_tempdir_attachments( $postid ) {
	$dir_path = RCL_UPLOAD_PATH . 'post-media/' . $postid;
	rcl_remove_dir( $dir_path );
}

/* deprecated */
function rcl_form_field( $args ) {
	$field = new Rcl_Form_Fields();

	return $field->get_field( $args );
}

add_action( 'update_post_rcl', 'rcl_send_mail_about_new_post', 10, 3 );
function rcl_send_mail_about_new_post( $post_id, $postData, $update ) {

	if ( $update || rcl_check_access_console() ) {
		return false;
	}

	$title = __( 'New write', 'wp-recall' );
	$email = get_site_option( 'admin_email' );

	$textm = '<p>' . sprintf( __( 'An user added new write on the website "%s"', 'wp-recall' ), get_bloginfo( 'name' ) ) . '.</p>';
	$textm .= '<p>' . __( 'The name of the write', 'wp-recall' ) . ': <a href="' . get_permalink( $post_id ) . '">' . get_the_title( $post_id ) . '</a>' . '</p>';
	$textm .= '<p>' . __( 'The author of the write', 'wp-recall' ) . ': <a href="' . rcl_get_user_url( $postData['post_author'] ) . '">' . get_the_author_meta( 'display_name', $postData['post_author'] ) . '</a>' . '</p>';
	$textm .= '<p>' . __( 'Don\'t forget to check this write, probably it is waiting for your moderation', 'wp-recall' ) . '.</p>';

	rcl_mail( $email, $title, $textm );
}

add_filter( 'rcl_uploader_manager_items', 'rcl_add_post_uploader_image_buttons', 10, 3 );
function rcl_add_post_uploader_image_buttons( $items, $attachment_id, $uploader ) {

	if ( ! in_array( $uploader->uploader_id, array( 'post_uploader', 'post_thumbnail' ) ) ) {
		return $items;
	}

	$is_admin = function_exists( 'get_current_screen' ) && ! wp_doing_ajax() ? 1 : 0;

	$isImage = wp_attachment_is_image( $attachment_id );

	$formFields = new Rcl_Public_Form_Fields( $uploader->post_type, array(
		'form_id' => $uploader->form_id
	) );

	if ( ! $is_admin && ! isset( $_POST['is_wp_admin_page'] ) && $isImage && $uploader->uploader_id == 'post_uploader' && $formFields->is_active_field( 'post_thumbnail' ) ) {

		$items[] = array(
			'icon'    => 'fa-image',
			'title'   => __( 'Appoint a thumbnail', 'wp-recall' ),
			'onclick' => 'rcl_set_post_thumbnail(' . $attachment_id . ',' . $uploader->post_parent . ',this);return false;'
		);
	}

	$addGallery = true;

	if ( $formFields->is_active_field( 'post_uploader' ) ) {

		$field = $formFields->get_field( 'post_uploader' );

		if ( $field->isset_prop( 'gallery' ) ) {
			$addGallery = $field->get_prop( 'gallery' );
		}
	}

	if ( $isImage && $addGallery ) {

		$postGallery  = get_post_meta( $uploader->post_parent, 'rcl_post_gallery', 1 );
		$valueGallery = ( $postGallery && in_array( $attachment_id, $postGallery ) ) ? $attachment_id : '';

		$items[] = array(
			'icon'    => ( $postGallery && in_array( $attachment_id, $postGallery ) ) ? 'fa-toggle-on' : 'fa-toggle-off',
			'class'   => 'rcl-switch-gallery-button-' . $attachment_id,
			'title'   => __( 'Output in a gallery', 'wp-recall' ),
			'content' => '<input type="hidden" id="rcl-post-gallery-attachment-' . $attachment_id . '" name="rcl-post-gallery[]" value="' . $valueGallery . '">',
			'onclick' => 'rcl_switch_attachment_in_gallery(' . $attachment_id . ',this);return false;'
		);
	}

	return $items;
}

function rcl_get_post_gallery( $gallery_id, $attachment_ids ) {

	return rcl_get_image_gallery( array(
		'id'           => 'rcl-post-gallery-' . $gallery_id,
		'center_align' => true,
		'attach_ids'   => $attachment_ids,
		//'width' => 500,
		'height'       => 350,
		'slides'       => array(
			'slide' => 'large',
			'full'  => 'large'
		),
		'navigator'    => array(
			'thumbnails' => array(
				'width'  => 50,
				'height' => 50,
				'arrows' => true
			)
		)
	) );
}
