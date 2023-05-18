jQuery(function ($) {
    rcl_do_action('rcl_init');
});

jQuery(window).on('load', function () {
    jQuery('body').on('drop', function (e) {
        return false;
    });
    jQuery(document.body).on("drop", function (e) {
        e.preventDefault();
    });
});

rcl_add_action('rcl_init', 'rcl_init_ajax_tab');

function rcl_init_ajax_tab() {
    jQuery('body').on('click', '.rcl-ajax', function () {

        var e = jQuery(this);

        if (e.hasClass('tab-upload'))
            return false;

        rcl_do_action('rcl_before_upload_tab', e);

        rcl_ajax({
            data: {
                action: 'rcl_ajax_tab',
                post: e.data('post'),
                tab_url: e.attr('href') //encodeURIComponent(e.attr('href'))
            },
            success: function (data) {

                e.removeClass('tab-upload');

                data = rcl_apply_filters('rcl_upload_tab', data);

                if (data.result.error) {
                    rcl_notice(data.result.error, 'error', 10000);
                    return false;
                }

                var url = data.post.tab_url;
                var supports = data.post.supports;
                var subtab_id = data.post.subtab_id;

                if (supports && supports.indexOf('dialog') >= 0) { //если вкладка поддерживает диалог

                    if (!subtab_id) { //если загружается основная вкладка

                        ssi_modal.show({
                            className: 'rcl-dialog-tab ' + data.post.tab_id,
                            sizeClass: 'small',
                            buttons: [{
                                label: Rcl.local.close,
                                closeAfter: true
                            }],
                            content: data.result
                        });

                    } else {

                        var box_id = '#ssi-modalContent';

                    }

                } else {

                    rcl_update_history_url(url);

                    if (!subtab_id) {
                        jQuery('.rcl-tab-button .recall-button').removeClass('active').removeClass('rcl-bttn__active');
                    }

                    e.addClass('active').addClass('rcl-bttn__active');

                    var box_id = '#lk-content';

                }

                if (box_id) {

                    jQuery(box_id).html(data.result);

                    var options = rcl_get_options_url_params();

                    if (options.scroll == 1) {
                        var offsetTop = jQuery(box_id).offset().top;
                        jQuery('body,html').animate({
                                scrollTop: offsetTop - options.offset
                            },
                            1000);
                    }

                    if (data.includes) {

                        var includes = data.includes;

                        includes.forEach(function (src, i, includes) {

                            jQuery.getScript(src);

                        });

                    }

                }

                if (!data.post.subtab_id) {
                    if (typeof animateCss !== 'undefined') {
                        jQuery('#lk-content').animateCss('fadeIn');
                    }
                } else {
                    if (typeof animateCss !== 'undefined') {
                        jQuery('#lk-content .rcl-subtab-content').animateCss('fadeIn');
                    }
                }

                rcl_do_action('rcl_upload_tab', {
                    element: e,
                    result: data
                });

            }
        });

        return false;

    });
}

function rcl_get_options_url_params() {

    var options = {
        scroll: 1,
        offset: 100
    };

    options = rcl_apply_filters('rcl_options_url_params', options);

    return options;
}

function rcl_add_dropzone(idzone) {

    jQuery(document.body).on("drop", function (e) {
        var dropZone = jQuery(idzone),
            node = e.target,
            found = false;

        if (dropZone[0]) {
            dropZone.removeClass('in hover');
            do {
                if (node === dropZone[0]) {
                    found = true;
                    break;
                }
                node = node.parentNode;
            } while (node != null);

            if (found) {
                e.preventDefault();
            } else {
                return false;
            }
        }
    });

    jQuery(idzone).on('dragover', function (e) {
        var dropZone = jQuery(idzone),
            timeout = window.dropZoneTimeout;

        if (!timeout) {
            dropZone.addClass('in');
        } else {
            clearTimeout(timeout);
        }

        var found = false,
            node = e.target;

        do {
            if (node === dropZone[0]) {
                found = true;
                break;
            }
            node = node.parentNode;
        } while (node != null);

        if (found) {
            dropZone.addClass('hover');
        } else {
            dropZone.removeClass('hover');
        }

        window.dropZoneTimeout = setTimeout(function () {
            window.dropZoneTimeout = null;
            dropZone.removeClass('in hover');
        }, 100);
    });
}

function passwordStrength(password) {
    var desc = new Array();
    desc[0] = Rcl.local.pass0;
    desc[1] = Rcl.local.pass1;
    desc[2] = Rcl.local.pass2;
    desc[3] = Rcl.local.pass3;
    desc[4] = Rcl.local.pass4;
    desc[5] = Rcl.local.pass5;
    var score = 0;
    if (password.length > 6)
        score++;
    if ((password.match(/[a-z]/)) && (password.match(/[A-Z]/)))
        score++;
    if (password.match(/\d+/))
        score++;
    if (password.match(/.[!,@,#,$,%,^,&,*,?,_,~,-,(,)]/))
        score++;
    if (password.length > 12)
        score++;
    document.getElementById("passwordDescription").innerHTML = desc[score];
    document.getElementById("passwordStrength").className = "strength" + score;
}

function rcl_manage_user_black_list(e, user_id, confirmText) {

    var class_i = jQuery(e).children('i').attr('class');

    if (class_i == 'rcli fa-refresh fa-spin')
        return false;

    if (!confirm(confirmText))
        return false;

    jQuery(e).children('i').attr('class', 'rcli fa-refresh fa-spin');

    rcl_ajax({
        data: {
            action: 'rcl_manage_user_black_list',
            user_id: user_id
        },
        success: function (data) {

            jQuery(e).children('i').attr('class', class_i);

            if (data['label']) {
                jQuery(e).find('span').text(data['label']);
            }

        }
    });

    return false;
}

function rcl_show_tab(id_block) {
    jQuery(".rcl-tab-button .recall-button").removeClass("active");
    jQuery("#lk-content .recall_content_block").removeClass("active");
    jQuery('#tab-button-' + id_block).children('.recall-button').addClass("active");
    jQuery('#lk-content .' + id_block + '_block').addClass("active");
    return false;
}

rcl_add_action('rcl_init', 'rcl_init_recallbar_hover');

function rcl_init_recallbar_hover() {
    jQuery("#recallbar .menu-item-has-children").on('hover', function () {
        jQuery(this).children(".sub-menu").css({
            'visibility': 'visible'
        });
    }, function () {
        jQuery(this).children(".sub-menu").css({
            'visibility': ''
        });
    });
}

rcl_add_action('rcl_before_upload_tab', 'rcl_add_class_upload_tab');

function rcl_add_class_upload_tab(e) {
    e.addClass('tab-upload');
}

rcl_add_action('rcl_before_upload_tab', 'rcl_add_preloader_tab');

function rcl_add_preloader_tab(e) {
    rcl_preloader_show('#lk-content > div');
    rcl_preloader_show('#ssi-modalContent > div');
}

rcl_add_action('rcl_init', 'rcl_init_get_smilies');

function rcl_init_get_smilies() {
    jQuery(document).on({
            mouseenter: function () {
                var sm_box = jQuery(this).next();
                var block = sm_box.children();
                sm_box.show();
                if (block.html())
                    return false;
                block.html(Rcl.local.loading + '...');
                var dir = jQuery(this).data('dir');

                rcl_ajax({
                    data: {
                        action: 'rcl_get_smiles_ajax',
                        area: jQuery(this).parent().data('area'),
                        dir: dir ? dir : 0
                    },
                    success: function (data) {
                        if (data['content']) {
                            block.html(data['content']);
                        }
                    }
                });

            },
            mouseleave: function () {
                jQuery(this).next().hide();
            }
        },
        "body .rcl-smiles .fa-smile-o");
}

rcl_add_action('rcl_init', 'rcl_init_hover_smilies');

function rcl_init_hover_smilies() {

    jQuery(document).on({
            mouseenter: function () {
                jQuery(this).show();
            },
            mouseleave: function () {
                jQuery(this).hide();
            }
        },
        "body .rcl-smiles > .rcl-smiles-list");

    jQuery('body').on('hover click', '.rcl-smiles > img', function () {
        var block = jQuery(this).next().children();
        if (block.html())
            return false;
        block.html(Rcl.local.loading + '...');
        var dir = jQuery(this).data('dir');

        rcl_ajax({
            data: {
                action: 'rcl_get_smiles_ajax',
                area: jQuery(this).parent().data('area'),
                dir: dir ? dir : 0
            },
            success: function (data) {
                if (data['content']) {
                    block.html(data['content']);
                }
            }
        });

        return false;
    });
}

rcl_add_action('rcl_init', 'rcl_init_click_smilies');

function rcl_init_click_smilies() {
    jQuery("body").on("click", '.rcl-smiles-list img', function () {
        var alt = jQuery(this).attr("alt");
        var area = jQuery(this).parents(".rcl-smiles").data("area");
        jQuery("#" + area).val(jQuery("#" + area).val() + " " + alt + " ");
    });
}

rcl_add_action('rcl_init', 'rcl_init_close_popup');

function rcl_init_close_popup() {
    jQuery('#rcl-popup,.floatform').on('click', '.close-popup', function () {
        rcl_hide_float_login_form();
        jQuery('#rcl-overlay').fadeOut();
        jQuery('#rcl-popup').empty();
        return false;
    });
}

rcl_add_action('rcl_init', 'rcl_init_click_overlay');

function rcl_init_click_overlay() {
    jQuery('#rcl-overlay').on('click', function () {
        rcl_hide_float_login_form();
        jQuery('#rcl-overlay').fadeOut();
        jQuery('#rcl-popup').empty();
        return false;
    });
}

rcl_add_action('rcl_init', 'rcl_init_click_float_window');

function rcl_init_click_float_window() {
    jQuery(".float-window-recall").on('click', '.close', function () {
        jQuery(".float-window-recall").remove();
        return false;
    });
}

rcl_add_action('rcl_init', 'rcl_init_loginform_shift_tabs');

function rcl_init_loginform_shift_tabs() {
    jQuery('body').on('click', '.form-tab-rcl .link-tab-rcl', function () {
        jQuery('.form-tab-rcl').hide();

        if (jQuery(this).hasClass('link-login-rcl'))
            rcl_show_login_form_tab('login');

        if (jQuery(this).hasClass('link-register-rcl'))
            rcl_show_login_form_tab('register');

        if (jQuery(this).hasClass('link-remember-rcl'))
            rcl_show_login_form_tab('remember');

        return false;
    });
}

rcl_add_action('rcl_init', 'rcl_init_check_url_params');

function rcl_init_check_url_params() {

    var options = rcl_get_options_url_params();

    if (rcl_url_params['tab']) {

        if (!jQuery("#lk-content").length)
            return false;

        if (options.scroll == 1) {
            var offsetTop = jQuery("#lk-content").offset().top;
            jQuery('body,html').animate({
                    scrollTop: offsetTop - options.offset
                },
                1000);
        }

        var id_block = rcl_url_params['tab'];
        rcl_show_tab(id_block);
    }

}

rcl_add_action('rcl_init', 'rcl_init_close_notice');

function rcl_init_close_notice() {
    jQuery('#rcl-notice,body').on('click', 'a.close-notice', function () {
        rcl_close_notice(jQuery(this).parent());
        return false;
    });
}

rcl_add_action('rcl_init', 'rcl_init_cookie');

rcl_add_action('rcl_login_form', 'rcl_init_login_form');

function rcl_init_login_form(type_form) {

    if (rcl_url_params['action-rcl']) {
        jQuery('.panel_lk_recall.floatform > div').hide();
    }

    if (type_form == 'floatform') {

        jQuery("body").on('click', '.rcl-register', function () {
            rcl_show_float_login_form();
            rcl_show_login_form_tab('register', type_form);
            return false;
        });

        jQuery("body").on('click', '.rcl-login', function () {
            rcl_show_float_login_form();
            rcl_show_login_form_tab('login', type_form);
            return false;
        });

        if (rcl_url_params['action-rcl']) {
            rcl_show_float_login_form();
        }

    } else {

        if (rcl_url_params['action-rcl'] === 'login') {
            jQuery('.rcl-loginform-full.' + type_form + ' #register-form-rcl').hide();
        }

        if (rcl_url_params['action-rcl'] === 'register') {
            jQuery('.rcl-loginform-full.' + type_form + ' #login-form-rcl').hide();
        }

        if (rcl_url_params['action-rcl'] === 'remember') {
            jQuery('.rcl-loginform.' + type_form + ' #login-form-rcl').hide();
        }

    }

    if (rcl_url_params['action-rcl']) {
        rcl_show_login_form_tab(rcl_url_params['action-rcl'], type_form);
    }

}

function rcl_show_login_form_tab(tab, type_form) {
    type_form = (!type_form) ? '' : '.' + type_form;
    jQuery('.panel_lk_recall' + type_form + ' #' + tab + '-form-rcl').show();
}

function rcl_show_float_login_form() {
    jQuery('.panel_lk_recall.floatform > div').hide();
    rcl_setup_position_float_form();
    jQuery('.panel_lk_recall.floatform').show();
}

function rcl_hide_float_login_form() {
    jQuery('.panel_lk_recall.floatform').fadeOut().children('.form-tab-rcl').hide();
}

function rcl_setup_position_float_form() {
    jQuery("#rcl-overlay").fadeIn();
    var screen_top = jQuery(window).scrollTop();
    var popup_h = jQuery('.panel_lk_recall.floatform').height();
    var window_h = jQuery(window).height();
    screen_top = screen_top + 60;
    jQuery('.panel_lk_recall.floatform').css('top', screen_top + 'px');
}

rcl_add_action('rcl_footer', 'rcl_beat');

function rcl_beat() {

    var beats = rcl_apply_filters('rcl_beats', rcl_beats);

    var DataBeat = rcl_get_actual_beats_data(beats);

    if (rcl_beats_delay && DataBeat.length) {

        rcl_do_action('rcl_beat');

        rcl_ajax({
            data: {
                action: 'rcl_beat',
                databeat: JSON.stringify(DataBeat)
            },
            success: function (data) {

                data.forEach(function (result, i, data) {

                    rcl_do_action('rcl_beat_success_' + result['beat_name']);

                    new (window[result['success']])(result['result']);

                });

            }
        });

    }

    rcl_beats_delay++;

    setTimeout('rcl_beat()', 1000);
}

function rcl_get_actual_beats_data(beats) {

    var beats_actual = new Array();

    if (beats) {

        beats.forEach(function (beat, i, beats) {
            var rest = rcl_beats_delay % beat.delay;
            if (rest == 0) {

                var object = new (window[beat.beat_name])(beat.data);

                if (object.data) {

                    object = rcl_apply_filters('rcl_beat_' + beat.beat_name, object);

                    object.beat_name = beat.beat_name;

                    var k = beats_actual.length;
                    beats_actual[k] = object;
                }
            }
        });

    }

    return beats_actual;

}
