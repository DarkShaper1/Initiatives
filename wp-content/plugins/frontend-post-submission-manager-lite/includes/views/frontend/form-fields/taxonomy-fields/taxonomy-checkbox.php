
<?php

$child_of = !empty($field_details['child_of']) ? $field_details['child_of'] : 0;
$terms = get_terms($taxonomy, array('hide_empty' => 0, 'child_of' => $child_of));
$terms_hierarchy = array();
$fpsml_library_obj->sort_terms_hierarchicaly($terms, $terms_hierarchy, $child_of);
$terms_exclude = !empty($field_details['exclude_terms']) ? explode(',', $field_details['exclude_terms']) : array();
$display_type = $field_details['display_type'];
$display_class = 'fpsml-' . $display_type . '-checkbox';
$args = array('terms' => $terms_hierarchy,
    'exclude' => $terms_exclude,
    'hierarchical' => $taxonomy_details->hierarchical,
    'html' => '',
    'field_name' => $field_key,
    'checked' => array(),
    'class' => $display_class,
    'checked_terms' => (!empty($edit_post_terms_id)) ? $edit_post_terms_id : array()
);

if (count($terms_hierarchy) > 0) {
    $checkbox_html = $fpsml_library_obj->print_terms_as_checkbox($args);
    echo $fpsml_library_obj->sanitize_html($checkbox_html);
}

