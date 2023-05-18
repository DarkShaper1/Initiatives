<?php

$field_key_array = explode( '|', $field_key );
$taxonomy = end( $field_key_array );
$taxonomy_field_type = $field_details['field_type'];
$taxonomy_details = get_taxonomy( $taxonomy );

if ( !empty( $edit_post ) ) {
    $edit_post_terms = get_the_terms( $edit_post, $taxonomy );
    if ( !empty( $edit_post_terms ) ) {
        if ( $taxonomy_details->hierarchical == 1 ) {
            $edit_post_terms_id = array_column( $edit_post_terms, 'term_id' );
        } else {
            $edit_post_terms_id = array_column( $edit_post_terms, 'name' );
        }
    } else {
        $edit_post_terms_id = array();
    }
}
switch( $taxonomy_field_type ) {
    case 'checkbox':
        include(FPSML_PATH . '/includes/views/frontend/form-fields/taxonomy-fields/taxonomy-checkbox.php');
        break;
    case 'select':
        include(FPSML_PATH . '/includes/views/frontend/form-fields/taxonomy-fields/taxonomy-select.php');
        break;
    case 'textfield':
        include(FPSML_PATH . '/includes/views/frontend/form-fields/taxonomy-fields/taxonomy-textfield.php');
        break;
}
?>

