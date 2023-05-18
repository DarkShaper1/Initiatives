<?php

function rcl_get_groups( $args ) {
	return RQ::tbl( new Rcl_Groups_Query() )
	         ->setup_termdata()
	         ->parse( $args )
	         ->get_results();
}

function rcl_get_groups_users( $args ) {
	return RQ::tbl( new Rcl_Groups_Users_Query() )
	         ->parse( $args )
	         ->get_results();
}

function rcl_get_groups_options( $args ) {
	return RQ::tbl( new Rcl_Groups_Options_Query() )
	         ->parse( $args )
	         ->get_results();
}

function rcl_group_init( $group_id = false ) {
	global $rcl_group;

	if ( ! $group_id ) {
		return false;
	}

	$rcl_group = rcl_get_group( $group_id );

	$rcl_group->current_user = rcl_group_current_user_status();

	$rcl_group->single_group = 1;

	if ( rcl_is_group_can( 'admin' ) || current_user_can( 'edit_others_posts' ) ) {
		rcl_sortable_scripts();
	}

	do_action( 'rcl_group_init', $rcl_group );

	return $rcl_group;
}

function rcl_group_is_allowed_callback( $callback ) {

	$allowedCallbacks = apply_filters( 'rcl_group_allowed_callback', [
		'rcl_get_group_options',
		'rcl_get_group_widgets',
		'rcl_get_group_requests_content',
		'rcl_get_group_users',
		'rcl_group_ajax_delete_user',
		'rcl_group_ajax_update_role'
	] );

	return in_array( $callback, $allowedCallbacks );

}

/* deprecated */
function add_post_in_group() {
	rcl_single_group();
}

function rcl_single_group() {
	echo rcl_get_single_group();//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

function rcl_get_single_group() {
	global $rcl_group;

	do_action( 'rcl_group_before' );

	rcl_dialog_scripts();

	$admin = ( rcl_is_group_can( 'admin' ) || rcl_check_access_console() ) ? 1 : 0;

	$class = ( $admin ) ? 'class="admin-view"' : '';

	$content = '<div id="rcl-group" data-group="' . $rcl_group->term_id . '" ' . $class . '>';

	if ( $admin ) {
		$content .= rcl_group_admin_panel();
	}

	$content .= '<div id="group-popup"></div>';

	$content .= rcl_get_include_template( 'single-group.php', __FILE__ );

	$content .= '</div>';

	return $content;
}

function rcl_create_group( $groupdata ) {
	global $wpdb;

	$groupdata = wp_parse_args( $groupdata, [
		'group_status' => 'open',
		'group_date'   => current_time( 'mysql' )
	] );

	$args = array(
		'alias_of'    => ''
	,
		'description' => ''
	,
		'parent'      => 0
	,
		'slug'        => ''
	);

	$data = wp_insert_term( $groupdata['name'], 'groups', $args );

	if ( isset( $data->error_data ) ) {

		$term = get_term( ( int ) $data->error_data['term_exists'], 'groups' );

		for ( $a = 2; $a < 10; $a ++ ) {
			$args['slug'] = $term->slug . '-' . $a;
			$data         = wp_insert_term( $groupdata['name'], 'groups', $args );
			if ( ! isset( $data->error_data ) ) {
				break;
			}
		}
	}

	if ( ! $data || isset( $data->errors ) ) {
		return false;
	}

	$group_id = $data['term_id'];

	$result = $wpdb->insert(
		RCL_PREF . 'groups', array(
			'ID'           => $group_id,
			'admin_id'     => $groupdata['admin_id'],
			'group_status' => $groupdata['group_status'],
			'group_date'   => $groupdata['group_date']
		)
	);

	if ( ! $result ) {
		return false;
	}

	rcl_update_group_option( $group_id, 'can_register', 1 );
	rcl_update_group_option( $group_id, 'default_role', 'author' );

	do_action( 'rcl_create_group', $group_id );

	return $group_id;
}

function rcl_update_group( $args ) {
	global $wpdb;

	if ( isset( $args['name'] ) ) {
		$wpdb->update( $wpdb->prefix . 'terms', array( 'name' => esc_html( $args['name'] ) ), array( 'term_id' => absint( $args['group_id'] ) )
		);
	}

	if ( isset( $args['description'] ) ) {
		$wpdb->update( $wpdb->prefix . 'term_taxonomy', array( 'description' => esc_html( stripslashes_deep( esc_html( $args['description'] ) ) ) ), array( 'term_id' => absint( $args['group_id'] ) )
		);
	}

	if ( isset( $args['status'] ) ) {
		$wpdb->update( RCL_PREF . 'groups', array( 'group_status' => sanitize_key( $args['status'] ) ), array( 'ID' => absint( $args['group_id'] ) )
		);
	}

	if ( isset( $args['default_role'] ) ) {
		rcl_update_group_option( absint( $args['group_id'] ), 'default_role', sanitize_key( $args['default_role'] ) );
	}

	$category = array_map( 'trim', explode( ',', $args['category'] ) );
	rcl_update_group_option( absint( $args['group_id'] ), 'category', $category );

	$can_register = ( ! isset( $args['can_register'] ) ) ? 0 : 1;
	rcl_update_group_option( absint( $args['group_id'] ), 'can_register', $can_register );

	do_action( 'rcl_update_group', $args );
}

function rcl_delete_group( $group_id ) {
	rcl_delete_term_groups( $group_id, $group_id, 'groups' );
}

add_action( 'delete_term', 'rcl_delete_term_groups', 10, 3 );
function rcl_delete_term_groups( $term_id, $tt_id, $taxonomy ) {
	if ( ! $taxonomy || $taxonomy != 'groups' ) {
		return false;
	}
	global $wpdb;

	do_action( 'rcl_pre_delete_group', $term_id );

	$imade_id = rcl_get_group_option( $term_id, 'avatar_id' );
	wp_delete_attachment( $imade_id, true );
	//phpcs:disable
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . RCL_PREF . "groups_options WHERE group_id = '%d'", $term_id ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . RCL_PREF . "groups_users WHERE group_id = '%d'", $term_id ) );
	$wpdb->query( $wpdb->prepare( "DELETE FROM " . RCL_PREF . "groups WHERE ID = '%d'", $term_id ) );
	//phpcs:enable
}

function rcl_register_group_area( $contents ) {
	global $rcl_group_area;
	$rcl_group_area[] = $contents;
}

function rcl_remove_group_area( $area_id ) {
	global $rcl_group_area;
	foreach ( $rcl_group_area as $key => $area ) {
		if ( isset( $area['id'] ) && $area['id'] == $area_id ) {
			unset( $rcl_group_area[ $key ] );

			return true;
		}
	}

	return false;
}

function rcl_is_group_area( $area_id ) {
	global $rcl_group_area;
	foreach ( $rcl_group_area as $key => $area ) {
		if ( isset( $area['id'] ) && $area['id'] == $area_id ) {
			return true;
		}
	}

	return false;
}

function rcl_is_group_single() {
	global $rcl_group;
	if ( isset( $rcl_group->single_group ) && $rcl_group->single_group ) {
		return true;
	}

	return false;
}

function rcl_get_group_roles() {

	return array(
		'banned'    => array(
			'user_level' => 0,
			'role_name'  => __( 'Ban', 'wp-recall' )
		),
		'reader'    => array(
			'user_level' => 1,
			'role_name'  => __( 'Reader', 'wp-recall' )
		),
		'author'    => array(
			'user_level' => 5,
			'role_name'  => __( 'Author', 'wp-recall' )
		),
		'moderator' => array(
			'user_level' => 7,
			'role_name'  => __( 'Moderator', 'wp-recall' )
		),
		'admin'     => array(
			'user_level' => 10,
			'role_name'  => __( 'Administrator', 'wp-recall' )
		)
	);
}

function rcl_is_group_user() {
	global $rcl_group;
	if ( $rcl_group->current_user ) {
		return true;
	} else {
		return false;
	}
}

function rcl_is_group_can( $role ) {
	global $rcl_group;

	//if ( rcl_is_user_role( $user_ID, array( 'administrator', 'editor' ) ) )
	//return true;

	$group_roles = rcl_get_group_roles();

	if ( ! isset( $rcl_group->current_user ) ) {
		$rcl_group->current_user = rcl_group_current_user_status();
	}

	$user_role = $rcl_group->current_user;

	if ( ! $user_role ) {
		return false;
	}

	if ( $group_roles[ $user_role ]['user_level'] >= $group_roles[ $role ]['user_level'] ) {
		return true;
	} else {
		return false;
	}
}

function rcl_get_group_permalink( $term_id ) {

	if ( rcl_get_option( 'group-output' ) ) {

		$page_id = rcl_get_option( 'group-page' );

		$homeUrl = untrailingslashit( get_the_permalink( $page_id ) );

		if ( '' != get_site_option( 'permalink_structure' ) ) {

			$term = get_term( $term_id, 'groups' );

			$url = $homeUrl . '/' . $term->slug;

			if ( preg_match( "/\/$/", get_site_option( 'permalink_structure' ) ) ) {
				$url .= '/';
			}
		} else {

			$url = $homeUrl . '&group-id=' . $term_id;
		}
	} else {

		$url = get_term_link( ( int ) $term_id, 'groups' );
	}

	return $url;
}

function rcl_group_permalink() {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}
	echo esc_url( rcl_get_group_permalink( $rcl_group->term_id ) );
}

function rcl_group_name() {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}
	echo wp_kses_post( $rcl_group->name );
}

function rcl_group_post_counter() {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}
	echo absint( $rcl_group->count );
}

function rcl_group_status() {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}

	switch ( $rcl_group->group_status ) {
		case 'open':
			echo esc_html__( 'Open group', 'wp-recall' );
			break;
		case 'closed':
			echo esc_html__( 'Closed group', 'wp-recall' );
			break;
	}
}

function rcl_group_count_users() {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}
	echo absint( $rcl_group->group_users );
}

function rcl_get_group_thumbnail( $group_id, $size = 'thumbnail' ) {
	$avatar_id = rcl_get_group_option( $group_id, 'avatar_id' );
	if ( ! $avatar_id ) {
		$url = rcl_addon_url( 'img/group-avatar.png', __FILE__ );
	} else {
		$image_attributes = wp_get_attachment_image_src( $avatar_id, $size );
		$url              = $image_attributes[0];
	}

	$attr = ( isset( $image_attributes ) ) ? "width=$image_attributes[1] height=$image_attributes[2]" : '';

	$content = '<img src="' . $url . '" ' . $attr . '>';

	if ( rcl_is_group_single() ) {
		$content = apply_filters( 'rcl_group_thumbnail', $content );
	}

	return $content;
}

function rcl_group_thumbnail( $size = 'thumbnail' ) {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo rcl_get_group_thumbnail( $rcl_group->term_id, $size );
}

function rcl_has_group_thumbnail( $group_id ) {
	return rcl_get_group_option( $group_id, 'avatar_id' );
}

function rcl_get_group_description( $group_id ) {
	return term_description( $group_id, 'groups' );
}

function rcl_group_description() {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}
	echo wp_kses_post( rcl_get_group_description( $rcl_group->term_id ) );
}

function rcl_group_current_user_status() {
	global $rcl_group, $user_ID;
	if ( $rcl_group->admin_id == $user_ID ) {
		return 'admin';
	}

	return rcl_get_group_user_status( $user_ID, $rcl_group->term_id );
}

function rcl_get_group_user_status( $user_id, $group_id ) {
	global $wpdb;

	return $wpdb->get_var( "SELECT user_role FROM " . RCL_PREF . "groups_users WHERE group_id='$group_id' AND user_id='$user_id'" ); //phpcs:ignore
}

//вносим изменения в запрос вывода пользователей
//при получении юзеров через фильтры группы
function rcl_group_add_users_query( $query ) {
	global $rcl_group;

	$role = ( isset( $_POST['value'] ) ) ? sanitize_key( $_POST['value'] ) : false;

	$role_query = ( $role && $role != 'all' ) ? "='" . $role . "'" : "NOT IN ('admin','moderator')";


	$query['select'][] = "groups_users.user_role";

	if ( $role == 'admin' ) {
		$query['join'][]  = "LEFT JOIN " . RCL_PREF . "groups_users AS groups_users ON wp_users.ID=groups_users.user_id";
		$query['where'][] = "(groups_users.user_role = 'admin' AND groups_users.group_id='$rcl_group->term_id') OR (wp_users.ID='$rcl_group->admin_id')";
		$query['groupby'] = "wp_users.ID";
	} else {
		$query['join'][]  = "INNER JOIN " . RCL_PREF . "groups_users AS groups_users ON wp_users.ID=groups_users.user_id";
		$query['where'][] = "groups_users.group_id = '$rcl_group->term_id' AND groups_users.user_role $role_query";
	}

	return $query;
}

function rcl_group_users( $number, $template = 'mini' ) {
	global $rcl_group;
	if ( ! $rcl_group ) {
		return false;
	}
	add_filter( 'rcl_users_query', 'rcl_group_add_users_query' );
	switch ( $template ) {
		case 'rows':
			$data = 'descriptions,rating_total,posts_count,comments_count,user_registered';
			break;
		case 'avatars':
			$data = 'rating_total';
			break;
		default:
			$data = '';
	}
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo rcl_get_userlist( array(
		'number'   => $number,
		'template' => $template,
		'orderby'  => 'time_action',
		'data'     => $data
	) );
}

function rcl_get_group_users( $group_id ) {
	add_filter( 'rcl_users_query', 'rcl_group_add_users_query' );
	add_filter( 'rcl_page_link_attributes', 'rcl_group_add_page_link_attributes', 10 );

	if ( rcl_is_group_can( 'moderator' ) || current_user_can( 'edit_others_posts' ) ) {
		add_action( 'rcl_user_description', 'rcl_add_group_user_options' );
	}

	$page       = ( isset( $_POST['rcl-page'] ) ) ? absint( $_POST['rcl-page'] ) : false;
	$users_role = ( isset( $_POST['value'] ) ) ? sanitize_key( $_POST['value'] ) : "all";

	$content = '<div id="group-userlist">';

	$group_roles = rcl_get_group_roles();

	$content .= '<div class="rcl-data-filters">'
	            . __( 'Sort by status', 'wp-recall' ) . ': ';

	foreach ( $group_roles as $role => $data ) {

		$class = 'data-filter';
		if ( $role == $users_role ) {
			$class .= ' rcl-bttn__active';
		}

		$content .= rcl_get_group_link( 'rcl_get_group_users', $data['role_name'], array(
			'value' => $role,
			'class' => $class
		) );
	}
	$content .= '</div>';

	$content .= '<h3>' . __( 'Group members', 'wp-recall' ) . '</h3>';
	$content .= rcl_get_userlist(
		array(
			'paged'   => $page,
			'ajax'    => true,
			'filters' => 0,
			'orderby' => 'time_action',
			'data'    => 'rating_total,posts_count,comments_count,description,user_registered',
			'add_uri' => array( 'value' => $users_role )
		) );

	$content .= '</div>';

	return $content;
}

function rcl_group_add_page_link_attributes( $attrs ) {
	global $rcl_group;

	$users_role = ( isset( $_POST['value'] ) ) ? sanitize_key( $_POST['value'] ) : "all";

	$attrs['data']['callback'] = 'rcl_get_group_users&rcl-page=' . $attrs['data']['page'] . '&pager-id=' . $attrs['data']['pager-id'];
	$attrs['data']['value']    = $users_role;
	$attrs['data']['group']    = $rcl_group->term_id;
	$attrs['class']            = 'rcl-group-link';

	return $attrs;
}

function rcl_get_group_option( $group_id, $option_key ) {
	return RQ::tbl( new Rcl_Groups_Options_Query() )
	         ->select( [ 'option_value' ] )
	         ->where( [
		         'group_id'   => $group_id,
		         'option_key' => $option_key,
	         ] )->get_var( 'cache' );
}

function rcl_update_group_option( $group_id, $option_key, $new_value ) {
	global $wpdb;

	$value = rcl_get_group_option( $group_id, $option_key );

	if ( ! isset( $value ) ) {
		return rcl_add_group_option( $group_id, $option_key, $new_value );
	}

	$new_value = maybe_serialize( $new_value );

	return $wpdb->update(
		RCL_PREF . "groups_options", array( 'option_value' => $new_value ), array(
			'group_id'   => $group_id,
			'option_key' => $option_key
		)
	);
}

function rcl_add_group_option( $group_id, $option_key, $value ) {
	global $wpdb;

	$value = maybe_serialize( $value );

	return $wpdb->insert(
		RCL_PREF . "groups_options", array(
			'option_value' => $value,
			'group_id'     => $group_id,
			'option_key'   => $option_key
		)
	);
}

function rcl_delete_group_option( $group_id, $option_key ) {
	global $wpdb;

	return $wpdb->query( "DELETE FROM " . RCL_PREF . "groups_options WHERE group_id='$group_id' AND option_key='$option_key'" ); //phpcs:ignore
}

function rcl_get_group( $group_id ) {
	return RQ::tbl( new Rcl_Groups_Query() )->setup_termdata()->where( array(
		'ID' => $group_id
	) )->get_row( 'cache' );
}

function rcl_update_group_user_role( $user_id, $group_id, $new_role ) {
	global $wpdb;

	$result = $wpdb->update(
		RCL_PREF . "groups_users", array(
		'user_role' => $new_role
	), array(
			'user_id'  => $user_id,
			'group_id' => $group_id
		)
	);

	do_action( 'rcl_update_group_user_role', array(
		'user_id'   => $user_id,
		'group_id'  => $group_id,
		'user_role' => $new_role
	) );

	return $result;
}

function rcl_group_add_user( $user_id, $group_id ) {
	global $wpdb;

	if ( rcl_get_group_user_status( $user_id, $group_id ) ) {
		return false;
	}

	$default_role = rcl_get_group_option( $group_id, 'default_role' );

	$role = ( $default_role ) ? $default_role : 'author';

	$args = array(
		'group_id'    => $group_id,
		'user_id'     => $user_id,
		'user_role'   => $role,
		'status_time' => 0,
		'user_date'   => current_time( 'mysql' )
	);

	$result = $wpdb->insert(
		RCL_PREF . 'groups_users', $args
	);

	rcl_group_update_users_count( $group_id );

	do_action( 'rcl_group_add_user', $args );

	return $result;
}

function rcl_group_remove_user( $user_id, $group_id ) {
	global $wpdb;

	if ( ! rcl_get_group_user_status( $user_id, $group_id ) ) {
		return false;
	}

	$result = $wpdb->query( "DELETE FROM " . RCL_PREF . "groups_users WHERE group_id='$group_id' AND user_id='$user_id'" ); //phpcs:ignore

	rcl_group_update_users_count( $group_id );

	$args = array(
		'group_id' => $group_id,
		'user_id'  => $user_id
	);

	do_action( 'rcl_group_remove_user', $args );

	return $result;
}

function rcl_group_update_users_count( $group_id ) {
	global $wpdb;

	return $wpdb->update(
		RCL_PREF . "groups", array(
		'group_users' => RQ::tbl( new Rcl_Groups_Users_Query() )
		                   ->where( array(
			                   'group_id' => $group_id
		                   ) )
		                   ->get_count()
	), array(
			'ID' => $group_id
		)
	);
}

function rcl_group_add_request_for_membership( $user_id, $group_id ) {

	$rcl_group = rcl_get_group( $group_id );

	$requests   = rcl_get_group_option( $group_id, 'requests_group_access' );
	$requests[] = $user_id;
	rcl_update_group_option( $group_id, 'requests_group_access', $requests );

	$subject     = __( 'Request for group access', 'wp-recall' );
	$textmail    = sprintf(
		'<p>%s</p>
            <h3>%s:</h3>
            <p>%s</p>
            <p>%s:</p>
            <p>%s</p>', sprintf(
		__( 'You have received a new request for access to group managed by you "%s" on the site "%s"', 'wp-recall' ), $rcl_group->name, get_bloginfo( 'name' )
	), __( 'User information', 'wp-recall' ), sprintf(
		'<b>%s</b>: <a href="' . rcl_get_user_url( $user_id ) . '">' . get_the_author_meta( 'display_name', $user_id ) . '</a>', __( 'Profile', 'wp-recall' )
	), __( 'You can approve or reject the request by clicking on the link', 'wp-recall' ), rcl_get_group_permalink( $group_id )
	);
	$admin_email = get_the_author_meta( 'user_email', $rcl_group->admin_id );
	rcl_mail( $admin_email, $subject, $textmail );

	do_action( 'rcl_group_new_access_request', $user_id, $group_id );
}

/* deprecated */
function rcl_get_options_group( $group_id ) {
	$category = rcl_get_group_option( $group_id, 'category' );
	$category = ( is_array( $category ) ) ? implode( ', ', $category ) : $category;

	return array( 'tags' => $category );
}

function rcl_get_tags_list_group( $tags, $post_id = null, $first = null ) {

	if ( ! $tags ) {
		return false;
	}

	$name = '';

	if ( $post_id ) {

		$group_data = get_the_terms( $post_id, 'groups' );
		foreach ( $group_data as $data ) {
			if ( $data->parent == 0 ) {
				$group_id = $data->term_id;
			} else {
				$name = $data->name;
			}
		}
	} else {

		if ( isset( $_GET['group-tag'] ) ) {
			$name = sanitize_text_field( wp_unslash( $_GET['group-tag'] ) );
		}
	}

	$tg_lst = '<select name="group-tag">';

	if ( $first ) {
		$tg_lst .= '<option value="">' . $first . '</option>';
	}
	$ob_tags = [];
	if ( ! is_object( $tags ) ) {
		$ar_tags = explode( ',', $tags );
		$i       = 0;
		foreach ( $ar_tags as $tag ) {
			$ob_tags[ ++ $i ]    = new stdClass();
			$ob_tags[ $i ]->name = trim( $tag );
		}
	} else {
		$a = 0;
		foreach ( $tags as $tag ) {
			$ob_tags[ ++ $a ]    = new stdClass();
			$ob_tags[ $a ]->name = $tag->name;
			$ob_tags[ $a ]->slug = $tag->slug;
		}
	}

	foreach ( $ob_tags as $gr_tag ) {
		if ( ! $gr_tag->name ) {
			continue;
		}
		if ( ! isset( $gr_tag->slug ) ) {
			$slug = $gr_tag->name;
		} else {
			$slug = $gr_tag->slug;
		}
		$tg_lst .= '<option ' . selected( $name, $slug, false ) . ' value="' . $slug . '">' . trim( $gr_tag->name ) . '</option>';
	}

	$tg_lst .= '</select>';

	return $tg_lst;
}

function rcl_get_group_link( $callback, $name, $args = false ) {
	global $rcl_group;

	$class = ( isset( $args['class'] ) ) ? $args['class'] : '';

	return rcl_get_button( array(
		'label' => $name,
		'class' => 'rcl-group-link ' . $class,
		'data'  => array(
			'callback' => $callback,
			'group'    => $rcl_group->term_id,
			'value'    => isset( $args['value'] ) ? $args['value'] : false
		)
	) );
}

function rcl_get_group_callback( $callback, $name, $args = false ) {
	global $rcl_group;

	return rcl_get_button( array(
		'label' => $name,
		'class' => 'rcl-group-callback',
		'data'  => array(
			'callback' => $callback,
			'group'    => $rcl_group->term_id,
			'name'     => $args ? implode( ',', $args ) : false
		)
	) );
}

rcl_ajax_action( 'rcl_get_group_link_content', true );
function rcl_get_group_link_content() {
	global $rcl_group;

	rcl_verify_ajax_nonce();

	$group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;
	$callback = isset( $_POST['callback'] ) ? sanitize_key( $_POST['callback'] ) : '';

	if ( ! rcl_group_is_allowed_callback( $callback ) ) {
		exit;
	}

	if ( ! function_exists( $callback ) ) {
		exit;
	}

	$rcl_group = rcl_get_group( $group_id );

	$content = '<div id="group-link-content">';
	$content .= $callback( $group_id );
	$content .= '</div>';

	wp_send_json( array(
		'dialog' => array(
			'content' => $content,
			'class'   => 'group-dialog',
			'size'    => 'small'
		)
	) );
}

rcl_ajax_action( 'rcl_group_callback' );
function rcl_group_callback() {
	global $rcl_group;
	$group_id = isset( $_POST['group_id'] ) ? intval( $_POST['group_id'] ) : 0;
	$user_id  = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
	$callback = isset( $_POST['callback'] ) ? sanitize_key( $_POST['callback'] ) : '';

	if ( ! rcl_group_is_allowed_callback( $callback ) ) {
		exit;
	}

	if ( ! function_exists( $callback ) ) {
		exit;
	}

	$rcl_group = rcl_get_group( $group_id );

	$result = $callback( $group_id, $user_id );

	wp_send_json( $result );
}

function rcl_group_ajax_delete_user( $group_id, $user_id ) {
	$result = rcl_group_remove_user( $user_id, $group_id );
	if ( $result ) {
		$log['success'] = __( 'User deleted', 'wp-recall' );
		$log['place']   = 'buttons';
	} else {
		$log['error'] = __( 'Error', 'wp-recall' );
		$log['place'] = 'notice';
	}

	return $log;
}

function rcl_group_ajax_update_role( $group_id, $user_id ) {
	global $user_ID;

	if ( $user_ID == $user_id ) {
		return false;
	}

	$new_role = isset( $_POST['user_role'] ) ? sanitize_key( $_POST['user_role'] ) : '';
	$result   = rcl_update_group_user_role( $user_id, $group_id, $new_role );
	if ( $result ) {
		$log['success'] = __( 'User Status updated', 'wp-recall' );
	} else {
		$log['error'] = __( 'Error', 'wp-recall' );
	}
	$log['place'] = 'notice';

	return $log;
}

function rcl_get_group_category_list() {
	global $rcl_group;

	$targs = array(
		'number'       => 0,
		'hide_empty'   => true,
		'hierarchical' => false,
		'pad_counts'   => false,
		'get'          => '',
		'child_of'     => 0,
		'parent'       => $rcl_group->term_id
	);

	$tags = get_terms( 'groups', $targs );

	if ( ! $tags ) {
		return false;
	}

	$content = '<div class="search-form-rcl">';
	$content .= '<form method="get">';

	if ( rcl_get_option( 'group-output' ) && '' == get_site_option( 'permalink_structure' ) ) {
		$content .= '<input type="hidden" name="page_id" value="' . absint( rcl_get_option( 'group-page' ) ) . '">';
		$content .= '<input type="hidden" name="group-id" value="' . absint( $rcl_group->term_id ) . '">';
	}

	$content .= rcl_get_tags_list_group( ( object ) $tags, '', __( 'Display all records', 'wp-recall' ) );

	$content .= rcl_get_button( array(
		'label'  => __( 'Show', 'wp-recall' ),
		'submit' => true
	) );

	$content .= '</form>';
	$content .= '</div>';

	return $content;
}

function rcl_group_admin_panel() {
	global $rcl_group;

	$admins_buttons = array(
		array(
			'callback' => 'rcl_get_group_options',
			'name'     => __( 'Basic settings', 'wp-recall' )
		),
		array(
			'callback' => 'rcl_get_group_widgets',
			'name'     => __( 'Widget management', 'wp-recall' )
		)
	);

	if ( $rcl_group->group_status == 'closed' ) {

		$requests = rcl_get_group_option( $rcl_group->term_id, 'requests_group_access' );

		if ( ! $requests ) {
			$requests = array();
		}

		$admins_buttons[] = array(
			'callback' => 'rcl_get_group_requests_content',
			'name'     => __( 'Access request', 'wp-recall' ) . ' - ' . count( $requests )
		);
	}

	$admins_buttons = apply_filters( 'rcl_group_admin_panel', $admins_buttons );
	$buttons        = [];
	foreach ( $admins_buttons as $button ) {
		$buttons[] = '<li class="admin-button">' . rcl_get_group_link( $button['callback'], $button['name'] ) . '</li>';
	}

	return '<div id="group-admin-panel">'
	       . '<span class="title-panel"><i class="rcli fa-cogs"></i>' . __( 'Administration', 'wp-recall' ) . '</span>'
	       . '<ul>' . implode( '', $buttons ) . '</ul>'
	       . '</div>';
}

add_action( 'pre_get_posts', 'rcl_init_group_data', 10 );
function rcl_init_group_data( $query ) {
	global $post, $rcl_group, $wpdb;

	if ( ! $query->is_main_query() ) {
		return $query;
	}

	if ( rcl_get_option( 'group-output' ) ) {

		$groupPage = rcl_get_option( 'group-page' );

		$isGroupPage = ( get_query_var( 'page_id' ) == $groupPage || $query->queried_object_id == $groupPage ) ? true : false;

		if ( $query->is_page && $isGroupPage ) {

			$group_var = get_query_var( 'group-id' );

			if ( '' != get_site_option( 'permalink_structure' ) ) {

				$term = get_term_by( 'slug', $group_var, 'groups' );

				$group_id = $term ? $term->term_id : false;
			} else {

				$group_id = ( $group_var ) ? $group_var : false;
			}

			$rcl_group = rcl_group_init( $group_id );
		}
	} else {

		if ( $query->is_tax && isset( $query->query['groups'] ) ) {

			if ( ! isset( $query->query_vars['groups'] ) ) {
				return false;
			}

			$curent_term = get_term_by( 'slug', $query->query_vars['groups'], 'groups' );

			if ( $curent_term->parent != 0 ) {
				$group_id = $curent_term->parent;
			} else {
				$group_id = $curent_term->term_id;
			}

			$rcl_group = rcl_group_init( $group_id );
		}
	}

	if ( $query->is_single ) {

		if ( isset( $query->query['post_type'] ) && $query->query['post_type'] == 'post-group' && isset( $query->query['name'] ) ) {

			if ( ! $post ) {
				//phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.QuotedSimplePlaceholder
				$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM " . $wpdb->prefix . "posts WHERE post_name='%s'", $query->query['name'] ) );
			} else {
				$post_id = $post->ID;
			}

			$cur_terms = get_the_terms( $post_id, 'groups' );
			$term_id   = false;
			foreach ( ( array ) $cur_terms as $cur_term ) {

				if ( ! is_object( $cur_term ) || $cur_term->parent != 0 ) {
					continue;
				}

				$term_id = $cur_term->term_id;
				break;
			}

			$rcl_group = rcl_get_group( $term_id );
		}
	}

	return $query;
}

add_action( 'pre_get_posts', 'rcl_edit_group_pre_get_posts', 20 );
function rcl_edit_group_pre_get_posts( $query ) {
	if ( ! $query->is_main_query() ) {
		return $query;
	}

	global $user_ID, $rcl_group;

	if ( $rcl_group ) {

		if ( isset( $_GET['group-tag'] ) ) {

			if ( empty( $_GET['group-tag'] ) ) {
				wp_safe_redirect( rcl_get_group_permalink( $rcl_group->term_id ) );
				exit;
			}

			if ( empty( $_GET['group-id'] ) ) {

				$query->set( 'groups', sanitize_text_field( wp_unslash( $_GET['group-tag'] ) ) );

				return $query;
			}
		}

		if ( isset( $_GET['group-page'] ) && $_GET['group-page'] != '' ) {
			$query->set( 'posts_per_page', 1 );
		}

		if ( $rcl_group->admin_id == $user_ID || rcl_is_user_role( $user_ID, [ 'administrator' ] ) ) {
			return $query;
		}

		if ( ! isset( $rcl_group->current_user ) && $user_ID ) {
			$in_group = rcl_get_group_user_status( $user_ID, $rcl_group->term_id );
		} else {
			$in_group = $rcl_group->current_user;
		}

		if ( $rcl_group->group_status == 'closed' ) {

			if ( ! $in_group || $in_group == 'banned' ) {

				if ( $query->is_single ) {
					global $comments_array;

					add_filter( 'the_content', 'rcl_close_group_post_content' );
					add_filter( 'the_content', 'rcl_get_link_group_tag', 80 );
					add_filter( 'the_content', 'rcl_add_namegroup', 80 );
					add_filter( 'comments_array', 'rcl_close_group_comments_content' );
					add_filter( 'comments_open', 'rcl_close_group_comments', 10 );
					remove_filter( 'rating_block_content', 'rcl_add_buttons_rating', 10 );
				}
			}
		} else {
			if ( $in_group == 'banned' ) {
				if ( $query->is_single ) {
					add_filter( 'comments_open', 'rcl_close_group_comments', 10 );
					remove_filter( 'rating_block_content', 'rcl_add_buttons_rating', 10 );
				}
			}
		}
	}

	return $query;
}

function rcl_get_member_group_access_status() {
	global $rcl_group, $user_ID;

	if ( $rcl_group->admin_id == $user_ID || rcl_is_user_role( $user_ID, [ 'administrator' ] ) ) {
		return true;
	}

	if ( ! $rcl_group->current_user && $user_ID ) {
		$in_group = rcl_get_group_user_status( $user_ID, $rcl_group->term_id );
	} else {
		$in_group = $rcl_group->current_user;
	}

	if ( $rcl_group->group_status == 'closed' ) {

		if ( ! $in_group || $in_group == 'banned' ) {
			return false;
		}
	} else {
		if ( $in_group == 'banned' ) {
			return false;
		}
	}

	return true;
}

function rcl_close_group_post_content() {
	global $rcl_group;

	return rcl_get_notice( [
		'title' => __( 'Publication unavailable!', 'wp-recall' ),
		'text'  => __( 'To view the publication , you must be a member of the group', 'wp-recall' ) . ' "' . $rcl_group->name . '"',
		'type'  => 'error'
	] );
}

function rcl_close_group_comments_content( $comments ) {
	foreach ( $comments as $comment ) {
		$comment->comment_content = rcl_get_notice( [
			'text' => __( 'Comment hidden by privacy settings', 'wp-recall' ),
			'type' => 'error'
		] );
	}

	return $comments;
}

function rcl_close_group_comments( $open ) {
	$open = false;

	return $open;
}

function rcl_get_closed_groups( $user_id ) {
	return RQ::tbl( new Rcl_Groups_Query() )
	         ->select( [ 'ID' ] )
	         ->where( [
		         'group_status'     => 'closed',
		         'admin_id__not_in' => $user_id
	         ] )
	         ->join(
		         [ 'ID', 'group_id', 'LEFT' ], RQ::tbl( new Rcl_Groups_Users_Query( 'groups_users' ) )
		                                         ->where_string( "(groups_users.user_id != '$user_id' OR groups_users.user_id IS NULL)" )
	         )
	         ->get_col( 'cache' );
}

function rcl_get_closed_group_posts( $user_id ) {
	global $wpdb;

	$groups = rcl_get_closed_groups( $user_id );

	if ( ! $groups ) {
		return array();
	}

	$cachekey = json_encode( array( 'rcl_get_closed_group_posts', $user_id ) );
	$cache    = wp_cache_get( $cachekey );
	if ( $cache ) {
		return $cache;
	}

	$sql = "SELECT term_relationships.object_id FROM $wpdb->term_relationships AS term_relationships "
	       . "INNER JOIN $wpdb->term_taxonomy AS term_taxonomy ON term_relationships.term_taxonomy_id=term_taxonomy.term_taxonomy_id "
	       . "WHERE term_taxonomy.term_id IN (" . implode( ',', $groups ) . ") GROUP BY term_relationships.object_id";

	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$posts = $wpdb->get_col( $sql );

	wp_cache_add( $cachekey, $posts );

	return $posts;
}
