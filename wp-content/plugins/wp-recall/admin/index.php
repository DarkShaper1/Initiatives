<?php
require_once "admin-menu.php";
require_once "metaboxes.php";

add_action( 'admin_init', 'rcl_admin_scripts', 10 );

add_filter( 'display_post_states', 'rcl_mark_own_page', 10, 2 );
function rcl_mark_own_page( $post_states, $post ) {

	if ( $post->post_type === 'page' ) {

		$plugin_pages = get_site_option( 'rcl_plugin_pages' );

		if ( ! $plugin_pages ) {
			return $post_states;
		}

		if ( in_array( $post->ID, $plugin_pages ) ) {
			$post_states[] = __( 'The page of plugin WP-Recall' );
		}
	}

	return $post_states;
}

add_filter( 'rcl_field_options', 'rcl_edit_field_options', 10, 3 );
function rcl_edit_field_options( $options, $field, $manager_id ) {

	$types = array( 'range', 'runner' );

	if ( in_array( $field->type, $types ) ) {

		foreach ( $options as $k => $option ) {

			if ( $option['slug'] == 'required' ) {
				unset( $options[ $k ] );
			}
		}
	}

	return $options;
}

function rmag_global_options() {

	require_once RCL_PATH . 'admin/classes/class-rcl-options-manager.php';

	$Manager = new Rcl_Options_Manager( array(
		'option_name'  => 'primary-rmag-options',
		'page_options' => 'manage-wpm-options',
	) );

	$Manager = apply_filters( 'rcl_commerce_options', $Manager );

	$content = '<h2>' . esc_html__( 'Settings of commerce', 'wp-recall' ) . '</h2>';

	$content .= $Manager->get_content();

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

function rmag_update_options() {
	if ( current_user_can( 'administrator' ) && isset( $_POST['primary-rmag-options'], $_POST['_wpnonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'update-options-rmag' ) ) {
			return false;
		}

		if ( isset( $_POST['global'] ) ) {
			//phpcs:ignore
			foreach ( $_POST['global'] as $key => $value ) {
				if ( $key == 'primary-rmag-options' ) {
					continue;
				}
				//TODO sanitize options
				$options[ sanitize_key( $key ) ] = $value;
			}

			update_site_option( 'primary-rmag-options', $options );
		}

		if ( isset( $_POST['local'] ) ) {
			//phpcs:ignore
			foreach ( ( array ) $_POST['local'] as $key => $value ) {
				//TODO sanitize options
				update_site_option( sanitize_key( $key ), $value );
			}
		}

		wp_safe_redirect( admin_url( 'admin.php?page=manage-wpm-options' ) );
		exit;
	}
}

add_action( 'init', 'rmag_update_options' );
function rcl_wp_list_current_action() {
	if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) ) {
		return false;
	}

	if ( isset( $_REQUEST['action'] ) && - 1 != $_REQUEST['action'] ) {
		return sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
	}

	if ( isset( $_REQUEST['action2'] ) && - 1 != $_REQUEST['action2'] ) {
		return sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) );
	}

	return false;
}

if ( is_admin() ) {
	add_action( 'admin_init', 'rcl_postmeta_post' );
}
function rcl_postmeta_post() {
	add_meta_box( 'recall_meta', __( 'WP-Recall settings', 'wp-recall' ), 'rcl_options_box', 'post', 'normal', 'high' );
	add_meta_box( 'recall_meta', __( 'WP-Recall settings', 'wp-recall' ), 'rcl_options_box', 'page', 'normal', 'high' );
}

function rcl_options_box( $post ) {
	//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo apply_filters( 'rcl_post_options', '', $post );
	?>
    <input type="hidden" name="rcl_fields_nonce" value="<?php echo esc_attr( wp_create_nonce( __FILE__ ) ); ?>"/>
	<?php
}

function rcl_postmeta_update( $post_id ) {
	if ( ! isset( $_POST['rcl_fields_nonce'] ) ) {
		return false;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_POST['rcl_fields_nonce'] ), __FILE__ ) ) {
		return false;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	if ( ! isset( $_POST['wprecall'] ) ) {
		return false;
	}
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$POST = rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['wprecall'] ) );

	foreach ( $POST as $key => $value ) {
		if ( ! is_array( $value ) ) {
			$value = trim( $value );
		}
		if ( $value == '' ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}

	return $post_id;
}

rcl_ajax_action( 'rcl_update_options', false );
function rcl_update_options() {

	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json( [ 'error' => esc_html__( 'Error', 'wp-recall' ) ] );
	}

	rcl_verify_ajax_nonce();

	$POST = $_POST;

	array_walk_recursive(
		$POST, function ( &$v, $k ) {
		//TODO sanitize options
		$v = trim( $v );
	} );

	foreach ( $POST as $option_name => $values ) {

		if ( ! is_array( $values ) ) {
			continue;
		}

		$values = apply_filters( $option_name . '_pre_update', $values );

		if ( $option_name == 'local' ) {

			foreach ( $values as $local_name => $value ) {
				update_site_option( $local_name, $value );
			}
		} else {
			update_site_option( $option_name, $values );
		}
	}

	do_action( 'rcl_update_options' );

	wp_send_json( array(
		'success' => esc_html__( 'Settings saved!', 'wp-recall' )
	) );
}

add_action( 'rcl_update_options', 'rcl_delete_temp_default_avatar_cover', 10 );
function rcl_delete_temp_default_avatar_cover() {

	if ( isset( $_POST['rcl_global_options']['default_avatar'] ) ) {
		rcl_delete_temp_media( intval( $_POST['rcl_global_options']['default_avatar'] ) );
	}

	if ( isset( $_POST['rcl_global_options']['default_cover'] ) ) {
		rcl_delete_temp_media( intval( $_POST['rcl_global_options']['default_cover'] ) );
	}
}

function rcl_add_cover_options( $options ) {

	$options->box( 'primary' )->group( 'design' )->add_options( [
		array(
			'type'       => 'uploader',
			'temp_media' => 1,
			'max_size'   => 5120,
			'multiple'   => 0,
			'crop'       => [ 'ratio' => 0 ],
			'filetitle'  => 'rcl-default-cover',
			'filename'   => 'rcl-default-cover',
			'slug'       => 'default_cover',
			'title'      => __( 'Default cover', 'wp-recall' ),
		),
		array(
			'type'       => 'runner',
			'value_min'  => 0,
			'value_max'  => 5120,
			'value_step' => 256,
			'default'    => 1024,
			'slug'       => 'cover_weight',
			'title'      => __( 'Max weight of cover', 'wp-recall' ) . ', Kb',
			'notice'     => __( 'Set the image upload limit in kb, by default', 'wp-recall' ) . ' 1024Kb' .
			                '. ' . __( 'If 0 is specified, download is disallowed.', 'wp-recall' )
		)
	] );

	return $options;
}

function wp_enqueue_theme_rcl( $url ) {
	wp_enqueue_style( 'theme_rcl', $url );
}

/* 16.0.0 */
add_action( 'admin_init', 'rcl_update_custom_fields', 10 );
function rcl_update_custom_fields() {
	global $wpdb;

	if ( ! current_user_can( 'administrator' ) ) {
		return false;
	}

	if ( ! isset( $_POST['rcl_save_custom_fields'] ) || ! isset( $_POST['_wpnonce'] ) ) {
		return false;
	}

	if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'rcl-update-custom-fields' ) ) {
		return false;
	}

	$fields = array();

	$table = 'postmeta';

	if ( isset( $_POST['rcl-fields-options']['name-option'] ) && $_POST['rcl-fields-options']['name-option'] == 'rcl_profile_fields' ) {
		$table = 'usermeta';
	}

	$POSTDATA = apply_filters( 'rcl_pre_update_custom_fields_options', rcl_recursive_map( 'sanitize_text_field', $_POST ) );

	if ( ! $POSTDATA ) {
		return false;
	}

	if ( isset( $POSTDATA['rcl_deleted_custom_fields'] ) ) {

		$deleted = explode( ',', $POSTDATA['rcl_deleted_custom_fields'] );

		if ( $deleted ) {

			foreach ( $deleted as $slug ) {
				$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->$table . " WHERE meta_key = %s", $slug ) );
			}
		}
	}

	$newFields = array();

	if ( isset( $POSTDATA['new-field'] ) ) {

		$nKey = 0;

		foreach ( $POSTDATA['new-field'] as $optionSlug => $vals ) {
			$newFields[ $nKey ] = $vals;
			$nKey ++;
		}
	}

	$fields = array();
	$nKey   = 0;

	foreach ( $POSTDATA['fields'] as $k => $slug ) {

		if ( ! $slug ) {

			if ( ! isset( $newFields[ $nKey ] ) || ! $newFields[ $nKey ]['title'] ) {
				continue;
			}

			if ( isset( $newFields[ $nKey ]['slug'] ) && $newFields[ $nKey ]['slug'] ) {
				$slug = $newFields[ $nKey ]['slug'];
			} else {
				$slug = str_replace( array(
					'-',
					' '
				), '_', rcl_sanitize_string( $newFields[ $nKey ]['title'] ) . '-' . rand( 10, 100 ) );
			}

			$field = $newFields[ $nKey ];

			$nKey ++;
		} else {

			if ( ! isset( $POSTDATA['field'][ $slug ] ) ) {
				continue;
			}

			$field = $POSTDATA['field'][ $slug ];
		}

		$field['slug'] = $slug;

		$fields[] = $field;
	}

	foreach ( $fields as $k => $field ) {

		if ( isset( $field['values'] ) && $field['values'] && is_array( $field['values'] ) ) {

			$values = array();
			foreach ( $field['values'] as $val ) {
				if ( $val == '' ) {
					continue;
				}
				$values[] = $val;
			}

			$fields[ $k ]['values'] = $values;
		}
	}

	if ( isset( $POSTDATA['options'] ) ) {
		$fields['options'] = $POSTDATA['options'];
	}

	update_site_option( sanitize_key( $_POST['rcl-fields-options']['name-option'] ), $fields );

	do_action( 'rcl_update_custom_fields', $fields, $POSTDATA );

	if ( isset( $_POST['_wp_http_referer'] ) ) {
		wp_safe_redirect( wp_unslash( $_POST['_wp_http_referer'] ) );
	}
	exit;
}

rcl_ajax_action( 'rcl_get_new_custom_field', false );
function rcl_get_new_custom_field() {

	$post_type = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$primary = isset( $_POST['primary_options'] ) ? rcl_recursive_map( 'sanitize_text_field', (array) json_decode( wp_unslash( $_POST['primary_options'] ) ) ) : [];
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$default = isset( $_POST['default_options'] ) ? rcl_recursive_map( 'sanitize_text_field', (array) json_decode( wp_unslash( $_POST['default_options'] ) ) ) : [];


	$manageFields = new Rcl_Custom_Fields_Manager( $post_type, $primary );

	if ( $default ) {

		$manageFields->defaultOptions = array();

		foreach ( $default as $option ) {
			$manageFields->defaultOptions[] = ( array ) $option;
		}
	}

	$content = $manageFields->empty_field();

	wp_send_json( array(
		'content' => $content
	) );
}

rcl_ajax_action( 'rcl_get_custom_field_options', false );
function rcl_get_custom_field_options() {

	$type_field = isset( $_POST['type_field'] ) ? sanitize_key( $_POST['type_field'] ) : '';
	$old_type   = isset( $_POST['old_type'] ) ? sanitize_key( $_POST['old_type'] ) : '';
	$post_type  = isset( $_POST['post_type'] ) ? sanitize_key( $_POST['post_type'] ) : '';
	$slug_field = isset( $_POST['slug_field'] ) ? sanitize_key( $_POST['slug_field'] ) : '';
	//phpcs:disable
	$primary = isset( $_POST['primary_options'] ) ? ( array ) json_decode( wp_unslash( $_POST['primary_options'] ) ) : [];
	$default = isset( $_POST['default_options'] ) ? ( array ) json_decode( wp_unslash( $_POST['default_options'] ) ) : [];
	//phpcs:enable
	$manageFields = new Rcl_Custom_Fields_Manager( $post_type, $primary );

	if ( $default ) {

		$manageFields->defaultOptions = array();

		foreach ( $default as $option ) {
			$manageFields->defaultOptions[] = ( array ) $option;
		}
	}

	$manageFields->field = array( 'type' => $type_field );

	if ( strpos( $slug_field, 'CreateNewField' ) === false ) {

		$manageFields->field['slug'] = $slug_field;
	} else {

		$manageFields->field['slug'] = '';
		$manageFields->new_slug      = $slug_field;
	}

	$content = $manageFields->get_options();

	$multiVars = array(
		'select',
		'radio',
		'checkbox',
		'multiselect'
	);

	if ( in_array( $type_field, $multiVars ) ) {

		$content .= '<script>'
		            . "jQuery('#field-" . $slug_field . " .rcl-field-input .dynamic-values').sortable({
             containment: 'parent',
             placeholder: 'ui-sortable-placeholder',
             distance: 15,
             stop: function( event, ui ) {
                 var items = ui.item.parents('.dynamic-values').find('.dynamic-value');
                 items.each(function(f){
                     if(items.length == (f+1)){
                         jQuery(this).children('a').attr('onclick','rcl_add_dynamic_field(this);return false;').children('i').attr('class','fa-plus');
                     }else{
                         jQuery(this).children('a').attr('onclick','rcl_remove_dynamic_field(this);return false;').children('i').attr('class','fa-minus');
                     }
                 });

             }
         });"
		            . '</script>';
	}

	wp_send_json( array(
		'content' => $content
	) );
}

add_filter( 'admin_footer_text', 'rcl_admin_footer_text', 10 );
function rcl_admin_footer_text( $footer_text ) {
	$current_screen = get_current_screen();

	$dlm_page_ids = array(
		'toplevel_page_manage-wprecall',
		'wp-recall_page_rcl-options',
		'wp-recall_page_rcl-repository',
		'wp-recall_page_manage-addon-recall',
		'wp-recall_page_manage-templates-recall',
		'wp-recall_page_rcl-tabs-manager',
		'wp-recall_page_manage-userfield',
		'wp-recall_page_manage-public-form'
	);

	if ( isset( $current_screen->id ) && in_array( $current_screen->id, $dlm_page_ids ) ) {
		$footer_text = sprintf( __( 'If you liked plugin %sWP-Recall%s, please vote for it in repository %s★★★★★%s. Thank you so much!', 'wp-recall' ), '<strong>', '</strong>', '<a href="https://wordpress.org/support/view/plugin-reviews/wp-recall?filter=5#new-post" target="_blank">', '</a>' );
	}

	return $footer_text;
}

function rcl_send_addon_activation_notice( $addon_id, $addon_headers ) {
	wp_remote_post( RCL_SERVICE_HOST . '/products-files/api/add-ons.php?rcl-addon-info=add-notice', array(
			'body' => array(
				'rcl-key'  => get_site_option( 'rcl-key' ),
				'addon-id' => $addon_id,
				'headers'  => array(
					'version' => $addon_headers['version'],
					'item-id' => $addon_headers['item-id'],
					'key-id'  => $addon_headers['key-id'],
				),
				'host'     => ( isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '' )
			)
		)
	);
}

/* new fields manager functions */

rcl_ajax_action( 'rcl_manager_get_new_field', false );
function rcl_manager_get_new_field() {
	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json( array(
			'error' => esc_html__( 'Error', 'wp-recall' )
		) );
	}
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$managerProps = isset( $_POST['props'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['props'] ) ) : [];

	$Manager = new Rcl_Fields_Manager( $managerProps['manager_id'], $managerProps );

	$field_id = 'newField-' . rand( 1, 10000 );

	$Manager->add_field( array(
		'slug' => $field_id,
		'type' => $Manager->types[0],
		'_new' => true
	) );

	wp_send_json( array(
		'content' => $Manager->get_field_manager( $field_id )
	) );
}

rcl_ajax_action( 'rcl_manager_get_custom_field_options', false );
function rcl_manager_get_custom_field_options() {

	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json( array(
			'error' => esc_html__( 'Error', 'wp-recall' )
		) );
	}

	$new_type = isset( $_POST['newType'] ) ? sanitize_key( $_POST['newType'] ) : '';
	$field_id = isset( $_POST['fieldId'] ) ? sanitize_text_field( wp_unslash( $_POST['fieldId'] ) ) : '';
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$managerProps = isset( $_POST['manager'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['manager'] ) ) : [];

	$Manager = new Rcl_Fields_Manager( $managerProps['manager_id'], $managerProps );

	if ( stristr( $field_id, 'newField' ) !== false ) {

		$Manager->add_field( array(
			'slug' => $field_id,
			'type' => $new_type,
			'_new' => true
		) );
	} else {

		$Manager->set_field_prop( $field_id, 'type', $new_type );

		$Manager->fields[ $field_id ] = $Manager::setup( ( array ) $Manager->fields[ $field_id ] );
	}

	$content = $Manager->get_field_options_content( $field_id );

	$multiVars = array(
		'select',
		'radio',
		'checkbox',
		'multiselect'
	);

	if ( in_array( $new_type, $multiVars ) ) {

		$content .= $Manager->sortable_dynamic_values_script( $field_id );
	}

	wp_send_json( array(
		'content' => $content
	) );
}

rcl_ajax_action( 'rcl_manager_get_new_area', false );
function rcl_manager_get_new_area() {

	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json( array(
			'error' => esc_html__( 'Error', 'wp-recall' )
		) );
	}

	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$managerProps = isset( $_POST['props'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['props'] ) ) : [];

	$Manager = new Rcl_Fields_Manager( 'any', $managerProps );

	wp_send_json( array(
		'content' => $Manager->get_active_area()
	) );
}

rcl_ajax_action( 'rcl_manager_get_new_group', false );
function rcl_manager_get_new_group() {

	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json( array(
			'error' => esc_html__( 'Error', 'wp-recall' )
		) );
	}

	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$managerProps = isset( $_POST['props'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['props'] ) ) : [];

	$Manager = new Rcl_Fields_Manager( 'any', $managerProps );

	wp_send_json( array(
		'content' => $Manager->get_group_areas()
	) );
}

rcl_ajax_action( 'rcl_manager_update_fields_by_ajax', false );
function rcl_manager_update_fields_by_ajax() {

	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json( [ 'error' => __( 'Error', 'wp-recall' ) ] );
	}

	rcl_verify_ajax_nonce();

	$args = rcl_manager_update_data_fields();

	wp_send_json( $args );
}

add_action( 'admin_init', 'rcl_manager_update_fields_by_post', 10 );
function rcl_manager_update_fields_by_post() {
	global $wpdb;

	if ( ! isset( $_POST['rcl_manager_update_fields_by_post'] ) || ! isset( $_POST['_wpnonce'] ) ) {
		return false;
	}

	if ( ! current_user_can( 'administrator' ) ) {
		return false;
	}

	if ( ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'rcl-update-custom-fields' ) ) {
		return false;
	}

	rcl_manager_update_data_fields();
	if ( isset( $_POST['_wp_http_referer'] ) ) {
		wp_safe_redirect( wp_unslash( $_POST['_wp_http_referer'] ) );
	}
	exit;
}

function rcl_manager_update_data_fields() {
	global $wpdb;

	$copy        = isset( $_POST['copy'] ) ? sanitize_text_field( wp_unslash( $_POST['copy'] ) ) : '';
	$manager_id  = isset( $_POST['manager_id'] ) ? sanitize_key( $_POST['manager_id'] ) : '';
	$option_name = isset( $_POST['option_name'] ) ? sanitize_key( $_POST['option_name'] ) : '';
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$fieldsData = isset( $_POST['fields'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['fields'] ) ) : [];
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$structure = isset( $_POST['structure'] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['structure'] ) ) : false;

	$fields    = array();
	$keyFields = array();
	$changeIds = array();
	$isset_new = false;

	foreach ( $fieldsData as $field_id => $field ) {

		if ( ! $field['title'] ) {
			continue;
		}

		if ( isset( $field['values'] ) ) {
			//удаляем из массива values пустые значения
			$values = array();
			foreach ( $field['values'] as $k => $v ) {
				if ( $v == '' ) {
					continue;
				}
				$values[ $k ] = $v;
			}
			$field['values'] = $values;
		}

		if ( stristr( $field_id, 'newField' ) !== false ) {

			$isset_new = true;

			$old_id = $field_id;

			if ( ! $field['id'] ) {

				$field_id = str_replace( array(
					'-',
					' '
				), '_', rcl_sanitize_string( $field['title'] ) . '-' . rand( 1, 100 ) );
			} else {
				$field_id = $field['id'];
			}

			$changeIds[ $old_id ] = $field_id;
		}

		$field['slug'] = $field_id;

		$keyFields[ $field_id ] = 1;

		unset( $field['id'] );

		$fields[] = $field;
	}

	if ( $structure ) {

		$strArray = array();
		$area_id  = - 1;
		$group_id = 0;
		foreach ( $structure as $value ) {

			if ( is_array( $value ) ) {

				if ( isset( $value['group_id'] ) ) {
					$group_id = $value['group_id'];
					//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
					$strArray[ $group_id ] = isset( $_POST['structure-groups'][ $group_id ] ) ? rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['structure-groups'][ $group_id ] ) ) : array();
				} else if ( isset( $value['field_id'] ) ) {
					$strArray[ $group_id ]['areas'][ $area_id ]['fields'][] = $value['field_id'];
				}
			} else {
				$area_id ++;
				$strArray[ $group_id ]['areas'][ $area_id ]['width'] = isset( $_POST['structure-areas'][ $area_id ]['width'] ) ? intval( $_POST['structure-areas'][ $area_id ]['width'] ) : 0;
			}
		}

		$endStructure = array();

		foreach ( $strArray as $group_id => $group ) {

			if ( isset( $group['id'] ) && $group_id != $group['id'] ) {
				$group_id = $group['id'];
			}

			$endStructure[ $group_id ]          = $group;
			$endStructure[ $group_id ]['areas'] = array();

			foreach ( $group['areas'] as $area ) {

				$fieldsArea = array();

				if ( ! empty( $area['fields'] ) ) {
					foreach ( $area['fields'] as $k => $field_id ) {

						if ( isset( $changeIds[ $field_id ] ) ) {
							$field_id = $changeIds[ $field_id ];
						}

						if ( ! isset( $keyFields[ $field_id ] ) ) {
							unset( $area['fields'][ $k ] );
							continue;
						}

						$fieldsArea[] = $field_id;
					}
				}

				$endStructure[ $group_id ]['areas'][] = array(
					'width'  => round( $area['width'], 0 ),
					'fields' => $fieldsArea
				);
			}
		}

		$structure = $endStructure;
	}

	$fields = apply_filters( 'rcl_pre_update_manager_fields', $fields, $manager_id );

	update_site_option( $option_name, $fields );

	$args = array(
		'success' => esc_html__( 'Settings saved!', 'wp-recall' )
	);

	if ( $structure ) {
		update_site_option( 'rcl_fields_' . $manager_id . '_structure', $structure );
	} else {
		delete_site_option( 'rcl_fields_' . $manager_id . '_structure' );
	}

	if ( ! empty( $_POST['deleted_fields'] ) && isset( $_POST['delete_table_data'] ) ) {
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$deleteFields = rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['delete_table_data'] ) );
		if ( ! empty( $deleteFields ) ) {
			foreach ( $deleteFields as $table_name => $colname ) {
				//phpcs:disable
				$fields_to_delete = rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['deleted_fields'] ) );
				$wpdb->query( "DELETE FROM $table_name WHERE $colname IN ('" . implode( "','", $fields_to_delete ) . "')" );
				//phpcs:enable
			}

			$args['reload'] = true;
		}
	}

	if ( $copy ) {

		update_site_option( 'rcl_fields_' . $copy, $fields );

		if ( $structure ) {
			update_site_option( 'rcl_fields_' . $copy . '_structure', $structure );
		}

		do_action( 'rcl_fields_copy', $fields, $manager_id, $copy );

		$args['reload'] = true;
	}

	if ( $isset_new ) {
		$args['reload'] = true;
	}

	do_action( 'rcl_fields_update', $fields, $manager_id );

	return $args;
}

/* new fields manager functions end */

