<?php

defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('FPSML_Frontend_Hooks')) {

    class FPSML_Frontend_Hooks {

        function __construct() {
            add_action('wp_footer', array($this, 'append_extra_html'));
            add_action('the_content', array($this, 'append_custom_fields_before'), 10);
            add_action('the_content', array($this, 'append_custom_fields_after'), 11);
            add_action('template_redirect', array($this, 'generate_form_preview'));
            add_filter('body_class', array($this, 'add_preview_class'));
        }

        function append_extra_html() {
            include(FPSML_PATH . '/includes/views/frontend/wp_footer.php');
        }

        function register_frontend_assets() {

            wp_enqueue_style('fpsml-style', FPSML_URL . '/assets/css/fpsml-frontend-style.css', array(), FPSML_VERSION);
            if (is_rtl()) {
                wp_enqueue_style('fpsml-rtl-style', FPSML_URL . '/assets/css/fpsml-rtl-frontend-style.css', array(), FPSML_VERSION);
            }

            wp_enqueue_style('fpsml-fonts', FPSML_URL . '/assets/font-face/NunitoSans/stylesheet.css', array(), FPSML_VERSION);
            wp_enqueue_style('fpsml-fonts', FPSML_URL . '/assets/font-face/comingsoon/stylesheet.css', array(), FPSML_VERSION);
        }

        function append_custom_fields_before($content) {
            $this->register_frontend_assets();
            $append_content = '';
            $display_position_check = 'before_content';
            include(FPSML_PATH . '/includes/cores/post-content-append.php');
            $content = $append_content . $content;
            return $content;
        }

        function append_custom_fields_after($content) {
            $this->register_frontend_assets();
            $append_content = '';
            $display_position_check = 'after_content';
            include(FPSML_PATH . '/includes/cores/post-content-append.php');
            $content = $content . $append_content;
            return $content;
        }

        function generate_form_preview() {
            if (is_user_logged_in() && !empty($_GET['fpsml_form_preview']) && !empty($_GET['fpsml_form_alias']) && wp_verify_nonce($_GET['_wpnonce'], 'fpsml_preview_nonce')) {
                include(FPSML_PATH . '/includes/views/frontend/frontend-preview-template.php');
                die();
            }
        }

        function add_preview_class($classes) {
            if (isset($_GET['fpsml_form_preview'])) {
                $classes[] = 'fpsml-preview-page';
            }
            return $classes;
        }

    }

    new FPSML_Frontend_Hooks();
}
