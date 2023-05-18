<?php
defined('ABSPATH') or die('No script kiddies please!!');
$fpsml_form_alias = get_post_meta($post->ID, '_fpsml_form_alias', true);
if (!empty($fpsml_form_alias)) {
    global $fpsml_library_obj;
    $form_row = $fpsml_library_obj->get_form_row_by_alias($fpsml_form_alias);
    if (empty($form_row)) {
        return;
    }
    if (empty($form_row->form_details)) {
        return;
    }
    ?>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Frontend Form', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field"><a href="<?php echo admin_url('admin.php?page=fpsm&action=edit_form&form_id=' . $form_row->form_id); ?>" target="_blank" class="button-secondary"><?php echo esc_html($form_row->form_title); ?></a></div>
    </div>
    <?php
    $form_details = maybe_unserialize($form_row->form_details);

    //if fields are empty
    if (empty($form_details['form']['fields'])) {
        return;
    }
    $form_fields = $form_details['form']['fields'];
    foreach ($form_fields as $field_key => $field_details) {
        // If field is enabled in the form


        if ($fpsml_library_obj->is_custom_field_key($field_key)) {
            if (!empty($field_details['show_on_form'])) {
                $custom_field_meta_key = $fpsml_library_obj->get_meta_key_by_field_key($field_key);
                $custom_field_value = get_post_meta($post->ID, $custom_field_meta_key, true);
                ?>
                <div class="fpsml-field-wrap">
                    <label><?php echo esc_html($field_details['field_label']); ?></label>
                    <div class="fpsml-field">
                        <?php
                        $custom_field_name = "fpsml_custom_fields[$custom_field_meta_key]";
                        $field_type = $field_details['field_type'];
                        ?>
                        <input type="hidden" name="fpsml_included_custom_fields[]" value="<?php echo esc_attr($custom_field_meta_key); ?>"/>
                        <?php
                        switch ($field_type) {
                            case 'textfield':
                                ?>
                                <input type="text" name="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($custom_field_value); ?>"/>
                                <?php
                                break;
                            case 'textarea':
                                ?>
                                <textarea name="<?php echo esc_attr($custom_field_name); ?>"><?php echo esc_html($custom_field_value); ?></textarea>
                                <?php
                                break;
                            case 'select':
                                ?>
                                <select name="<?php echo esc_attr($custom_field_name); ?>">
                                    <?php
                                    if (!empty($field_details['options'])) {
                                        foreach ($field_details['options'] as $option_count => $option) {
                                            ?>
                                            <option value="<?php echo esc_attr($field_details['values'][$option_count]) ?>" <?php echo ($custom_field_value == $field_details['values'][$option_count]) ? 'selected="selected"' : ''; ?>><?php echo esc_html($option); ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <?php
                                break;
                            case 'checkbox':
                                $display_type = $field_details['display_type'];
                                $display_class = 'fpsml-checkbox-' . $display_type;
                                $custom_field_value = (empty($custom_field_value)) ? array() : $custom_field_value;
                                ?>
                                <div class="fpsml-checkbox-list-wrap <?php echo esc_attr($display_class); ?>">
                                    <?php
                                    if (!empty($field_details['options'])) {
                                        foreach ($field_details['options'] as $option_count => $option) {
                                            $attr_id = 'fpsml-' . $custom_field_meta_key . '-' . $option_count;
                                            ?>
                                            <div class="fpsml-checkbox">
                                                <input class="fpsml-disable-checkbox-toggle" type="checkbox" name="<?php echo esc_attr($custom_field_name); ?>[]" value="<?php echo esc_attr($field_details['values'][$option_count]); ?>" <?php echo (in_array($field_details['values'][$option_count], $custom_field_value)) ? 'checked="checked"' : ''; ?> id="<?php echo esc_attr($attr_id); ?>"/>
                                                <label for="<?php echo esc_attr($attr_id); ?>"><?php echo esc_html($option); ?></label>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                                <?php
                                break;
                            case 'radio':
                                ?>
                                <?php
                                $display_type = $field_details['display_type'];
                                $display_class = 'fpsml-radio-' . $display_type;
                                ?>
                                <div class="fpsml-checkbox-list-wrap <?php echo esc_attr($display_class); ?>">
                                    <?php
                                    if (!empty($field_details['options'])) {
                                        ?>

                                        <?php
                                        foreach ($field_details['options'] as $option_count => $option) {
                                            $attr_id = 'fpsml-' . $custom_field_meta_key . '-' . $option_count;
                                            ?>
                                            <div class="fpsml-radio">
                                                <input type="radio" name="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($field_details['values'][$option_count]); ?>" <?php checked($custom_field_value, $field_details['values'][$option_count]); ?> id="<?php echo esc_attr($attr_id); ?>"/>
                                                <label for="<?php echo esc_attr($attr_id); ?>"><?php echo esc_html($option); ?></label>
                                            </div>
                                            <?php
                                        }
                                        ?>

                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                                break;
                            case 'number':
                                ?>
                                <input type="number" name="<?php echo esc_attr($custom_field_name); ?>" min="<?php echo esc_attr($field_details['min_limit']); ?>" max="<?php echo esc_attr($field_details['max_limit']); ?>" value="<?php echo esc_attr($custom_field_value); ?>"/>
                                <?php
                                break;
                            case 'email':
                                ?>
                                <input type="email" name="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($custom_field_value); ?>"/>
                                <?php
                                break;
                            case 'datepicker':
                                ?>
                                <div class="fpsml-date-picker">
                                    <?php
                                    if (empty($field_details['string_format'])) {
                                        ?>
                                        <input type="text" name="<?php echo esc_attr($custom_field_name); ?>" class="fpsml-front-datepicker" data-date-format="<?php echo esc_attr($field_details['date_format']); ?>" value="<?php echo esc_attr($custom_field_value); ?>"/>
                                        <?php
                                    } else {
                                        ?>
                                        <input type="text" name="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($custom_field_value); ?>"/>
                                        <p class="description"><?php esc_html_e('Note: Date is showing up in the string format because you had enabled the string format option.', 'frontend-post-submission-manager-lite'); ?></p>
                                        <?php
                                    }
                                    ?>

                                </div>
                                <?php
                                break;
                            case 'file_uploader':
                                ?>
                                <div class="fpsml-file-preview-wrap">
                                    <?php
                                    if (!empty($custom_field_value)) {
                                        $media_ids = explode(',', $custom_field_value);
                                        foreach ($media_ids as $media_id) {
                                            $media_thumbnail_url = wp_get_attachment_image_src($media_id, 'thumbnail');
                                            ?>
                                            <div class="fpsml-file-preview-row">
                                                <span class="fpsml-file-preview-column"><img src="<?php echo esc_url($media_thumbnail_url[0]); ?>"/></span>
                                                <span class="fpsml-file-preview-column"><?php echo get_the_title($media_id); ?></span>
                                                <span class="fpsml-file-preview-column"><?php echo esc_html($fpsml_library_obj->get_attachment_filesize($media_id)); ?></span>
                                                <span class="fpsml-file-preview-column"><input type="button" class="fpsml-media-remove-button" data-media-id='<?php echo intval($media_id); ?>' value="<?php esc_html_e('Remove', 'frontend-post-submission-manager-lite'); ?>"/></span>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                    <input type="hidden" name="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($custom_field_value); ?>" class="fpsml-fileuploader-value"/>
                                </div>
                                <?php
                                break;
                            case 'url':
                                ?>
                                <input type="url" name="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_url($custom_field_value); ?>"/>
                                <?php
                                break;
                            case 'tel':
                                ?>
                                <input type="tel" name="<?php echo esc_attr($custom_field_name); ?>" value="<?php echo esc_attr($custom_field_value); ?>"/>
                                <?php
                                break;
                            case 'youtube':
                                if (strpos('embed', $custom_field_value)) {
                                    $youtube_embed_url = $custom_field_value;
                                } else {
                                    $url_array = explode('=', $custom_field_value);
                                    $youtube_embed_url = 'https://www.youtube.com/embed/' . end($url_array);
                                }
                                $width = $field_details['embed_width'];
                                $height = $field_details['embed_height'];
                                ?>
                                <iframe
                                    class="fpsml-youtube-embed-iframe"
                                    width="<?php echo esc_attr($width); ?>"
                                    height="<?php echo esc_attr($height); ?>"
                                    src="<?php echo esc_url($youtube_embed_url); ?>"
                                    allowfullscreen>
                                </iframe>
                                <?php
                                break;
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
        } else if ($field_key == 'author_name' && !empty($field_details['show_on_form'])) {
            ?>
            <div class="fpsml-field-wrap">
                <label><?php echo esc_html($field_details['field_label']); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="fpsml_custom_fields[fpsml_author_name]" value="<?php echo esc_attr(get_post_meta($post->ID, 'fpsml_author_name', true)); ?>"/>
                    <input type="hidden" name="fpsml_included_custom_fields[]" value="fpsml_author_name"/>
                </div>
            </div>
            <?php
        } else if ($field_key == 'author_email' && !empty($field_details['show_on_form'])) {
            ?>
            <div class="fpsml-field-wrap">
                <label><?php echo esc_html($field_details['field_label']); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="fpsml_custom_fields[fpsml_author_email]" value="<?php echo esc_attr(get_post_meta($post->ID, 'fpsml_author_email', true)); ?>"/>
                    <input type="hidden" name="fpsml_included_custom_fields[]" value="fpsml_author_email"/>
                </div>
            </div>
            <?php
        }
    }
    wp_nonce_field('fpsml_metabox_nonce', 'fpsml_metabox_nonce_field');
}



