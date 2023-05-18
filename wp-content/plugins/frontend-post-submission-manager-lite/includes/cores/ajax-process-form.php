<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!!' );
if ( $this->admin_ajax_nonce_verify() ) {
    $form_data = $_POST['form_data']; // Sanitization is done in line number 9 using a function to sanitize multidimensional array
    $form_data = stripslashes_deep( $form_data );
    parse_str( $form_data, $form_data );
    global $fpsml_library_obj;
    $form_data = $fpsml_library_obj->sanitize_array( $form_data, array( 'post_content' => 'html' ) );
    $form_alias = $form_data['form_alias'];
    $form_row = $fpsml_library_obj->get_form_row_by_alias( $form_alias );
    if ( empty( $form_row ) ) {
        die( esc_html__( 'No form found for this alias.', 'frontend-post-submission-manager-lite' ) );
    }
    $form_details = maybe_unserialize( $form_row->form_details );
    $form_fields = $form_details['form']['fields'];
    $error_flag = 0;
    $error_details = array();
    $response = array();
    if ( !empty( $form_fields ) ) {
        $taxonomy_lists = array();
        $custom_field_lists = array();
        foreach ( $form_fields as $field_key => $field_details ) {
            if ( $fpsml_library_obj->is_taxonomy_key( $field_key ) ) {
                $taxonomy_lists[] = $field_key;
            }
            // if field is enabled in backend
            if ( !empty( $field_details['show_on_form'] ) ) {
                $required_message = (!empty( $field_details['required_error_message'] )) ? esc_html__( $field_details['required_error_message'] ) : esc_html__( 'This field is requied', 'frontend-post-submission-manager-lite' );
                // if the field is required
                if ( !empty( $field_details['required'] ) && empty( $form_data[$field_key] ) ) {
                    $error_flag = 1;
                    $error_details[$field_key] = $required_message;
                } else {
                    // Other validations are done here
                    $field_recog_key = $field_key;
                    if ( $fpsml_library_obj->is_custom_field_key( $field_key ) ) {
                        $field_recog_key = 'custom_field';
                    }
                    switch( $field_recog_key ) {
                        case 'post_title':
                        case 'post_content':
                        case 'post_excerpt':
                        case 'author_name':
                        case 'author_email':
                            if ( !empty( $field_details['character_limit'] ) ) {
                                $field_value_length = strlen( sanitize_text_field( $form_data[$field_key] ) );
                                if ( $field_value_length > $field_details['character_limit'] ) {
                                    $character_limit_error_message = (!empty( $field_details['character_limit_error_message'] )) ? esc_html__( $field_details['character_limit_error_message'] ) : esc_html__( sprintf( 'Max characters allowed is %d', $field_details['character_limit'] ), 'frontend-post-submission-manager-lite' );
                                    $error_flag = 1;
                                    $error_details[$field_key] = $character_limit_error_message;
                                }
                            }
                            break;
                        case 'custom_field':
                            if ( !empty( $field_details['character_limit'] ) ) {
                                $field_value_length = strlen( sanitize_text_field( $form_data[$field_key] ) );
                                if ( $field_value_length > $field_details['character_limit'] ) {
                                    $character_limit_error_message = (!empty( $field_details['character_limit_error_message'] )) ? esc_html__( $field_details['character_limit_error_message'] ) : esc_html__( sprintf( 'Max characters allowed is %d', $field_details['character_limit'] ), 'frontend-post-submission-manager-lite' );
                                    $error_flag = 1;
                                    $error_details[$field_key] = $character_limit_error_message;
                                } else {
                                    $custom_field_lists[] = $field_key;
                                }
                            } else {
                                $custom_field_lists[] = $field_key;
                            }
                            break;
                    }
                }
            }
        }

        if ( !empty( $form_details['security']['frontend_form_captcha'] ) ) {
            $captcha = sanitize_text_field( $form_data['g-recaptcha-response'] ); // get the captchaResponse parameter sent from our ajax
            $required = esc_html__( 'This field is required', 'frontend-post-submission-manager-lite' );
            if ( empty( $captcha ) ) {
                $error_details['captcha'] = (!empty( $form_details['security']['error_message'] )) ? esc_attr( $form_details['security']['error_message'] ) : $required_message;
                $error_flag = 1;
            } else {

                $secret_key = (!empty( $form_details['security']['secret_key'] )) ? esc_attr( $form_details['security']['secret_key'] ) : '';
                $captcha_response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=" . $secret_key . "&response=" . $captcha );

                if ( is_wp_error( $captcha_response ) ) {
                    $error_details['security'] = esc_html__( 'Captcha Validation failed.', 'frontend-post-submission-manager-lite' );
                    $error_flag = 1;
                } else {
                    $captcha_response = json_decode( $captcha_response['body'] );
                    if ( $captcha_response->success == false ) {
                        $error_details['security'] = (!empty( $form_details['security']['error_message'] )) ? esc_attr( $form_details['security']['error_message'] ) : $required_message;
                        $error_flag = 1;
                    }
                }
            }
        }

        if ( $error_flag == 1 ) {
            $response['status'] = 403;
            $response['error_details'] = $error_details;
            $response['message'] = (!empty( $form_details['basic']['validation_error_message'] )) ? esc_html( $form_details['basic']['validation_error_message'] ) : esc_html__( 'Form validation error occurred.', 'frontend-post-submission-manager-lite' );
        } else {
            //Lets process the form
            $post_id = (!empty( $form_data['post_id'] )) ? intval( $form_data['post_id'] ) : 0;
            $post_title = (!empty( $form_data['post_title'] )) ? $form_data['post_title'] : '';
            $post_content = (!empty( $form_data['post_content'] )) ? $form_data['post_content'] : '';
            $post_type = $form_row->post_type;
            $post_excerpt = (!empty( $form_data['post_excerpt'] )) ? $form_data['post_excerpt'] : '';
            $post_status = $form_details['basic']['post_status'];
            if ( $form_row->form_type == 'login_require' ) {
                //if the form is login require form and user is logged in
                if ( is_user_logged_in() ) {
                    $post_author_id = get_current_user_id();
                } else {
                    // if  the form is login require form but users are not logged in
                    $response['status'] = 403;
                    $response['message'] = esc_html__( 'Invalid form submission', 'frontend-post-submission-manager-lite' );
                    die( json_encode( $response ) );
                }
            } else {
                $post_author_id = intval( $form_details['basic']['post_author'] );
            }
            //Lets check the post status of the post for edited post
            $post_status = (!empty( $post_id )) ? get_post_status( $post_id ) : $post_status;
            // Lets insert post into DB
            $postarr = array(
                'ID' => $post_id,
                'post_author' => $post_author_id,
                'post_content' => $post_content,
                'post_title' => $post_title,
                'post_excerpt' => $post_excerpt,
                'post_status' => $post_status,
                'post_type' => $post_type
            );
            /**
             * Filters the post array before inserting the post into db
             *
             * @param array $postarr
             * @param array $form_data
             * @param obj $form_row
             *
             * @since 1.0.0
             */
            $postarr = apply_filters( 'fpsml_insert_postdata', $postarr, $form_data, $form_row );
            $insert_update_post_id = wp_insert_post( $postarr );
            if ( !empty( $insert_update_post_id ) ) {


                //Lets assign the post image to the post
                if ( isset( $form_data['post_image'] ) ) {
                    if ( !empty( $post_id ) && empty( $form_data['post_image'] ) ) {
                        delete_post_thumbnail( $post_id );
                    } else {
                        set_post_thumbnail( $insert_update_post_id, intval( $form_data['post_image'] ) );
                    }
                }

                //Lets assign post format
                if ( !empty( $form_details['basic']['post_format'] ) ) {
                    set_post_format( $insert_update_post_id, $form_details['basic']['post_format'] );
                }

                // Lets assign taxonomy terms
                if ( !empty( $taxonomy_lists ) ) {

                    foreach ( $taxonomy_lists as $taxonomy_key ) {
                        $taxonomy_settings = $form_details['form']['fields'][$taxonomy_key];
                        // If taxonomy is enabled in the form
                        $taxonomy_array = explode( '|', $taxonomy_key );
                        $taxonomy_name = end( $taxonomy_array );
                        if ( !empty( $taxonomy_settings['show_on_form'] ) ) {
                            $form_data[$taxonomy_key] = (!empty( $form_data[$taxonomy_key] )) ? $form_data[$taxonomy_key] : '';
                            if ( is_array( $form_data[$taxonomy_key] ) ) {
                                $post_assign_terms = implode( ',', $form_data[$taxonomy_key] );
                            } else {
                                $post_assign_terms = $form_data[$taxonomy_key];
                            }
                            wp_set_post_terms( $insert_update_post_id, $post_assign_terms, $taxonomy_name );
                        }

                        // If explicit auto assign of the terms is enabled
                        if ( !empty( $taxonomy_settings['auto_assign'] ) ) {
                            $auto_assign_terms = implode( ',', $taxonomy_settings['auto_assign'] );
                            wp_set_post_terms( $insert_update_post_id, $auto_assign_terms, $taxonomy_name, true );
                        }
                    }
                }
                if ( !empty( $form_data['author_email'] ) && !empty( $form_details['form']['fields']['author_email']['show_on_form'] ) ) {
                    update_post_meta( $insert_update_post_id, 'fpsml_author_email', $form_data['author_email'] );
                }
                if ( !empty( $form_data['author_name'] ) && !empty( $form_details['form']['fields']['author_name']['show_on_form'] ) ) {
                    update_post_meta( $insert_update_post_id, 'fpsml_author_name', $form_data['author_name'] );
                }
                //Lets work on custom fields here
                if ( !empty( $custom_field_lists ) ) {
                    foreach ( $custom_field_lists as $custom_field_key ) {
                        $custom_field_value = (!empty( $form_data[$custom_field_key] )) ? $form_data[$custom_field_key] : '';
                        $custom_field_settings = $form_details['form']['fields'][$custom_field_key];
                        $custom_field_array = explode( '|', $custom_field_key );
                        $custom_field_meta_key = end( $custom_field_array );
                        $custom_field_type = $custom_field_settings['field_type'];
                        if ( $custom_field_type == 'datepicker' && !empty( $custom_field_settings['string_format'] ) ) {
                            $custom_field_value = strtotime( $custom_field_value );
                        }
                        /**
                         * Filters the custom field value before storing it in the database
                         *
                         * @param mixed $custom_field_value
                         * @param string $custom_field_key
                         * @param obj $form_row
                         *
                         * @since 1.0.0
                         */
                        $custom_field_value = apply_filters( 'fpsml_custom_field_value', $custom_field_value, $custom_field_key, $form_row );
                        update_post_meta( $insert_update_post_id, $custom_field_meta_key, $custom_field_value );
                    }
                }
                // Storing form alias for the reference
                update_post_meta( $insert_update_post_id, '_fpsml_form_alias', $form_alias );
                $response['status'] = 200;
                $response['message'] = (!empty( $form_details['basic']['form_success_message'] )) ? esc_html( $form_details['basic']['form_success_message'] ) : esc_html__( 'Form submission successful.', 'frontend-post-submission-manager-lite' );
                // If redirection is enabled for post submission
                if ( empty( $post_id ) ) {
                    if ( !empty( $form_details['basic']['redirection'] ) ) {
                        if ( $form_details['basic']['redirection_type'] == 'url' ) {
                            if ( !empty( $form_details['basic']['redirection_url'] ) ) {
                                $response['redirect_url'] = esc_url( $form_details['basic']['redirection_url'] );
                            }
                        } else {
                            $post_url = get_the_permalink( $insert_update_post_id );
                            $response['redirect_url'] = $post_url;
                        }
                    }
                } else {
                    if ( !empty( $form_details['basic']['edit_redirection'] ) ) {
                        if ( !empty( $form_details['basic']['edit_redirection_url'] ) ) {
                            $response['redirect_url'] = esc_url( $form_details['basic']['edit_redirection_url'] );
                        }
                    }
                }
                $action = (empty( $post_id )) ? 'insert' : 'update';
                /**
                 * Fires when the successful form submission is complete
                 *
                 * @param int $insert_update_post_id
                 * @param array $form_row
                 * @param string $action
                 */
                do_action( 'fpsml_form_submission_success', $insert_update_post_id, $form_row, $action );
            } else {
                $response['status'] = 403;
                $response['message'] = esc_html__( 'There occurred some error.', 'frontend-post-submission-manager-lite' );
            }
        }
    } else {
        $response['status'] = 403;
        $response['message'] = esc_html__( 'Invalid form submission', 'frontend-post-submission-manager-lite' );
    }
    /**
     * Filters the form process response array
     *
     * @param array $response
     * @param array $form_data
     * @param obj $form_row
     *
     * @since 1.0.0
     */
    $response = apply_filters( 'fpsml_form_response', $response, $form_data, $form_row );
    echo json_encode( $response );
    die();
} else {
    $this->permission_denied();
}