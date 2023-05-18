<?php

require_once 'addon-settings.php';

add_action( 'admin_menu', 'rcl_profile_admin_menu', 30 );
function rcl_profile_admin_menu() {
	add_submenu_page( 'manage-wprecall', __( 'Profile fields', 'wp-recall' ), __( 'Profile fields', 'wp-recall' ), 'manage_options', 'manage-userfield', 'rcl_profile_fields_manager' );
}

add_filter( 'rcl_field_options', 'rcl_edit_profile_field_options', 10, 3 );
function rcl_edit_profile_field_options( $options, $field, $manager_id ) {

	if ( $manager_id != 'profile' || ! rcl_is_register_open() ) {
		return $options;
	}

	$options[] = array(
		'type'   => 'radio',
		'slug'   => 'register',
		'title'  => __( 'display in registration form', 'wp-recall' ),
		'values' => array(
			__( 'No', 'wp-recall' ),
			__( 'Yes', 'wp-recall' )
		)
	);

	return $options;
}

function rcl_profile_fields_manager() {

	$Manager = new Rcl_Profile_Fields_Manager();

	$content = '<h2>' . esc_html__( 'Manage profile fields', 'wp-recall' ) . '</h2>';

	$content .= '<p>' . esc_html__( 'On this page you can create custom fields of the user profile, as well as to manage already created fields', 'wp-recall' ) . '</p>';

	$content .= $Manager->get_manager();

	echo $content;//phpcs:ignore
}

//Сохраняем изменения в произвольных полях профиля со страницы пользователя
add_action( 'personal_options_update', 'rcl_save_profile_fields' );
add_action( 'edit_user_profile_update', 'rcl_save_profile_fields' );
function rcl_save_profile_fields( $user_id ) {

	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return false;
	}

	rcl_update_profile_fields( $user_id );
}

//Выводим произвольные поля профиля на странице пользователя в админке
if ( is_admin() ):
	add_action( 'profile_personal_options', 'rcl_get_custom_fields_profile' );
	add_action( 'edit_user_profile', 'rcl_get_custom_fields_profile' );
endif;
function rcl_get_custom_fields_profile( $user ) {

	$args = array(
		'exclude' => array(
			'first_name',
			'last_name',
			'description',
			'user_url',
			'display_name',
			'user_email',
			'primary_pass',
			'repeat_pass',
			'show_admin_bar_front'
		),
		'user_id' => $user->ID
	);

	$fields = apply_filters( 'rcl_admin_profile_fields', rcl_get_profile_fields( $args ), $user );

	if ( $fields ) {

		$content = '<h3>' . esc_html__( 'Custom Profile Fields', 'wp-recall' ) . ':</h3>
        <table class="form-table rcl-form rcl-custom-fields-box">';

		$hiddens = array();
		foreach ( $fields as $field ) {

			if ( $field['type'] == 'hidden' ) {
				$hiddens[] = $field;
				continue;
			}

			if ( ! isset( $field['value_in_key'] ) ) {
				$field['value_in_key'] = true;
			}

			if ( ! isset( $field['value'] ) ) {
				$field['value'] = get_the_author_meta( $field['slug'], $user->ID );
			}

			$fieldObject = Rcl_Field::setup( $field );

			$content .= '<tr class="rcl-custom-field">';
			$content .= '<th><label>' . $fieldObject->get_title() . ':</label></th>';
			$content .= '<td>' . $fieldObject->get_field_input() . '</td>';
			$content .= '</tr>';
		}

		$content .= '</table>';

		foreach ( $hiddens as $field ) {

			if ( ! isset( $field['value'] ) ) {
				$field['value'] = get_the_author_meta( $field['slug'], $user->ID );
			}

			$content .= Rcl_Field::setup( $field )->get_field_input();
		}

		echo $content;//phpcs:ignore
	}
}

//save users page option in global array of options
add_action( 'rcl_fields_update', 'rcl_update_users_page_option', 10, 2 );
function rcl_update_users_page_option( $fields, $manager_id ) {
	if ( $manager_id != 'profile' || ! isset( $_POST['users_page_rcl'] ) ) {
		return false;
	}
	rcl_update_option( 'users_page_rcl', intval( $_POST['users_page_rcl'] ) );
}

//add users page value in the time of saving global options of plugin
add_filter( 'rcl_global_options_pre_update', 'rcl_add_options_users_page_value', 10 );
function rcl_add_options_users_page_value( $values ) {
	$values['users_page_rcl'] = rcl_get_option( 'users_page_rcl', 0 );

	return $values;
}
