<?php

function pfm_get_form( $args = false ) {

	$PrimeForm = new PrimeForm( $args );

	return $PrimeForm->get_form();
}

function pfm_the_topic_form() {
	global $PrimeForum, $PrimeQuery;

	if ( ! $PrimeForum || ! $PrimeForum->forum_id || $PrimeQuery->errors ) {
		return;
	}

	if ( $PrimeForum->forum_closed ) {

		echo wp_kses( pfm_get_notice( esc_html__( 'The forum is closed. It is impossible to create new topics.', 'wp-recall' ) ), rcl_kses_allowed_html() );

		return;
	}

	if ( ! pfm_is_can( 'topic_create' ) ) {
		$notice = pfm_get_notice( esc_html__( 'You are not authorised to publish new topics in this forum', 'wp-recall' ), 'warning' );
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'pfm_notice_noaccess_topic_form', wp_kses( $notice, rcl_kses_allowed_html() ) );

		return;
	}

	echo wp_kses( pfm_get_form( apply_filters( 'pfm_topic_form_args', array(
		'forum_id' => $PrimeForum->forum_id,
		'action'   => 'topic_create',
		'submit'   => esc_html__( 'Create topic', 'wp-recall' )
	) ) ), rcl_kses_allowed_html() );
}

function pfm_the_post_form() {
	global $PrimeTopic, $PrimeQuery;

	if ( ! $PrimeTopic || ! $PrimeTopic->topic_id || $PrimeQuery->errors ) {
		return;
	}

	if ( $PrimeTopic->forum_closed ) {

		echo wp_kses( pfm_get_notice( esc_html__( 'The forum is closed. It is impossible to create new topics.', 'wp-recall' ) ), rcl_kses_allowed_html() );

		return;
	}

	if ( $PrimeTopic->topic_closed ) {

		echo wp_kses( pfm_get_notice( esc_html__( 'The topic is closed. It is prohibited to publish new topics.', 'wp-recall' ) ), rcl_kses_allowed_html() );

		return;
	}

	if ( ! pfm_is_can( 'post_create' ) ) {
		$notice = pfm_get_notice( esc_html__( 'You are not authorised to publish messages in this topic', 'wp-recall' ), 'warning' );
		//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'pfm_notice_noaccess_post_form', wp_kses( $notice, rcl_kses_allowed_html() ) );

		return;
	}

	$args = array(
		'method'         => 'post_create',
		'serialize_form' => 'prime-topic-form'
	);

	$formArgs = array(
		'topic_id' => $PrimeTopic->topic_id,
		'action'   => 'post_create',
		'onclick'  => 'pfm_ajax_action(' . json_encode( $args ) . ',this);return false;',
		'submit'   => __( 'Add message', 'wp-recall' )
	);

	$pageAmount = ceil( $PrimeTopic->post_count / $PrimeQuery->number );

	if ( $PrimeQuery->current_page < $pageAmount ) {
		$formArgs['fields'][] = array(
			'type'  => 'hidden',
			'slug'  => 'redirect',
			'value' => 'post-url'
		);
	}

	echo wp_kses( pfm_get_form( $formArgs ), rcl_kses_allowed_html() );
}

add_filter( 'pfm_form_bottom', 'pfm_add_manager_fields_post_form', 10, 2 );
function pfm_add_manager_fields_post_form( $content, $action ) {
	if ( $action != 'post_create' ) {
		return $content;
	}

	if ( ! pfm_is_can( 'topic_close' ) ) {
		return $content;
	}

	$fields = array(
		array(
			'type'   => 'checkbox',
			'slug'   => 'close-topic',
			'values' => array(
				1 => __( 'Close topic', 'wp-recall' )
			)
		)
	);

	$content .= '<div class="post-form-manager">';

	foreach ( $fields as $field ) {

		$content .= Rcl_Field::setup( $field )->get_field_input();
	}

	$content .= '</div>';

	return $content;
}

add_filter( 'pfm_form_bottom', 'pfm_add_smilies_post_form', 10, 2 );
function pfm_add_smilies_post_form( $content, $action ) {

	if ( ! in_array( $action, array(
		'topic_create',
		'post_create',
		'post_edit',
		'topic_from_post_create'
	) ) ) {
		return $content;
	}

	$content .= rcl_get_smiles( 'editor-action_' . $action );

	return $content;
}

add_filter( 'pfm_form_fields', 'pfm_add_post_reason_edit_field', 10, 2 );
function pfm_add_post_reason_edit_field( $fields, $action ) {

	if ( $action != 'post_edit' ) {
		return $fields;
	}

	if ( ! pfm_get_option( 'reason-edit', 1 ) ) {
		return $fields;
	}

	$fields[] = array(
		'type'      => 'text',
		'title'     => __( 'The reason for the edit', 'wp-recall' ),
		'slug'      => 'reason_edit',
		'maxlength' => 70
	);

	return $fields;
}
