<?php

global $wpdb;

$postmeta = $wpdb->get_results( "SELECT meta_key FROM " . $wpdb->prefix . "postmeta GROUP BY meta_key ORDER BY meta_key" );

$fields = array(
	'price-products' => esc_html__( 'The price of the product in the main currency', 'wp-recall' ),
	'outsale'        => '1 - ' . esc_html__( 'the item is no longer available', 'wp-recall' )
);

$fields = apply_filters( 'products_field_list', $fields );

$content = '<style>';
$content .= '#migration-box{'
            . ''
            . '}'
            . '#migration-step{'
            . 'font-weight: bold;'
            . '}'
            . '#migration-progress-box{'
            . 'border: 1px solid #ccc;
               margin: 20px 10px 20px 0;'
            . '}'
            . '#migration-progress{'
            . 'height: 30px;
               background: #ffaf36;
               width: 0;'
            . '}'
            . '#migration-log{'
            . 'margin: 20px 0;
                background: #fff;
                padding: 10px;
                font-size: 12px;
                max-height: 200px;
                overflow: auto;
                box-shadow: 2px -2px 6px 0px #ccc inset;
                border: 1px solid #ccc;'
            . '}'
            . '#migration-log span{'
            . '}'
            . '#migration-log .error{'
            . 'color:red;'
            . '}'
            . '#migration-manager{'
            . ''
            . '}';
$content .= '</style>';

$content .= '<style>table{min-width:500px;width:50%;margin:20px 0;}table td{border:1px solid #ccc;padding:3px;}</style>';

$content .= '<h2>' . esc_html__( 'Export/import data', 'wp-recall' ) . '</h2><form method="post" action="">
' . wp_nonce_field( 'get-csv-file', '_wpnonce', true, false ) . '
<p><input type="checkbox" name="product[fields][]" checked value="post_title"> ' . esc_html__( 'Add a title', 'wp-recall' ) . '</p>
<p><input type="checkbox" name="product[fields][]" checked value="post_content"> ' . esc_html__( 'Add a description', 'wp-recall' ) . '</p>
<p><input type="checkbox" name="product[fields][]" value="post_excerpt"> ' . esc_html__( 'Add a short description', 'wp-recall' ) . '</p>
<h3>' . esc_html__( 'Optional fields', 'wp-recall' ) . ':</h3><table><tr>';

foreach ( $fields as $key => $name ) {
	$content .= '<b>' . $key . '</b> - ' . $name . '<br />';
}

if ( $fields ) {
	$n = 1;
	foreach ( $fields as $key => $desc ) {
		$n ++;
		$content .= '<td><input type="checkbox" name="product[meta][]" value="' . $key . '"> ' . $key . '</td>';
		if ( $n % 2 ) {
			$content .= '</tr><tr>';
		}
	}
}

$content .= '</tr><tr><td colspan="2" align="right">'
            . '<input type="submit" name="get_csv_file" value="' . esc_html__( 'Upload products to a file', 'wp-recall' ) . '"></td></tr></table>
' . wp_nonce_field( 'get-csv-file', '_wpnonce', true, false ) . '
</form>';

$content .= '<form method="post" action="" enctype="multipart/form-data">
' . wp_nonce_field( 'rcl-import-products-nonce', '_wpnonce', true, false ) . '
<p>
<input type="file" name="rcl-import-products" value="1">
<input type="submit" value="' . esc_html__( 'Import products from a file', 'wp-recall' ) . '"><br>
<small><span style="color:red;">' . esc_html__( 'Attention', 'wp-recall' ) . '!</span> ' . esc_html__( 'Empty values of arbitrary fields in the import file remove them from the database', 'wp-recall' ) . '</small>
</p>
</form>';

if ( isset( $_FILES['rcl-import-products'], $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'rcl-import-products-nonce' ) ) {

	$file_name = isset( $_FILES['rcl-import-products']['name'] ) ? sanitize_text_field( wp_unslash( $_FILES['rcl-import-products']['name'] ) ) : '';

	$rest = substr( $file_name, - 4 ); //получаем расширение файла

	if ( $rest == '.xml' ) {

		$filename = isset( $_FILES['rcl-import-products']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['rcl-import-products']['tmp_name'] ) ) : '';

		$filepath = wp_normalize_path( current( wp_upload_dir() ) . "/" . basename( $filename ) );

		copy( $filename, $filepath );

		$content .= '<script>rcl_init_import_products("' . $filepath . '");</script>';
		$content .= '<div id="migration-box">';
		$content .= '<div id="migration-step">Ожидание...</div>';
		$content .= '<div id="migration-progress-box">';
		$content .= '<div id="migration-progress"></div>';
		$content .= '</div>';
		$content .= '<div id="migration-log"></div>';
		$content .= '</div>';
	} else {
		echo '<div class="error">' . esc_html__( 'Incorrect extension of the downloaded file. XML format expected!', 'wp-recall' ) . '</div>';
	}
}

echo wp_kses( $content, rcl_kses_allowed_html() );
