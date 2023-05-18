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
    <span class="object-title">
		<a title="<?php echo esc_attr( get_the_title( $rating->object_id ) ); ?>"
           href="<?php echo esc_url( get_permalink( $rating->object_id ) ); ?>">
			<?php echo esc_html( get_the_title( $rating->object_id ) ); ?>
		</a>
	</span>
</div>