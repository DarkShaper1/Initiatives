<?php
/**
 * @var string $thumbnail post
 * @var string $excerpt post
 */
global $post; ?>
<div class="post-group">
    <div class="postdata-header">
        <div class="post-meta">
            <span class="post-date">
                <i class="rcli fa-clock-o"></i><?php echo get_the_date(); ?>
            </span>
            <span class="post-comments-number">
                <i class="rcli fa-comments-o"></i><?php comments_number( '0', '1', '%' ); ?>
            </span>
        </div>
        <h3>
            <a href="<?php the_permalink(); ?>"><?php echo wp_kses_post( $post->post_title ); ?></a>
        </h3>
    </div>

	<?php if ( $thumbnail && has_post_thumbnail() ) { ?>
        <div class="post-group-thumb"><?php the_post_thumbnail( 'thumbnail' ); ?></div>
	<?php } ?>

	<?php if ( $excerpt ) { ?>
        <div class="post-group-content">
			<?php the_excerpt(); ?>
        </div>
	<?php } ?>

</div>
