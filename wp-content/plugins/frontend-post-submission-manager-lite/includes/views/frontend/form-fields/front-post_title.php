<?php
defined('ABSPATH') or die('No script kiddies please!!');
?>
<input type="text" name="<?php echo esc_attr($field_key); ?>" value="<?php echo (!empty($edit_post)) ? esc_attr($edit_post->post_title) : ''; ?>"/>
