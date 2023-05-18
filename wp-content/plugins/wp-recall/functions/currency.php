<?php

//Перечень действующих валют
function rcl_get_currency_list() {

	$rub = ( is_admin() ) ? 'p' : '<i class="rcli fa-rub"></i>';

	return array(
		'RUB' => array( 'руб', $rub, '<span class="ruble-symbol">P<span>–</span></span>' ),
		'UAH' => array( 'гривен', 'грн', 'грн' ),
		'KZT' => array( 'тенге', 'тнг', 'тнг' ),
		'USD' => array( 'dollars', '<i class="rcli fa-usd"></i>', '$' ),
		'EUR' => array( 'euro', '<i class="rcli fa-eur"></i>', '€' ),
	);
}

function rcl_get_currency( $cur = false, $type = 0 ) {

	$curs = rcl_get_currency_list();

	$curs = apply_filters( 'currency_list', $curs );

	if ( ! $cur ) {
		foreach ( $curs as $cur => $nms ) {
			$crs[ $cur ] = $cur;
		}

		return $crs;
	}

	if ( ! isset( $curs[ $cur ][ $type ] ) ) {
		return false;
	}

	return $curs[ $cur ][ $type ];
}

function rcl_type_currency_list( $post_id ) {

	if ( rcl_get_commerce_option( 'multi_cur' ) ) {
		$type  = get_post_meta( $post_id, 'type_currency', 1 );
		$curs  = array( rcl_get_commerce_option( 'primary_cur' ), rcl_get_commerce_option( 'secondary_cur' ) );
		$conts = '<select name="wprecall[type_currency]">';
		foreach ( $curs as $cur ) {
			$conts .= '<option ' . selected( $type, $cur, false ) . ' value="' . $cur . '">' . $cur . '</option>';
		}
		$conts .= '</select>';
	} else {
		$conts = rcl_get_commerce_option( 'primary_cur' );
	}

	echo wp_kses( $conts, rcl_kses_allowed_html() );
}

function rcl_get_current_type_currency( $post_id ) {

	if ( rcl_get_commerce_option( 'multi_cur' ) ) {
		$type = get_post_meta( $post_id, 'type_currency', 1 );
		$curs = array( rcl_get_commerce_option( 'primary_cur' ), rcl_get_commerce_option( 'secondary_cur' ) );
		if ( $type == $curs[0] || $type == $curs[1] ) {
			$current = $type;
		} else {
			$current = $curs[0];
		}
	} else {
		$current = rcl_get_commerce_option( 'primary_cur' );
	}

	return $current;
}

function get_current_currency( $post_id ) {
	$current = rcl_get_current_type_currency( $post_id );

	return rcl_get_currency( $current, 1 );
}

//Вывод основной валюты сайта
function rcl_get_primary_currency( $type = 0 ) {
	return rcl_get_currency( rcl_get_commerce_option( 'primary_cur', 'RUB' ), $type );
}

function rcl_primary_currency( $type = 0 ) {

	echo wp_kses_post( rcl_get_primary_currency( $type ) );
}
