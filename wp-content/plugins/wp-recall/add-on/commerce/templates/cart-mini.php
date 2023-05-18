<?php
/* Шаблон для отображения содержимого шорткода minibasket - малой корзины пользователя */
/* Данный шаблон можно разместить в папке используемого шаблона /wp-content/wp-recall/templates/ и он будет подключаться оттуда */
?>
<?php $Cart = new Rcl_Cart(); ?>
<div class="rcl-mini-cart <?php echo ( $Cart->products_amount ) ? 'not-empty' : 'empty-cart'; ?>">

    <div class="cart-icon">
        <i class="rcli fa-shopping-cart"></i>
    </div>
    <div><?php esc_html_e( 'In your cart', 'wp-recall' ); ?>:</div>
    <div class="cart-content">
        <span class="products-amount">
			<?php esc_html_e( 'Total number of goods', 'wp-recall' ); ?>: <span
                    class="rcl-order-amount"><?php echo esc_html( $Cart->products_amount ); ?></span> шт.
        </span>
        <span class="cart-price">
			<?php esc_html_e( 'Total amount', 'wp-recall' ); ?>: <span
                    class="rcl-order-price"><?php echo esc_html( $Cart->order_price ); ?></span>
        </span>
        <span class="cart-url">
            <a href="<?php echo esc_url( $Cart->cart_url ); ?>"><?php esc_html_e( 'Go to cart', 'wp-recall' ); ?></a>
        </span>
    </div>
    <div class="empty-notice"><?php esc_html_e( 'Empty', 'wp-recall' ); ?></div>
</div>
