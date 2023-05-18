<div class="fpsml-form-message"></div>
<?php
$custom_field_type_list = FPSML_CUSTOM_FIELD_TYPE_LIST;
if (!empty($custom_field_type_list)) {
    foreach ($custom_field_type_list as $custom_field_type => $custom_field_details) {
        $custom_field_type_label = $custom_field_details['label'];
        $field_name_prefix = 'form_details[form][fields][{{data.field_key}}]';
        $show_hide_toggle_class = '{{data.meta_key}}';
        $field_details['field_label'] = '{{data.label}}';
        $field_type = $custom_field_type;
        $field_key = '{{data.field_key}}';
        ?>
        <script type="text/html" id="tmpl-custom-<?php echo esc_attr($custom_field_type); ?>">
            <?php include(FPSML_PATH . '/includes/views/backend/js-templates/tmpl-custom-field-holder.php'); ?>
        </script>
        <?php
    }
}
?>

