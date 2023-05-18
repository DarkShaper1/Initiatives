<?php

add_filter( 'the_content', 'rcl_add_product_box', 10 );
function rcl_add_product_box( $content ) {
	global $post;

	if ( $post->post_type != 'products' || doing_filter( 'get_the_excerpt' ) ) {
		return $content;
	}

	$content = apply_filters( 'rcl_product_content', $content );

	if ( doing_filter( 'the_content' ) ) {

		if ( ! in_array( 'top', rcl_get_commerce_option( 'cart_button_single_page', array( 'top', 'bottom' ) ) ) ) {
			return $content;
		}
	}

	return rcl_get_product_box( $post->ID ) . $content;
}

function rcl_get_product_box( $product_id ) {

	$cartBox = new Rcl_Cart_Button_Form( array(
		'product_id' => $product_id
	) );

	$oldSlider = get_post_meta( $product_id, 'recall_slider', 1 );
	$gallery   = get_post_meta( $product_id, 'rcl_post_gallery', 1 );

	$content = '<div id="rcl-product-box">';

	if ( $gallery || $oldSlider ) {

		$content .= '<div class="product-gallery">';

		$content .= rcl_get_product_gallery( $product_id );

		$content .= '</div>';
	}

	$content .= '<div class="product-metabox">';

	$content .= $cartBox->cart_form();

	$content .= '</div>';

	$content .= '</div>';

	return $content;
}
