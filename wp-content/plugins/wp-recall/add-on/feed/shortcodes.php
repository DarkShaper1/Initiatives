<?php

/* $atts
  array(
 * 'filters',
 * 'load',
 * 'user_feed',
 * 'content',
 * 'number',
 * 'per_page',
 * 'offset',
 * 'orderby',
 * 'order'
 * ) */

add_shortcode( 'feed', 'rcl_get_feed_list' );
function rcl_get_feed_list( $atts = array() ) {
	global $user_ID, $rcl_feed;

	if ( ! $user_ID ) {
		return apply_filters( 'rcl_feed_no_login_notice', rcl_get_notice( [
			'class' => 'rcl-feed-notice',
			'text'  => __( 'Login or register to view the latest publications and comments from users for which you have subscribed.', 'wp-recall' )
		] ) );
	}

	rcl_feed_scripts_init();

	add_filter( 'rcl_rating_user_can', 'rcl_feed_unset_can_vote', 10 );

	if ( ! $atts ) {
		$atts = array();
	}

	include_once 'classes/class-rcl-feed-list.php';

	$list    = new Rcl_Feed_List( $atts );
	$rclnavi = false;
	if ( ! isset( $atts['number'] ) ) {

		$rclnavi = new Rcl_PageNavi(
			'rcl-feed', $list->count_feed(), array(
				'in_page' => 30
			)
		);

		$list->query['offset'] = $rclnavi->offset;
	}

	$content = $list->get_filters();

	$feedsdata = $list->get_feed();

	if ( ! $feedsdata ) {
		return $content . rcl_get_notice( [
				'text' => __( 'No news found.', 'wp-recall' )
			] );
	}

	$load = ( $rclnavi->in_page ) ? 'data-load="' . $list->load . '"' : '';

	$content .= '<div id="rcl-feed" data-custom="' . base64_encode( json_encode( $atts ) ) . '" data-feed="' . $list->content . '" ' . $load . '>';

	foreach ( $feedsdata as $rcl_feed ) {
		$list->setup_data( $rcl_feed );
		$content .= '<div id="feed-' . $rcl_feed->feed_type . '-' . $rcl_feed->feed_ID . '" class="feed-box feed-user-' . $rcl_feed->feed_author . ' feed-' . $rcl_feed->feed_type . '">';
		$content .= rcl_get_include_template( 'feed-post.php', __FILE__ );
		$content .= '</div>';
	}

	if ( $list->load == 'ajax' && $rclnavi->in_page ) {
		$content .= '<div id="feed-preloader"><div></div></div>'
		            . '<div id="feed-bottom"></div>';
	}

	$content .= '</div>';

	if ( $list->load == 'pagenavi' && $rclnavi->in_page ) {
		$content .= $rclnavi->pagenavi();
	}

	$list->remove_data();

	return $content;
}
