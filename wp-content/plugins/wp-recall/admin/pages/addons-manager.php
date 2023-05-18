<?php
rcl_dialog_scripts();

global $active_addons, $Rcl_Addons_Manager;

$Rcl_Addons_Manager->get_addons_data();

$cnt_all   = count( $Rcl_Addons_Manager->addons_data );
$cnt_act   = count( $active_addons );
$cnt_inact = $cnt_all - $cnt_act;

echo '<div class="wrap">';

echo '<div id="icon-plugins" class="icon32"><br></div>
    <h2>' . esc_html__( 'WP-Recall Add-ons', 'wp-recall' ) . '</h2>';

if ( isset( $_GET['update-addon'] ) ) {

	$type        = 'updated';
	$text_notice = '';

	switch ( $_GET['update-addon'] ) {
		case 'activate':
			$text_notice = esc_html__( 'Add-on activated. New settings may be available on WP-Recall page', 'wp-recall' );
			break;
		case 'deactivate':
			$text_notice = esc_html__( 'Add-on deactivated.', 'wp-recall' );
			break;
		case 'delete':
			$text_notice = esc_html__( 'Files and data Add-ons have been deleted.', 'wp-recall' );
			break;
		case 'upload':
			$text_notice = esc_html__( 'Add-on has been successfully loaded. You can activate this add-on.', 'wp-recall' );
			break;
		case 'error-info':
			$text_notice = esc_html__( 'Add-on has not been loaded. Correct headers not found.', 'wp-recall' );
			$type        = 'error';
			break;
		case 'error-activate':
			$text_notice = isset( $_GET['error-text'] ) ? sanitize_text_field( wp_unslash( $_GET['error-text'] ) ) : 'Error';
			$type        = 'error';
			break;
	}

	echo '<div id="message" class="' . esc_attr( $type ) . '"><p>' . esc_html( $text_notice ) . '</p></div>';
}

if ( isset( $_POST['save-rcl-key'], $_POST['_wpnonce'], $_POST['rcl-key'] ) ) {
	if ( wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add-rcl-key' ) ) {
		update_site_option( 'rcl-key', sanitize_text_field( wp_unslash( $_POST['rcl-key'] ) ) );
		echo '<div id="message" class="updated"><p>' . esc_html__( 'Key has been saved', 'wp-recall' ) . '!</p></div>';
	}
}
//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<div class="rcl-admin-service-box rcl-key-box">';

echo '<h4>' . esc_html__( 'RCLKEY', 'wp-recall' ) . '</h4>
    <form action="" method="post">
        ' . esc_html__( 'Enter RCLKEY', 'wp-recall' ) . ' <input type="text" name="rcl-key" value="' . esc_attr( get_site_option( 'rcl-key' ) ) . '">
        <input class="button" type="submit" value="' . esc_html__( 'Save', 'wp-recall' ) . '" name="save-rcl-key">
        ' . wp_nonce_field( 'add-rcl-key', '_wpnonce', true, false ) . '
    </form>
    <p class="install-help">' . esc_html__( 'The key is required to update the add-ons here. You can get it in your personal account of website', 'wp-recall' ) . ' <a href="https://codeseller.ru/" target="_blank">https://codeseller.ru</a></p>';

echo '</div>';

echo '<div class="rcl-admin-service-box rcl-upload-form-box upload-addon">';

echo '<h4>' . esc_html__( 'Install the add-on to WP-Recall format .ZIP', 'wp-recall' ) . '</h4>
    <p class="install-help">' . esc_html__( 'If you have the add-on archive for WP-Recall format .zip, you can upload and install it here.', 'wp-recall' ) . '</p>
    <form class="wp-upload-form" action="" enctype="multipart/form-data" method="post">
        <label class="screen-reader-text" for="addonzip">' . esc_html__( 'Add-on archive', 'wp-recall' ) . '</label>
        <input id="addonzip" type="file" name="addonzip">
        <input id="install-plugin-submit" class="button" type="submit" value="' . esc_attr__( 'Install', 'wp-recall' ) . '" name="install-addon-submit">
        ' . wp_nonce_field( 'install-addons-rcl', '_wpnonce', true, false ) . '
    </form>

    </div>

    <ul class="subsubsub">
        <li class="all"><b>' . esc_html__( 'All', 'wp-recall' ) . '<span class="count">(' . esc_html( $cnt_all ) . ')</span></b>|</li>
        <li class="active"><b>' . esc_html__( 'Active', 'wp-recall' ) . '<span class="count">(' . esc_html( $cnt_all ) . ')</span></b>|</li>
        <li class="inactive"><b>' . esc_html__( 'Inactive', 'wp-recall' ) . '<span class="count">(' . esc_html( $cnt_all ) . ')</span></b></li>
    </ul>';

$Rcl_Addons_Manager->prepare_items();
?>
    <form method="get" class="rcl-repository-list">
        <input type="hidden" name="page" value="manage-addon-recall">
		<?php echo wp_kses( $Rcl_Addons_Manager->search_box( esc_html__( 'Search by name', 'wp-recall' ), 'search_id' ), rcl_kses_allowed_html() ); ?>
    </form>

    <form method="post" class="rcl-repository-list">
        <input type="hidden" name="page" value="manage-addon-recall">
<?php
//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
//$Rcl_Addons_Manager->search_box( __( 'Search by name', 'wp-recall' ), 'search_id' );
$Rcl_Addons_Manager->display();
echo '</form></div>';
