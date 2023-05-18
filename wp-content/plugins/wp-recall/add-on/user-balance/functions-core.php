<?php

function rcl_gateways() {
	global $rcl_gateways;

	return $rcl_gateways;
}

function rcl_gateway_register( $gateway_id, $gatewayClassName ) {
	rcl_gateways()->add_gateway( $gateway_id, $gatewayClassName );
}

//получение данных из таблицы произведенных платежей
function rcl_get_payments( $args = false ) {
	require_once 'classes/class-rcl-payments.php';

	return RQ::tbl( new Rcl_Payments() )->parse( $args )->get_results();
}

function rcl_get_user_balance( $user_id = false ) {
	global $wpdb, $user_ID;

	if ( ! $user_id ) {
		$user_id = $user_ID;
	}

	if ( $user_id == $user_ID && isset( RCL()->User()->balance ) ) {
		return RCL()->User()->balance;
	}
	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$balance = $wpdb->get_var( $wpdb->prepare( "SELECT user_balance FROM " . RMAG_PREF . "users_balance WHERE user_id=%d", $user_id ) );

	$userBalance = $balance ? $balance : 0;

	if ( $user_id == $user_ID ) {
		RCL()->User()->balance = $userBalance;
	}

	return $userBalance;
}

function rcl_update_user_balance( $newmoney, $user_id, $comment = '' ) {
	global $wpdb, $user_ID;

	$newmoney = rcl_commercial_round( str_replace( ',', '.', $newmoney ) );
	//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$balance = $wpdb->get_var( $wpdb->prepare( "SELECT user_balance FROM " . RMAG_PREF . "users_balance WHERE user_id=%d", $user_id ) );

	if ( isset( $balance ) ) {

		do_action( 'rcl_pre_update_user_balance', $newmoney, $user_id, $comment );

		$result = $wpdb->update( RMAG_PREF . 'users_balance', array( 'user_balance' => $newmoney ), array( 'user_id' => $user_id ) );

		if ( ! $result ) {
			rcl_add_log(
				'rcl_update_user_balance: ' . __( 'Failed to refresh user balance', 'wp-recall' ), array(
					$newmoney,
					$user_id,
					$comment
				)
			);
		}

		if ( $user_id == $user_ID ) {
			RCL()->User()->balance = $newmoney;
		}

		return $result;
	}

	return rcl_add_user_balance( $newmoney, $user_id, $comment );
}

function rcl_add_user_balance( $money, $user_id, $comment = '' ) {
	global $wpdb, $user_ID;

	$result = $wpdb->insert( RMAG_PREF . 'users_balance', array( 'user_id' => $user_id, 'user_balance' => $money ) );

	if ( ! $result ) {
		rcl_add_log(
			'rcl_add_user_balance: ' . __( 'Failed to add user balance', 'wp-recall' ), array(
				$money,
				$user_id,
				$comment
			)
		);
	}

	if ( $user_id == $user_ID ) {
		RCL()->User()->balance = $money;
	}

	do_action( 'rcl_add_user_balance', $money, $user_id, $comment );

	return $result;
}

function rcl_get_html_usercount() {
	global $rcl_gateways;

	$user_count = rcl_get_user_balance();

	if ( ! $user_count ) {
		$user_count = 0;
	}

	$content = '<div class="rcl-balance-widget">';
	$content .= '<div class="balance-amount">';
	$content .= '<span class="amount-title">' . esc_html__( 'Your balance', 'wp-recall' ) . ':</span>';
	$content .= '<span class="amount-size">' . apply_filters( 'rcl_html_usercount', $user_count . ' ' . rcl_get_primary_currency( 1 ), $user_count ) . '</span>';

	if ( $rcl_gateways && count( $rcl_gateways->gateways ) > 1 ) {
		$content .= ' ' . rcl_get_button( [
				'label'   => __( 'replenish', 'wp-recall' ),
				'onclick' => 'rcl_switch_view_balance_form(this);return false;',
				'icon'    => 'fa-plus-circle',
				'type'    => 'clear',
				'class'   => 'update-link'
			] );
	}

	$content .= '</div>';

	if ( $rcl_gateways && count( $rcl_gateways->gateways ) > 1 ) {
		$content .= '<div class="balance-form">';
		$content .= rcl_form_user_balance();
		$content .= '</div>';
	}

	$content .= '</div>';

	return $content;
}

function rcl_mail_payment_error( $hash = false, $other = false ) {
	global $post;

	$textmail = '';
	if ( $other ) {
		foreach ( $other as $k => $v ) {
			$textmail .= $k . ' - ' . $v . '<br>';
		}
	}

	foreach ( $_REQUEST as $key => $R ) {
		$textmail .= $key . ' - ' . $R . '<br>';
	}

	if ( $hash ) {
		$textmail .= 'Cформированный хеш - ' . $hash . '<br>';
		$title    = 'Неудачная оплата';
	} else {
		$title = 'Данные платежа';
	}

	$textmail .= 'Текущий пост - ' . $post->ID . '<br>';
	$textmail .= 'RESULT - ' . rcl_get_commerce_option( 'page_result_pay' ) . '<br>';
	$textmail .= 'SUCCESS - ' . rcl_get_commerce_option( 'page_success_pay' ) . '<br>';

	$email = get_site_option( 'admin_email' );

	rcl_mail( $email, $title, $textmail );
}
