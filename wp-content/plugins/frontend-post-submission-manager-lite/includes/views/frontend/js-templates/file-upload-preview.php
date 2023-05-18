<div class="fpsml-file-preview-row">
    <span class="fpsml-file-preview-column"><img src="{{data.media_url}}"/></span>
    <span class="fpsml-file-preview-column">{{data.media_name}}</span>
    <span class="fpsml-file-preview-column">{{data.media_size}}</span>
    <span class="fpsml-file-preview-column"><input type="button" class="fpsml-media-delete-button" data-media-id='{{data.media_id}}' data-media-key='{{data.media_key}}' value="<?php esc_html_e('Delete', 'frontend-post-submission-manager-lite'); ?>"/></span>
</div>