<div class="fpsml-custom-field-add-form">
    <h3><?php esc_html_e('Custom Field', 'frontend-post-submission-manager-lite'); ?></h3>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Label', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" id="fpsml-custom-field-label"/>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Meta Key', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <input type="text" id="fpsml-custom-field-meta-key"/>
            <p class="description"><?php esc_html_e('Please use plain text without any special characters for meta key and use underscore(_) instead of white space.', 'frontend-post-submission-manager-lite'); ?></p>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Field Type', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <select id="fpsml-custom-field-type" style="display:none">
                <?php
                $custom_field_type_list = FPSML_CUSTOM_FIELD_TYPE_LIST;
                foreach ($custom_field_type_list as $custom_field_type => $custom_field_details) {
                    $custom_field_label = $custom_field_details['label'];
                    ?>
                    <option value="<?php echo esc_attr($custom_field_type); ?>"><?php echo esc_html($custom_field_label); ?></option>
                    <?php
                }
                ?>

            </select>
            <div class="fpsml-custom-field-btns-wrap">
                <?php
                foreach ($custom_field_type_list as $custom_field_type => $custom_field_details) {
                    $custom_field_label = $custom_field_details['label'];
                    ?>
                    <div class="fpsml-custom-fld-btn fpsml-custom-field-type-trigger-btn <?php echo ($custom_field_type == 'textfield') ? 'btn-selected' : ''; ?>" data-field-type="<?php echo esc_attr($custom_field_type); ?>">
                        <i class="<?php echo esc_attr($custom_field_details['icon']); ?>"></i> <?php echo esc_html($custom_field_label); ?>
                    </div>

                <?php }
                ?>
            </div>
        </div>
    </div>

    <div class="fpsml-field-wrap">
        <div class="fpsml-field">
            <input type="button" class="fpsml-button-secondary fpsml-custom-field-add-trigger" value="<?php esc_attr_e('Add', 'frontend-post-submission-manager-lite'); ?>"/>
        </div>

    </div>
</div>