<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please' );

/*
  Plugin Name: Frontend Post Submission Manager Lite
  Description: A plugin to submit and manage WordPress posts from frontend with or without logging in
  Version:     1.0.6
  Author:      WP Shuffle
  Author URI:  http://wpshuffle.com
  Plugin URI: http://wpshuffle.com/wordpress-plugins/frontend-post-submission-manager-lite
  License:     GPL2
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
  Domain Path: /languages
  Text Domain: frontend-post-submission-manager-lite
 */

// Define FPSML_URL and FPSML_PATH
defined( 'FPSML_URL' ) or define( 'FPSML_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );
defined( 'FPSML_PATH' ) or define( 'FPSML_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
defined( 'FPSML_LANGAUGE_PATH' ) or define( 'FPSML_LANGAUGE_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages' );



// Include the plugin's main class.
include(FPSML_PATH . '/includes/classes/class-frontend-post-submission-manager-lite.php');

/**
 * Returns the main instance of Frontend Post Submission Manager class.
 *
 * @since  1.0.0
 * return Frontend_Post_Submission_Manager_Lite
 */
function fpsml_initialize() {
    return Frontend_Post_Submission_Manager_Lite::instance();
}

$GLOBALS['fpsml'] = fpsml_initialize();
