<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!!' );
$editor_type = (!empty( $field_details['editor_type'] )) ? $field_details['editor_type'] : 'simple';
$post_content = (!empty( $edit_post )) ? $edit_post->post_content : '';
$editor_height = (!empty( $field_details['editor_height'] )) ? intval( $field_details['editor_height'] ) : '';
if ( $editor_type == 'simple' ) {
    ?>
    <textarea name="<?php echo esc_attr( $field_key ); ?>" <?php echo (!empty( $editor_height )) ? 'style="height:' . $editor_height . 'px"' : ''; ?>><?php echo $fpsml_library_obj->sanitize_html( $post_content ); ?></textarea>
    <?php
} else {
    switch( $editor_type ) {
        case 'rich':
            $teeny = false;
            $show_quicktags = true;
            break;
        case 'visual':
            $teeny = false;
            $show_quicktags = false;
            break;
            break;
        case 'html':
            $teeny = true;
            $show_quicktags = true;
            break;
    }
    $media_upload = (!empty( $field_details['media_upload'] )) ? true : false;
    $editor_settings = array(
        'textarea_name' => $field_key,
        'media_buttons' => $media_upload,
        'teeny' => $teeny,
        'wpautop' => true,
        'quicktags' => $show_quicktags,
        'editor_height' => $editor_height,
        'editor_class' => apply_filters( 'fpsml_editor_class', 'fpsml-post-content-editor' )
    );
    wp_editor( $post_content, 'fpsml_' . $form_row->form_alias, $editor_settings );
}
?>
