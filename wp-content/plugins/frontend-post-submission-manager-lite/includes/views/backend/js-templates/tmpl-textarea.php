<div class="fpsml-each-form-field">
    <div class="fpsml-field-head fpsml-clearfix">
        <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span>{{data.label}} <span class="fpsml-field-type-label">- <?php esc_html_e('Textarea', 'frontend-post-submission-manager-lite'); ?></span></h3>
        <a href="javascript:void(0);" class="fpsml-field-remove-trigger"><span class="dashicons dashicons-trash"></span></a>
    </div>
    <div class="fpsml-field-body fpsml-display-none">
        <?php include(FPSML_PATH . '/includes/views/backend/js-templates/tmpl-common-fields.php'); ?>
        <div class="fpsml-show-fields-ref-{{data.meta_key}} fpsml-display-none">
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Character Limit', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="number" min="0" name="form_details[form][fields][{{data.field_key}}][character_limit]"/>
                </div>
            </div>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e('Character Limit Error Message', 'frontend-post-submission-manager-lite'); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="form_details[form][fields][{{data.field_key}}][character_limit_error_message]"/>
                </div>
            </div>
        </div>
    </div>
</div>