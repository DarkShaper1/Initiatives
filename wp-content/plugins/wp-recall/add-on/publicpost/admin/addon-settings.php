<?php

add_filter( 'rcl_options', 'rcl_get_publics_options_page' );
function rcl_get_publics_options_page( $options ) {
	global $_wp_additional_image_sizes;

	$_wp_additional_image_sizes['thumbnail'] = 1;
	$_wp_additional_image_sizes['medium']    = 1;
	$_wp_additional_image_sizes['large']     = 1;
	foreach ( $_wp_additional_image_sizes as $name => $size ) {
		$sh_name = $name;
		if ( $size != 1 ) {
			$sh_name .= ' (' . $size['width'] . '*' . $size['height'] . ')';
		}
		$d_sizes[ $name ] = $sh_name;
	}

	$post_types = get_post_types( array(
		'public'   => true,
		'_builtin' => false
	), 'objects' );

	$types = array( 'post' => __( 'Records', 'wp-recall' ) );

	foreach ( $post_types as $post_type ) {
		$types[ $post_type->name ] = $post_type->label;
	}

	$pages = rcl_get_pages_ids();

	$options->add_box( 'publicpost', array(
		'title' => __( 'Publication settings', 'wp-recall' ),
		'icon'  => 'fa-pencil-square-o'
	) )->add_group( 'general', array(
		'title' => __( 'General settings', 'wp-recall' )
	) )->add_options( array(
		array(
			'type'   => 'select',
			'slug'   => 'public_form_page_rcl',
			'title'  => __( 'Publishing and editing', 'wp-recall' ),
			'values' => $pages,
			'notice' => __( 'You are required to publish a links to managing publications, you must specify the page with the shortcode [public-form]', 'wp-recall' )
		),
		array(
			'type'      => 'select',
			'slug'      => 'info_author_recall',
			'title'     => __( 'Display information about the author', 'wp-recall' ),
			'values'    => array(
				__( 'Disabled', 'wp-recall' ),
				__( 'Enabled', 'wp-recall' )
			),
			'childrens' => array(
				1 => array(
					array(
						'type'   => 'checkbox',
						'slug'   => 'post_types_authbox',
						'title'  => __( 'Types of write for the author`s block output', 'wp-recall' ),
						'values' => $types,
						'notice' => __( 'Select the types of writes where the author`s block should be displayed. If nothing is specified, it is displayed everywhere', 'wp-recall' )
					)
				)
			)
		),
		array(
			'type'      => 'select',
			'slug'      => 'publics_block_rcl',
			'title'     => __( 'List of publications tab', 'wp-recall' ),
			'values'    => array( __( 'Disabled', 'wp-recall' ), __( 'Enabled', 'wp-recall' ) ),
			'childrens' => array(
				1 => array(
					array(
						'type'   => 'checkbox',
						'slug'   => 'post_types_list',
						'title'  => __( 'Type of post for output a list of writes', 'wp-recall' ),
						'values' => $types,
						'notice' => __( 'Select the type of post which will be to output its archive of writes in this tab. If nothing is specified, it will be outputed a writes all types', 'wp-recall' )
					),
					array(
						'type'   => 'select',
						'slug'   => 'view_publics_block_rcl',
						'title'  => __( 'List of publications of the user', 'wp-recall' ),
						'values' => array(
							__( 'Only owner of the account', 'wp-recall' ),
							__( 'Show everyone including guests', 'wp-recall' )
						)
					)
				)
			)
		)
	) );

	$options->box( 'publicpost' )->add_group( 'form', array(
		'title' => __( 'Form of publication', 'wp-recall' )
	) )->add_options( array(
		array(
			'type'   => 'select',
			'slug'   => 'public_preview',
			'title'  => __( 'Use preview', 'wp-recall' ),
			'values' => array( __( 'No', 'wp-recall' ), __( 'Yes', 'wp-recall' ) )
		),
		array(
			'type'   => 'select',
			'slug'   => 'public_draft',
			'title'  => __( 'Use draft', 'wp-recall' ),
			'values' => array( __( 'No', 'wp-recall' ), __( 'Yes', 'wp-recall' ) )
		),
		array(
			'type'   => 'select',
			'slug'   => 'default_size_thumb',
			'title'  => __( 'The image size in editor by default', 'wp-recall' ),
			'values' => $d_sizes,
			'notice' => __( 'Select image size for the visual editor during publishing', 'wp-recall' )
		),
		array(
			'type'      => 'select',
			'slug'      => 'output_public_form_rcl',
			'title'     => __( 'Form of publication output in the personal cabinet', 'wp-recall' ),
			'values'    => array( __( 'Do not display', 'wp-recall' ), __( 'Output', 'wp-recall' ) ),
			'default'   => 1,
			'childrens' => array(
				1 => array(
					array(
						'type'   => 'number',
						'slug'   => 'form-lk',
						'title'  => __( 'The form ID', 'wp-recall' ),
						'notice' => __( 'Enter the form ID according to the personal Cabinet. The default is 1', 'wp-recall' )
					)
				)
			)
		)
	) );

	$options->box( 'publicpost' )->add_group( 'records', array(
		'title' => __( 'Publication of records', 'wp-recall' )
	) )->add_options( array(
		array(
			'type'      => 'select',
			'slug'      => 'user_public_access_recall',
			'title'     => __( 'Publication is allowed', 'wp-recall' ),
			'values'    => array(
				10 => __( 'only Administrators', 'wp-recall' ),
				7  => __( 'Editors and higher', 'wp-recall' ),
				2  => __( 'Authors and higher', 'wp-recall' ),
				0  => __( 'Guests and users', 'wp-recall' )
			),
			'childrens' => array(
				array(
					array(
						'type'   => 'select',
						'slug'   => 'guest_post_redirect',
						'title'  => __( 'Redirect to', 'wp-recall' ),
						'values' => $pages,
						'notice' => __( 'Select the page to which the visitors will be redirected after a successful publication, if email authorization is included in the registration precess', 'wp-recall' )
					)
				)
			)
		),
		array(
			'type'      => 'select',
			'slug'      => 'moderation_public_post',
			'title'     => __( 'Moderation of publications', 'wp-recall' ),
			'values'    => array( __( 'Publish now', 'wp-recall' ), __( 'Send for moderation', 'wp-recall' ) ),
			'notice'    => __( 'If subject to moderation: To allow the user to see their publication before moderation has been completed, the user should be classifies as Author or higher', 'wp-recall' ),
			'childrens' => array(
				1 => array(
					array(
						'type'   => 'checkbox',
						'slug'   => 'post_types_moderation',
						'title'  => __( 'Type post', 'wp-recall' ),
						'values' => $types,
						'notice' => __( 'Select the types of posts that will be sent for moderation. If nothing is specified, then the moderation is valid for all types', 'wp-recall' )
					)
				)
			)
		)
	) );

	$options->box( 'publicpost' )->add_group( 'edit', array(
		'title' => __( 'Editing', 'wp-recall' )
	) )->add_options( array(
		array(
			'type'   => 'checkbox',
			'slug'   => 'front_editing',
			'title'  => __( 'Frontend editing', 'wp-recall' ),
			'values' => array(
				10 => __( 'Administrators', 'wp-recall' ),
				7  => __( 'Editors', 'wp-recall' ),
				2  => __( 'Authors', 'wp-recall' )
			)
		),
		array(
			'type'   => 'number',
			'slug'   => 'time_editing',
			'title'  => __( 'The time limit edit', 'wp-recall' ),
			'notice' => __( 'Limit editing time of publication in hours, by default: unlimited', 'wp-recall' )
		)
	) );

	$options->box( 'publicpost' )->add_group( 'fields', array(
		'title' => __( 'Custom fields', 'wp-recall' )
	) )->add_options( array(
		array(
			'type'      => 'select',
			'slug'      => 'pm_rcl',
			'title'     => __( 'Automatic output', 'wp-recall' ),
			'values'    => array( __( 'No', 'wp-recall' ), __( 'Yes', 'wp-recall' ) ),
			'notice'    => __( 'Settings only for fields created using the form of the publication wp-recall', 'wp-recall' ),
			'childrens' => array(
				1 => array(
					array(
						'type'   => 'select',
						'slug'   => 'pm_place',
						'title'  => __( 'Output fields location', 'wp-recall' ),
						'values' => array(
							__( 'Above publication content', 'wp-recall' ),
							__( 'On content recording', 'wp-recall' )
						)
					),
					array(
						'type'   => 'checkbox',
						'slug'   => 'pm_post_types',
						'title'  => __( 'Types of posts for the output of custom fields', 'wp-recall' ),
						'values' => $types,
						'notice' => __( 'Select types of posts where the values of arbitrary fields will be displayed. If nothing is specified, it is displayed everywhere', 'wp-recall' )
					)
				)
			)
		)
	) );

	return $options;
}
