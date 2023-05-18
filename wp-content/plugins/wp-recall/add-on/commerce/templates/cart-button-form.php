<?php
/**
 * @var object $Cart_Button
 * @var object $Product_Variations
 */
?>
<form class="rcl-cart-form" data-product="<?php echo esc_attr( $Cart_Button->product_id ) ?>" method="post">

	<?php
	if ( $Cart_Button->output['old_price'] ) {
		echo wp_kses( $Cart_Button->old_price_box(), rcl_kses_allowed_html() );
	}
	?>

	<?php
	if ( $Cart_Button->output['price'] ) {
		echo wp_kses( $Cart_Button->price_box( $Cart_Button->output['variations'] ? $Product_Variations : false ), rcl_kses_allowed_html() );
	}
	?>

	<?php
	if ( $Cart_Button->output['variations'] ) {
		echo wp_kses( $Cart_Button->variations_box( $Cart_Button->product_id ), rcl_kses_allowed_html() );
	}
	?>

	<?php do_action( 'rcl_cart_button_form_middle', $Cart_Button ); ?>

	<?php
	if ( $Cart_Button->output['quantity'] ) {
		echo wp_kses( $Cart_Button->quantity_selector_box(), rcl_kses_allowed_html() );
	}
	?>

	<?php
	if ( $Cart_Button->output['cart_button'] ) {
		echo wp_kses( $Cart_Button->cart_button(), rcl_kses_allowed_html() );
	}
	?>

	<?php do_action( 'rcl_cart_button_form_bottom', $Cart_Button ); ?>

    <input type="hidden" name="cart[product_id]" value="<?php echo esc_attr( $Cart_Button->product_id ) ?>">

</form>
