<?php
$form_settings = (!empty($form_details['form'])) ? $form_details['form'] : array();
?>
<div class="fpsml-settings-each-section fpsml-display-none fpsml-clearfix" data-tab="form">
    <div class="fpsml-form-fields-wrap ">
        <div class="fpsml-form-fields-list">
            <h3 class="fpsml-form-settings-heading"><?php esc_html_e('Form Fields', 'frontend-post-submission-manager-lite'); ?></h3>
            <div class="fpsml-sortable">
                <?php
                $post_type = $form_row->post_type;
                $form_type = $form_row->form_type;
                $form_fields = (!empty($form_settings['fields'])) ? $form_settings['fields'] : $fpsml_library_obj->get_default_fields($post_type, $form_type);
                foreach ($form_fields as $field_key => $field_details) {
                    $field_file = $fpsml_library_obj->generate_field_file($field_key);
                    if (file_exists(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/' . $field_file)) {
                        $field_name_prefix = "form_details[form][fields][$field_key]";
                        include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/' . $field_file);
                    }
                }
                ?>
            </div>
        </div>
        <div class="fpsml-form-other-settings">
            <h3 class="fpsml-form-settings-heading"><?php esc_html_e('Other Settings', 'frontend-post-submission-manager-lite'); ?></h3>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Submit Button Label', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="form_details[form][submit_button_label]" value="<?php echo (!empty($form_settings['submit_button_label'])) ? esc_attr($form_settings['submit_button_label']) : ''; ?>"/>
                </div>
            </div>
        </div>
    </div>

    <?php include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/custom-field-add-form.php'); ?>
</div>