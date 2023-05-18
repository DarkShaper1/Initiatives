<?php
if (empty($_GET['form_id'])) {
    return;
}
defined('ABSPATH') or die('No script kiddies please!!');
global $fpsml_library_obj;
$form_id = intval($_GET['form_id']);
$form_row = $fpsml_library_obj->get_form_row_by_id($form_id);
if (empty($form_row)) {
    return;
}
$form_details = (!empty($form_row->form_details)) ? $form_row->form_details : '';
$form_details = maybe_unserialize($form_details);
?>
<div class="wrap fpsml-wrap fpsml-clearfix">
    <div class="fpsml-header fpsml-clearfix">
        <h1 class="fpsml-floatLeft">
            <?php esc_html_e('Frontend Post Submission Manager', 'frontend-post-submission-manager-lite'); ?>
            <span><?php esc_html_e('Lite', 'frontend-post-submission-manager-lite'); ?></span>
        </h1>

        <div class="fpsml-add-wrap">
            <a href="javascript:void(0);" class="fpsml-button-primary fpsml-form-save" data-form='fpsml-edit-form'><?php esc_html_e('Save', 'frontend-post-submission-manager-lite'); ?></a>
            <a href="<?php echo site_url() . '?fpsml_form_preview=true&fpsml_form_alias=' . esc_attr($form_row->form_alias) . '&_wpnonce=' . wp_create_nonce('fpsml_preview_nonce'); ?>" class="fpsml-button-primary btn-preview" target="_blank">
                <?php esc_html_e('Preview', 'frontend-post-submission-manager-lite'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=fpsm'); ?>" class="fpsml-button-primary btn-cancel"><?php esc_html_e('Cancel', 'frontend-post-submission-manager-lite'); ?></a>
        </div>


    </div>

    <?php
    /**
     * Form Navigation
     */
    include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-navigation.php');
    ?>
    <form class="fpsml-form-wrap fpsml-edit-form">
        <input type="hidden" name="form_id" value="<?php echo intval($form_id); ?>"/>
        <input type="hidden" name="post_type" value="<?php echo (!empty($form_row->post_type)) ? esc_attr($form_row->post_type) : 'post'; ?>"/>
        <input type="hidden" name="form_type" value="<?php echo (!empty($form_row->form_type)) ? esc_attr($form_row->form_type) : 'login_require'; ?>"/>
        <?php
        /**
         * Fires on start of the form sections
         *
         * @since 1.0.0
         *
         * @param array $form_row
         *
         */
        do_action('fpsml_form_sections_start', $form_row);
        ?>
        <?php
        /**
         * Basic Settings
         */
        include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/basic-settings.php');
        ?>
        <?php
        if ($form_row->form_type == 'login_require') {
            /**
             * Login Form Settings
             */
            include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/login-form-settings.php');
        }
        ?>
        <?php
        /**
         * Form Fields Settings
         */
        include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-field-settings.php');
        ?>
        <?php
        /**
         * Layout Settings
         */
        include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/layout-settings.php');
        ?>
        <?php
        /**
         * Notification Settings
         */
        include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/notification-settings.php');
        ?>
        <?php
        /**
         * Security Settings
         */
        include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/security-settings.php');
        ?>
        <?php
        /**
         * Upgrade to PRO
         */
        include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/upgrade-to-pro.php');
        ?>

        <?php
        /**
         * Fires on end of the form sections
         *
         * @since 1.0.0
         *
         * @param array $form_row
         *
         */
        do_action('fpsml_form_sections_end', $form_row);
        ?>

    </form>

</div>