<?php
global $Rcl_History_Orders;

$Rcl_History_Orders->prepare_items();

echo '<div class="wrap"><h2>' . esc_html__( 'Order history', 'wp-recall' ) . '</h2>';

echo wp_kses( rcl_get_chart_orders( $Rcl_History_Orders->items ), rcl_kses_allowed_html() );
?>
    <form method="get">
		<?php
		$currentStatus = ( isset( $_GET['sts'] ) ) ? intval( $_GET['sts'] ) : 0;
		$sts           = rcl_order_statuses();
		?>
        <select name="sts" id="filter-by-status">
            <option<?php selected( $currentStatus, 0 ); ?>
                    value="0"><?php esc_html_e( 'All statuses', 'wp-recall' ); ?></option>
			<?php
			foreach ( $sts as $id => $name ) {
				printf( "<option %s value='%s'>%s</option>\n", selected( $id, $currentStatus, false ), esc_attr( $id ), esc_attr( $name )
				);
			}
			?>
        </select>
        <span class="rcl-datepicker-box">
		<input type="text" name="date-start" id="orders-date-start" onclick="rcl_show_datepicker( this );"
               class="rcl-datepicker"
               value="<?php echo ( isset( $_GET['date-start'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['date-start'] ) ) ) : ''; ?>">
	</span>
        <span class="date-separator">-</span>
        <span class="rcl-datepicker-box">
		<input type="text" name="date-end" id="orders-date-end" onclick="rcl_show_datepicker( this );"
               class="rcl-datepicker"
               value="<?php echo ( isset( $_GET['date-end'] ) ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['date-end'] ) ) ) : ''; ?>">
	</span>
        <input type="hidden" name="page" value="manage-rmag">
		<?php submit_button( __( 'Filter', 'wp-recall' ), 'button', '', false, array( 'id' => 'search-submit' ) ); ?>
    </form>
    <form method="post">
        <input type="hidden" name="page" value="manage-rmag">
<?php
$Rcl_History_Orders->search_box( __( 'Search', 'wp-recall' ), 'search_id' );
$Rcl_History_Orders->display();
echo '</form></div>';
