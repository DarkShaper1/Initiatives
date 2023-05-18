<div class="fpsml-each-form-field">
    <div class="fpsml-field-head fpsml-clearfix">
        <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span><?php esc_html_e('Post Image', 'frontend-post-submission-manager-lite'); ?></h3>
    </div>
    <div class="fpsml-field-body fpsml-display-none">
        <?php include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/common-fields.php'); ?>
        <div class="fpsml-show-fields-ref-<?php echo esc_attr($field_key); ?> <?php echo (empty($field_details['show_on_form'])) ? 'fpsml-display-none' : ''; ?>">
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Upload Button Label', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="<?php echo esc_attr($field_name_prefix); ?>[upload_button_label]" value="<?php echo (!empty($field_details['upload_button_label'])) ? esc_attr($field_details['upload_button_label']) : ''; ?>"/>
                </div>
            </div>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('File Extension Error Message', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="<?php echo esc_attr($field_name_prefix); ?>[file_extension_error_message]" value="<?php echo (!empty($field_details['file_extension_error_message'])) ? esc_attr($field_details['file_extension_error_message']) : ''; ?>"/>
                    <p class="description"><?php esc_html_e('Please use {file} and {extensions} to replace uploaded file name and allowed extension in the displayed message. For example: {file} has invalid extension. Only {extensions} are allowed.', 'frontend-post-submission-manager-lite'); ?></p>
                </div>
            </div>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Upload File Size Limit', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="number" min="1" name="<?php echo esc_attr($field_name_prefix); ?>[upload_file_size_limit]" value="<?php echo (!empty($field_details['upload_file_size_limit'])) ? intval($field_details['upload_file_size_limit']) : ''; ?>"/>
                    <p class="description"><?php esc_html_e('Please enter the max size of the file being uploaded in MB. Default is 5 MB.', 'frontend-post-submission-manager-lite'); ?></p>
                    <?php
                    $max_upload_filesize = ini_get('upload_max_filesize');
                    ?>
                    <p class="description"><?php esc_html_e(sprintf("Please note that the number shouldn't exceed %s. If you want to allow more than %s then please update the value in your server's php.ini file.", $max_upload_filesize, $max_upload_filesize), 'frontend-post-submission-manager-lite'); ?></p>
                </div>
            </div>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Max Size Error Message', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="<?php echo esc_attr($field_name_prefix); ?>[max_size_error_message]" value="<?php echo (!empty($field_details['max_size_error_message'])) ? esc_attr($field_details['max_size_error_message']) : ''; ?>"/>
                </div>
            </div>
        </div>
    </div>
</div>