<?php

class Rcl_Fields_Manager extends Rcl_Fields {

	public $manager_id = false;
	//public $fields = array();
	public $option_name = '';
	public $structure_edit = false;
	public $template_fields = false;
	public $default_fields = array();
	public $default_is_null = false;
	public $sortable = true;
	public $empty_field = true;
	public $create_field = true;
	public $switch_id = false;
	public $switch_type = true;
	public $fields_delete = true;
	public $field_options = array();
	public $new_field_options = array();
	public $new_field_type = false;
	public $default_box = true;
	public $meta_delete = false;
	public $current_item = 0;
	public $group_id = 0;
	public $onsubmit = 'rcl_manager_update_fields';
	public $types = array(
		'text',
		'textarea',
		'select',
		'multiselect',
		'checkbox',
		'radio',
		'email',
		'tel',
		'number',
		'date',
		'time',
		'url',
		'agree',
		'file',
		'dynamic',
		'runner',
		'range',
		'editor',
		'uploader'
	);

	function __construct( $manager_id, $args = false ) {

		rcl_dialog_scripts();

		rcl_iconpicker();

		$this->manager_id = $manager_id;

		$this->init_properties( $args );

		if ( $this->sortable ) {
			rcl_sortable_scripts();
		}

		//if ( $this->structure_edit )
		rcl_resizable_scripts();

		if ( ! $this->option_name ) {
			$this->option_name = 'rcl_fields_' . $this->manager_id;
		}

		$fields = apply_filters( 'rcl_custom_fields', $this->get_active_fields(), $this->manager_id );

		parent::__construct( $fields, $this->get_structure() );

		$this->setup_active_fields();

		if ( $this->template_fields ) {
			$this->setup_template_fields();
		}
	}

	function init_properties( $args ) {

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = is_bool( $args[ $name ] ) ? ( boolean ) $args[ $name ] : $args[ $name ];
			}
		}
	}

	function setup_template_fields( $fields = false ) {

		if ( ! $fields ) {
			$fields = $this->get_template_fields();
		}

		if ( ! $fields || ! is_array( $fields ) ) {
			return false;
		}

		$template_fields = array();

		foreach ( $fields as $field ) {

			if ( ! $field ) {
				continue;
			}

			$template_fields[ $field['slug'] ] = $this::setup( $field );
		}

		if ( $template_fields ) {
			$this->template_fields = $template_fields;
		}
	}

	function get_template_fields() {
		return apply_filters( 'rcl_template_fields_manager', $this->template_fields, $this->manager_id );
	}

	function setup_default_fields( $fields = false ) {

		if ( ! $fields ) {
			$fields = $this->get_default_fields();
		}

		if ( ! $fields ) {
			return false;
		}

		$default_fields = array();

		foreach ( $fields as $field ) {

			if ( ! $field ) {
				continue;
			}

			$default_fields[ $field['slug'] ] = $this::setup( $field );
			//$this->add_field($field, true);

			if ( ! $this->default_box && ! $this->is_active_field( $field['slug'] ) ) {
				$this->add_field( $field );
			}
		}

		if ( $default_fields ) {
			$this->default_fields = $default_fields;
		}

		if ( ! $this->fields && $this->default_is_null ) {

			$this->fields = $this->default_fields;

			$this->setup_structure( true );
		}
	}

	function setup_active_fields() {

		$fields = $this->get_default_fields();

		if ( ! $fields ) {
			return false;
		}

		foreach ( $fields as $field ) {

			if ( ! $field || ! $this->is_active_field( $field['slug'] ) ) {
				continue;
			}


			if ( ! isset( $field['options'] ) ) {
				continue;
			}

			$activeField = $this->get_field( $field['slug'] );

			$activeField->set_prop( 'options', $field['options'] );
		}
	}

	function setup_fields( $fields ) {
		if ( is_array( $fields ) ) {
			parent::__construct( $fields );
		}
	}

	function get_active_fields() {

		/* $name_option = 'rcl_fields_'.$this->manager_id;

		  if(!$fields = get_site_option($name_option)){

		  switch($this->manager_id){
		  case 'post': $fields = get_site_option('rcl_fields_post_1'); break;
		  case 'orderform': $fields = get_site_option('rcl_cart_fields'); break;
		  case 'profile': $fields = get_site_option('rcl_profile_fields'); break;
		  }

		  } */

		return apply_filters( $this->option_name . '_in_manager', get_site_option( $this->option_name ) );
	}

	function get_structure() {
		if ( ! $this->structure_edit ) {
			return false;
		}

		return get_site_option( 'rcl_fields_' . $this->manager_id . '_structure' );
	}

	function get_field( $field_id, $serviceType = false ) {
		if ( ! $serviceType ) {
			return isset( $this->fields[ $field_id ] ) ? $this->fields[ $field_id ] : false;
		} else if ( $serviceType == 'default' ) {
			return $this->default_fields[ $field_id ];
		} else if ( $serviceType == 'template' ) {
			return $this->template_fields[ $field_id ];
		}
	}

	function add_field( $args, $serviceType = false ) {
		if ( $serviceType ) {
			$this->default_fields[ $args['slug'] ] = $this::setup( $args );
		} else {
			$this->fields[ $args['slug'] ] = $this::setup( $args );
		}
	}

	function set_field_prop( $field_id, $propName, $propValue, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		$field->$propName = $propValue;

		if ( $serviceType ) {
			$this->default_fields[ $field_id ] = $field;
		} else {
			$this->fields[ $field_id ] = $field;
		}
	}

	function isset_field_prop( $field_id, $propName, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		if ( ! $field ) {
			return false;
		}

		return isset( $field->$propName );
	}

	function get_field_prop( $field_id, $propName, $serviceType = false ) {

		if ( ! $this->isset_field_prop( $field_id, $propName, $serviceType ) ) {
			return false;
		}

		$field = $this->get_field( $field_id, $serviceType );

		return $field->$propName;
	}

	function get_manager() {

		$content = '<div class="rcl-fields-manager ' . ( $this->structure_edit ? 'structure-edit' : 'structure-simple' ) . '">';

		if ( $this->meta_delete ) {
			$content .= '<span id="rcl-manager-confirm-delete" class="rcl-hide">' . __( 'To delete a data adding this field?', 'wp-recall' ) . '</span>';
		}

		if ( $this->template_fields ) {
			$content .= '<div class="rcl-manager-box service-box">';
			$content .= '<span class="manager-title">' . __( 'Templates', 'wp-recall' ) . '</span>';
			$content .= $this->get_service_box();
			$content .= '</div>';
		}

		if ( $this->default_fields && $this->default_box ) {
			$content .= '<div class="rcl-manager-box default-box">';
			$content .= '<span class="manager-title">' . __( 'Inactive fields', 'wp-recall' ) . '</span>';
			$content .= $this->get_default_box();
			$content .= '</div>';
		}

		$content .= '<div class="rcl-manager-box rcl-custom-fields-box">';
		$content .= '<span class="manager-title">' . __( 'Active fields', 'wp-recall' ) . '</span>';
		$content .= '<form method="post" action="" class="rcl-fields-manager-form" ' . ( $this->onsubmit ? 'onsubmit="' . $this->onsubmit . '();return false;"' : '' ) . '>';

		$content .= $this->get_manager_options_form();

		$content .= '<div class="rcl-manager-groups preloader-parent">';

		foreach ( $this->structure as $group_id => $group ) {
			$content .= $this->get_group_areas( $group );
		}

		$content .= '</div>';

		$content .= $this->get_submit_box();
		$content .= '<input type="hidden" name="manager_id" value="' . $this->manager_id . '">';
		$content .= '<input type="hidden" name="option_name" value="' . $this->option_name . '">';

		if ( ! $this->onsubmit ) {
			$content .= wp_nonce_field( 'rcl-update-custom-fields', '_wpnonce', true, false );
			$content .= '<input type="hidden" name="rcl_manager_update_fields_by_post" value="1">';
		}

		$content .= '</form>';
		$content .= '</div>';

		$content .= '</div>';

		if ( $this->sortable ) {
			$content .= $this->sortable_fields_script();
		}

		$content .= $this->resizable_areas_script();

		$content .= $this->sortable_dynamic_values_script();

		$props = get_object_vars( $this );

		unset( $props['fields'] );
		unset( $props['default_fields'] );
		unset( $props['field_options'] );

		$content .= "<script>rcl_init_manager_fields(" . json_encode( $props ) . ");</script>";

		return $content;
	}

	function get_manager_options_form() {

		$fields = $this->get_manager_options_form_fields();

		if ( ! $fields ) {
			return false;
		}

		$content = '<div class="rcl-manager-options">';
		foreach ( $fields as $field ) {
			$content .= $this::setup( $field )->get_field_html();
		}
		$content .= '</div>';

		return $content;
	}

	function get_manager_options_form_fields() {
		return array();
	}

	function get_group_areas( $group = array() ) {

		$group = wp_parse_args( $group, array(
			'title' => '',
			'id'    => 'section-' . rand( 100, 10000 ),
			'type'  => 0,
			'areas' => array(
				array(
					'fields' => array()
				)
			)
		) );

		$content = '<div id="manager-group-' . $this->group_id . '" class="manager-group">';

		if ( $this->structure_edit ) {

			$this->group_id = $group['id'];

			$content .= '<input type="hidden" name="structure[][group_id]" value="' . $this->group_id . '">';

			$content .= '<div class="group-header">';

			$content .= '<div class="group-primary-settings">';

			$content .= '<div class="group-title-field">';
			$content .= $this::setup( array(
				'slug'        => 'group-title',
				'type'        => 'text',
				'input_name'  => 'structure-groups[' . $this->group_id . '][title]',
				'placeholder' => __( 'Name of the section', 'wp-recall' ),
				'value'       => $group['title']
			) )->get_field_html();
			$content .= '</div>';

			$content .= '<div class="rcl-areas-manager">';

			//if ( count( $this->structure ) > 1 ) {
			$content .= rcl_get_button( [
				'size'    => 'medium',
				'type'    => 'clear',
				'title'   => __( 'Delete section', 'wp-recall' ),
				'icon'    => 'fa-trash',
				'class'   => 'group-manager-button group-delete',
				'onclick' => 'rcl_remove_manager_group("' . __( 'Are you sure?', 'wp-recall' ) . '",this);return false;',
			] );
			//}

			$content .= rcl_get_button( [
				'size'    => 'medium',
				'type'    => 'clear',
				'title'   => __( 'Settings of section', 'wp-recall' ),
				'icon'    => 'fa-sliders',
				'class'   => 'group-manager-button group-settings',
				'onclick' => 'rcl_switch_view_settings_manager_group(this);return false;',
			] );

			$content .= rcl_get_button( [
				'size'    => 'medium',
				'type'    => 'clear',
				'label'   => __( 'Add a group of fields', 'wp-recall' ),
				'icon'    => 'fa-plus',
				'class'   => 'group-manager-button add-area',
				'onclick' => 'rcl_manager_get_new_area(this);return false;',
			] );

			$content .= '</div>';

			$content .= '</div>';

			$fields = array(
				'group-id'     => array(
					'slug'       => 'group-id',
					'type'       => 'text',
					'input_name' => 'structure-groups[' . $this->group_id . '][id]',
					'title'      => 'ID секции',
					'required'   => true,
					'value'      => $this->group_id
				),
				'group-notice' => array(
					'slug'       => 'group-notice',
					'type'       => 'text',
					'input_name' => 'structure-groups[' . $this->group_id . '][notice]',
					'title'      => __( 'A note of this section', 'wp-recall' ),
					'value'      => isset( $group['notice'] ) ? $group['notice'] : ''
				)
			);

			$content .= '<div class="manager-group-settings">';
			foreach ( $fields as $field ) {
				$content .= $this::setup( $field )->get_field_html();
			}
			$content .= '</div>';

			$content .= '</div>';
		}

		$content .= '<div class="manager-group-areas preloader-parent">';

		foreach ( $group['areas'] as $area ) {
			$content .= $this->get_active_area( $area );
		}

		$content .= '</div>';
		$content .= '</div>';

		return $content;
	}

	function get_active_area( $area = array() ) {

		if ( $this->empty_field ) {

			$this->add_field( array(
				'slug' => 'newField-' . rand( 1, 10000 ),
				'type' => $this->types[0],
				'_new' => true
			) );
		}

		$widthArea = isset( $area['width'] ) && $area['width'] ? $area['width'] : 100;

		$content = '<div class="manager-area preloader-parent" style="width:' . ( $widthArea ? $widthArea . '%' : 'auto' ) . ';">';

		if ( $this->structure_edit ) {

			$content .= '<div class="area-width-content">' . $widthArea . '</div>';

			$content .= '<input type="hidden" name="structure[]" value="area">';
			$content .= '<input type="hidden" class="area-width" name="structure-areas[][width]" value="' . $widthArea . '">';
		}

		$content .= '<div class="area-content">';

		if ( $this->structure_edit ) {

			$content .= '<div class="rcl-areas-manager">';
			$content .= '<a href="#" title="' . __( 'Delete group of fields', 'wp-recall' ) . '"onclick="rcl_remove_manager_area(\'' . __( 'Are you sure?', 'wp-recall' ) . '\',this);return false"><i class="rcli fa-trash" aria-hidden="true"></i></a>';

			if ( $this->sortable ) {
				$content .= '<span class="area-move left-align"><i class="rcli fa-arrows" aria-hidden="true"></i></span>';
			}
			/* if ( $this->create_field ) {
			  $content .= '<a href="#" onclick="rcl_manager_get_new_field(this);return false;" title="' . __( 'Добавить поле', 'wp-recall' ) . '" class="add-field left-align"><i class="rcli fa-plus-square" aria-hidden="true"></i> ' . __( 'Добавить поле', 'wp-recall' ) . '</a>';
			  } */
			$content .= '</div>';
		}

		$content .= '<div class="rcl-active-fields fields-box">';

		if ( $this->fields ) {

			if ( $this->structure_edit ) {

				if ( isset( $area['fields'] ) && $area['fields'] ) {
					foreach ( $area['fields'] as $field_id ) {
						if ( ! $this->is_active_field( $field_id ) ) {
							continue;
						}

						$content .= $this->get_field_manager( $field_id );
					}
				}
			} else {

				foreach ( $this->fields as $field_id => $field ) {
					if ( ! $this->is_active_field( $field_id ) ) {
						continue;
					}

					$content .= $this->get_field_manager( $field_id, false );
				}
			}
		}

		$content .= '</div>';

		$content .= "<div class=submit-box>";

		if ( $this->create_field ) {
			$content .= "<input type=button onclick='rcl_manager_get_new_field(this);' class='add-field-button button-secondary right' value='+ " . __( 'Add field', 'wp-recall' ) . "'>";
		}

		$content .= "</div>";

		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

	function get_submit_box() {

		$content = "<div class=submit-box>";

		if ( $this->structure_edit ) {
			$content .= "<input type=button onclick='rcl_manager_get_new_group(this);' class='add-field-button button-secondary right' value='+ " . __( 'Add new section', 'wp-recall' ) . "'>";
		}

		$content .= "<input class='button button-primary' type=submit value='" . __( 'Save', 'wp-recall' ) . "' name='rcl_save_custom_fields'>";

		if ( $this->meta_delete ) {
			foreach ( $this->meta_delete as $table_name => $colname ) {
				$content .= "<input type=hidden name=delete_table_data[$table_name] value='$colname'>";
			}

			$content .= "<div id='field-delete-confirm' style='display:none;'>" . __( 'To remove the data added to this field?', 'wp-recall' ) . "</div>";
		}

		$content .= "</div>";

		return $content;
	}

	function get_default_box() {

		if ( ! $this->default_fields ) {
			return false;
		}

		$content = '<div class="rcl-service-fields rcl-default-fields fields-box">';

		foreach ( $this->default_fields as $field_id => $field ) {

			if ( $this->is_active_field( $field_id ) ) {
				continue;
			}

			$content .= $this->get_field_manager( $field_id, 'default' );
		}

		$content .= '</div>';

		return $content;
	}

	function get_service_box() {

		if ( ! $this->template_fields ) {
			return false;
		}

		$content = '<div class="rcl-service-fields rcl-template-fields fields-box">';

		foreach ( $this->template_fields as $field_id => $field ) {
			$content .= $this->get_field_manager( $field_id, 'template' );
		}

		$content .= '</div>';

		return $content;
	}

	function get_default_fields() {
		return apply_filters( 'rcl_default_fields_manager', $this->default_fields, $this->manager_id );
	}

	function get_field_manager( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		if ( ! $field ) {
			return false;
		}

		$classes = array( 'manager-field' );

		if ( $this->is_service_type( $field_id, 'default' ) ) {
			$classes[] = 'default-field';
		} else if ( $this->is_service_type( $field_id, 'template' ) ) {
			$classes[] = 'template-field';
		}

		if ( $this->meta_delete ) {
			$classes[] = 'must-meta-delete';
		}

		$content = '<div id="manager-field-' . $field_id . '" class="' . implode( ' ', $classes ) . '" data-type="' . $field->type . '" data-id="' . $field_id . '">';

		if ( $this->structure_edit ) {
			$content .= '<input type="hidden" name="structure[][field_id]" value="' . $field_id . '">';
		}

		$content .= $this->get_field_header( $field_id, $serviceType );

		$content .= $this->get_field_options_box( $field_id, $serviceType );

		$content .= '</div>';

		return $content;
	}

	function setup_options( $options, $field_id, $serviceType = false ) {

		if ( ! $options ) {
			return null;
		}

		$field = $this->get_field( $field_id, $serviceType );

		foreach ( $options as $k => $option ) {

			$option_id = $option['slug'];

			if ( ! isset( $option['input_name'] ) ) {
				$options[ $k ]['input_name'] = 'fields[' . $field_id . '][' . $option['slug'] . ']';
			}

			if ( ! isset( $option['value'] ) && isset( $field->$option_id ) ) {
				$options[ $k ]['value'] = $field->$option_id;
			}
		}

		return $options;
	}

	function get_field_header( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		$content = '<div class="manager-field-header">';
		$content .= '<span class="field-icon icon-type-' . $field->type . '"></span>';

		if ( $field->is_new() ) {
			$content .= $this::setup( array(
				'slug'        => 'title',
				'type'        => 'text',
				'placeholder' => __( 'Point a title of new field', 'wp-recall' ),
				'input_name'  => 'fields[' . $field_id . '][title]'
			) )->get_field_html();
		} else {
			$content .= $this::setup( array(
				'slug'        => 'title',
				'type'        => 'text',
				'placeholder' => __( 'Point a title of this field', 'wp-recall' ),
				'input_name'  => 'fields[' . $field_id . '][title]',
				'value'       => $field->title
			) )->get_field_html();
			//$content .= '<span class="field-title">'.$field->title.'</span>';
		}

		$buttons = $this->get_control_buttons( $field_id, $field );

		if ( $buttons ) {
			$content .= '<span class="field-control">';

			foreach ( $buttons as $button ) {
				$content .= rcl_get_button( $button );
			}

			$content .= '</span>';
		}

		$content .= '</div>';

		return $content;
	}

	function get_control_buttons( $field_id, $field ) {

		$buttons = array();

		if ( $field->must_delete && $this->fields_delete && ! $this->is_service_type( $field_id, 'default' ) && ! $field->is_new() ) {
			$buttons['delete'] = array(
				'icon'    => 'fa-trash',
				'class'   => 'control-delete',
				'onclick' => 'rcl_manager_field_delete("' . $field_id . '", ' . ( $this->meta_delete ? 1 : 0 ) . ', this);return false;',
			);
		}

		$buttons['edit'] = array(
			'class'   => 'control-edit',
			'icon'    => 'fa-sliders',
			'onclick' => 'rcl_manager_field_switch(this);return false;'
		);

		if ( $this->sortable ) {
			$buttons['sortable'] = array(
				'class' => 'control-move',
				'icon'  => 'fa-arrows'
			);
		}

		return apply_filters( 'rcl_manager_field_controls', $buttons, $field_id, $this->manager_id );
	}

	function get_field_options_box( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		$content = '<div class="manager-field-settings">';

		if ( ! $field->is_new() ) {
			$content .= '<span class="field-id">' . __( 'ID', 'wp-recall' ) . ': ' . $field_id . '</span>';
		}

		$content .= $this->get_field_general_options_content( $field_id, $serviceType );

		$content .= $this->get_field_options_content( $field_id, $serviceType );

		$content .= '</div>';

		return $content;
	}

	function get_field_general_options_content( $field_id, $serviceType = false ) {

		$options = $this->get_field_general_options( $field_id, $serviceType );

		if ( ! $options ) {
			return false;
		}

		$content = '<div class="field-primary-options">';

		foreach ( $options as $option ) {
			$content .= $this::setup( $option )->get_field_html();
		}

		$content .= '</div>';

		return $content;
	}

	function get_field_options_content( $field_id, $serviceType = false ) {

		$options = $this->get_field_options( $field_id, $serviceType );

		$content = '<div class="field-secondary-options">';

		foreach ( $options as $option ) {
			$content .= $this::setup( $option )->get_field_html();
		}

		$content .= '</div>';

		return $content;
	}

	function get_field_general_options( $field_id, $serviceType = false ) {

		$field = $this->get_field( $field_id, $serviceType );

		if ( $field->is_new() || $this->switch_id ) {
			$options['id'] = array(
				'slug'        => 'id',
				'type'        => 'text',
				'pattern'     => '[a-z0-9-_]+',
				'value'       => $field->is_new() ? '' : $field_id,
				'title'       => __( 'ID', 'wp-recall' ),
				'notice'      => __( 'not required, but you can list your own meta_key in this field', 'wp-recall' ),
				'placeholder' => __( 'Latin letters and numbers', 'wp-recall' )
			);
		}

		if ( $this->switch_type ) {

			if ( $typeList = $this->get_types_list() ) {

				if ( $this->is_service_type( $field_id ) || ! isset( $typeList[ $field->type ] ) ) {
					//для дефолтных полей устанавливаем фиксированный тип
					$options['type'] = array(
						'slug'  => 'type',
						'type'  => 'hidden',
						'value' => $field->type
					);
				} else {
					$options['type'] = array(
						'slug'   => 'type',
						'type'   => 'select',
						'title'  => __( 'Type of field', 'wp-recall' ),
						'values' => $typeList
					);
				}
			}
		} else {

			$options['type'] = array(
				'slug'  => 'type',
				'type'  => 'hidden',
				'value' => ( $field->is_new() && $this->new_field_type ) ? $this->new_field_type : $field->type
			);
		}

		$options = apply_filters( 'rcl_field_general_options', $options, $field, $this->manager_id );

		return $this->setup_options( $options, $field_id, $serviceType );
	}

	function get_field_options( $field_id, $serviceType = false ) {

		$options = array();

		$field = $this->get_field( $field_id, $serviceType );

		$fieldOptions = $field->get_options();

		if ( $fieldOptions ) {
			foreach ( $fieldOptions as $option ) {
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( $this->field_options ) {

			foreach ( $this->field_options as $option ) {
				$option                     = ( array ) $option;
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( $field->is_new() && $this->new_field_options ) {

			foreach ( $this->new_field_options as $option ) {
				$option                     = ( array ) $option;
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( isset( $field->options ) ) {
			foreach ( $field->options as $option ) {
				$options[ $option['slug'] ] = $option;
			}
		}

		if ( ! $serviceType && $this->is_default_field( $field_id ) ) {
			//для поля в активной зоне добавляем опции,
			//которые были определены для дефолтного поля,
			//если такое есть
			$defaultField = $this->get_field( $field_id, 1 );

			if ( isset( $defaultField->options ) ) {

				foreach ( $defaultField->options as $option ) {
					$options[ $option['slug'] ] = $option;
				}
			}
		}

		$options = apply_filters( 'rcl_field_options', $options, $field, $this->manager_id, $this );

		return $this->setup_options( $options, $field_id, false );
	}

	function sortable_fields_script() {
		return '<script>jQuery(window).on("load", function() { rcl_init_manager_sortable(); });</script>';
	}

	function resizable_areas_script() {
		return '<script>jQuery(window).on("load", function() { rcl_init_manager_areas_resizable(); });</script>';
	}

	function sortable_dynamic_values_script( $field_id = false ) {

		return '<script>
				jQuery(function(){
					jQuery("' . ( $field_id ? "#manager-field-" . $field_id . " " : '' ) . '.rcl-field-input .dynamic-values").sortable({
						containment: "parent",
						placeholder: "ui-sortable-placeholder",
						distance: 15,
						stop: function( event, ui ) {

							var items = ui.item.parents(".dynamic-values").find(".dynamic-value");

							items.each(function(f){
								if(items.length == (f+1)){
									jQuery(this).children("a").attr("onclick","rcl_add_dynamic_field(this);return false;").children("i").attr("class","rcl-bttn__ico rcl-bttn__ico-left rcli fa-plus");
								}else{
									jQuery(this).children("a").attr("onclick","rcl_remove_dynamic_field(this);return false;").children("i").attr("class","rcl-bttn__ico rcl-bttn__ico-left rcli fa-minus");
								}
							});

						}
					});
				});
			</script>';
	}

	function is_service_type( $field_id, $serviceType = array( 'default', 'template' ) ) {

		if ( is_array( $serviceType ) ) {

			if ( in_array( 'default', $serviceType ) && isset( $this->default_fields[ $field_id ] ) ) {
				return true;
			} else if ( in_array( 'template', $serviceType ) && isset( $this->template_fields[ $field_id ] ) ) {
				return true;
			}

			return false;
		}

		if ( $serviceType == 'default' ) {
			return isset( $this->default_fields[ $field_id ] );
		} else if ( $serviceType == 'template' ) {
			return isset( $this->template_fields[ $field_id ] );
		}
	}

	function is_active_field( $field_id ) {
		return isset( $this->fields[ $field_id ] );
	}

	function is_default_field( $field_id ) {
		return $this->is_service_type( $field_id, 'default' );
	}

	function get_types_list() {
		global $wprecall;

		$typesList = array();
		foreach ( $this->types as $type ) {
			if ( ! isset( $wprecall->fields[ $type ] ) ) {
				continue;
			}
			$typesList[ $type ] = $wprecall->fields[ $type ]['label'];
		}

		return apply_filters( 'rcl_field_types_manager_' . $this->manager_id, $typesList );
	}

}
