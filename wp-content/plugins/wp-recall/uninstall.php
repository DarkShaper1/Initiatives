<?php

/**
 * Created by PhpStorm.
 * Author: Maksim Martirosov
 * Date: 05.10.2015
 * Time: 20:39
 * Project: wp-recall
 */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

require_once 'classes/class-rcl-install.php';
require_once 'functions/plugin-pages.php';
require_once 'functions/files.php';
require_once 'classes/query/class-rcl-old-query.php';
require_once 'classes/query/class-rcl-query.php';
require_once 'classes/query/class-rq.php';
require_once 'classes/class-rcl-query-tables.php';

$upload_dir = rcl_get_wp_upload_dir();
define( 'RCL_UPLOAD_PATH', $upload_dir['basedir'] . '/rcl-uploads/' );
define( 'RCL_TAKEPATH', WP_CONTENT_DIR . '/wp-recall/' );

//Удаляем созданные роли
RCL_Install::remove_roles();

//Удаляем расписания крона
wp_clear_scheduled_hook( 'rcl_cron_hourly_schedule' );
wp_clear_scheduled_hook( 'rcl_cron_twicedaily_schedule' );
wp_clear_scheduled_hook( 'rcl_cron_daily_schedule' );

//Подчищаем на сервере
rcl_remove_dir( RCL_TAKEPATH );
rcl_remove_dir( RCL_UPLOAD_PATH );

//Удаляем таблицы и настройки плагина
$tables = $wpdb->get_results( "SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_name like '".$wpdb->prefix."rcl_%'" );
if ( $tables ) {
	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS " . $table->table_name );
	}
}

$tables = $wpdb->get_results( "SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE table_name like '".$wpdb->prefix."rmag_%'" );
if ( $tables ) {
	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS " . $table->table_name );
	}
}

$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'rcl_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'rmag_%'" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'widget_rcl%'" );
$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'rcl_%'" );

//включаем всем пользователям сайта показ админ панели
$wpdb->update(
	$wpdb->prefix . 'usermeta', array( 'meta_value' => 'true' ), array( 'meta_key' => 'show_admin_bar_front' )
);

//удаляем все страницы плагина
rcl_delete_plugin_pages();
