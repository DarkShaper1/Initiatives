<?php global $addon, $rcl_addons, $wprecall; ?>
<div class="addon-box plugin-card plugin-card-<?php echo esc_attr( $addon->slug ); ?>">
    <div class="plugin-card-top">
        <div class="name column-name">
            <h3>
                <a href="<?php echo esc_url( $addon->add_on_uri ); ?>" class="thickbox">
					<?php echo esc_html( $addon->name ); ?>
                    <img src="<?php echo esc_url( $addon->thumbnail ); ?>" class="plugin-icon" alt="">
                </a>
            </h3>
        </div>
        <div class="action-links">
            <ul class="plugin-action-buttons">
				<?php if ( isset( $rcl_addons[ $addon->slug ] ) && version_compare( $addon->version, $rcl_addons[ $addon->slug ]['version'] ) > 0 ): ?>
                    <li><a class="update-now button aria-button-if-js"
                           data-addon="<?php echo esc_attr( $addon->slug ); ?>" href="#"
                           aria-label="Обновить сейчас" role="button"
                           onclick='rcl_update_addon(<?php echo json_encode( array( 'slug' => $addon->slug ) ); ?>, this );return false;'>Обновить</a>
                    </li>
				<?php elseif ( isset( $rcl_addons[ $addon->slug ] ) ): ?>
                    <li><span class="button button-disabled"
                              title="<?php esc_html_e( 'This add-on has already been installed', 'wp-recall' ) ?>"><?php esc_html_e( 'Installed', 'wp-recall' ) ?></span>
                    </li>
				<?php else: ?>
                    <li><a class="button" target="_blank" data-slug="<?php echo esc_attr( $addon->slug ); ?>"
                           href="<?php echo esc_url( $addon->add_on_uri ); ?>"
                           aria-label="<?php esc_html_e( 'Go to page', 'wp-recall' ) ?> <?php echo esc_attr( $addon->name ); ?> <?php echo esc_attr( $addon->version ); ?>"
                           data-name="<?php echo esc_attr( $addon->name ); ?> <?php echo esc_attr( $addon->version ); ?>"><?php esc_html_e( 'Go to', 'wp-recall' ) ?></a>
                    </li>
				<?php endif; ?>
                <li><a href="#" class="open-addon-details-modal"
                       onclick='rcl_get_details_addon(<?php echo json_encode( array( 'slug' => $addon->slug ) ); ?>, this );return false;'
                       aria-label="Подробности о <?php echo esc_attr( $addon->name ); ?>"
                       data-title="<?php echo esc_attr( $addon->name ); ?>">Детали</a>
                </li>
        </div>
        <div class="desc column-description">
            <p><?php print_r( $addon->description ); ?></p>
            <p class="authors"><cite><?php esc_html_e( 'Author', 'wp-recall' ) ?>: <a
                            href="<?php echo esc_url( $addon->author_uri ); ?>"
                            target="_blank"><?php echo esc_attr( $addon->author ); ?></a></cite>
            </p>
        </div>
        <div class="addon-terms">
			<?php
			if ( isset( $addon->terms ) && $addon->terms ):

				foreach ( $addon->terms as $taxonomy => $terms ) {
					$html = array();
					?>

                    <p>
                        <cite><?php echo( ( $taxonomy == 'prodcat' ) ? esc_html__( 'Category', 'wp-recall' ) : esc_html__( 'Tags', 'wp-recall' ) ) ?>
                            :

							<?php
							foreach ( $terms as $slug => $name ) {

								$html[] = '<a href="' . esc_url( admin_url( 'admin.php?page=rcl-repository&type=tag&s=' . $name ) ) . '">' . esc_html( $name ) . '</a>';
							}
							?>

							<?php
							echo wp_kses( implode( ', ', $html ), rcl_kses_allowed_html() );
							?>

                        </cite></p>

					<?php
				}

			endif;
			?>
        </div>
    </div>
    <div class="plugin-card-bottom">
        <div class="vers column-rating">
			<?php
			wp_star_rating( array(
				'rating' => ( $addon->rating->value <= 5 ) ? $addon->rating->value : '5',
				'type'   => 'rating',
				'number' => $addon->rating->votes
			) );
			?>
            <span class="num-ratings">(<?php echo esc_html( $addon->rating->votes ); ?>)</span>
        </div>
        <div class="column-updated">
            <strong><?php esc_html_e( 'Updated', 'wp-recall' ) ?>:</strong> <span
                    title="<?php echo esc_attr( $addon->update ); ?>">
				<?php echo esc_html( human_time_diff( strtotime( $addon->update ), time() ) ) . ' ' . esc_html__( 'ago', 'wp-recall' ); ?>
            </span>
        </div>
        <div class="column-downloaded"><?php echo esc_html( $addon->downloads ); ?><?php esc_html_e( 'downloads', 'wp-recall' ) ?></div>
        <div class="column-compatibility">
			<?php if ( isset( $addon->support_core ) ) { ?>
                <span class="compatibility-compatible"><strong><?php esc_html_e( 'Compatible', 'wp-recall' ) ?></strong> с WP-Recall <?php echo esc_html( $addon->support_core ); ?> и выше</span>
				<?php if ( version_compare( $addon->support_core, $wprecall->version ) > 0 ) { ?>
                    <span class="compatibility-untested"><?php esc_html_e( 'Operation is not guaranteed with your version of WP-Recall', 'wp-recall' ) ?></span>
				<?php } ?>
			<?php } ?>
        </div>
    </div>
</div>