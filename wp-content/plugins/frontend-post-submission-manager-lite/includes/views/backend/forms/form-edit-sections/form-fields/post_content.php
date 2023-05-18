<div class="fpsml-each-form-field">
    <div class="fpsml-field-head fpsml-clearfix">
        <h3 class="fpsml-field-title"><span class="dashicons dashicons-arrow-down"></span><?php esc_html_e( 'Post Content', 'frontend-post-submission-manager-lite' ); ?></h3>
    </div>
    <div class="fpsml-field-body fpsml-display-none">
        <?php include(FPSML_PATH . '/includes/views/backend/forms/form-edit-sections/form-fields/common-fields.php'); ?>
        <div class="fpsml-show-fields-ref-<?php echo esc_attr( $field_key ); ?> <?php echo (empty( $field_details['show_on_form'] )) ? 'fpsml-display-none' : ''; ?>">
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e( 'Editor Type', 'frontend-post-submission-manager-lite' ); ?></label>
                <div class="fpsml-field">
                    <?php
                    $editor_type = (!empty( $field_details['editor_type'] )) ? $field_details['editor_type'] : 'simple';
                    ?>
                    <select name="<?php echo esc_attr( $field_name_prefix ); ?>[editor_type]" class="fpsml-editor-type">
                        <option value="simple" <?php selected( $editor_type, 'simple' ); ?>><?php esc_html_e( 'Simple Textarea', 'frontend-post-submission-manager-lite' ); ?></option>
                        <option value="rich" <?php selected( $editor_type, 'rich' ); ?>><?php esc_html_e( 'Rich Text Editor', 'frontend-post-submission-manager-lite' ); ?></option>
                        <option value="visual" <?php selected( $editor_type, 'visual' ); ?>><?php esc_html_e( 'Visual Text Editor', 'frontend-post-submission-manager-lite' ); ?></option>
                        <option value="html" <?php selected( $editor_type, 'html' ); ?>><?php esc_html_e( 'HTML Text Editor', 'frontend-post-submission-manager-lite' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e( 'Editor Height', 'frontend-post-submission-manager-lite' ); ?></label>
                <div class="fpsml-field">
                    <input type="number" name="<?php echo esc_attr( $field_name_prefix ); ?>[editor_height]" value="<?php echo (!empty( $field_details['editor_height'] )) ? esc_attr( $field_details['editor_height'] ) : ''; ?>" min="1"/>
                    <p class="description"><?php esc_html_e( 'Please enter the height of the editor in px if you want to increase or decrease the default height.', 'frontend-post-submission-manager-lite' ); ?></p>
                </div>
            </div>
            <?php
            $media_ref_editors = array( 'rich', 'visual' );
            if ( $form_row->form_type == 'login_require' ) {
                ?>
                <div class="fpsml-field-wrap fpsml-editor-type-ref <?php echo (!in_array( $editor_type, $media_ref_editors )) ? 'fpsml-display-none' : '' ?>">
                    <label><?php esc_html_e( 'Media Upload', 'frontend-post-submission-manager-lite' ); ?></label>
                    <div class="fpsml-field">
                        <input type="checkbox" name="<?php echo esc_attr( $field_name_prefix ); ?>[media_upload]" value="1" <?php echo (!empty( $field_details['media_upload'] )) ? 'checked="checked"' : ''; ?>/>
                        <p class="description"><?php esc_html_e( 'Please check if you want to enable the direct media upload to the post content for logged in users.', 'frontend-post-submission-manager-lite' ); ?></p>
                        <p class="description"><?php echo __( sprintf( 'Please note that media upload button only shows if logged in user role has the upload_files capabilities. Please check %s here %s for an easy reference.', '<a href="https://wordpress.org/support/article/roles-and-capabilities/#capability-vs-role-table" target="_blank">', '</a>' ), 'frontend-post-submission-manager-lite' ); ?></p>
                    </div>
                </div>
                <?php
            }
            ?>

            <div class="fpsml-field-wrap">
                <label><?php esc_html_e( 'Character Limit', 'frontend-post-submission-manager-lite' ); ?></label>
                <div class="fpsml-field">
                    <input type="number" min="0" name="<?php echo esc_attr( $field_name_prefix ); ?>[character_limit]" value="<?php echo (!empty( $field_details['character_limit'] )) ? intval( $field_details['character_limit'] ) : ''; ?>"/>
                </div>
            </div>
            <div class="fpsml-field-wrap">
                <label><?php esc_html_e( 'Character Limit Error Message', 'frontend-post-submission-manager-lite' ); ?></label>
                <div class="fpsml-field">
                    <input type="text" name="form_details[form][fields][<?php echo esc_attr( $field_key ) ?>][character_limit_error_message]" value="<?php echo (!empty( $field_details['character_limit_error_message'] )) ? esc_attr( $field_details['character_limit_error_message'] ) : ''; ?>"/>
                </div>
            </div>
        </div>
    </div>

</div>
