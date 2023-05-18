<?php

require_once 'classes/class-rcl-feed-query.php';

require_once 'addon-core.php';
require_once 'shortcodes.php';

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_feed_scripts_office', 10 );
endif;
function rcl_feed_scripts_office() {
	global $user_ID;
	if ( $user_ID || rcl_is_office() ) {
		rcl_feed_scripts_init();
	}
}

function rcl_feed_scripts_init() {
	rcl_enqueue_style( 'rcl-feed', rcl_addon_url( 'style.css', __FILE__ ) );
	rcl_enqueue_script( 'rcl-feed', rcl_addon_url( 'js/scripts.js', __FILE__ ) );
}

add_action( 'init', 'rcl_add_block_feed_button' );
function rcl_add_block_feed_button() {
	rcl_block( 'actions', 'rcl_add_feed_button', array( 'id' => 'fd-footer', 'order' => 5, 'public' => - 1 ) );
}

function rcl_add_feed_button( $user_id ) {
	global $user_ID;
	if ( ! $user_ID || $user_ID == $user_id ) {
		return false;
	}
	if ( rcl_get_feed_author_current_user( $user_id ) ) {
		return rcl_get_feed_callback_link( $user_id, __( 'Unsubscribe', 'wp-recall' ), 'rcl_update_feed_current_user' );
	} else {
		return rcl_get_feed_callback_link( $user_id, __( 'Subscribe', 'wp-recall' ), 'rcl_update_feed_current_user' );
	}
}

function rcl_add_userlist_follow_button() {
	global $rcl_user;
	echo wp_kses( '<div class="follow-button">' . rcl_add_feed_button( $rcl_user->ID ) . '</div>', rcl_kses_allowed_html() );
}

add_action( 'init', 'rcl_add_followers_tab', 10 );
function rcl_add_followers_tab() {
	global $user_LK;
	$count = 0;

	if ( ! is_admin() && $user_LK ) {
		$count = rcl_feed_count_subscribers( $user_LK );
	}

	rcl_tab(
		array(
			'id'       => 'followers',
			'name'     => __( 'Followers', 'wp-recall' ),
			'supports' => array( 'ajax', 'cache' ),
			'public'   => 1,
			'icon'     => 'fa-twitter',
			'output'   => 'counters',
			'counter'  => $count,
			'content'  => array(
				array(
					'callback' => array(
						'name' => 'rcl_followers_tab'
					)
				)
			)
		)
	);
}

add_action( 'init', 'rcl_add_subscriptions_tab', 10 );
function rcl_add_subscriptions_tab() {
	global $user_LK;
	$count = 0;
	if ( ! is_admin() && $user_LK ) {
		$count = rcl_feed_count_authors( $user_LK );
	}

	rcl_tab(
		array(
			'id'       => 'subscriptions',
			'name'     => __( 'Subscriptions', 'wp-recall' ),
			'supports' => array( 'ajax', 'cache' ),
			'public'   => 0,
			'icon'     => 'fa-bell-o',
			'output'   => 'counters',
			'counter'  => $count,
			'content'  => array(
				array(
					'callback' => array(
						'name' => 'rcl_subscriptions_tab'
					)
				)
			)
		)
	);
}

function rcl_followers_tab( $user_id ) {

	$content = '<h3>' . __( 'List of subscribers', 'wp-recall' ) . '</h3>';

	$cnt = rcl_feed_count_subscribers( $user_id );

	if ( ! $cnt ) {
		return $content . rcl_get_notice( [ 'text' => __( 'You do not have any subscribers yet', 'wp-recall' ) ] );
	}

	add_filter( 'rcl_user_description', 'rcl_add_userlist_follow_button', 90 );
	add_filter( 'rcl_users_query', 'rcl_feed_subsribers_query_userlist', 10 );
	$content .= rcl_get_userlist( array(
		'template'    => 'rows',
		'per_page'    => 20,
		'orderby'     => 'user_registered',
		'filters'     => 1,
		'search_form' => 0,
		'data'        => 'rating_total,description,posts_count,comments_count',
		'add_uri'     => array( 'tab' => 'followers' )
	) );

	return $content;
}

function rcl_subscriptions_tab( $user_id ) {

	$content = '<h3>' . __( 'List of subscriptions', 'wp-recall' ) . '</h3>';

	$feeds = rcl_feed_count_authors( $user_id );

	if ( ! $feeds ) {
		return $content . rcl_get_notice( [ 'text' => __( 'You do not have any subscriptions', 'wp-recall' ) ] );
	}

	add_filter( 'rcl_user_description', 'rcl_add_userlist_follow_button', 90 );
	add_filter( 'rcl_users_query', 'rcl_feed_authors_query_userlist', 10 );
	$content .= rcl_get_userlist( array(
		'template'    => 'rows',
		'orderby'     => 'user_registered',
		'per_page'    => 20,
		'filters'     => 1,
		'search_form' => 0,
		'data'        => 'rating_total,description,posts_count,comments_count',
		'add_uri'     => array( 'tab' => 'subscriptions' )
	) );

	return $content;
}

function rcl_feed_authors_query_userlist( $query ) {
	global $user_LK;
	$query['join'][]   = "INNER JOIN " . RCL_PREF . "feeds AS feeds ON wp_users.ID = feeds.object_id";
	$query['where'][]  = "feeds.user_id='$user_LK'";
	$query['where'][]  = "feeds.feed_type='author'";
	$query['where'][]  = "feeds.feed_status='1'";
	$query['relation'] = "AND";

	//$query['groupby'] = false;
	return $query;
}

function rcl_feed_subsribers_query_userlist( $query ) {
	global $user_LK;
	$query['join'][]  = "INNER JOIN " . RCL_PREF . "feeds AS feeds ON wp_users.ID = feeds.user_id";
	$query['where'][] = "feeds.object_id='$user_LK'";
	$query['where'][] = "feeds.feed_type='author'";
	$query['where'][] = "feeds.feed_status='1'";

	//$query['groupby'] = false;
	return $query;
}

function rcl_update_feed_current_user( $author_id ) {
	global $user_ID;

	$ignored_id = rcl_is_ignored_feed_author( $author_id );

	if ( $ignored_id ) {

		$args = array(
			'feed_id'     => $ignored_id,
			'user_id'     => $user_ID,
			'object_id'   => $author_id,
			'feed_type'   => 'author',
			'feed_status' => 1
		);

		$result = rcl_update_feed_data( $args );

		if ( $result ) {
			$data['success'] = __( 'Signed up for a subscription', 'wp-recall' );
			$data['this']    = __( 'Unsubscribe', 'wp-recall' );
		} else {
			$data['error'] = __( 'Error', 'wp-recall' ) . ' 100';
		}
	} else {

		$feed = rcl_get_feed_author_current_user( $author_id );

		if ( $feed ) {
			$result = rcl_remove_feed_author( $author_id );
			if ( $result ) {
				$data['success'] = __( 'Subscription has been cancelled', 'wp-recall' );
				$data['this']    = __( 'Subscribe', 'wp-recall' );
			} else {
				$data['error'] = __( 'Error', 'wp-recall' ) . ' 101';
			}
		} else {
			$result = rcl_add_feed_author( $author_id );
			if ( $result ) {
				$data['success'] = __( 'Signed up for a subscription', 'wp-recall' );
				$data['this']    = __( 'Unsubscribe', 'wp-recall' );
			} else {
				$data['error'] = __( 'Error', 'wp-recall' ) . ' 102';
			}
		}
	}

	$data['return'] = 'notice';

	return $data;
}

rcl_ajax_action( 'rcl_feed_progress' );
function rcl_feed_progress() {
	global $rcl_feed;

	rcl_verify_ajax_nonce();

	if ( ! isset( $_POST['custom'] ) ) {
		wp_send_json( [
			'error' => esc_html__( 'Error', 'wp-recall' )
		] );
	}

	$customData = rcl_recursive_map( 'sanitize_text_field', json_decode( base64_decode( sanitize_text_field( wp_unslash( $_POST['custom'] ) ) ) ) );

	$customData = ( array ) $customData;

	$customData['paged']   = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;
	$customData['content'] = isset( $_POST['content'] ) ? sanitize_text_field( wp_unslash( $_POST['content'] ) ) : '';
	$customData['filters'] = 0;

	if ( isset( $customData['query'] ) ) {
		unset( $customData['query'] );
	}

	include_once 'classes/class-rcl-feed-list.php';
	$list = new Rcl_Feed_List( $customData );

	$count = $list->count_feed();

	$rclnavi = new Rcl_PageNavi(
		'rcl-feed', $count, array(
			'in_page'      => $list->query['number'],
			'current_page' => $list->paged
		)
	);

	$list->query['offset'] = $rclnavi->offset;

	$feedsdata = $list->get_feed();

	$content = '';

	if ( ! $feedsdata ) {
		wp_send_json( [
			'content' => rcl_get_notice( [ 'text' => __( 'News no more', 'wp-recall' ) ] ),
			'code'    => 0
		] );
	}

	foreach ( $feedsdata as $rcl_feed ) {
		$list->setup_data( $rcl_feed );
		$content .= '<div id="feed-' . $rcl_feed->feed_type . '-' . $rcl_feed->feed_ID . '" class="feed-box feed-user-' . $rcl_feed->feed_author . ' feed-' . $rcl_feed->feed_type . '">';
		$content .= rcl_get_include_template( 'feed-post.php', __FILE__ );
		$content .= '</div>';
	}

	$list->remove_data();

	$result['content'] = $content;
	$result['code']    = 100;

	wp_send_json( $result );
}
