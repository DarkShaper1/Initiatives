<?php

function rcl_get_feeds( $args = false ) {
	return RQ::tbl( new Rcl_Feed_Query() )->parse( $args )->get_results();
}

/* array(
  'user_id'=>$user_id,
  'object_id'=>$object_id,
  'feed_type'=>'author',
  'feed_status'=>1
  ) */
//добавляем новую подписку по переданному массиву значений
function rcl_insert_feed_data( $args ) {
	global $wpdb;

	$result = $wpdb->insert(
		RCL_PREF . "feeds", $args
	);

	if ( ! $result ) {
		rcl_add_log( 'rcl_insert_feed_data: ' . __( 'Failed to add new subscription', 'wp-recall' ), $args );
	}

	$feed_id = $wpdb->insert_id;

	do_action( 'rcl_insert_feed_data', $feed_id, $args );

	return $feed_id;
}

//Обновляем данные фида по переданному массиву значений
function rcl_update_feed_data( $args ) {
	global $wpdb;

	if ( ! isset( $args['feed_id'] ) ) {
		return false;
	}

	$feed_id = $args['feed_id'];
	unset( $args['feed_id'] );

	$result = $wpdb->update(
		RCL_PREF . "feeds", $args, array( 'feed_id' => $feed_id )
	);

	if ( ! $result ) {
		rcl_add_log( 'rcl_update_feed_data: ' . __( 'Failed to change feed data', 'wp-recall' ), $args );
	}

	if ( ! $result ) {
		return false;
	}

	do_action( 'rcl_update_feed_data', $feed_id, $args );

	return $result;
}

//добавляем подписку текущему пользователю на указанного пользователя
function rcl_add_feed_author( $author_id ) {
	global $user_ID;

	return rcl_insert_feed_data( array(
		'user_id'     => $user_ID,
		'object_id'   => $author_id,
		'feed_type'   => 'author',
		'feed_status' => 1
	) );
}

//удаляем подписку текущему пользователю на указанного пользователя
function rcl_remove_feed_author( $author_id ) {
	$feed_id = rcl_get_feed_author_current_user( $author_id );

	return rcl_remove_feed( $feed_id );
}

add_action( 'delete_user', 'rcl_remove_user_feed', 10 );
function rcl_remove_user_feed( $user_id ) {
	global $wpdb;

	return $wpdb->query(
		$wpdb->prepare( "DELETE FROM " . RCL_PREF . "feeds WHERE user_id=%d OR object_id=%d", absint( $user_id ), absint( $user_id ) ) //phpcs:ignore
	);
}

//получаем данные фида по ИД
function rcl_get_feed_data( $feed_id ) {
	return RQ::tbl( new Rcl_Feed_Query() )->select( [ 'feed_id' => $feed_id ] )->get_row( 'cache' );
}

//удаление фида по ИД
function rcl_remove_feed( $feed_id ) {
	global $wpdb;

	$feed = rcl_get_feed_data( $feed_id );

	if ( ! $feed ) {
		return false;
	}

	do_action( 'rcl_pre_remove_feed', $feed );

	return $wpdb->query(
		$wpdb->prepare( "DELETE FROM " . RCL_PREF . "feeds WHERE feed_id='%d'", $feed_id ) //phpcs:ignore
	);
}

function rcl_is_ignored_feed_author( $author_id ) {
	global $user_ID;

	return RQ::tbl( new Rcl_Feed_Query() )->select( [ 'feed_id' ] )->where( [
		'user_id'     => $user_ID,
		'object_id'   => $author_id,
		'feed_type'   => 'author',
		'feed_status' => 0,
	] )->get_var( 'cache' );
}

//получаем ИД фида текущего пользователя по ИД автора
function rcl_get_feed_author_current_user( $author_id ) {
	global $user_ID;

	return RQ::tbl( new Rcl_Feed_Query() )->select( [ 'feed_id' ] )->where( [
		'user_id'     => $user_ID,
		'object_id'   => $author_id,
		'feed_type'   => 'author',
		'feed_status' => 1,
	] )->get_var( 'cache' );
}

function rcl_get_feed_callback_link( $user_id, $name, $callback ) {
	return '<div class="callback-link user-link-' . $user_id . '">'
	       . rcl_get_button( array(
			'icon'  => 'fa-rss',
			'class' => array( 'feed-callback' ),
			'data'  => array(
				'feed'     => $user_id,
				'callback' => $callback
			),
			'label' => $name
		) )
	       . '</div>';
}

//считаем кол-во подписок указанного пользователя
function rcl_feed_count_authors( $user_id ) {

	return RQ::tbl( new Rcl_Feed_Query() )->where( [
		'user_id'     => $user_id,
		'feed_type'   => 'author',
		'feed_status' => 1
	] )->get_count( 'feed_id', 'cache' );
}

//считаем кол-во подписчиков указанного пользователя
function rcl_feed_count_subscribers( $user_id ) {

	return RQ::tbl( new Rcl_Feed_Query() )->where( [
		'object_id'   => $user_id,
		'feed_type'   => 'author',
		'feed_status' => 1
	] )->get_count( 'feed_id', 'cache' );
}

rcl_ajax_action( 'rcl_feed_callback' );
function rcl_feed_callback() {

	rcl_verify_ajax_nonce();

	$allowedCallbacks = apply_filters( 'rcl_feed_allowed_callbacks', [
		'rcl_ignored_feed_author',
		'rcl_update_feed_current_user'
	] );

	$callback = isset( $_POST['callback'] ) ? sanitize_key( $_POST['callback'] ) : '';

	if ( ! in_array( $callback, $allowedCallbacks ) ) {
		exit;
	}

	$data    = isset( $_POST['data'] ) ? intval( $_POST['data'] ) : 0;
	$content = $callback( $data );

	wp_send_json( $content );
}

function rcl_feed_content() {
	global $rcl_feed;

	echo wp_kses_post( apply_filters( 'rcl_feed_content', $rcl_feed->feed_content ) );
}

add_filter( 'rcl_feed_content', 'rcl_add_feed_content_meta', 10 );
function rcl_add_feed_content_meta( $content ) {
	global $rcl_feed;

	switch ( $rcl_feed->feed_type ) {
		case 'posts':
			return $content;
		case 'comments':
			$content .= '<div class="feed-content-meta">' . esc_html__( 'For publication', 'wp-recall' ) . ' <a href="' . get_permalink( $rcl_feed->feed_parent ) . '">' . get_the_title( $rcl_feed->feed_parent ) . '</a></div>';
			break;
		case 'answers':
			$content .= '<div class="feed-content-meta">' . esc_html__( 'In response to', 'wp-recall' ) . ' <a href="' . get_comment_link( $rcl_feed->feed_parent ) . '">' . __( 'your comment', 'wp-recall' ) . '</a></div>';
			break;
		default:
			return $content;
	}

	return $content;
}

function rcl_feed_unset_can_vote( $userCan ) {

	$userCan['vote'] = false;

	return $userCan;
}

add_filter( 'rcl_feed_excerpt', 'rcl_add_feed_rating', 10 );
function rcl_add_feed_rating( $content ) {
	global $rcl_feed;
	if ( ! function_exists( 'rcl_get_html_post_rating' ) ) {
		return $content;
	}
	$content .= rcl_get_html_post_rating( $rcl_feed->feed_ID, $rcl_feed->post_type, $rcl_feed->feed_author );

	return $content;
}

add_filter( 'rcl_feed_excerpt', 'wpautop', 11 );
add_filter( 'rcl_feed_content', 'rcl_get_feed_excerpt', 20 );
function rcl_get_feed_excerpt( $content ) {
	global $rcl_feed;

	if ( isset( $rcl_feed->show_full ) ) {
		return $content;
	}

	if ( $rcl_feed->feed_type != 'posts' ) {
		return $content;
	}

	$content = strip_shortcodes( $content );

	if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
		$content = explode( $matches[0], $content, 2 );
		$content = $content[0];
	} else {
		$content = wp_trim_words( $content, 50, '...' );
	}

	$thumb = get_post_meta( $rcl_feed->feed_ID, '_thumbnail_id', 1 );
	if ( $thumb ) {
		$src     = wp_get_attachment_image_src( $thumb, 'medium' );
		$content = '<img class="aligncenter" src="' . $src[0] . '" alt="" />' . $content;
	}

	$content = apply_filters( 'rcl_feed_excerpt', $content );

	$content .= apply_filters( 'the_content_more_link', ' <a href="' . get_permalink( $rcl_feed->feed_ID ) . '" class="more-link">' . __( 'Read more', 'wp-recall' ) . '</a>', __( 'Read more', 'wp-recall' ) );

	return $content;
}

add_filter( 'rcl_feed_content', 'rcl_get_feed_attachment', 30 );
function rcl_get_feed_attachment( $content ) {
	global $rcl_feed;

	if ( $rcl_feed->feed_type != 'posts' || $rcl_feed->post_type != 'attachment' ) {
		return $content;
	}

	$src = wp_get_attachment_image_src( $rcl_feed->feed_ID, 'medium' );

	return '<a href="' . $rcl_feed->feed_permalink . '"><img class="aligncenter" src="' . $src[0] . '" alt="" /></a>' . $content;
}

function rcl_feed_options() {
	global $rcl_feed;

	$content = '<div class="feed-options">'
	           . '<i class="rcli fa-times"></i>'
	           . '<div class="options-box">'
	           . rcl_get_feed_callback_link( $rcl_feed->feed_author, esc_html__( 'Ignore publications', 'wp-recall' ) . ' ' . get_the_author_meta( 'display_name', $rcl_feed->feed_author ), 'rcl_ignored_feed_author' )
	           . '</div>'
	           . '</div>';

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

function rcl_get_author_feed_data( $author_id ) {
	global $user_ID;

	return RQ::tbl( new Rcl_Feed_Query() )->where( [
		'user_id'   => $user_ID,
		'object_id' => $author_id,
		'feed_type' => 'author'
	] )->get_row( 'cache' );
}

function rcl_ignored_feed_author( $author_id ) {
	global $user_ID;

	$feed = rcl_get_author_feed_data( $author_id );

	$args = array(
		'user_id'     => $user_ID,
		'object_id'   => $author_id,
		'feed_type'   => 'author',
		'feed_status' => 0
	);

	if ( ! $feed ) {

		$result = rcl_insert_feed_data( $args );
	} else {

		if ( ! $feed->feed_status ) {
			$args['feed_status'] = 1;
		}

		$args['feed_id'] = $feed->feed_id;

		$result = rcl_update_feed_data( $args );
	}

	if ( $result ) {
		$data['success'] = __( 'Subscription status has been changed', 'wp-recall' );
		$data['all']     = ( ! $feed || $feed->feed_status ) ? __( 'Subscribe', 'wp-recall' ) : __( 'Unsubscribe', 'wp-recall' );
	} else {
		$data['error'] = 'Error';
	}

	$data['return'] = 'notice';

	return $data;
}

function rcl_get_feed_array( $user_id, $type_feed = 'author' ) {
	global $user_ID;

	$cachekey = json_encode( array( 'rcl_get_feed_array', $user_id, $type_feed ) );
	$cache    = wp_cache_get( $cachekey );
	if ( $cache ) {
		return $cache;
	}

	$users = RQ::tbl( new Rcl_Feed_Query() )->select( [ 'object_id' ] )->where( [
		'user_id'   => $user_ID,
		'object_id' => $user_id,
		'feed_type' => 'author'
	] )->get_col();

	if ( $users ) {

		$sec_feeds = RQ::tbl( new Rcl_Feed_Query() )->select( [ 'object_id' ] )->where( [
			'user_id__in' => $users,
			'feed_type'   => $type_feed,
			'feed_status' => 1,
		] )->get_col();

		if ( $sec_feeds ) {
			$users = array_unique( array_merge( $users, $sec_feeds ) );
		}
	}

	wp_cache_add( $cachekey, $users );

	return $users;
}

function rcl_feed_title() {
	global $rcl_feed;

	echo wp_kses_post( apply_filters( 'rcl_feed_title', $rcl_feed->feed_title ) );
}

add_filter( 'rcl_feed_title', 'rcl_add_link_feed_title', 10 );
function rcl_add_link_feed_title( $feed_title ) {
	global $rcl_feed;

	return ( $rcl_feed->feed_permalink ) ? sprintf( '<a href="%s">%s</a>', esc_url( $rcl_feed->feed_permalink ), $feed_title ) : $feed_title;
}
