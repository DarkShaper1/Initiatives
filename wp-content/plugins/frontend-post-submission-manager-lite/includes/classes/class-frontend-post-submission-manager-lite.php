<?php

defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('Frontend_Post_Submission_Manager_Lite')) {

    /**
     * Plugin Main Class
     *
     * @since 1.0.0
     */
    class Frontend_Post_Submission_Manager_Lite {

        /**
         * Plugin's current version.
         *
         * @var string
         */
        public $version = '1.0.6';

        /**
         * The single instance of the class.
         *
         * @since 1.0.0
         */
        protected static $_instance = null;

        /**
         * Main FPSM Instance.
         *
         * Ensures only one instance of FPSM is loaded or can be loaded.
         *
         * @since 1.0.0
         * @static
         * @return Frontend_Post_Submission_Manager_Lite - Main instance.
         */
        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Throw error on object clone.
         *
         * The whole idea of the singleton design pattern is that there is a single
         * object therefore, we don't want the object to be cloned.
         *
         * @since 1.0.0
         * @access protected
         * @return void
         */
        public function __clone() {
            // Cloning instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, esc_html__('No script kiddies please!!', 'frontend-post-submission-manager-lite'), '1.6');
        }

        /**
         * Disable unserializing of the class.
         *
         * @since 1.0.0
         * @access protected
         * @return void
         */
        public function __wakeup() {
            // Unserializing instances of the class is forbidden.
            _doing_it_wrong(__FUNCTION__, esc_html__('No script kiddies please!!', 'frontend-post-submission-manager-lite'), '1.6');
        }

        /**
         * Returns true if the request is a non-legacy REST API request.
         *
         * Legacy REST requests should still run some extra code for backwards compatibility.
         *
         * @todo: replace this function once core WP function is available: https://core.trac.wordpress.org/ticket/42061.
         *
         * @return bool
         */
        public function is_rest_api_request() {
            if (empty($_SERVER['REQUEST_URI'])) {
                return false;
            }

            $rest_prefix = trailingslashit(rest_get_url_prefix());
            $is_rest_api_request = ( false !== strpos($_SERVER['REQUEST_URI'], $rest_prefix) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            return $is_rest_api_request;
        }

        /**
         * What type of request is this?
         *
         * @param  string $type admin, ajax, cron or frontend.
         * @return bool
         */
        private function is_request($type) {
            switch ($type) {
                case 'admin':
                    return is_admin();
                case 'ajax':
                    return defined('DOING_AJAX');
                case 'cron':
                    return defined('DOING_CRON');
                case 'frontend':
                    return (!is_admin() || defined('DOING_AJAX') ) && !defined('DOING_CRON') && !$this->is_rest_api_request();
            }
        }

        /**
         * Plugin's initialization constructor
         *
         * @since 1.0.0
         */
        function __construct() {
            $this->define_constants();
            $this->includes();
        }

        function define_constants() {
            global $wpdb;
            defined('FPSML_VERSION') or define('FPSML_VERSION', $this->version);
            defined('FPSML_FORM_TABLE') or define('FPSML_FORM_TABLE', $wpdb->prefix . 'fpsm_forms');
            $custom_field_type_list = array(
                'textfield' => array('label' => esc_html__('Texfield', 'frontend-post-submission-manager-lite'), 'icon' => 'fas fa-edit'),
                'textarea' => array('label' => esc_html__('Textarea', 'frontend-post-submission-manager-lite'), 'icon' => 'fas fa-expand'),
            );
            /**
             * Filters custom field type list
             *
             * @param array $custom_field_type_list
             *
             * @since 1.0.0
             */
            $custom_field_type_list = apply_filters('fpsml_custom_field_type_list', $custom_field_type_list);
            defined('FPSML_CUSTOM_FIELD_TYPE_LIST') or define('FPSML_CUSTOM_FIELD_TYPE_LIST', $custom_field_type_list);
        }

        function includes() {
            include(FPSML_PATH . '/includes/classes/class-fpsml-init.php');
            include(FPSML_PATH . '/includes/classes/class-fpsml-library.php');
            include(FPSML_PATH . '/includes/classes/class-fpsml-shortcode.php');
            include(FPSML_PATH . '/includes/classes/class-fpsml-fileuploader.php');
            include(FPSML_PATH . '/includes/classes/class-fpsml-ajax.php');
            include(FPSML_PATH . '/includes/classes/class-fpsml-frontend-hooks.php');
            include(FPSML_PATH . '/includes/classes/class-fpsml-notification.php');


            //include all the admin related classes
            if ($this->is_request('admin')) {
                include(FPSML_PATH . '/includes/classes/admin/class-fpsml-activation.php');
                include(FPSML_PATH . '/includes/classes/admin/class-fpsml-admin-enqueue.php');
                include(FPSML_PATH . '/includes/classes/admin/class-fpsml-admin.php');
                include(FPSML_PATH . '/includes/classes/admin/class-fpsml-ajax-admin.php');
                include(FPSML_PATH . '/includes/classes/admin/class-fpsml-metabox.php');
            }
        }

    }

}