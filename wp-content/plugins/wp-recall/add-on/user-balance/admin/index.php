<?php
require_once 'class-rcl-payments-history.php';
require_once 'addon-settings.php';

add_action( 'admin_init', 'rcl_payments_options_init', 10 );
function rcl_payments_options_init() {

	if ( ! rcl_gateways()->gateways ) {
		return false;
	}

	foreach ( rcl_gateways()->gateways as $gateWayID => $className ) {

		rcl_gateways()->gateway( $gateWayID )->options_init();
	}
}

add_action( 'admin_head', 'rcl_admin_user_account_scripts' );
function rcl_admin_user_account_scripts() {
	wp_enqueue_script( 'jquery-core' );
	wp_enqueue_script( 'rcl_admin_user_account_scripts', plugins_url( 'assets/scripts.js', __FILE__ ) );
}

// создаем допколонку для вывода баланса пользователя
add_filter( 'manage_users_columns', 'rcl_balance_user_admin_column' );
function rcl_balance_user_admin_column( $columns ) {

	return array_merge( $columns, array( 'balance_user_recall' => __( "Balance", 'wp-recall' ) )
	);
}

add_filter( 'manage_users_custom_column', 'rcl_balance_user_admin_content', 10, 3 );
function rcl_balance_user_admin_content( $custom_column, $column_name, $user_id ) {

	switch ( $column_name ) {
		case 'balance_user_recall':
			$custom_column = '<input type="text" class="balanceuser-' . $user_id . '" size="4" value="' . rcl_get_user_balance( $user_id ) . '">'
			                 . '<input type="button" class="button edit_balance" id="user-' . $user_id . '" value="Ok">';
			break;
	}

	return $custom_column;
}

function rcl_get_chart_payments( $pays ) {
	global $chartData, $chartArgs;

	if ( ! $pays ) {
		return false;
	}

	$chartArgs = array();
	$chartData = array(
		'title'   => __( 'Income dynamics', 'wp-recall' ),
		'title-x' => __( 'Time period', 'wp-recall' ),
		'data'    => array(
			array(
				__( '"Days/Months"', 'wp-recall' ),
				__( '"Payments (PCs.)"', 'wp-recall' ),
				__( '"Income (thousands)"', 'wp-recall' )
			)
		)
	);

	foreach ( $pays as $pay ) {
		$pay = ( object ) $pay;
		rcl_setup_chartdata( $pay->time_action, $pay->pay_amount );
	}

	return rcl_get_chart( $chartArgs );
}

/* * ***********************************************
  Меняем баланс пользователя из админки
 * *********************************************** */
rcl_ajax_action( 'rcl_edit_balance_user' );
function rcl_edit_balance_user() {

	if ( ! current_user_can( 'administrator' ) ) {
		wp_send_json( array(
			'error' => esc_html__( 'Error', 'wp-recall' )
		) );
	}

	$user_id = isset( $_POST['user'] ) ? intval( $_POST['user'] ) : 0;
	$balance = isset( $_POST['balance'] ) ? floatval( str_replace( ',', '.', sanitize_text_field( wp_unslash( $_POST['balance'] ) ) ) ) : 0;

	do_action( 'rcl_pre_edit_user_balance_by_admin', $user_id, $balance );

	if ( ! $user_id ) {
		wp_send_json( array( 'error' => esc_html__( 'Balance was not changed', 'wp-recall' ) ) );
	}

	rcl_update_user_balance( $balance, $user_id, __( 'Balance changed', 'wp-recall' ) );

	wp_send_json( array(
		'success' => esc_html__( 'Balance successfully changed', 'wp-recall' ),
		'user_id' => $user_id,
		'balance' => $balance
	) );
}

add_action( 'admin_menu', 'rcl_statistic_user_pay_page', 25 );
function rcl_statistic_user_pay_page() {
	$prim = 'manage-rmag';
	if ( ! function_exists( 'rcl_commerce_menu' ) ) {
		$prim = 'manage-wpm-options';
		add_menu_page( 'Rcl Commerce', 'Rcl Commerce', 'manage_options', $prim, 'rmag_global_options' );
		add_submenu_page( $prim, __( 'Payment systems', 'wp-recall' ), __( 'Payment systems', 'wp-recall' ), 'manage_options', $prim, 'rmag_global_options' );
	}

	$hook = add_submenu_page( $prim, __( 'Payments', 'wp-recall' ), __( 'Payments', 'wp-recall' ), 'manage_options', 'manage-wpm-cashe', 'rcl_admin_statistic_cashe' );
	add_action( "load-$hook", 'rcl_payments_page_options' );
}

function rcl_payments_page_options() {
	global $Rcl_Payments_History;
	$option = 'per_page';
	$args   = array(
		'label'   => __( 'Payments', 'wp-recall' ),
		'default' => 50,
		'option'  => 'rcl_payments_per_page'
	);
	add_screen_option( $option, $args );
	$Rcl_Payments_History = new Rcl_Payments_History();
}

function rcl_admin_statistic_cashe() {
	global $Rcl_Payments_History;

	$Rcl_Payments_History->prepare_items();
	$sr = ( $Rcl_Payments_History->total_items ) ? floor( $Rcl_Payments_History->sum / $Rcl_Payments_History->total_items ) : 0;

	echo '<div class="wrap"><h2>' . esc_html__( 'Payment history', 'wp-recall' ) . '</h2>';

	echo '<p>' . esc_html__( 'All payments', 'wp-recall' ) . ': ' . esc_html( $Rcl_Payments_History->total_items ) . ' ' . esc_html__( 'for the amount of', 'wp-recall' ) . ' ' . esc_html( $Rcl_Payments_History->sum ) . ' ' . wp_kses_post( rcl_get_primary_currency( 1 ) ) . ' (' . esc_html__( 'Average check', 'wp-recall' ) . ': ' . esc_html( $sr ) . ' ' . wp_kses_post( rcl_get_primary_currency( 1 ) ) . ')</p>';
	echo '<p>' . esc_html__( 'Total in the system', 'wp-recall' ) . ': ' . esc_html( $Rcl_Payments_History->sum_balance ) . ' ' . wp_kses_post( rcl_get_primary_currency( 1 ) ) . '</p>';
	//echo '<p>Средняя выручка за сутки: '.$day_pay.' '.rcl_get_primary_currency(1).'</p>';
	echo wp_kses( rcl_get_chart_payments( $Rcl_Payments_History->items ), rcl_kses_allowed_html() );

	?>
    <form method="get">
        <input type="hidden" name="page" value="manage-wpm-cashe">
		<?php
		$Rcl_Payments_History->months_dropdown( 'rcl_payments' );
		submit_button( __( 'Filter', 'wp-recall' ), 'button', '', false, array( 'id' => 'search-submit' ) );
		?>
    </form>
    <form method="post">
        <input type="hidden" name="page" value="manage-wpm-cashe">
	<?php
	$Rcl_Payments_History->search_box( __( 'Search', 'wp-recall' ), 'search_id' );
	$Rcl_Payments_History->display();
	echo '</form></div>';
}
