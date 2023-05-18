<?php
/* Шаблон для отображения содержимого истории заказов пользователя */
/* Данный шаблон можно разместить в папке используемого шаблона /wp-content/wp-recall/templates/ и он будет подключаться оттуда */
?>
<?php global $rcl_orders; ?>
<div class="order-data rcl-form">
    <table>
        <tr>
            <th><?php esc_html_e( 'Order number', 'wp-recall' ); ?></th>
            <th><?php esc_html_e( 'Order date', 'wp-recall' ); ?></th>
            <th><?php esc_html_e( 'Number of goods', 'wp-recall' ); ?></th>
            <th><?php esc_html_e( 'Sum', 'wp-recall' ); ?></th>
            <th><?php esc_html_e( 'Order status', 'wp-recall' ); ?></th>
        </tr>
		<?php foreach ( $rcl_orders as $order ) { ?>
            <tr>
                <td>
                    <a href="<?php echo esc_url( rcl_get_tab_permalink( $order->user_id, 'orders' ) ); ?>&order-id=<?php echo esc_attr( $order->order_id ); ?>">
						<?php esc_html_e( 'Order', 'wp-recall' ); ?>: <?php echo absint( $order->order_id ); ?>
                    </a>
                </td>
                <td><?php echo esc_html( $order->order_date ); ?></td>
                <td><?php echo esc_html( $order->products_amount ); ?></td>
                <td><?php echo esc_html( $order->order_price ) . ' ' . wp_kses_post( rcl_get_primary_currency( 1 ) ); ?></td>
                <td><?php echo wp_kses_post( rcl_get_status_name_order( $order->order_status ) ); ?></td>
            </tr>
		<?php } ?>
        <tr>
            <th colspan="5"></th>
        </tr>
    </table>
</div>
