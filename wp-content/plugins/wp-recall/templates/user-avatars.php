<?php global $rcl_user, $rcl_users_set; ?>
<div class="user-single" data-user-id="<?php echo (int) $rcl_user->ID; ?>">
    <div class="thumb-user">
        <a title="<?php rcl_user_name(); ?>" href="<?php rcl_user_url(); ?>">
			<?php rcl_user_avatar( 70 ); ?>
			<?php rcl_user_action(); ?>
        </a>
		<?php rcl_user_rayting(); ?>
    </div>
</div>