<?php global $post; ?>
<div class="prime-forum-content">

	<?php if ( pfm_have_posts() ): ?>

		<?php pfm_the_topic_manager(); ?>

        <div class="prime-topics-header">
            <span class="topic_count"><?php pfm_the_post_count(); ?><?php esc_html_e( 'reply(ies) in the topic', 'wp-recall' ); ?></span>
			<?php pfm_page_navi(); ?>
        </div>

		<?php do_action( 'pfm_topic_loop_before' ); ?>

        <div class="prime-posts">
			<?php
			global $PrimeQuery;
			if ( has_post_thumbnail( $post->ID ) ):
				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			else:
				$thumb = array( '/wp-admin/images/wordpress-logo.svg', 64, 64 );
			endif;
			?>
            <div itemid="<?php pfm_the_topic_permalink(); ?>" itemscope
                 itemtype="https://schema.org/DiscussionForumPosting">
                <meta itemprop="headline" content="<?php pfm_the_topic_name(); ?>"/>
                <span itemprop="author" itemscope itemtype="https://schema.org/Person">
					<meta itemprop="name"
                          content="<?php echo esc_attr( pfm_get_user_name( pfm_topic_field( 'user_id' ) ) ); ?>"/>
				</span>

                <meta itemprop="description"
                      content="<?php echo esc_attr( wp_trim_words( wp_strip_all_tags( $PrimeQuery->posts[0]->post_content ), 50 ) ); ?>"/>
                <meta itemprop="datePublished"
                      content="<?php echo esc_attr( mysql2date( 'c', $PrimeQuery->posts[0]->post_date, false ) ); ?>"/>
                <div style="display: none;" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
                    <img alt="" itemprop="url contentUrl" src="<?php echo esc_url( $thumb[0] ); ?>"/>
                    <meta itemprop="width" content="<?php echo esc_attr( $thumb[1] ); ?>"/>
                    <meta itemprop="height" content="<?php echo esc_attr( $thumb[2] ); ?>"/>
                </div>
                <div style="display: none;" itemprop="interactionStatistic" itemscope
                     itemtype="https://schema.org/InteractionCounter">
                    <link itemprop="interactionType" href="https://schema.org/CommentAction"/>
                    <meta itemprop="userInteractionCount" content="<?php pfm_the_post_count(); ?>"/>
                </div>

				<?php while ( pfm_get_next( 'post' ) ) : ?>

					<?php pfm_the_template( 'pfm-single-post' ); ?>

				<?php endwhile; ?>

            </div>

        </div>

		<?php pfm_page_navi(); ?>

		<?php pfm_the_topic_manager(); ?>

	<?php else: ?>

		<?php pfm_the_notices(); ?>

	<?php endif; ?>

	<?php pfm_the_post_form(); ?>

</div>
