<div class="fpsml-login-form-wrapper fpsml-login-form-<?php echo esc_attr($form_template); ?>">
    <?php
    /**
     * Fires just before displaying the login form
     *
     * @param obj $form_row
     * @param array $form_details
     *
     * @since 1.0.0
     */
    do_action('fpsml_login_form_before', $form_row, $form_details);
    if (!empty($login_settings['login_form_title'])) {
        ?>
        <h3><?php echo esc_html($login_settings['login_form_title']); ?></h3>
        <?php
    }
    $username_label = (!empty($login_settings['username_label'])) ? esc_attr($login_settings['username_label']) : __('Username', 'frontend-post-submission-manager-lite');
    $password_label = (!empty($login_settings['password_label'])) ? esc_attr($login_settings['password_label']) : __('Password', 'frontend-post-submission-manager-lite');
    $login_button_label = (!empty($login_settings['login_button_label'])) ? esc_attr($login_settings['login_button_label']) : __('Login', 'frontend-post-submission-manager-lite');
    $remember_me_label = (!empty($login_settings['remember_me_label'])) ? esc_attr($login_settings['remember_me_label']) : __('Remember', 'frontend-post-submission-manager-lite');
    $login_error_message = (!empty($login_settings['login_error_message'])) ? esc_attr($login_settings['login_error_message']) : __('Invalid username or password.', 'frontend-post-submission-manager-lite');
    global $user_login;
    $login = (isset($_GET['login']) ) ? sanitize_text_field($_GET['login']) : 0;

    // In case of a login error.
    if ($login === "failed") {
        echo '<p class="fpsml-login-msg"><strong>ERROR:</strong> ' . esc_html($login_error_message) . '</p>';
    } elseif ($login === "empty") {
        echo '<p class="fpsml-login-msg"><strong>ERROR:</strong> ' . esc_html($login_error_message) . '</p>';
    } elseif ($login === "captcha_error") {
        $captcha_error_message = (!empty($form_details['security']['error_message'])) ? $form_details['security']['error_message'] : esc_html__('Invalid Captcha', 'frontend-post-submission-manager-lite');
        echo '<p class="fpsml-login-msg"><strong>ERROR:</strong> ' . esc_html($captcha_error_message) . '</p>';
    }
    $args = array(
        'echo' => true,
        'redirect' => $fpsml_library_obj->get_current_page_url(),
        'form_id' => 'fpsml-login-form',
        'label_username' => $username_label,
        'label_password' => $password_label,
        'label_log_in' => $login_button_label,
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => true,
        'label_remember' => $remember_me_label,
        'value_username' => NULL,
        'value_remember' => false
    );

// Calling the login form.
    wp_login_form($args);
    if (!empty($login_settings['login_note'])) {
        ?>
        <div class="fpsml-login-note"><?php echo $fpsml_library_obj->sanitize_html($login_settings['login_note']); ?></div>
        <?php
    }
    /**
     * Fires just after displaying the login form
     *
     * @param obj $form_row
     * @param array $form_details
     *
     * @since 1.0.0
     */
    do_action('fpsml_login_after_before', $form_row, $form_details);
    ?>
</div>
<script>jQuery(document).ready(function ($) {
        if (window.location.href.indexOf('?') > -1) {
            history.pushState('', document.title, window.location.pathname);
        }
    });
</script>