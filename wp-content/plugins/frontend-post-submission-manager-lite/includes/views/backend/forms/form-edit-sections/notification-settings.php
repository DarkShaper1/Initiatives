<div class="fpsml-settings-each-section fpsml-display-none" data-tab="notification">
    <div class="fpsml-each-form-field">
        <div class="fpsml-field-head fpsml-clearfix">
            <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span><?php esc_html_e('Admin Notification', 'frontend-post-submission-manager-lite'); ?></h3>
        </div>
        <div class="fpsml-field-body fpsml-display-none">
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Enable', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="checkbox" name="form_details[notification][admin][enable]" value="1" class="fpsml-checkbox-toggle-trigger" data-toggle-class="fpsml-show-fields-ref-admin-notification" <?php echo (!empty($form_details['notification']['admin']['enable'])) ? 'checked="checked"' : ''; ?>>
                </div>
            </div>
            <div class="fpsml-show-fields-ref-admin-notification <?php echo (empty($form_details['notification']['admin']['enable'])) ? 'fpsml-display-none' : ''; ?>">
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e('Notification Emails', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field fpsml-checkbox-toggle">
                        <input type="text" name="form_details[notification][admin][notification_emails]" value="<?php echo (!empty($form_details['notification']['admin']['notification_emails'])) ? esc_attr($form_details['notification']['admin']['notification_emails']) : '' ?>">
                        <p class="description"><?php esc_html_e('Please enter the emails in which you want to receive notifications separated by comma. If kept blank, it will go to admin email.', 'frontend-post-submission-manager-lite'); ?></p>
                    </div>
                </div>
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e('Subject', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field fpsml-checkbox-toggle">
                        <input type="text" name="form_details[notification][admin][subject]" value="<?php echo (!empty($form_details['notification']['admin']['subject'])) ? esc_attr($form_details['notification']['admin']['subject']) : '' ?>">
                        <p class="description"><?php esc_html_e('You can use [post_title] and [author_name] to replace it with submitted post title and author name in the subject while receiving the post admin notification.', 'frontend-post-submission-manager-lite'); ?></p>
                    </div>
                </div>
                <div class="fpsml-field-wrap fpsml-required-message ">
                    <label><?php esc_html_e('From name', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <input type="text" name="form_details[notification][admin][from_name]" value="<?php echo (!empty($form_details['notification']['admin']['from_name'])) ? esc_attr($form_details['notification']['admin']['from_name']) : ''; ?>">
                    </div>
                </div>
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e('From Email', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <input type="text" name="form_details[notification][admin][from_email]" value="<?php echo (!empty($form_details['notification']['admin']['from_email'])) ? esc_attr($form_details['notification']['admin']['from_email']) : ''; ?>">
                    </div>
                </div>
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e('Message', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <textarea name="form_details[notification][admin][notification_message]"><?php echo (!empty($form_details['notification']['admin']['notification_message'])) ? $fpsml_library_obj->output_converting_br($form_details['notification']['admin']['notification_message']) : $fpsml_library_obj->default_admin_notification(); ?></textarea>
                        <p class="description"><?php esc_html_e('Please use [post_title],[post_admin_link] to replace it with the submitted post title and post admin link in the post submission admin notification email message.', 'frontend-post-submission-manager-lite'); ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="fpsml-each-form-field">
        <div class="fpsml-field-head fpsml-clearfix">
            <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span><?php esc_html_e('Post Publish Notification', 'frontend-post-submission-manager-lite'); ?></h3>
        </div>
        <div class="fpsml-field-body fpsml-display-none">
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Enable', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="checkbox" name="form_details[notification][post_publish][enable]" value="1" class="fpsml-checkbox-toggle-trigger" data-toggle-class="fpsml-show-fields-ref-post-notification" <?php echo (!empty($form_details['notification']['post_publish']['enable'])) ? 'checked="checked"' : ''; ?>>
                </div>
            </div>
            <div class="fpsml-show-fields-ref-post-notification <?php echo (empty($form_details['notification']['post_publish']['enable'])) ? 'fpsml-display-none' : ''; ?>">
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e('Subject', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field fpsml-checkbox-toggle">
                        <input type="text" name="form_details[notification][post_publish][subject]" value="<?php echo (!empty($form_details['notification']['post_publish']['subject'])) ? esc_attr($form_details['notification']['post_publish']['subject']) : '' ?>">
                        <p class="description"><?php esc_html_e('You can use [post_title] and [author_name] to replace it with submitted post title and author name in the subject while sending the post publish notification.', 'frontend-post-submission-manager-lite'); ?></p>
                    </div>
                </div>
                <div class="fpsml-field-wrap fpsml-required-message ">
                    <label><?php esc_html_e('From name', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <input type="text" name="form_details[notification][post_publish][from_name]" value="<?php echo (!empty($form_details['notification']['post_publish']['from_name'])) ? esc_attr($form_details['notification']['post_publish']['from_name']) : ''; ?>">
                    </div>
                </div>
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e('From Email', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <input type="text" name="form_details[notification][post_publish][from_email]" value="<?php echo (!empty($form_details['notification']['post_publish']['from_email'])) ? esc_attr($form_details['notification']['post_publish']['from_email']) : ''; ?>">
                    </div>
                </div>
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e('Message', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <textarea name="form_details[notification][post_publish][notification_message]"><?php echo (!empty($form_details['notification']['post_publish']['notification_message'])) ? $fpsml_library_obj->output_converting_br($form_details['notification']['post_publish']['notification_message']) : $fpsml_library_obj->default_publish_notification(); ?></textarea>
                        <p class="description"><?php esc_html_e('Please use [author_name],[post_title],[post_link] to replace with the approved post\'s author name, post title and post frontend link in the post publish email message.', 'frontend-post-submission-manager-lite'); ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>