<?php global $typeform; ?>
<div class="form-tab-rcl" id="remember-form-rcl">
    <div class="form_head form_rmmbr">
        <a href="#" class="link-login-rcl link-tab-rcl "><?php esc_html_e( 'Authorization', 'wp-recall' ); ?></a>
		<?php if ( $typeform != 'sign' && rcl_is_register_open() ) { ?>
            <a href="#" class="link-register-rcl link-tab-rcl "><?php esc_html_e( 'Registration', 'wp-recall' ); ?></a>
		<?php } ?>
    </div>
    <span class="form-title"><?php esc_html_e( 'Password generation', 'wp-recall' ); ?></span>

    <div class="form-block-rcl"><?php rcl_notice_form( 'remember' ); ?></div>

	<?php if ( ! isset( $_GET['success'] ) ) { ?>
        <form action="<?php echo esc_url( wp_lostpassword_url() ); ?>" method="post">
            <div class="form-block-rcl default-field">
                <input required type="text" placeholder="<?php esc_html_e( 'Username or e-mail', 'wp-recall' ); ?>"
                       name="user_login">
                <i class="rcli fa-key"></i>
            </div>
            <div class="form-block-rcl">
				<?php do_action( 'lostpassword_form' ); ?>
            </div>
            <div class="form-block-rcl">
				<?php
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo rcl_get_button( array(
					'label'     => __( 'Get New Password', 'wp-recall' ),
					'submit'    => true,
					'fullwidth' => true,
					'size'      => 'medium',
					'icon'      => 'fa-envelope',
					'class'     => 'link-tab-form'
				) );
				?>
				<?php
				//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo wp_nonce_field( 'remember-key-rcl', '_wpnonce', true, false );
				?>
                <input type="hidden" name="redirect_to" value="<?php rcl_referer_url( 'remember' ); ?>">
            </div>
        </form>
	<?php } ?>
</div>

