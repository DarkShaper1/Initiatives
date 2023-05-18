<div class="fpsml-each-form-field">
    <div class="fpsml-field-head fpsml-clearfix">
        <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span>{{data.label}} <span class="fpsml-field-type-label">- <?php echo esc_html( $custom_field_type_label ); ?></span></h3>
        <a href="javascript:void(0);" class="fpsml-field-remove-trigger"><span class="dashicons dashicons-trash"></span></a>
    </div>
    <div class="fpsml-field-body fpsml-display-none">
        <input type="hidden" name="<?php echo esc_attr( $field_name_prefix ); ?>[field_type]" value="<?php echo esc_attr( $custom_field_type ); ?>"/>
        <?php
        if ( file_exists( FPSML_PATH . '/includes/views/backend/forms/custom-field-types/' . $custom_field_type . '.php' ) ) {
            include(FPSML_PATH . '/includes/views/backend/forms/custom-field-types/' . $custom_field_type . '.php');
            include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/post-display-fields.php');
        }
        ?>
    </div>
</div>