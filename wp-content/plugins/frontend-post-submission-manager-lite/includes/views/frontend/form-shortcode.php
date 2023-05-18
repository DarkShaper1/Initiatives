<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!!' );
$form_type = $form_row->form_type;
if ( $form_type == 'login_require' ) {
    if ( is_user_logged_in() ) {
        include(FPSML_PATH . '/includes/views/frontend/form-html.php');
    } else {
        include(FPSML_PATH . '/includes/views/frontend/login-html.php');
    }
} else {
    include(FPSML_PATH . '/includes/views/frontend/form-html.php');
}