<?php
defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('FPSML_Shortcode')) {

    class FPSML_Shortcode {

        function __construct() {
            add_shortcode('fpsm', array($this, 'output_form_shortcode'));
            add_action('wp_login_failed', array($this, 'login_failed'));
            add_filter('authenticate', array($this, 'verify_username_password'), 1, 3);
            add_action('login_form', array($this, 'login_extra_fields'));
            add_filter('login_form_middle', array($this, 'login_extra_fields'));
            add_filter('login_form_middle', array($this, 'login_google_captcha'));
            add_filter('authenticate', array($this, 'login_google_recaptcha_validation'), 10, 3);
        }

        function register_frontend_assets() {
            $fpsml_settings = get_option('fpsml_settings');

            $translation_strings = array(
                'are_your_sure' => esc_html__('It looks like you have been editing something. If you leave before saving, your changes will be lost.', 'frontend-post-submission-manager-lite'),
                'typeError' => esc_html__("{file} has invalid extension. Only {extensions} are allowed.", 'frontend-post-submission-manager-lite'),
                'sizeError' => esc_html__("{file} is too large, maximum file size is {sizeLimit}.", 'frontend-post-submission-manager-lite'),
                'minSizeError' => esc_html__("{file} is too small, minimum file size is {minSizeLimit}.", 'frontend-post-submission-manager-lite'),
                'emptyError' => esc_html__("{file} is empty, please select files again without it.", 'frontend-post-submission-manager-lite'),
                'onLeave' => esc_html__("The files are being uploaded, if you leave now the upload will be cancelled.", 'frontend-post-submission-manager-lite')
            );
            $js_obj = array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('fpsml_ajax_nonce'),
                'no_preview' => FPSML_URL . '/assets/images/no-preview.jpg',
                'translation_strings' => $translation_strings,
                'fpsml_settings' => $fpsml_settings
            );
            wp_enqueue_style('fpsml-style', FPSML_URL . '/assets/css/fpsml-frontend-style.css', array(), FPSML_VERSION);
            if (is_rtl()) {
                wp_enqueue_style('fpsml-rtl-style', FPSML_URL . '/assets/css/fpsml-rtl-frontend-style.css', array(), FPSML_VERSION);
            }
            if (!is_user_logged_in()) {
                wp_enqueue_style('fpsml-login-style', FPSML_URL . '/assets/css/fpsml-login-form-style.css', array(), FPSML_VERSION);
            }
            wp_enqueue_style('fpsml-fonts', FPSML_URL . '/assets/font-face/NunitoSans/stylesheet.css', array(), FPSML_VERSION);
            wp_enqueue_style('fpsml-fonts', FPSML_URL . '/assets/font-face/comingsoon/stylesheet.css', array(), FPSML_VERSION);
            if (empty($fpsml_settings['disable_jquery_ui_css'])) {
                wp_enqueue_style('jquery-ui', FPSML_URL . '/assets/css/jquery-ui.min.css', array(), FPSML_VERSION);
            }
            wp_enqueue_style('fpsml-fileuploader', FPSML_URL . '/assets/css/fileuploader.css', array(), FPSML_VERSION);
            if (empty($fpsml_settings['disable_fontawesome'])) {
                wp_enqueue_style('fontawesome', FPSML_URL . '/assets/fontawesome/css/all.min.css', array(), FPSML_VERSION);
            }
            $js_dependencies = array('jquery', 'fpsml-fileuploader', 'wp-util', 'jquery-ui-autocomplete', 'jquery-ui-datepicker');
            wp_enqueue_script('fpsml-fileuploader', FPSML_URL . '/assets/js/fpsml-fileuploader.js', array(), FPSML_VERSION);
            if (empty($fpsml_settings['disable_are_you_sure_js'])) {
                $js_dependencies[] = 'fpsml-are-you-sure-script';
                wp_enqueue_script('fpsml-are-you-sure-script', FPSML_URL . '/assets/js/jquery.are-you-sure.js', array('jquery'), FPSML_VERSION);
            }
            wp_enqueue_style('fpsml-custom-style', FPSML_URL . '/assets/css/fpsml-custom-style.css', array(), FPSML_VERSION);
            wp_enqueue_script('fpsml-script', FPSML_URL . '/assets/js/fpsml-frontend.js', $js_dependencies, FPSML_VERSION);
            wp_localize_script('fpsml-script', 'fpsml_js_obj', $js_obj);
        }

        function output_form_shortcode($atts) {
            if (!empty($atts['alias'])) {
                global $fpsml_library_obj;
                $alias = $atts['alias'];
                $form_row = $fpsml_library_obj->get_form_row_by_alias($alias);
                if (!empty($form_row)) {
                    $form_details = maybe_unserialize($form_row->form_details);
                    $this->register_frontend_assets();
                    $GLOBALS['fpsml_form_details'] = $form_details;
                    $GLOBALS['fpsml_form_alias'] = $alias;
                    ob_start();
                    include(FPSML_PATH . '/includes/views/frontend/form-shortcode.php');
                    $form_html = ob_get_contents();
                    ob_end_clean();

                    return $form_html;
                } else {
                    return esc_html__('Form not available for this alias.', 'frontend-post-submission-manager-lite');
                }
            }
        }

        function redirect_login_page() {
            if (isset($_POST['requested_page'])) {
                $login_page = esc_url($_POST['requested_page']);
                $page_viewed = basename($_SERVER['REQUEST_URI']);

                if ($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
                    wp_redirect($login_page);
                    exit;
                }
            }
        }

        function login_failed() {
            if (isset($_POST['requested_page'])) {
                $login_page = esc_url($_POST['requested_page']);
                wp_redirect($login_page . '?login=failed');
                exit;
            }
        }

        function verify_username_password($user, $username, $password) {
            if (isset($_POST['requested_page'])) {
                $login_page = esc_url($_POST['requested_page']);
                if ($username == "" || $password == "") {
                    wp_redirect($login_page . "?login=empty");
                    exit;
                } else {

                }
            }
        }

        function login_extra_fields($login_form_buttom_html) {
            if (!$this->is_login_page()) {
                global $fpsml_library_obj;
                $current_page_url = $fpsml_library_obj->get_current_page_url();
                $login_form_html = '<input type="hidden" name="requested_page" value="' . $current_page_url . '"/>';
                return $login_form_buttom_html . $login_form_html;
            } else {
                return $login_form_buttom_html;
            }
        }

        function is_login_page() {
            return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
        }

        function login_google_captcha($login_form_buttom_html) {
            /**
             * Don't add this in default login page
             */
            if (!$this->is_login_page()) {
                global $fpsml_form_details;
                global $fpsml_form_alias;
                if (!empty($fpsml_form_details['security']['login_form_captcha'])) {
                    $site_key = (!empty($fpsml_form_details['security']['site_key'])) ? esc_attr($fpsml_form_details['security']['site_key']) : '';
                    if (!empty($site_key)) {
                        ob_start();
                        ?>
                        <div class="fpsml-captcha-wrap">
                            <label><?php echo (!empty($fpsml_form_details['security']['captcha_label'])) ? esc_attr($fpsml_form_details['security']['captcha_label']) : ''; ?></label>
                            <div class="fpsml-field">
                                <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>"></div>
                            </div>
                        </div>
                        <input type="hidden" name="fpsml_login_check" value="yes"/>
                        <input type="hidden" name="fpsml_alias" value="<?php echo esc_attr($fpsml_form_alias); ?>"/>
                        <?php
                        $captcha_html = ob_get_contents();
                        ob_end_clean();
                        $login_form_buttom_html .= $captcha_html;
                    }
                }
            }
            return $login_form_buttom_html;
        }

        function login_google_recaptcha_validation($user, $username, $password) {

            if (!empty($_REQUEST['fpsml_login_check']) && !empty($_REQUEST['fpsml_alias'])) {
                global $fpsml_library_obj;
                $fpsml_alias = sanitize_text_field($_REQUEST['fpsml_alias']);
                $form_row = $fpsml_library_obj->get_form_row_by_alias($fpsml_alias);
                $form_details = maybe_unserialize($form_row->form_details);

                if (!empty($form_details['security']['login_form_captcha'])) {
                    $captcha = sanitize_text_field($_REQUEST['g-recaptcha-response']);

                    /* Check if captcha is filled */
                    if (empty($captcha)) {
                        wp_redirect(esc_url($_POST['redirect_to']) . '/?login=captcha_error');
                        exit;
                    } else {

                        $secret_key = (!empty($form_details['security']['secret_key'])) ? $form_details['security']['secret_key'] : '';
                        $captcha_response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=" . $secret_key . "&response=" . $captcha);
                        if (is_wp_error($captcha_response)) {
                            wp_redirect(esc_url($_POST['redirect_to']) . '/?login=captcha_error');
                            exit;
                        } else {
                            $captcha_response = json_decode($captcha_response['body']);
                            if ($captcha_response->success == false) {
                                wp_redirect(esc_url($_POST['redirect_to']) . '/?login=captcha_error');
                                exit;
                            }
                        }
                    }
                }
            }
        }

    }

    new FPSML_Shortcode();
}
