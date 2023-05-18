<?php
$edit_terms_value = (!empty($edit_post_terms_id)) ? implode(',', $edit_post_terms_id) : '';
if (!empty($field_details['auto_complete'])) {
    $terms = get_terms($taxonomy, array('hide_empty' => 0));
    $tags = array_column($terms, 'name');
    $tags = implode(',', $tags);
    ?>

    <input type="text" class="fpsml-auto-complete-field"/>
    <textarea class="fpsml-available-tags fpsml-display-none"><?php echo esc_html($tags); ?></textarea>
    <input type="hidden" name="<?php echo esc_attr($field_key); ?>" class="fpsml-auto-complete-values" value="<?php echo esc_attr($edit_terms_value); ?>"/>
    <div class="fpsml-auto-complete-tags">
        <?php
        if (!empty($edit_post_terms_id)) {
            foreach ($edit_post_terms_id as $edit_post_term) {
                ?>
                <div class="fpsml-each-tag"><span class="fpsml-tag-text"><?php echo esc_html($edit_post_term); ?></span><span class="fpsml-tag-remove-trigger"><i class="fas fa-times-circle"></i></span></div>
                        <?php
                    }
                }
                ?>
    </div>
    <?php
} else {
    ?>
    <input type="text" name="<?php echo esc_attr($field_key); ?>" value="<?php echo ($edit_terms_value); ?>"/>
    <?php
}
?>

