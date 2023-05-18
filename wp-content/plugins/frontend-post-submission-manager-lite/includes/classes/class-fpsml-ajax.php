<?php

defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('FPSML_Ajax')) {

    class FPSML_Ajax {

        function __construct() {
            /**
             * Custom Media Upload
             */
            add_action('wp_ajax_fpsml_file_upload_action', array($this, 'file_upload_action'));
            add_action('wp_ajax_nopriv_fpsml_file_upload_action', array($this, 'file_upload_action'));

            /**
             * Custom Media Delete
             */
            add_action('wp_ajax_fpsml_media_delete_action', array($this, 'media_delete_action'));
            add_action('wp_ajax_nopriv_fpsml_media_delete_action', array($this, 'media_delete_action'));

            /**
             *  Ajax Form Submission
             */
            add_action('wp_ajax_fpsml_form_process', array($this, 'ajax_form_process'));
            add_action('wp_ajax_nopriv_fpsml_form_process', array($this, 'ajax_form_process'));
        }

        function file_upload_action() {
            if ($this->admin_ajax_nonce_verify()) {

                $form_alias = sanitize_text_field($_GET['form_alias']);
                $field_name = sanitize_text_field($_GET['field_name']);
                global $fpsml_library_obj;
                $form_row = $fpsml_library_obj->get_form_row_by_alias($form_alias);
                $form_details = maybe_unserialize($form_row->form_details);
                $field_details = $form_details['form']['fields'][$field_name];
                $default_allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'JPG', 'JPEG', 'PNG', 'BMP');
                /**
                 * Filters allowed extensions for image field type
                 *
                 * @param array $allowed_extensions
                 *
                 * @since 1.0.0
                 */
                $default_allowed_extensions = apply_filters('fpsml_image_allowed_extensions', $default_allowed_extensions);
                if ($field_name == 'post_image') {
                    $allowed_extensions = $default_allowed_extensions;
                } else {
                    $allowed_extensions = $field_details['file_extensions'];
                    if (!empty($allowed_extensions)) {
                        $allowed_extensions = implode('|', $allowed_extensions);
                        $allowed_extensions = explode('|', $allowed_extensions);
                    } else {
                        $allowed_extensions = $default_allowed_extensions;
                    }
                }
                $upload_file_size_limit = (!empty($field_details['upload_file_size_limit'])) ? $field_details['upload_file_size_limit'] * 1000 * 1000 : 5 * 1000 * 1000;
                $uploader = new FPSML_qqFileUploaders($allowed_extensions, $upload_file_size_limit);
                $upload_dir = wp_upload_dir();

                $upload_path = $upload_dir['path'] . '/';
                $upload_url = $upload_dir['url'];

                $result = $uploader->handleUpload($upload_path, $replaceOldFile = false, $upload_url);

                echo json_encode($result);
                die();
            } else {
                $this->permission_denied();
            }
        }

        /**
         * Ajax nonce verification for ajax in admin
         *
         * @return bolean
         * @since 1.0.0
         */
        function admin_ajax_nonce_verify() {
            if (!empty($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'fpsml_ajax_nonce')) {
                return true;
            } else {
                return false;
            }
        }

        function permission_denied() {
            die('No script kiddies please!!');
        }

        function media_delete_action() {
            if ($this->admin_ajax_nonce_verify()) {
                $media_id = intval($_POST['media_id']);
                $media_key = sanitize_text_field($_POST['media_key']);
                $attachment_date = get_the_date("U", $media_id);
                $attachment_code = md5($attachment_date);
                if ($media_key != $attachment_code) {
                    $response['status'] = 403;
                    $response['messsage'] = esc_html__('Unauthorized access', 'frontend-post-submission-manager-lite');
                } else {
                    $media_delete_check = wp_delete_attachment($media_id, true);
                    if ($media_delete_check) {
                        $response['status'] = 200;
                        $response['messsage'] = esc_html__('Media deleted successfully.', 'frontend-post-submission-manager-lite');
                    } else {
                        $response['status'] = 403;
                        $response['messsage'] = esc_html__('Error occurred while deleting the media.', 'frontend-post-submission-manager-lite');
                    }
                }
                die(json_encode($response));
            } else {
                $this->permission_denied();
            }
        }

        function ajax_form_process() {
            include(FPSML_PATH . '/includes/cores/ajax-process-form.php');
        }

        /**
         * Process post delete
         */
        function process_post_delete() {
            if ($this->admin_ajax_nonce_verify()) {
                $post_id = intval($_POST['post_id']);
                $delete_key = sanitize_text_field($_POST['delete_key']);
                $verify_delete_key = md5(get_the_date('d-m-y H:i a', $post_id));
                if ($delete_key != $verify_delete_key) {
                    $response['status'] = 403;
                    $response['message'] = esc_html__('Unauthorized delete from delete key', 'frontend-post-submission-manager-lite');
                } else {
                    $current_user_id = get_current_user_id();

                    $post_author_user_id = get_post_field('post_author', $post_id);

                    if ($current_user_id != $post_author_user_id) {
                        $response['status'] = 403;
                        $response['message'] = esc_html__('Unauthorized delete from user', 'frontend-post-submission-manager-lite');
                    } else {
                        $delete_check = wp_trash_post($post_id);
                        if ($delete_check) {
                            $response['status'] = 200;
                            $response['message'] = esc_html__('Post delete successfully.', 'frontend-post-submission-manager-lite');
                        } else {
                            $response['status'] = 403;
                            $response['message'] = esc_html__('There occurred some error.', 'frontend-post-submission-manager-lite');
                        }
                    }
                }
                die(json_encode($response));
            } else {
                $this->permission_denied();
            }
        }

    }

    new FPSML_Ajax();
}