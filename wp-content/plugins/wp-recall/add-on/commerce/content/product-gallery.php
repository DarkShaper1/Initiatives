<?php

function rcl_get_product_gallery( $product_id, $size = 'rcl-product-thumb' ) {

	$oldSlider = get_post_meta( $product_id, 'recall_slider', 1 );
	$gallery   = get_post_meta( $product_id, 'rcl_post_gallery', 1 );

	if ( ! $gallery ) {
		$gallery = array();
	}

	if ( ! $gallery && $oldSlider ) {

		$attach_ids = get_post_meta( $product_id, 'children_prodimage', 1 );

		if ( $attach_ids ) {

			$gallery = array_unique( array_merge( $gallery, explode( ',', $attach_ids ) ) );
		}
	}

	if ( has_post_thumbnail( $product_id ) ) {
		$gallery = array_unique( array_merge( [ get_post_thumbnail_id( $product_id ) ], $gallery ) );
	}

	if ( ! $gallery ) {
		return false;
	}

	return rcl_get_image_gallery( array(
		'id'         => 'rcl-product-gallery-' . $product_id,
		'attach_ids' => $gallery,
		'width'      => 350,
		'height'     => 350,
		'slides'     => array(
			'slide' => $size,
			'full'  => 'large'
		),
		'navigator'  => array(
			'thumbnails' => array(
				'width'  => 50,
				'height' => 50,
				'arrows' => true
			)
		)
	) );
}
