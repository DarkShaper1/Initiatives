<?php

class Rcl_Public_Form extends Rcl_Public_Form_Fields {

	public $post_id = 0;
	public $post_type = 'post';
	public $fields_options;
	public $form_object;
	public $post;
	public $form_id;
	public $current_field = array();
	public $options = array(
		'preview' => 1,
		'draft'   => 1,
		'delete'  => 1
	);
	public $user_can = array(
		'upload'  => false,
		'publish' => false,
		'delete'  => false,
		'draft'   => false,
		'edit'    => false
	);
	public $core_fields = array(
		'post_content',
		'post_title',
		'post_uploader',
		'post_excerpt',
		'post_thumbnail'
	);
	public $tax_fields = array();

	function __construct( $args = false ) {
		global $user_ID;

		$this->init_properties( $args );

		if ( isset( $_GET['rcl-post-edit'] ) ) {
			$this->post_id = intval( $_GET['rcl-post-edit'] );
		}

		if ( $this->post_id ) {

			$this->post      = get_post( $this->post_id );
			$this->post_type = $this->post->post_type;
			$this->form_id   = get_post_meta( $this->post_id, 'publicform-id', 1 );
		}

		if ( ! $this->form_id ) {
			$this->form_id = 1;
		}

		$this->setup_user_can();

		if ( $this->user_can['publish'] && ! $user_ID ) {
			add_filter( 'rcl_public_form_fields', array( $this, 'add_guest_fields' ), 10 );
		}

		add_filter( 'rcl_custom_fields', array( $this, 'init_public_form_fields_filter' ), 10 );

		parent::__construct( $this->post_type, array(
			'form_id' => $this->form_id
		) );

		$this->init_options();

		do_action( 'rcl_public_form_init', $this->get_object_form() );

		if ( $this->options['preview'] ) {
			rcl_dialog_scripts();
		}

		if ( $this->user_can['upload'] ) {
			rcl_fileupload_scripts();
			add_action( 'wp_footer', array( $this, 'init_form_scripts' ), 100 );
		}

		$this->form_object = $this->get_object_form();

		do_action( 'rcl_pre_get_public_form', $this );
	}

	function init_public_form_fields_filter( $fields ) {
		return apply_filters( 'rcl_public_form_fields', $fields, $this->get_object_form(), $this );
	}

	function init_properties( $args ) {
		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}
	}

	function get_object_form() {

		$dataForm = array();

		$dataForm['post_id']      = $this->post_id;
		$dataForm['post_type']    = $this->post_type;
		$dataForm['post_status']  = ( $this->post_id ) ? $this->post->post_status : 'new';
		$dataForm['post_content'] = ( $this->post_id ) ? $this->post->post_content : '';
		$dataForm['post_excerpt'] = ( $this->post_id ) ? $this->post->post_excerpt : '';
		$dataForm['post_title']   = ( $this->post_id ) ? $this->post->post_title : '';

		return ( object ) $dataForm;
	}

	function add_guest_fields( $fields ) {

		$guestFields = array(
			array(
				'slug'     => 'name-user',
				'title'    => __( 'Your Name', 'wp-recall' ),
				'required' => 1,
				'type'     => 'text'
			),
			array(
				'slug'     => 'email-user',
				'title'    => __( 'Your E-mail', 'wp-recall' ),
				'required' => 1,
				'type'     => 'email'
			)
		);

		return array_merge( $guestFields, $fields );
	}

	function init_options() {

		$this->options['preview'] = rcl_get_option( 'public_preview' );
		$this->options['draft']   = rcl_get_option( 'public_draft' );

		$this->options = apply_filters( 'rcl_public_form_options', $this->options, $this->get_object_form() );
	}

	function setup_user_can() {
		global $user_ID;

		$this->user_can['publish'] = true;

		$user_can = rcl_get_option( 'user_public_access_recall' );

		if ( $user_can ) {

			if ( $user_ID ) {

				$userinfo = get_userdata( $user_ID );

				if ( $userinfo->user_level >= $user_can ) {
					$this->user_can['publish'] = true;
				} else {
					$this->user_can['publish'] = false;
				}
			} else {

				$this->user_can['publish'] = false;
			}
		}

		$this->user_can['draft'] = $user_ID ? true : false;

		$this->user_can['upload'] = $this->user_can['publish'];

		if ( $user_ID && $this->post_id ) {

			$this->user_can['edit'] = current_user_can( 'edit_post', $this->post_id );

			if ( ! $this->user_can['edit'] && $this->post_type == 'post-group' ) {

				$this->user_can['edit'] = rcl_can_user_edit_post_group( $this->post_id );
			}

			$this->user_can['delete'] = $this->user_can['edit'];
		}

		$this->user_can = apply_filters( 'rcl_public_form_user_can', $this->user_can, $this->get_object_form() );
	}

	function get_errors() {
		global $user_ID;

		$errors = array();

		if ( ! $this->user_can['publish'] ) {

			if ( ! $user_ID ) {
				$errors[] = __( 'You must be logged in to post. Login or register', 'wp-recall' );
			} else if ( $this->post_type == 'post-group' ) {
				$errors[] = __( 'Sorry, but you have no rights to publish in this group :(', 'wp-recall' );
			} else {
				$errors[] = __( 'Sorry, but you have no right to post on this site :(', 'wp-recall' );
			}
		} else if ( $this->post_id && ! $this->user_can['edit'] ) {
			$errors[] = __( 'You can not edit this publication :(', 'wp-recall' );
		}

		return apply_filters( 'rcl_public_form_errors', $errors, $this );
	}

	function get_errors_content() {

		$errorContent = '';

		foreach ( $this->get_errors() as $error ) {
			$errorContent .= rcl_get_notice( array(
				'type' => 'error',
				'text' => $error
			) );
		}

		return $errorContent;
	}

	function isset_notice() {
		return ! empty( $_GET['notice-warning'] ) || ! empty( $_GET['notice-success'] );
	}

	function get_notice_content() {

		$noticeContent = '';

		$noticesData = apply_filters( 'rcl_public_form_notices', [
			'warning' => [
				'required-fields' => __( 'Please fill in required fields!', 'wp-recall' )
			],
			'success' => [
				'draft-saved' => __( 'The draft has been saved successfully!', 'wp-recall' )
			]
		] );

		foreach ( $noticesData as $type => $notices ) {

			if ( empty( $_GET[ 'notice-' . $type ] ) ) {
				continue;
			}

			$noticeKey = sanitize_key( $_GET[ 'notice-' . $type ] );

			if ( empty( $notices[ $noticeKey ] ) ) {
				continue;
			}

			$noticeContent .= rcl_get_notice( array(
				'type' => $type,
				'text' => $notices[ $noticeKey ]
			) );

		}

		return $noticeContent;
	}

	function get_form( $args = array() ) {

		$content = '';

		if ( $this->get_errors() ) {
			return $this->get_errors_content();
		}

		if ( $this->isset_notice() ) {
			$content .= $this->get_notice_content();
		}

		$dataPost = $this->get_object_form();

		if ( $this->taxonomies ) {
			foreach ( $this->taxonomies as $taxname => $object ) {
				$this->tax_fields[] = 'taxonomy-' . $taxname;
			}
		}

		$attrs = array(
			'data-form_id'   => $this->form_id,
			'data-post_id'   => $this->post_id,
			'data-post_type' => $this->post_type,
			'class'          => array( 'rcl-public-form' )
		);

		$attrs = apply_filters( 'rcl_public_form_attributes', $attrs, $dataPost );

		$attrsForm = array();
		foreach ( $attrs as $k => $v ) {
			if ( is_array( $v ) ) {
				$attrsForm[] = $k . '="' . implode( ' ', $v ) . '"';
				continue;
			}
			$attrsForm[] = $k . '="' . $v . '"';
		}

		$content .= '<div class="rcl-public-box rcl-form">';

		$buttons = [];

		if ( rcl_check_access_console() ) {
			$buttons[] = [
				'href'  => admin_url( 'admin.php?page=manage-public-form&post-type=' . $this->post_type . '&form-id=' . $this->form_id ),
				'label' => __( 'Edit this form', 'wp-recall' ),
				'icon'  => 'fa-list',
				'type'  => 'clear'
			];
		}

		$buttons = apply_filters( 'rcl_public_form_top_manager_args', $buttons, $this );

		if ( $buttons ) {

			$content .= '<div id="rcl-public-form-top-manager" class="rcl-wrap rcl-wrap__right">';

			foreach ( $buttons as $button ) {
				$content .= rcl_get_button( $button );
			}

			$content .= '</div>';
		}

		$content .= '<form action="" method="post" ' . implode( ' ', $attrsForm ) . '>';

		if ( $this->fields ) {
			$content .= $this->get_content_form();
		}

		$content .= apply_filters( 'rcl_public_form', '', $this->get_object_form() );

		$content .= $this->get_primary_buttons();

		if ( $this->form_id ) {
			$content .= '<input type="hidden" name="form_id" value="' . $this->form_id . '">';
		}

		$content .= '<input type="hidden" name="post_id" value="' . $this->post_id . '">';
		$content .= '<input type="hidden" name="post_type" value="' . $this->post_type . '">';
		$content .= '<input type="hidden" name="rcl-edit-post" value="1">';
		$content .= wp_nonce_field( 'rcl-edit-post', '_wpnonce', true, false );
		$content .= '</form>';

		if ( $this->user_can['delete'] && $this->options['delete'] ) {

			$content .= '<div id="form-field-delete" class="rcl-form-field">';

			$content .= $this->get_delete_box();

			$content .= '</div>';
		}

		$content .= apply_filters( 'after_public_form_rcl', '', $this->get_object_form() );

		$content .= '</div>';

		return $content;
	}

	function get_primary_buttons() {

		$buttons = array();

		if ( $this->post_id ) {
			$buttons['gotopost'] = array(
				'href'  => get_permalink( $this->post_id ),
				'label' => __( 'Go to the post', 'wp-recall' ),
				'attrs' => array(
					'target' => '_blank'
				),
				'id'    => 'rcl-view-post',
				'icon'  => 'fa-share'
			);
		}

		if ( $this->options['draft'] && $this->user_can['draft'] ) {
			$buttons['draft'] = array(
				'onclick' => 'rcl_save_draft(this); return false;',
				'label'   => __( 'Save as Draft', 'wp-recall' ),
				'id'      => 'rcl-draft-post',
				'icon'    => 'fa-shield'
			);
		}

		if ( $this->options['preview'] ) {
			$buttons['preview'] = array(
				'onclick' => 'rcl_preview(this); return false;',
				'label'   => __( 'Preview', 'wp-recall' ),
				'id'      => 'rcl-preview-post',
				'icon'    => 'fa-eye'
			);
		}

		$buttons['publish'] = array(
			'onclick' => 'rcl_publish(this); return false;',
			'label'   => __( 'Publish', 'wp-recall' ),
			'id'      => 'rcl-publish-post',
			'icon'    => 'fa-print'
		);

		$buttons = apply_filters( 'rcl_public_form_primary_buttons', $buttons, $this->get_object_form(), $this );

		if ( ! $buttons ) {
			return false;
		}

		$content = '<div class="rcl-form-field submit-public-form">';

		foreach ( $buttons as $button ) {
			$content .= rcl_get_button( $button );
		}

		$content .= '</div>';

		return $content;
	}

	function get_field_form( $field_id, $args = false ) {

		$dataPost = $this->get_object_form();

		$field = $this->get_field( $field_id );

		if ( ! $field ) {
			return false;
		}

		$this->current_field = $field;

		$contentField = false;

		if ( $this->taxonomies && in_array( $field_id, $this->tax_fields ) ) {

			if ( $taxonomy = $this->is_taxonomy_field( $field_id ) ) {

				$contentField = $this->get_terms_list( $taxonomy, $field_id );
			}
		} else {

			if ( in_array( $field_id, $this->core_fields ) ) {

				if ( $field_id == 'post_content' ) {

					$contentField = $this->get_editor( array(
						'post_content' => $dataPost->post_content,
						'options'      => $field->get_prop( 'post-editor' )
					) );

					$contentField .= $field->get_notice();
				} else if ( $field_id == 'post_excerpt' ) {

					$field->set_prop( 'value', $dataPost->post_excerpt );

					$contentField = $field->get_field_input();
				} else if ( $field_id == 'post_title' ) {

					$field->set_prop( 'value', esc_textarea( $dataPost->post_title ) );

					$contentField = $field->get_field_input( esc_textarea( $dataPost->post_title ) );
				} else if ( $field->type == 'uploader' ) {

					if ( $field_id == 'post_thumbnail' ) {

						$field->set_prop( 'uploader_props', array(
							'post_parent' => $this->post_id,
							'form_id'     => intval( $this->form_id ),
							'post_type'   => $this->post_type,
							'multiple'    => 0,
							'crop'        => 1
						) );

						$uploader = $field->get_uploader();

						if ( $this->post_id ) {

							$thumbnail_id = get_post_meta( $this->post_id, '_thumbnail_id', 1 );
						} else {

							$thumbnail_id = RQ::tbl( new Rcl_Temp_Media() )
							                  ->select( [ 'media_id' ] )
							                  ->where( [
								                  'user_id'         => $uploader->user_id ? $uploader->user_id : 0,
								                  'session_id'      => $uploader->user_id ? '' : ( ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : '' ),
								                  'uploader_id__in' => array( 'post_thumbnail' )
							                  ] )
							                  ->limit( 1 )
							                  ->get_var();
						}

						if ( $thumbnail_id ) {
							$field->set_prop( 'value', $thumbnail_id );
						}

						$contentField = $field->get_field_input();
					}

					if ( $field_id == 'post_uploader' ) {

						$field->set_prop( 'uploader_props', array(
							'post_parent' => $this->post_id,
							'form_id'     => intval( $this->form_id ),
							'post_type'   => $this->post_type
						) );

						$uploader = $field->get_uploader();

						if ( $this->post_id ) {
							$imagIds = RQ::tbl( new Rcl_Posts_Query() )->select( [ 'ID' ] )->where( [
								'post_parent' => $this->post_id,
								'post_type'   => 'attachment',
							] )->limit( - 1 )->order( 'ASC' )->get_col();
						} else {

							$imagIds = RQ::tbl( new Rcl_Temp_Media() )
							             ->select( [ 'media_id' ] )
							             ->where( [
								             'user_id'         => $uploader->user_id ? $uploader->user_id : 0,
								             'session_id'      => $uploader->user_id ? '' : ( ! empty( $_COOKIE['PHPSESSID'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['PHPSESSID'] ) ) : '' ),
								             'uploader_id__in' => array( 'post_uploader', 'post_thumbnail' )
							             ] )
							             ->limit( - 1 )->order( 'ASC' )->get_col();
						}

						$contentField = $uploader->get_gallery( $imagIds );

						$contentField .= $uploader->get_uploader();

						$contentField .= $field->get_notice();
					}
				}
			} else {

				if ( ! isset( $field->value ) ) {
					$field->set_prop( 'value', ( $this->post_id ) ? get_post_meta( $this->post_id, $field_id, 1 ) : null );
				}

				$contentField = $field->get_field_input();
			}
		}

		if ( ! $contentField ) {
			return false;
		}

		$content = '<div id="form-field-' . $field_id . '" class="rcl-form-field field-' . $field_id . '">';

		$content .= '<label>' . $field->get_title() . '</label>';

		$content .= $contentField;

		$content .= '</div>';

		return $content;
	}

	function get_terms_list( $taxonomy, $field_id ) {

		$field = $this->get_field( $field_id );

		$content = '<div class="rcl-terms-select taxonomy-' . $taxonomy . '">';

		$terms = $field->isset_prop( 'values' ) ? $field->get_prop( 'values' ) : array();

		if ( $this->is_hierarchical_tax( $taxonomy ) ) {

			if ( $this->post_type == 'post-group' ) {

				global $rcl_group;

				if ( isset( $rcl_group->term_id ) && $rcl_group->term_id ) {
					$group_id = $rcl_group->term_id;
				} else if ( $this->post_id ) {
					$group_id = rcl_get_group_id_by_post( $this->post_id );
				}

				$options_gr = rcl_get_options_group( $group_id );

				$termList = rcl_get_tags_list_group( $options_gr['tags'], $this->post_id );

				if ( ! $termList ) {
					return false;
				}

				$content .= $termList;
			} else {

				$type   = ( $val = $field->get_prop( 'type-select' ) ) ? $val : 'select';
				$number = ( $val = $field->get_prop( 'number-select' ) ) ? $val : 1;

				$termList   = new Rcl_List_Terms( $taxonomy, $type, $field->get_prop( 'required' ) );
				$post_terms = $this->get_post_terms( $taxonomy );

				$content .= $termList->get_select_list( $this->get_allterms( $taxonomy ), $post_terms, $number, $terms );
			}
		} else {

			$content .= $this->tags_field( $taxonomy, $terms );
		}

		$content .= $field->get_notice();

		$content .= '</div>';

		return $content;
	}

	function get_editor( $args = false ) {

		$wp_uploader = false;
		$quicktags   = false;
		$tinymce     = false;

		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {

			if ( in_array( 'media', $args['options'] ) ) {
				$wp_uploader = true;
			}

			if ( in_array( 'html', $args['options'] ) ) {
				$quicktags = true;
			}

			if ( in_array( 'editor', $args['options'] ) ) {
				$tinymce = true;
			}
		}

		$data = array(
			'wpautop'       => 1
		,
			'media_buttons' => $wp_uploader
		,
			'textarea_name' => 'post_content'
		,
			'textarea_rows' => 10
		,
			'tabindex'      => null
		,
			'editor_css'    => ''
		,
			'editor_class'  => 'autosave'
		,
			'teeny'         => 0
		,
			'dfw'           => 0
		,
			'tinymce'       => $tinymce
		,
			'quicktags'     => $quicktags
		);

		$post_content = ( isset( $args['post_content'] ) ) ? $args['post_content'] : false;

		ob_start();

		wp_editor( $post_content, 'post_content', $data );

		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

	function get_tags_checklist( $taxonomy, $t_args = array() ) {

		if ( ! is_array( $t_args ) || $t_args === false ) {
			return false;
		}

		$values      = [];
		$checkedVals = [];
		$post_tags   = ( $this->post_id ) ? $this->get_tags( $this->post_id, $taxonomy ) : array();

		if ( $t_args['number'] != 0 && $tags = get_terms( $taxonomy, $t_args ) ) {

			foreach ( $tags as $tag ) {

				$checked = false;

				if ( isset( $post_tags[ $tag->slug ] ) && $tag->name == $post_tags[ $tag->slug ]->name ) {
					$checked = true;
					unset( $post_tags[ $tag->slug ] );
				}

				if ( $checked ) {
					$checkedVals[] = $tag->name;
				}

				$values[ $tag->name ] = $tag->name;

			}

		}

		if ( $post_tags ) {

			foreach ( $post_tags as $tag ) {

				$checkedVals[] = $tag->name;

				$values[ $tag->name ] = $tag->name;

			}

		}

		if ( ! $values ) {
			return false;
		}

		return Rcl_Field::setup( [
			'type'       => 'checkbox',
			'slug'       => $taxonomy . '-tags',
			'input_name' => 'tags[' . $taxonomy . ']',
			'required'   => $this->current_field->get_prop( 'required' ),
			'values'     => $values,
			'value'      => $checkedVals
		] )->get_field_input();
	}

	function get_tags( $post_id, $taxonomy = 'post_tag' ) {

		$posttags = get_the_terms( $post_id, $taxonomy );

		$tags = array();
		if ( $posttags ) {
			foreach ( $posttags as $tag ) {
				$tags[ $tag->slug ] = $tag;
			}
		}

		return $tags;
	}

	function tags_field( $taxonomy, $terms ) {

		if ( ! $this->taxonomies || ! isset( $this->taxonomies[ $taxonomy ] ) ) {
			return false;
		}

		$args = array(
			'input_field' => $this->current_field->get_prop( 'input-tags' ),
			'terms_cloud' => array(
				'hide_empty' => false,
				'number'     => $this->current_field->get_prop( 'number-tags' ),
				'orderby'    => 'count',
				'order'      => 'DESC',
				'include'    => $terms
			)
		);

		$args = apply_filters( 'rcl_public_form_tags', $args, $taxonomy, $this->get_object_form() );

		$content = $this->get_tags_checklist( $taxonomy, $args['terms_cloud'] );

		if ( $args['input_field'] ) {
			$content .= $this->get_tags_input( $taxonomy );
		}

		if ( ! $content ) {
			return false;
		}

		return '<div class="rcl-tags-list">' . $content . '</div>';
	}

	function get_tags_input( $taxonomy = 'post_tag' ) {

		rcl_autocomplete_scripts();

		$args = array(
			'type'        => 'text',
			'id'          => 'rcl-tags-' . $taxonomy,
			'name'        => 'tags[' . $taxonomy . ']',
			'placeholder' => $this->taxonomies[ $taxonomy ]->labels->new_item_name,
			'label'       => '<span>' . $this->taxonomies[ $taxonomy ]->labels->add_new_item . '</span><br><small>' . $this->taxonomies[ $taxonomy ]->labels->name . ' ' . __( 'It separates by push of Enter button', 'wp-recall' ) . '</small>'
		);

		$fields = rcl_form_field( $args );

		$fields .= "<script>
		jQuery(window).on('load', function(){
			jQuery('#rcl-tags-" . $taxonomy . "').magicSuggest({
				data: Rcl.ajaxurl,
				dataUrlParams: { action: 'rcl_get_like_tags',taxonomy: '" . $taxonomy . "',ajax_nonce:Rcl.nonce },
				noSuggestionText: '" . __( "Not found", "rcl-public" ) . "',
				ajaxConfig: {
					  xhrFields: {
						withCredentials: true,
					  }
				}
			});
		});
		</script>";

		return $fields;
	}

	function get_allterms( $taxonomy ) {

		$args = array(
			'number'       => 0
		,
			'offset'       => 0
		,
			'orderby'      => 'id'
		,
			'order'        => 'ASC'
		,
			'hide_empty'   => false
		,
			'fields'       => 'all'
		,
			'slug'         => ''
		,
			'hierarchical' => true
		,
			'name__like'   => ''
		,
			'pad_counts'   => false
		,
			'get'          => ''
		,
			'child_of'     => 0
		,
			'parent'       => ''
		);

		$args = apply_filters( 'rcl_public_form_hierarchical_terms', $args, $taxonomy, $this->get_object_form() );

		return get_terms( $taxonomy, $args );
	}

	function get_post_terms( $taxonomy ) {

		if ( ! isset( $this->taxonomies[ $taxonomy ] ) ) {
			return false;
		}

		if ( $this->post_type == 'post' && $taxonomy == 'category' ) {

			$post_terms = get_the_terms( $this->post_id, $taxonomy );
		} else {

			$post_terms = get_the_terms( $this->post_id, $taxonomy );
		}

		if ( $post_terms ) {

			foreach ( $post_terms as $key => $term ) {

				foreach ( $post_terms as $t ) {

					if ( $t->parent == $term->term_id ) {
						unset( $post_terms[ $key ] );
						break;
					}
				}
			}
		}

		return $post_terms;
	}

	function get_delete_box() {
		global $user_ID;

		if ( rcl_is_user_role( $user_ID, array( 'administrator', 'editor' ) ) ) {

			$content = '<div id="rcl-delete-post">
						' . rcl_get_button( array(
					'label' => __( 'Delete post', 'wp-recall' ),
					'class' => array( 'public-form-button delete-toggle' ),
					'icon'  => 'fa-trash'
				) ) . '
						<div class="delete-form-contayner">
							<form action="" method="post"  onsubmit="return confirm(\'' . __( 'Are you sure?', 'wp-recall' ) . '\');">
							' . wp_nonce_field( 'rcl-delete-post', '_wpnonce', true, false ) . '
							' . $this->get_reasons_list() . '
							<label>' . __( 'or enter your own', 'wp-recall' ) . '</label>
							<textarea required id="reason_content" name="reason_content"></textarea>
							<p><input type="checkbox" name="no-reason" onclick="(!document.getElementById(\'reason_content\').getAttribute(\'disabled\')) ? document.getElementById(\'reason_content\').setAttribute(\'disabled\', \'disabled\') : document.getElementById(\'reason_content\').removeAttribute(\'disabled\')" value="1"> ' . __( 'Without notice', 'wp-recall' ) . '</p>
							' . rcl_get_button( array(
					'submit' => true,
					'label'  => __( 'Delete post', 'wp-recall' ),
					'icon'   => 'fa-trash'
				) ) . '<input type="hidden" name="rcl-delete-post" value="1">
							<input type="hidden" name="post_id" value="' . $this->post_id . '">
							</form>
						</div>
					</div>';
		} else {

			$content = '<form method="post" action="" onsubmit="return confirm(\'' . __( 'Are you sure?', 'wp-recall' ) . '\');">
						' . wp_nonce_field( 'rcl-delete-post', '_wpnonce', true, false ) . '
						' . rcl_get_button( array(
					'submit' => true,
					'label'  => __( 'Delete post', 'wp-recall' ),
					//'class'	 => array( 'delete-post-submit public-form-button' ),
					'icon'   => 'fa-trash'
				) ) . '
						<input type="hidden" name="rcl-delete-post" value="1">
						<input type="hidden" name="post_id" value="' . $this->post_id . '">'
			           . '</form>';
		}

		return $content;
	}

	function get_reasons_list() {

		$reasons = array(
			array(
				'value'   => __( 'Does not correspond the topic', 'wp-recall' ),
				'content' => __( 'The publication does not correspond to the site topic', 'wp-recall' ),
			),
			array(
				'value'   => __( 'Not completed', 'wp-recall' ),
				'content' => __( 'Publication does not correspond the rules', 'wp-recall' ),
			),
			array(
				'value'   => __( 'Advertising/Spam', 'wp-recall' ),
				'content' => __( 'Publication labeled as advertising or spam', 'wp-recall' ),
			)
		);

		$reasons = apply_filters( 'rcl_public_form_delete_reasons', $reasons, $this->get_object_form() );

		if ( ! $reasons ) {
			return false;
		}

		$content = '<label>' . __( 'Use blank notice', 'wp-recall' ) . ':</label>';

		foreach ( $reasons as $reason ) {
			$content .= rcl_get_button( array(
				'onclick' => 'document.getElementById("reason_content").value="' . $reason['content'] . '"',
				'label'   => $reason['value'],
				'class'   => 'reason-delete'
			) );
		}

		return $content;
	}

	function init_form_scripts() {

		$obj = $this->form_object;

		echo '<script type="text/javascript">'
		     . 'rcl_init_public_form({'
		     . 'post_type:"' . esc_js( $obj->post_type ) . '",'
		     . 'post_id:"' . esc_js( $obj->post_id ) . '",'
		     . 'post_status:"' . esc_js( $obj->post_status ) . '",'
		     . 'form_id:"' . esc_js( $this->form_id ) . '"'
		     . '});</script>';
	}

}
