<?php
defined('ABSPATH') or die("Cheating........Uh!!");
?>
<div id="fb-root"></div>
<div>
	<?php
	echo sprintf(__('You can appreciate the effort put in this free plugin by rating it <a href="%s" target="_blank">here</a>', 'super-socializer'), 'https://wordpress.org/support/view/plugin-reviews/super-socializer');
	?>
</div>
<div class="metabox-holder columns-2" id="post-body">
	<form action="options.php" method="post">
		<?php settings_fields('the_champ_general_options'); ?>
		<div class="the_champ_left_column">
			<div class="stuffbox">
				<h3><label><?php _e('General Options', 'super-socializer');?></label></h3>
				<div class="inside">
				<table width="100%" border="0" cellspacing="0" cellpadding="0" class="form-table editcomment menu_content_table">
					<tr>
						<th>
						<label for="the_champ_footer_script"><?php _e("Include Javascript in website footer", 'super-socializer'); ?></label><img id="the_champ_footer_script_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
						</th>
						<td>
						<input id="the_champ_footer_script" name="the_champ_general[footer_script]" type="checkbox" <?php echo isset($theChampGeneralOptions['footer_script']) ? 'checked = "checked"' : '';?> value="1" />
						</td>
					</tr>
					
					<tr class="the_champ_help_content" id="the_champ_footer_script_help_cont">
						<td colspan="2">
						<div>
						<?php _e('If enabled (recommended), Javascript files will be included in the footer of your website.', 'super-socializer') ?>
						</div>
						</td>
					</tr>

					<tr>
						<th>
						<label for="the_champ_combined_script"><?php _e("Load all Javascript files in single file", 'super-socializer'); ?></label><img id="the_champ_combined_script_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
						</th>
						<td>
						<input id="the_champ_combined_script" name="the_champ_general[combined_script]" type="checkbox" <?php echo isset($theChampGeneralOptions['combined_script']) ? 'checked = "checked"' : '';?> value="1" />
						</td>
					</tr>
					
					<tr class="the_champ_help_content" id="the_champ_combined_script_help_cont">
						<td colspan="2">
						<div>
						<?php _e('Loads Javascript in single request.', 'super-socializer') ?>
						</div>
						</td>
					</tr>

					<tr>
						<th>
						<label for="the_champ_delete_options"><?php _e("Delete all the options on plugin deletion", 'super-socializer'); ?></label><img id="the_champ_delete_options_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
						</th>
						<td>
						<input id="the_champ_delete_options" name="the_champ_general[delete_options]" type="checkbox" <?php echo isset($theChampGeneralOptions['delete_options']) ? 'checked = "checked"' : '';?> value="1" />
						</td>
					</tr>
					
					<tr class="the_champ_help_content" id="the_champ_delete_options_help_cont">
						<td colspan="2">
						<div>
						<?php _e('If enabled, plugin options will get deleted when plugin is deleted/uninstalled and you will need to reconfigure the options when you install the plugin next time.', 'super-socializer') ?>
						</div>
						</td>
					</tr>

					<tr>
						<th>
						<label for="the_champ_custom_css"><?php _e("Custom CSS", 'super-socializer' ); ?></label><img id="the_champ_custom_css_help" class="the_champ_help_bubble" src="<?php echo plugins_url('../images/info.png', __FILE__) ?>" />
						</th>
						<td>
						<textarea rows="7" cols="63" id="the_champ_custom_css" name="the_champ_general[custom_css]"><?php echo isset( $theChampGeneralOptions['custom_css'] ) ? $theChampGeneralOptions['custom_css'] : '' ?></textarea>
						</td>
					</tr>
					
					<tr class="the_champ_help_content" id="the_champ_custom_css_help_cont">
						<td colspan="2">
						<div>
						<?php _e('You can specify any additional CSS rules (without &lt;style&gt; tag)', 'super-socializer' ) ?>
						</div>
						</td>
					</tr>	
				</table>
				
				<div class="the_champ_clear"></div>
				<p class="submit">
					<input id="the_champ_enable_fblike" style="margin-left:8px" type="submit" name="save" class="button button-primary" value="<?php _e("Save Changes", 'super-socializer'); ?>" />
				</p>
				<div>
					<?php
					echo sprintf(__('You can appreciate the effort put in this free plugin by rating it <a href="%s" target="_blank">here</a>', 'super-socializer'), 'https://wordpress.org/support/view/plugin-reviews/super-socializer');
					?>
				</div>
				</div>
			</div>
			</div>
			<?php include 'help.php'; ?>
	</form>
	<div class="clear"></div>
	<div class="stuffbox">
		<h3><label><?php _e("Instagram Shoutout", 'super-socializer' ); ?></label></h3>
		<div class="inside" style="padding-left:7px">
		<p><?php _e( 'If you can send (to hello@heateor.com) how this plugin is helping your business, we would be glad to shoutout on Instagram. You can also send any relevant hashtags and people to mention in the Instagram post.', 'super-socializer' ) ?></p>
		</div>
	</div>
</div>