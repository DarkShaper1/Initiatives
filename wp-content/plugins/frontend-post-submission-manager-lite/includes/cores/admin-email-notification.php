<?php

defined('ABSPATH') or die('No script kiddies please!!');
global $fpsml_library_obj;
if (!empty($form_details['notification']['admin']['enable'])) {
    $from_name = (!empty($form_details['notification']['admin']['from_name'])) ? $form_details['notification']['admin']['from_name'] : esc_html__('No Reply', 'frontend-post-submission-manager-lite');
    $from_email = (!empty($form_details['notification']['admin']['from_email'])) ? $form_details['notification']['admin']['from_email'] : $fpsml_library_obj->default_from_email();
    $subject = (!empty($form_details['notification']['admin']['subject'])) ? $form_details['notification']['admin']['subject'] : $fpsml_library_obj->default_from_email();

    if ($form_row->form_type == 'login_require') {
        $post_author_id = get_post_field('post_author', $insert_update_post_id);
        $notification_email = get_the_author_meta('user_email', $post_author_id);
        $author_name = get_the_author_meta('display_name', $post_author_id);
    } else {
        $notification_email = get_post_meta($insert_update_post_id, 'fpsml_author_email', true);
        if (empty($notification_email)) {
            return;
        }
        $author_name = get_post_meta($insert_update_post_id, 'fpsml_author_name', true);
    }
    $subject = str_replace('[post_title]', get_the_title($insert_update_post_id), $subject);
    $subject = str_replace('[author_name]', $author_name, $subject);
    $notification_message = (!empty($form_details['notification']['admin']['notification_message'])) ? $form_details['notification']['admin']['notification_message'] : $fpsml_library_obj->sanitize_escaping_linebreaks($fpsml_library_obj->default_admin_notification());
    $notification_message = str_replace('[post_title]', get_the_title($insert_update_post_id), $notification_message);
    $post_edit_link = get_edit_post_link($insert_update_post_id);
    $notification_message = str_replace('[post_admin_link]', '<a href="' . $post_edit_link . '">' . $post_edit_link . '</a>', $notification_message);
    $admin_emails = (!empty($form_details['notification']['admin']['notification_emails'])) ? explode(',', $form_details['notification']['admin']['notification_emails']) : get_bloginfo('admin_email');
    $headers = array();
    $charset = get_option('blog_charset');
    $headers[] = 'Content-Type: text/html; charset=' . $charset;
    $headers[] = "From: $from_name <$from_email>";
    if (is_array($admin_emails)) {
        foreach ($admin_emails as $admin_email) {
            wp_mail($admin_email, $subject, $notification_message, $headers);
        }
    } else {
        wp_mail($admin_emails, $subject, $notification_message, $headers);
    }
}


