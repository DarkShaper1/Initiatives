<?php

if ( ! function_exists( 'wp_send_new_user_notifications' ) ) {
	function wp_send_new_user_notifications( $user_id, $notify = 'both' ) {
		wp_new_user_notification( $user_id, null, $notify );
	}

}
function rcl_insert_user( $data ) {

	if ( get_user_by( 'email', $data['user_email'] ) ) {
		return false;
	}

	if ( get_user_by( 'login', $data['user_login'] ) ) {
		return false;
	}

	$userdata = array_merge( $data, array(
		'user_nicename' => '',
		'nickname'      => $data['user_email'],
		'first_name'    => $data['display_name'],
		'rich_editing'  => 'true'  // false - выключить визуальный редактор для пользователя.
	) );

	$user_id = wp_insert_user( $userdata );

	if ( ! $user_id || is_wp_error( $user_id ) ) {
		return false;
	}

	$timeAction = '0000-00-00 00:00:00';

	if ( rcl_get_option( 'confirm_register_recall' ) ) {
		wp_update_user( array( 'ID' => $user_id, 'role' => 'need-confirm' ) );
	} else {
		$timeAction = current_time( 'mysql' );
	}

	global $wpdb;

	$wpdb->insert( RCL_PREF . 'user_action', array( 'user' => $user_id, 'time_action' => $timeAction ) );

	rcl_register_mail( array(
		'user_id'    => $user_id,
		'user_pass'  => $userdata['user_pass'],
		'user_login' => $userdata['user_login'],
		'user_email' => $userdata['user_email']
	) );

	wp_send_new_user_notifications( $user_id, 'admin' );

	do_action( 'rcl_insert_user', $user_id, $userdata );

	return $user_id;
}

//подтверждаем регистрацию пользователя по ссылке
function rcl_confirm_user_registration() {
	global $wpdb;

	if ( ! empty( $_GET['rcl-confirmdata'] ) ) {

		$confirmdata = urldecode( sanitize_text_field( wp_unslash( $_GET['rcl-confirmdata'] ) ) );

		$confirmdata = json_decode( base64_decode( $confirmdata ) );

		if ( $user = get_user_by( 'login', $confirmdata[0] ) ) {

			if ( md5( $user->ID ) != $confirmdata[1] ) {
				return false;
			}

			if ( ! rcl_is_user_role( $user->ID, 'need-confirm' ) ) {
				return false;
			}

			$defaultRole = get_site_option( 'default_role' );
			if ( $defaultRole == 'need-confirm' ) {
				update_site_option( 'default_role', 'author' );
				$defaultRole = 'author';
			}

			wp_update_user( array( 'ID' => $user->ID, 'role' => $defaultRole ) );

			if ( ! rcl_get_time_user_action( $user->ID ) ) {
				$wpdb->insert( RCL_PREF . 'user_action', array(
					'user'        => $user->ID,
					'time_action' => current_time( 'mysql' )
				) );
			}

			do_action( 'rcl_confirm_registration', $user->ID );

			if ( rcl_get_option( 'login_form_recall' ) == 2 ) {
				wp_safe_redirect( wp_login_url() . '?success=checkemail' );
			} else {

				$place = home_url();
				if ( rcl_get_option( 'login_form_recall', 0 ) == 1 ) {
					$place = rcl_format_url( get_permalink( rcl_get_option( 'page_login_form_recall' ) ) );
				}
				wp_safe_redirect( add_query_arg( array(
					'action-rcl' => 'login',
					'success'    => 'checkemail'
				), $place ) );
			}
			exit;
		}
	}

	if ( rcl_get_option( 'login_form_recall' ) == 2 ) {
		wp_safe_redirect( wp_login_url() . '?checkemail=confirm' );
	} else {
		wp_safe_redirect( add_query_arg( array(
			'action-rcl' => 'login',
			'login'      => 'checkemail'
		), home_url() ) );
	}
	exit;
}

//принимаем данные для подтверждения регистрации
add_action( 'init', 'rcl_confirm_user_resistration_activate', 10 );
function rcl_confirm_user_resistration_activate() {

	if ( ! isset( $_GET['rcl-confirmdata'] ) ) {
		return false;
	}

	if ( rcl_get_option( 'confirm_register_recall' ) ) {
		add_action( 'wp', 'rcl_confirm_user_registration', 10 );
	}
}

//добавляем коды ошибок для тряски формы ВП
add_filter( 'shake_error_codes', 'rcl_add_shake_error_codes' );
function rcl_add_shake_error_codes( $codes ) {
	return array_merge( $codes, array(
		'rcl_register_login',
		'rcl_register_empty',
		'rcl_register_email',
		'rcl_register_login_us',
		'rcl_register_email_us'
	) );
}

//регистрация пользователя на сайте
add_filter( 'registration_errors', 'rcl_get_register_user', 90 );
function rcl_get_register_user( $errors ) {
	global $wp_errors;

	$wp_errors = new WP_Error();

	if ( $errors->has_errors() ) {
		$wp_errors = $errors;

		return $wp_errors;
	}

	//Dont unslash password
	//phpcs:ignore
	$pass  = isset( $_POST['user_pass'] ) ? $_POST['user_pass'] : '';
	$email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
	$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';

	if ( ! empty( $_POST['redirect_to'] ) ) {
		$ref = apply_filters( 'url_after_register_rcl', sanitize_text_field( wp_unslash( $_POST['redirect_to'] ) ) );
	} else {
		$ref = wp_registration_url();
	}

	$get_fields = rcl_get_profile_fields();
	$required   = true;
	if ( $get_fields ) {
		foreach ( $get_fields as $field ) {

			$field = apply_filters( 'chek_custom_field_regform', $field );
			if ( ! $field ) {
				continue;
			}

			$slug = $field['slug'];
			if ( isset( $field['required'] ) && $field['required'] == 1 && isset( $field['register'] ) && $field['register'] == 1 ) {

				if ( $field['type'] == 'checkbox' ) {

					$count_field = count( $field['values'] );

					for ( $a = 0; $a < $count_field; $a ++ ) {
						if ( ! isset( $_POST[ $slug ][ $a ] ) ) {
							$required = false;
						} else {
							$required = true;
							break;
						}
					}
				} else {
					if ( ! isset( $_POST[ $slug ] ) ) {
						$required = false;
					}
				}
			}
		}
	}

	if ( ! $pass || ! $email || ! $login || ! $required ) {
		$wp_errors->add( 'rcl_register_empty', __( 'Fill in the required fields!', 'wp-recall' ) );

		return $wp_errors;
	}

	$wp_errors = apply_filters( 'rcl_registration_errors', $wp_errors, $login, $email );

	if ( is_wp_error( $wp_errors ) && $wp_errors->has_errors() ) {
		return $wp_errors;
	}

	do_action( 'pre_register_user_rcl', $ref );

	//регистрируем юзера с указанными данными

	$userdata = array(
		'user_pass'    => $pass,
		'user_login'   => $login,
		'user_email'   => $email,
		'display_name' => isset( $_POST['display_name'] ) ? sanitize_user( wp_unslash( $_POST['display_name'] ) ) : ''
	);


	$user_id = rcl_insert_user( $userdata );

	if ( $user_id ) {

		if ( rcl_get_option( 'login_form_recall' ) == 2 || false !== strpos( $ref, wp_login_url() ) ) {
			//если форма ВП, то возвращаем на login с нужными GET-параметрами
			if ( rcl_get_option( 'confirm_register_recall' ) == 1 ) {
				wp_safe_redirect( wp_login_url() . '?checkemail=confirm' );
			} else {
				wp_safe_redirect( wp_login_url() . '?checkemail=registered' );
			}
		} else {

			//иначе возвращаем на ту же страницу
			if ( rcl_get_option( 'confirm_register_recall' ) ) {
				wp_safe_redirect( rcl_format_url( $ref ) . 'action-rcl=login&register=checkemail' );
			} else {
				wp_safe_redirect( rcl_format_url( $ref ) . 'action-rcl=login&register=success' );
			}
		}

		exit();
	}
}

//принимаем данные с формы регистрации
add_action( 'wp', 'rcl_get_register_user_activate', 10 );
function rcl_get_register_user_activate() {
	if ( isset( $_POST['register_wpnonce'] ) ) { //если данные пришли с формы wp-recall

		if ( ! wp_verify_nonce( sanitize_key( $_POST['register_wpnonce'] ), 'register-key-rcl' ) ) {
			return false;
		}

		$email = isset( $_POST['user_email'] ) ? sanitize_email( wp_unslash( $_POST['user_email'] ) ) : '';
		$login = isset( $_POST['user_login'] ) ? sanitize_user( wp_unslash( $_POST['user_login'] ) ) : '';

		register_new_user( $login, $email );
	}
}

//письмо высылаемое при регистрации
function rcl_register_mail( $userdata ) {

	$user_login = $userdata['user_login'];
	$user_id    = $userdata['user_id'];

	$userdata = apply_filters( 'rcl_register_mail_data', $userdata );

	$textmail = '
    <p>' . esc_html__( 'You or someone else signed up on our website', 'wp-recall' ) . ' "' . get_bloginfo( 'name' ) . '" ' . esc_html__( 'with the following data:', 'wp-recall' ) . '</p>
    <p>' . esc_html__( 'Login', 'wp-recall' ) . ': ' . $userdata['user_login'] . '</p>
    <p>' . esc_html__( 'Password', 'wp-recall' ) . ': ' . $userdata['user_pass'] . '</p>';

	if ( rcl_get_option( 'confirm_register_recall' ) ) {

		$subject = esc_html__( 'Confirm your registration!', 'wp-recall' );

		$confirmstr = base64_encode(
			json_encode(
				array(
					$user_login,
					md5( $user_id ) //todo rework
				)
			)
		);

		$url = add_query_arg( array(
			'rcl-confirmdata' => urlencode( $confirmstr )
		), home_url() );

		$textmail .= '<p>' . esc_html__( 'If it was you, then confirm your registration by clicking on the link below', 'wp-recall' ) . ':</p>
        <p><a href="' . $url . '">' . $url . '</a></p>
        <p>' . esc_html__( 'Unable to activate the account?', 'wp-recall' ) . '</p>
        <p>' . esc_html__( 'Copy the link below, paste it into the address bar of your browser and hit Enter', 'wp-recall' ) . '</p>';
	} else {

		$subject = esc_html__( 'Registration completed', 'wp-recall' );
	}

	$textmail .= '<p>' . esc_html__( 'If it wasn’t you, then just ignore this email', 'wp-recall' ) . '</p>';

	$textmail = apply_filters( 'rcl_register_mail_text', $textmail, $userdata );

	rcl_mail( $userdata['user_email'], $subject, $textmail );
}

//сохраняем данные произвольных полей профиля при регистрации
add_action( 'user_register', 'rcl_register_user_data', 10 );
function rcl_register_user_data( $user_id ) {

	update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

	rcl_update_profile_fields( $user_id );
}

//Формируем массив сервисных сообщений формы регистрации и входа
function rcl_notice_form( $form = 'login' ) {
	global $wp_errors;

	do_action( 'rcl_' . $form . '_form_head' );

	$wp_error = new WP_Error();

	if ( is_wp_error( $wp_errors ) && $wp_errors->has_errors() ) {
		$wp_errors->export_to( $wp_error );
	}

	$wp_error = apply_filters( 'rcl_login_errors', $wp_error );

	if ( $wp_error->get_error_code() ) {
		$errors   = '';
		$messages = '';
		foreach ( $wp_error->get_error_codes() as $code ) {
			$severity = $wp_error->get_error_data( $code );
			foreach ( $wp_error->get_error_messages( $code ) as $error_message ) {

				if ( 'message' == $severity ) {
					$messages .= ' ' . wp_kses_post( $error_message ) . "<br />\n";
				} else {
					$errors .= ' ' . wp_kses_post( $error_message ) . "<br />\n";
				}
			}
		}

		if ( ! empty( $errors ) ) {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<div class="login-error">' . apply_filters( 'login_errors', $errors ) . "</div>\n";
		}
		if ( ! empty( $messages ) ) {
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<span class="login-message">' . apply_filters( 'login_messages', $messages ) . "</span>\n";
		}
	}
}

//Проверяем заполненность поля повтора пароля
add_filter( 'rcl_registration_errors', 'rcl_chek_repeat_pass' );
function rcl_chek_repeat_pass( $errors ) {

	if ( ! rcl_get_option( 'repeat_pass' ) ) {
		return false;
	}

	if ( isset( $_POST['user_secondary_pass'], $_POST['user_pass'] ) && $_POST['user_secondary_pass'] !== $_POST['user_pass'] ) {
		$errors = new WP_Error();
		$errors->add( 'rcl_register_repeat_pass', esc_html__( 'Repeated password not correct!', 'wp-recall' ) );
	}

	return $errors;
}

add_filter( 'wp_login_errors', 'rcl_checkemail_success' );
add_filter( 'rcl_login_errors', 'rcl_checkemail_success' );
function rcl_checkemail_success( $errors ) {

	if ( isset( $_GET['success'] ) && $_GET['success'] == 'checkemail' ) {

		$errors = new WP_Error();
		$errors->add( 'checkemail', esc_html__( 'Your email has been successfully confirmed! Log in using your username and password', 'wp-recall' ), 'message' );
	}

	if ( isset( $_GET['register'] ) ) {

		$errors = new WP_Error();

		if ( $_GET['register'] == 'success' ) {

			$errors->add( 'register', esc_html__( 'Registration completed!', 'wp-recall' ), 'message' );
		}

		if ( $_GET['register'] == 'checkemail' ) {

			$errors->add( 'register', esc_html__( 'Registration is completed! Check your email for the confirmation link.', 'wp-recall' ), 'message' );
		}
	}

	if ( isset( $_GET['login'] ) ) {

		$errors = new WP_Error();

		if ( $_GET['login'] == 'checkemail' ) {

			$errors->add( 'register', esc_html__( 'Your email is not confirmed!', 'wp-recall' ), 'error' );
		}
	}

	if ( isset( $_GET['remember'] ) ) {

		$errors = new WP_Error();

		if ( $_GET['remember'] == 'success' ) {

			$errors->add( 'register', esc_html__( 'Your password has been sent! Check your email.', 'wp-recall' ), 'message' );
		}
	}

	return $errors;
}


function rcl_get_current_url( $typeform = false, $unset = false ) {

	$args = array(
		'register'        => false,
		'login'           => false,
		'remember'        => false,
		'success'         => false,
		'rcl-confirmdata' => false
	);

	$args['action-rcl'] = $typeform;

	if ( $typeform == 'remember' ) {
		$args['remember'] = 'success';
	}

	return add_query_arg( $args );
}

function rcl_referer_url( $typeform = false ) {
	echo esc_url( rcl_get_current_url( $typeform ) );
}

function rcl_form_action( $typeform ) {
	echo esc_url( rcl_get_current_url( $typeform, true ) );
}

//Добавляем фильтр для формы авторизации
add_action( 'login_form', 'rcl_filters_signform', 1 );
function rcl_filters_signform() {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'signform_fields_rcl', '' );
}

//Добавляем фильтр для формы регистрации
add_action( 'register_form', 'rcl_filters_regform', 1 );
function rcl_filters_regform() {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'regform_fields_rcl', '' );
}

add_filter( 'regform_fields_rcl', 'rcl_password_regform', 5 );
function rcl_password_regform( $content ) {

	$difficulty = rcl_get_option( 'difficulty_parole' );

	$content .= '<div class="form-block-rcl default-field">';

	$content .= '<input placeholder="' . esc_html__( 'Password', 'wp-recall' ) . '" required id="primary-pass-user" type="password" ' . ( $difficulty == 1 ? 'onkeyup="passwordStrength(this.value)"' : '' ) . ' name="user_pass">';

	$content .= '<i class="rcli fa-lock"></i>';
	$content .= '<span class="required">*</span>';
	$content .= '</div>';

	if ( $difficulty == 1 ) {
		$content .= '<div class="form-block-rcl">
                <label>' . esc_html__( 'Password strength indicator', 'wp-recall' ) . ':</label>
                <div id="passwordStrength" class="strength0">
                    <div id="passwordDescription">' . esc_html__( 'A password has not been entered', 'wp-recall' ) . '</div>
                </div>
            </div>';
	}

	return $content;
}

//Добавляем поле повтора пароля в форму регистрации
add_filter( 'regform_fields_rcl', 'rcl_secondary_password', 10 );
function rcl_secondary_password( $fields ) {

	if ( ! rcl_get_option( 'repeat_pass' ) ) {
		return $fields;
	}

	$fields .= '<div class="form-block-rcl default-field">
                    <input placeholder="' . esc_html__( 'Repeat the password', 'wp-recall' ) . '" required id="secondary-pass-user" type="password" name="user_secondary_pass">
                    <i class="rcli fa-lock"></i>
                    <span class="required">*</span>
                <div id="notice-chek-password"></div>
            </div>
            <script>jQuery(function(){
            jQuery("#registerform,.form-tab-rcl").on("keyup","#secondary-pass-user",function(){
                var pr = jQuery("#primary-pass-user").val();
                var sc = jQuery(this).val();
                var notice;
                if(pr!=sc) notice = "<span class=login-error>' . esc_html__( 'The passwords do not match!', 'wp-recall' ) . '</span>";
                else notice = "<span class=login-message>' . esc_html__( 'The passwords match', 'wp-recall' ) . '</span>";
                jQuery("#notice-chek-password").html(notice);
            });});
        </script>';

	return $fields;
}

//Вывод произвольных полей профиля в форме регистрации
add_filter( 'regform_fields_rcl', 'rcl_custom_fields_regform', 20 );
function rcl_custom_fields_regform( $content ) {

	$regFields = array();

	$fields = rcl_get_profile_fields();

	if ( $fields ) {

		foreach ( $fields as $field ) {

			if ( ! isset( $field['register'] ) || $field['register'] != 1 ) {
				continue;
			}

			if ( ! isset( $field['value_in_key'] ) ) {
				$field['value_in_key'] = true;
			}

			$regFields[] = $field;
		}
	}

	$regFields = apply_filters( 'rcl_register_form_fields', stripslashes_deep( $regFields ) );

	if ( ! $regFields ) {
		return $content;
	}

	$hiddens = array();
	foreach ( $regFields as $field ) {

		$field = apply_filters( 'custom_field_regform', $field );

		if ( $field['type'] == 'hidden' ) {
			$hiddens[] = $field;
			continue;
		}

		$class = !empty( $field['class'] )  ? $field['class'] : '';
		$id    = !empty( $field['id'] )  ? 'id=' . $field['id'] : '';
		$attr  = !empty( $field['attr'] ) ? $field['attr'] : '';

		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$field['value'] = isset( $_POST[ $field['slug'] ] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST[ $field['slug'] ] ) ) : false;

		unset( $field['class'] );
		unset( $field['attr'] );
		unset( $field['id'] );

		$fieldObject = Rcl_Field::setup( $field );

		$content .= '<div class="form-block-rcl ' . $class . '" ' . $id . ' ' . $attr . '>';

		if ( $fieldObject->title ) {
			$content .= '<label>' . $fieldObject->get_title();
			if ( $field['type'] ) {
				$content .= '<span class="colon">:</span>';
			}
			$content .= '</label>';
		}

		$content .= $fieldObject->get_field_input();

		$content .= '</div>';
	}

	foreach ( $hiddens as $field ) {
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$field['value'] = isset( $_POST[ $field['slug'] ] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST[ $field['slug'] ] ) ) : false;
		$fieldObject    = Rcl_Field::setup( $field );
		$content        .= $fieldObject->get_field_input();
	}

	return $content;
}
