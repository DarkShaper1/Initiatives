<?php include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/common-fields.php'); ?>
<div class="fpsml-show-fields-ref-<?php echo (!empty($show_hide_toggle_class)) ? esc_attr($show_hide_toggle_class) : esc_attr($field_key); ?> <?php echo (empty($field_details['show_on_form'])) ? 'fpsml-display-none' : ''; ?>">
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Character Limit', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="number" min="0" name="<?php echo esc_attr($field_name_prefix); ?>[character_limit]" value="<?php echo (!empty($field_details['character_limit'])) ? intval($field_details['character_limit']) : ''; ?>"/>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Character Limit Error Message', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" name="<?php echo esc_attr($field_name_prefix); ?>[character_limit_error_message]" value="<?php echo (!empty($field_details['character_limit_error_message'])) ? esc_attr($field_details['character_limit_error_message']) : ''; ?>"/>
        </div>
    </div>
</div>