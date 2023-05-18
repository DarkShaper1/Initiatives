<?php
defined('ABSPATH') or die('No script kiddies please!!');
?>
<div class="wrap fpsml-wrap">
    <div class="fpsml-header">
        <h1 class="fpsml-floatLeft">
            <?php esc_html_e('Frontend Post Submission Manager', 'frontend-post-submission-manager-lite'); ?>
            <span><?php esc_html_e('Lite', 'frontend-post-submission-manager-lite'); ?></span>
        </h1>
        <div class="fpsml-add-wrap">
            <a href="https://1.envato.market/JMVMq" target="_blank"><input type="button" class="fpsml-button-primary" value="<?php esc_html_e('Upgrade to PRO', 'frontend-post-submission-manager-lite'); ?>"></a>
        </div>
    </div>

    <div class="fpsml-grid-wrap">
        <div class="fpsml-title-wrap">
            <h2><?php esc_html_e('Form Lists', 'frontend-post-submission-manager-lite'); ?></h2>

        </div>
        <table class="wp-list-table widefat fixed fpsml-form-lists-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Form Title', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Post Type', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Form Type', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Status', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Action', 'frontend-post-submission-manager-lite'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                global $wpdb;
                $form_table = FPSML_FORM_TABLE;
                $form_rows = $wpdb->get_results("select * from $form_table order by form_title asc");
                if (!empty($form_rows)) {
                    foreach ($form_rows as $form_row) {
                        ?>
                        <tr>
                            <td><a href="<?php echo admin_url('admin.php?page=fpsm&form_id=' . intval($form_row->form_id) . '&action=edit_form'); ?>"><?php echo esc_html($form_row->form_title); ?></a></td>
                            <td>
                                <span class="fpsml-shortcode-preview">[fpsm alias="<?php echo esc_html(($form_row->form_alias)); ?>"]</span>
                                <span class="fpsml-clipboard-copy"><i class="fas fa-clipboard-list"></i></span>
                            </td>
                            <td><?php echo esc_html($form_row->post_type); ?></td>
                            <td><?php
                                $form_type_label = array('login_require' => esc_html__('Login Require Form'), 'guest' => esc_html__('Guest Form', 'frontend-post-submission-manager-lite'));
                                echo esc_html($form_type_label[$form_row->form_type]);
                                ?></td>
                            <td><?php echo (!empty($form_row->form_status)) ? esc_html__('Active', 'frontend-post-submission-manager-lite') : esc_html__('Inactive', 'frontend-post-submission-manager-lite'); ?></td>
                            <td>
                                <a class="fpsml-edit" href="<?php echo admin_url('admin.php?page=fpsm&form_id=' . intval($form_row->form_id) . '&action=edit_form'); ?>" title="<?php esc_html_e('Edit Form', 'frontend-post-submission-manager-lite'); ?>"><?php esc_html_e('Edit', 'frontend-post-submission-manager-lite'); ?></a>
                                <a class="fpsml-preview" href="<?php echo site_url() . '?fpsml_form_preview=true&fpsml_form_alias=' . esc_attr($form_row->form_alias) . '&_wpnonce=' . wp_create_nonce('fpsml_preview_nonce'); ?>" target="_blank" title="<?php esc_html_e('Preview', 'frontend-post-submission-manager-lite'); ?>"><?php esc_html_e('Preview', 'frontend-post-submission-manager-lite'); ?></a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5"><?php esc_html_e('No forms added yet.', 'frontend-post-submission-manager-lite'); ?></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th><?php esc_html_e('Form Title', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Shortcode', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Post Type', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Form Type', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Status', 'frontend-post-submission-manager-lite'); ?></th>
                    <th><?php esc_html_e('Action', 'frontend-post-submission-manager-lite'); ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>