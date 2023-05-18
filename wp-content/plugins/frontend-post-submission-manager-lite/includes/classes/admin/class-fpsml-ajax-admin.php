<?php

defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('FPSML_Ajax_Admin')) {

    class FPSML_Ajax_Admin {

        function __construct() {
            add_action('wp_ajax_fpsml_form_edit_action', array($this, 'process_form_edit'));
            add_action('wp_ajax_nopriv_fpsml_form_edit_action', array($this, 'permission_denied'));
            add_action('wp_ajax_fpsml_settings_save_action', array($this, 'save_global_settings'));
            add_action('wp_ajax_nopriv_fpsml_settings_save_action', array($this, 'permission_denied'));
        }

        function permission_denied() {
            die('No script kiddies please!!');
        }

        /**
         * Ajax nonce verification for ajax in admin
         *
         * @return bolean
         * @since 1.0.0
         */
        function admin_ajax_nonce_verify() {
            if (!empty($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'fpsml_backend_ajax_nonce')) {
                return true;
            } else {
                return false;
            }
        }

        /**
         * Process form edit
         *
         * @return void
         * @since 1.0.0
         */
        function process_form_edit() {
            if ($this->admin_ajax_nonce_verify()) {
                /**
                 * Fires on starting of form edit ajax
                 *
                 * @since 1.0.0
                 */
                do_action('fpsml_before_form_edit_ajax');
                $form_data = stripslashes_deep($_POST['form_data']);
                parse_str($form_data, $form_data);
                global $fpsml_library_obj;
                $sanitize_rule = array('notification_message' => 'to_br', 'login_note' => 'html', 'field_note' => 'html', 'custom_css' => 'to_br');
                $form_data = $fpsml_library_obj->sanitize_array($form_data, $sanitize_rule);
                $form_id = $form_data['form_id'];
                $form_title = $form_data['form_title'];
                $form_alias = $form_data['form_alias'];
                $post_type = $form_data['post_type'];
                $form_type = $form_data['form_type'];
                $form_status = (!empty($form_data['form_status'])) ? 1 : 0;
                if (empty($form_title) || empty($form_alias)) {
                    $response['status'] = 403;
                    $response['message'] = esc_html__('Form title or Alias cannot be empty.', 'frontend-post-submission-manager-lite');
                } else {
                    if ($fpsml_library_obj->is_alias_available($form_alias, $form_id)) {
                        global $wpdb;

                        $wpdb->update(FPSML_FORM_TABLE, array('form_title' => $form_title,
                            'form_alias' => $form_alias,
                            'form_details' => maybe_serialize($form_data['form_details']),
                            'form_status' => $form_status,
                                ), array('form_id' => $form_id), array('%s', '%s', '%s', '%d'), array('%d')
                        );

                        $response['status'] = 200;
                        $response['message'] = esc_html__('Form updated successfully.', 'frontend-post-submission-manager-lite');
                    } else {
                        $response['status'] = 403;
                        $response['message'] = esc_html__('Form alias already used. Please use some other alias.', 'frontend-post-submission-manager-lite');
                    }
                }
                die(json_encode($response));
            } else {
                $this->permission_denied();
            }
        }

        /**
         * Save global settings
         *
         * @since 1.0.0
         */
        function save_global_settings() {
            if ($this->admin_ajax_nonce_verify()) {
                global $fpsml_library_obj;
                $form_data = stripslashes_deep($_POST['form_data']);
                parse_str($form_data, $form_data);
                $form_data = $fpsml_library_obj->sanitize_array($form_data);
                $fpsml_settings = $form_data['fpsml_settings'];
                update_option('fpsml_settings', $fpsml_settings);
                $response['status'] = 200;
                $response['message'] = esc_html__('Settings saved successfully', 'frontend-post-submission-manager-lite');
                die(json_encode($response));
            } else {
                $this->permission_denied();
            }
        }

    }

    new FPSML_Ajax_Admin();
}