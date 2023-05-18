<div class="fpsml-field-wrap">
    <label><?php esc_html_e('Show on form', 'frontend-post-submission-manager-lite'); ?></label>
    <div class="fpsml-field">
        <input type="checkbox" name="<?php echo esc_attr($field_name_prefix); ?>[show_on_form]" value="1" <?php echo (!empty($field_details['show_on_form'])) ? 'checked="checked"' : ''; ?> class="fpsml-checkbox-toggle-trigger" data-toggle-class="fpsml-show-fields-ref-<?php echo (!empty($show_hide_toggle_class)) ? esc_attr($show_hide_toggle_class) : esc_attr($field_key); ?>"/>
    </div>
</div>
<div class="fpsml-show-fields-ref-<?php echo (!empty($show_hide_toggle_class)) ? esc_attr($show_hide_toggle_class) : esc_attr($field_key); ?> <?php echo (empty($field_details['show_on_form'])) ? 'fpsml-display-none' : ''; ?>">
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Required', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="checkbox" name="<?php echo esc_attr($field_name_prefix); ?>[required]" value="1" <?php echo (!empty($field_details['required'])) ? 'checked="checked"' : ''; ?> class="fpsml-checkbox-toggle-trigger" data-toggle-class="fpsml-required-message"/>
        </div>
    </div>
    <div class="fpsml-field-wrap fpsml-required-message <?php echo (empty($field_details['required'])) ? 'fpsml-display-none' : ''; ?>">
        <label><?php esc_html_e('Required Error Message', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" name="<?php echo esc_attr($field_name_prefix); ?>[required_error_message]" value="<?php echo (!empty($field_details['required_error_message'])) ? esc_attr($field_details['required_error_message']) : ''; ?>"/>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Field Label', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" name="<?php echo esc_attr($field_name_prefix); ?>[field_label]" value="<?php echo (!empty($field_details['field_label'])) ? esc_attr($field_details['field_label']) : ''; ?>"/>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Field Note', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <textarea name="<?php echo esc_attr($field_name_prefix); ?>[field_note]"><?php echo (!empty($field_details['field_note'])) ? $fpsml_library_obj->sanitize_html($field_details['field_note']) : ''; ?></textarea>
            <p class="description"><?php esc_html_e('This note will show just below the field. Pleaes leave blank if you don\'t want to display the field note.', 'frontend-post-submission-manager-lite'); ?></p>
        </div>
    </div>
</div>