<?php
/* Шаблон для отображения содержимого шорткода basket - полной корзины пользователя */
/* Данный шаблон можно разместить в папке используемого шаблона /wp-content/wp-recall/templates/ и он будет подключаться оттуда */
/**
 * @var object $Cart Объект корзины
 */
?>

<?php global $post; ?>

<?php do_action( 'rcl_cart_before' ); ?>

<table class="order-table rcl-form">
    <tr>
        <th class="column-product-name">
			<?php esc_html_e( 'Product', 'wp-recall' ); ?>
        </th>
        <th class="column-product-price">
			<?php esc_html_e( 'Price', 'wp-recall' ); ?>
        </th>
        <th class="column-product-amount">
			<?php esc_html_e( 'Amount', 'wp-recall' ); ?>
        </th>
        <th class="column-product-sumprice">
			<?php esc_html_e( 'Sum', 'wp-recall' ); ?>
        </th>
    </tr>
	<?php foreach ( $Cart->products as $k => $product ): setup_postdata( $post = get_post( $product->product_id ) ); ?>
        <tr id="product-<?php the_ID(); ?>-<?php echo esc_html( $k ); ?>" class="product-box">
            <td class="column-product-name">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				<?php rcl_product_excerpt( $post->ID ); ?>
				<?php rcl_product_variation_list( $product->variations ); ?>
            </td>
            <td class="column-product-price">
                <div class="rcl-cart-subtitle"><?php esc_html_e( 'Price', 'wp-recall' ); ?>:</div>
                <span><?php echo esc_html( $product->product_price ); ?></span><?php echo rcl_get_primary_currency( 1 );//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </td>
            <td class="column-product-amount">
                <div class="rcl-cart-subtitle"><?php esc_html_e( 'Amount', 'wp-recall' ); ?>:</div>
                <div class="quantity-selector">
                    <a class="edit-amount add-product"
                       onclick="rcl_cart_add_product(<?php echo esc_js( $product->product_id ); ?>,<?php echo esc_js( $k ); ?> );return false;"
                       href="#">
                        <i class="rcli fa-plus"></i>
                    </a>
                    <span class="product-amount">
						<?php echo esc_html( $product->product_amount ); ?>
					</span>
                    <a class="edit-amount remove-product"
                       onclick="rcl_cart_remove_product(<?php echo esc_js( $product->product_id ); ?>,<?php echo esc_js( $k ); ?> );return false;"
                       href="#">
                        <i class="rcli fa-minus"></i>
                    </a>
                </div>
            </td>
            <td class="column-product-sumprice">
                <div class="rcl-cart-subtitle"><?php esc_html_e( 'Sum', 'wp-recall' ); ?>:</div>
                <span class="product-sumprice">
					<?php echo esc_html( $product->product_price * $product->product_amount ); ?>
				</span>
				<?php echo wp_kses_post( rcl_get_primary_currency( 1 ) ); ?>
            </td>
        </tr>
	<?php
	endforeach;
	wp_reset_postdata();
	?>
    <tr>
        <th colspan="2"><?php esc_html_e( 'Total', 'wp-recall' ); ?></th>
        <th class="column-product-amount total-amount">
            <span class="rcl-order-amount">
				<?php echo esc_html( $Cart->products_amount ); ?>
            </span>
        </th>
        <th class="column-product-sumprice total-sumprice">
            <span class="rcl-order-price">
				<?php echo esc_html( $Cart->order_price ); ?>
            </span>
			<?php echo wp_kses_post( rcl_get_primary_currency( 1 ) ); ?>
        </th>
    </tr>
</table>

<?php do_action( 'rcl_cart' ); ?>
