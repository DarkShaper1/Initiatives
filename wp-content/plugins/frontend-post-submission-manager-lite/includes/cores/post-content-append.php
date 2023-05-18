<?php
defined('ABSPATH') or die('No script kiddies please!!');
global $wp_query;
if (empty($wp_query->queried_object_id)) {
    return $content;
}
$post_id = $wp_query->queried_object_id;
$form_alias = get_post_meta($post_id, '_fpsml_form_alias', true);
if (empty($form_alias)) {
    return;
}
global $fpsml_library_obj;
$form_row = $fpsml_library_obj->get_form_row_by_alias($form_alias);
if (empty($form_row->form_details)) {
    return $content;
}
$form_details = maybe_unserialize($form_row->form_details);
if (empty($form_details['form']['fields'])) {
    return $content;
}
if (empty($form_details['form']['fields'])) {
    return $content;
}
$form_fields = $form_details['form']['fields'];
$append_flag = 0;
ob_start();
foreach ($form_fields as $field_key => $field_details) {
    // If field is enabled in the form
    if ($fpsml_library_obj->is_custom_field_key($field_key)) {
        if (!empty($field_details['show_on_form']) && !empty($field_details['post_detail_display']) && $field_details['display_position'] == $display_position_check) {
            $custom_field_meta_key = $fpsml_library_obj->get_meta_key_by_field_key($field_key);
            $custom_field_value = get_post_meta($post_id, $custom_field_meta_key, true);
            /**
             * Filters custom field value html being printed
             *
             * @param mixed $custom_field_value
             * @param string $custom_field_meta_key
             *
             * @since 1.0.0
             */
            $filterd_custom_field_value = apply_filters('fpsml_custom_field_html', $custom_field_value, $custom_field_meta_key);
            if (empty($append_flag)) {
                $append_flag = 1;
            }
            if (!empty($custom_field_value)) {
                ?>
                <div class="fpsml-each-display-field">
                    <label><?php echo esc_html($field_details['display_label']); ?></label>
                    <div class="fpsml-display-value">
                        <?php
                        switch ($field_details['field_type']) {
                            case 'textfield':
                            case 'textarea':
                            case 'select':
                            case 'radio':
                            case 'number':
                            case 'datepicker':
                                echo $fpsml_library_obj->sanitize_html($filterd_custom_field_value);
                                break;
                            case 'email':
                                ?>
                                <a href="mailto:<?php echo esc_attr($custom_field_value) ?>"><?php echo $fpsml_library_obj->sanitize_html($custom_field_value); ?></a>
                                <?php
                                break;
                            case 'checkbox':
                                if (is_array($custom_field_value)) {
                                    foreach ($custom_field_value as $c_value) {
                                        ?>
                                        <span class="fpsml-each-checkbox-value"><?php echo $fpsml_library_obj->sanitize_html($c_value); ?></span>
                                        <?php
                                    }
                                }

                                break;
                            case 'file_uploader':
                                if (!empty($custom_field_value)) {
                                    $media_ids = explode(',', $custom_field_value);
                                    foreach ($media_ids as $media_id) {
                                        if (wp_attachment_is_image($media_id)) {
                                            $image_display_size = $field_details['image_size'];
                                            $display_image_url = $media_thumbnail_url = wp_get_attachment_image_src($media_id, $image_display_size);
                                            $media_url = wp_get_attachment_image_src($media_id, 'full');
                                        } else {
                                            $media_url = wp_get_attachment_url($media_id);
                                        }
                                        if (wp_attachment_is_image($media_id)) {
                                            ?>
                                            <div class="fpsml-display-each-image">
                                                <a href="<?php echo esc_url($media_url[0]); ?>" <?php echo (!empty($field_details['open_in_new_tab'])) ? 'target="_blank"' : ''; ?>><img src="<?php echo esc_url($display_image_url[0]); ?>" alt="<?php echo get_the_title($media_id); ?>"/></a>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <a href="<?php echo esc_url($media_url); ?>" <?php echo (!empty($field_details['open_in_new_tab'])) ? 'target="_blank"' : ''; ?>><?php echo get_the_title($media_id); ?><</a>
                                            <?php
                                        }
                                    }
                                }
                                break;
                            case 'url':
                                ?><a href="<?php echo esc_url($custom_field_value); ?>" <?php echo (!empty($field_details['open_in_new_tab'])) ? 'target="_blank"' : ''; ?>><?php echo $fpsml_library_obj->sanitize_html($custom_field_value); ?></a><?php
                                break;
                            case 'tel':
                                ?><a href="tel:<?php echo esc_url($custom_field_value); ?>"><?php echo $fpsml_library_obj->sanitize_html($custom_field_value); ?></a><?php
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
                                    <?php
                                    /**
                                     * Fires inside the youtube embed iframe
                                     *
                                     * @param string $custom_field_meta_key
                                     * @param array $form_row
                                     *
                                     * @since 1.0.0
                                     */
                                    do_action('fpsml_youtube_embed_extra', $custom_field_meta_key, $form_row);
                                    ?>
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
        }
    }
}
if ($append_flag == 1) {
    $append_class = (!empty($form_details['layout']['custom_field_display_template'])) ? 'fpsml-custom-' . $form_details['layout']['custom_field_display_template'] : 'fpsml-custom-template-1';
    $append_content = ob_get_contents();
    $append_content = '<div class="fpsml-custom-fields-content-wrap fpsml-append-' . $display_position_check . ' ' . $append_class . '">' . $append_content . '</div>';
}
ob_end_clean();

