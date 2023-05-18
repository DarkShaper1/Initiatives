<?php
global $typeform;
$f_reg = ( $typeform == 'register' ) ? 'display:block;' : '';
?>

<div class="form-tab-rcl" id="register-form-rcl" style="<?php echo esc_attr( $f_reg ); ?>">
    <div class="form_head">
        <div class="form_auth">
			<?php if ( ! $typeform ) { ?>
                <a href="#" class="link-login-rcl link-tab-rcl">
					<?php esc_html_e( 'Authorization ', 'wp-recall' ); ?>
                </a>
			<?php } ?>
        </div>
        <div class="form_reg form_active"><?php esc_html_e( 'Registration', 'wp-recall' ); ?></div>
    </div>

    <div class="form-block-rcl"><?php rcl_notice_form( 'register' ); ?></div>

	<?php $user_login = ( isset( $_REQUEST['user_login'] ) ) ? sanitize_user( wp_unslash( $_REQUEST['user_login'] ) ) : ''; ?>
	<?php $user_email = ( isset( $_REQUEST['user_email'] ) ) ? sanitize_email( wp_unslash( $_REQUEST['user_email'] ) ) : ''; ?>

    <form action="<?php rcl_form_action( 'register' ); ?>" method="post" enctype="multipart/form-data">
        <div class="form-block-rcl default-field">
            <input required type="text" placeholder="<?php esc_html_e( 'Login', 'wp-recall' ); ?>"
                   value="<?php echo esc_attr( $user_login ); ?>" name="user_login" id="login-user">
            <i class="rcli fa-user"></i>
            <span class="required">*</span>
        </div>
        <div class="form-block-rcl default-field">
            <input required type="email" placeholder="<?php esc_html_e( 'E-mail', 'wp-recall' ); ?>"
                   value="<?php echo esc_attr( $user_email ); ?>" name="user_email" id="email-user">
            <i class="rcli fa-at"></i>
            <span class="required">*</span>
        </div>
        <div class="form-block-rcl form_extend">
			<?php do_action( 'register_form' ); ?>
        </div>
        <div class="form-block-rcl">
			<?php
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo rcl_get_button( array(
				'label'     => __( 'Signup', 'wp-recall' ),
				'submit'    => true,
				'icon'      => 'fa-book',
				'size'      => 'medium',
				'fullwidth' => true
			) );
			?>

			<?php
			//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo wp_nonce_field( 'register-key-rcl', 'register_wpnonce', true, false );
			?>
            <input type="hidden" name="redirect_to" value="<?php rcl_referer_url( 'register' ); ?>">
        </div>
    </form>
</div>