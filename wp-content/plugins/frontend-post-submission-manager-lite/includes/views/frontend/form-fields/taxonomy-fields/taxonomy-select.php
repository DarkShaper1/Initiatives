<div class="fpsml-select-field <?php echo (!empty( $field_details['select_multiple'] )) ? 'fpsml-multiple-select' : ''; ?>">
    <select name="<?php echo esc_attr( $field_key ) ?><?php if ( !empty( $field_details['select_multiple'] ) ) { ?>[]<?php } ?>" <?php if ( !empty( $field_details['select_multiple'] ) ) { ?>multiple="multiple"<?php } ?>>
        <option value=""><?php echo (!empty( $field_details['first_option_label'] )) ? esc_html( $field_details['first_option_label'] ) : esc_html__( sprintf( 'Выбери %s', 'город' ), 'frontend-post-submission-manager-lite' ); ?></option>
        <?php
        $child_of = !empty( $field_details['child_of'] ) ? $field_details['child_of'] : 0;
        $terms = get_terms( $taxonomy, array( 'hide_empty' => 0, 'child_of' => $child_of ) );
        $terms_hierarchy = array();
        $fpsml_library_obj->sort_terms_hierarchicaly( $terms, $terms_hierarchy, $child_of );
        $terms_exclude = !empty( $field_details['exclude_terms'] ) ? explode( ',', $field_details['exclude_terms'] ) : array();
        $args = array( 'terms' => $terms_hierarchy,
            'exclude' => $terms_exclude,
            'hierarchical' => $taxonomy_details->hierarchical,
            'html' => '',
            'selected_terms' => (!empty( $edit_post_terms_id )) ? $edit_post_terms_id : array()
        );

        if ( count( $terms_hierarchy ) > 0 ) {
            $option = $fpsml_library_obj->print_terms_as_option( $args );
            echo $fpsml_library_obj->sanitize_html( $option );
        }
        ?>
    </select>
</div>