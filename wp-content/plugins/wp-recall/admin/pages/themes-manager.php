<?php
rcl_dialog_scripts();

global $active_addons, $Rcl_Templates_Manager;

$Rcl_Templates_Manager->get_templates_data();

$cnt_all = $Rcl_Templates_Manager->template_number;

echo '<div class="wrap">';

echo '<div id="icon-plugins" class="icon32"><br></div>
    <h2>' . esc_html__( 'Templates', 'wp-recall' ) . ' WP-Recall</h2>';

if ( isset( $_POST['save-rcl-key'], $_POST['rcl-key'], $_POST['_wpnonce'] ) ) {
	if ( wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add-rcl-key' ) ) {
		update_site_option( 'rcl-key', sanitize_text_field( wp_unslash( $_POST['rcl-key'] ) ) );
		echo '<div id="message"><p>' . esc_html__( 'Key has been saved', 'wp-recall' ) . '!</p></div>';
	}
}
//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<div class="rcl-admin-service-box rcl-key-box">';

echo '<h4>' . esc_html__( 'RCLKEY', 'wp-recall' ) . '</h4>
    <form action="" method="post">
        ' . esc_html__( 'Enter RCLKEY', 'wp-recall' ) . ' <input type="text" name="rcl-key" value="' . esc_attr( get_site_option( 'rcl-key' ) ) . '">
        <input class="button" type="submit" value="' . esc_attr__( 'Save', 'wp-recall' ) . '" name="save-rcl-key">
        ' . wp_nonce_field( 'add-rcl-key', '_wpnonce', true, false ) . '
    </form>
    <p class="install-help">' . esc_html__( 'Required to update the templates here. Get it  in  your account online', 'wp-recall' ) . ' <a href="https://codeseller.ru/" target="_blank">https://codeseller.ru</a></p>';

echo '</div>';

echo '<div class="rcl-admin-service-box rcl-upload-form-box upload-template">';

echo '<h4>' . esc_html__( 'Install the add-on to WP-Recall format .ZIP', 'wp-recall' ) . '</h4>
    <p class="install-help">' . esc_html__( 'If you have an archive template for wp-recall format .zip, here you can upload and install it', 'wp-recall' ) . '</p>
    <form class="wp-upload-form" action="" enctype="multipart/form-data" method="post">
        <label class="screen-reader-text" for="addonzip">' . esc_html__( 'Add-on archive', 'wp-recall' ) . '</label>
        <input id="addonzip" type="file" name="addonzip">
        <input id="install-plugin-submit" class="button" type="submit" value="' . esc_attr__( 'Install', 'wp-recall' ) . '" name="install-template-submit">
        ' . wp_nonce_field( 'install-template-rcl', '_wpnonce', true, false ) . '
    </form>

    </div>

    <ul class="subsubsub">
        <li class="all"><b>' . esc_html__( 'All', 'wp-recall' ) . '<span class="count">(' . esc_html( $cnt_all ) . ')</span></b></li>
    </ul>';
//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
$Rcl_Templates_Manager->prepare_items();
?>

    <form method="post">
        <input type="hidden" name="page" value="manage-addon-recall">
<?php
$Rcl_Templates_Manager->search_box( 'Search by name', 'search_id' );
$Rcl_Templates_Manager->display();
echo '</form></div>';
