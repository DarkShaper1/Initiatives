<?php

global $wpdb;

if ( ! defined( 'RMAG_PREF' ) ) {
	define( 'RMAG_PREF', $wpdb->prefix . "rmag_" );
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$collate = '';

if ( $wpdb->has_cap( 'collation' ) ) {
	if ( ! empty( $wpdb->charset ) ) {
		$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$collate .= " COLLATE $wpdb->collate";
	}
}

$table = RMAG_PREF . "users_balance";
$sql   = "CREATE TABLE IF NOT EXISTS " . $table . " (
		user_id BIGINT(20) UNSIGNED NOT NULL,
		user_balance VARCHAR (20) NOT NULL,
		PRIMARY KEY  user_id (user_id)
	  ) $collate;";

dbDelta( $sql );

$table = RMAG_PREF . "pay_results";
$sql   = "CREATE TABLE IF NOT EXISTS " . $table . " (
			ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			payment_id INT(20) UNSIGNED NOT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL,
			pay_amount VARCHAR(20) NOT NULL,
			time_action DATETIME NOT NULL,
			pay_system VARCHAR(100) NOT NULL,
			pay_type VARCHAR(100) NOT NULL,
			PRIMARY KEY  id (id),
			KEY payment_id (payment_id),
			KEY user_id (user_id)
		  ) $collate;";

dbDelta( $sql );

if ( ! rcl_isset_plugin_page( 'payment-result' ) ) {
	rcl_update_commerce_option( 'page_result_pay', rcl_create_plugin_page( 'payment-result', [
		'post_title' => __( 'result', 'wp-recall' )
	] ) );
}

if ( ! rcl_isset_plugin_page( 'payment-success' ) ) {
	rcl_update_commerce_option( 'page_success_pay', rcl_create_plugin_page( 'payment-success', [
		'post_title' => __( 'success', 'wp-recall' )
	] ) );
}

if ( ! rcl_isset_plugin_page( 'payment-fail' ) ) {
	rcl_update_commerce_option( 'page_fail_pay', rcl_create_plugin_page( 'payment-fail', [
		'post_title' => __( 'The unsuccessfull payment', 'wp-recall' )
	] ) );
}

if ( ! rcl_isset_plugin_page( 'payment-successfully' ) ) {
	rcl_update_commerce_option( 'page_successfully_pay', rcl_create_plugin_page( 'payment-successfully', [
		'post_title' => __( 'The successfully payment', 'wp-recall' )
	] ) );
}
