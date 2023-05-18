jQuery(function ($) {

    if (RclUploaders.isset('rcl_avatar')) {
        RclUploaders.get('rcl_avatar').afterDone = function (e, data) {
            jQuery('#rcl-avatar .avatar-image img, #recallbar img.avatar').attr('srcset', '')
                .attr('src', data.result.src.thumbnail)
                .load()
                .animateCss('zoomIn');

            rcl_do_action('rcl_success_upload_avatar', data);
        };

        RclUploaders.get('rcl_avatar').animateLoading = function (status) {
            status ? rcl_preloader_show(jQuery('#rcl-avatar')) : rcl_preloader_hide();
        };
    }

});