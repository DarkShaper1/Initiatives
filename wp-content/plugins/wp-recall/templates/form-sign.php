<?php
global $typeform;

$f_sign = ( ! $typeform || $typeform == 'sign' ) ? 'display:block;' : '';

?>

<div class="form-tab-rcl" id="login-form-rcl" style="<?php echo esc_attr( $f_sign ); ?>">
    <div class="form_head">
        <div class="form_auth form_active"><?php esc_html_e( 'Authorization', 'wp-recall' ); ?></div>
		<?php if ( rcl_is_register_open() ): ?>
            <div class="form_reg">
				<?php if ( ! $typeform ) { ?>
                    <a href="#" class="link-register-rcl link-tab-rcl ">
						<?php esc_html_e( 'Registration', 'wp-recall' ); ?>
                    </a>
				<?php } ?>
            </div>
		<?php endif; ?>
    </div>

    <div class="form-block-rcl"><?php rcl_notice_form( 'login' ); ?></div>

	<?php $user_login = ( isset( $_REQUEST['user_login'] ) ) ? sanitize_user( wp_unslash( $_REQUEST['user_login'] ) ) : ''; ?>

    <form action="<?php rcl_form_action( 'login' ); ?>" method="post">
        <div class="form-block-rcl default-field">
            <input required type="text" placeholder="<?php esc_html_e( 'Login', 'wp-recall' ); ?>"
                   value="<?php echo esc_attr( $user_login ); ?>" name="user_login">
            <i class="rcli fa-user"></i>
            <span class="required">*</span>
        </div>
        <div class="form-block-rcl default-field">
            <input required type="password" placeholder="<?php esc_html_e( 'Password', 'wp-recall' ); ?>"
                   value="" name="user_pass">
            <i class="rcli fa-lock"></i>
            <span class="required">*</span>
        </div>
        <div class="form-block-rcl">
			<?php do_action( 'login_form' ); ?>

            <div class="default-field rcl-field-input type-checkbox-input">
                <div class="rcl-checkbox-box">
                    <input type="checkbox" id="chck_remember" class="checkbox-custom" value="1" name="rememberme">
                    <label class="block-label"
                           for="chck_remember"><?php esc_html_e( 'Remember', 'wp-recall' ); ?></label>
                </div>
            </div>
        </div>
        <div class="form-block-rcl">
			<?php
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo rcl_get_button( array(
				'label'     => esc_html__( 'Entry', 'wp-recall' ),
				'submit'    => true,
				'fullwidth' => true,
				'size'      => 'medium',
				'icon'      => 'fa-sign-in',
				'class'     => 'link-tab-form'
			) );
			?>
            <a href="#"
               class="link-remember-rcl link-tab-rcl "><?php esc_html_e( 'Lost your Password', 'wp-recall' ); // Забыли пароль            ?>
                ?</a>
			<?php
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_nonce_field( 'login-key-rcl', 'login_wpnonce', true, false );
			?>
            <input type="hidden" name="redirect_to" value="<?php rcl_referer_url( 'login' ); ?>">
        </div>
    </form>
</div>
