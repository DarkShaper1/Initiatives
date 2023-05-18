<?php

defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('FPSML_Metabox')) {

    class FPSML_Metabox {

        function __construct() {
            add_action('add_meta_boxes', array($this, 'register_fpsml_metabox'));
            add_action('save_post', array($this, 'save_fpsml_metabox'), 10, 2);
        }

        function register_fpsml_metabox() {
            add_meta_box('fpsml-metabox', esc_html__('Frontend Post Submission Manager'), array($this, 'render_fpsml_metabox'));
        }

        function render_fpsml_metabox($post) {
            include(FPSML_PATH . '/includes/cores/fpsml-metabox-render.php');
        }

        function save_fpsml_metabox($post_id, $post) {
            if (empty($_POST['fpsml_metabox_nonce_field']) || empty($_POST['fpsml_custom_fields'])) {
                return;
            }
            if (!wp_verify_nonce($_POST['fpsml_metabox_nonce_field'], 'fpsml_metabox_nonce')) {
                return;
            }
            // Check if user has permissions to save data.
            if (!current_user_can('edit_post', $post_id)) {
                return;
            }

            // Check if not an autosave.
            if (wp_is_post_autosave($post_id)) {
                return;
            }

            // Check if not a revision.
            if (wp_is_post_revision($post_id)) {
                return;
            }
            global $fpsml_library_obj;
            $fpsml_custom_fields = $fpsml_library_obj->sanitize_array($_POST['fpsml_custom_fields']);
            $fpsml_included_custom_fields = $fpsml_library_obj->sanitize_array($_POST['fpsml_included_custom_fields']);
            foreach ($fpsml_included_custom_fields as $custom_field_key) {
                $custom_field_value = (isset($fpsml_custom_fields[$custom_field_key])) ? $fpsml_custom_fields[$custom_field_key] : '';
                update_post_meta($post_id, $custom_field_key, $custom_field_value);
            }
        }

    }

    new FPSML_Metabox();
}
