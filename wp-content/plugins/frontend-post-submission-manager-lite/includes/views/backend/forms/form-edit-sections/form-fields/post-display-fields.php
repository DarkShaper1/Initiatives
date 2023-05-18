<div class="fpsml-show-fields-ref-<?php echo (!empty($show_hide_toggle_class)) ? esc_attr($show_hide_toggle_class) : esc_attr($field_key); ?> <?php echo (empty($field_details['show_on_form'])) ? 'fpsml-display-none' : ''; ?>">
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Post Detail Display', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="checkbox" name="<?php echo esc_attr($field_name_prefix) ?>[post_detail_display]" value="1" <?php echo (!empty($field_details['post_detail_display'])) ? 'checked="checked"' : ''; ?> class="fpsml-checkbox-toggle-trigger" data-toggle-class="fpsml-post-detail-display-ref-<?php echo esc_attr($show_hide_toggle_class); ?>"/>
            <p class="description"><?php esc_html_e('Please check if you want to display this field received value in the frontend post detail page.', 'frontend-post-submission-manager-lite'); ?></p>
        </div>
    </div>
    <div class="fpsml-post-detail-display-ref-<?php echo esc_attr($show_hide_toggle_class); ?> <?php echo (empty($field_details['post_detail_display'])) ? 'fpsml-display-none' : ''; ?>">
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Display Position', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <select name="<?php echo esc_attr($field_name_prefix) ?>[display_position]">
                    <?php
                    $selected_display_position = (!empty($field_details['display_position'])) ? $field_details['display_position'] : 'after_content';
                    ?>
                    <option value="after_content" <?php selected($selected_display_position, 'after_content'); ?>><?php esc_html_e('After Content', 'frontend-post-submission-manager-lite'); ?></option>
                    <option value="before_content" <?php selected($selected_display_position, 'before_content'); ?>><?php esc_html_e('Before Content', 'frontend-post-submission-manager-lite'); ?></option>
                </select>

            </div>
        </div>
        <div class="fpsml-field-wrap">
            <label><?php esc_html_e('Display Label', 'frontend-post-submission-manager-lite'); ?></label>
            <div class="fpsml-field">
                <input type="text" name="<?php echo esc_attr($field_name_prefix) ?>[display_label]" value="<?php echo (!empty($field_details['display_label'])) ? esc_attr($field_details['display_label']) : ''; ?>"/>
            </div>
        </div>

    </div>
</div>