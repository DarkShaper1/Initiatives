<?php

require_once 'classes/class-rcl-profile-fields.php';

if ( is_admin() ) {
	require_once 'admin/index.php';
}

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'rcl_profile_scripts', 10 );
endif;
function rcl_profile_scripts() {
	global $user_ID;
	if ( rcl_is_office( $user_ID ) ) {
		rcl_enqueue_style( 'rcl-profile', rcl_addon_url( 'style.css', __FILE__ ) );
		rcl_enqueue_script( 'rcl-profile-scripts', rcl_addon_url( 'js/scripts.js', __FILE__ ) );
	}
}

add_filter( 'rcl_init_js_variables', 'rcl_init_js_profile_variables', 10 );
function rcl_init_js_profile_variables( $data ) {
	$data['local']['no_repeat_pass'] = __( 'Repeated password not correct!', 'wp-recall' );

	return $data;
}

add_action( 'init', 'rcl_tab_profile' );
function rcl_tab_profile() {

	rcl_tab(
		array(
			'id'       => 'profile',
			'name'     => __( 'Profile', 'wp-recall' ),
			'supports' => array( 'ajax' ),
			'public'   => 0,
			'icon'     => 'fa-user',
			'content'  => array(
				array(
					'callback' => array(
						'name' => 'rcl_tab_profile_content'
					)
				)
			)
		)
	);
}

add_action( 'rcl_bar_setup', 'rcl_bar_add_profile_link', 10 );
function rcl_bar_add_profile_link() {
	global $user_ID;

	if ( ! is_user_logged_in() ) {
		return false;
	}

	rcl_bar_add_menu_item( 'profile-link', array(
			'url'   => rcl_get_tab_permalink( $user_ID, 'profile' ),
			'icon'  => 'fa-user-secret',
			'label' => __( 'Profile settings', 'wp-recall' )
		)
	);
}

add_action( 'init', 'rcl_add_block_show_profile_fields' );
function rcl_add_block_show_profile_fields() {
	rcl_block( 'details', 'rcl_show_custom_fields_profile', array( 'id' => 'pf-block', 'order' => 20, 'public' => 1 ) );
}

function rcl_show_custom_fields_profile( $master_id ) {

	$get_fields = rcl_get_profile_fields();

	$content = '';

	if ( $get_fields ) {

		foreach ( ( array ) stripslashes_deep( $get_fields ) as $field ) {
			$field = apply_filters( 'custom_field_profile', $field );
			if ( ! $field ) {
				continue;
			}
			$slug = isset( $field['name'] ) ? $field['name'] : $field['slug'];

			if ( isset( $field['req'] ) && $field['req'] ) {
				$field['public_value'] = $field['req'];
			}

			if ( isset( $field['public_value'] ) && $field['public_value'] == 1 ) {
				$field['value'] = get_the_author_meta( $slug, $master_id );
				$content        .= Rcl_Field::setup( $field )->get_field_value( true );
			}
		}
	}

	if ( ! $content ) {
		return false;
	}

	return '<div class="show-profile-fields">' . $content . '</div>';
}

if ( ! is_admin() ) {
	add_action( 'wp', 'rcl_update_profile_notice' );
}
function rcl_update_profile_notice() {
	if ( isset( $_GET['updated'] ) ) {
		add_action( 'rcl_area_notice', function () {
			echo rcl_get_notice( [//phpcs:ignore
				'type' => 'success',
				'text' => esc_html__( 'Your profile has been updated', 'wp-recall' )
			] );
		} );
	}
}

//Обновляем профиль пользователя
add_action( 'wp', 'rcl_edit_profile', 10 );
function rcl_edit_profile() {
	global $user_ID;

	if ( ! isset( $_POST['submit_user_profile'] ) || ! isset( $_POST['_wpnonce'] ) ) {
		return false;
	}

	if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'update-profile_' . $user_ID ) ) {
		return false;
	}

	rcl_update_profile_fields( $user_ID );

	do_action( 'personal_options_update', $user_ID );

	$redirect_url = rcl_get_tab_permalink( $user_ID, 'profile' ) . '&updated=true';

	wp_safe_redirect( $redirect_url );

	exit;
}

add_filter( 'rcl_profile_fields', 'rcl_add_office_profile_fields', 10 );
function rcl_add_office_profile_fields( $fields ) {
	global $userdata;

	$profileFields = array();

	if ( isset( $userdata ) && $userdata->user_level >= rcl_get_option( 'consol_access_rcl', 7 ) ) {
		$profileFields[] = array(
			'slug'   => 'show_admin_bar_front',
			'title'  => __( 'Admin toolbar', 'wp-recall' ),
			'type'   => 'select',
			'values' => array(
				'false' => __( 'Disabled', 'wp-recall' ),
				'true'  => __( 'Enabled', 'wp-recall' )
			)
		);
	}

	$profileFields[] = array(
		'slug'     => 'user_email',
		'title'    => __( 'E-mail', 'wp-recall' ),
		'type'     => 'email',
		'required' => 1
	);

	$profileFields[] = array(
		'slug'     => 'primary_pass',
		'title'    => __( 'New password', 'wp-recall' ),
		'type'     => 'password',
		'required' => 0,
		'notice'   => __( 'If you want to change your password - enter a new one', 'wp-recall' )
	);

	$profileFields[] = array(
		'slug'     => 'repeat_pass',
		'title'    => __( 'Repeat password', 'wp-recall' ),
		'type'     => 'password',
		'required' => 0,
		'notice'   => __( 'Repeat the new password', 'wp-recall' )
	);

	return ( $fields ) ? array_merge( $profileFields, $fields ) : $profileFields;
}

function rcl_tab_profile_content( $master_id ) {
	global $userdata, $user_ID;

	$profileFields = rcl_get_profile_fields( array( 'user_id' => $master_id ) );

	$Table = new Rcl_Table( array(
		'cols'  => array(
			array(
				'width' => 30
			),
			array(
				'width' => 70
			)
		),
		'zebra' => true,
		//'border' => array('table', 'rows')
	) );

	$content = '<h3>' . __( 'User profile', 'wp-recall' ) . ' ' . $userdata->display_name . '</h3>
	<form name="profile" id="your-profile" action="" method="post"  enctype="multipart/form-data">';

	$profileFields = stripslashes_deep( $profileFields );

	$hiddens = array();
	foreach ( $profileFields as $field ) {

		$field = apply_filters( 'custom_field_profile', $field );

		$slug = isset( $field['name'] ) ? $field['name'] : $field['slug'];

		if ( ! $field || ! $slug ) {
			continue;
		}

		if ( $field['type'] == 'hidden' ) {
			$hiddens[] = $field;
			continue;
		}

		$fieldObject = Rcl_Field::setup( $field );

		$fieldObject->set_prop( 'value', ( isset( $userdata->$slug ) ) ? $userdata->$slug : false );

		if ( $slug == 'email' ) {
			$fieldObject->set_prop( 'value', get_the_author_meta( 'email', $user_ID ) );
		}

		if ( $field['slug'] != 'show_admin_bar_front' && ! isset( $field['value_in_key'] ) ) {
			$fieldObject->set_prop( 'value_in_key', true );
		}

		$fieldInput = $fieldObject->get_field_input();

		if ( isset( $fieldObject->admin ) && $fieldObject->admin && ! rcl_is_user_role( $user_ID, 'administrator' ) ) {
			if ( $fieldObject->value ) {
				$fieldInput = $fieldObject->get_field_value();
			}
		}

		$Table->add_row( array(
			$fieldObject->get_title(),
			$fieldInput
		), apply_filters( 'rcl_profile_row_attrs', array( 'id' => 'profile-field-' . $slug ), $field ) );
	}

	$content .= $Table->get_table();

	foreach ( $hiddens as $field ) {

		$slug = isset( $field['name'] ) ? $field['name'] : $field['slug'];

		$fieldObject = Rcl_Field::setup( $field );

		$fieldObject->set_prop( 'value', ( isset( $userdata->$slug ) ) ? $userdata->$slug : false );

		$content .= $fieldObject->get_field_input();
	}

	$content .= "<script>
				jQuery(function(){
					jQuery('#your-profile').find('.required-checkbox').each(function(){
						var name = jQuery(this).attr('name');
						var chekval = jQuery('#your-profile input[name=\"'+name+'\"]:checked').val();
						if(chekval) jQuery('#your-profile input[name=\"'+name+'\"]').attr('required',false);
						else jQuery('#your-profile input[name=\"'+name+'\"]').attr('required',true);
					});"
	            . "});"
	            . "</script>";

	$content = apply_filters( 'profile_options_rcl', $content, $userdata );

	$content .= wp_nonce_field( 'update-profile_' . $user_ID, '_wpnonce', true, false ) . '
		<div style="text-align:right;">'
	            . rcl_get_button( array(
			'label'   => __( 'Update profile', 'wp-recall' ),
			'id'      => 'cpsubmit',
			'icon'    => 'fa-check-circle',
			'onclick' => 'return rcl_check_profile_form()? rcl_submit_form(this): false;'
		) )
	            . '<input type="hidden" value="1" name="submit_user_profile" />
		</div>
	</form>';

	if ( rcl_get_option( 'delete_user_account' ) ) {
		$content .= '
		<form method="post" action="" name="delete_account">
		' . wp_nonce_field( 'delete-user-' . $user_ID, '_wpnonce', true, false )
		            . rcl_get_button( array(
				'label'   => __( 'Delete your profile', 'wp-recall' ),
				'id'      => 'delete_acc',
				'icon'    => 'fa-eraser',
				'onclick' => 'return confirm("' . __( 'Are you sure? It can’t be restaured!', 'wp-recall' ) . '")? rcl_submit_form(this): false;'
			) )
		            . '<input type="hidden" value="1" name="rcl_delete_user_account"/>
		</form>';
	}

	return $content;
}

//Выводим возможность синхронизации соц.аккаунтов в его личном кабинете
//при активированном плагине Ulogin
if ( function_exists( 'ulogin_profile_personal_options' ) ) {
	function get_ulogin_profile_options( $profile_block, $userdata ) {
		ob_start();
		ulogin_profile_personal_options( $userdata );
		$profile_block .= ob_get_contents();
		ob_end_clean();

		return $profile_block;
	}

	add_filter( 'profile_options_rcl', 'get_ulogin_profile_options', 10, 2 );
}

add_action( 'init', 'rcl_delete_user_account_activate' );
function rcl_delete_user_account_activate() {
	if ( isset( $_POST['rcl_delete_user_account'] ) ) {
		add_action( 'wp', 'rcl_delete_user_account' );
	}
}

//Удаляем аккаунт пользователя
function rcl_delete_user_account() {
	global $user_ID, $wpdb;
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'delete-user-' . $user_ID ) ) {
		return false;
	}

	require_once( ABSPATH . 'wp-admin/includes/user.php' );

	$wpdb->query( $wpdb->prepare( "DELETE FROM " . RCL_PREF . "user_action WHERE user = %d", $user_ID ) );//phpcs:ignore

	$delete = wp_delete_user( $user_ID );

	if ( $delete ) {
		wp_die( esc_html__( 'We are very sorry but your account has been deleted!', 'wp-recall' ) );
	} else {
		wp_die( esc_html__( 'Account deletion failed! Go back and try again.', 'wp-recall' ) );
	}
}
