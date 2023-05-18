<?php

add_filter( 'rcl_options', 'rcl_chat_options' );
function rcl_chat_options( $options ) {

	$options->add_box( 'chat', array(
		'title' => __( 'IM settings', 'wp-recall' ),
		'icon'  => 'fa-comments'
	) )->add_group( 'general', array(
		'title' => __( 'General settings', 'wp-recall' )
	) )->add_options( array(
		array(
			'type'      => 'runner',
			'title'     => __( 'Delay between requests', 'wp-recall' ),
			'slug'      => 'delay',
			'group'     => 'chat',
			'value_min' => 5,
			'value_max' => 60,
			'default'   => 15,
			'notice'    => __( 'In seconds. It is recommended to choose at '
			                   . 'least 10 seconds', 'wp-recall' ),
		),
		array(
			'type'      => 'runner',
			'title'     => __( 'User Downtime', 'wp-recall' ),
			'slug'      => 'inactivity',
			'group'     => 'chat',
			'value_min' => 1,
			'value_max' => 20,
			'default'   => 10,
			'notice'    => __( '"In minutes. The time of user inactivity '
			                   . 'after which he ceases to receive new messages in chat', 'wp-recall' )
		),
		array(
			'type'      => 'runner',
			'title'     => __( 'Antispam', 'wp-recall' ),
			'slug'      => 'antispam',
			'group'     => 'chat',
			'value_min' => 0,
			'value_max' => 20,
			'default'   => 5,
			'notice'    => __( 'Specify a number of users, who other user will '
			                   . 'be able to send an unread private message for a day. If its '
			                   . 'value is exceeded the sending of messages will be blocked. If zero, this function is disabled', 'wp-recall' )
		),
		array(
			'type'      => 'runner',
			'title'     => __( 'The number of characters in the message', 'wp-recall' ),
			'slug'      => 'words',
			'group'     => 'chat',
			'value_min' => 100,
			'value_max' => 1000,
			'default'   => 300
		),
		array(
			'type'      => 'runner',
			'title'     => __( 'Posts per page', 'wp-recall' ),
			'slug'      => 'in_page',
			'group'     => 'chat',
			'value_min' => 10,
			'value_max' => 200,
			'default'   => 50
		),
		array(
			'type'   => 'select',
			'title'  => __( 'Using OEMBED', 'wp-recall' ),
			'slug'   => 'oembed',
			'group'  => 'chat',
			'values' => array(
				__( 'No', 'wp-recall' ),
				__( 'Yes', 'wp-recall' )
			),
			'notice' => __( 'Option is responsible for the incorporation of '
			                . 'media content, such as from Youtube or Twitter from the link', 'wp-recall' ),
		),
		array(
			'type'      => 'select',
			'title'     => __( 'Attaching files', 'wp-recall' ),
			'slug'      => 'file_upload',
			'group'     => 'chat',
			'values'    => array(
				__( 'No', 'wp-recall' ),
				__( 'Yes', 'wp-recall' )
			),
			'childrens' => array(
				1 => array(
					array(
						'type'    => 'text',
						'title'   => __( 'Allowed file types', 'wp-recall' ),
						'slug'    => 'file_types',
						'group'   => 'chat',
						'default' => 'jpeg, jpg, png, zip, mp3',
						'notice'  => __( 'By default: jpeg, jpg, png, zip, mp3', 'wp-recall' )
					),
					array(
						'type'       => 'runner',
						'value_min'  => 1,
						'value_max'  => 10,
						'value_step' => 1,
						'default'    => 2,
						'title'      => __( 'Maximum file size, MB', 'wp-recall' ),
						'slug'       => 'file_size',
						'group'      => 'chat'
					)
				)
			)
		)
	) );

	$options->box( 'chat' )->add_group( 'personal', array(
		'title' => __( 'Personal chat', 'wp-recall' )
	) )->add_options( array(
		array(
			'type'    => 'number',
			'title'   => __( 'Number of messages in the conversation', 'wp-recall' ),
			'slug'    => 'messages_amount',
			'group'   => 'chat',
			'default' => 100,
			'notice'  => __( 'The maximum number of messages in the '
			                 . 'conversation between two users. Default: 100', 'wp-recall' )
		),
		array(
			'type'   => 'select',
			'slug'   => 'messages_mail',
			'title'  => __( 'Mail alert', 'wp-recall' ),
			'values' => array(
				__( 'Without the text of the message', 'wp-recall' ),
				__( 'Full text of the message', 'wp-recall' )
			)
		),
		array(
			'type'      => 'select',
			'title'     => __( 'Contacts bar', 'wp-recall' ),
			'slug'      => 'contact_panel',
			'group'     => 'chat',
			'values'    => array(
				__( 'No', 'wp-recall' ),
				__( 'Yes', 'wp-recall' )
			),
			'childrens' => array(
				1 => array(
					array(
						'type'   => 'select',
						'title'  => __( 'Output location', 'wp-recall' ),
						'slug'   => 'place_contact_panel',
						'group'  => 'chat',
						'values' => array(
							__( 'Right', 'wp-recall' ),
							__( 'Left', 'wp-recall' )
						)
					)
				)
			)
		)
	) );

	return $options;
}
