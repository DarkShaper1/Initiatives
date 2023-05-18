<?php

require_once RCL_PATH . 'admin/classes/class-rcl-tabs-manager.php';

$areaType = ( isset( $_GET['area-type'] ) ) ? sanitize_key( $_GET['area-type'] ) : 'area-menu';

$tabsManager = new Rcl_Tabs_Manager( $areaType );

$content = '<h2>' . esc_html__( 'Personal account tabs manager', 'wp-recall' ) . '</h2>';

$content .= '<p>' . esc_html__( 'On this page you can create new tabs personal account with arbitrary content and manage existing tabs in different areas of the personal account', 'wp-recall' ) . '</p>';

$content .= $tabsManager->form_navi();

$content .= $tabsManager->get_manager();

echo wp_kses( $content, rcl_kses_allowed_html() );
