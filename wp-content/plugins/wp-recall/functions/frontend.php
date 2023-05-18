<?php
add_action( 'wp_head', 'rcl_register_avatar_sizes', 10 );
function rcl_register_avatar_sizes() {

	$sizes = [ 70, 150, 300 ];
	foreach ( $sizes as $k => $size ) {
		add_image_size( 'rcl-avatar-' . $size, $size, $size, 1 );
	}
}

add_action( 'rcl_area_tabs', 'rcl_apply_filters_area_tabs', 10 );
function rcl_apply_filters_area_tabs() {

	$content = '<div id="lk-content" class="rcl-content">';
	$content .= apply_filters( 'rcl_content_area_tabs', '' );
	$content .= '</div>';
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $content;
}

add_action( 'rcl_area_menu', 'rcl_apply_filters_area_menu', 10 );
function rcl_apply_filters_area_menu() {

	$content = '<div id="lk-menu" class="rcl-menu">';
	$content .= apply_filters( 'rcl_content_area_menu', '' );
	$content .= '</div>';
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $content;
}

add_action( 'rcl_area_top', 'rcl_apply_filters_area_top', 10 );
function rcl_apply_filters_area_top() {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'rcl_content_area_top', '' );
}

add_action( 'rcl_area_details', 'rcl_apply_filters_area_details', 10 );
function rcl_apply_filters_area_details() {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'rcl_content_area_details', '' );
}

add_action( 'rcl_area_actions', 'rcl_apply_filters_area_actions', 10 );
function rcl_apply_filters_area_actions() {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'rcl_content_area_actions', '' );
}

add_action( 'rcl_area_counters', 'rcl_apply_filters_area_counters', 10 );
function rcl_apply_filters_area_counters() {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'rcl_content_area_counters', '' );
}

function rcl_user_name() {
	global $rcl_user;
	echo esc_html( $rcl_user->display_name );
}

function rcl_user_url() {
	global $rcl_user;
	echo esc_url( rcl_get_user_url( $rcl_user->ID ) );
}

function rcl_user_avatar( $size = 50 ) {
	global $rcl_user;
	echo get_avatar( $rcl_user->ID, $size );
}

function rcl_user_rayting() {
	global $rcl_user, $rcl_users_set;
	if ( ! rcl_exist_addon( 'rating-system' ) ) {
		return false;
	}
	if ( false !== array_search( 'rating_total', $rcl_users_set->data ) || isset( $rcl_user->rating_total ) ) {
		if ( ! isset( $rcl_user->rating_total ) ) {
			$rcl_user->rating_total = 0;
		}

		echo wp_kses( rcl_rating_block( array( 'value' => $rcl_user->rating_total ) ), rcl_kses_allowed_html() );
	}
}

add_action( 'rcl_user_description', 'rcl_user_meta', 30 );
function rcl_user_meta() {
	global $rcl_user, $rcl_users_set;

	if ( false !== array_search( 'profile_fields', $rcl_users_set->data ) || isset( $rcl_user->profile_fields ) ) {
		if ( ! isset( $rcl_user->profile_fields ) ) {
			$rcl_user->profile_fields = array();
		}

		if ( $rcl_user->profile_fields ) {
			echo '<div class="user-profile-fields">';
			foreach ( $rcl_user->profile_fields as $k => $field ) {
				echo wp_kses( Rcl_Field::setup( $field )->get_field_value( 'title' ), rcl_kses_allowed_html() );
			}
			echo '</div>';
		}
	}
}

add_action( 'rcl_user_description', 'rcl_user_comments', 20 );
function rcl_user_comments() {
	global $rcl_user, $rcl_users_set;
	if ( false !== array_search( 'comments_count', $rcl_users_set->data ) || isset( $rcl_user->comments_count ) ) {
		if ( ! isset( $rcl_user->comments_count ) ) {
			$rcl_user->comments_count = 0;
		}
		echo '<span class="filter-data"><i class="rcli fa-comment"></i>' . esc_html__( 'Comments', 'wp-recall' ) . ': ' . esc_html( $rcl_user->comments_count ) . '</span>';
	}
}

add_action( 'rcl_user_description', 'rcl_user_posts', 20 );
function rcl_user_posts() {
	global $rcl_user, $rcl_users_set;
	if ( false !== array_search( 'posts_count', $rcl_users_set->data ) || isset( $rcl_user->posts_count ) ) {
		if ( ! isset( $rcl_user->posts_count ) ) {
			$rcl_user->posts_count = 0;
		}
		echo '<span class="filter-data"><i class="rcli fa-file-text-o"></i>' . esc_html__( 'Publics', 'wp-recall' ) . ': ' . esc_html( $rcl_user->posts_count ) . '</span>';
	}
}

function rcl_user_action( $type = 1 ) {
	global $rcl_user;

	$action = ( isset( $rcl_user->time_action ) ) ? $rcl_user->time_action : $rcl_user->user_registered;

	switch ( $type ) {
		case 1:
			$last_action = rcl_get_useraction( $action );
			if ( ! $last_action ) {
				echo '<span class="status_user online"><i class="rcli fa-circle"></i></span>';
			} else {
				echo '<span class="status_user offline" title="' . esc_html__( 'offline', 'wp-recall' ) . ' ' . esc_attr( $last_action ) . '"><i class="rcli fa-circle"></i></span>';
			}
			break;
		case 2:
			echo wp_kses_post( rcl_get_miniaction( $action ) );
			break;
	}
}

function rcl_user_description() {
	global $rcl_user;

	if ( isset( $rcl_user->description ) && $rcl_user->description ) {
		echo '<div class="ballun-status">';
		echo '<div class="status-user-rcl">' . nl2br( esc_html( $rcl_user->description ) ) . '</div>
        </div>';
	}

	do_action( 'rcl_user_description' );
}

add_action( 'rcl_user_description', 'rcl_user_register', 20 );
function rcl_user_register() {
	global $rcl_user, $rcl_users_set;
	if ( false !== array_search( 'user_registered', $rcl_users_set->data ) || isset( $rcl_user->user_registered ) ) {
		if ( ! isset( $rcl_user->user_registered ) ) {
			return false;
		}
		echo '<span class="filter-data"><i class="rcli fa-calendar-check-o"></i>' . esc_html__( 'Registration', 'wp-recall' ) . ': ' . esc_html( mysql2date( 'd-m-Y', $rcl_user->user_registered ) ) . '</span>';
	}
}

add_action( 'rcl_user_description', 'rcl_filter_user_description', 10 );
function rcl_filter_user_description() {
	global $rcl_user;
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'rcl_description_user', '', $rcl_user->ID );
}

add_filter( 'users_search_form_rcl', 'rcl_default_search_form' );
function rcl_default_search_form( $form ) {
	global $user_LK, $rcl_tab;

	$search_text  = ( ( isset( $_GET['search_text'] ) ) ) ? sanitize_text_field( wp_unslash( $_GET['search_text'] ) ) : '';
	$search_field = ( isset( $_GET['search_field'] ) ) ? sanitize_key( $_GET['search_field'] ) : '';

	$form .= '<div class="rcl-search-form">
            <form method="get">
                <div class="rcl-search-form-title">' . __( 'Search users', 'wp-recall' ) . '</div>
                <input type="text" name="search_text" value="' . esc_attr( $search_text ) . '">
                <select name="search_field">
                    <option ' . selected( $search_field, 'display_name', false ) . ' value="display_name">' . esc_html__( 'by name', 'wp-recall' ) . '</option>
                    <option ' . selected( $search_field, 'user_login', false ) . ' value="user_login">' . esc_html__( 'by login', 'wp-recall' ) . '</option>
                </select>'
	         . rcl_get_button( array(
			'label'  => __( 'Search', 'wp-recall' ),
			'submit' => true
		) )
	         . '<input type="hidden" name="default-search" value="1">';

	if ( $user_LK && $rcl_tab ) {

		$get = rcl_get_option( 'link_user_lk_rcl', 'user' );

		$form .= '<input type="hidden" name="' . $get . '" value="' . $user_LK . '">';
		$form .= '<input type="hidden" name="tab" value="' . $rcl_tab->id . '">';
	}

	$form .= '</form>
        </div>';

	return $form;
}

function rcl_action() {
	global $rcl_userlk_action;
	$last_action = rcl_get_useraction( $rcl_userlk_action );
	$class       = ( ! $last_action ) ? 'online' : 'offline';

	if ( $last_action ) {
		$status = esc_html__( 'offline', 'wp-recall' ) . ' ' . $last_action;
	} else {
		$status = esc_html__( 'online', 'wp-recall' );
	}

	echo sprintf( '<span class="user-status %s">%s</span>', esc_attr( $class ), esc_html( $status ) );
}

function rcl_avatar( $avatar_size = 120, $attr = false ) {
	global $user_LK;
	?>
    <div id="rcl-avatar">
		<span class="avatar-image">
			<?php echo get_avatar( $user_LK, $avatar_size, false, false, $attr ); ?>
			<span id="avatar-upload-progress"><span></span></span>
		</span>
		<?php do_action( 'rcl_avatar' ); ?>
    </div>
	<?php
}

add_action( 'rcl_avatar', 'rcl_setup_avatar_icons', 10 );
function rcl_setup_avatar_icons() {

	$icons = rcl_avatar_icons();

	if ( ! $icons ) {
		return false;
	}

	$html = array();
	foreach ( $icons as $icon_id => $icon ) {

		$atts = array();

		if ( isset( $icon['atts'] ) ) {
			foreach ( $icon['atts'] as $attr => $val ) {
				$val    = ( is_array( $val ) ) ? implode( ' ', $val ) : $val;
				$atts[] = $attr . '="' . $val . '"';
			}
		}

		$string = '<a ' . implode( ' ', $atts ) . '>';

		if ( isset( $icon['icon'] ) ) {
			$string .= '<i class="rcli ' . $icon['icon'] . '"></i>';
		}

		if ( isset( $icon['content'] ) ) {
			$string .= $icon['content'];
		}

		$string .= '</a>';

		$html[] = '<span class="rcl-avatar-icon icon-' . $icon_id . '">' . $string . '</span>';
	}

	echo '<span class="avatar-icons">' . wp_kses( implode( '', $html ), rcl_kses_allowed_html() ) . '</span>';
}

function rcl_avatar_icons() {
	return apply_filters( 'rcl_avatar_icons', array() );
}

function rcl_status_desc() {
	global $user_LK;
	$desc = get_the_author_meta( 'description', $user_LK );
	if ( $desc ) {
		echo '<div class="ballun-status">'
		     . '<div class="status-user-rcl">' . esc_html( $desc ) . '</div>'
		     . '</div>';
	}
}

function rcl_username() {
	global $user_LK;
	echo esc_html( get_the_author_meta( 'display_name', $user_LK ) );
}

function rcl_notice() {
	if ( $notify = apply_filters( 'notify_lk', '' ) ) {
		echo wp_kses_post( rcl_get_notice( [ 'text' => $notify ] ) );
	}
}

//добавляем стили колорпикера и другие в хеадер
add_action( 'wp_head', 'rcl_inline_styles', 100 );
function rcl_inline_styles() {

	list( $r, $g, $b ) = ( $color = rcl_get_option( 'primary-color' ) ) ? sscanf( $color, "#%02x%02x%02x" ) : array(
		76,
		140,
		189
	);

	$styles = apply_filters( 'rcl_inline_styles', '', array( $r, $g, $b ) );

	if ( ! $styles ) {
		return false;
	}

	// удаляем пробелы, переносы, табуляцию
	$styles = preg_replace( '/ {2,}/', '', str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $styles ) );
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "<style>" . $styles . "</style>\r\n";
}

add_filter( 'rcl_inline_styles', 'rcl_default_inline_styles', 5, 2 );
function rcl_default_inline_styles( $styles, $rgb ) {

	list( $r, $g, $b ) = $rgb;

	$styles .= 'a.recall-button,
    span.recall-button,
    .recall-button.rcl-upload-button,
    input[type="submit"].recall-button,
    input[type="submit"] .recall-button,
    input[type="button"].recall-button,
    input[type="button"] .recall-button,
    a.recall-button:hover,
    .recall-button.rcl-upload-button:hover,
    input[type="submit"].recall-button:hover,
    input[type="submit"] .recall-button:hover,
    input[type="button"].recall-button:hover,
    input[type="button"] .recall-button:hover{
        background: rgb(' . $r . ', ' . $g . ', ' . $b . ');
    }
    a.recall-button.active,
    a.recall-button.active:hover,
    a.recall-button.filter-active,
    a.recall-button.filter-active:hover,
    a.data-filter.filter-active,
    a.data-filter.filter-active:hover{
        background: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.4);
    }
    .rcl_preloader i{
        color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .rcl-user-getails .status-user-rcl::before{
        border-left-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .rows-list .status-user-rcl::before{
        border-top-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .status-user-rcl{
        border-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }
    .rcl-field-input input[type="checkbox"]:checked + label.block-label::before,
    .rcl-field-input input[type="radio"]:checked + label.block-label::before{
        background:rgb(' . $r . ',' . $g . ',' . $b . ');
        border-color:rgb(' . $r . ',' . $g . ',' . $b . ');
    }';

	return $styles;
}

// background color button api
add_filter( 'rcl_inline_styles', 'rcl_api_button_inline_background', 10, 2 );
function rcl_api_button_inline_background( $styles, $rgb ) {
	list( $r, $g, $b ) = $rgb;
	$background_color = $r . ',' . $g . ',' . $b;

	$styles .= '
		body .rcl-bttn.rcl-bttn__type-primary {
			background-color: rgb(' . $background_color . ');
		}
		.rcl-bttn.rcl-bttn__type-primary.rcl-bttn__active {
			background-color: rgba(' . $r . ', ' . $g . ', ' . $b . ', 0.4);
		}
		.rcl-bttn.rcl-bttn__type-simple.rcl-bttn__active {
			box-shadow: 0 -5px 0 -3px rgb(' . $r . ', ' . $g . ', ' . $b . ') inset;
		}
	';

	return $styles;
}

// color button api
add_filter( 'rcl_inline_styles', 'rcl_api_button_inline_color', 10 );
function rcl_api_button_inline_color( $styles ) {
	$color_button = rcl_get_option( 'rcl-button-text-color', '#fff' );

	$styles .= '
		body .rcl-bttn.rcl-bttn__type-primary {
			color: ' . $color_button . ';
		}
	';

	return $styles;
}

// size button api
add_filter( 'rcl_inline_styles', 'rcl_api_button_inline_size', 10 );
function rcl_api_button_inline_size( $styles ) {
	$size = rcl_get_option( 'rcl-button-font-size', '14' );

	$styles .= '
		body .rcl-bttn,
		.rcl-bttn.rcl-bttn__size-small {
			font-size: ' . 0.86 * $size . 'px;
		}
		.rcl-bttn.rcl-bttn__size-standart {
			font-size: ' . $size . 'px;
		}
		.rcl-bttn.rcl-bttn__size-medium {
			font-size: ' . 1.16 * $size . 'px;
		}
		.rcl-bttn__type-clear.rcl-bttn__mod-only-icon.rcl-bttn__size-medium,
		.rcl-bttn.rcl-bttn__size-large {
			font-size: ' . 1.33 * $size . 'px;
		}
		.rcl-bttn.rcl-bttn__size-big {
			font-size: ' . 1.5 * $size . 'px;
		}
		.rcl-bttn__type-clear.rcl-bttn__mod-only-icon.rcl-bttn__size-large {
			font-size: ' . 1.66 * $size . 'px;
		}
		.rcl-bttn__type-clear.rcl-bttn__mod-only-icon.rcl-bttn__size-big {
			font-size: ' . 2 * $size . 'px;
		}
	';

	return $styles;
}

// css variable
// Основные цвета WP-Recall переведем в css переменные
// для удобства: hex и rgb значения - чтобы потом самим css генерировать как прозрачность текста (rgba)
add_filter( 'rcl_inline_styles', 'rcl_css_variable', 10, 2 );
function rcl_css_variable( $styles, $rgb ) {
	$rcl_color = rcl_get_option( 'primary-color', '#4c8cbd' );

	list( $r, $g, $b ) = $rgb;

	// темнее rgb
	$rd = round( $r * 0.45 );
	$gd = round( $g * 0.45 );
	$bd = round( $b * 0.45 );

	// ярче rgb
	$rl = round( $r * 1.4 );
	$gl = round( $g * 1.4 );
	$bl = round( $b * 1.4 );

	// инверт rgb
	$rf = round( 0.75 * ( 255 - $r ) );
	$gf = round( 0.75 * ( 255 - $g ) );
	$bf = round( 0.75 * ( 255 - $b ) );

	// https://stackoverflow.com/questions/3942878/how-to-decide-font-color-in-white-or-black-depending-on-background-color
	$text_color = '';
	$threshold  = apply_filters( 'rcl_text_color_threshold', 150 );
	if ( ( $r * 0.299 + $g * 0.587 + $b * 0.114 ) > $threshold ) {
		$text_color = '--rclText:#000;';
	}

	$styles .= '
:root{
' . $text_color . ';
--rclHex:' . $rcl_color . ';
--rclRgb:' . $r . ',' . $g . ',' . $b . ';
--rclRgbDark:' . $rd . ',' . $gd . ',' . $bd . ';
--rclRgbLight:' . $rl . ',' . $gl . ',' . $bl . ';
--rclRgbFlip:' . $rf . ',' . $gf . ',' . $bf . ';
}
';

	return $styles;
}

add_action( 'wp_footer', 'rcl_init_footer_action', 100 );
function rcl_init_footer_action() {
	echo '<script>rcl_do_action("rcl_footer")</script>';
}

add_action( 'wp_footer', 'rcl_popup_contayner', 4 );
function rcl_popup_contayner() {
	echo '<div id="rcl-overlay"></div>
        <div id="rcl-popup"></div>';
}

function rcl_get_author_block() {
	global $post;

	$content = "<div id=block_author-rcl>";
	$content .= "<h3>" . __( 'Publication author', 'wp-recall' ) . "</h3>";

	if ( function_exists( 'rcl_add_userlist_follow_button' ) ) {
		add_filter( 'rcl_user_description', 'rcl_add_userlist_follow_button', 90 );
	}

	$content .= rcl_get_userlist( array(
		'template' => 'rows',
		'orderby'  => 'display_name',
		'include'  => $post->post_author,
		'filter'   => 0,
		'data'     => 'rating_total,description,posts_count,user_registered,comments_count'
	) );

	if ( function_exists( 'rcl_add_userlist_follow_button' ) ) {
		remove_filter( 'rcl_user_description', 'rcl_add_userlist_follow_button', 90 );
	}

	$content .= "</div>";

	return $content;
}

function rcl_get_time_user_action( $user_id ) {

	$cachekey = json_encode( array( 'rcl_get_time_user_action', ( int ) $user_id ) );
	$cache    = wp_cache_get( $cachekey );
	if ( $cache ) {
		return $cache;
	}

	$action = RQ::tbl( new Rcl_User_Action() )->select( [ 'time_action' ] )->where( [ 'user' => $user_id ] )->get_var();

	if ( ! $action ) {
		$action = '0000-00-00 00:00:00';
	}

	wp_cache_add( $cachekey, $action, 'default', rcl_get_option( 'timeout', 10 ) * 60 );

	return $action;
}

function rcl_get_miniaction( $action ) {
	global $rcl_user;

	if ( ! $action ) {
		$action = rcl_get_time_user_action( $rcl_user->ID );
	}

	$last_action = rcl_get_useraction( $action );

	$class = ( ! $last_action && $action ) ? 'online' : 'offline';

	$content = apply_filters( 'rcl_before_miniaction', '' );

	$content .= ( ! $last_action && $action ) ? '<i class="rcli fa-circle"></i>' : __( 'offline', 'wp-recall' ) . ' ' . $last_action;

	return sprintf( '<div class="status_author_mess %s">%s</div>', $class, $content );
}

//заменяем ссылку автора комментария на ссылку его ЛК
add_filter( 'get_comment_author_url', 'rcl_get_link_author_comment', 10 );
function rcl_get_link_author_comment( $url ) {
	global $comment;
	if ( ! isset( $comment ) || $comment->user_id == 0 ) {
		return $url;
	}

	return rcl_get_user_url( $comment->user_id );
}

add_action( 'wp_head', 'rcl_hidden_admin_panel' );
function rcl_hidden_admin_panel() {
	global $user_ID;

	if ( ! $user_ID ) {
		return show_admin_bar( false );
	}

	$access = rcl_check_access_console();

	if ( $access ) {
		return true;
	}

	show_admin_bar( false );
}

add_action( 'init', 'rcl_banned_user_redirect' );
function rcl_banned_user_redirect() {
	global $user_ID;
	if ( ! $user_ID ) {
		return false;
	}
	if ( rcl_is_user_role( $user_ID, 'banned' ) ) {
		wp_die( esc_html__( 'Congratulations! You have been banned.', 'wp-recall' ) );
	}
}

add_filter( 'the_content', 'rcl_message_post_moderation' );
function rcl_message_post_moderation( $content ) {
	global $post;

	if ( ! isset( $post ) || ! $post ) {
		return $content;
	}

	if ( $post->post_status == 'pending' ) {
		$content = rcl_get_notice( [
				'text' => __( 'Publication pending approval!', 'wp-recall' ),
				'type' => 'error'
			] ) . $content;
	}

	if ( $post->post_status == 'draft' ) {
		$content = rcl_get_notice( [ 'text' => __( 'Draft of a post!', 'wp-recall' ), 'type' => 'error' ] ) . $content;
	}

	return $content;
}

function rcl_sort_gallery( $attaches, $key, $user_id = false ) {
	global $user_ID;

	if ( ! $attaches ) {
		return false;
	}
	$gallerylist = [];
	$news        = [];
	if ( ! $user_id ) {
		$user_id = $user_ID;
	}
	$cnt = count( $attaches );
	$v   = $cnt + 10;
	foreach ( $attaches as $attach ) {
		$id = str_replace( $key . '-' . $user_id . '-', '', $attach->post_name );
		if ( ! is_numeric( $id ) || $id > 100 ) {
			$id = $v ++;
		}
		if ( ! $id ) {
			$id = 0;
		}
		foreach ( $attach as $k => $att ) {
			$gallerylist[ ( int ) $id ][ $k ] = $attach->$k;
		}
	}

	$b   = 0;
	$cnt = count( $gallerylist );
	for ( $a = 0; $b < $cnt; $a ++ ) {
		if ( ! isset( $gallerylist[ $a ] ) ) {
			continue;
		}
		$new[ $b ] = $gallerylist[ $a ];
		$b ++;
	}
	for ( $a = $cnt - 1; $a >= 0; $a -- ) {
		$news[] = ( object ) $new[ $a ];
	}

	return $news;
}

function rcl_bar_add_icon( $id_icon, $args ) {
	global $rcl_bar;
	if ( ! rcl_get_option( 'view_recallbar' ) ) {
		return false;
	}
	$rcl_bar['icons'][ $id_icon ] = $args;

	return true;
}

function rcl_bar_add_menu_item( $id_item, $args ) {
	global $rcl_bar;
	if ( ! rcl_get_option( 'view_recallbar' ) ) {
		return false;
	}
	$rcl_bar['menu'][ $id_item ] = $args;

	return true;
}

add_action( 'init', 'rcl_add_block_black_list_button', 10 );
function rcl_add_block_black_list_button() {
	rcl_block( 'actions', 'rcl_user_black_list_button', array( 'id' => 'bl-block', 'order' => 50, 'public' => - 1 ) );
}

function rcl_user_black_list_button( $office_id ) {
	global $user_ID;

	$user_block = get_user_meta( $user_ID, 'rcl_black_list:' . $office_id );

	$title = ( $user_block ) ? __( 'Unblock', 'wp-recall' ) : __( 'Blacklist', 'wp-recall' );

	return rcl_get_button( [
		'label'   => $title,
		'class'   => 'rcl-manage-blacklist',
		'icon'    => 'fa-bug',
		'onclick' => 'rcl_manage_user_black_list(this,' . $office_id . ',"' . __( 'Are you sure?', 'wp-recall' ) . '");return false;'
	] );
}

add_filter( 'rcl_tabs', 'rcl_check_user_blocked', 10 );
function rcl_check_user_blocked( $rcl_tabs ) {
	global $user_ID, $user_LK;
	if ( $user_LK && $user_LK != $user_ID ) {
		$user_block = get_user_meta( $user_LK, 'rcl_black_list:' . $user_ID );
		if ( $user_block ) {
			$rcl_tabs = array();
			add_action( 'rcl_area_tabs', 'rcl_add_user_blocked_notice', 10 );
		}
	}

	return $rcl_tabs;
}

function rcl_add_user_blocked_notice() {
	echo wp_kses_post( rcl_get_notice( [
		'text' => esc_html__( 'The user has restricted access to their page', 'wp-recall' ),
		'type' => 'error'
	] ) );
}

add_action( 'wp', 'rcl_post_bar_setup', 10 );
function rcl_post_bar_setup() {
	do_action( 'rcl_post_bar_setup' );
}

function rcl_post_bar_add_item( $id_item, $args ) {
	global $rcl_post_bar;

	if ( isset( $args['url'] ) ) {
		$args['href'] = $args['url'];
	}

	$rcl_post_bar['items'][ $id_item ] = $args;

	return true;
}

add_filter( 'the_content', 'rcl_post_bar', 999 );
function rcl_post_bar( $content ) {
	global $rcl_post_bar;

	if ( doing_filter( 'get_the_excerpt' ) || ! is_single() || is_front_page() ) {
		return $content;
	}

	$rcl_bar_items = apply_filters( 'rcl_post_bar_items', !empty($rcl_post_bar['items'])? $rcl_post_bar['items']: [] );

	if ( ! isset( $rcl_bar_items ) || ! $rcl_bar_items ) {
		return $content;
	}


	$bar = '<div id="rcl-post-bar">';

	foreach ( $rcl_bar_items as $id_item => $item ) {

		$bar .= '<div id="bar-item-' . $id_item . '" class="post-bar-item">';

		$bar .= rcl_get_button( $item );

		$bar .= '</div>';
	}

	$bar .= '</div>';

	return $bar . $content;
}
