<?php

if ( is_admin() ) {
	require_once 'admin/index.php';
}

require_once 'classes/rcl-groups-query.php';
require_once 'groups-init.php';
require_once 'groups-core.php';
require_once 'shortcodes.php';
require_once 'groups-widgets.php';

if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
	require_once 'groups-public.php';
}

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_groups_scripts', 10 );
endif;
function rcl_groups_scripts() {
	rcl_enqueue_style( 'rcl-groups', rcl_addon_url( 'style.css', __FILE__ ) );
	rcl_enqueue_script( 'rcl-groups', rcl_addon_url( 'js/scripts.js', __FILE__ ) );
}

add_filter( 'rcl_init_js_variables', 'rcl_init_js_groups_variables', 10 );
function rcl_init_js_groups_variables( $data ) {
	$data['groups']['avatar_size'] = rcl_get_option( 'group_avatar_weight', 1024 );

	return $data;
}

add_action( 'init', 'rcl_register_rating_group_type' );
function rcl_register_rating_group_type() {

	if ( ! function_exists( 'rcl_register_rating_type' ) ) {
		return false;
	}

	rcl_register_rating_type( array(
			'post_type' => 'post-group',
			'type_name' => __( 'Groups records', 'wp-recall' ),
			'style'     => true
		)
	);
}

add_filter( 'page_rewrite_rules', 'rcl_group_set_rewrite_rules' );
function rcl_group_set_rewrite_rules( $rules ) {

	if ( ! rcl_get_option( 'group-output' ) ) {
		return $rules;
	}

	$page = get_post( rcl_get_option( 'group-page' ) );

	if ( ! $page ) {
		return $rules;
	}

	$rules[ $page->post_name . '/([^/]+)/?$' ] = 'index.php?pagename=' . $page->post_name . '&group-id=$matches[1]';

	return $rules;
}

add_filter( 'query_vars', 'rcl_group_set_query_vars' );
function rcl_group_set_query_vars( $vars ) {

	$vars[] = 'group-id';

	return apply_filters( 'rcl_group_query_vars', $vars );
}

add_action( 'parse_query', 'rcl_group_add_seo_filters', 10 );
function rcl_group_add_seo_filters() {
	global $wp_query;

	if ( ! rcl_get_option( 'group-output' ) ) {
		return false;
	}

	if ( ! $wp_query ) {
		return false;
	}

	$groupPage = rcl_get_option( 'group-page' );

	$isGroupPage = ( get_query_var( 'page_id' ) == $groupPage || $wp_query->queried_object_id == $groupPage ) ? true : false;

	if ( ! $wp_query->is_page || ! $isGroupPage ) {
		return false;
	}

	add_filter( 'the_title', 'rcl_group_setup_page_title', 30, 2 );
	add_filter( 'document_title_parts', 'rcl_group_replace_title', 30 );
	add_filter( 'wp_title', 'rcl_group_replace_title', 30 );

	add_filter( 'get_canonical_url', 'rcl_group_replace_canonical_url', 30 );
	add_filter( 'get_shortlink', 'rcl_group_replace_shortlink', 30 );

	add_filter( 'aioseo_canonical_url', 'rcl_group_replace_canonical_url', 30 );
	add_filter( 'aioseo_description', 'rcl_group_replace_description', 30 );
	add_filter( 'aioseo_title_page', 'rcl_group_replace_title', 30 );

	add_filter( 'wpseo_title', 'rcl_group_replace_title', 30 );
	add_filter( 'wpseo_canonical', 'rcl_group_replace_canonical_url', 30 );
	add_filter( 'wpseo_metadesc', 'rcl_group_replace_description', 30 );
}

function rcl_group_replace_title( $title ) {
	global $rcl_group;

	if ( ! $rcl_group ) {
		return $title;
	}

	if ( $rcl_group->name ) {

		if ( is_array( $title ) ) {
			$title = array( 'title' => $rcl_group->name );
		} else {
			$title = $rcl_group->name;
		}
	}

	return $title;
}

function rcl_group_setup_page_title( $title, $post_id ) {
	global $rcl_group;

	$post_type = get_post_type( $post_id );

	$forum_page = rcl_get_option( 'group-page', '' );

	if ( $post_type == 'nav_menu_item' || $post_id != $forum_page || ! $rcl_group || ! in_the_loop() ) {
		return $title;
	}

	$groupName = $rcl_group->name;

	if ( $groupName ) {
		return $rcl_group->name;
	}
}

function rcl_group_replace_shortlink( $url ) {
	global $rcl_group;

	if ( ! $rcl_group ) {
		return $url;
	}

	$groupPage = rcl_get_option( 'group-page' );

	$shortUrl = home_url( '?p=' . $groupPage . '&group-id=' . $rcl_group->term_id );

	if ( $shortUrl ) {
		$url = $shortUrl;
	}

	return $url;
}

function rcl_group_replace_canonical_url( $url ) {
	global $rcl_group;

	if ( ! $rcl_group ) {
		return $url;
	}

	$groupUrl = rcl_get_group_permalink( $rcl_group->term_id );

	if ( $groupUrl ) {
		$url = $groupUrl;
	}

	return $url;
}

function rcl_group_replace_description( $descr ) {
	global $rcl_group;

	if ( ! $rcl_group ) {
		return $descr;
	}

	$description = get_term_field( 'description', $rcl_group->term_id, 'groups' );

	if ( $description ) {
		$descr = $description;
	}

	return $descr;
}

add_action( 'delete_user', 'rcl_group_delete_user_in_groups' );
function rcl_group_delete_user_in_groups( $user_id ) {

	$groups = rcl_get_groups_users( array( 'user_id' => $user_id, 'number' => - 1 ) );

	if ( ! $groups ) {
		return false;
	}

	foreach ( $groups as $group ) {
		rcl_group_remove_user( $user_id, $group->group_id );
	}
}

//обновление кеша вкладки групп ее админа
add_action( 'rcl_create_group', 'rcl_tab_groups_remove_cache', 10 );
add_action( 'rcl_pre_delete_group', 'rcl_tab_groups_remove_cache', 10 );
add_action( 'rcl_group_add_user', 'rcl_tab_groups_remove_cache', 10 );
add_action( 'rcl_group_remove_user', 'rcl_tab_groups_remove_cache', 10 );
function rcl_tab_groups_remove_cache( $groupdata ) {

	if ( rcl_get_option( 'use_cache' ) ) {

		if ( is_array( $groupdata ) ) {
			$user_id = $groupdata['user_id'];
		} else {
			$group_id = $groupdata;
			$group    = rcl_get_group( $group_id );
			$user_id  = $group->admin_id;
		}

		$string = rcl_get_tab_permalink( $user_id, 'groups' );

		rcl_delete_file_cache( $string );
	}
}

add_action( 'init', 'rcl_add_postlist_group', 10 );
function rcl_add_postlist_group() {
	rcl_postlist( 'group', 'post-group', __( 'Groups records', 'wp-recall' ), array( 'order' => 40 ) );
}

add_action( 'init', 'rcl_add_tab_groups' );
function rcl_add_tab_groups() {

	rcl_tab(
		array(
			'id'       => 'groups',
			'name'     => __( 'Groups', 'wp-recall' ),
			'supports' => array( 'ajax', 'cache' ),
			'public'   => 1,
			'icon'     => 'fa-group',
			'content'  => array(
				array(
					'id'       => 'all-groups',
					'name'     => __( 'All groups', 'wp-recall' ),
					'icon'     => 'fa-group',
					'callback' => array(
						'name' => 'rcl_tab_groups',
						'args' => array( 'user_id' )
					)
				),
				array(
					'id'       => 'admin-groups',
					'name'     => __( 'Groups created', 'wp-recall' ),
					'icon'     => 'fa-cogs',
					'callback' => array(
						'name' => 'rcl_tab_groups',
						'args' => array( 'admin_id' )
					)
				)
			)
		)
	);
}

function rcl_tab_groups( $type_account = 'user_id' ) {

	global $user_ID, $user_LK;

	$content = '';

	if ( rcl_is_office( $user_ID ) ) {

		$group_can_public = rcl_get_option( 'public_group_access_recall' );
		if ( $group_can_public ) {
			$userdata = get_userdata( $user_ID );
			if ( $userdata->user_level >= $group_can_public ) {
				$public_groups = true;
			} else {
				$public_groups = false;
			}
		} else {
			$public_groups = true;
		}

		if ( $public_groups ) {
			$content = '<div id="create-group">'
			           . '<form method="post">'
			           . '<div class="form-field">'
			           . '<input type="text" required placeholder="' . __( 'Enter the name of the new group', 'wp-recall' ) . '" name="group_name">'
			           . rcl_get_button( array(
					'onclick' => 'rcl_send_form_data("rcl_ajax_create_group", this);return false;',
					'label'   => __( 'Create', 'wp-recall' ),
					'submit'  => true
				) )
			           . '</div>'
			           . wp_nonce_field( 'rcl-group-create', '_wpnonce', true, false )
			           . '</form>'
			           . '</div>';
		}
	}

	$content .= rcl_get_grouplist( array( 'filters' => 1, 'search_form' => 0, $type_account => $user_LK ) );

	return $content;
}

add_action( 'init', 'rcl_register_default_group_sidebars', 10 );
function rcl_register_default_group_sidebars() {

	rcl_register_group_area(
		array(
			'name' => __( 'Header', 'wp-recall' ),
			'id'   => 'header'
		)
	);

	rcl_register_group_area(
		array(
			'name' => __( 'Sidebar', 'wp-recall' ),
			'id'   => 'sidebar'
		)
	);

	rcl_register_group_area(
		array(
			'name' => __( 'Main', 'wp-recall' ),
			'id'   => 'content'
		)
	);

	rcl_register_group_area(
		array(
			'name' => __( 'Footer', 'wp-recall' ),
			'id'   => 'footer'
		)
	);
}

function rcl_get_link_group_tag( $content ) {
	global $post, $user_ID, $rcl_group;
	if ( $post->post_type != 'post-group' ) {
		return $content;
	}

	$group_data = get_the_terms( $post->ID, 'groups' );
	$group_id   = false;
	foreach ( ( array ) $group_data as $data ) {
		if ( $data->parent == 0 ) {
			$group_id = $data->term_id;
		} else {
			$tag = $data;
		}
	}

	if ( ! isset( $tag ) || ! $tag ) {
		return $content;
	}

	if ( doing_filter( 'the_excerpt' ) ) {

		if ( ! $rcl_group ) {
			$rcl_group = rcl_get_group( $group_id );
		}

		if ( $rcl_group->group_status == 'closed' ) {
			if ( $rcl_group->admin_id != $user_ID ) {

				$user_status = rcl_get_group_user_status( $user_ID, $rcl_group->term_id );

				if ( ! $user_status ) {
					$content = rcl_close_group_post_content();
				}
			}
		}
	}

	$cat = '<p class="post-group-meta"><i class="rcli fa-folder-open rcl-icon"></i>' . __( 'Group categories', 'wp-recall' ) . ': <a href="' . rcl_format_url( rcl_get_group_permalink( $group_id ) ) . 'group-tag=' . $tag->slug . '">' . $tag->name . '</a></p>';

	return $cat . $content;
}

function rcl_init_get_link_group_tag() {
	if ( is_single() ) {
		add_filter( 'the_content', 'rcl_get_link_group_tag', 80 );
	} else {
		add_filter( 'the_excerpt', 'rcl_get_link_group_tag', 80 );
	}
}

add_action( 'wp', 'rcl_init_get_link_group_tag', 10 );
function rcl_init_namegroup() {
	if ( is_single() ) {
		add_filter( 'the_content', 'rcl_add_namegroup', 80 );
	}
	if ( is_search() ) {
		add_filter( 'the_excerpt', 'rcl_add_namegroup', 80 );
	}
}

add_action( 'wp', 'rcl_init_namegroup', 10 );
function rcl_add_namegroup( $content ) {
	global $post;
	if ( get_post_type( $post->ID ) != 'post-group' ) {
		return $content;
	}

	$group = rcl_get_group_by_post( $post->ID );

	if ( ! $group ) {
		return $content;
	}

	$group_link = '<p class="post-group-meta"><i class="rcli fa-users rcl-icon"></i><span>' . __( 'Published in group', 'wp-recall' ) . '</span>: <a href="' . rcl_get_group_permalink( $group->term_id ) . '">' . $group->name . '</a></p>';

	return $group_link . $content;
}

//Создаем новую группу
function rcl_new_group() {

	global $user_ID;

	$name_group = isset( $_POST['rcl_group']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['rcl_group']['name'] ) ) : '';
	$group_id   = rcl_create_group( array( 'name' => $name_group, 'admin_id' => $user_ID ) );

	if ( ! $group_id ) {
		rcl_notice_text( __( 'Group creation failed', 'wp-recall' ), 'error' );
	} else {
		wp_safe_redirect( rcl_get_group_permalink( $group_id ) );
		exit;
	}
}

add_action( 'init', 'rcl_init_group_create' );
function rcl_init_group_create() {
	if ( isset( $_POST['rcl_group'], $_POST['_wpnonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'rcl-group-create' ) ) {
			return false;
		}
		add_action( 'wp', 'rcl_new_group' );
	}
}

rcl_ajax_action( 'rcl_ajax_create_group' );
function rcl_ajax_create_group() {
	global $user_ID;

	rcl_verify_ajax_nonce();

	$group_name = isset( $_POST['group_name'] ) ? sanitize_text_field( wp_unslash( $_POST['group_name'] ) ) : '';

	if ( ! $group_name ) {
		wp_send_json( array(
			'error' => __( 'Enter the name', 'wp-recall' )
		) );
	}

	if ( is_numeric( $group_name ) ) {
		wp_send_json( array(
			'error' => __( 'Specify the name of new group as string', 'wp-recall' )
		) );
	}

	do_action( 'rcl_pre_create_group' );

	$group_id = rcl_create_group( array(
		'name'     => $group_name,
		'admin_id' => $user_ID
	) );

	if ( ! $group_id ) {
		wp_send_json( array(
			'error' => __( 'Group creation failed', 'wp-recall' )
		) );
	}

	do_action( 'rcl_ajax_create_group', $group_id );

	wp_send_json( array(
		'success'  => __( 'New group is successfully created!', 'wp-recall' ),
		'redirect' => rcl_get_group_permalink( $group_id )
	) );
}

add_filter( 'rcl_group_thumbnail', 'rcl_group_add_thumb_buttons' );
function rcl_group_add_thumb_buttons( $content ) {
	global $rcl_group;

	if ( ! rcl_is_group_can( 'admin' ) || rcl_get_option( 'group_avatar_weight', 1024 ) <= 0 ) {
		return $content;
	}

	$uploder = new Rcl_Uploader( 'rcl_group_avatar', array(
		'multiple'    => 0,
		'crop'        => 1,
		'filetitle'   => 'rcl-group-avatar-' . $rcl_group->term_id,
		'filename'    => 'rcl-group-avatar-' . $rcl_group->term_id,
		'group_id'    => $rcl_group->term_id,
		'image_sizes' => array(
			array(
				'height' => 300,
				'width'  => 300,
				'crop'   => 1
			)
		),
		'resize'      => array( 300, 300 ),
		'min_height'  => 300,
		'min_width'   => 300,
		'max_size'    => rcl_get_option( 'group_avatar_weight', 1024 )
	) );

	$content .= '<div id="group-avatar-upload">
            <span id="file-upload" class="rcli fa-download">
                ' . $uploder->get_input() . '
            </span>
	</div>';

	return $content;
}

add_action( 'rcl_upload', 'rcl_group_avatar_upload', 10, 2 );
function rcl_group_avatar_upload( $uploads, $class ) {
	if ( $class->uploader_id != 'rcl_group_avatar' ) {
		return;
	}

	if ( $avatar_id = rcl_get_group_option( $class->group_id, 'avatar_id' ) ) {
		wp_delete_attachment( $avatar_id );
	}

	rcl_update_group_option( $class->group_id, 'avatar_id', $uploads['id'] );

	do_action( 'rcl_group_avatar_upload', $class->group_id, $uploads['id'] );
}

add_action( 'wp', 'rcl_group_actions' );
function rcl_group_actions() {
	global $user_ID, $rcl_group;

	if ( ! isset( $_POST['group-action'] ) || ! isset( $_POST['_wpnonce'] ) || ! isset( $_POST['group-submit'] ) ) {
		return false;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'group-action-' . $user_ID ) ) {
		return false;
	}

	switch ( $_POST['group-action'] ) {
		case 'leave':
			rcl_group_remove_user( $user_ID, $rcl_group->term_id );
			break;
		case 'join':
			rcl_group_add_user( $user_ID, $rcl_group->term_id );
			break;
		case 'ask':
			rcl_group_add_request_for_membership( $user_ID, $rcl_group->term_id );
			break;
		case 'update':
			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$args             = isset( $_POST['group-options'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['group-options'] ) ) : [];
			$args['group_id'] = $rcl_group->term_id;
			rcl_update_group( $args );
			break;
		case 'update-widgets':
			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$data = isset( $_POST['data'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['data'] ) ) : [];
			rcl_update_group_widgets( $rcl_group->term_id, $data );
			break;
	}

	wp_safe_redirect( rcl_get_group_permalink( $rcl_group->term_id ) );
	exit;
}

function rcl_get_group_options( $group_id ) {
	global $rcl_group, $user_ID;

	$default_role = rcl_get_group_option( $group_id, 'default_role' );
	$category     = rcl_get_group_option( $group_id, 'category' );

	$category = ( is_array( $category ) ) ? implode( ', ', $category ) : $category;

	$fields = [
		'name'         => [
			'type'     => 'text',
			'slug'     => 'name',
			'title'    => __( 'Group name', 'wp-recall' ),
			'default'  => $rcl_group->name,
			'required' => 1
		],
		'description'  => [
			'type'    => 'textarea',
			'slug'    => 'description',
			'title'   => __( 'Description', 'wp-recall' ),
			'default' => esc_html( strip_tags( rcl_get_group_description( $group_id ) ) )
		],
		'status'       => [
			'type'    => 'radio',
			'slug'    => 'status',
			'title'   => __( 'Group status', 'wp-recall' ),
			'values'  => [
				'open'   => __( 'Open group', 'wp-recall' ),
				'closed' => __( 'Closed group', 'wp-recall' )
			],
			'default' => $rcl_group->group_status
		],
		'can_register' => [
			'type'    => 'checkbox',
			'slug'    => 'can_register',
			'title'   => __( 'Membership', 'wp-recall' ),
			'values'  => [
				1 => __( 'Registration allowed', 'wp-recall' )
			],
			'default' => array( 1 => rcl_get_group_option( $group_id, 'can_register' ) )
		],
		'default_role' => [
			'type'    => 'radio',
			'slug'    => 'default_role',
			'title'   => __( 'New user role', 'wp-recall' ),
			'values'  => [
				'reader' => __( 'Visitor', 'wp-recall' ),
				'author' => __( 'Author', 'wp-recall' )
			],
			'default' => $default_role
		],
		'category'     => [
			'type'    => 'textarea',
			'slug'    => 'category',
			'title'   => sprintf( '%s <small>(%s)</small>', __( 'Group categories', 'wp-recall' ), __( 'separate by commas', 'wp-recall' ) ),
			'default' => $category
		],
	];

	$fields = apply_filters( 'rcl_group_options_fields', $fields, $group_id );

	foreach ( $fields as $k => $field ) {
		$fields[ $k ]['name'] = 'group-options[' . $field['slug'] . ']';
	}

	require_once RCL_PATH . 'classes/class-rcl-form.php';

	$form = new Rcl_Form( [
		'fields' => $fields
	] );

	$content = '<div id="group-options">'
	           . '<h3>' . __( 'Group settings', 'wp-recall' ) . '</h3>'
	           . '<form method="post">';

	$content .= apply_filters( 'rcl_group_options', $form->get_fields_list(), $group_id );

	$content .= '<div class="group-option">';
	$content .= rcl_get_button( [
		'icon'   => 'fa-floppy-o',
		'label'  => __( 'Save settings', 'wp-recall' ),
		'submit' => true
	] );
	$content .= '<input type="hidden" name="group-action" value="update">'
	            . '<input type="hidden" name="group-submit" value="1">'
	            . wp_nonce_field( 'group-action-' . $user_ID, '_wpnonce', true, false )
	            . '</div>'
	            . '</form>'
	            . '</div>';

	return $content;
}

function rcl_get_group_requests_content( $group_id ) {

	$requests = rcl_get_group_option( $group_id, 'requests_group_access' );

	$content = '<h3>' . __( 'Requests for access to the group', 'wp-recall' ) . '</h3>';

	if ( ! $requests ) {
		$content .= rcl_get_notice( [ 'text' => __( 'No requests', 'wp-recall' ) ] );

		return $content;
	}

	add_action( 'rcl_user_description', 'rcl_add_group_access_button' );

	$content .= rcl_get_userlist( array(
		'include' => implode( ',', $requests ),
		'filters' => 0,
		'orderby' => 'time_action',
		'data'    => 'rating_total,posts_count,comments_count,description,user_registered'
	) );

	return $content;
}

function rcl_add_group_access_button() {
	global $rcl_user;
	echo '<div class="group-request" data-user="' . esc_attr( $rcl_user->ID ) . '">';
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo rcl_get_button( array(
		'label' => esc_html__( 'Approve request', 'wp-recall' ),
		'icon'  => 'fa-thumbs-up',
		'class' => array( 'apply-request' ),
		'data'  => array(
			'request' => 1
		)
	) );
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo rcl_get_button( array(
		'label' => esc_html__( 'Reject request', 'wp-recall' ),
		'icon'  => 'fa-thumbs-down',
		'class' => array( 'apply-request' ),
		'data'  => array(
			'request' => 0
		)
	) );
	echo '</div>';
}

function rcl_add_group_user_options() {
	global $rcl_user, $rcl_group, $user_ID;

	if ( $user_ID == $rcl_user->ID ) {
		return false;
	}
	if ( $rcl_user->ID == $rcl_group->admin_id ) {
		return false;
	}

	$group_roles = rcl_get_group_roles();

	echo '<div id="options-user-' . esc_attr( $rcl_user->ID ) . '" class="group-request" data-user="' . esc_attr( $rcl_user->ID ) . '">';

	echo '<div class="group-user-option">';
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo rcl_get_group_callback( 'rcl_group_ajax_delete_user', esc_html__( 'Delete', 'wp-recall' ) );
	echo '</div>';

	echo '<div class="group-user-option">';
	echo esc_html__( 'User status', 'wp-recall' ) . ' <select name="user_role">';
	foreach ( $group_roles as $role => $data ) {
		echo '<option value="' . esc_attr( $role ) . '" ' . selected( $rcl_user->user_role, $role, false ) . '>' . esc_html( $data['role_name'] ) . '</option>';
	}
	echo '</select>';
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo rcl_get_group_callback( 'rcl_group_ajax_update_role', esc_html__( 'Save', 'wp-recall' ), array( 'user_role' ) );
	echo '</div>';

	echo '</div>';
}

rcl_ajax_action( 'rcl_apply_group_request' );
function rcl_apply_group_request() {
	global $rcl_group;

	rcl_verify_ajax_nonce();

	$user_id  = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
	$apply    = isset( $_POST['apply'] ) ? intval( $_POST['apply'] ) : 0;
	$group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;

	$rcl_group = rcl_get_group( $group_id );

	if ( ! rcl_is_group_can( 'admin' ) ) {
		return false;
	}

	$requests = rcl_get_group_option( $group_id, 'requests_group_access' );

	$key = array_search( $user_id, $requests );

	if ( ! $requests || false === $key ) {
		return false;
	}

	unset( $requests[ $key ] );

	if ( $apply ) {

		$subject  = __( 'Request access to the group approved!', 'wp-recall' );
		$textmail = sprintf(
			'<h3>%s "' . $rcl_group->name . '"!</h3>
                <p>%s</p>
                <p>%s.</p>
                <p>%s:</p>
                <p>' . rcl_get_group_permalink( $group_id ) . '</p>', __( 'Welcome to the group', 'wp-recall' ), sprintf( __( 'Congratulations , your access request to a private group on "%s" website has been approved', 'wp-recall' ), get_bloginfo( 'name' ) ), __( 'Now you can take part in the life of the group as its full participant.', 'wp-recall' ), __( 'You can visit the group by clicking on the link', 'wp-recall' )
		);

		rcl_group_add_user( $user_id, $group_id );

		$log['result'] = '<span class="success">' . __( 'Request approved', 'wp-recall' ) . '</span>';
	} else {

		$log['result'] = '<span class="error">' . __( 'Request rejected', 'wp-recall' ) . '</span>';
		$subject       = __( 'Access request to the group has been rejected.', 'wp-recall' );
		$textmail      = sprintf( '<p>' . __( 'We are sorry, but your request to access a private group "%s" on the site "%s" has been rejected by its administrator', 'wp-recall' ) . '.</p>', $rcl_group->name, get_bloginfo( 'name' )
		);
	}

	$user_email = get_the_author_meta( 'user_email', $user_id );
	rcl_mail( $user_email, $subject, $textmail );

	rcl_update_group_option( $group_id, 'requests_group_access', $requests );

	$log['user_id'] = $user_id;

	wp_send_json( $log );
}

//исключаем из поиска публикации из закрытых групп
add_action( 'pre_get_posts', 'rcl_search_filter_closed_posts', 10 );
function rcl_search_filter_closed_posts( $query ) {

	if ( ! is_admin() && $query->is_main_query() ) {

		global $user_ID;

		if ( $query->is_search ) {
			$groups = rcl_get_closed_groups( $user_ID );

			if ( ! $groups ) {
				return $query;
			}

			$query->set( 'tax_query', array(
				array(
					'taxonomy' => 'groups',
					'field'    => 'id',
					'terms'    => $groups,
					'operator' => 'NOT IN'
				)
			) );
		}
	}

	return $query;
}

//исключаем из фида комментарии из закрытых групп
add_filter( 'rcl_feed_comments_query', 'rcl_add_feed_ignored_posts_in_comments', 10 );
function rcl_add_feed_ignored_posts_in_comments( $query ) {
	global $user_ID;

	$ignored_posts = rcl_get_closed_group_posts( $user_ID );

	if ( $ignored_posts ) {
		$query['where'][] = "wp_comments.comment_post_ID NOT IN (" . implode( ',', $ignored_posts ) . ")";
	}

	return $query;
}

//исключаем из фида публикации из закрытых групп
add_filter( 'rcl_feed_posts_query', 'rcl_add_feed_group_query', 10, 2 );
function rcl_add_feed_group_query( $query, int $user_id ) {
	global $wpdb;

	// phpcs:disable
	$groups = $wpdb->get_col( "SELECT groups_users.group_id, jgroups.ID "
	                          . "FROM " . RCL_PREF . "groups_users AS groups_users "
	                          . "INNER JOIN " . RCL_PREF . "groups AS jgroups ON groups_users.user_id=jgroups.admin_id "
	                          . "WHERE (groups_users.user_id='$user_id' OR jgroups.admin_id='$user_id') "
	                          . "GROUP BY groups_users.group_id, jgroups.ID" );
	// phpcs:enable

	if ( $groups ) {

		$groups = array_unique( $groups );

		$feeds = new Rcl_Feed_Query();

		$authors_ignor = $feeds->get_col( array(
			'feed_type'   => 'author',
			'user_id'     => $user_id,
			'feed_status' => 0,
			'fields'      => array( 'object_id' )
		) );

		$authors_ignor[] = $user_id;

		// phpcs:disable
		$objects = $wpdb->get_col( "SELECT term_relationships.object_id "
		                           . "FROM $wpdb->term_relationships AS term_relationships "
		                           . "INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON term_relationships.term_taxonomy_id=term_taxonomy.term_taxonomy_id "
		                           . "INNER JOIN $wpdb->posts AS posts ON term_relationships.object_id=posts.ID "
		                           . "WHERE term_taxonomy.term_id IN (" . implode( ',', $groups ) . ") "
		                           . "AND posts.post_status = 'publish'" );
		// phpcs:enable

		if ( $objects ) {
			$query['where_or'][] = "(wp_posts.ID IN (" . implode( ',', $objects ) . ") AND wp_posts.post_author NOT IN (" . implode( ',', $authors_ignor ) . "))";
		}
	}

	return $query;
}

add_action( 'delete_user', 'rcl_group_delete_requests_after_delete_user', 10 );
function rcl_group_delete_requests_after_delete_user( $removed_user_id ) {

	$groups_requests = RQ::tbl( new Rcl_Groups_Options_Query() )
	                     ->select( [ 'option_value', 'group_id' ] )
	                     ->where( [
		                     'option_key' => 'requests_group_access',
	                     ] )->get_results();

	if ( ! $groups_requests ) {
		return;
	}

	foreach ( $groups_requests as $_requests ) {

		$requests_users = $_requests->option_value;
		$group_id       = $_requests->group_id;

		$need_update = false;

		/**
		 * Если в группу нет заявок - ничего не делаем
		 */
		if ( ! $requests_users ) {
			continue;
		}

		/**
		 * Если есть заявки - ищем пользователя
		 */
		foreach ( $requests_users as $key => $u_id ) {

			if ( $u_id == $removed_user_id ) {
				$need_update = true;
				unset( $requests_users[ $key ] );
			}
		}

		/**
		 * Если заявок от пользователя не было - ничего не делаем
		 */
		if ( ! $need_update ) {
			continue;
		}

		/**
		 * Если заявок не осталось - удалим опцию
		 */
		if ( ! $requests_users ) {
			rcl_delete_group_option( $group_id, 'requests_group_access' );
		}

		/**
		 * Обновим список заявок
		 */
		if ( $requests_users ) {
			rcl_update_group_option( $group_id, 'requests_group_access', $requests_users );
		}
	}

}
