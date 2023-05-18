<?php

add_filter( 'rcl_commerce_options', 'rcl_user_account_options', 10 );
function rcl_user_account_options( $options ) {
	global $rcl_gateways;

	require_once RCL_PATH . 'classes/class-rcl-options.php';

	$payment_opt = array( __( 'Payment from user’s personal account', 'wp-recall' ) );

	$systems = array();
	foreach ( rcl_gateways()->gateways as $id => $className ) {
		if ( $id == 'user_balance' || rcl_gateways()->gateway( $id )->handle_activate ) {
			continue;
		}
		$systems[ $id ] = rcl_gateways()->gateway( $id )->label;
	}

	if ( $systems ) {
		$payment_opt[] = __( 'Payment through payment systems', 'wp-recall' );
		$payment_opt[] = __( 'Offer both options', 'wp-recall' );
	}

	if ( $options->isset_box( 'shop' ) ) {
		$options->box( 'shop' )->add_group( 'order-payment', array(
			'title' => __( 'The payment of an order', 'wp-recall' )
		) )->add_options( array(
			array(
				'type'   => 'select',
				'slug'   => 'type_order_payment',
				'values' => $payment_opt,
				'notice' => __( 'If the connection to the payment aggregator not used, apply "Funds from user personal account"!', 'wp-recall' )
			)
		) );
	}

	$group = $options->add_box( 'payments', array(
		'title' => __( 'Settings of payments', 'wp-recall' )
	) )->add_group( 'primary', array(
		'title' => __( 'General settings of payments', 'wp-recall' )
	) );

	$groupOptions = array(
		array(
			'type'   => 'select',
			'title'  => __( 'The general currency', 'wp-recall' ),
			'slug'   => 'primary_cur',
			'values' => rcl_get_currency()
		)
	);

	if ( $systems ) {

		$groupOptions[] = array(
			'type'   => 'checkbox',
			'title'  => __( 'The using payments systems', 'wp-recall' ),
			'slug'   => 'payment_gateways',
			'values' => $systems,
			'value'  => rcl_get_commerce_option( 'payment_gateways', rcl_get_commerce_option( 'connect_sale' ) ),
			'notice' => __( 'Applied connection type', 'wp-recall' )
		);

		$groupOptions[] = array(
			'type'  => 'text',
			'title' => __( 'The caption on the button of the confirmation of a way of a payment', 'wp-recall' ),
			'slug'  => 'submit_choose',
			'value' => $group->get_value( 'submit_choose' ) ? $group->get_value( 'submit_choose' ) : __( 'Continue' ),
		);

		$groupOptions[] = array(
			'type'    => 'custom',
			'title'   => __( 'Service page of payment systems', 'wp-recall' ),
			'slug'    => 'service-pages-notice',
			'content' => '1. Создайте на своем сайте четыре страницы:<br>
				- пустую для success<br>
				- пустую для result<br>
				- одну с текстом о неудачной оплате (fail)<br>
				- одну с текстом об удачной оплате<br>
				Название и URL созданных страниц могут быть произвольными.<br>
				2. Укажите здесь какие страницы и для чего вы создали. <br>
				3. В настройках своего аккаунта платежной системы укажите URL страницы для fail, success и result'
		);

		$groupOptions[] = array(
			'type'   => 'select',
			'title'  => __( 'The page of RESULT', 'wp-recall' ),
			'slug'   => 'page_result_pay',
			'values' => rcl_get_pages_ids()
		);

		$groupOptions[] = array(
			'type'   => 'select',
			'title'  => __( 'The page of SUCCESS', 'wp-recall' ),
			'slug'   => 'page_success_pay',
			'values' => rcl_get_pages_ids()
		);

		$groupOptions[] = array(
			'type'   => 'select',
			'title'  => __( 'The page of FAIL', 'wp-recall' ),
			'slug'   => 'page_fail_pay',
			'values' => rcl_get_pages_ids()
		);

		$groupOptions[] = array(
			'type'   => 'select',
			'title'  => __( 'The page of a successfully payment', 'wp-recall' ),
			'slug'   => 'page_successfully_pay',
			'values' => rcl_get_pages_ids()
		);
	} else {

		$groupOptions[] = array(
			'type'    => 'custom',
			'title'   => __( 'The using systems of payment', 'wp-recall' ),
			'slug'    => 'payment_gateways',
			'content' => rcl_get_notice( [
				'type' => 'error',
				'text' => 'Похоже ни одного подключения не настроено. Скачайте <a href="https://codeseller.ru/product_tag/platezhnye-sistemy/" target="_blank">одно из доступных дополнений</a> для подключения к платежному агрегатору и настройте его'
			] )
		);
	}

	$group->add_options( $groupOptions );

	/* support old options */
	global $rclOldOptionData;

	apply_filters( 'rcl_pay_child_option', '' );

	if ( $rclOldOptionData ) {

		foreach ( $rclOldOptionData as $box_id => $box ) {

			foreach ( $box['groups'] as $k => $group ) {

				$group['options'] = array_merge( [
					[
						'type'    => 'custom',
						'slug'    => 'notice',
						'content' => rcl_get_notice( [
							'type' => 'error',
							'text' => __( 'Attention! You are using the old version payment connection. You need to update this payment connection.', 'wp-recall' )
						] )
					]
				], $group['options'] );


				$options->add_box( $k . '-old-gateway', array(
					'title' => $group['title']
				) )->add_group( $k . '-old-gateway', array(
					'title' => $group['title']
				) )->add_options( $group['options'] );
			}
		}
	}

	unset( $rclOldOptionData );

	/*	 * * */

	return $options;
}
