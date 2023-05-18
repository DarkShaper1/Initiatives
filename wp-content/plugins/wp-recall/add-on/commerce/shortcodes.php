<?php

add_shortcode( 'minibasket', 'rcl_get_mini_cart' );
function rcl_get_mini_cart() {

	return rcl_get_include_template( 'cart-mini.php', __FILE__ );
}

add_shortcode( 'basket', 'rcl_get_cart' );
function rcl_get_cart( $cartProducts = false ) {

	$Cart = new Rcl_Cart_Constructor();

	return $Cart->get_cart( $cartProducts );
}

add_shortcode( 'productlist', 'rcl_shortcode_productlist' );
function rcl_shortcode_productlist( $atts ) {
	global $post, $productlist, $user_ID;
	
	extract( shortcode_atts( array(
		'num'         => false,
		'inpage'      => 10,
		'type'        => 'list',
		'width'       => 150,
		'cat'         => false,
		'cat__not_in' => false,
		'desc'        => 200,
		'tag'         => false,
		'include'     => false,
		'exclude'     => false,
		'orderby'     => 'post_date',
		'order'       => 'DESC',
		'author'      => false,
		'switch'      => 1
	), $atts ) );

	$productlist = $atts;

	$args = array(
		'numberposts' => - 1,
		'author'      => $author,
		'post_type'   => 'products',
		'include'     => $include,
		'fields'      => 'ids'
	);

	if ( $exclude ) {
		$args['post__not_in'] = array_map( 'trim', explode( ',', $exclude ) );
	}

	if ( $cat ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'prodcat',
			'field'    => 'id',
			'terms'    => explode( ',', $cat )
		);
	}

	if ( $cat__not_in ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'prodcat',
			'field'    => 'id',
			'terms'    => explode( ',', $cat__not_in ),
			'operator' => 'NOT IN'
		);
	}

	if ( $tag ) {
		$args['tax_query'][] = array(
			'taxonomy' => 'product_tag',
			'field'    => 'id',
			'terms'    => explode( ',', $tag )
		);
	}

	if ( ! $num ) {
		$count_prod = count( get_posts( $args ) );
	} else {
		$count_prod = false;
		$inpage     = $num;
	}

	$rclnavi = new Rcl_PageNavi( 'rcl-products', $count_prod, array( 'in_page' => intval( $inpage ) ) );

	$args['numberposts'] = $inpage;
	$args['fields']      = '';

	$more_args = array(
		'numberposts' => $inpage,
		'offset'      => $rclnavi->offset,
		'orderby'     => $orderby,
		'order'       => $order
	);

	$args = array_merge( $more_args, $args );

	$rcl_cache = new Rcl_Cache();

	if ( ! $user_ID && $rcl_cache->is_cache ) {

		$file = $rcl_cache->get_file( json_encode( $args ) );

		if ( ! $file->need_update ) {
			return $rcl_cache->get_cache();
		}
	}

	$products = get_posts( $args );

	if ( ! $products ) {
		return false;
	}

	$type_list = ( $switch ) ? $type : $type . ' cancel-switch';

	$prodlist = '<div class="products-box type-' . esc_attr( $type_list ) . '">
                    <div class="products-list">';

	foreach ( $products as $post ) {
		setup_postdata( $post );
		$prodlist .= rcl_get_include_template( 'product-' . sanitize_key( $type ) . '.php', __FILE__, $atts );
	}

	wp_reset_query();

	$prodlist .= '</div>'
	             . '</div>';

	if ( ! $num ) {
		$prodlist .= $rclnavi->pagenavi();
	}

	if ( ! $user_ID && $rcl_cache->is_cache ) {
		$rcl_cache->update_cache( $prodlist );
	}

	return $prodlist;
}
