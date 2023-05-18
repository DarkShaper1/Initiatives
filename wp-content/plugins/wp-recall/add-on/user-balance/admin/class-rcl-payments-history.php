<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

add_action( 'admin_init', array( 'Rcl_Payments_History', 'delete_payment' ) );

class Rcl_Payments_History extends WP_List_Table {

	var $per_page = 50;
	var $current_page = 1;
	var $total_items;
	var $offset = 0;
	var $sum_balance;
	var $sum = 0;

	function __construct() {
		global $status, $page;

		parent::__construct( array(
			'singular' => __( 'payment', 'wp-recall' ),
			'plural'   => __( 'payments', 'wp-recall' ),
			'ajax'     => false
		) );

		$this->per_page     = $this->get_items_per_page( 'rcl_payments_per_page', 50 );
		$this->current_page = $this->get_pagenum();
		$this->offset       = ( $this->current_page - 1 ) * $this->per_page;

		add_action( 'admin_head', array( &$this, 'admin_header' ) );
	}

	function admin_header() {
		$page = ( isset( $_GET['page'] ) ) ? sanitize_key( $_GET['page'] ) : false;
		if ( 'manage-wpm-cashe' != $page ) {
			return;
		}
		echo '<style>';
		echo '.wp-list-table .column-payment_number { width: 5%; }';
		echo '.wp-list-table .column-payment_user { width: 30%; }';
		echo '.wp-list-table .column-payment_id { width: 15%; }';
		echo '.wp-list-table .column-payment_sum { width: 10%;}';
		echo '.wp-list-table .column-payment_date { width: 15%;}';
		echo '.wp-list-table .column-pay_system { width: 15%;}';
		echo '.wp-list-table .column-pay_type { width: 10%;}';
		echo '</style>';
	}

	function no_items() {
		esc_html_e( 'No payments found.', 'wp-recall' );
	}

	function column_default( $item, $column_name ) {

		switch ( $column_name ) {
			case 'payment_number':
				return $item->ID;
			case 'payment_user':
				return $item->user_id . ': ' . get_the_author_meta( 'user_login', $item->user_id );
			case 'payment_id':
				return $item->payment_id;
			case 'payment_sum':
				return $item->pay_amount . ' ' . rcl_get_primary_currency( 2 );
			case 'payment_date':
				return $item->time_action;
			case 'pay_system':
				return $item->pay_system;
			case 'pay_type':
				if ( $item->pay_type == 1 ) {
					$item->pay_type = 'user-balance';
				}

				if ( $item->pay_type == 2 ) {
					$item->pay_type = 'order-payment';
				}

				return $item->pay_type;
			default:
				return print_r( $item, true );
		}
	}

	function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'payment_number' => '№',
			'payment_user'   => __( 'Users', 'wp-recall' ),
			'payment_id'     => __( 'Payments ID', 'wp-recall' ),
			'payment_sum'    => __( 'Sum', 'wp-recall' ),
			'payment_date'   => __( 'Date', 'wp-recall' ),
			'pay_system'     => __( 'Payment system', 'wp-recall' ),
			'pay_type'       => __( 'Payment type', 'wp-recall' )
		);
	}

	function column_payment_user( $item ) {
		$page    = isset( $_REQUEST['page'] ) ? sanitize_key( $_REQUEST['page'] ) : '';
		$actions = array(
			'delete'       => sprintf( '<a href="?page=%s&action=%s&payment=%s">' . __( 'Delete payment', 'wp-recall' ) . '</a>', $page, 'delete', $item->ID ),
			'all-payments' => sprintf( '<a href="?page=%s&action=%s&user_id=%s">' . __( 'All user payments', 'wp-recall' ) . '</a>', $page, 'all-payments', $item->user_id ),
		);

		return sprintf( '%1$s %2$s', $item->user_id . ': ' . get_the_author_meta( 'user_login', $item->user_id ), $this->row_actions( $actions ) );
	}

	function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'wp-recall' ),
		);
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="payments[]" value="%s" />', $item->ID
		);
	}

	function months_dropdown( $post_type ) {
		global $wpdb, $wp_locale;
		//phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$months = $wpdb->get_results( "
			SELECT DISTINCT YEAR( time_action ) AS year, MONTH( time_action ) AS month
			FROM " . RMAG_PREF . "pay_results
			ORDER BY time_action DESC
		" );
		//phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
		$months = apply_filters( 'months_dropdown_results', $months, $post_type );

		$month_count = count( $months );

		if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
			return;
		}

		$m = isset( $_GET['m'] ) ? sanitize_text_field( wp_unslash( $_GET['m'] ) ) : 0;
		?>
        <label for="filter-by-date"
               class="screen-reader-text"><?php esc_html_e( 'Filter by date' ); ?></label>
        <select name="m" id="filter-by-date">
            <option <?php selected( $m, 0 ); ?> value="0"><?php esc_html_e( 'All dates' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year ) {
					continue;
				}
				$month = zeroise( $arc_row->month, 2 );
				$year  = $arc_row->year;
				printf( "<option %s value='%s'>%s</option>\n", selected( $m, $year . '-' . $month, false ), esc_attr( $arc_row->year . '-' . $month ),
					/* translators: 1: month name, 2: 4-digit year */ sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year ) //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
			?>
        </select>
		<?php
	}

	static function delete_payment() {
		global $wpdb;

		$page = ( isset( $_GET['page'] ) ) ? sanitize_key( $_GET['page'] ) : false;
		if ( 'manage-wpm-cashe' != $page ) {
			return;
		}

		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'delete' ) {

			if ( isset( $_REQUEST['payment'] ) ) {
				$payment = intval( $_REQUEST['payment'] );
				//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->query( $wpdb->prepare( "DELETE FROM " . RMAG_PREF . "pay_results WHERE ID = %d", $payment ) );
			}

			if ( isset( $_REQUEST['payments'] ) ) {
				//phpcs:ignore  WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$payments = rcl_recursive_map( 'sanitize_text_field', wp_unslash( $_REQUEST['payments'] ) );
				$cnt      = count( $payments );
				for ( $a = 0; $a < $cnt; $a ++ ) {
					$id = intval( $payments[ $a ] );
					if ( $id ) {
						//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$wpdb->query( $wpdb->prepare( "DELETE FROM " . RMAG_PREF . "pay_results WHERE ID = %d", $id ) );
					}
				}
			}
		}
	}

	function get_sum_balance() {
		global $wpdb;

		//phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_var( "SELECT SUM(CAST(user_balance AS DECIMAL)) FROM " . RMAG_PREF . "users_balance WHERE user_balance!='0'" );
	}

	function get_data() {

		$payments = new Rcl_Payments();

		$tableAs = $payments->table['as'];

		$payments->limit( $this->per_page, $this->offset );

		if ( isset( $_POST['s'] ) ) {

			$payments->where_string( "($tableAs.user_id = '" . intval( $_POST['s'] ) . "' OR $tableAs.payment_id = '" . intval( $_POST['s'] ) . "')" );

			if ( ! empty( $_GET['m'] ) ) {
				$payments->where( [ 'time_action__like' => sanitize_text_field( wp_unslash( $_GET['m'] ) ) . '-' ] );
			}
		} else if ( ! empty( $_GET['m'] ) ) {
			$payments->where( [ 'time_action__like' => sanitize_text_field( wp_unslash( $_GET['m'] ) ) . '-' ] );
		} else if ( isset( $_GET['user_id'] ) ) {
			$payments->where( [ 'user_id' => intval( $_GET['user_id'] ) ] );
		}

		$items = $payments->get_results();

		$payments->select( [ 'sum' => [ 'pay_amount' ] ] );

		$this->total_items = $payments->get_count();

		$this->sum = rcl_commercial_round( $payments->get_var() );

		$this->sum_balance = rcl_commercial_round( $this->get_sum_balance() );

		return $items;
	}

	function prepare_items() {

		$data                  = $this->get_data();
		$this->_column_headers = $this->get_column_info();
		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $this->per_page
		) );

		$this->items = $data;
	}

}
