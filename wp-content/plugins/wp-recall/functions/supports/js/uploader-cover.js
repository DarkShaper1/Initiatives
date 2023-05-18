jQuery(function ($) {

    if (RclUploaders.isset('rcl_cover')) {
        RclUploaders.get('rcl_cover').afterDone = function (e, data) {

            jQuery('#lk-conteyner').css('background-image', 'url(' + data.result.src.full + ')').animateCss('fadeIn');

            rcl_notice('Изображение загружено', 'success', 10000);

            rcl_do_action('rcl_success_upload_cover', data);

        };

        RclUploaders.get('rcl_cover').animateLoading = function (status) {

            status ? rcl_preloader_show(jQuery('#lk-conteyner')) : rcl_preloader_hide();

        };
    }

});
