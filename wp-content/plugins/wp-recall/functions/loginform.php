<?php

function rcl_login_form() {

	echo wp_kses( rcl_get_authorize_form( 'floatform' ), rcl_kses_allowed_html() );
}

add_shortcode( 'loginform', 'rcl_get_login_form' );
function rcl_get_login_form( $atts ) {
	$form = false;
	extract( shortcode_atts( array( 'form' => false ), $atts ) );

	return rcl_get_authorize_form( 'pageform', $form );
}

function rcl_get_authorize_form( $type = false, $form = false ) {
	global $user_ID, $rcl_user_URL, $typeform;
	$typeform = $form;

	$can_register = rcl_is_register_open();

	ob_start();

	echo '<div class="rcl-loginform rcl-loginform-' . ( ( $form ) ? esc_attr( $form ) : 'full' ) . ' panel_lk_recall ' . esc_attr( $type ) . '">';

	if ( $user_ID ) {

		echo '<div class="username"><b>' . esc_html__( 'Hi', 'wp-recall' ) . ', ' . esc_html( get_the_author_meta( 'display_name', $user_ID ) ) . '!</b></div>
            <div class="author-avatar">';
		echo '<a href="' . esc_url( $rcl_user_URL ) . '" title="' . esc_html__( 'To personal account', 'wp-recall' ) . '">' . get_avatar( $user_ID, 60 ) . '</a>';

		if ( function_exists( 'rcl_rating_block' ) ):
			echo wp_kses( rcl_rating_block( array( 'ID' => $user_ID, 'type' => 'user' ) ), rcl_kses_allowed_html() );
		endif;

		echo '</div>';

		$buttons = array(
			rcl_get_button( [
				'href'  => $rcl_user_URL,
				'label' => esc_html__( 'To personal account', 'wp-recall' ),
				'icon'  => 'fa-home'
			] ),
			rcl_get_button( [
				'href'  => wp_logout_url( home_url() ),
				'label' => esc_html__( 'Exit', 'wp-recall' ),
				'icon'  => 'fa-external-link'
			] )
		);
		echo wp_kses( rcl_get_primary_widget_buttons( $buttons ), rcl_kses_allowed_html() );
	} else {

		$login_form = rcl_get_option( 'login_form_recall' );

		if ( $login_form == 1 && $type != 'pageform' ) {

			$redirect_url = rcl_format_url( get_permalink( rcl_get_option( 'page_login_form_recall' ) ) );

			$buttons = array(
				rcl_get_button( [
					'href'  => $redirect_url . 'action-rcl=login',
					'label' => esc_html__( 'Entry', 'wp-recall' ),
					'icon'  => 'fa-sign-in'
				] )
			);

			if ( $can_register ) {
				$buttons[] = rcl_get_button( [
					'href'  => $redirect_url . 'action-rcl=register',
					'label' => esc_html__( 'Registration', 'wp-recall' ),
					'icon'  => 'fa-book'
				] );
			}
			echo wp_kses( rcl_get_primary_widget_buttons( $buttons ), rcl_kses_allowed_html() );
		} else if ( $login_form == 2 ) {

			$buttons = array(
				rcl_get_button( [
					'href'  => esc_url( wp_login_url( '/' ) ),
					'label' => esc_html__( 'Entry', 'wp-recall' ),
					'icon'  => 'fa-sign-in'
				] )
			);

			if ( $can_register ) {
				$buttons[] = rcl_get_button( [
					'href'  => esc_url( wp_registration_url() ),
					'label' => esc_html__( 'Registration', 'wp-recall' ),
					'icon'  => 'fa-book'
				] );
			}
			echo wp_kses( rcl_get_primary_widget_buttons( $buttons ), rcl_kses_allowed_html() );
		} else if ( $login_form == 3 || $type ) {

			if ( $typeform != 'register' ) {
				rcl_include_template( 'form-sign.php' );
			}
			if ( $typeform != 'sign' && $can_register ) {
				rcl_include_template( 'form-register.php' );
			}
			if ( ! $typeform || $typeform == 'sign' ) {
				rcl_include_template( 'form-remember.php' );
			}
		} else if ( ! $login_form ) {

			$buttons = array(
				rcl_get_button( [
					'class' => 'rcl-login',
					'label' => esc_html__( 'Entry', 'wp-recall' ),
					'icon'  => 'fa-sign-in'
				] )
			);

			if ( $can_register ) {
				$buttons[] = rcl_get_button( [
					'class' => 'rcl-register',
					'label' => esc_html__( 'Registration', 'wp-recall' ),
					'icon'  => 'fa-book'
				] );
			}
			echo wp_kses( rcl_get_primary_widget_buttons( $buttons ), rcl_kses_allowed_html() );
		}
	}

	echo '</div>';

	if ( ! $user_ID && $type ) {
		echo '<script>rcl_do_action("rcl_login_form","' . esc_js( $type ) . '")</script>';
	}

	$html = ob_get_contents();
	ob_end_clean();

	return $html;
}

function rcl_get_primary_widget_buttons( $buttons ) {

	$content = '';

	$buttons = apply_filters( 'rcl_primary_widget_buttons', $buttons );

	if ( $buttons ) {

		foreach ( $buttons as $button ) {
			$content .= sprintf( '<div class="rcl-widget-button">%s</div>', $button );
		}
	}

	return sprintf( '<div class="rcl-widget-buttons">%s</div>', apply_filters( 'buttons_widget_rcl', $content ) );
}

function rcl_get_loginform_url( $type ) {

	if ( $type == 'login' ) {
		switch ( rcl_get_option( 'login_form_recall' ) ) {
			case 1:
				return rcl_format_url( get_permalink( rcl_get_option( 'page_login_form_recall' ) ) ) . 'action-rcl=login';
				break;
			case 2:
				return wp_login_url( get_permalink( rcl_get_option( 'page_login_form_recall' ) ) );
				break;
			default:
				return '#';
				break;
		}
	}

	if ( $type == 'register' ) {
		switch ( rcl_get_option( 'login_form_recall' ) ) {
			case 1:
				return rcl_format_url( get_permalink( rcl_get_option( 'page_login_form_recall' ) ) ) . 'action-rcl=register';
				break;
			case 2:
				return wp_registration_url();
				break;
			default:
				return '#';
				break;
		}
	}
}
