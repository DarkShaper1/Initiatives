<?php

if ( ! is_admin() ):
	add_action( 'rcl_enqueue_scripts', 'ftf_scripts', 10 );
endif;
function ftf_scripts() {
	rcl_enqueue_style( 'ftf-style', rcl_addon_url( 'style.css', __FILE__ ) );
}

add_filter( 'rcl_inline_styles', 'pfm_color_from_elements', 10, 2 );
function pfm_color_from_elements( $styles, $rgb ) {

	if ( ! pfm_get_option( 'forum-colors' ) ) {
		return $styles;
	}

	if ( ! is_prime_forum() ) {
		return $styles;
	} // не нужны за пределами форума

	list( $r, $g, $b ) = $rgb;
	$color = $r . ',' . $g . ',' . $b;

	$styles .= '
    #prime-topic-form-box,
    .prime-forum-header,
    .prime-forum-footer{
        background-color: rgba(' . $color . ',0.02);
        border-color: rgba(' . $color . ',0.12);
    }
    #prime-forum .prime-forum-icon {
        color: rgba(' . $color . ',0.15);
    }
    #prime-forum .prime-post,
    #prime-forum .prime-forum-item {
        background-color: rgba(' . $color . ',0.01);
        border-color: rgba(' . $color . ',0.18);
    }
    #prime-forum .prime-group-box .prime-group {
        background-color: rgba(' . $color . ',0.1);
    }
    #prime-forum .prime-group-box .prime-child-forums .prime-forum {
        background-color: rgba(' . $color . ',0.02);
    }
    #prime-forum .prime-forum-topics {
        border-left-color: rgba(' . $color . ',0.15);
        border-right-color: rgba(' . $color . ',0.15);
    }
    #prime-forum .prime-parent-box .prime-item-label span{
        background: rgba(' . $color . ',0.1);
        color: rgba(' . $color . ',0.9);
    }
    #prime-forum .prime-parent-box .prime-item-label span::after{
        border-color: transparent transparent rgba(' . $color . ',0.1) rgba(' . $color . ',0.1);
    }
    #prime-forum .prime-parent-box,
    #prime-forum .prime-parent-box .prime-item-label,
    #prime-forum .prime-parent-box .prime-item-label span::before{
        border-color: rgba(' . $color . ',0.1);
    }
    ';

	return $styles;
}

add_action( 'pfm_footer', 'pfm_add_the_visitors', 10 );
function pfm_add_the_visitors() {
	pfm_the_visitors();
}
