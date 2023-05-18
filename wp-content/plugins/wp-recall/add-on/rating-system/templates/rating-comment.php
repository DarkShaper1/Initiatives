<?php
global $rating;

?>
<div class="rating-single">
    <div class="object-rating">
        <i class="rcli fa-star"></i>
        <span class="rtng-ttl"><?php echo esc_html( $rating->rating_total ); ?></span>
        <span class="rtng-time"><?php if ( $rating->days_value_sum ) {
				echo '(' . esc_html( $rating->days_value_sum ) . ')';
			} ?></span>
    </div>
    <div class="rating-sidebar">
        <a title="<?php echo esc_attr( get_the_author_meta( 'display_name', $rating->object_author ) ); ?>"
           href="<?php echo esc_url( rcl_get_user_url( $rating->object_author ) ); ?>">
			<?php echo get_avatar( $rating->object_author, 60 ); ?>
        </a>
    </div>
    <div class="rating-meta">
        <p>
			<?php echo esc_html( wp_strip_all_tags( get_comment_text( $rating->object_id ) ) ); ?>
            <span class="comm-more"><a href="<?php echo esc_url( get_comment_link( $rating->object_id ) ); ?>"
                                       title="<?php esc_attr_e( 'Go to comment', 'wp-recall' ) ?>"><i
                            class="rcli fa-angle-double-right"></i></a></span>
        </p>
    </div>
</div>