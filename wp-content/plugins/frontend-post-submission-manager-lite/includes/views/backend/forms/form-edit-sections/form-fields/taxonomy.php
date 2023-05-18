<?php
$field_key_array = explode('|', $field_key);
$taxonomy = end($field_key_array);
$taxonomy_details = get_taxonomy($taxonomy);
global $fpsml_library_obj;
$show_hide_toggle_class = $taxonomy;
?>
<div class="fpsml-each-form-field">
    <div class="fpsml-field-head fpsml-clearfix">
        <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span><?php echo esc_html($taxonomy_details->label); ?></h3>
    </div>
    <div class="fpsml-field-body fpsml-display-none">
        <?php include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/common-fields.php'); ?>
        <div class="fpsml-show-fields-ref-<?php echo esc_attr($taxonomy); ?> <?php echo (empty($field_details['show_on_form'])) ? 'fpsml-display-none' : ''; ?>">
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Field Type', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <select name="<?php echo esc_attr($field_name_prefix) ?>[field_type]" class="fpsml-toggle-trigger" data-toggle-class='fpsml-taxonomy-field-type-ref'>
                        <?php
                        $field_type = (!empty($field_details['field_type'])) ? $field_details['field_type'] : 'select';
                        ?>
                        <option value="select" <?php selected($field_type, 'select'); ?>><?php esc_html_e('Select Dropdown', 'frontend-post-submission-manager-lite'); ?></option>
                        <option value="checkbox" <?php selected($field_type, 'checkbox'); ?>><?php esc_html_e('Checkbox', 'frontend-post-submission-manager-lite'); ?></option>
                        <?php
                        if ($taxonomy_details->hierarchical == 0) {
                            ?>
                            <option value="textfield" <?php selected($field_type, 'textfield'); ?>><?php esc_html_e('Textfield', 'frontend-post-submission-manager-lite'); ?></option>
                            <?php
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="fpsml-field-wrap fpsml-taxonomy-field-type-ref" <?php echo $fpsml_library_obj->display_none($field_type, 'select'); ?> data-toggle-ref="select">
                <label><?php esc_html_e('First Option Label', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="<?php echo esc_attr($field_name_prefix) ?>[first_option_label]" value="<?php echo (!empty($field_details['first_option_label'])) ? esc_attr($field_details['first_option_label']) : ''; ?>"/>
                </div>
            </div>
            <div class="fpsml-field-wrap fpsml-taxonomy-field-type-ref" <?php echo $fpsml_library_obj->display_none($field_type, 'select'); ?> data-toggle-ref="select">
                <label><?php esc_html_e('Multiple', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="checkbox" name="<?php echo esc_attr($field_name_prefix) ?>[select_multiple]" value="1" <?php echo (!empty($field_details['select_multiple'])) ? 'checked="checked"' : ''; ?>/>
                    <p class="description"><?php esc_html_e(sprintf('Please check if you want to enable mutiple %s selection', $taxonomy_details->label), 'frontend-post-submission-manager-lite'); ?></p>
                </div>
            </div>
            <div class="fpsml-field-wrap fpsml-taxonomy-field-type-ref" <?php echo $fpsml_library_obj->display_none($field_type, 'checkbox'); ?> data-toggle-ref='checkbox'>
                <label><?php esc_html_e('Display Type', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <select name="<?php echo esc_attr($field_name_prefix) ?>[display_type]">
                        <?php
                        $display_type = (!empty($field_details['display_type'])) ? $field_details['display_type'] : 'inline';
                        ?>
                        <option value="inline" <?php selected($display_type, 'inline'); ?>><?php esc_html_e('Inline', 'frontend-post-submission-manager-lite'); ?></option>
                        <option value="block" <?php selected($display_type, 'block'); ?>><?php esc_html_e('Block', 'frontend-post-submission-manager-lite'); ?></option>
                    </select>
                </div>
            </div>
            <?php
            if ($taxonomy_details->hierarchical == 0) {
                ?>
                <div class="fpsml-field-wrap fpsml-taxonomy-field-type-ref" <?php echo $fpsml_library_obj->display_none($field_type, 'textfield'); ?> data-toggle-ref='textfield'>
                    <label><?php esc_html_e('Auto Complete', 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <input type="checkbox" name="<?php echo esc_attr($field_name_prefix); ?>[auto_complete]" value="1" <?php echo (!empty($field_details['auto_complete'])) ? 'checked="checked"' : ''; ?>/>
                    </div>
                </div>
                <?php
            } else {
                ?>
                <div class="fpsml-field-wrap">
                    <label><?php esc_html_e(sprintf('Display Child %s of ', $taxonomy_details->label), 'frontend-post-submission-manager-lite'); ?></label>
                    <div class="fpsml-field">
                        <select name="<?php echo esc_attr($field_name_prefix) ?>[child_of]">
                            <option value=""><?php _e('None', 'frontend-post-submission-manager-lite'); ?></option>
                            <?php
                            $selected_child_of = (!empty($field_details['child_of'])) ? $field_details['child_of'] : '';
                            $terms = get_terms($taxonomy, array('hide_empty' => 0));
                            $terms_hierarchy = array();
                            $fpsml_library_obj->sort_terms_hierarchicaly($terms, $terms_hierarchy);
                            if (count($terms_hierarchy) > 0) {
                                $args = array('terms' => $terms_hierarchy,
                                    'hierarchical' => $taxonomy_details->hierarchical,
                                    'selected_terms' => $selected_child_of
                                );
                                $option = $fpsml_library_obj->print_terms_as_option($args);
                            }
                            echo $fpsml_library_obj->sanitize_html($option);
                            ?>
                        </select>
                    </div>
                </div>

            <?php } ?>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e(sprintf('Exclude %s', $taxonomy_details->label), 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="<?php echo esc_attr($field_name_prefix) ?>[exclude_terms]" value="<?php echo (!empty($field_details['exclude_terms'])) ? esc_attr($field_details['exclude_terms']) : ''; ?>"/>
                    <p class="description"><?php esc_html_e(sprintf('Please enter the slug of the %s separated by comma(,) which you want to exclude from displaying.', $taxonomy_details->label), 'frontend-post-submission-manager-lite'); ?></p>
                </div>
            </div>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e(sprintf('Auto assign %s ', $taxonomy_details->label), 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <select name="<?php echo esc_attr($field_name_prefix) ?>[auto_assign][]" multiple="multiple">
                        <option value=""><?php _e('None', 'frontend-post-submission-manager-lite'); ?></option>
                        <?php
                        $selected_auto_assign = (!empty($field_details['auto_assign'])) ? $field_details['auto_assign'] : '';
                        $terms = get_terms($taxonomy, array('hide_empty' => 0));
                        $terms_hierarchy = array();
                        $fpsml_library_obj->sort_terms_hierarchicaly($terms, $terms_hierarchy);
                        if (count($terms_hierarchy) > 0) {
                            $args = array('terms' => $terms_hierarchy,
                                'hierarchical' => $taxonomy_details->hierarchical,
                                'selected_terms' => $selected_auto_assign
                            );
                            $option = $fpsml_library_obj->print_terms_as_option($args);
                        }
                        echo $fpsml_library_obj->sanitize_html($option);
                        ?>
                    </select>
                    <p class="description"><?php esc_html_e(sprintf('Please choose the %s that you want to assign explicitly. Please use control or command key to select multiple options.', $taxonomy_details->label), 'frontend-post-submission-manager-lite'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>