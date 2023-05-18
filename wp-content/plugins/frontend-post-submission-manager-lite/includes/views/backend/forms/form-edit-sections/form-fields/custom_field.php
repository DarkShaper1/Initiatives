<?php
$field_key_array = explode('|', $field_key);
$meta_key = end($field_key_array);
$field_label = (!empty($field_details['field_label'])) ? $field_details['field_label'] : esc_html__('Untitled Field', 'frontend-post-submission-manager-lite');
$show_hide_toggle_class = $meta_key;
$field_type = $field_details['field_type'];
$custom_field_type_list = FPSML_CUSTOM_FIELD_TYPE_LIST;
?>
<div class="fpsml-each-form-field" data-meta-key="<?php echo esc_attr($meta_key) ?>">
    <div class="fpsml-field-head fpsml-clearfix">
        <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span><?php echo esc_html($field_label); ?><span class="fpsml-field-type-label"> - <?php echo esc_html($custom_field_type_list[$field_type]['label']); ?></span></h3>
        <a href="javascript:void(0);" class="fpsml-field-remove-trigger"><span class="dashicons dashicons-trash"></span></a>
    </div>
    <div class="fpsml-field-body fpsml-display-none">
        <input type="hidden" name="<?php echo esc_attr($field_name_prefix); ?>[field_type]" value="<?php echo esc_attr($field_type); ?>"/>
        <?php
        if (file_exists(FPSML_PATH . '/includes/views/backend/forms/custom-field-types/' . $field_type . '.php')) {
            include(FPSML_PATH . '/includes/views/backend/forms/custom-field-types/' . $field_type . '.php');
            include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/post-display-fields.php');
        }
        /**
         * Fires at the end of all the custom field option has been printed
         *
         * @param type string $field_key
         * @param type array $field_details
         *
         * @since 1.0.0
         */
        do_action('fpsml_custom_field_admin_end', $field_key, $field_details);
        ?>

    </div>
</div>
