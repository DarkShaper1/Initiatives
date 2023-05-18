<?php

defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('FPSML_Notification')) {

    class FPSML_Notification {

        function __construct() {
            add_action('fpsml_form_submission_success', array($this, 'trigger_admin_notification'), 10, 3);
            add_action('wp_trash_post', array($this, 'trigger_post_reject_notifications'));
            //add_action('init', array($this, 'post_publish_notification_helper'));
            add_action('transition_post_status', array($this, 'trigger_post_publish_notification'), 10, 3);
        }

        function trigger_admin_notification($insert_update_post_id, $form_row, $action) {
            if ($action == 'insert') {
                $form_details = maybe_unserialize($form_row->form_details);
                include(FPSML_PATH . '/includes/cores/admin-email-notification.php');
            }
        }

        function trigger_post_reject_notifications($post_id) {
            if (!is_admin()) {
                return;
            }
            if (defined('DOING_AJAX')) {
                return;
            }
            $form_alias = get_post_meta($post_id, '_fpsml_form_alias', true);
            if (empty($form_alias)) {
                return;
            }

            global $fpsml_library_obj;
            $form_row = $fpsml_library_obj->get_form_row_by_alias($form_alias);
            $form_details = maybe_unserialize($form_row->form_details);
            if (empty($form_details['notification']['post_trash']['enable'])) {
                return;
            }

            if ($form_row->form_type == 'login_require') {
                $post_author_id = get_post_field('post_author', $post_id);
                $notification_email = get_the_author_meta('user_email', $post_author_id);
                $author_name = get_the_author_meta('display_name', $post_author_id);
            } else {
                $notification_email = get_post_meta($post_id, 'fpsml_author_email', true);
                if (empty($notification_email)) {
                    return;
                }
                $author_name = get_post_meta($post_id, 'fpsml_author_name', true);
            }
            $from_name = (!empty($form_details['notification']['post_trash']['from_name'])) ? $form_details['notification']['post_trash']['from_name'] : esc_html__('No Reply', 'frontend-post-submission-manager-lite');
            $from_email = (!empty($form_details['notification']['post_trash']['from_email'])) ? $form_details['notification']['post_trash']['from_email'] : $fpsml_library_obj->default_from_email();
            $subject = (!empty($form_details['notification']['post_trash']['subject'])) ? $form_details['notification']['post_trash']['subject'] : $fpsml_library_obj->default_from_email();
            $subject = str_replace('[post_title]', get_the_title($post_id), $subject);
            $subject = str_replace('[author_name]', $author_name, $subject);
            $notification_message = (!empty($form_details['notification']['post_trash']['notification_message'])) ? $form_details['notification']['post_trash']['notification_message'] : $fpsml_library_obj->sanitize_escaping_linebreaks($fpsml_library_obj->default_trash_notification());
            $notification_message = str_replace('[post_title]', get_the_title($post_id), $notification_message);
            $notification_message = str_replace('[author_name]', $author_name, $notification_message);
            $headers = array();
            $charset = get_option('blog_charset');
            $headers[] = 'Content-Type: text/html; charset=' . $charset;
            $headers[] = "From: $from_name <$from_email>";
            wp_mail($notification_email, $subject, $notification_message, $headers);
        }

        function post_publish_notification_helper() {
            global $fpsml_library_obj;
            $post_types = $fpsml_library_obj->get_registered_post_types();
            foreach ($post_types as $post_type) {
                $publish_action = 'publish_' . $post_type->name;
                add_action($publish_action, array($this, 'trigger_post_publish_notification'), 10, 3);
            }
        }

        function trigger_post_publish_notification($new_status, $old_status, $post) {
            if (!(defined('REST_REQUEST') && REST_REQUEST )) {
                if (!('publish' === $new_status && 'publish' !== $old_status)) {
                    return;
                }
                $post_id = $post->ID;

                $form_alias = get_post_meta($post_id, '_fpsml_form_alias', true);
                if (empty($form_alias)) {
                    return;
                }
                global $fpsml_library_obj;
                $form_row = $fpsml_library_obj->get_form_row_by_alias($form_alias);
                if (empty($form_row->form_details)) {
                    return;
                }
                $form_details = maybe_unserialize($form_row->form_details);
                if (empty($form_details['notification']['post_publish']['enable'])) {
                    return;
                }
                if ($form_row->form_type == 'login_require') {
                    $post_author_id = get_post_field('post_author', $post_id);
                    $notification_email = get_the_author_meta('user_email', $post_author_id);
                    $author_name = get_the_author_meta('display_name', $post_author_id);
                } else {
                    $notification_email = get_post_meta($post_id, 'fpsml_author_email', true);
                    if (empty($notification_email)) {
                        return;
                    }
                    $author_name = get_post_meta($post_id, 'fpsml_author_name', true);
                }
                $post_link = get_permalink($post_id);
                $from_name = (!empty($form_details['notification']['post_publish']['from_name'])) ? $form_details['notification']['post_publish']['from_name'] : esc_html__('No Reply', 'frontend-post-submission-manager-lite');
                $from_email = (!empty($form_details['notification']['post_publish']['from_email'])) ? $form_details['notification']['post_publish']['from_email'] : $fpsml_library_obj->default_from_email();
                $subject = (!empty($form_details['notification']['post_publish']['subject'])) ? $form_details['notification']['post_publish']['subject'] : $fpsml_library_obj->default_from_email();
                $subject = str_replace('[post_title]', get_the_title($post_id), $subject);
                $subject = str_replace('[author_name]', $author_name, $subject);
                $notification_message = (!empty($form_details['notification']['post_publish']['notification_message'])) ? $form_details['notification']['post_publish']['notification_message'] : $fpsml_library_obj->sanitize_escaping_linebreaks($fpsml_library_obj->default_publish_notification());
                $notification_message = str_replace('[post_title]', get_the_title($post_id), $notification_message);
                $notification_message = str_replace('[author_name]', $author_name, $notification_message);
                $notification_message = str_replace('[post_link]', $post_link, $notification_message);
                $headers = array();
                $charset = get_option('blog_charset');
                $headers[] = 'Content-Type: text/html; charset=' . $charset;
                $headers[] = "From: $from_name <$from_email>";
                wp_mail($notification_email, $subject, $notification_message, $headers);
            }
        }

    }

    new FPSML_Notification();
}