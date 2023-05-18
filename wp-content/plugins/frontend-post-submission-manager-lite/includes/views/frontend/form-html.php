<?php
defined('ABSPATH') or die('No script kiddies please!!');
$form_template = (!empty($form_details['layout']['template'])) ? $form_details['layout']['template'] : 'template-1';
$form_alias_class = 'fpsml-alias-' . $form_row->form_alias;
?>
<form method="post" class="fpsml-front-form fpsml-<?php echo esc_attr($form_template); ?> <?php echo esc_attr($form_alias_class); ?>" data-alias="<?php echo esc_attr($form_row->form_alias); ?>">
    <?php if (empty($form_details['customize']['hide_form_title'])) { ?><h2 class="fpsml-form-title"><?php echo esc_html($form_row->form_title); ?></h2><?php } ?>

    <input type="hidden" name="form_alias" value="<?php echo esc_attr($form_row->form_alias); ?>"/>
    <?php if (!empty($edit_post)) {
        ?>
        <input type="hidden" name="post_id" value="<?php echo intval($edit_post->ID); ?>" class="fpsml-edit-post-id"/>
        <?php
    }
    ?>
    <?php
    /**
     * Fires at the start of form
     *
     * @since 1.0.0
     */
    do_action('fpsml_form_start', $form_row);
    if (!empty($form_details['form']['fields'])) {
        foreach ($form_details['form']['fields'] as $field_key => $field_details) {

            $field_file = $fpsml_library_obj->generate_field_file($field_key);
            if (file_exists(FPSML_PATH . '/includes/views/frontend/form-fields/front-' . $field_file)) {
                // If field is enabled from the backend
                if (!empty($field_details['show_on_form'])) {
                    $field_class = $fpsml_library_obj->generate_field_class($field_key);
                    if ($fpsml_library_obj->is_taxonomy_key($field_key)) {
                        $field_type = $field_details['field_type'];
                        $field_type_class = ' fpsml-taxonomy-' . $field_type;
                    } else if ($fpsml_library_obj->is_custom_field_key($field_key)) {
                        $field_type = $field_details['field_type'];
                        $field_type_class = ' fpsml-custom-field-' . $field_type;
                    } else {
                        $field_type_class = '';
                    }
                    ?>
                    <div class="fpsml-field-wrap<?php echo esc_attr($field_type_class); ?> <?php echo esc_attr($field_class); ?>" data-field-key="<?php echo esc_attr($field_key); ?>">
                        <label><?php echo (!empty($field_details['field_label'])) ? esc_html($field_details['field_label']) : ''; ?></label>
                        <div class="fpsml-field">
                            <?php
                            include(FPSML_PATH . '/includes/views/frontend/form-fields/front-' . $field_file);
                            if (!empty($field_details['field_note'])) {
                                ?>
                                <div class="fpsml-field-note"><?php echo $fpsml_library_obj->sanitize_html($field_details['field_note']); ?></div>
                                <?php
                            }
                            ?>
                        </div>
                        <div class="fpsml-error"></div>
                    </div>
                    <?php
                }
            }
        }
    }
    /**
     * Captcha
     */
    if (!empty($form_details['security']['frontend_form_captcha'])) {
        $site_key = (!empty($form_details['security']['site_key'])) ? $form_details['security']['site_key'] : '';
        if (!empty($site_key)) {
            ?>

            <div class="fpsml-field-wrap fpsml-captcha-field" data-field-key="captcha">
                <label><?php echo (!empty($form_details['security']['captcha_label'])) ? esc_attr($form_details['security']['captcha_label']) : ''; ?></label>
                <div class="fpsml-field">
                    <div data-field-key="security">
                        <script type="text/javascript" src="//www.google.com/recaptcha/api.js"></script>
                        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    /**
     * Fires at the end of form
     *
     * @since 1.0.0
     */
    do_action('fpsml_form_end', $form_row);
    ?>
    <div class="fpsml-field-wrap fpsml-has-submit-btn">
        <div class="fpsml-field">
            <input type="submit" value="<?php echo (!empty($form_details['form']['submit_button_label'])) ? esc_attr($form_details['form']['submit_button_label']) : esc_html__('Submit', 'frontend-post-submission-manager-lite'); ?>"/>
            <img src="<?php echo FPSML_URL . '/assets/images/ajax-loader-front.gif'; ?>" class="fpsml-ajax-loader"/>
        </div>
    </div>
    <div class="fpsml-form-message fpsml-display-none"></div>
</form>

