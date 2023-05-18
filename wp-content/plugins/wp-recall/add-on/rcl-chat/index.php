<?php

include_once 'chats-query.php';
include_once 'core.php';

rcl_init_beat( 'rcl_chat_beat_core', [ 'rcl_chat_get_new_messages' ] );

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_chat_scripts', 10 );
else:
	include_once 'addon-options.php';
endif;
function rcl_chat_scripts() {
	global $user_ID, $rcl_options;

	if ( is_user_logged_in() ) {
		rcl_enqueue_script( 'rcl-chat-sounds', rcl_addon_url( 'js/ion.sound.min.js', __FILE__ ) );
	}

	rcl_enqueue_style( 'rcl-chat', rcl_addon_url( 'style.css', __FILE__ ) );
	rcl_enqueue_script( 'rcl-chat', rcl_addon_url( 'js/scripts.js', __FILE__ ) );

	$file_upload = ( isset( $rcl_options['chat']['file_upload'] ) ) ? $rcl_options['chat']['file_upload'] : 0;

	if ( $user_ID && $file_upload ) {

		$contact_panel = ( isset( $rcl_options['chat']['contact_panel'] ) ) ? $rcl_options['chat']['contact_panel'] : 0;

		if ( $contact_panel || rcl_is_office() ) {
			rcl_fileupload_scripts();
		}
	}
}

add_action( 'template_redirect', 'rcl_chat_filter_attachment_pages', 20 );
function rcl_chat_filter_attachment_pages() {
	global $post;

	if ( ! is_single() || ! in_array( $post->post_type, array(
			'attachment'
		) ) ) {
		return;
	}

	if ( stripos( $post->post_excerpt, 'rcl_chat_attachment' ) === false ) {
		return;
	}

	status_header( 404 );
	include( get_query_template( '404' ) );
	exit;
}

add_action( 'rcl_bar_setup', 'rcl_bar_add_chat_icon', 10 );
function rcl_bar_add_chat_icon() {
	global $user_ID, $rcl_options;

	if ( ! is_user_logged_in() ) {
		return false;
	}

	//если выводится панель контактов
	if ( isset( $rcl_options['chat']['contact_panel'] ) && $rcl_options['chat']['contact_panel'] ) {
		return false;
	}

	rcl_bar_add_icon( 'rcl-messages', array(
			'icon'    => 'fa-envelope',
			'url'     => rcl_get_tab_permalink( $user_ID, 'chat' ),
			'label'   => __( 'Messages', 'wp-recall' ),
			'counter' => rcl_chat_noread_messages_amount( $user_ID )
		)
	);
}

add_filter( 'rcl_init_js_variables', 'rcl_init_js_chat_variables', 10 );
function rcl_init_js_chat_variables( $data ) {
	global $rcl_options;

	$data['chat']['sounds']     = rcl_addon_url( 'sounds/', __FILE__ );
	$data['chat']['delay']      = ( isset( $rcl_options['chat']['delay'] ) && $rcl_options['chat']['delay'] ) ? $rcl_options['chat']['delay'] : 15;
	$data['chat']['inactivity'] = ( isset( $rcl_options['chat']['inactivity'] ) && $rcl_options['chat']['inactivity'] ) ? $rcl_options['chat']['inactivity'] : 10;
	$data['chat']['file_size']  = ( isset( $rcl_options['chat']['file_size'] ) && $rcl_options['chat']['file_size'] ) ? $rcl_options['chat']['file_size'] : 2;

	$data['local']['empty_mess']       = __( 'Write something', 'wp-recall' );
	$data['local']['max_words']        = __( 'Exceeds the maximum message size', 'wp-recall' );
	$data['local']['upload_size_chat'] = sprintf( __( 'Exceeds the maximum file size! Max. %d MB', 'wp-recall' ), ( int ) $data['chat']['file_size'] );

	return $data;
}

add_filter( 'rcl_inline_styles', 'rcl_chat_add_inline_styles', 10, 2 );
function rcl_chat_add_inline_styles( $styles, $rgb ) {

	list( $r, $g, $b ) = $rgb;

	// разбиваем строку на нужный нам формат
	$rs = round( $r * 0.95 );
	$gs = round( $g * 0.95 );
	$bs = round( $b * 0.95 );

	// $r $g $b - родные цвета от кнопки
	// $rs $gs $bs - темный оттенок от кнопки

	$styles .= '.rcl-chat .message-box::before{border-right-color:rgba(' . $r . ',' . $g . ',' . $b . ',0.15);}'
	           . '.rcl-chat .message-box{background:rgba(' . $r . ',' . $g . ',' . $b . ',0.15);}'
	           . '.rcl-chat .nth .message-box::before{border-right-color:rgba(' . $r . ',' . $g . ',' . $b . ',0.35);}'
	           . '.rcl-chat .nth .message-box {background:rgba(' . $r . ',' . $g . ',' . $b . ',0.35);}';

	if ( ! is_user_logged_in() ) {
		return $styles;
	} // гостям дальше не надо

	$panel = rcl_get_option( 'chat' );

	$styles .= '.rcl-chat .important-shift{background:rgba(' . $rs . ',' . $gs . ',' . $bs . ',0.85);}';

	if ( $panel['contact_panel'] == 0 ) {
		return $styles;
	} // не выводим панель контактов

	$styles .= '.rcl-noread-users,.rcl-chat-panel{background:rgba(' . $rs . ',' . $gs . ',' . $bs . ',0.85);}'
	           . '.rcl-noread-users a.active-chat::before{border-right-color:rgba(' . $rs . ',' . $gs . ',' . $bs . ',0.85);}'
	           . '.left-panel .rcl-noread-users a.active-chat::before{border-left-color:rgba(' . $rs . ',' . $gs . ',' . $bs . ',0.85);}'
	           . '.messages-icon .chat-new-messages{background:rgb(' . $rs . ',' . $gs . ',' . $bs . ');}';

	return $styles;
}

add_action( 'init', 'rcl_add_chat_tab', 10 );
function rcl_add_chat_tab() {
	global $user_ID;

	$tab_data = array(
		'id'       => 'chat',
		'name'     => __( 'Chat', 'wp-recall' ),
		'supports' => array( 'ajax' ),
		'public'   => 1,
		'icon'     => 'fa-comments-o',
		'output'   => 'menu',
		'content'  => array(
			array(
				'id'       => 'private-contacts',
				'name'     => __( 'Contacts', 'wp-recall' ),
				'icon'     => 'fa-book',
				'callback' => array(
					'name' => 'rcl_chat_tab'
				)
			)
		)
	);

	if ( rcl_is_office( $user_ID ) ) {
		$tab_data['content'][] = array(
			'id'       => 'important-messages',
			'name'     => __( 'Important messages', 'wp-recall' ),
			'icon'     => 'fa-star',
			'callback' => array(
				'name' => 'rcl_get_tab_user_important'
			)
		);
	}

	rcl_tab( $tab_data );
}

function rcl_chat_tab( $office_id ) {
	global $user_ID;

	if ( $office_id == $user_ID ) {
		return rcl_get_tab_user_contacts();
	}

	if ( $user_ID ) {
		$chatdata = rcl_get_chat_private( $office_id );
		$chat     = $chatdata['content'];
	} else {
		$chat = rcl_get_notice( array(
			'type' => 'error',
			'text' => __( 'Sign in to send a message to the user', 'wp-recall' )
		) );
	}

	return $chat;
}

function rcl_get_chat_private( $user_id, $args = array() ) {
	global $user_ID;

	$chat_room = rcl_get_private_chat_room( $user_id, $user_ID );

	return rcl_get_the_chat_by_room( $chat_room, $args );
}

function rcl_get_the_chat_by_room( $chat_room, $args = array() ) {
	global $rcl_options;

	$file_upload = ( isset( $rcl_options['chat']['file_upload'] ) ) ? $rcl_options['chat']['file_upload'] : 0;

	$args = array_merge( array(
		'userslist'   => 1,
		'file_upload' => $file_upload,
		'chat_status' => 'private',
		'chat_room'   => $chat_room
	), $args );

	require_once 'class-rcl-chat.php';

	$chat = new Rcl_Chat( $args );

	return array(
		'content' => $chat->get_chat(),
		'token'   => $chat->chat_token
	);
}

function rcl_chat_add_page_link_attributes( $attrs ) {

	$attrs['onclick']      = 'rcl_chat_navi(this); return false;';
	$attrs['class']        = 'rcl-chat-page-link';
	$attrs['href']         = '#';
	$attrs['data']['post'] = false;

	return $attrs;
}

function rcl_get_tab_user_contacts() {
	global $user_ID;

	$content = '<h3>' . __( 'User contacts', 'wp-recall' ) . '</h3>';
	$content .= rcl_get_user_contacts_list( $user_ID );

	return $content;
}

function rcl_get_user_contacts( $user_id, $limit ) {
	global $wpdb;
	//phpcs:disable
	$messages = $wpdb->get_results(
		"SELECT t.* FROM ( "
		. "SELECT chat_messages.* FROM " . RCL_PREF . "chat_messages AS chat_messages "
		. "INNER JOIN " . RCL_PREF . "chat_users AS chat_users ON chat_messages.chat_id=chat_users.chat_id "
		. "WHERE chat_messages.private_key!='0' "
		. "AND (chat_messages.user_id='$user_id' OR chat_messages.private_key='$user_id') "
		. "AND chat_users.user_id='$user_id' "
		. "AND chat_users.user_status!='0' "
		. "ORDER BY chat_messages.message_time DESC "
		. "LIMIT 18446744073709551615 "
		. ") "
		. "AS t "
		. "GROUP BY t.chat_id "
		. "ORDER BY t.message_time DESC "
		. "LIMIT $limit[0],$limit[1]"
		, ARRAY_A
	);

	//phpcs:enable
	return stripslashes_deep( $messages );
}

function rcl_get_user_contacts_list( $user_id ) {
	global $wpdb;
	//phpcs:disable
	$amount = $wpdb->query(
		"SELECT COUNT(chat_messages.chat_id) FROM " . RCL_PREF . "chat_messages AS chat_messages "
		. "INNER JOIN " . RCL_PREF . "chat_users AS chat_users ON chat_messages.chat_id=chat_users.chat_id "
		. "WHERE chat_messages.private_key!='0' "
		. "AND (chat_messages.user_id='$user_id' OR chat_messages.private_key='$user_id') "
		. "AND chat_users.user_id='$user_id' "
		. "AND chat_users.user_status!='0' "
		. "GROUP BY chat_messages.chat_id "
	);
	//phpcs:enable
	if ( ! $amount ) {

		$notice = __( 'No contacts yet. Start a chat with another user on his page', 'wp-recall' );

		if ( rcl_get_option( 'users_page_rcl' ) ) {
			$notice .= '. <a href="' . get_permalink( rcl_get_option( 'users_page_rcl' ) ) . '">' . __( 'Choose from the list of users', 'wp-recall' ) . '</a>.';
		}

		return rcl_get_notice( [
			'text' => apply_filters( 'rcl_chat_no_contacts_notice', $notice, $user_id )
		] );
	}

	rcl_dialog_scripts();

	$inpage = 20;

	$pagenavi = new Rcl_PageNavi( 'chat-contacts', $amount, array( 'in_page' => $inpage ) );

	$messages = rcl_get_user_contacts( $user_id, array( $pagenavi->offset, $inpage ) );

	foreach ( $messages as $k => $message ) {
		$messages[ $k ]['user_id']   = ( $message['user_id'] == $user_id ) ? $message['private_key'] : $message['user_id'];
		$messages[ $k ]['author_id'] = $message['user_id'];
	}

	$content = '<div class="rcl-chat-contacts">';

	$content .= '<div class="contacts-counter"><span>' . __( 'Total number of contacts', 'wp-recall' ) . ': ' . $amount . '</span></div>';

	foreach ( $messages as $message ) {

		$class = ( ! $message['message_status'] ) ? 'noread-message' : '';

		$content .= '<div class="contact-box preloader-parent" data-contact="' . $message['user_id'] . '">';
		$content .= '<a href="#" title="' . __( 'Delete contact', 'wp-recall' ) . '" onclick="rcl_chat_remove_contact(this,' . $message['chat_id'] . ');return false;" class="chat-remove"><i class="rcli fa-times" aria-hidden="true"></i></a>';
		$content .= '<a href="#" title="' . __( 'Open chat in window', 'wp-recall' ) . '" onclick="rcl_get_chat_window(this,' . $message['user_id'] . ');return false;" class="chat-window-restore"><i class="rcli fa-window-restore" aria-hidden="true"></i></a>';
		$content .= '<a class="chat-contact ' . $class . '" href="' . rcl_get_tab_permalink( $message['user_id'], 'chat' ) . '">';

		$content .= '<div class="avatar-contact">'
		            . get_avatar( $message['user_id'], 50 )
		            . '</div>';

		$content .= '<div class="message-content">'
		            . '<div class="message-meta">'
		            . '<span class="author-name">' . get_the_author_meta( 'display_name', $message['user_id'] ) . '</span>'
		            . '<span class="time-message">' . rcl_human_time_diff( $message['message_time'] ) . ' ' . __( 'ago', 'wp-recall' ) . '</span>'
		            . '</div>'
		            . '<div class="message-text">'
		            . ( ( $user_id == $message['author_id'] ) ? '<span class="master-avatar">' . get_avatar( $user_id, 25 ) . '</span>' : '' )
		            . rcl_chat_excerpt( $message['message_content'] )
		            . '</div>'
		            . '</div>';

		$content .= '</a>';

		$content .= '</div>';
	}

	$content .= '</div>';

	$content .= $pagenavi->pagenavi();

	return $content;
}

function rcl_get_tab_user_important( $user_id ) {

	$amount_messages = rcl_chat_count_important_messages( $user_id );

	if ( ! $amount_messages ) {
		return rcl_get_notice( array(
			'type' => 'error',
			'text' => __( 'No important messages yet', 'wp-recall' )
		) );
	}

	require_once 'class-rcl-chat.php';

	$chat = new Rcl_Chat();

	$content = '<div class="rcl-chat">';

	$content .= '<div class="chat-content">';

	$content .= '<div class="chat-messages-box">';

	$content .= '<div class="chat-messages">';

	$pagenavi = new Rcl_PageNavi( 'rcl-chat', $amount_messages, array( 'in_page' => $chat->query['number'] ) );

	$chat->offset = $pagenavi->offset;

	$messages = rcl_chat_get_important_messages( $user_id, array( $pagenavi->offset, $chat->query['number'] ) );

	$messages = rcl_chat_messages_add_important_meta( $messages );

	krsort( $messages );

	foreach ( $messages as $k => $message ) {
		$content .= $chat->get_message_box( $message );
	}

	$content .= '</div>';

	$content .= '</div>';

	$content .= '</div>';

	$content .= '</div>';

	$content .= $pagenavi->pagenavi();

	return $content;
}

add_action( 'wp_footer', 'rcl_get_last_chats_box', 10 );
function rcl_get_last_chats_box() {
	global $user_ID, $user_LK, $rcl_options;

	if ( ! $user_ID ) {
		return false;
	}

	if ( ! isset( $rcl_options['chat']['contact_panel'] ) || ! $rcl_options['chat']['contact_panel'] ) {
		return false;
	}

	$messages = rcl_get_user_contacts( $user_ID, array( 0, 5 ) );

	if ( ! $messages ) {
		return false;
	}

	foreach ( $messages as $message ) {
		$user_id                      = ( $message['user_id'] == $user_ID ) ? $message['private_key'] : $message['user_id'];
		$users[ $user_id ]['status']  = ( ! $message['message_status'] && $message['private_key'] == $user_ID ) ? 0 : 1;
		$users[ $user_id ]['chat_id'] = $message['chat_id'];
	}

	$new_counter = rcl_chat_noread_messages_amount( $user_ID );

	$class = array();

	$class[] = ( ! isset( $rcl_options['chat']['place_contact_panel'] ) || ! $rcl_options['chat']['place_contact_panel'] ) ? 'right-panel' : 'left-panel';

	$class[] = ( ! empty( $_COOKIE['rcl_chat_contact_panel'] ) ) ? '' : 'hidden-contacts';

	echo '<div id="rcl-chat-noread-box" class="' . esc_attr( implode( ' ', $class ) ) . '">';

	echo '<div class="rcl-mini-chat"></div>';

	echo '<div class="rcl-noread-users">';
	echo '<span class="messages-icon">'
	     . '<a href="' . esc_url( rcl_get_tab_permalink( $user_ID, 'chat' ) ) . '" onclick="return rcl_chat_shift_contact_panel();">'
	     . '<i class="rcli fa-envelope" aria-hidden="true"></i>';

	if ( $new_counter ) {
		echo '<span class="chat-new-messages">' . wp_kses_post( $new_counter ) . '</span>';
	}

	echo '</a>'
	     . '</span>'
	     . '<div class="chat-contacts">';

	foreach ( $users as $user_id => $data ) {

		if ( $user_id == $user_LK ) {
			continue;
		}

		echo '<span class="rcl-chat-user contact-box" data-contact="' . esc_attr( $user_id ) . '">';
		echo '<a class="chat-delete-contact" href="#" title="' . esc_html__( 'Delete contact', 'wp-recall' ) . '" onclick="rcl_chat_remove_contact(this,' . esc_js( $data['chat_id'] ) . ');return false;"><i class="rcli fa-times" aria-hidden="true"></i></a>';
		echo '<a href="#" onclick="rcl_get_mini_chat(this,' . esc_js( $user_id ) . '); return false;">';
		if ( ! $data['status'] ) {
			echo '<i class="rcli fa-commenting" aria-hidden="true"></i>';
		}
		echo get_avatar( $user_id, 40 );
		echo '</a>';
		echo '</span>';
	}

	echo '<span class="more-contacts">'
	     . '<a href="' . esc_url( rcl_get_tab_permalink( $user_ID, 'chat' ) ) . '">'
	     . '. . .';
	echo '</a>'
	     . '</span>';

	echo '</div>';

	echo '</div>';

	echo '</div>';
}

function rcl_get_private_chat_room( $user_1, $user_2 ) {
	return ( $user_1 < $user_2 ) ? 'private:' . $user_1 . ':' . $user_2 : 'private:' . $user_2 . ':' . $user_1;
}

function rcl_chat_disable_oembeds() {
	remove_action( 'wp_head', 'wp_oembed_add_host_js' );
}

add_action( 'init', 'rcl_chat_disable_oembeds', 9999 );

add_shortcode( 'rcl-chat', 'rcl_chat_shortcode' );
function rcl_chat_shortcode( $atts ) {
	global $user_ID;

	if ( ! isset( $atts['chat_room'] ) || empty( $atts['chat_room'] ) ) {
		return __( 'Not set attributes: chat_room', 'wp-recall' );
	}

	$file_upload = ( isset( $atts['file_upload'] ) ) ? $atts['file_upload'] : 0;

	if ( $user_ID && $file_upload ) {
		rcl_fileupload_scripts();
	}

	require_once 'class-rcl-chat.php';
	$chat = new Rcl_Chat( $atts );

	return $chat->get_chat();
}

add_action( 'rcl_chat', 'rcl_chat_reset_oembed_filter' );
function rcl_chat_reset_oembed_filter() {
	remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
}

/**
 * @param $room - private:1:3 | custom-room
 */
function rcl_chat_is_private_room( $room ) {
	return strpos( trim( $room ), 'private:' ) === 0;
}

/**
 * @param $user_id
 * @param $room - private:1:3
 *
 * @return bool
 */
function rcl_chat_user_in_room( $user_id, $room ) {
	list( $prefix, $user_1, $user_2 ) = explode( ':', trim( $room ) );

	return in_array( $user_id, [ $user_1, $user_2 ] );
}

include_once 'actions.php';
include_once 'actions-cron.php';
include_once 'actions-ajax.php';
