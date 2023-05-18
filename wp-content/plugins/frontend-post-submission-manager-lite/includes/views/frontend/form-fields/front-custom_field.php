<?php

$field_type = $field_details['field_type'];
$field_file = 'front-' . $field_type . '.php';
$custom_field_array = explode('|', $field_key);
$custom_field_meta_key = end($custom_field_array);
$custom_field_saved_value = (!empty($edit_post)) ? get_post_meta($post_id, $custom_field_meta_key, true) : '';
if (file_exists(FPSML_PATH . '/includes/views/frontend/form-fields/custom-field-types/' . $field_file)) {
    include(FPSML_PATH . '/includes/views/frontend/form-fields/custom-field-types/' . $field_file);
}
?>