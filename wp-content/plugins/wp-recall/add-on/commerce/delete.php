<?php

global $wpdb;
define( 'RMAG_PREF', $wpdb->prefix . "rmag_" );
delete_site_option( 'primary-rmag-options' );

$wpdb->query( "DROP TABLE " . RMAG_PREF . "details_orders" ); // phpcs:ignore
$wpdb->query( "DROP TABLE " . RMAG_PREF . "orders_history" ); // phpcs:ignore
