<?php

add_action( 'admin_init', 'rcl_options_products' );
function rcl_options_products() {
	add_meta_box( 'recall_meta', __( 'WP-Recall settings', 'wp-recall' ), 'rcl_options_box', 'products', 'normal', 'high' );
}

add_action( 'admin_init', 'rcl_products_fields', 1 );
function rcl_products_fields() {
	add_meta_box( 'products_fields', __( 'Product features', 'wp-recall' ), 'rcl_metabox_products', 'products', 'normal', 'high' );
}

function rcl_metabox_products( $post ) {

	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui-sortable' );

	$PrVars = new Rcl_Product_Variations( array( 'product_id' => $post->ID ) );

	$content = '<div class="rcl-product-meta">';

	$content .= '<label class="meta-title">' . esc_html_e( 'Product price', 'wp-recall' ) . '</label>';

	$content .= '<div class="meta-content">';

	$content .= '<input type="text" name="wprecall[price-products]" value="' . esc_attr( get_post_meta( $post->ID, 'price-products', 1 ) ) . '"> ' . rcl_get_primary_currency( 2 );

	$content .= '</div>';

	$content .= '</div>';

	$content .= '<div class="rcl-product-meta">';

	$content .= '<label class="meta-title">' . esc_html__( 'Product old price', 'wp-recall' ) . '</label>';

	$content .= '<div class="meta-content">';

	$content .= '<input type="text" name="wprecall[product-oldprice]" value="' . esc_attr( get_post_meta( $post->ID, 'product-oldprice', 1 ) ) . '"> ' . rcl_get_primary_currency( 2 );

	$content .= '</div>';

	$content .= '</div>';

	if ( $PrVars->variations ):

		$content .= '<div class="rcl-product-meta">';

		$content .= '<label class="meta-title">' . esc_html__( 'Product variations', 'wp-recall' ) . '</label>';

		$content .= '<div class="meta-content">';

		$content .= '<div class="rcl-variations-list">';

		foreach ( $PrVars->variations as $variation ) {

			$content .= '<div class="variation-box">';

			$content .= '<input type="checkbox" class="variation-checkbox" name="product-variations[' . esc_attr( $variation['slug'] ) . '][status]" ' . checked( $PrVars->product_exist_variation( $variation['slug'] ), true, false ) . ' value="1" id="variation-' . esc_attr( $variation['slug'] ) . '"><label class="variation-title" for="variation-' . esc_attr( $variation['slug'] ) . '">' . esc_html( $variation['title'] ) . '</label>';

			$content .= '<div class="variation-values">';

			foreach ( $variation['values'] as $k => $value ) {

				$productVal = $PrVars->get_product_variation_value( $variation['slug'], $value );
				$varPrice   = $productVal ? $productVal['price'] : '';

				$content .= '<div class="variation-value">';
				$content .= '<span class="variation-value-name">' . esc_html( $value ) . '</span>';
				$content .= '<input type="number" name="product-variations[' . esc_attr( $variation['slug'] ) . '][values][' . esc_attr( $k ) . '][price]" value="' . esc_attr( $varPrice ) . '">';
				$content .= '<input type="hidden" name="product-variations[' . esc_attr( $variation['slug'] ) . '][values][' . esc_attr( $k ) . '][name]" value="' . esc_attr( $value ) . '">';
				$content .= '</div>';
			}

			$content .= '</div>';

			$content .= '</div>';
		}

		$content .= '</div>';

		$content .= '</div>';

		$content .= '</div>';

	endif;

	$content .= '<div class="rcl-product-meta">';

	$content .= '<div class="meta-content">';

	$content .= '<input type="checkbox" name="wprecall[outsale]" ' . checked( get_post_meta( $post->ID, 'outsale', 1 ), 1, false ) . ' value="1"> ' . esc_html__( 'Withdraw from sale', 'wp-recall' );

	$content .= '</div>';

	$content .= '</div>';

	if ( rcl_get_commerce_option( 'sistem_related_products' ) == 1 ):

		$related = get_post_meta( $post->ID, 'related_products_recall', 1 );

		$rel_prodcat     = ( isset( $related['prodcat'] ) ) ? $related['prodcat'] : '';
		$rel_product_tag = ( isset( $related['product_tag'] ) ) ? $related['product_tag'] : '';

		$content .= '<div class="rcl-product-meta">';

		$content .= '<label class="meta-title">' . esc_html__( 'Similar and recommended products', 'wp-recall' ) . '</label>';

		$content .= '<div class="meta-content">';

		$args = array(
			'show_option_none' => esc_html__( 'Choose a category', 'wp-recall' ),
			'hide_empty'       => 0,
			'echo'             => 0,
			'selected'         => $rel_prodcat,
			'hierarchical'     => 0,
			'name'             => 'wprecall[related_products_recall][prodcat]',
			'id'               => 'name',
			'class'            => 'postform',
			'taxonomy'         => 'prodcat',
			'hide_if_empty'    => false
		);

		$content .= wp_dropdown_categories( $args ) . ' - ' . esc_html__( 'Select a product category', 'wp-recall' );

		$content .= '</div>';

		$content .= '<div class="meta-content">';

		$args = array(
			'show_option_none' => esc_html__( 'Select a tag', 'wp-recall' ),
			'hide_empty'       => 0,
			'echo'             => 0,
			'selected'         => $rel_product_tag,
			'hierarchical'     => 0,
			'name'             => 'wprecall[related_products_recall][product_tag]',
			'id'               => 'name',
			'class'            => 'postform',
			'taxonomy'         => 'product_tag',
			'hide_if_empty'    => false
		);

		$content .= wp_dropdown_categories( $args ) . ' - ' . esc_html__( 'select a product tag', 'wp-recall' );

		$content .= '</div>';

		$content .= '</div>';

	endif;

	$metaBox = '<div class="rcl-products-metabox">';
	$metaBox .= apply_filters( 'rcl_products_custom_fields', $content, $post );
	$metaBox .= '</div>';

	$metaBox .= '<input type="hidden" name="rcl_commerce_fields_nonce" value="' . esc_attr( wp_create_nonce( __FILE__ ) ) . '" />';

	echo wp_kses( $metaBox, rcl_kses_allowed_html() );
}

add_action( 'save_post_products', 'rcl_commerce_fields_update', 10 );
function rcl_commerce_fields_update( $post_id ) {

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}

	if ( ! isset( $_POST['rcl_commerce_fields_nonce'] ) ) {
		return false;
	}

	if ( ! isset( $_POST['rcl_commerce_fields_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['rcl_commerce_fields_nonce'] ), __FILE__ ) ) {
		return false;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}

	if ( ! isset( $_POST['product-variations'] ) ) {

		delete_post_meta( $post_id, 'product-variations' );
	} else {
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$new_variations = rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_POST['product-variations'] ) );
		$variations     = array();
		foreach ( $new_variations as $varSlug => $var ) {

			if ( ! isset( $var['status'] ) || ! $var['status'] ) {
				continue;
			}

			$variations[] = array(
				'slug'   => $varSlug,
				'values' => $var['values']
			);
		}

		if ( $variations ) {
			update_post_meta( $post_id, 'product-variations', $variations );
		} else {
			delete_post_meta( $post_id, 'product-variations' );
		}
	}

	if ( ! isset( $_POST['wprecall']['outsale'] ) ) {
		delete_post_meta( $post_id, 'outsale' );
	}

	if ( ! isset( $_POST['wprecall']['availability_product'] ) ) {
		delete_post_meta( $post_id, 'availability_product' );
	}

	if ( empty( $_POST['children_prodimage'] ) ) {

		delete_post_meta( $post_id, 'children_prodimage' );
	} else {
		//phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$prod_images_ids = rcl_recursive_map( 'absint', wp_unslash( $_POST['children_prodimage'] ) );
		update_post_meta( $post_id, 'children_prodimage', implode( ',', $prod_images_ids ) );
	}

	return $post_id;
}
