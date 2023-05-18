<?php defined('ABSPATH') or die("Cheating........Uh!!"); ?>
<div id="fb-root"></div>
	<div>
		<?php
		echo sprintf(__('You can appreciate the effort put in this free plugin by rating it <a href="%s" target="_blank">here</a>', 'super-socializer'), 'https://wordpress.org/support/view/plugin-reviews/super-socializer');
		?>
	</div>
	<div class="metabox-holder">
		<form action="options.php" method="post">
		<?php settings_fields('the_champ_login_options'); ?>

		<div class="stuffbox" style="width:98.7%">
			<h3><label><?php _e('Master Control', 'super-socializer');?></label></h3>
			<div class="inside" style="padding:5px;">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
					<tr>
						<th>
						<label for="the_champ_login_enable"><?php _e("Enable Social Login", 'super-socializer'); ?></label><img id="the_champ_sl_enable_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
						</th>
						<td>
						<input id="the_champ_login_enable" name="the_champ_login[enable]" type="checkbox" <?php echo isset($theChampLoginOptions['enable']) ? 'checked' : '';?> value="1" />
						</td>
					</tr>
					
					<tr class="the_champ_help_content" id="the_champ_sl_enable_help_cont">
						<td colspan="2">
						<div>
						<?php _e('Master control for Social Login. It must be checked to enable Social Login functionality', 'super-socializer') ?>
						</div>
						</td>
					</tr>
				</table>
			</div>
		</div>

		<div class="menu_div" id="tabs" <?php echo isset($theChampLoginOptions['enable']) ? '' : 'style="display:none"' ?>>
			<h2 class="nav-tab-wrapper" style="height:34px">
			<ul>
				<li style="margin-left:9px"><a style="margin:0; line-height:auto !important; height:23px" class="nav-tab" href="#tabs-1"><?php _e('Basic Configuration', 'super-socializer') ?></a></li>
				<li style="margin-left:9px"><a style="margin:0; line-height:auto !important; height:23px" class="nav-tab" href="#tabs-2"><?php _e('Advanced Configuration', 'super-socializer') ?></a></li>
				<li style="margin-left:9px"><a style="margin:0; line-height:auto !important; height:23px" class="nav-tab" href="#tabs-3"><?php _e('GDPR', 'super-socializer') ?></a></li>
				<?php if($theChampIsBpActive){ ?>
				<li style="margin-left:9px"><a style="margin:0; line-height:auto !important; height:23px" class="nav-tab" href="#tabs-4"><?php _e('XProfile Integration', 'super-socializer') ?></a></li>
				<?php } ?>
				<li style="margin-left:9px"><a style="margin:0; height:23px" class="nav-tab" href="#tabs-5"><?php _e('Shortcode & Widget', 'super-socializer') ?></a></li>
				<li style="margin-left:9px"><a style="margin:0; height:23px" class="nav-tab" href="#tabs-6"><?php _e('FAQ', 'super-socializer') ?></a></li>
			</ul>
			</h2>
			<div class="menu_containt_div" id="tabs-1">
				<div class="clear"></div>
				<div class="the_champ_left_column">
				<div class="stuffbox">
					<h3 class="hndle"><label><?php _e('Basic Configuration', 'super-socializer');?></label></h3>
					<div class="inside">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
						<tr>
							<td colspan="2"><a href="https://www.heateor.com/social-login-buttons" target="_blank" style="text-decoration:none"><input style="width: auto;padding: 10px 42px;" type="button" value="<?php _e('Customize Social Login Icons', 'super-socializer'); ?> >>>" class="ss_demo"></a></td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_sl_disable_reg"><?php _e("Disable user registration via Social Login", 'super-socializer'); ?></label><img id="the_champ_sl_disable_reg_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_disable_reg" name="the_champ_login[disable_reg]" onclick="if(this.checked){jQuery('#the_champ_disable_reg_options').css('display', 'table-row-group')}else{ jQuery('#the_champ_disable_reg_options').css('display', 'none') }" type="checkbox" <?php echo isset($theChampLoginOptions['disable_reg']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_disable_reg_help_cont">
							<td colspan="2">
							<div>
							<?php _e('After enabling this option, new users will not be able to login through social login. Only existing users will be able to social login.', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						
						<tbody id="the_champ_disable_reg_options" <?php echo !isset($theChampLoginOptions['disable_reg']) ? 'style = "display: none"' : '';?> >
						<tr>
							<th>
							<label for="the_champ_sl_disable_reg_redirect"><?php _e("Redirection url", 'super-socializer'); ?></label><img id="the_champ_sl_disable_reg_redirect_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_disable_reg_redirect" name="the_champ_login[disable_reg_redirect]" type="text" value="<?php echo isset($theChampLoginOptions['disable_reg_redirect']) ? $theChampLoginOptions['disable_reg_redirect'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_disable_reg_redirect_help_cont">
							<td colspan="2">
							<div>
							<?php _e('User will be redirected to this page after unsuccessful registration attempt via Social Login. You can specify the url of registration form or of a page showing message regarding disabled registration through Social Login.', 'super-socializer'); ?>
							</div>
							</td>
						</tr>
						</tbody>

						<tr>
							<th>
							<label for="the_champ_sl_disable_sl_admin"><?php _e("Disable Social Login for admin accounts", 'super-socializer'); ?></label><img id="the_champ_sl_disable_sl_admin_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_disable_sl_admin" name="the_champ_login[disable_sl_admin]" type="checkbox" <?php echo isset($theChampLoginOptions['disable_sl_admin']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_disable_sl_admin_help_cont">
							<td colspan="2">
							<div>
							<?php _e('After enabling this option, administrator users will not be able to login through social login. Other users will be able to login via social login.', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label><?php _e("Select Social Networks", 'super-socializer'); ?></label><img id="the_champ_sl_providers_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_facebook" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('facebook', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="facebook" />
							<label for="the_champ_login_facebook"><?php _e("Facebook", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_twitter" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('twitter', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="twitter" />
							<label for="the_champ_login_twitter"><?php _e("Twitter", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_linkedin" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('linkedin', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="linkedin" />
							<label for="the_champ_login_linkedin"><?php _e("LinkedIn", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_google" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('google', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="google" />
							<label for="the_champ_login_google"><?php _e("Google", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_vkontakte" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('vkontakte', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="vkontakte" />
							<label for="the_champ_login_vkontakte"><?php _e("Vkontakte", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_instagram" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('instagram', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="instagram" />
							<label for="the_champ_login_instagram"><?php _e("Instagram", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_steam" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('steam', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="steam" />
							<label for="the_champ_login_steam"><?php _e("Steam", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_line" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('line', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="line" />
							<label for="the_champ_login_line"><?php _e("Line", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_wordpress" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('wordpress', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="wordpress" />
							<label for="the_champ_login_wordpress"><?php _e("Wordpress", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_live" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('microsoft', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="microsoft" />
							<label for="the_champ_login_live"><?php _e("Windows Live", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_yahoo" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('yahoo', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="yahoo" />
							<label for="the_champ_login_yahoo"><?php _e("Yahoo", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_discord" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('discord', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="discord" />
							<label for="the_champ_login_discord"><?php _e("Discord", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_amazon" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('amazon', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="amazon" />
							<label for="the_champ_login_amazon"><?php _e("Amazon", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_stackoverflow" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('stackoverflow', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="stackoverflow" />
							<label for="the_champ_login_stackoverflow"><?php _e("Stack Overflow", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_spotify" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('spotify', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="spotify" />
							<label for="the_champ_login_spotify"><?php _e("Spotify", 'super-socializer'); ?></label>
							</div><div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_dribbble" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('dribbble', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="dribbble" />
							<label for="the_champ_login_dribbble"><?php _e("Dribbble", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_twitch" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('twitch', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="twitch" />
							<label for="the_champ_login_twitch"><?php _e("Twitch", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_foursquare" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('foursquare', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="foursquare" />
							<label for="the_champ_login_foursquare"><?php _e("Foursquare", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_dropbox" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('dropbox', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="dropbox" />
							<label for="the_champ_login_dropbox"><?php _e("Dropbox", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_disqus" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('disqus', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="disqus" />
							<label for="the_champ_login_disqus"><?php _e("Disqus", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_reddit" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('reddit', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="reddit" />
							<label for="the_champ_login_reddit"><?php _e("Reddit", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_mailru" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('mailru', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="mailru" />
							<label for="the_champ_login_mailru"><?php _e("Mail.ru", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_github" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('github', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="github" />
							<label for="the_champ_login_github"><?php _e("Github", 'super-socializer'); ?></label>
							</div>
							<div class="theChampHorizontalSharingProviderContainer">
							<input id="the_champ_login_kakao" name="the_champ_login[providers][]" type="checkbox" <?php echo isset($theChampLoginOptions['providers']) && in_array('kakao', $theChampLoginOptions['providers']) ? 'checked' : '';?> value="kakao" />
							<label for="the_champ_login_kakao"><?php _e("Kakao", 'super-socializer'); ?></label>
							</div>
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_providers_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Select Social ID provider to enable in Social Login', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_fblogin_key"><?php _e("Facebook App ID", 'super-socializer'); ?></label><img id="the_champ_slfb_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_fblogin_key" name="the_champ_login[fb_key]" type="text" value="<?php echo isset($theChampLoginOptions['fb_key']) ? $theChampLoginOptions['fb_key'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slfb_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Facebook Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Facebook App ID', 'super-socializer'), 'http://support.heateor.com/how-to-get-facebook-app-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Site URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_fblogin_secret"><?php _e("Facebook App Secret", 'super-socializer'); ?></label><img id="the_champ_slfb_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_fblogin_secret" name="the_champ_login[fb_secret]" type="text" value="<?php echo isset($theChampLoginOptions['fb_secret']) ? $theChampLoginOptions['fb_secret'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slfb_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Facebook Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Facebook App Secret', 'super-socializer'), 'http://support.heateor.com/how-to-get-facebook-app-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Site URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_twlogin_key"><?php _e("Twitter API Key", 'super-socializer'); ?></label><img id="the_champ_sltw_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_twlogin_key" name="the_champ_login[twitter_key]" type="text" value="<?php echo isset($theChampLoginOptions['twitter_key']) ? $theChampLoginOptions['twitter_key'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sltw_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Twitter Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Twitter API Key', 'super-socializer'), 'http://support.heateor.com/how-to-get-twitter-api-key-and-secret/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Website</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_twlogin_secret"><?php _e("Twitter API Secret", 'super-socializer'); ?></label><img id="the_champ_sltw_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_twlogin_secret" name="the_champ_login[twitter_secret]" type="text" value="<?php echo isset($theChampLoginOptions['twitter_secret']) ? $theChampLoginOptions['twitter_secret'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sltw_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Twitter Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Twitter API Secret', 'super-socializer'), 'http://support.heateor.com/how-to-get-twitter-api-key-and-secret/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Website</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_lilogin_key"><?php _e("LinkedIn Client ID", 'super-socializer'); ?></label><img id="the_champ_slli_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_lilogin_key" name="the_champ_login[li_key]" type="text" value="<?php echo isset($theChampLoginOptions['li_key']) ? $theChampLoginOptions['li_key'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slli_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for LinkedIn Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get LinkedIn Client ID', 'super-socializer'), 'http://support.heateor.com/how-to-get-linkedin-api-key/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Redirect URLs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()).'/?SuperSocializerAuth=Linkedin'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_lilogin_secret"><?php _e("LinkedIn Client Secret", 'super-socializer'); ?></label><img id="the_champ_slli_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_lilogin_secret" name="the_champ_login[li_secret]" type="text" value="<?php echo isset($theChampLoginOptions['li_secret']) ? $theChampLoginOptions['li_secret'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slli_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for LinkedIn Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get LinkedIn Client Secret', 'super-socializer'), 'http://support.heateor.com/how-to-get-linkedin-api-key/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Redirect URLs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()).'/?SuperSocializerAuth=Linkedin'; ?></strong>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_gplogin_key"><?php _e("Google Client ID", 'super-socializer'); ?></label><img id="the_champ_slgp_id_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_gplogin_key" name="the_champ_login[google_key]" type="text" value="<?php echo isset($theChampLoginOptions['google_key']) ? $theChampLoginOptions['google_key'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slgp_id_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Google Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Google Client ID', 'super-socializer'), 'http://support.heateor.com/how-to-get-google-plus-client-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>AUTHORIZED REDIRECT URI</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_gplogin_secret"><?php _e("Google Client Secret", 'super-socializer'); ?></label><img id="the_champ_slgp_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_gplogin_secret" name="the_champ_login[google_secret]" type="text" value="<?php echo isset($theChampLoginOptions['google_secret']) ? $theChampLoginOptions['google_secret'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slgp_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Google Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Google Client Secret', 'super-socializer'), 'http://support.heateor.com/how-to-get-google-plus-client-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>AUTHORIZED REDIRECT URI</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_vklogin_key"><?php _e("Vkontakte Application ID", 'super-socializer'); ?></label><img id="the_champ_slvk_id_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_vklogin_key" name="the_champ_login[vk_key]" type="text" value="<?php echo isset($theChampLoginOptions['vk_key']) ? $theChampLoginOptions['vk_key'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slvk_id_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Vkontakte Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Vkontakte Application ID', 'super-socializer'), 'http://support.heateor.com/how-to-get-vkontakte-application-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Site address</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_vklogin_secure_key"><?php _e("Vkontakte Secure key", 'super-socializer'); ?></label><img id="the_champ_slvk_secure_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_vklogin_secure_key" name="the_champ_login[vk_secure_key]" type="text" value="<?php echo isset($theChampLoginOptions['vk_secure_key']) ? $theChampLoginOptions['vk_secure_key'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slvk_secure_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Vkontakte Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Vkontakte Application ID', 'super-socializer'), 'http://support.heateor.com/how-to-get-vkontakte-application-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Site address</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_insta_key"><?php _e("Instagram App ID", 'super-socializer'); ?></label><img id="the_champ_slinsta_id_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_insta_key" name="the_champ_login[insta_id]" type="text" value="<?php echo isset($theChampLoginOptions['insta_id']) ? $theChampLoginOptions['insta_id'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_slinsta_id_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Instagram Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Instagram App ID', 'super-socializer'), 'http://support.heateor.com/how-to-get-instagram-client-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Valid OAuth Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Instagram'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_insta_secret"><?php _e("Instagram App Secret", 'super-socializer'); ?></label><img id="the_champ_insta_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_insta_secret" name="the_champ_login[insta_app_secret]" type="text" value="<?php echo isset($theChampLoginOptions['insta_app_secret']) ? $theChampLoginOptions['insta_app_secret'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_insta_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Instagram Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Instagram App Secret', 'super-socializer'), 'http://support.heateor.com/how-to-get-instagram-client-id/') ?>
							<br/>
							<span style="color: #14ACDF"><?php _e('Paste following url in <strong>Valid OAuth Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Instagram'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_line_key"><?php _e("Line Channel ID", 'super-socializer'); ?></label><img id="the_champ_sl_line_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_line_key" name="the_champ_login[line_channel_id]" type="text" value="<?php echo isset($theChampLoginOptions['line_channel_id']) ? $theChampLoginOptions['line_channel_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_line_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Line Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Line Channel ID', 'super-socializer'), 'http://support.heateor.com/create-line-channel-for-line-login/') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Line'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_line_secret"><?php _e("Line Channel Secret", 'super-socializer'); ?></label><img id="the_champ_sl_line_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_line_secret" name="the_champ_login[line_channel_secret]" type="text" value="<?php echo isset($theChampLoginOptions['line_channel_secret']) ? $theChampLoginOptions['line_channel_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_line_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Line Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Line Channel Secret', 'super-socializer'), 'http://support.heateor.com/create-line-channel-for-line-login/') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Line'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- wordpress -->
						<tr>
							<th>
							<label for="the_champ_sl_wordpress_key"><?php _e("Wordpress Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_wordpress_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_wordpress_key" name="the_champ_login[wordpress_client_id]" type="text" value="<?php echo isset($theChampLoginOptions['wordpress_client_id']) ? $theChampLoginOptions['wordpress_client_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_wordpress_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Wordpress Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Wordpress Client ID', 'super-socializer'), 'http://support.heateor.com/get-wordpress-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URLs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Wordpress'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_wordpress_secret"><?php _e("Wordpress Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_wordpress_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_wordpress_secret" name="the_champ_login[wordpress_client_secret]" type="text" value="<?php echo isset($theChampLoginOptions['wordpress_client_secret']) ? $theChampLoginOptions['wordpress_client_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_wordpress_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Wordpress Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Wordpress Client Secret', 'super-socializer'), 'http://support.heateor.com/get-wordpress-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URLs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Wordpress'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of WordPress -->
						<tr>
							<th>
							<label for="the_champ_sl_live_key"><?php _e("Microsoft Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_live_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_live_key" name="the_champ_login[live_channel_id]" type="text" value="<?php echo isset($theChampLoginOptions['live_channel_id']) ? $theChampLoginOptions['live_channel_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_live_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Live Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Microsoft Client ID', 'super-socializer'), 'http://support.heateor.com/how-to-get-windows-live-client-id-and-client-secret-key') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Live'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_live_secret"><?php _e("Microsoft Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_live_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_live_secret" name="the_champ_login[live_channel_secret]" type="text" value="<?php echo isset($theChampLoginOptions['live_channel_secret']) ? $theChampLoginOptions['live_channel_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_live_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Live Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Microsoft Client Secret key', 'super-socializer'), 'http://support.heateor.com/how-to-get-windows-live-client-id-and-client-secret-key') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Live'; ?></strong>
							</div>
							</td>
						</tr>
						<tr>
							<th>
							<label for="the_champ_sl_steam_key"><?php _e("Steam API Key", 'super-socializer'); ?></label><img id="the_champ_sl_steam_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_steam_key" name="the_champ_login[steam_api_key]" type="text" value="<?php echo isset($theChampLoginOptions['steam_api_key']) ? $theChampLoginOptions['steam_api_key'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_steam_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Steam Social Login to work. Get it at <a href="%s" target="_blank">this link</a>', 'super-socializer'), 'https://steamcommunity.com/dev/apikey'); ?><br/>
							<span style="color: #14ACDF"><?php _e('Save following <strong>domain</strong> to get the key', 'super-socializer'); ?></span><br/>
							<strong style="color: #14ACDF"><?php echo esc_url(home_url()); ?></strong>
							</div>
							</td>
						</tr>

						<!-- yahoo -->
						<tr>
							<th>
							<label for="the_champ_yahoo_key"><?php _e("Yahoo Client ID", 'super-socializer'); ?><img id="the_champ_yahoo_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_yahoo_key" name="the_champ_login[yahoo_channel_id]" type="text" value="<?php echo isset($theChampLoginOptions['yahoo_channel_id']) ? $theChampLoginOptions['yahoo_channel_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_yahoo_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Yahoo Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Yahoo Client ID', 'super-socializer'), 'http://support.heateor.com/get-yahoo-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in the <strong>Redirect URI(s)</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Yahoo'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_yahoo_secret"><?php _e("Yahoo Client Secret", 'super-socializer'); ?><img id="the_champ_yahoo_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_yahoo_secret" name="the_champ_login[yahoo_channel_secret]" type="text" value="<?php echo isset($theChampLoginOptions['yahoo_channel_secret']) ? $theChampLoginOptions['yahoo_channel_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_yahoo_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Yahoo Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Yahoo Client Secret key', 'super-socializer'), 'http://support.heateor.com/get-yahoo-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in the <strong>Redirect URI(s)</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Yahoo'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of yahoo -->

						<!-- discord -->
						<tr>
							<th>
							<label for="the_champ_discord_key"><?php _e("Discord Client ID", 'super-socializer'); ?><img id="the_champ_discord_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_discord_key" name="the_champ_login[discord_channel_id]" type="text" value="<?php echo isset($theChampLoginOptions['discord_channel_id']) ? $theChampLoginOptions['discord_channel_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_discord_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Discord Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Discord Client ID', 'super-socializer'), 'http://support.heateor.com/discord-client-id-discord-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in the <strong>Redirects</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Discord'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_discord_secret"><?php _e("Discord Client Secret", 'super-socializer'); ?><img id="the_champ_discord_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_discord_secret" name="the_champ_login[discord_channel_secret]" type="text" value="<?php echo isset($theChampLoginOptions['discord_channel_secret']) ? $theChampLoginOptions['discord_channel_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_discord_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Discord Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Discord Client Secret key', 'super-socializer'), 'http://support.heateor.com/discord-client-id-discord-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in the <strong>Redirects</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Discord'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of discord -->

						<!-- amazon -->
						<tr>
							<th>
							<label for="the_champ_amazon_key"><?php _e("Amazon Client ID", 'super-socializer'); ?><img id="the_champ_amazon_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_amazon_key" name="the_champ_login[amazon_client_id]" type="text" value="<?php echo isset($theChampLoginOptions['amazon_client_id']) ? $theChampLoginOptions['amazon_client_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_amazon_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Amazon Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Amazon Client ID', 'super-socializer'), 'http://support.heateor.com/amazon-client-id-client-secret-amazon-developer-center') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in the <strong>Allowed Return URLs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Amazon'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_amazon_secret"><?php _e("Amazon Client Secret", 'super-socializer'); ?><img id="the_champ_amazon_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_amazon_secret" name="the_champ_login[amazon_client_secret]" type="text" value="<?php echo isset($theChampLoginOptions['amazon_client_secret']) ? $theChampLoginOptions['amazon_client_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_amazon_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Amazon Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Amazon Client Secret key', 'super-socializer'), 'http://support.heateor.com/amazon-client-id-client-secret-amazon-developer-center') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in the <strong>Allowed Return URLs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Amazon'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of amazon -->

						<!-- Stack Overflow -->
						<tr>
							<th>
							<label for="the_champ_stackoverflow_client_id"><?php _e("Stack Overflow Client ID", 'super-socializer'); ?><img id="the_champ_stackoverflow_client_id_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_stackoverflow_client_id" name="the_champ_login[stackoverflow_client_id]" type="text" value="<?php echo isset($theChampLoginOptions['stackoverflow_client_id']) ? $theChampLoginOptions['stackoverflow_client_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_stackoverflow_client_id_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Stack Overflow Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Stack Overflow Client ID', 'super-socializer'), 'http://support.heateor.com/stackoverflow-client-id-stackoverflow-client-secret') ?>
							</div>
							</td>
						</tr>
						<tr>
							<th>
							<label for="the_champ_stackoverflow_secret"><?php _e("Stack Overflow Client Secret", 'super-socializer'); ?><img id="the_champ_stackoverflow_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" /></label>
							</th>
							<td>
							<input id="the_champ_stackoverflow_secret" name="the_champ_login[stackoverflow_client_secret]" type="text" value="<?php echo isset($theChampLoginOptions['stackoverflow_client_secret']) ? $theChampLoginOptions['stackoverflow_client_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_stackoverflow_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Stack Overflow Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Stack Overflow Client Secret key', 'super-socializer'), 'http://support.heateor.com/stackoverflow-client-id-stackoverflow-client-secret') ?>
							</div>
							</td>
						</tr>
						<tr>
							<th>
							<label for="the_champ_stackoverflow_key"><?php _e("Stack Overflow Key", 'super-socializer'); ?></label>
							<img id="the_champ_stackoverflow_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url( '../images/info.png', __FILE__ ) ?>" />
							</th>
							<td>
							<input id="the_champ_stackoverflow_key" name="the_champ_login[stackoverflow_key]" type="text" value="<?php echo $theChampLoginOptions['stackoverflow_key'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_stackoverflow_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Stack Overflow Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Stack Overflow Key', 'super-socializer'), 'http://support.heateor.com/stackoverflow-client-id-stackoverflow-client-secret') ?>
							</div>
							</td>
						</tr>
						<!-- Stack Overflow -->

						<!-- Spotify -->
						<tr>
							<th>
							<label for="the_champ_sl_spotify_key"><?php _e("Spotify Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_spotify_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_spotify_key" name="the_champ_login[spotify_client_id]" type="text" value="<?php echo isset($theChampLoginOptions['spotify_client_id']) ? $theChampLoginOptions['spotify_client_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_spotify_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Spotify Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Spotify Client ID', 'super-socializer'), 'http://support.heateor.com/get-spotify-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Spotify'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_spotify_secret"><?php _e("Spotify Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_spotify_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_spotify_secret" name="the_champ_login[spotify_client_secret]" type="text" value="<?php echo isset($theChampLoginOptions['spotify_client_secret']) ? $theChampLoginOptions['spotify_client_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_spotify_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Spotify Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Spotify Client Secret', 'super-socializer'), 'http://support.heateor.com/get-spotify-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Spotify'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of Spotify -->

						<!-- Dribbble -->
						<tr>
							<th>
							<label for="the_champ_sl_dribbble_key"><?php _e("Dribbble Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_dribbble_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_dribbble_key" name="the_champ_login[dribbble_client_id]" type="text" value="<?php echo isset($theChampLoginOptions['dribbble_client_id']) ? $theChampLoginOptions['dribbble_client_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_dribbble_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Dribbble Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Dribbble Client ID', 'super-socializer'), 'http://support.heateor.com/get-dribbble-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Dribbble'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_dribbble_secret"><?php _e("Dribbble Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_dribbble_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_dribbble_secret" name="the_champ_login[dribbble_client_secret]" type="text" value="<?php echo isset($theChampLoginOptions['dribbble_client_secret']) ? $theChampLoginOptions['dribbble_client_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_dribbble_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Dribbble Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Dribbble Client Secret', 'super-socializer'), 'http://support.heateor.com/get-dribbble-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Dribbble'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of Dribbble -->
						<!-- Twitch -->
						<tr>
							<th>
							<label for="the_champ_sl_twitch_key"><?php _e("Twitch Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_twitch_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_twitch_key" name="the_champ_login[twitch_client_id]" type="text" value="<?php echo $theChampLoginOptions['twitch_client_id'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_twitch_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Twitch Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Twitch Client ID', 'super-socializer'), 'http://support.heateor.com/how-to-enable-twitch-login-at-wordpress-website/') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Twitch'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_twitch_secret"><?php _e("Twitch Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_twitch_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_twitch_secret" name="the_champ_login[twitch_client_secret]" type="text" value="<?php echo $theChampLoginOptions['twitch_client_secret'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_twitch_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Twitch Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Twitch Client Secret', 'super-socializer'), 'http://support.heateor.com/how-to-enable-twitch-login-at-wordpress-website/') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Twitch'; ?></strong>
							</div>
							</td>
						</tr>

						<!-- end of Twitch -->
						<!-- Foursquare -->
						<tr>
							<th>
							<label for="the_champ_sl_foursquare_key"><?php _e("Foursquare Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_foursquare_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_foursquare_key" name="the_champ_login[foursquare_client_id]" type="text" value="<?php echo $theChampLoginOptions['foursquare_client_id'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_foursquare_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Foursquare Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Foursquare Client ID', 'super-socializer'), 'http://support.heateor.com/get-foursquare-client-id-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Foursquare'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_foursquare_secret"><?php _e("Foursquare Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_foursquare_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_foursquare_secret" name="the_champ_login[foursquare_client_secret]" type="text" value="<?php echo $theChampLoginOptions['foursquare_client_secret'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_foursquare_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Foursquare Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Foursquare Client Secret', 'super-socializer'), 'http://support.heateor.com/get-foursquare-client-id-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Foursquare'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- Dropbox -->
						<tr>
							<th>
							<label for="the_champ_sl_dropbox_key"><?php _e("Dropbox App Key", 'super-socializer'); ?></label><img id="the_champ_sl_dropbox_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_dropbox_key" name="the_champ_login[dropbox_app_key]" type="text" value="<?php echo $theChampLoginOptions['dropbox_app_key'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_dropbox_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Dropbox Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Dropbox App Key', 'super-socializer'), 'http://support.heateor.com/get-dropbox-app-key-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Dropbox'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_dropbox_secret"><?php _e("Dropbox App Secret", 'super-socializer'); ?></label><img id="the_champ_sl_dropbox_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_dropbox_secret" name="the_champ_login[dropbox_app_secret]" type="text" value="<?php echo $theChampLoginOptions['dropbox_app_secret'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_dropbox_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Dropbox Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Dropbox App Secret', 'super-socializer'), 'http://support.heateor.com/get-dropbox-app-key-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect URIs</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Dropbox'; ?></strong>
							</div>
							</td>
						</tr>

						<!-- end of Dropbox -->

						<!-- Disqus -->
						<tr>
							<th>
							<label for="the_champ_sl_disqus_key"><?php _e("Disqus Public Key", 'super-socializer'); ?></label><img id="the_champ_sl_disqus_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_disqus_key" name="the_champ_login[disqus_public_key]" type="text" value="<?php echo $theChampLoginOptions['disqus_public_key'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_disqus_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Disqus Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Disqus Public Key', 'super-socializer'), 'http://support.heateor.com/get-disqus-public-key-and-secret-key') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Disqus'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_disqus_secret"><?php _e("Disqus Secret Key", 'super-socializer'); ?></label><img id="the_champ_sl_disqus_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_disqus_secret" name="the_champ_login[disqus_secret_key]" type="text" value="<?php echo $theChampLoginOptions['disqus_secret_key'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_disqus_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Disqus Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Disqus Secret Key', 'super-socializer'), 'http://support.heateor.com/get-disqus-public-key-and-secret-key') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Disqus'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of Disqus -->

						<!-- Reddit -->
						<tr>
							<th>
							<label for="the_champ_sl_reddit_key"><?php _e("Reddit Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_reddit_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_reddit_key" name="the_champ_login[reddit_client_id]" type="text" value="<?php echo $theChampLoginOptions['reddit_client_id'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_reddit_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Reddit Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Reddit Client ID', 'super-socializer'), 'http://support.heateor.com/get-reddit-client-id-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect Uri</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Reddit'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_reddit_secret"><?php _e("Reddit Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_reddit_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_reddit_secret" name="the_champ_login[reddit_client_secret]" type="text" value="<?php echo $theChampLoginOptions['reddit_client_secret'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_reddit_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Reddit Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Reddit Client Secret', 'super-socializer'), 'http://support.heateor.com/get-reddit-client-id-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Redirect Uri</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Reddit'; ?></strong>
							</div>
							</td>
						</tr>

						<!-- end of Reddit -->

						<!-- Mailru -->
						<tr>
							<th>
							<label for="the_champ_sl_mailru_key"><?php _e("Mail.ru Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_mailru_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_mailru_key" name="the_champ_login[mailru_client_id]" type="text" value="<?php echo $theChampLoginOptions['mailru_client_id'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_mailru_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Mail.ru Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Mail.ru Client ID', 'super-socializer'), 'http://support.heateor.com/get-mail-ru-client-id-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>All redirect_uri</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Mailru'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_mailru_secret"><?php _e("Mail.ru Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_mailru_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_mailru_secret" name="the_champ_login[mailru_client_secret]" type="text" value="<?php echo $theChampLoginOptions['mailru_client_secret'] ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_mailru_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Mail.ru Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Mail.ru Client Secret', 'super-socializer'), 'http://support.heateor.com/get-mail-ru-client-id-and-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>All redirect_uri</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Mailru'; ?></strong>
							</div>
							</td>
						</tr>

						<!-- end of Mailru -->

						<!-- Github -->
						<tr>
							<th>
							<label for="the_champ_sl_github_key"><?php _e("Github Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_github_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_github_key" name="the_champ_login[github_client_id]" type="text" value="<?php echo isset($theChampLoginOptions['github_client_id']) ? $theChampLoginOptions['github_client_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_github_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Github Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Github Client ID', 'super-socializer'), 'http://support.heateor.com/get-github-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Github'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_github_secret"><?php _e("Github Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_github_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_github_secret" name="the_champ_login[github_client_secret]" type="text" value="<?php echo isset($theChampLoginOptions['github_client_secret']) ? $theChampLoginOptions['github_client_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_github_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Github Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Github Client Secret', 'super-socializer'), 'http://support.heateor.com/get-github-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Github'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of Github -->

						<!-- Kakao -->
						<tr>
							<th>
							<label for="the_champ_sl_kakao_key"><?php _e("Kakao Client ID", 'super-socializer'); ?></label><img id="the_champ_sl_kakao_key_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_kakao_key" name="the_champ_login[kakao_client_id]" type="text" value="<?php echo isset($theChampLoginOptions['kakao_client_id']) ? $theChampLoginOptions['kakao_client_id'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_kakao_key_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Kakao Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Kakao Client ID', 'super-socializer'), 'http://support.heateor.com/get-kakao-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Kakao'; ?></strong>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_kakao_secret"><?php _e("Kakao Client Secret", 'super-socializer'); ?></label><img id="the_champ_sl_kakao_secret_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_kakao_secret" name="the_champ_login[kakao_client_secret]" type="text" value="<?php echo isset($theChampLoginOptions['kakao_client_secret']) ? $theChampLoginOptions['kakao_client_secret'] : '' ?>" />
							</td>
						</tr>
						<tr class="the_champ_help_content" id="the_champ_sl_kakao_secret_help_cont">
							<td colspan="2">
							<div>
							<?php echo sprintf(__('Required for Kakao Social Login to work. Please follow the documentation at <a href="%s" target="_blank">this link</a> to get Kakao Client Secret', 'super-socializer'), 'http://support.heateor.com/get-kakao-client-id-client-secret') ?>
							<br/>
							<span style="color:#14ACDF"><?php _e('Paste following url in <strong>Callback URL</strong> option mentioned at the link', 'super-socializer'); ?></span>
							<br/>
							<strong style="color:#14ACDF"><?php echo esc_url(home_url()).'/SuperSocializerAuth/Kakao'; ?></strong>
							</div>
							</td>
						</tr>
						<!-- end of Kakao -->
					</table>
					</div>
				</div>
				</div>
				<?php include 'help.php'; ?>
			</div>
			
			<div class="menu_containt_div" id="tabs-2">
				<div class="clear"></div>
				<div class="the_champ_left_column">
				<div class="stuffbox">
					<h3 class="hndle"><label><?php _e('Social Login Options', 'super-socializer');?></label></h3>
					<div class="inside">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
						<tr>
							<th>
							<label for="the_champ_fblogin_title"><?php _e("Title", 'super-socializer'); ?></label><img id="the_champ_sl_title_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_fblogin_title" name="the_champ_login[title]" type="text" value="<?php echo isset($theChampLoginOptions['title']) ? $theChampLoginOptions['title'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_title_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Text to display above the Social Login interface', 'super-socializer') ?>
							</div>
							<img src="<?php echo plugins_url('../images/snaps/title.png', __FILE__); ?>" />
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_sl_same_tab"><?php _e("Trigger social login in the same browser tab", 'super-socializer'); ?></label><img id="the_champ_sl_same_tab_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_same_tab" name="the_champ_login[same_tab_login]" type="checkbox" <?php echo isset($theChampLoginOptions['same_tab_login']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_same_tab_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Trigger social login in the same browser tab instead of a popup window', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_align"><?php _e("Center align icons", 'super-socializer'); ?></label><img id="the_champ_sl_align_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_align" name="the_champ_login[center_align]" type="checkbox" <?php echo isset($theChampLoginOptions['center_align']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_align_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Center align social login icons', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_fblogin_enableAtLogin"><?php _e("Enable at login page", 'super-socializer'); ?></label><img id="the_champ_sl_loginpage_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_fblogin_enableAtLogin" name="the_champ_login[enableAtLogin]" type="checkbox" <?php echo isset($theChampLoginOptions['enableAtLogin']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_loginpage_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Social Login interface will get enabled at the login page of your website', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_fblogin_enableAtRegister"><?php _e("Enable at register page", 'super-socializer'); ?></label><img id="the_champ_sl_regpage_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_fblogin_enableAtRegister" name="the_champ_login[enableAtRegister]" type="checkbox" <?php echo isset($theChampLoginOptions['enableAtRegister']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_regpage_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Social Login interface will get enabled at the registration page of your website', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_fblogin_enableAtComment"><?php _e("Enable at comment form", 'super-socializer'); ?></label><img id="the_champ_sl_cmntform_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_fblogin_enableAtComment" name="the_champ_login[enableAtComment]" type="checkbox" <?php echo isset($theChampLoginOptions['enableAtComment']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_cmntform_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Social Login interface will get enabled at your Wordpress Comment form', 'super-socializer') ?>
							</div>
							<img src="<?php echo plugins_url('../images/snaps/sl_wpcomment.png', __FILE__); ?>" />
							</td>
						</tr>

						<?php
						/**
						 * Check if WooCommerce is active
						 **/
						if ( heateor_ss_is_plugin_active('woocommerce/woocommerce.php') ) {
						    ?>
						    <tr>
								<th>
								<label for="the_champ_sl_wc_before_form"><?php _e("Enable before WooCommerce Customer Login Form", 'super-socializer'); ?></label><img id="the_champ_sl_wc_before_form_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
								<input id="the_champ_sl_wc_before_form" name="the_champ_login[enable_before_wc]" type="checkbox" <?php echo isset($theChampLoginOptions['enable_before_wc']) ? 'checked' : '';?> value="1" />
								</td>
							</tr>
							
							<tr class="the_champ_help_content" id="the_champ_sl_wc_before_form_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Social Login Interface will get enabled before the customer login form at WooCommerce My Account page', 'super-socializer') ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
								<label for="the_champ_sl_wc_after_form"><?php _e("Enable at WooCommerce Customer Login Form", 'super-socializer'); ?></label><img id="the_champ_sl_wc_after_form_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
								<input id="the_champ_sl_wc_after_form" name="the_champ_login[enable_after_wc]" type="checkbox" <?php echo isset($theChampLoginOptions['enable_after_wc']) ? 'checked' : '';?> value="1" />
								</td>
							</tr>
							
							<tr class="the_champ_help_content" id="the_champ_sl_wc_after_form_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Integrate Social Login Interface with the customer login form at WooCommerce My Account page', 'super-socializer') ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
								<label for="the_champ_sl_wc_register_form"><?php _e("Enable at WooCommerce Customer Register Form", 'super-socializer'); ?></label><img id="the_champ_sl_wc_register_form_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
								<input id="the_champ_sl_wc_register_form" name="the_champ_login[enable_register_wc]" type="checkbox" <?php echo isset($theChampLoginOptions['enable_register_wc']) ? 'checked' : '';?> value="1" />
								</td>
							</tr>
							
							<tr class="the_champ_help_content" id="the_champ_sl_wc_after_form_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Integrate Social Login Interface with the customer register form at WooCommerce My Account page', 'super-socializer') ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
								<label for="the_champ_sl_wc_checkout"><?php _e("Enable at WooCommerce checkout page", 'super-socializer'); ?></label><img id="the_champ_sl_wc_checkout_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
								<input id="the_champ_sl_wc_checkout" name="the_champ_login[enable_wc_checkout]" type="checkbox" <?php echo isset($theChampLoginOptions['enable_wc_checkout']) ? 'checked' : '';?> value="1" />
								</td>
							</tr>
							
							<tr class="the_champ_help_content" id="the_champ_sl_wc_checkout_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Social Login Interface will get enabled at WooCommerce checkout page', 'super-socializer') ?>
								</div>
								</td>
							</tr>
						    <?php
						}
						if(!isset($theChampFacebookOptions['force_fb_comment']) && isset($theChampLoginOptions['enable'])){
							?>
							<tr>
								<th>
								<label for="the_champ_approve_comment"><?php _e("Auto-approve comments made by Social Login users", 'super-socializer'); ?></label><img id="the_champ_approve_comment_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
								<input id="the_champ_approve_comment" name="the_champ_login[autoApproveComment]" type="checkbox" <?php echo isset($theChampLoginOptions['autoApproveComment']) ? 'checked' : '';?> value="1" />
								</td>
							</tr>
							
							<tr class="the_champ_help_content" id="the_champ_approve_comment_help_cont">
								<td colspan="2">
								<div>
								<?php _e('If this option is enabled, and WordPress comment is made by Social Login user, comment will get approved immediately without keeping in moderation.', 'super-socializer') ?><br/>
								<strong><?php _e('Note: This is not related to Facebook comments', 'super-socializer') ?></strong>
								</div>
								</td>
							</tr>
							<?php
						}
						?>
						<tr>
							<th>
							<label for="the_champ_login_avatar"><?php _e("Enable social avatar", 'super-socializer'); ?></label><img id="the_champ_sl_avatar_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_login_avatar" onclick="if(this.checked){jQuery('#the_champ_avatar_options').css('display', 'table-row-group')}else{ jQuery('#the_champ_avatar_options').css('display', 'none') }" name="the_champ_login[avatar]" type="checkbox" <?php echo isset($theChampLoginOptions['avatar']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_avatar_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Social profile pictures of the logged in user will be displayed as profile avatar', 'super-socializer') ?>
							</div>
							<img src="<?php echo plugins_url('../images/snaps/sl_wpavatar.png', __FILE__); ?>" />
							<img src="<?php echo plugins_url('../images/snaps/sl_wpavatar2.png', __FILE__); ?>" />
							</td>
						</tr>
						<tbody id="the_champ_avatar_options" <?php echo !isset($theChampLoginOptions['avatar']) ? 'style = "display: none"' : '';?> >
						<tr>
							<th>
							<label><?php _e("Avatar quality", 'super-socializer'); ?></label><img id="the_champ_sl_avatar_quality_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_login_average_avatar" name="the_champ_login[avatar_quality]" type="radio" <?php echo !isset($theChampLoginOptions['avatar_quality']) || $theChampLoginOptions['avatar_quality'] == 'average' ? 'checked' : '';?> value="average" /> <label for="the_champ_login_average_avatar"><?php _e("Average", 'super-socializer'); ?></label><br/>
							<input id="the_champ_login_better_avatar" name="the_champ_login[avatar_quality]" type="radio" <?php echo isset($theChampLoginOptions['avatar_quality']) && $theChampLoginOptions['avatar_quality'] == 'better' ? 'checked' : '';?> value="better" /> <label for="the_champ_login_better_avatar"><?php _e("Best", 'super-socializer'); ?></label>
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_avatar_quality_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Choose avatar quality', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<?php if($theChampIsBpActive){ ?>
						<tr>
							<th>
							<label for="the_champ_sl_avatar_options"><?php _e("Show option for users to update social avatar at BuddyPress profile page", 'super-socializer'); ?></label><img id="the_champ_sl_avatar_options_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_avatar_options" name="the_champ_login[avatar_options]" type="checkbox" <?php echo isset($theChampLoginOptions['avatar_options']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_avatar_options_help_cont">
							<td colspan="2">
							<div>
							<?php _e('If enabled, users would be able to update their social avatar from "Profile photo" section in BuddyPress profile at front-end', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						<?php } ?>

						</tbody>
						
						<tr>
							<th>
							<label for="the_champ_login_email_required"><?php _e("Email required", 'super-socializer'); ?></label><img id="the_champ_sl_emailreq_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input onclick="theChampEmailPopupOptions(this)" id="the_champ_login_email_required" name="the_champ_login[email_required]" type="checkbox" <?php echo isset($theChampLoginOptions['email_required']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_emailreq_help_cont">
							<td colspan="2">
							<div>
							<?php _e('If enabled and Social ID provider does not provide user\'s email address on login, user will be asked to provide his/her email address. Otherwise, a dummy email will be generated', 'super-socializer') ?>
							</div>
							<img src="<?php echo plugins_url('../images/snaps/sl_email_required.png', __FILE__); ?>" />
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_password_email"><?php _e("Send post-registration email to user to set account password", 'super-socializer'); ?></label><img id="the_champ_sl_postreg_email_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_password_email" name="the_champ_login[password_email]" type="checkbox" <?php echo isset($theChampLoginOptions['password_email']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_postreg_email_help_cont">
							<td colspan="2">
							<div>
							<?php _e('If enabled, an email will be sent to user after registration through Social Login, regarding his/her login credentials (username-password to be able to login via traditional login form)', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_postreg_admin_email"><?php _e("Send new user registration notification email to admin", 'super-socializer'); ?></label><img id="the_champ_sl_postreg_admin_email_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_postreg_admin_email" name="the_champ_login[new_user_admin_email]" type="checkbox" <?php echo isset($theChampLoginOptions['new_user_admin_email']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_postreg_admin_email_help_cont">
							<td colspan="2">
							<div>
							<?php _e('If enabled, an email will be sent to admin after new user registers through Social Login, notifying admin about the new user registration', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label><?php _e("Login redirection", 'super-socializer'); ?></label><img id="the_champ_sl_loginredirect_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td id="the_champ_login_redirection_column">
							<input id="the_champ_login_redirection_same" name="the_champ_login[login_redirection]" type="radio" <?php echo !isset($theChampLoginOptions['login_redirection']) || $theChampLoginOptions['login_redirection'] == 'same' ? 'checked' : '';?> value="same" />
							<label for="the_champ_login_redirection_same"><?php _e('Same page where user logged in', 'super-socializer') ?></label><br/>
							<input id="the_champ_login_redirection_home" name="the_champ_login[login_redirection]" type="radio" <?php echo isset($theChampLoginOptions['login_redirection']) && $theChampLoginOptions['login_redirection'] == 'homepage' ? 'checked' : '';?> value="homepage" />
							<label for="the_champ_login_redirection_home"><?php _e('Homepage', 'super-socializer') ?></label><br/>
							<input id="the_champ_login_redirection_account" name="the_champ_login[login_redirection]" type="radio" <?php echo isset($theChampLoginOptions['login_redirection']) && $theChampLoginOptions['login_redirection'] == 'account' ? 'checked' : '';?> value="account" />
							<label for="the_champ_login_redirection_account"><?php _e('Account dashboard', 'super-socializer') ?></label><br/>
							<?php if($theChampIsBpActive){ ?>
								<input id="the_champ_login_redirection_bp" name="the_champ_login[login_redirection]" type="radio" <?php echo isset($theChampLoginOptions['login_redirection']) && $theChampLoginOptions['login_redirection'] == 'bp_profile' ? 'checked' : '';?> value="bp_profile" />
								<label for="the_champ_login_redirection_bp"><?php _e('BuddyPress profile page', 'super-socializer') ?></label><br/>
							<?php } ?>
							<input id="the_champ_login_redirection_custom" name="the_champ_login[login_redirection]" type="radio" <?php echo isset($theChampLoginOptions['login_redirection']) && $theChampLoginOptions['login_redirection'] == 'custom' ? 'checked' : '';?> value="custom" />
							<label for="the_champ_login_redirection_custom"><?php _e('Custom Url', 'super-socializer') ?></label><br/>
							<input id="the_champ_login_redirection_url" name="the_champ_login[login_redirection_url]" type="text" value="<?php echo isset($theChampLoginOptions['login_redirection_url']) ? $theChampLoginOptions['login_redirection_url'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_loginredirect_help_cont">
							<td colspan="2">
							<div>
							<?php _e('User will be redirected to the selected page after Social Login', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label><?php _e("Registration redirection", 'super-socializer'); ?></label><img id="the_champ_sl_register_redirect_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td id="the_champ_register_redirection_column">
							<input id="the_champ_register_redirection_same" name="the_champ_login[register_redirection]" type="radio" <?php echo !isset($theChampLoginOptions['register_redirection']) || $theChampLoginOptions['register_redirection'] == 'same' ? 'checked' : '';?> value="same" />
							<label for="the_champ_register_redirection_same"><?php _e('Same page from where user registered', 'super-socializer') ?></label><br/>
							<input id="the_champ_register_redirection_home" name="the_champ_login[register_redirection]" type="radio" <?php echo isset($theChampLoginOptions['register_redirection']) && $theChampLoginOptions['register_redirection'] == 'homepage' ? 'checked' : '';?> value="homepage" />
							<label for="the_champ_register_redirection_home"><?php _e('Homepage', 'super-socializer') ?></label><br/>
							<input id="the_champ_register_redirection_account" name="the_champ_login[register_redirection]" type="radio" <?php echo isset($theChampLoginOptions['register_redirection']) && $theChampLoginOptions['register_redirection'] == 'account' ? 'checked' : '';?> value="account" />
							<label for="the_champ_register_redirection_account"><?php _e('Account dashboard', 'super-socializer') ?></label><br/>
							<?php if($theChampIsBpActive){ ?>
								<input id="the_champ_register_redirection_bp" name="the_champ_login[register_redirection]" type="radio" <?php echo isset($theChampLoginOptions['register_redirection']) && $theChampLoginOptions['register_redirection'] == 'bp_profile' ? 'checked' : '';?> value="bp_profile" />
								<label for="the_champ_register_redirection_bp"><?php _e('BuddyPress profile page', 'super-socializer') ?></label><br/>
							<?php } ?>
							<input id="the_champ_register_redirection_custom" name="the_champ_login[register_redirection]" type="radio" <?php echo isset($theChampLoginOptions['register_redirection']) && $theChampLoginOptions['register_redirection'] == 'custom' ? 'checked' : '';?> value="custom" />
							<label for="the_champ_register_redirection_custom"><?php _e('Custom Url', 'super-socializer') ?></label><br/>
							<input id="the_champ_register_redirection_url" name="the_champ_login[register_redirection_url]" type="text" value="<?php echo isset($theChampLoginOptions['register_redirection_url']) ? $theChampLoginOptions['register_redirection_url'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_register_redirect_help_cont">
							<td colspan="2">
							<div>
							<?php _e('User will be redirected to the selected page after registration (first Social Login) through Social Login', 'super-socializer') ?>
							</div>
							</td>
						</tr>
					</table>
					</div>
				</div>

				<div class="stuffbox">
					<h3 class="hndle"><label><?php _e('Social Account Linking Options', 'super-socializer');?></label></h3>
					<div class="inside">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
						<tr>
							<th>
							<label for="the_champ_scl_title"><?php _e("Title", 'super-socializer'); ?></label><img id="the_champ_scl_title_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_scl_title" name="the_champ_login[scl_title]" type="text" value="<?php echo isset($theChampLoginOptions['scl_title']) ? $theChampLoginOptions['scl_title'] : '' ?>" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_scl_title_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Text to display above the Social Account Linking interface', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<tr>
							<th>
							<label for="the_champ_sl_linking"><?php _e("Link social account to already existing account, if email address matches", 'super-socializer'); ?></label><img id="the_champ_sl_linking_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_linking" name="the_champ_login[link_account]" type="checkbox" <?php echo isset($theChampLoginOptions['link_account']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_linking_help_cont">
							<td colspan="2">
							<div>
							<?php _e('If email address of the user\'s Social Account matches with an already existing account at your website, that social account will be linked to existing account. User would be able to manage this from Social Account Linking interface at their profile page.', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<?php if($theChampIsBpActive){ ?>
						<tr>
							<th>
							<label for="the_champ_sl_bp_linking"><?php _e("Enable social account linking at BuddyPress profile page", 'super-socializer'); ?></label><img id="the_champ_sl_bp_linking_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_sl_bp_linking" name="the_champ_login[bp_linking]" type="checkbox" <?php echo isset($theChampLoginOptions['bp_linking']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_bp_linking_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Enable this option to show social account linking interface at BuddyPress profile page', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						<?php } ?>
						
					</table>
					</div>
				</div>

					<div class="stuffbox" <?php echo !isset($theChampLoginOptions['email_required']) ? 'style="display: none"' : '' ?> id="the_champ_email_popup_options">
					<h3 class="hndle"><label><?php _e('Email popup options', 'super-socializer');?></label></h3>
					<div class="inside">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
						<tr>
							<th>
							<label for="the_champ_login_email_required_text"><?php _e("Text on 'Email required' popup", 'super-socializer'); ?></label><img id="the_champ_sl_emailreq_text_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<textarea rows="4" cols="40" id="the_champ_login_email_required_text" name="the_champ_login[email_popup_text]"><?php echo isset($theChampLoginOptions['email_popup_text']) ? $theChampLoginOptions['email_popup_text'] : '' ?></textarea>
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_emailreq_text_help_cont">
							<td colspan="2">
							<div>
							<?php _e('This text will be displayed on email required popup. Leave empty if not required.', 'super-socializer') ?>
							</div>
							<img width="550" src="<?php echo plugins_url('../images/snaps/sl_email_popup_message.png', __FILE__); ?>" />
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_login_email_required_error"><?php _e("Error message for 'Email required' popup", 'super-socializer'); ?></label><img id="the_champ_sl_emailreq_error_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<textarea rows="4" cols="40" id="the_champ_login_email_required_error" name="the_champ_login[email_error_message]"><?php echo isset($theChampLoginOptions['email_error_message']) ? $theChampLoginOptions['email_error_message'] : '' ?></textarea>
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_emailreq_error_help_cont">
							<td colspan="2">
							<div>
							<?php _e('This message will be displayed to user if it provides invalid or already registered email', 'super-socializer') ?>
							</div>
							<img width="550" src="<?php echo plugins_url('../images/snaps/sl_emailreq_message.png', __FILE__); ?>" />
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_email_popup_height"><?php _e("Email popup height", 'super-socializer'); ?></label><img id="the_champ_email_popup_height_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input style="width: 100px" id="the_champ_email_popup_height" name="the_champ_login[popup_height]" type="text" value="<?php echo isset($theChampLoginOptions['popup_height']) ? $theChampLoginOptions['popup_height'] : '' ?>" />px
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_email_popup_height_help_cont">
							<td colspan="2">
							<div>
							<?php _e('If you are seeing vertical scrollbar in the "Email required" popup, you can increase the height of popup by specifying in this option. Leave empty for default.', 'super-socializer') ?>
							</div>
							</td>
						</tr>
						
						<tr>
							<th>
							<label for="the_champ_password_email_verification"><?php _e("Enable email verification", 'super-socializer'); ?></label><img id="the_champ_sl_emailver_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_password_email_verification" name="the_champ_login[email_verification]" type="checkbox" <?php echo isset($theChampLoginOptions['email_verification']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_sl_emailver_help_cont">
							<td colspan="2">
							<div>
							<?php _e('If enabled, email provided by the user will be verified by sending a confirmation link to that email. User would not be able to login without verifying his/her email', 'super-socializer') ?>
							</div>
							</td>
						</tr>
					</table>
					</div>
					</div>
				</div>
				<?php include 'help.php'; ?>
			</div>
			
			<div class="menu_containt_div" id="tabs-3">
				<div class="clear"></div>
				<div class="the_champ_left_column">
				<div class="stuffbox">
					<h3><label><?php _e('GDPR', 'super-socializer');?></label></h3>
					<div class="inside">
					<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
						<tr>
							<th>
							<label for="the_champ_gdpr_enable"><?php _e("Enable GDPR opt-in", 'super-socializer'); ?></label><img id="the_champ_gdpr_enable_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
							</th>
							<td>
							<input id="the_champ_gdpr_enable" name="the_champ_login[gdpr_enable]" type="checkbox" <?php echo isset($theChampLoginOptions['gdpr_enable']) ? 'checked' : '';?> value="1" />
							</td>
						</tr>
						
						<tr class="the_champ_help_content" id="the_champ_gdpr_enable_help_cont">
							<td colspan="2">
							<div>
							<?php _e('Enable it to show GDPR opt-in for social login and social account linking', 'super-socializer') ?>
							</div>
							</td>
						</tr>

						<tbody id="the_champ_gdpr_options" <?php echo !isset($theChampLoginOptions['gdpr_enable']) ? 'style = "display: none"' : '';?> >
							<tr>
								<th>
								<label for="the_champ_gdpr_placement_above"><?php _e( "Placement of GDPR opt-in", 'super-socializer'); ?></label><img id="the_champ_gdpr_placement_help" class="the_champ_help_bubble" src="<?php echo plugins_url( '../images/info.png', __FILE__) ?>" />
								</th>
								<td>
								<input id="the_champ_gdpr_placement_above" name="the_champ_login[gdpr_placement]" type="radio" <?php echo ! isset( $theChampLoginOptions['gdpr_placement'] ) || $theChampLoginOptions['gdpr_placement'] == 'above' ? 'checked' : '';?> value="above" />
								<label for="the_champ_gdpr_placement_above"><?php _e( 'Above Social Login icons', 'super-socializer' ) ?></label><br/>
								<input id="the_champ_gdpr_placement_below" name="the_champ_login[gdpr_placement]" type="radio" <?php echo $theChampLoginOptions['gdpr_placement'] == 'below' ? 'checked' : '';?> value="below" />
								<label for="the_champ_gdpr_placement_below"><?php _e( 'Below Social Login icons', 'super-socializer' ) ?></label>
								</td>
							</tr>
							<tr class="the_champ_help_content" id="the_champ_gdpr_placement_help_cont">
								<td colspan="2">
								<div>
								<?php _e( 'Placement of GDPR opt-in above or below the social login icons', 'super-socializer' ) ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
								<label for="the_champ_privacy_policy_optin_text"><?php _e("Opt-in text", 'super-socializer'); ?></label><img id="the_champ_privacy_policy_optin_text_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
								<textarea rows="7" cols="63" id="the_champ_privacy_policy_optin_text" name="the_champ_login[privacy_policy_optin_text]"><?php echo isset( $theChampLoginOptions['privacy_policy_optin_text'] ) ? $theChampLoginOptions['privacy_policy_optin_text'] : '' ?></textarea>
								</td>
							</tr>

							<tr class="the_champ_help_content" id="the_champ_privacy_policy_optin_text_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Text for the GDPR opt-in', 'super-socializer') ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
									<label><?php _e("Text to link to Terms-Conditions page", 'super-socializer'); ?></label><img id="the_champ_tc_placeholder_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
									<input id="the_champ_tc_placeholder" name="the_champ_login[tc_placeholder]" type="text" value="<?php echo $theChampLoginOptions['tc_placeholder'] ?>" />
								</td>
							</tr>

							<tr class="the_champ_help_content" id="the_champ_tc_placeholder_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Word(s) in the opt-in text to be linked to terms-conditions page', 'super-socializer') ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
									<label><?php _e("Terms-Conditions Url", 'super-socializer'); ?></label><img id="the_champ_tc_url_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
									<input id="the_champ_tc_url" name="the_champ_login[tc_url]" type="text" value="<?php echo $theChampLoginOptions['tc_url'] ?>" />
								</td>
							</tr>

							<tr class="the_champ_help_content" id="the_champ_tc_url_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Url of the terms-conditions page of your website', 'super-socializer') ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
									<label><?php _e("Text to link to Privacy Policy page", 'super-socializer'); ?></label><img id="the_champ_privacy_ppu_placeholder_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
									<input id="the_champ_privacy_ppu_placeholder" name="the_champ_login[ppu_placeholder]" type="text" value="<?php echo $theChampLoginOptions['ppu_placeholder'] ?>" />
								</td>
							</tr>

							<tr class="the_champ_help_content" id="the_champ_privacy_ppu_placeholder_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Word(s) in the opt-in text to be linked to privacy policy page', 'super-socializer') ?>
								</div>
								</td>
							</tr>

							<tr>
								<th>
									<label><?php _e("Privacy Policy Url", 'super-socializer'); ?></label><img id="the_champ_privacy_policy_url_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
								</th>
								<td>
									<input id="the_champ_privacy_policy_url" name="the_champ_login[privacy_policy_url]" type="text" value="<?php echo $theChampLoginOptions['privacy_policy_url'] ?>" />
								</td>
							</tr>

							<tr class="the_champ_help_content" id="the_champ_privacy_policy_url_help_cont">
								<td colspan="2">
								<div>
								<?php _e('Url of the privacy policy page of your website', 'super-socializer') ?>
								</div>
								</td>
							</tr>
						</tbody>
					</table>
					</div>
				</div>
				</div>
				<?php include 'help.php'; ?>
			</div>

			<?php if($theChampIsBpActive){
				$profileFields = array(
					'Social ID' => 'id',
					'Social Network' => 'provider',
					'Email' => 'email',
					'Name' => 'name',
					'Username' => 'user_name',
					'First Name' => 'first_name',
					'Last Name' => 'last_name',
					'Bio' => 'bio',
					'Social Profile Url' => 'link',
					'Social Avatar Url' => 'avatar',
					'Large Social Avatar Url' => 'large_avatar'
				);
			?>
			<div class="menu_containt_div" id="tabs-4">
				<div class="clear"></div>
				<div class="the_champ_left_column">
				<div class="stuffbox">
					<h3><label><?php _e('XProfile Integration', 'super-socializer');?></label></h3>
					<div class="inside">
					<?php
					global $wpdb;
					$xprofileFields = $wpdb-> get_results("SELECT * FROM " . $wpdb-> prefix . "bp_xprofile_fields");
					if($xprofileFields){
						?>
						<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
						<?php
						foreach($xprofileFields as $field){
							if($field-> id == 1){
								continue;
							}	
							?>
							<tr>
								<td>
									<label><?php _e($field-> name, 'super-socializer'); ?></label>
								</td>
								<td>
									<select name="the_champ_login[xprofile_mapping][<?php echo $field-> name ?>]">
										<option value="">--<?php _e('Select', 'super-socializer') ?>--</option>
										<?php
										foreach($profileFields as $key => $val){
											?>
											<option <?php echo isset($theChampLoginOptions['xprofile_mapping'][$field-> name]) && $theChampLoginOptions['xprofile_mapping'][$field-> name] == $val ? 'selected' : '' ?> value="<?php echo $val ?>"><?php echo ucfirst($key) ?></option>
											<?php
										}
										?>
									</select>
								</td>
							</tr>
							<?php
						}
						?>
						</table>
						<?php
					}
					?>
					</div>
				</div>
				</div>
				<?php include 'help.php'; ?>
			</div>
			<?php } ?>
			
			<div class="menu_containt_div" id="tabs-5">
				<div class="clear"></div>
				<div class="the_champ_left_column">
				<div class="stuffbox">
					<h3><label><?php _e('Shortcode & Widget', 'super-socializer');?></label></h3>
					<div class="inside" style="padding-left:7px">
						<p><a style="text-decoration:none" href="http://support.heateor.com/social-login-shortcode-and-widget/" target="_blank"><?php _e('Social Login Shortcode & Widget', 'super-socializer') ?></a></p>
						<p><a style="text-decoration:none" href="http://support.heateor.com/social-linking-shortcode" target="_blank"><?php _e('Social Linking Shortcode', 'super-socializer') ?></a></p>
					</div>
				</div>
				</div>
				<?php include 'help.php'; ?>
			</div>

			<div class="menu_containt_div" id="tabs-6">
				<div class="clear"></div>
				<div class="the_champ_left_column">
				<div class="stuffbox">
					<h3><label><?php _e('FAQ', 'super-socializer');?></label></h3>
					<div class="inside faq" style="padding-left:7px">
						<p><?php _e('<strong>Note:</strong> Plugin will not work on local server. You should have an online website for the plugin to function properly.', 'super-socializer'); ?></p>
						<p>
						<a href="javascript:void(0)"><?php _e('Why is social login not working?', 'super-socializer'); ?></a>
						<div><?php _e('Make sure that App ID and Secret (Client ID and Secret) keys you have saved, belong to the same app', 'super-socializer'); ?></div>
						</p>
						<p><a href="https://wordpress.org/support/topic/social-login-not-working-with-cache-enabled/" target="_blank"><?php _e('Social Login not working with Varnish enabled', 'super-socializer') ?></a></p>
						<p><a href="https://wordpress.org/support/topic/social-login-not-working-with-cache-enabled/" target="_blank"><?php _e('Why the user is not appearing logged in even after Social Login until the webpage is refreshed manually?', 'super-socializer' ) ?></a></p>
						<p><a href="http://support.heateor.com/browser-blocking-social-features/" target="_blank"><?php _e('Why is my browser blocking some features of the plugin?', 'super-socializer' ) ?></a></p>
					</div>
				</div>
				</div>
				<?php include 'help.php'; ?>
			</div>
			
		</div>

		<div class="the_champ_clear"></div>
		<p class="submit">
			<input style="margin-left:8px" type="submit" name="save" class="button button-primary" value="<?php _e("Save Changes", 'super-socializer'); ?>" />
		</p>
		<p>
			<?php
			echo sprintf(__('You can appreciate the effort put in this free plugin by rating it <a href="%s" target="_blank">here</a>', 'super-socializer'), 'https://wordpress.org/support/view/plugin-reviews/super-socializer');
			?>
		</p>
		</form>
		<div class="clear"></div>
		<div class="stuffbox">
			<h3><label><?php _e("Instagram Shoutout", 'super-socializer'); ?></label></h3>
			<div class="inside" style="padding-left:10px">
			<p><?php _e( 'If you can send (to hello@heateor.com) how this plugin is helping your business, we would be glad to shoutout on Instagram. You can also send any relevant hashtags and people to mention in the Instagram post.', 'super-socializer' ) ?></p>
			</div>
		</div>
	</div>