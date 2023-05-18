<?php

$login_settings = (!empty($form_details['login'])) ? $form_details['login'] : array();
$login_type = (!empty($login_settings['login_type'])) ? $login_settings['login_type'] : 'login_form';
$form_template = (!empty($form_details['layout']['template'])) ? $form_details['layout']['template'] : 'template-1';
switch ($login_type) {
    case 'login_message':
        include(FPSML_PATH . '/includes/views/frontend/login-types/login-message.php');
        break;
    case 'login_page_redirect':
        include(FPSML_PATH . '/includes/views/frontend/login-types/login-page-redirect.php');
        break;
    case 'login_form':
        include(FPSML_PATH . '/includes/views/frontend/login-types/login-form.php');
        break;
}
