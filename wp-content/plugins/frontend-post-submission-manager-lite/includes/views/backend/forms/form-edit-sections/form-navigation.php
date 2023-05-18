
<div class="fpsml-shortcode-wrapper">
    <div class="fpsml-field-wrap fpsml-shortcode-common">
        <label><?php esc_html_e('Form Shortcode', 'frontend-post-submission-manager-lite') ?></label>
        <div class="fpsml-field">
            <span class="fpsml-shortcode-preview">[fpsm alias="<?php echo esc_attr($form_row->form_alias); ?>"]</span>
            <span class="fpsml-clipboard-copy"><i class="fas fa-clipboard-list"></i></span>
        </div>
    </div>
</div>

<div class="fpsml-form-nav-wrap">
    <ul class="fpsml-form-nav">
        <li><a href="javascript:void(0);" class="fpsml-nav-item fpsml-active-nav" data-tab="basic"><span class="dashicons dashicons-admin-generic"></span><?php esc_html_e('Basic', 'frontend-post-submission-manager-lite'); ?></a></li>
        <?php if ($form_row->form_type == 'login_require') { ?>
            <li><a href="javascript:void(0);" class="fpsml-nav-item" data-tab="login"><span class="dashicons dashicons-lock"></span><?php esc_html_e('Login', 'frontend-post-submission-manager-lite'); ?></a></li>
        <?php } ?>
        <li><a href="javascript:void(0);" class="fpsml-nav-item" data-tab="form"><span class="dashicons dashicons-feedback"></span><?php esc_html_e('Form', 'frontend-post-submission-manager-lite'); ?></a></li>
        <li><a href="javascript:void(0);" class="fpsml-nav-item" data-tab="layout"><span class="dashicons dashicons-layout"></span><?php esc_html_e('Layout', 'frontend-post-submission-manager-lite'); ?></a></li>
        <li><a href="javascript:void(0);" class="fpsml-nav-item" data-tab="notification"><span class="dashicons dashicons-email"></span><?php esc_html_e('Notification', 'frontend-post-submission-manager-lite'); ?></a></li>
        <li><a href="javascript:void(0);" class="fpsml-nav-item" data-tab="security"><span class="dashicons dashicons-shield"></span><?php esc_html_e('Security', 'frontend-post-submission-manager-lite'); ?></a></li>
        <li><a href="javascript:void(0);" class="fpsml-nav-item" data-tab="upgrade"><span class="dashicons dashicons-star-filled"></span><?php esc_html_e('Upgrade to PRO', 'frontend-post-submission-manager-lite'); ?></a></li>
                <?php
                /**
                 * Fires while building the nav
                 *
                 * @since 1.0.0
                 */
                do_action('fpsml_form_nav');
                ?>
    </ul>
</div>