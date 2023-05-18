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
class Rcl_Field_Uploader extends Rcl_Field_Abstract {

	public $required;
	public $fix_editor;
	public $filename = '';
	public $filetitle = '';
	public $file_types = 'jpg, jpeg, png';
	public $crop = 0;
	public $max_size = 512;
	public $max_files = 5;
	public $multiple = 1;
	public $dropzone = 0;
	public $mode_output = 'grid';
	public $temp_media = 1;
	public $uploader_props = array();

	function __construct( $args ) {
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
			),
			array(
				'slug'       => 'max_files',
				'default'    => $this->max_files,
				'type'       => 'runner',
				'value_min'  => 1,
				'value_max'  => 100,
				'value_step' => 1,
				'title'      => __( 'Max number of files', 'wp-recall' ),
			),
			array(
				'slug'    => 'dropzone',
				'default' => $this->dropzone,
				'type'    => 'radio',
				'values'  => array(
					__( 'Disabled', 'wp-recall' ),
					__( 'Enabled', 'wp-recall' )
				),
				'title'   => __( 'Dropzone', 'wp-recall' ),
			),
			array(
				'slug'    => 'mode_output',
				'default' => $this->mode_output,
				'type'    => 'radio',
				'values'  => array(
					'grid' => __( 'Cards', 'wp-recall' ),
					'list' => __( 'List', 'wp-recall' ),
					//'gallery'	 => __( 'Gallery', 'wp-recall' )
				),
				'title'   => __( 'Mode of files output', 'wp-recall' ),
			),
			array(
				'slug'    => 'fix_editor',
				'default' => $this->fix_editor,
				'type'    => 'text',
				'title'   => __( 'ID of an attaching editor', 'wp-recall' ),
				'notice'  => __( 'You can attach this uploader for one of text editors, pointing its ID', 'wp-recall' ),
			)
		);
	}

	function get_uploader_props() {
		global $user_ID;

		return wp_parse_args( $this->uploader_props, array(
			'temp_media'   => $this->temp_media,
			'fix_editor'   => $this->fix_editor,
			'required'     => intval( $this->required ),
			'user_id'      => $user_ID,
			'min_width'    => 200,
			'min_height'   => 200,
			'filename'     => $this->filename,
			'filetitle'    => $this->filetitle,
			'dropzone'     => $this->dropzone,
			'multiple'     => $this->multiple,
			'max_size'     => $this->max_size,
			'auto_upload'  => $this->multiple ? 1 : 0,
			'file_types'   => array_map( 'trim', explode( ',', $this->file_types ) ),
			'max_files'    => $this->max_files,
			'crop'         => $this->multiple ? 0 : $this->crop,
			'input_attach' => $this->input_name,
			'mode_output'  => $this->mode_output
		) );
	}

	function get_uploader() {
		return new Rcl_Uploader( $this->id, $this->get_uploader_props() );
	}

	function get_input() {

		$uploader = $this->get_uploader();

		$content = '';

		if ( rcl_is_ajax() ) {

			ob_start();

			/* global $wp_scripts, $wp_styles;

			  $wp_scripts->do_items( array(
			  //'jquery-ui-core',
			  'fileupload-ui-widget',
			  //'jquery-ui-widget',
			  'jquery-ui-sortable',
			  'rcl-core-scripts',
			  'jquery-iframe-transport',
			  'jquery-fileupload',
			  'jquery-fileupload-process',
			  'jquery-fileupload-image',
			  'rcl-uploader-scripts',
			  ) );

			  $wp_styles->do_items( array(
			  'rcl-uploader-style'
			  ) );

			  $content .= ob_get_contents(); */

			ob_end_clean();
		}

		$content .= $uploader->get_gallery( $this->value, true );

		$content .= $uploader->get_uploader();

		return $content;
	}

	function get_value() {

		if ( ! $this->value ) {
			return false;
		}

		$attachList = '';

		if ( $this->mode_output == 'gallery' ) {

			/* $width = 100;

			  $galArgs = array(
			  'id' => 'rcl-gallery-'.$this->id,
			  'attach_ids' => $this->value,
			  //'center_align' => true,
			  //'width' => (count($this->value) < 7)? count($this->value) * 73: 500,
			  'height' => $width,
			  'slides' => array(
			  'slide' => array($width,$width),
			  'full' => 'large'
			  ),
			  'options' => array(
			  '$SlideWidth' => $width,
			  '$SlideSpacing' => 3
			  )
			  );

			  if(count($attach_ids) >= 7){
			  $galArgs['navigator'] = array(
			  'arrows' => true
			  );
			  }

			  $content = rcl_get_image_gallery($galArgs); */

			$content = rcl_get_image_gallery( array(
				'id'           => 'rcl-gallery-' . $this->id,
				'center_align' => true,
				'attach_ids'   => $this->value,
				//'width' => 500,
				'height'       => 250,
				'slides'       => array(
					'slide' => 'large',
					'full'  => 'large'
				),
				'navigator'    => array(
					'thumbnails' => array(
						'width'  => 50,
						'height' => 50,
						'arrows' => true
					)
				)
			) );
		} else {

			$attachIDs = is_array( $this->value ) ? $this->value : array( $this->value );

			global $wpdb;
			$IDs = RQ::tbl( new Rcl_Query( [
				'name' => $wpdb->posts,
				'cols' => [ 'ID', 'post_type' ]
			] ) )->select( [ 'ID' ] )
			         ->where( [ 'post_type' => 'attachment', 'ID__in' => $attachIDs ] )
			         ->limit( - 1 )
			         ->orderby( 'post_title', 'ASC' )
			         ->get_col();

			foreach ( $IDs as $ID ) {
				$attachList .= $this->get_single_attachment( $ID );
			}
		}

		if ( ! $attachList ) {
			return false;
		}

		$content = '<div id="rcl-gallery-' . $this->id . '" class="rcl-upload-gallery mode-' . $this->mode_output . '">';
		$content .= $attachList;
		$content .= '</div>';

		return $content;
	}

	function get_single_attachment( $attach_id ) {

		$is_image = wp_attachment_is_image( $attach_id );

		if ( $is_image ) {

			$image = wp_get_attachment_image( $attach_id, 'thumbnail' );
		} else {

			$image = wp_get_attachment_image( $attach_id, array( 100, 100 ), true );
		}

		if ( ! $image ) {
			return false;
		}

		$url = wp_get_attachment_url( $attach_id );

		$content = '<div class="gallery-attachment gallery-attachment-' . $attach_id . ' ' . ( $is_image ? 'type-image' : 'type-file' ) . '">';

		$content .= '<a href="' . $url . '" target="_blank">' . $image . '</a>';

		$content .= '<div class="attachment-title">';
		$content .= '<a href="' . $url . '" target="_blank">' . basename( get_post_field( 'guid', $attach_id ) ) . '</a>';
		$content .= '</div>';

		$content .= '</div>';

		return $content;
	}

}
