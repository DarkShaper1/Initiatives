<?php
$layout_settings = (!empty($form_details['layout'])) ? $form_details['layout'] : array();
?>
<div class="fpsml-settings-each-section fpsml-display-none" data-tab="layout">
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Form Template', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <select name="form_details[layout][template]" class="fpsml-form-template">
                <?php
                $selected_template = (!empty($layout_settings['template'])) ? $layout_settings['template'] : 'template-1';
                for ($i = 1; $i <= 5; $i++) {
                    ?>
                    <option value="template-<?php echo intval($i); ?>" <?php selected($selected_template, 'template-' . $i); ?>><?php esc_html_e(sprintf('Template %d', $i), 'frontend-post-submission-manager-lite'); ?></option>
                    <?php
                }
                ?>
            </select>
            <div class="fpsml-form-template-preview">
                <?php
                for ($i = 1; $i <= 5; $i++) {
                    ?>
                    <img src="<?php echo FPSML_URL . '/assets/images/form-template-previews/template-' . $i . '.jpg'; ?>" data-template-id="<?php echo 'template-' . $i; ?>" class="fpsml-form-template-preview-img <?php echo ($selected_template != 'template-' . $i) ? 'fpsml-display-none' : ''; ?>"/>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <div class="fpsml-field-wrap">
        <label><?php esc_html_e('Custom Fields Display Template', 'frontend-post-submission-manager-lite'); ?></label>
        <div class="fpsml-field">
            <select name="form_details[layout][custom_field_display_template]" class="fpsml-custom-field-template-trigger">
                <?php
                $selected_template = (!empty($layout_settings['custom_field_display_template'])) ? $layout_settings['custom_field_display_template'] : 'template-1';
                for ($i = 1; $i <= 2; $i++) {
                    ?>
                    <option value="template-<?php echo intval($i); ?>" <?php selected($selected_template, 'template-' . $i); ?>><?php esc_html_e(sprintf('Template %d', $i), 'frontend-post-submission-manager-lite'); ?></option>
                    <?php
                }
                ?>
            </select>
            <div class="fpsml-post-template-preview">
                <?php
                for ($i = 1; $i <= 2; $i++) {
                    ?>
                    <img src="<?php echo FPSML_URL . '/assets/images/post-field-previews/template-' . $i . '.jpg'; ?>" data-template-id="<?php echo 'template-' . $i; ?>" class="fpsml-post-template-preview-img <?php echo ($selected_template != 'template-' . $i) ? 'fpsml-display-none' : ''; ?>"/>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</div>