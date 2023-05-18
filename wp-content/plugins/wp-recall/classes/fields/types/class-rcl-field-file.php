<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class-rcl-custom-field-text
 *
 * @author Андрей
 */
class Rcl_Field_File extends Rcl_Field_Uploader {

	public $mode_output = 'list';
	public $multiple = 0;

	function __construct( $args ) {

		if ( isset( $args['ext-files'] ) ) {
			$args['file_types'] = $args['ext-files'];
		}

		if ( isset( $args['sizefile'] ) ) {
			$args['max_size'] = $args['sizefile'];
		}

		parent::__construct( $args );
	}

	function get_options() {

		return array(
			array(
				'slug'       => 'max_size',
				'default'    => $this->max_size,
				'type'       => 'runner',
				'unit'       => 'Kb',
				'value_min'  => 256,
				'value_max'  => 5120,
				'value_step' => 256,
				'title'      => __( 'File size', 'wp-recall' ),
				'notice'     => __( 'maximum size of uploaded file, Kb (Default - 512)', 'wp-recall' )
			),
			array(
				'slug'    => 'file_types',
				'default' => $this->file_types,
				'type'    => 'text',
				'title'   => __( 'Allowed file types', 'wp-recall' ),
				'notice'  => __( 'allowed types of files are divided by comma, for example: pdf, zip, jpg', 'wp-recall' )
			)
		);
	}

	function get_uploader_props() {
		global $user_ID;

		return wp_parse_args( $this->uploader_props, array(
			'user_id'      => $user_ID,
			'multiple'     => 0,
			'temp_media'   => 1,
			'max_size'     => $this->max_size,
			'auto_upload'  => 1,
			'file_types'   => array_map( 'trim', explode( ',', $this->file_types ) ),
			'max_files'    => 1,
			'crop'         => $this->crop,
			'filename'     => $this->filename,
			'filetitle'    => $this->filetitle,
			'input_attach' => $this->input_name,
			'mode_output'  => 'list'
		) );
	}

}
