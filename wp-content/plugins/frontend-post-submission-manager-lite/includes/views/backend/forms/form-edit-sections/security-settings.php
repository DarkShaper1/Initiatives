<div class="fpsml-settings-each-section fpsml-display-none" data-tab="security">
    <div class="fpsml-field-wrap">
        <label><?php _e('ReCaptcha Site Key', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" name="form_details[security][site_key]" value="<?php echo (!empty($form_details['security']['site_key'])) ? esc_attr($form_details['security']['site_key']) : ''; ?>"/>
            <p class="description"><?php _e('Please go <a href="https://www.google.com/recaptcha/admin" target="_blank">here</a> to get the google reCaptcha site key.', 'frontend-post-submission-manager-lite'); ?></p>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php _e('ReCaptcha Secret Key', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" name="form_details[security][secret_key]" value="<?php echo (!empty($form_details['security']['secret_key'])) ? esc_attr($form_details['security']['secret_key']) : ''; ?>"/>
            <p class="description"><?php _e('Please go <a href="https://www.google.com/recaptcha/admin" target="_blank">here</a> to get the google reCaptcha secret key.', 'frontend-post-submission-manager-lite'); ?></p>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php _e('Enable ReCaptcha in frontend form', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <?php
            $frontend_form_captcha = (!empty($form_details['security']['frontend_form_captcha'])) ? 1 : 0;
            ?>
            <input type="checkbox" name="form_details[security][frontend_form_captcha]" value="1" <?php checked($frontend_form_captcha, true); ?>/>
            <p class="description"><?php _e('Please check to enable to reCaptcha in frontend form.', 'frontend-post-submission-manager-lite'); ?></p>
        </div>
    </div>
    <?php if ($form_row->form_type == 'login_require') { ?>
        <div class="fpsml-field-wrap">
            <label><?php _e('Enable ReCaptcha in login form', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <?php
                $login_form_captcha = (!empty($form_details['security']['login_form_captcha'])) ? 1 : 0;
                ?>
                <input type="checkbox" name="form_details[security][login_form_captcha]" value="1" <?php checked($login_form_captcha, true); ?>/>
                <p class="description"><?php _e('Please check to enable to reCaptcha in login form.', 'frontend-post-submission-manager-lite'); ?></p>
            </div>
        </div>
    <?php } ?>
    <div class="fpsml-field-wrap">
        <label><?php _e('ReCaptcha Label', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" name="form_details[security][captcha_label]" value="<?php echo (!empty($form_details['security']['captcha_label'])) ? esc_attr($form_details['security']['captcha_label']) : ''; ?>"/>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php _e('ReCaptcha Error Message', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" name="form_details[security][error_message]" value="<?php echo (!empty($form_details['security']['error_message'])) ? esc_attr($form_details['security']['error_message']) : ''; ?>"/>
        </div>
    </div>
</div>