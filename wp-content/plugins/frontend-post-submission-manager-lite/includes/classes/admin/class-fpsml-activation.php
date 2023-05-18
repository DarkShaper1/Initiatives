<?php

defined('ABSPATH') or die('No script kiddies please!!');
if (!class_exists('FPSML_Activation')) {

    class FPSML_Activation {

        function __construct() {
            //All the activation related tasks are initialized here

            register_activation_hook(FPSML_PATH . '/frontend-post-submission-manager-lite.php', array($this, 'activation_tasks'));
        }

        function activation_tasks() {
            $this->create_tables();
        }

        function create_tables() {
            /**
             * Necessary Table Creation on activation
             */
            if (is_multisite()) {
                global $wpdb;
                $current_blog = $wpdb->blogid;

                // Get all blogs in the network and activate plugin on each one
                $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                foreach ($blog_ids as $blog_id) {
                    switch_to_blog($blog_id);

                    $charset_collate = $wpdb->get_charset_collate();
                    $form_table = $wpdb->prefix . 'fpsm_forms';
                    $form_table_sql = "CREATE TABLE $form_table (
						form_id mediumint(9) NOT NULL AUTO_INCREMENT,
						form_title varchar(255),
						form_alias varchar(255),
						form_details longtext,
                                                post_type varchar(255),
                                                form_type varchar(255),
						form_status mediumint(9) NOT NULL DEFAULT 1,
						PRIMARY KEY form_id (form_id)
					  ) $charset_collate;";

                    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                    dbDelta($form_table_sql);
                    $row_count = $wpdb->get_var("SELECT count(*) from $form_table");
                    // var_dump($row_count);
                    // die();
                    if ($row_count == 0) {
                        $this->insert_default_forms($form_table);
                    }

                    restore_current_blog();
                }
            } else {
                global $wpdb;

                $charset_collate = $wpdb->get_charset_collate();
                $form_table = FPSML_FORM_TABLE;
                $form_table_sql = "CREATE TABLE $form_table (
						form_id mediumint(9) NOT NULL AUTO_INCREMENT,
						form_title varchar(255),
						form_alias varchar(255),
						form_details longtext,
                                                post_type varchar(255),
                                                form_type varchar(255),
						form_status mediumint(9) NOT NULL DEFAULT 1,
						PRIMARY KEY form_id (form_id)
					  ) $charset_collate;";
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta($form_table_sql);
                $row_count = $wpdb->get_var("SELECT count(*) from $form_table");
                if ($row_count == 0) {
                    $this->insert_default_forms($form_table);
                }
            }
        }

        function insert_default_forms($form_table) {
            global $wpdb;
            global $fpsml_library_obj;

            $form_status = 1;
            $post_type = 'post';

            /**
             * Login require default form
             */
            $form_title = esc_html__('Login Require Form', 'frontend-post-submission-manager-lite');
            $form_alias = 'login_require_form';
            $form_type = 'login_require';
            $form_details = $fpsml_library_obj->get_default_form_details($post_type, $form_type);

            $insert_check = $wpdb->insert($form_table, array('form_title' => $form_title,
                'form_alias' => $form_alias,
                'form_details' => maybe_serialize($form_details),
                'form_status' => $form_status,
                'form_type' => $form_type,
                'post_type' => $post_type
                    ), array('%s', '%s', '%s', '%d', '%s', '%s')
            );
            /**
             * Guest post default form
             */
            $form_title = esc_html__('Guest Post Form', 'frontend-post-submission-manager-lite');
            $form_alias = 'guest_post_form';
            $form_type = 'guest';
            $form_details = $fpsml_library_obj->get_default_form_details($post_type, $form_type);
            $insert_check = $wpdb->insert($form_table, array('form_title' => $form_title,
                'form_alias' => $form_alias,
                'form_details' => maybe_serialize($form_details),
                'form_status' => $form_status,
                'form_type' => $form_type,
                'post_type' => $post_type
                    ), array('%s', '%s', '%s', '%d', '%s', '%s')
            );
        }

    }

    new FPSML_Activation();
}