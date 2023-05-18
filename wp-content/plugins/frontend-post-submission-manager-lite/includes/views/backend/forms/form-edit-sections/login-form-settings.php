<?php
defined('ABSPATH') or die('No script kiddies please!!');
$login_form_settings = (!empty($form_details['login'])) ? $form_details['login'] : array();
?>
<div class="fpsml-settings-each-section fpsml-display-none fpsml-clearfix" data-tab="login">
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Login Type', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <select name="form_details[login][login_type]" class="fpsml-toggle-trigger" data-toggle-class="fpsml-login-type-ref">
                <?php
                $selected_login_type = (!empty($login_form_settings['login_type'])) ? $login_form_settings['login_type'] : 'login_form';
                ?>
                <option value="login_form" <?php selected($selected_login_type, 'login_form'); ?>><?php esc_html_e('Show Login Form', 'frontend-post-submission-manager-lite'); ?></option>
                <option value="login_message" <?php selected($selected_login_type, 'login_message'); ?>><?php esc_html_e('Show Login Message', 'frontend-post-submission-manager-lite'); ?></option>
                <option value="login_page_redirect" <?php selected($selected_login_type, 'login_page_redirect'); ?>><?php esc_html_e('Redirect to Login Page', 'frontend-post-submission-manager-lite'); ?></option>
            </select>
        </div>
    </div>
    <div class="fpsml-login-type-ref <?php echo ($selected_login_type != 'login_form') ? 'fpsml-display-none' : ''; ?>" data-toggle-ref="login_form">
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login Form Title', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][login_form_title]" value="<?php echo (!empty($login_form_settings['login_form_title'])) ? esc_attr($login_form_settings['login_form_title']) : ''; ?>"/>
            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Username Label', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][username_label]" value="<?php echo (!empty($login_form_settings['username_label'])) ? esc_attr($login_form_settings['username_label']) : ''; ?>"/>
            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Password Label', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][password_label]" value="<?php echo (!empty($login_form_settings['password_label'])) ? esc_attr($login_form_settings['password_label']) : ''; ?>"/>
            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Show Remember Me', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="checkbox" name="form_details[login][show_remember_me]" value="1" class="fpsml-checkbox-toggle-trigger" data-toggle-class="fpsml-remember-me-label" <?php echo (!empty($login_form_settings['show_remember_me'])) ? 'checked="checked"' : ''; ?>/>
            </div>
        </div>
        <div class="fpsml-field-wrap fpsml-remember-me-label <?php echo (empty($login_form_settings['show_remember_me'])) ? 'fpsml-display-none' : ''; ?>">
            <label><?php esc_html_e('Remember Me Label', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][remember_me_label]" value="<?php echo (!empty($login_form_settings['remember_me_label'])) ? esc_attr($login_form_settings['remember_me_label']) : ''; ?>"/>
            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login Button Label', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][login_button_label]" value="<?php echo (!empty($login_form_settings['login_button_label'])) ? esc_attr($login_form_settings['login_button_label']) : ''; ?>"/>
            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login Note', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <textarea name="form_details[login][login_note]"> <?php echo (!empty($login_form_settings['login_note'])) ? $fpsml_library_obj->sanitize_html($login_form_settings['login_note']) : ''; ?></textarea>
            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login error message', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][login_error_message]" value="<?php echo (!empty($login_form_settings['login_error_message'])) ? esc_attr($login_form_settings['login_error_message']) : ''; ?>"/>
            </div>
        </div>

    </div>
    <div class="fpsml-login-type-ref <?php echo ($selected_login_type != 'login_message') ? 'fpsml-display-none' : ''; ?>" data-toggle-ref="login_message">
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login Message', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <textarea name="form_details[login][login_message]"><?php echo (!empty($login_form_settings['login_message'])) ? $fpsml_library_obj->sanitize_html($login_form_settings['login_message']) : ''; ?></textarea>

            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login Link Label', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][login_link_label]" value="<?php echo (!empty($login_form_settings['login_link_label'])) ? $fpsml_library_obj->sanitize_html($login_form_settings['login_link_label']) : ''; ?>"/>
            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login Link URL', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][login_link_url]" value="<?php echo (!empty($login_form_settings['login_link_url'])) ? esc_url($login_form_settings['login_link_url']) : ''; ?>"/>
            </div>
        </div>
    </div>
    <div class="fpsml-login-type-ref <?php echo ($selected_login_type != 'login_page_redirect') ? 'fpsml-display-none' : ''; ?>" data-toggle-ref="login_page_redirect">
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Login Page URL', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="form_details[login][login_page_url]" value="<?php echo (!empty($login_form_settings['login_page_url'])) ? esc_url($login_form_settings['login_page_url']) : ''; ?>"/>
                <p class="description"><?php esc_html_e('Please enter the URL in which you want to redirect users when they are not logged in.', 'frontend-post-submission-manager-lite'); ?></p>
            </div>
        </div>
    </div>
</div>


