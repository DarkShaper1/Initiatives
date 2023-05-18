<?php
global $rcl_user, $rcl_users_set;
// если есть вызов в data атрибута comments_count
$uc_count = '';
if ( in_array( 'comments_count', $rcl_users_set->data ) ) {
	$uc_count .= '<div class="u_card_half">' . esc_html__( 'Comments', 'wp-recall' ) . '<br/><span>';
	$uc_count .= isset( $rcl_user->comments_count ) ? (int) $rcl_user->comments_count : '0';
	$uc_count .= '</span></div>';
}
// если есть вызов в data атрибута posts_count
$up_count = '';
if ( in_array( 'posts_count', $rcl_users_set->data ) ) {
	$up_count .= '<div class="u_card_half">' . esc_html__( 'Posts', 'wp-recall' ) . '<br/><span>';
	$up_count .= isset( $rcl_user->posts_count ) ? (int) $rcl_user->posts_count : '0';
	$up_count .= '</span></div>';
}

$style = ( isset( $rcl_users_set->width ) ) ? 'width:' . $rcl_users_set->width . 'px' : '';
?>
<div class="user-single" style="<?php echo esc_attr( $style ); ?>" data-user-id="<?php echo (int) $rcl_user->ID; ?>">
    <div class="u_card_top">
		<?php rcl_user_rayting(); ?>
		<?php rcl_user_action( 2 ); ?>
        <div class="thumb-user">
            <a title="<?php esc_html_e( 'to go to office of the user', 'wp-recall' ); ?>"
               href="<?php rcl_user_url(); ?>">
				<?php rcl_user_avatar( 300 ); ?>
            </a>
        </div>
        <div class="u_card_name">
			<?php rcl_user_name(); ?>
        </div>
    </div>
    <div class="u_card_bottom">
		<?php
		echo wp_kses_post( $uc_count );
		echo wp_kses_post( $up_count );
		?>
    </div>
</div>