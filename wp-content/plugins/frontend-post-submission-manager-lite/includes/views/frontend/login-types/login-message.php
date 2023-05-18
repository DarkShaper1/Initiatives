<?php
defined('ABSPATH') or die('No script kiddies please!!');
?>
<div class="fpsml-login-message-wrap fpsml-login-message-<?php echo esc_attr($form_template); ?>">
    <div class="fpsml-login-message"><?php echo $fpsml_library_obj->sanitize_html($login_settings['login_message']); ?></div>
    <a href="<?php echo esc_url($login_settings['login_link_url']); ?>" class="fpsml-login-link-button"><?php echo esc_html($login_settings['login_link_label']); ?></a>
</div>