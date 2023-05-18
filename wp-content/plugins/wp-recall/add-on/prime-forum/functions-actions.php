<?php

function pfm_add_ajax_action( $action, $functionName ) {
	global $PrimeActions;
	$PrimeActions[ $action ] = $functionName;
}

function pfm_get_manager( $actions, $itemType, $itemID = false ) {

	$items = array();

	foreach ( $actions as $action => $options ) {

		$args = array(
			'item_type' => $itemType,
			'method'    => $action
		);

		if ( $itemID ) {
			$args['item_id'] = $itemID;
		}

		if ( isset( $options['options'] ) ) {
			$args = array_merge( $args, $options['options'] );
		}


		$item = '<a href="#" title="' . $options['name'] . '" class="topic-action action-' . $action . '" onclick=\'pfm_ajax_action(' . json_encode( $args ) . ');return false;\'>';
		$item .= ( isset( $options['icon'] ) ) ? '<i class="rcli ' . $options['icon'] . '" aria-hidden="true"></i>' : $options['name'];
		$item .= '</a>';

		$items[] = $item;
	}

	$items = apply_filters( 'pfm_manager_items', $items, $itemType, $itemID );

	$content = '<div class="' . $itemType . '-manager prime-manager">';
	$content .= '<ul>';

	$content .= '<li>' . implode( '<li>', $items ) . '</li>';

	$content .= '</ul>';
	$content .= '</div>';

	return $content;
}

function pfm_the_author_manager() {
	global $PrimePost, $user_ID;

	$actions = array();

	if ( $user_ID && $PrimePost->user_id && $user_ID != $PrimePost->user_id ) {

		if ( function_exists( 'rcl_get_user_details' ) ) {

			$actions['get_author_info'] = array(
				'name' => __( 'Details about the author', 'wp-recall' ),
				'icon' => 'fa-info-circle'
			);
		}

		if ( rcl_exist_addon( 'rcl-chat' ) ) {

			$actions['get_private_chat'] = array(
				'name' => __( 'Go to the private chat', 'wp-recall' ),
				'icon' => 'fa-comments-o'
			);
		}
	}

	if ( ! $actions ) {
		return false;
	}

	$content = pfm_get_manager( $actions, 'author', $PrimePost->user_id );

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

function pfm_get_primary_manager() {
	global $user_ID;

	$actions = array(
		'get_last_updated_topics' => array(
			'name' => __( 'Get the list of updated topics', 'wp-recall' ),
			'icon' => 'fa-bell-o'
		)
	);

	if ( $user_ID ) {
		$actions['get_author_topics'] = array(
			'name' => __( 'Get your started topics', 'wp-recall' ),
			'icon' => 'fa-address-book'
		);
	}

	$actions['get_structure'] = array(
		'name' => __( 'Jump to the needed forum', 'wp-recall' ),
		'icon' => 'fa-rocket'
	);

	return pfm_get_manager( $actions, 'primary' );
}

function pfm_the_post_manager() {
	global $PrimePost, $PrimeTopic;

	if ( ! $PrimePost->post_id ) {
		return false;
	}

	$actions = array();

	if ( pfm_is_can_post_delete( $PrimePost->post_id ) ) {

		$actions['post_delete'] = array(
			'name'    => __( 'Delete message', 'wp-recall' ),
			'icon'    => 'fa-trash',
			'options' => array(
				'confirm' => __( 'Are you sure?', 'wp-recall' )
			)
		);
	}

	if ( pfm_is_can( 'post_migrate' ) ) {

		$actions['start_post_migrate'] = array(
			'name' => __( 'Transfer to the existing topic', 'wp-recall' ),
			'icon' => 'fa-share-square-o'
		);

		$actions['get_form_topic_create'] = array(
			'name' => __( 'Transfer to the new topic', 'wp-recall' ),
			'icon' => 'fa-code-fork'
		);
	}

	if ( pfm_is_can_post_edit( $PrimePost->post_id ) ) {

		$actions['get_form_post_edit'] = array(
			'name' => __( 'Edit message', 'wp-recall' ),
			'icon' => 'fa-pencil-square-o'
		);
	}

	if ( pfm_is_can( 'post_create' ) && $PrimeTopic && ! $PrimeTopic->topic_closed && ! $PrimeTopic->forum_closed ) {

		$actions['get_post_excerpt'] = array(
			'name' => __( 'Quote message', 'wp-recall' ),
			'icon' => 'fa-quote-right'
		);
	}

	$actions = apply_filters( 'pfm_post_manager_actions', $actions, $PrimePost );

	if ( ! $actions ) {
		return false;
	}

	$content = pfm_get_manager( $actions, 'post', $PrimePost->post_id );

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

function pfm_the_topic_manager() {
	global $PrimeTopic;

	if ( ! $PrimeTopic->topic_id ) {
		return false;
	}

	$actions = array();

	if ( pfm_is_can( 'post_migrate' ) ) {

		if ( isset( $_COOKIE['pfm_migrate_post'] ) ) {
			$actions['end_post_migrate']    = array(
				'name' => __( 'Transfer to this topic', 'wp-recall' )
			);
			$actions['cancel_post_migrate'] = array(
				'name' => __( 'Cancel transfer', 'wp-recall' ),
				'icon' => 'fa-times'
			);
		}
	}

	if ( pfm_is_can_topic_delete( $PrimeTopic->topic_id ) ) {

		$actions['topic_delete'] = array(
			'name'    => __( 'Delete topic', 'wp-recall' ),
			'icon'    => 'fa-trash',
			'options' => array(
				'confirm' => __( 'Are you sure?', 'wp-recall' )
			)
		);
	}

	if ( pfm_is_can( 'topic_migrate' ) ) {

		$actions['get_form_topic_migrate'] = array(
			'name' => __( 'Transfer topic', 'wp-recall' ),
			'icon' => 'fa-chain-broken'
		);
	}

	if ( pfm_is_can( 'topic_fix' ) ) {

		if ( $PrimeTopic->topic_fix ) {
			$actions['topic_unfix'] = array(
				'name' => __( 'Unpin topic', 'wp-recall' ),
				'icon' => 'fa-star'
			);
		} else {
			$actions['topic_fix'] = array(
				'name' => __( 'Pin topic', 'wp-recall' ),
				'icon' => 'fa-star-o'
			);
		}
	}

	if ( pfm_is_can( 'topic_close' ) ) {

		if ( $PrimeTopic->topic_closed ) {
			$actions['topic_unclose'] = array(
				'name' => __( 'Open topic', 'wp-recall' ),
				'icon' => 'fa-lock'
			);
		} else {
			$actions['topic_close'] = array(
				'name' => __( 'Close topic', 'wp-recall' ),
				'icon' => 'fa-unlock'
			);
		}
	}

	if ( pfm_is_can_topic_edit( $PrimeTopic->topic_id ) ) {

		$actions['get_form_topic_edit'] = array(
			'name' => __( 'Change name', 'wp-recall' ),
			'icon' => 'fa-pencil-square-o'
		);
	}

	$actions = apply_filters( 'pfm_topic_manager_actions', $actions, $PrimeTopic );

	if ( ! $actions ) {
		return false;
	}

	$content = pfm_get_manager( $actions, 'topic', $PrimeTopic->topic_id );

	echo wp_kses( $content, rcl_kses_allowed_html() );
}

add_action( 'rcl_init', 'pfm_init_actions_in_office' );
function pfm_init_actions_in_office() {
	global $user_ID;

	if ( rcl_is_office( $user_ID ) ) {
		add_action( 'wp', 'pfm_init_actions', 30 );
	}
}

add_action( 'pfm_after_init_query', 'pfm_init_actions', 30 );
function pfm_init_actions() {
	global $user_ID;

	if ( ! isset( $_REQUEST['pfm-action'] ) || ! isset( $_REQUEST['_wpnonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_key( $_REQUEST['_wpnonce'] ), 'pfm-nonce' ) ) {
		return;
	}

	$action = sanitize_key( $_REQUEST['pfm-action'] );

	switch ( $action ) {
		case 'topic_create': //создание топика

			if ( empty( $_REQUEST['topic_name'] ) || empty( $_REQUEST['forum_id'] ) || ! pfm_is_can( 'topic_create' ) ) {
				return false;
			}

			if ( empty( $_REQUEST['post_content'] ) ) {
				wp_die( esc_html__( 'Empty message! Go back and write something.', 'wp-recall' ) );
			}

			$topic_id = pfm_add_topic(
				array(
					'topic_name' => sanitize_text_field( wp_unslash( $_REQUEST['topic_name'] ) ),
					'forum_id'   => absint( $_REQUEST['forum_id'] )
				), array(
					'post_content' => wp_kses_post( wp_unslash( $_REQUEST['post_content'] ) )
				)
			);

			wp_safe_redirect( pfm_get_topic_permalink( $topic_id ) );
			exit;
		case 'post_edit': //редактирование поста

			if ( empty( $_REQUEST['topic_id'] ) || empty( $_REQUEST['post_id'] ) || ! pfm_is_can_post_edit( intval( $_REQUEST['post_id'] ) ) ) {
				return false;
			}

			$post_edit = '';

			if ( pfm_get_option( 'reason-edit', 1 ) ) {

				$post_edit = maybe_unserialize( pfm_get_post_field( absint( $_REQUEST['post_id'] ), 'post_edit' ) );

				$reasonEdit = '';
				if ( ! empty( $_POST['reason_edit'] ) ) {

					$reasonEdit = sanitize_textarea_field( wp_unslash( $_POST['reason_edit'] ) );
				}

				$post_edit[] = array(
					'time'   => current_time( 'mysql' ),
					'author' => pfm_get_user_name( $user_ID ),
					'reason' => $reasonEdit
				);
			}

			pfm_update_post( array(
				'post_content' => wp_kses_post( wp_unslash( $_REQUEST['post_content'] ) ),
				'post_id'      => absint( $_REQUEST['post_id'] ),
				'post_edit'    => $post_edit
			) );

			wp_safe_redirect( pfm_get_post_permalink( absint( $_REQUEST['post_id'] ) ) );
			exit;

		case 'topic_from_post_create': //создание топика из поста

			if ( ! pfm_is_can( 'post_migrate' ) || empty( $_REQUEST['forum_id'] ) ) {
				return false;
			}

			$migratedPost = pfm_get_post( absint( $_REQUEST['post_id'] ) );

			$topic_id = pfm_add_topic( array(
					'topic_name' => sanitize_text_field( wp_unslash( $_REQUEST['topic_name'] ) ),
					'forum_id'   => absint( $_REQUEST['forum_id'] ),
					'user_id'    => $migratedPost->user_id
				)
			);

			if ( ! empty( $_REQUEST['next_posts'] ) ) {

				$posts = pfm_get_posts( array(
					'topic_id'         => $migratedPost->topic_id,
					'post_index__from' => $migratedPost->post_index,
					'number'           => - 1
				) );

				foreach ( $posts as $post ) {
					pfm_update_post( array(
						'post_id'  => $post->post_id,
						'topic_id' => $topic_id
					) );
				}
			} else {

				pfm_update_post( array(
					'post_id'  => $migratedPost->post_id,
					'topic_id' => $topic_id
				) );
			}

			pfm_update_topic_data( $migratedPost->topic_id );
			pfm_update_topic_data( $topic_id );

			wp_safe_redirect( pfm_get_topic_permalink( $topic_id ) );
			exit;

		case 'topic_migrate': //перенос топика в другой форум

			if ( ! pfm_is_can( 'topic_migrate' ) || empty( $_REQUEST['forum_id'] ) ) {
				return false;
			}

			$migratedTopic = pfm_get_topic( absint( $_REQUEST['topic_id'] ) );

			pfm_update_topic( array(
				'topic_id' => absint( $_REQUEST['topic_id'] ),
				'forum_id' => absint( $_REQUEST['forum_id'] )
			) );

			pfm_update_forum_counter( $migratedTopic->forum_id );
			pfm_update_forum_counter( absint( $_REQUEST['forum_id'] ) );

			do_action( 'pfm_migrate_topic', absint( $_REQUEST['topic_id'] ), absint( $_REQUEST['forum_id'] ) );

			wp_safe_redirect( pfm_get_topic_permalink( absint( $_REQUEST['topic_id'] ) ) );
			exit;

		case 'topic_edit': //изменение заголовка топика

			if ( ! pfm_is_can_topic_edit( intval( $_REQUEST['topic_id'] ) ) ) {
				return false;
			}

			pfm_update_topic( array(
				'topic_id'   => absint( $_REQUEST['topic_id'] ),
				'topic_name' => sanitize_text_field( wp_unslash( $_REQUEST['topic_name'] ) )
			) );

			if ( rcl_is_office( $user_ID ) ) {
				wp_safe_redirect( rcl_get_tab_permalink( $user_ID, 'prime-forum' ) );
				exit;
			}

			wp_safe_redirect( pfm_get_topic_permalink( absint( $_REQUEST['topic_id'] ) ) );
			exit;

		case 'member_go':

			wp_safe_redirect( pfm_get_forum_permalink( absint( $_REQUEST['forum_id'] ) ) );
			exit;
	}
	if ( isset( $_POST['_wp_http_referer'] ) ) {
		wp_safe_redirect( wp_unslash( $_POST['_wp_http_referer'] ) );
	}

	exit;
}

rcl_ajax_action( 'pfm_ajax_action', true );
function pfm_ajax_action() {
	global $PrimeActions, $PrimeQuery, $PrimeUser;

	rcl_verify_ajax_nonce();

	$method = isset( $_POST['method'] ) ? sanitize_key( $_POST['method'] ) : '';

	$itemID = ( isset( $_POST['item_id'] ) ) ? absint( $_POST['item_id'] ) : null;

	if ( ! $method || ! isset( $PrimeActions[ $method ] ) ) {
		exit;
	}

	rcl_enqueue_script( 'pfm-scripts', rcl_addon_url( 'js/scripts.js', __FILE__ ) );

	$PrimeUser = new PrimeUser();

	$PrimeQuery = new PrimeQuery();

	do_action( 'pfm_pre_ajax_action' );

	$funcName = $PrimeActions[ $method ];

	$result = $funcName( $itemID );

	if ( ! $result ) {
		$result['error'] = __( 'Unable to perform action', 'wp-recall' );
	}

	do_action( 'pfm_ajax_action', $method, $itemID );

	$result = apply_filters( 'pfm_action_result', $result, $method, $itemID );

	wp_send_json( $result );
}

//сохранение ИД поста в куках для переноса в другой пост
pfm_add_ajax_action( 'confirm_migrate_post', 'pfm_action_confirm_migrate_post' );
function pfm_action_confirm_migrate_post( $post_id ) {

	if ( ! pfm_is_can( 'post_migrate' ) ) {
		return false;
	}
	$formdata = array();

	if ( isset( $_POST['formdata'] ) ) {
		if ( isset( $_POST['formdata'] ) ) {
			//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			parse_str( wp_unslash( $_POST['formdata'] ), $formdata );

			foreach ( $formdata as $k => $v ) {
				if ( $k == 'post_content' ) {
					$formdata[ $k ] = wp_kses_post( $v );
				} else {
					$formdata[ $k ] = sanitize_text_field( $v );
				}
			}
		}
	}

	$migrateData = array(
		'post_id'    => $post_id,
		'next_posts' => 0
	);

	if ( isset( $formdata['next_posts_migrate'][0] ) && $formdata['next_posts_migrate'][0] ) {

		$migrateData['next_posts'] = 1;
	}

	setcookie( 'pfm_migrate_post', json_encode( $migrateData ), time() + 3600, '/', isset( $_SERVER['HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HOST'] ) ) : '' );

	return array(
		'content' => pfm_get_notice( __( 'Go to the page of the necessary topic and press the "Transfer to this topic" button to end message transfer', 'wp-recall' ), 'warning' ),
		'title'   => __( 'Data is ready to be transferred', 'wp-recall' ),
		'dialog'  => true
	);
}

//показ формы с настройками миграции сообщения топика
pfm_add_ajax_action( 'start_post_migrate', 'pfm_action_start_post_migrate' );
function pfm_action_start_post_migrate( $post_id ) {

	if ( ! pfm_is_can( 'post_migrate' ) ) {
		return false;
	}

	$fields = array(
		array(
			'type'   => 'checkbox',
			'slug'   => 'next_posts_migrate',
			'values' => array(
				1 => __( 'Also transfer all subsequent messages', 'wp-recall' )
			)
		)
	);

	$args = array(
		'method'         => 'confirm_migrate_post',
		'serialize_form' => 'manager-migrate-form',
		'item_id'        => $post_id
	);

	$content = '<div id="manager-migrate" class="rcl-custom-fields-box">';
	$content .= '<form id="manager-migrate-form" method="post">';

	foreach ( $fields as $field ) {

		$fieldObject = Rcl_Field::setup( $field );

		$content .= '<div id="field-' . $fieldObject->id . '" class="form-field rcl-custom-field">';

		if ( $fieldObject->title ) {
			$content .= '<label>';
			$content .= $fieldObject->get_title();
			$content .= '</label>';
		}

		$content .= $fieldObject->get_field_input();

		$content .= '</div>';
	}

	$content .= '<div class="form-field fields-submit">';
	$content .= rcl_get_button( array(
		'label'   => __( 'Confirm transfer', 'wp-recall' ),
		'class'   => 'topic-action action-migrate_posts',
		'onclick' => 'pfm_ajax_action(' . json_encode( $args ) . ');return false;'
	) );
	$content .= '</div>';

	$content .= '</form>';
	$content .= '</div>';

	return array(
		'content' => $content,
		'title'   => __( 'Transfer messages to another topic', 'wp-recall' ),
		'dialog'  => true
	);
}

pfm_add_ajax_action( 'cancel_post_migrate', 'pfm_action_cancel_post_migrate' );
function pfm_action_cancel_post_migrate() {
	setcookie( 'pfm_migrate_post', '', time() + 3600, '/', isset( $_SERVER['HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HOST'] ) ) : '' );

	return array(
		'update-page'    => true,
		'preloader_live' => true
	);
}

//перенос поста в другой топик
pfm_add_ajax_action( 'end_post_migrate', 'pfm_action_end_post_migrate' );
function pfm_action_end_post_migrate( $topic_id ) {

	if ( ! isset( $_COOKIE['pfm_migrate_post'] ) || ! pfm_is_can( 'post_migrate' ) ) {
		return false;
	}
	//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$migrateData = rcl_recursive_map( 'sanitize_text_field', json_decode( wp_unslash( $_COOKIE['pfm_migrate_post'] ) ) );

	$post_id = intval( $migrateData->post_id );

	if ( ! $migrateData || ! $post_id ) {
		return array( 'error' => __( 'Unsuccessful transfer', 'wp-recall' ) );
	}

	$post = pfm_get_post( $post_id );

	if ( ! $post ) {
		return false;
	}

	$topicOld = $post->topic_id;

	if ( isset( $migrateData->next_posts ) && $migrateData->next_posts ) {

		$posts = pfm_get_posts( array(
			'topic_id'         => $topicOld,
			'post_index__from' => $post->post_index,
			'number'           => - 1
		) );

		foreach ( $posts as $post ) {
			pfm_update_post( array(
				'post_id'  => $post->post_id,
				'topic_id' => $topic_id
			) );
		}
	} else {

		pfm_update_post( array(
			'post_id'  => $post_id,
			'topic_id' => $topic_id
		) );
	}

	setcookie( 'pfm_migrate_post', '', time() + 3600, '/', isset( $_SERVER['HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HOST'] ) ) : '' );

	pfm_update_topic_data( $topicOld );
	pfm_update_topic_data( $topic_id );

	return array(
		'url-redirect'   => pfm_get_post_permalink( $post_id ),
		'preloader_live' => 1
	);
}

//вызов формы создания топика из поста
pfm_add_ajax_action( 'get_form_topic_create', 'pfm_action_get_form_topic_create' );
function pfm_action_get_form_topic_create( $post_id ) {

	if ( ! pfm_is_can( 'post_migrate' ) ) {
		return false;
	}

	$post = pfm_get_post( $post_id );

	return array(
		'content' => pfm_get_form( array(
			'action'     => 'topic_from_post_create',
			'submit'     => __( 'Save changes', 'wp-recall' ),
			'forum_list' => true,
			'post_id'    => $post_id,
			'values'     => array(
				'post_content' => wp_slash( $post->post_content )
			),
			'fields'     => array(
				array(
					'type'   => 'checkbox',
					'slug'   => 'next_posts',
					'values' => array( 1 => __( 'Also transfer all subsequent messages', 'wp-recall' ) )
				)
			)
		) ),
		'title'   => __( 'Transfer message to a new topic', 'wp-recall' ),
		'dialog'  => true
	);
}

//вызов формы редактирования поста
pfm_add_ajax_action( 'get_form_post_edit', 'pfm_action_get_form_post_edit' );
function pfm_action_get_form_post_edit( $post_id ) {

	if ( ! pfm_is_can_post_edit( $post_id ) ) {
		return false;
	}

	$post = pfm_get_post( $post_id );

	return array(
		'content' => pfm_get_form(
			array(
				'action'   => 'post_edit',
				'submit'   => __( 'Save changes', 'wp-recall' ),
				'post_id'  => $post_id,
				'topic_id' => $post->topic_id,
				'values'   => array(
					'post_content' => wp_slash( $post->post_content )
				)
			)
		),
		'title'   => __( 'Edit messages', 'wp-recall' ),
		'dialog'  => true
	);
}

//удаление поста
pfm_add_ajax_action( 'post_delete', 'pfm_action_post_delete' );
function pfm_action_post_delete( $post_id ) {

	if ( ! pfm_is_can_post_delete( $post_id ) ) {
		return false;
	}

	$post = pfm_get_post( $post_id );

	$res = pfm_delete_post( $post_id );

	if ( ! $res ) {
		return array( 'error' => __( 'Unsuccessful deletion', 'wp-recall' ) );
	}

	$result = array(
		'remove-item' => 'topic-post-' . $post_id
	);

	$topic = pfm_get_topic( $post->topic_id );

	if ( $topic->post_count == 1 ) {
		$result['url-redirect']   = pfm_get_forum_permalink( $topic->forum_id );
		$result['preloader_live'] = 1;
	}

	return $result;
}

//закрытие топика
pfm_add_ajax_action( 'topic_close', 'pfm_action_topic_close' );
function pfm_action_topic_close( $topic_id ) {

	if ( ! pfm_is_can( 'topic_close' ) ) {
		return false;
	}

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return array( 'error' => __( 'Unable to close topic', 'wp-recall' ) );
	}

	pfm_topic_close( $topic_id );

	return array(
		'update-page'    => true,
		'preloader_live' => 1
	);
}

//открытие топика
pfm_add_ajax_action( 'topic_unclose', 'pfm_action_topic_unclose' );
function pfm_action_topic_unclose( $topic_id ) {

	if ( ! pfm_is_can( 'topic_close' ) ) {
		return false;
	}

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return array( 'error' => __( 'Unable to open topic', 'wp-recall' ) );
	}

	pfm_topic_unclose( $topic_id );

	return array(
		'update-page'    => 1,
		'preloader_live' => 1
	);
}

//удаление топика
pfm_add_ajax_action( 'topic_delete', 'pfm_action_topic_delete' );
function pfm_action_topic_delete( $topic_id ) {

	if ( ! pfm_is_can_topic_delete( $topic_id ) ) {
		return false;
	}

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return array( 'error' => __( 'Unable to delete topic', 'wp-recall' ) );
	}

	pfm_delete_topic( $topic_id );

	$url = pfm_get_forum_permalink( $topic->forum_id );

	if ( ! empty( $_POST['topic_id'] ) ) {
		$result = array( 'url-redirect' => $url );
	} else if ( isset( $_POST['current_page'] ) ) {
		$result = array( 'url-redirect' => pfm_add_number_page( $url, absint( $_POST['current_page'] ) ) );
	}

	$result['preloader_live'] = 1;

	return $result;
}

//вызов формы переноса топика в другой форум
pfm_add_ajax_action( 'get_form_topic_migrate', 'pfm_action_get_form_topic_migrate' );
function pfm_action_get_form_topic_migrate( $topic_id ) {

	if ( ! pfm_is_can( 'topic_migrate' ) ) {
		return false;
	}

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return array( 'error' => __( 'Unable to get topic', 'wp-recall' ) );
	}

	$content = '<div id="post-manager" class="manager-box">';
	$content .= pfm_get_form( array(
		'action'         => 'topic_migrate',
		'submit'         => __( 'Transfer topic', 'wp-recall' ),
		'topic_id'       => $topic_id,
		'forum_list'     => true,
		'exclude_fields' => array(
			'topic_name',
			'post_content'
		)
	) );
	$content .= '</div>';

	return array(
		'content' => $content,
		'dialog'  => true,
		'title'   => __( 'Transfer topic to another forum', 'wp-recall' )
	);
}

//вызов формы изменения названия топика
pfm_add_ajax_action( 'get_form_topic_edit', 'pfm_action_get_form_topic_edit' );
function pfm_action_get_form_topic_edit( $topic_id ) {

	if ( ! pfm_is_can_topic_edit( $topic_id ) ) {
		return false;
	}

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return array( 'error' => __( 'Unable to get topic', 'wp-recall' ) );
	}

	$args = array(
		'action'         => 'topic_edit',
		'submit'         => __( 'Save changes', 'wp-recall' ),
		'forum_id'       => $topic->forum_id,
		'topic_id'       => $topic_id,
		'values'         => array(
			'topic_name' => $topic->topic_name
		),
		'exclude_fields' => array(
			'post_content'
		)
	);

	if ( $metas = RQ::tbl( new PrimeMeta() )->select( [
		'meta_key',
		'meta_value'
	] )->where( [
		'object_id'   => $topic_id,
		'object_type' => 'topic',
	] )->get_results() ) {
		foreach ( $metas as $meta ) {
			//phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			$args['values'][ $meta->meta_key ] = $meta->meta_value;
		}
	}

	$content = '<div id="post-manager" class="manager-box">';
	$content .= pfm_get_form( $args );
	$content .= '</div>';

	return array(
		'content' => $content,
		'dialog'  => true,
		'title'   => __( 'Edit topic', 'wp-recall' )
	);
}

//закрепление топика
pfm_add_ajax_action( 'topic_fix', 'pfm_action_topic_fix' );
function pfm_action_topic_fix( $topic_id ) {

	if ( ! pfm_is_can( 'topic_fix' ) ) {
		return false;
	}

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return array( 'error' => __( 'Unable to pin topic', 'wp-recall' ) );
	}

	pfm_update_topic( array(
		'topic_id'  => $topic_id,
		'topic_fix' => 1
	) );

	return array(
		'update-page'    => true,
		'preloader_live' => 1
	);
}

//открепление топика
pfm_add_ajax_action( 'topic_unfix', 'pfm_action_topic_unfix' );
function pfm_action_topic_unfix( $topic_id ) {

	if ( ! pfm_is_can( 'topic_fix' ) ) {
		return false;
	}

	$topic = pfm_get_topic( $topic_id );

	if ( ! $topic ) {
		return array( 'error' => __( 'Unable to unpin topic', 'wp-recall' ) );
	}

	pfm_update_topic( array(
		'topic_id'  => $topic_id,
		'topic_fix' => 0
	) );

	return array(
		'update-page'    => true,
		'preloader_live' => 1
	);
}

//получение цитаты публикации
pfm_add_ajax_action( 'get_post_excerpt', 'pfm_action_get_post_excerpt' );
function pfm_action_get_post_excerpt( $post_id ) {

	if ( ! pfm_is_can( 'post_create' ) ) {
		return false;
	}

	$post = pfm_get_post( $post_id );

	if ( ! $post ) {
		return array( 'error' => __( 'Unable to get the message quote', 'wp-recall' ) );
	}

	$author_name = $post->user_id ? pfm_get_user_name( $post->user_id ) : $post->guest_name;

	if ( ! empty( $_POST['excerpt'] ) ) {

		$content = wp_kses_post( wp_unslash( $_POST['excerpt'] ) );

		if ( strpos( $post->post_content, $content ) !== false ) {
			$content = '<blockquote><strong>' . $author_name . ' ' . __( 'said', 'wp-recall' ) . ' </strong><br />' . $content . '</blockquote><br />';
		} else {
			$content = '<blockquote>' . $content . '</blockquote><br />';
		}
	} else {

		$content = $post->post_content;

		$content = '<blockquote><strong>' . $author_name . ' ' . __( 'said', 'wp-recall' ) . ' </strong><br />' . $content . '</blockquote><br />';
	}

	$content = str_replace( array(
		'<br />' . chr( 13 ) . chr( 10 ),
		'<br />',
		'<br/>',
		'<br>'
	), "\n", $content );

	$content = str_replace( '<p></p>', "\n\n", $content );
	$content = str_replace( '<p> </p>', "\n\n", $content );
	$content = str_replace( '<p>', '', $content );
	$content = str_replace( '</p>', chr( 13 ) . chr( 10 ), $content );

	$content = htmlspecialchars_decode( $content, ENT_COMPAT );

	return array(
		'content'  => $content,
		'place-id' => '#editor-action_post_create'
	);
}

//получение списка форумов
pfm_add_ajax_action( 'get_structure', 'pfm_action_get_structure' );
function pfm_action_get_structure() {

	$content = '<div id="forum-manager" class="manager-box">';
	$content .= pfm_get_form( array(
		'action'         => 'member_go',
		'submit'         => __( 'Go to the selected forum', 'wp-recall' ),
		'forum_list'     => true,
		'exclude_fields' => array(
			'topic_name',
			'post_content'
		)
	) );
	$content .= '</div>';

	return array(
		'content' => $content,
		'dialog'  => true,
		'title'   => __( 'Jump to the forum', 'wp-recall' )
	);
}

//получение списка форумов
pfm_add_ajax_action( 'get_author_topics', 'pfm_action_get_author_topics' );
function pfm_action_get_author_topics() {
	global $user_ID;

	return array(
		'content' => pfm_get_user_topics_list( $user_ID, false ),
		'dialog'  => true,
		'title'   => __( 'Last started topics', 'wp-recall' )
	);
}

//получение обновленных тем
pfm_add_ajax_action( 'get_last_updated_topics', 'pfm_action_get_last_updated_topics' );
function pfm_action_get_last_updated_topics() {
	global $wpdb, $PrimeTopic, $PrimeQuery;

	$theme = pfm_get_current_theme();

	//phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
	$topics = $wpdb->get_results(
		"SELECT "
		. "ptopics.*, "
		. "MAX(pfm_posts.post_date) AS last_post_date, "
		. "MAX(pfm_posts.post_id) AS last_post_id "
		. "FROM "
		. RCL_PREF . "pforum_topics AS ptopics "
		. "INNER JOIN " . RCL_PREF . "pforum_posts AS pfm_posts ON ptopics.topic_id = pfm_posts.topic_id "
		. "GROUP BY ptopics.topic_id "
		. "ORDER BY MAX(pfm_posts.post_date) DESC "
		. "LIMIT 20"
	);
	//phpcs:enable WordPress.DB.PreparedSQL.NotPrepared

	$PrimeQuery->last['posts'] = $PrimeQuery->get_topics_last_post( $topics );

	$content = '<div id="prime-forum">';

	if ( $topics ) {
		$content .= '<div class="prime-topics-list prime-loop-list">';
		foreach ( wp_unslash( $topics ) as $PrimeTopic ) {
			$content .= rcl_get_include_template( 'pfm-single-topic.php', $theme['path'] );
		}
		$content .= '</div>';
	} else {

		$content .= pfm_get_notice( __( 'Nothing found', 'wp-recall' ) );
	}

	$content .= '</div>';

	return array(
		'content' => $content,
		'dialog'  => true,
		'title'   => __( 'Updated forum topics', 'wp-recall' )
	);
}

//получение приватного чата
pfm_add_ajax_action( 'get_private_chat', 'pfm_action_get_private_chat' );
function pfm_action_get_private_chat( $user_id ) {

	$chatdata = rcl_get_chat_private( $user_id );

	return array(
		'content'      => $chatdata['content'],
		'dialog'       => true,
		'dialog-width' => 'small',
		'title'        => __( 'Chat with', 'wp-recall' ) . ' ' . get_the_author_meta( 'display_name', $user_id ),
		'onClose'      => array( 'rcl_chat_clear_beat', array( $chatdata['token'] ) )
	);
}

//получение информации о пользователе
pfm_add_ajax_action( 'get_author_info', 'pfm_action_get_author_info' );
function pfm_action_get_author_info( $user_id ) {
	return array(
		'content'      => rcl_get_user_details( $user_id, array( 'zoom' => false ) ),
		'dialog'       => true,
		'dialog-width' => 'auto',
		'dialog-class' => 'rcl-user-getails',
		'title'        => __( 'Detailed information', 'wp-recall' )
	);
}

//предпросмотр сообщения
pfm_add_ajax_action( 'get_preview', 'pfm_action_get_preview' );
function pfm_action_get_preview() {

	$formdata = array();

	if ( isset( $_POST['formdata'] ) ) {
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		parse_str( wp_unslash( $_POST['formdata'] ), $formdata );

		foreach ( $formdata as $k => $v ) {
			if ( $k == 'post_content' ) {
				$formdata[ $k ] = wp_kses_post( $v );
			} else {
				$formdata[ $k ] = sanitize_text_field( $v );
			}
		}
	}

	$postContent = wp_unslash( $formdata['post_content'] );

	if ( ! $postContent ) {

		return array( 'error' => __( 'Empty message!', 'wp-recall' ) );
	}

	global $PrimeShorts, $PrimePost, $user_ID;

	$PrimeShorts = pfm_get_shortcodes();

	$theme = rcl_get_addon( get_site_option( 'rcl_pforum_template' ) );

	$postData = array(
		'post_id'         => 0,
		'user_id'         => $user_ID,
		'post_content'    => $postContent,
		'post_date'       => current_time( 'mysql' ),
		'display_name'    => $user_ID ? get_the_author_meta( 'display_name', $user_ID ) : '',
		'guest_name'      => ! $user_ID ? sanitize_text_field( $formdata['guest_name'] ) : '',
		'guest_email'     => ! $user_ID ? sanitize_email( wp_unslash( $formdata['guest_email'] ) ) : '',
		'user_registered' => $user_ID ? get_the_author_meta( 'user_registered', $user_ID ) : ''
	);

	$PrimePost = apply_filters( 'pfm_preview_postdata', $postData );

	$PrimePost = ( object ) $PrimePost;

	$content = '<div id="prime-forum">';

	$content .= rcl_get_include_template( 'pfm-single-post.php', $theme['path'] );

	$content .= '</div>';

	return array(
		'content'      => $content,
		'dialog'       => true,
		'dialog-width' => 'small',
		'title'        => __( 'Preview', 'wp-recall' )
	);
}

pfm_add_ajax_action( 'post_create', 'pfm_action_post_create' );
function pfm_action_post_create() {
	global $user_ID;

	$formdata = array();

	if ( isset( $_POST['formdata'] ) ) {
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		parse_str( wp_unslash( $_POST['formdata'] ), $formdata );

		foreach ( $formdata as $k => $v ) {
			if ( $k == 'post_content' ) {
				$formdata[ $k ] = wp_kses_post( $v );
			} else {
				$formdata[ $k ] = sanitize_text_field( $v );
			}
		}
	}

	if ( ! pfm_is_can( 'post_create' ) || ! $formdata['topic_id'] ) {
		return array( 'error' => __( 'Insufficient rights to publish', 'wp-recall' ) );
	}

	$topic = pfm_get_topic( absint( $formdata['topic_id'] ) );

	if ( $topic->topic_closed ) {
		return array( 'error' => __( 'Topic closed', 'wp-recall' ) );
	}

	if ( ! $formdata['post_content'] ) {
		return array( 'error' => __( 'Empty message! Go back and write something.', 'wp-recall' ) );
	}

	$lastPost = get_topic_last_post( absint( $formdata['topic_id'] ) );

	if ( $lastPost->post_content == wp_unslash( $formdata['post_content'] ) ) {
		return array( 'error' => __( 'Repeat the last message!', 'wp-recall' ) );
	}

	$args = array(
		'post_content' => $formdata['post_content'],
		'topic_id'     => absint( $formdata['topic_id'] )
	);

	if ( ! $user_ID ) {

		if ( ! sanitize_email( wp_unslash( $formdata['guest_email'] ) ) || ! $formdata['guest_name'] ) {
			return array( 'error' => __( 'Error', 'wp-recall' ) );
		}

		$args['guest_email'] = sanitize_email( wp_unslash( $formdata['guest_email'] ) );
		$args['guest_name']  = sanitize_text_field( $formdata['guest_name'] );
	}

	do_action( 'pfm_before_add_post', $args );

	$post_id = pfm_add_post( $args );

	if ( pfm_is_can( 'topic_close' ) ) {

		if ( isset( $formdata['close-topic'][0] ) && $formdata['close-topic'][0] ) {

			$topicClose = pfm_topic_close( $formdata['topic_id'] );

			if ( $topicClose ) {
				return array(
					'url-redirect'   => pfm_get_post_permalink( $post_id ),
					'preloader_live' => 1
				);
			}
		}
	}

	if ( isset( $formdata['redirect'] ) && $formdata['redirect'] == 'post-url' ) {
		return array(
			'url-redirect'   => pfm_get_post_permalink( $post_id ),
			'preloader_live' => 1
		);
	}

	$lastPosts = RQ::tbl( new PrimePosts() )
	               ->select( [ 'post_id' ] )
	               ->where( [
		               'topic_id' => $formdata['topic_id'],
	               ] )
	               ->date( 'post_date', '>', $formdata['form_load'] )
	               ->orderby( 'post_id', 'ASC' )
	               ->get_col();

	$result = array();

	if ( $lastPosts ) {
		foreach ( $lastPosts as $lastPost ) {
			$result['content'][] = pfm_get_post_box( $lastPost );
		}
	}

	$result['post_id']     = $post_id;
	$result['topic_id']    = $formdata['topic_id'];
	$result['current_url'] = pfm_get_post_permalink( $post_id );
	$result['form_load']   = current_time( 'mysql' );
	$result['append']      = '#prime-forum .prime-posts';

	return $result;
}
