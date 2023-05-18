<?php

class Rcl_Cart_Button_Form {

	public $product_id;
	public $product_price;
	public $product_old_price;
	public $product_amount;
	public $product_status;
	public $label;
	public $output = array(
		'price'       => true,
		'old_price'   => true,
		'variations'  => true,
		'quantity'    => true,
		'cart_button' => true,
	);

	function __construct( $args ) {

		$args = apply_filters( 'rcl_cart_button_form_props', $args );

		$this->init_properties( $args );

		if ( ! $this->label ) {
			$this->label = __( 'To cart', 'wp-recall' );
		}
	}

	function init_properties( $args ) {
		global $post;

		$properties = get_class_vars( get_class( $this ) );

		foreach ( $properties as $name => $val ) {
			if ( isset( $args[ $name ] ) ) {
				$this->$name = $args[ $name ];
			}
		}

		if ( ! $this->product_id && $post ) {
			$this->product_id = $post->ID;
		}

		if ( ! $this->product_price ) {
			$this->product_price = $this->get_price();
		}

		if ( ! $this->product_old_price ) {
			$this->product_old_price = $this->get_old_price();
		}

		$this->product_status = ( get_post_meta( $this->product_id, 'outsale', 1 ) ) ? 0 : 1;
	}

	function price_box( $Product_Variations = false ) {

		$content = '<span class="product-price">';

		if ( $this->product_price || $Product_Variations && $Product_Variations->get_product_variations( $this->product_id ) ) {
			$content .= '<span class="current-price">' . $this->product_price . '</span> ' . rcl_get_primary_currency( 1 );
		} else {
			$content .= '<span class="current-price">' . __( 'Free', 'wp-recall' ) . '</span>';
		}

		$content .= '</span>';

		return $content;
	}

	function old_price_box() {

		if ( ! $this->product_old_price ) {
			return false;
		}

		$content = '<span class="product-old-price">';

		$content .= $this->product_old_price . ' ' . rcl_get_primary_currency( 1 );

		$content .= '</span>';

		return $content;
	}

	function get_old_price() {
		global $post;

		if ( $post && is_object( $post ) && isset( $post->product_old_price ) ) {

			$this->product_old_price = $post->product_old_price;
		} else {

			$this->product_old_price = get_post_meta( $this->product_id, 'product-oldprice', 1 );
		}

		return $this->product_old_price;
	}

	function get_price() {
		global $post;

		if ( $post && is_object( $post ) && isset( $post->product_price ) ) {

			$this->product_price = $post->product_price;
		} else {

			$this->product_price = get_post_meta( $this->product_id, 'price-products', 1 );
		}

		return $this->product_price;
	}

	function cart_form( $args = false ) {

		$this->output = apply_filters( 'rcl_cart_button_form_args', wp_parse_args( $args, $this->output ), $this->product_id );

		if ( ! $this->output ) {
			return false;
		}

		$PrVars = new Rcl_Product_Variations();

		if ( $PrVars->get_product_variations( $this->product_id ) ) {
			rcl_dialog_scripts();
		}

		$content = '<div class="rcl-cart-box">';

		$content .= rcl_get_include_template( 'cart-button-form.php', __FILE__, array(
			'Cart_Button'        => apply_filters( 'rcl_cart_button_form', $this ),
			'Product_Variations' => $PrVars
		) );

		$content .= '</div>';

		return $content;
	}

	function cart_button() {

		if ( $this->product_status ) {
			$content = rcl_get_button( array(
				'label'   => $this->label,
				'onclick' => 'rcl_add_to_cart(this);return false;',
				'icon'    => 'fa-shopping-cart',
				'class'   => 'rcl-in-to-cart'
			) );
		} else {
			$content = rcl_get_button( array(
				'label'  => __( 'Not available', 'wp-recall' ),
				'icon'   => 'fa-refresh',
				'class'  => 'outsale-product',
				'status' => 'active'
			) );
		}

		return '<span class="cart-button">' . $content . '</span>';
	}

	function quantity_selector_box() {

		if ( ! $this->product_status ) {
			return false;
		}

		$content = '<span class="quantity-selector">';

		$content .= '<a href="#" class="edit-quantity" onclick="rcl_add_product_quantity(this);return false;"><i class="rcli fa-plus" aria-hidden="true"></i></a>';
		$content .= '<span class="quantity-field"><input type="number" min="1" name="cart[quantity]" value="1"></span>';
		$content .= '<a href="#" class="edit-quantity" onclick="rcl_remove_product_quantity(this);return false;"><i class="rcli fa-minus" aria-hidden="true"></i></a>';

		$content .= '</span>';

		return $content;
	}

	function variations_box( $product_id ) {

		$PrVars = new Rcl_Product_Variations();

		$productVars = $PrVars->get_product_variations( $product_id );

		if ( ! $productVars ) {
			return false;
		}

		$box_id = rand( 0, 100 );

		$content = '<div id="cart-box-' . $box_id . '" class="product-variations">';

		$content .= '<input type="hidden" name="cart[isset][variations]" value="1">';

		foreach ( $productVars as $k => $vars ) {

			$variation = $PrVars->get_variation( $vars['slug'] );

			/* перезаписываем доступные варианты вариации вариантами товара */
			$variation['values'] = array();
			foreach ( $vars['values'] as $val ) {
				$variation['values'][] = $val['name'];
			}
			/**/

			if ( isset( $variation['empty-first'] ) ) {

				array_unshift( $productVars[ $k ]['values'], array(
					'price' => "0",
					'name'  => $variation['empty-first']
				) );

				$variation['empty-value'] = $variation['empty-first'];
			}

			$variation['value_in_key'] = true;

			$variation['slug'] = 'cart[variations][' . $variation['slug'] . ']';

			$fieldObject = Rcl_Field::setup( $variation );

			$content .= '<div class="variation-box">';

			$content .= '<span class="variation-title">' . $variation['title'] . '</span>';

			$content .= $fieldObject->get_field_input();

			$content .= '</div>';
		}

		$content .= '<script>rcl_init_variations({'
		            . 'box_id: ' . $box_id . ','
		            . 'product_id: ' . $this->product_id . ','
		            . 'product_price: ' . $this->product_price . ','
		            . 'variations: ' . json_encode( $productVars )
		            . '});</script>';

		$content .= '</div>';

		return $content;
	}

}
