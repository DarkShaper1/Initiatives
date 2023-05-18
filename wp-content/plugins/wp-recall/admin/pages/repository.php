<?php

global $addon, $rcl_addons;

$rcl_addons = rcl_get_addons();

rcl_dialog_scripts();

wp_enqueue_style( 'rcl-admin-style', RCL_URL . 'admin/assets/style.css', false, VER_RCL );

$addonsData = array();
foreach ( $rcl_addons as $addonID => $addon ) {
	$addonsData[ $addonID ] = $addon['version'];
}

$sort = isset( $_GET['sort'] ) ? sanitize_key( $_GET['sort'] ) : 'update';

$type = isset( $_GET['type'] ) ? sanitize_key( $_GET['type'] ) : 'term';

$s = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$page = ( isset( $_GET['paged'] ) ) ? intval( $_GET['paged'] ) : 1;

$url = RCL_SERVICE_HOST . '/products-files/api/add-ons.php'
       . '?rcl-addon-info=get-add-ons&page=' . $page;

if ( $sort ) {
	$url .= '&sort=' . $sort;
}

if ( $type ) {
	$url .= '&type=' . $type;
}

if ( $s ) {
	$url .= '&s=' . $s;
}

$result = wp_remote_post( $url, array(
	'body' => array(
		'rcl-key'     => get_site_option( 'rcl-key' ),
		'rcl-version' => VER_RCL,
		'addons-data' => in_array( $sort, array( 'favorites', 'has_updated' ) ) ? $addonsData : false,
		'host'        => ( isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '' )
	)
) );

if ( is_wp_error( $result ) ) {
	$error_message = $result->get_error_message();
	echo esc_html__( 'Error' ) . ': ' . esc_html( $error_message );
	exit;
}

$result = json_decode( $result['body'] );

if ( ! $result ) {
	echo '<h2>' . esc_html__( 'Failed to get data', 'wp-recall' ) . '.</h2>';
	exit;
}

if ( is_array( $result ) && isset( $result['error'] ) ) {
	echo '<h2>' . esc_html__( 'Error', 'wp-recall' ) . '! ' . esc_html( $result['error'] ) . '</h2>';
	exit;
}

$navi = new Rcl_PageNavi( 'rcl-addons', intval( $result->count ), array(
	'key'     => 'paged',
	'in_page' => intval( $result->number )
) );

$content = '<h2>' . esc_html__( 'Repository for WP-Recall add-ons', 'wp-recall' ) . '</h2>';

if ( isset( $_POST['save-rcl-key'], $_POST['_wpnonce'], $_POST['rcl-key'] ) ) {
	if ( wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add-rcl-key' ) ) {
		update_site_option( 'rcl-key', sanitize_text_field( wp_unslash( $_POST['rcl-key'] ) ) );
		$content .= '<div id="message" class="updated"><p>' . esc_html__( 'Key has been saved', 'wp-recall' ) . '!</p></div>';
	}
}

$content .= '<div class="rcl-admin-service-box rcl-key-box">';

$content .= '<h4>' . esc_html__( 'RCLKEY', 'wp-recall' ) . '</h4>
<form action="" method="post">
    ' . esc_html__( 'Enter RCLKEY', 'wp-recall' ) . ' <input type="text" name="rcl-key" value="' . esc_attr( get_site_option( 'rcl-key' ) ) . '">
    <input class="button" type="submit" value="' . esc_attr__( 'Save', 'wp-recall' ) . '" name="save-rcl-key">
    ' . wp_nonce_field( 'add-rcl-key', '_wpnonce', true, false ) . '
</form>
<p class="install-help">' . esc_html__( 'The key is required to update the add-ons here. You can get it in your personal account of website', 'wp-recall' ) . ' <a href="https://codeseller.ru/" target="_blank">https://codeseller.ru</a></p>';

$content .= '</div>';

$content .= '<div class="wp-filter">
    <ul class="filter-links">
        <li class="plugin-install-featured"><a href="' . admin_url( 'admin.php?' ) . $navi->get_string( array(
		'type',
		's',
		'page'
	) ) . '&sort=update" class="' . ( $sort == 'update' ? 'current' : '' ) . '">' . esc_html__( 'All', 'wp-recall' ) . '</a></li>
        <li class="plugin-install-popular"><a href="' . admin_url( 'admin.php?' ) . $navi->get_string( array(
		'type',
		's',
		'page'
	) ) . '&sort=popular" class="' . ( $sort == 'popular' ? 'current' : '' ) . '">' . esc_html__( 'Popular', 'wp-recall' ) . '</a></li>
        <li class="plugin-install-favorites"><a href="' . admin_url( 'admin.php?' ) . $navi->get_string( array(
		'type',
		's',
		'page'
	) ) . '&sort=favorites" class="' . ( $sort == 'favorites' ? 'current' : '' ) . '">' . esc_html__( 'Reference', 'wp-recall' ) . '</a></li>
        <li class="plugin-install-has-updated"><a href="' . admin_url( 'admin.php?' ) . $navi->get_string( array(
		'type',
		's',
		'page'
	) ) . '&sort=has_updated" class="' . ( $sort == 'has_updated' ? 'current' : '' ) . '">' . esc_html__( 'Updates', 'wp-recall' ) . '</a></li>
    </ul>

    <form class="search-form search-plugins" method="get">
        <input type="hidden" name="page" value="rcl-repository">
        <input type="hidden" name="sort" value="' . $sort . '">
        <label class="screen-reader-text" for="typeselector">' . esc_html__( 'Search category', 'wp-recall' ) . ':</label>
        <select name="type" id="typeselector">
            <option value="term" ' . selected( $type, 'term', false ) . '>' . esc_html__( 'Word', 'wp-recall' ) . '</option>
            <option value="author" ' . selected( $type, 'author', false ) . '>' . esc_html__( 'Author', 'wp-recall' ) . '</option>
            <option value="tag" ' . selected( $type, 'tag', false ) . '>' . esc_html__( 'Tag', 'wp-recall' ) . '</option>
        </select>
        <label><span class="screen-reader-text">' . esc_html__( 'Search add-ons', 'wp-recall' ) . '</span>
            <input type="search" name="s" value="' . ( $s ? $s : '' ) . '" class="wp-filter-search" placeholder="' . esc_attr__( 'Search add-ons', 'wp-recall' ) . '..." aria-describedby="live-search-desc">
        </label>
        <input type="submit" id="search-submit" class="button hide-if-js" value="' . esc_attr__( 'Search add-ons', 'wp-recall' ) . '">
    </form>
</div>';

if ( $result->count && $result->addons ) {

	$content .= '<p class="rcl-search-results">' . esc_html__( 'Results found', 'wp-recall' ) . ': ' . intval( $result->count ) . '</p>';

	$content .= $navi->pagenavi();

	$content .= '<div class="wp-list-table widefat plugin-install rcl-repository-list">
        <div id="the-list">';
	foreach ( $result->addons as $add ) {
		if ( ! $add ) {
			continue;
		}
		$addon = array();
		foreach ( $add as $k => $v ) {
			$key           = str_replace( '-', '_', $k );
			$v             = ( isset( $v ) ) ? $v : '';
			$addon[ $key ] = $v;
		}
		$addon   = ( object ) $addon;
		$content .= rcl_get_include_template( 'add-on-card.php' );
	}
	$content .= '</div>'
	            . '</div>';

	$content .= $navi->pagenavi();
} else {
	$content .= '<h3>' . esc_html__( 'Nothing found', 'wp-recall' ) . '</h3>';
}

echo wp_kses( $content, rcl_kses_allowed_html() );;
