var rcl_actions = [];
var rcl_filters = [];
var rcl_beats = [];
var rcl_beats_delay = 0;
var rcl_url_params = rcl_get_value_url_params();

jQuery(document).ready(function ($) {

    rcl_init_update_requared_checkbox();

    $.fn.extend({
        insertAtCaret: function (myValue) {
            return this.each(function (i) {
                if (document.selection) {
                    // Для браузеров типа Internet Explorer
                    this.focus();
                    var sel = document.selection.createRange();
                    sel.text = myValue;
                    this.focus();
                } else if (this.selectionStart || this.selectionStart == '0') {
                    // Для браузеров типа Firefox и других Webkit-ов
                    var startPos = this.selectionStart;
                    var endPos = this.selectionEnd;
                    var scrollTop = this.scrollTop;
                    this.value = this.value.substring(0, startPos) + myValue + this.value.substring(endPos, this.value.length);
                    this.focus();
                    this.selectionStart = startPos + myValue.length;
                    this.selectionEnd = startPos + myValue.length;
                    this.scrollTop = scrollTop;
                } else {
                    this.value += myValue;
                    this.focus();
                }
            })
        },
        animateCss: function (animationNameStart, functionEnd) {
            var animationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
            this.addClass('animated ' + animationNameStart).one(animationEnd, function () {
                jQuery(this).removeClass('animated ' + animationNameStart);

                if (functionEnd) {
                    if (typeof functionEnd == 'function') {
                        functionEnd(this);
                    } else {
                        jQuery(this).animateCss(functionEnd);
                    }
                }
            });
            return this;
        }
    });

});

function rcl_do_action(action_name) {

    var callbacks_action = rcl_actions[action_name];

    if (!callbacks_action)
        return false;

    var args = [].slice.call(arguments, 1);

    callbacks_action.forEach(function (callback, i, callbacks_action) {
        if (window[callback])
            window[callback].apply(this, args);
        if (typeof callback === 'function')
            callback.apply(this, args);
    });
}

function rcl_add_action(action_name, callback) {
    if (!rcl_actions[action_name]) {
        rcl_actions[action_name] = [callback];
    } else {
        var i = rcl_actions[action_name].length;
        rcl_actions[action_name][i] = callback;
    }
}

function rcl_apply_filters(filter_name) {

    var args = [].slice.call(arguments, 1);

    var callbacks_filter = rcl_filters[filter_name];

    if (!callbacks_filter)
        return args[0];

    callbacks_filter.forEach(function (callback, i, callbacks_filter) {
        args[0] = window[callback].apply(this, args);
    });

    return args[0];
}

function rcl_add_filter(filter_name, callback) {
    if (!rcl_filters[filter_name]) {
        rcl_filters[filter_name] = [callback];
    } else {
        var i = rcl_filters[filter_name].length;
        rcl_filters[filter_name][i] = callback;
    }
}

function rcl_get_value_url_params() {
    var tmp_1 = new Array();
    var tmp_2 = new Array();
    var rcl_url_params = new Array();
    var get = location.search;
    if (get !== '') {
        tmp_1 = (get.substr(1)).split('&');
        for (var i = 0; i < tmp_1.length; i++) {
            tmp_2 = tmp_1[i].split('=');
            rcl_url_params[tmp_2[0]] = tmp_2[1];
        }
    }

    return rcl_url_params;
}

function rcl_is_valid_url(url) {
    var objRE = /http(s?):\/\/[-\w\.]{3,}\.[A-Za-z]{2,3}/;
    return objRE.test(url);
}

function setAttr_rcl(prmName, val) {
    var res = '';
    var d = location.href.split("#")[0].split("?");
    var base = d[0];
    var query = d[1];
    if (query) {
        var params = query.split("&");
        for (var i = 0; i < params.length; i++) {
            var keyval = params[i].split("=");
            if (keyval[0] !== prmName) {
                res += params[i] + '&';
            }
        }
    }
    res += prmName + '=' + val;
    return base + '?' + res;
}

function rcl_update_history_url(url) {

    if (url != window.location) {
        if (history.pushState) {
            window.history.pushState(null, null, url);
        }
    }

}

function rcl_init_cookie() {

    jQuery.cookie = function (name, value, options) {
        if (typeof value !== 'undefined') {
            options = options || {};
            if (value === null) {
                value = '';
                options.expires = -1;
            }
            var expires = '';
            if (options.expires && (typeof options.expires === 'number' || options.expires.toUTCString)) {
                var date;
                if (typeof options.expires === 'number') {
                    date = new Date();
                    date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
                } else {
                    date = options.expires;
                }
                expires = '; expires=' + date.toUTCString();
            }
            var path = options.path ? '; path=' + (options.path) : '';
            var domain = options.domain ? '; domain=' + (options.domain) : '';
            var secure = options.secure ? '; secure' : '';
            document.cookie = [name, '=', encodeURIComponent(value),
                expires, path,
                domain, secure].join('');
        } else {
            var cookieValue = null;
            if (document.cookie && document.cookie !== '') {
                var cookies = document.cookie.split(';');
                for (var i = 0; i < cookies.length; i++) {
                    var cookie = cookies[i].trim();
                    if (cookie.substring(0, name.length + 1) === (name + '=')) {
                        cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                        break;
                    }
                }
            }
            return cookieValue;
        }
    };

}

function rcl_add_dynamic_field(e) {
    var parent = jQuery(e).parents('.dynamic-value');
    var box = parent.parent('.dynamic-values');
    var html = parent.html();
    box.append('<span class="dynamic-value">' + html + '</span>');
    jQuery(e).attr('onclick', 'rcl_remove_dynamic_field(this);return false;').children('i').toggleClass("fa-plus fa-minus");
    box.children('span').last().children('input').val('').focus();
}

function rcl_remove_dynamic_field(e) {
    jQuery(e).parents('.dynamic-value').remove();
}

function rcl_rand(min, max) {
    if (max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    } else {
        return Math.floor(Math.random() * (min + 1));
    }
}

function rcl_notice(text, type, time_close) {

    time_close = time_close || false;

    var options = {
        text: text,
        type: type,
        time_close: time_close
    };

    options = rcl_apply_filters('rcl_notice_options', options);

    var notice_id = rcl_rand(1, 1000);

    var html = '<div id="notice-' + notice_id + '" class="notice-window type-' + options.type + '"><a href="#" class="close-notice"><i class="rcli fa-times"></i></a>' + options.text + '</div>';
    if (!jQuery('#rcl-notice').length) {
        jQuery('body > div').last().after('<div id="rcl-notice">' + html + '</div>');
    } else {
        if (jQuery('#rcl-notice > div').length)
            jQuery('#rcl-notice > div:last-child').after(html);
        else
            jQuery('#rcl-notice').html(html);
    }

    if (typeof animateCss !== 'undefined') {
        jQuery('#rcl-notice > div').last().animateCss('slideInLeft');
    }

    if (time_close) {
        setTimeout(function () {
            rcl_close_notice('#rcl-notice #notice-' + notice_id)
        }, options.time_close);
    }
}

function rcl_close_notice(e) {

    var timeCook = jQuery(e).data('notice_time');

    if (timeCook) {

        var idCook = jQuery(e).data('notice_id');
        var block = jQuery(e).parents('.rcl-notice');

        if (typeof animateCss !== 'undefined') {
            jQuery(block).animateCss('flipOutX', function () {
                jQuery(block).remove();
            });
        } else {
            jQuery(block).remove();
        }

        jQuery.cookie(idCook, '1', {
            expires: timeCook,
            path: '/'
        });

    } else {

        if (typeof animateCss !== 'undefined') {
            jQuery(e).animateCss('flipOutX', function (e) {
                jQuery(e).hide();
            });
        } else {
            jQuery(e).hide();
        }

    }

    return false;
}

function rcl_preloader_show(e, size) {

    var font_size = (size) ? size : 80;
    var margin = font_size / 2;

    var options = {
        size: font_size,
        margin: margin,
        icon: 'fa-circle-o-notch',
        class: 'rcl_preloader'
    };

    options = rcl_apply_filters('rcl_preloader_options', options);

    var style = 'style="font-size:' + options.size + 'px;margin: -' + options.margin + 'px 0 0 -' + options.margin + 'px;"';

    var html = '<div class="' + options.class + '"><i class="rcli ' + options.icon + ' fa-spin" ' + style + '></i></div>';

    if (typeof (e) === 'string')
        jQuery(e).after(html);
    else
        e.append(html);
}

function rcl_preloader_hide() {
    jQuery('.rcl_preloader').remove();
}

function rcl_setup_datepicker_options() {

    jQuery.datepicker.setDefaults(jQuery.extend(jQuery.datepicker.regional["ru"]));

    var options = {
        monthNames: ["Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
            "Июль",
            "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"],
        dayNamesMin: ["Вс", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
        firstDay: 1,
        dateFormat: 'yy-mm-dd',
        yearRange: "1950:c+3",
        changeYear: true
    };

    options = rcl_apply_filters('rcl_datepicker_options', options);

    return options;

}

function rcl_show_datepicker(e) {
    jQuery(e).datepicker(rcl_setup_datepicker_options());
    jQuery(e).datepicker("show");
    rcl_add_action('rcl_upload_tab', 'rcl_remove_datepicker_box');
}

function rcl_remove_datepicker_box() {
    jQuery('#ui-datepicker-div').remove();
}

function rcl_init_field_file(field_id) {
    jQuery("#" + field_id).parents('form').attr("enctype", "multipart/form-data");
}

function rcl_init_runner(props) {

    var box = jQuery('#rcl-runner-' + props.id);

    box.children('.rcl-runner-box').slider({
        value: parseInt(props.value),
        min: parseInt(props.min),
        max: parseInt(props.max),
        step: parseInt(props.step),
        create: function (event, ui) {
            var value = box.children('.rcl-runner-box').slider('value');
            box.children('.rcl-runner-value').text(value);
            box.children('.rcl-runner-field').val(value);
        },
        slide: function (event, ui) {
            box.find('.rcl-runner-value').text(ui.value);
            box.find('.rcl-runner-field').val(ui.value);
        }
    });
}

function rcl_init_range(props) {

    var box = jQuery('#rcl-range-' + props.id);

    box.children('.rcl-range-box').slider({
        range: true,
        values: [parseInt(props.values[0]), parseInt(props.values[1])],
        min: parseInt(props.min),
        max: parseInt(props.max),
        step: parseInt(props.step),
        create: function (event, ui) {
            var values = box.children('.rcl-range-box').slider('values');
            box.children('.rcl-range-value').text(values[0] + ' - ' + values[1]);
            box.children('.rcl-range-min').val(values[0]);
            box.children('.rcl-range-max').val(values[1]);
        },
        slide: function (event, ui) {
            box.children('.rcl-range-value').text(ui.values[0] + ' - ' + ui.values[1]);
            box.find('.rcl-range-min').val(ui.values[0]);
            box.find('.rcl-range-max').val(ui.values[1]);
        }
    });
}

function rcl_init_color(id, props) {
    jQuery("#" + id).wpColorPicker(props);
}

function rcl_init_field_maxlength(fieldID) {

    var field = jQuery('#' + fieldID);
    var maxlength = field.attr('maxlength');

    if (!field.parent().find('.maxlength').length) {

        if (field.val()) {
            maxlength = maxlength - field.val().length;
        }

        field.after('<span class="maxlength">' + maxlength + '</span>');
    }

    field.on('keyup', function () {
        var maxlength = jQuery(this).attr('maxlength');
        if (!maxlength)
            return false;
        var word = jQuery(this);
        var count = maxlength - word.val().length;
        jQuery(this).next().text(count);
        if (word.val().length > maxlength)
            word.val(word.val().substr(0, maxlength));
    });
}

function rcl_init_ajax_editor(id, options) {

    if (typeof QTags === 'undefined')
        return false;

    rcl_do_action('rcl_pre_init_ajax_editor', {
        id: id,
        options: options
    });

    var qt_options = {
        id: id,
        buttons: (options.qt_buttons) ? options.qt_buttons : "strong,em,link,block,del,ins,img,ul,ol,li,code,more,close"
    };

    QTags(qt_options);

    QTags._buttonsInit();

    if (options.tinymce && typeof tinyMCEPreInit != 'undefined') {

        tinyMCEPreInit.qtInit[id] = qt_options;

        tinyMCEPreInit.mceInit[id] = {
            body_class: id,
            selector: '#' + id,
            menubar: false,
            skin: "lightgray",
            theme: 'modern',
            toolbar1: "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_more,spellchecker,fullscreen,wp_adv",
            toolbar2: "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
            wpautop: true
        };

        tinymce.init(tinyMCEPreInit.mceInit[id]);
        tinyMCE.execCommand('mceAddEditor', true, id);

        switchEditors.go(id, 'html');
    }

}

function rcl_setup_quicktags(newTags) {

    if (typeof QTags === 'undefined')
        return false;

    newTags.forEach(function (tagArray, i, newTags) {

        QTags.addButton(
            tagArray[0],
            tagArray[1],
            tagArray[2],
            tagArray[3],
            tagArray[4],
            tagArray[5],
            tagArray[6]
        );

    });

}

rcl_add_action('rcl_pre_init_ajax_editor', 'rcl_add_ajax_quicktags');

function rcl_add_ajax_quicktags(editor) {

    if (typeof Rcl === 'undefined' || !Rcl.QTags)
        return false;

    rcl_setup_quicktags(Rcl.QTags);

}

rcl_add_action('rcl_footer', 'rcl_add_quicktags');

function rcl_add_quicktags() {

    if (typeof Rcl === 'undefined' || !Rcl.QTags)
        return false;

    rcl_setup_quicktags(Rcl.QTags);

}

function rcl_proccess_ajax_return(result) {

    var methods = {
        redirect: function (url) {

            var urlData = url.split('#');

            if (window.location.origin + window.location.pathname === urlData[0]) {
                location.reload();
            } else {
                location.replace(url);
            }

        },
        reload: function () {
            location.reload();
        },
        current_url: function (url) {
            rcl_update_history_url(url);
        },
        dialog: function (dialog) {

            if (dialog.content) {

                if (jQuery('#ssi-modalContent').length)
                    ssi_modal.close();

                var ssiOptions = {
                    className: 'rcl-dialog-tab ' + (dialog.class ? ' ' + dialog.class : ''),
                    sizeClass: dialog.size ? dialog.size : 'auto',
                    content: dialog.content,
                    buttons: []
                };

                if (dialog.buttons) {
                    ssiOptions.buttons = dialog.buttons;
                }

                var buttonClose = true;

                if ('buttonClose' in dialog) {
                    buttonClose = dialog.buttonClose;
                }

                if (buttonClose) {

                    ssiOptions.buttons.push({
                        label: Rcl.local.close,
                        closeAfter: true
                    });

                }

                if ('onClose' in dialog) {
                    ssiOptions.onClose = function (m) {
                        window[dialog.onClose[0]].apply(this, dialog.onClose[1]);
                    };
                }

                if (dialog.title)
                    ssiOptions.title = dialog.title;

                ssi_modal.show(ssiOptions);

            }

            if (dialog.close) {
                ssi_modal.close();
            }

        }
    };

    for (var method in result) {
        if (methods[method]) {
            methods[method](result[method]);
        }
    }

}

function rcl_ajax(prop) {

    if (prop.data.ask) {
        if (!confirm(prop.data.ask)) {
            rcl_preloader_hide();
            return false;
        }
    }

    if (typeof Rcl != 'undefined') {
        if (typeof prop.data === 'string') {
            prop.data += '&ajax_nonce=' + Rcl.nonce;
        } else if (typeof prop.data === 'object') {
            prop.data.ajax_nonce = Rcl.nonce;
        }
    }

    var action = false;
    if (typeof prop.data === 'string') {
        var propData = prop.data.split('&');
        var propObj = {};
        for (var key in propData) {
            propObj[propData[key].split("=")[0]] = propData[key].split("=")[1];
        }
        action = propObj.action;
    } else if (typeof prop.data === 'object') {
        action = prop.data.action;
    }

    if (typeof tinyMCE != 'undefined')
        tinyMCE.triggerSave();

    jQuery.ajax({
        type: 'POST',
        data: prop.data,
        dataType: 'json',
        url: (typeof ajaxurl !== 'undefined') ? ajaxurl : Rcl.ajaxurl,
        success: function (result, post) {

            var noticeTime = result.notice_time ? result.notice_time : 5000;

            if (!result) {
                rcl_notice(Rcl.local.error, 'error', noticeTime);
                return false;
            }

            if (result.error || result.errors) {

                rcl_preloader_hide();

                if (result.errors) {
                    jQuery.each(result.errors, function (index, error) {
                        rcl_notice(error, 'error', noticeTime);
                    });
                } else {
                    rcl_notice(result.error, 'error', noticeTime);
                }

                if (prop.error)
                    prop.error(result);

                return false;

            }

            if (!result.preloader_live) {
                rcl_preloader_hide();
            }

            if (result.success) {
                rcl_notice(result.success, 'success', noticeTime);
            }

            if (result.warning) {
                rcl_notice(result.warning, 'warning', noticeTime);
            }

            rcl_do_action('rcl_ajax_success', result);

            if (prop.success) {

                prop.success(result);

            } else {

                rcl_proccess_ajax_return(result);

            }

            rcl_do_action(action, result);

        }
    });

}


function rcl_submit_form(e) {

    var form = jQuery(e).parents('form');

    if (rcl_check_form(form))
        form.submit();

}

function rcl_send_form_data(action, e) {

    var form = jQuery(e).parents('form');

    if (!rcl_check_form(form))
        return false;

    if (e && jQuery(e).parents('.preloader-parent')) {
        rcl_preloader_show(jQuery(e).parents('.preloader-parent'));
    }

    if (typeof tinyMCE !== 'undefined')
        tinyMCE.triggerSave();

    rcl_ajax({
        data: form.serialize() + '&action=' + action
    });

}

function rcl_check_form(form) {

    var rclFormFactory = new RclForm(form);

    return rclFormFactory.validate();

}

function rcl_add_beat(beat_name, delay, data) {

    delay = (delay < 10) ? 10 : delay;

    var data = (data) ? data : false;

    var i = rcl_beats.length;

    rcl_beats[i] = {
        beat_name: beat_name,
        delay: delay,
        data: data
    };

}

function rcl_remove_beat(beat_name) {

    if (!rcl_beats)
        return false;

    var remove = false;
    var all_beats = rcl_beats;

    all_beats.forEach(function (beat, index, all_beats) {
        if (beat.beat_name != beat_name)
            return;
        delete rcl_beats[index];
        remove = true;
    });

    return remove;

}

function rcl_exist_beat(beat_name) {

    if (!rcl_beats)
        return false;

    var exist = false;

    rcl_beats.forEach(function (beat, index, rcl_beats) {
        if (beat.beat_name != beat_name)
            return;
        exist = true;
    });

    return exist;

}

function rcl_init_table(table_id) {

    jQuery('#' + table_id).on('click', '.rcl-table__cell-must-sort', function () {

        jQuery('#' + table_id).find('.rcl-table__cell-must-sort, .rcl-table__cell-sort').removeClass('rcl-table__cell-current-sort');

        var sortCell = jQuery(this);

        var sortby = sortCell.data('sort');
        var route = sortCell.attr('data-route');

        sortCell.addClass('rcl-table__cell-current-sort');
        jQuery('#' + table_id).find('[data-' + sortby + '-value]').addClass('rcl-table__cell-current-sort');

        var list = jQuery('#' + table_id + ' .rcl-table__row-must-sort');

        list.sort(function (a, b) {
            var aVal = jQuery(a).find('[data-' + sortby + '-value]').data(sortby + '-value');
            var bVal = jQuery(b).find('[data-' + sortby + '-value]').data(sortby + '-value');

            if (route == 'desc') {
                return (aVal < bVal) - (aVal > bVal); //по возрастанию
            } else {
                return (aVal > bVal) - (aVal < bVal); //по убыванию
            }
        });

        sortCell.attr('data-route', (route == 'desc' ? 'asc' : 'desc'));

        jQuery('#' + table_id + ' .rcl-table__row-must-sort').remove();

        list.each(function (i, e) {
            jQuery('#' + table_id + ' .rcl-table__row-header').after(jQuery(this));
        });

    });

}

function rcl_table_search(e, key, submit) {

    if (submit) {

        if (typeof submit == 'string') {

            return window[submit].call(this, e, key, submit);

        } else if (key == 'Enter') {
            jQuery(e).parents('form').submit();
        }

        return;

    }

    var table_id = jQuery(e).parents('.rcl-table').attr('id');

    var inputs = jQuery(e).parents('.rcl-table').find('.rcl-table__row-search input');

    var search = [];
    inputs.each(function (i, a) {

        if (jQuery(a).val() !== '') {
            search.push([jQuery(a).parent().data('rcl-ttitle'),
                jQuery(a).val()]);
        }

    });

    jQuery('#' + table_id + ' .rcl-table__row-must-sort').show();

    if (!search.length) {
        return;
    }

    var list = jQuery('#' + table_id + ' .rcl-table__row-must-sort');

    list.each(function (i, r) {

        var success = true;

        var cells = jQuery(r).find('.rcl-table__cell');

        cells.each(function (x, c) {

            search.forEach(function (s) {

                if (jQuery(c).data('rcl-ttitle') == s[0]) {

                    var value = jQuery(c).data('value');

                    if (typeof value == 'number' && jQuery(c).data('value') != s[1] ||
                        typeof value == 'string' && value.indexOf(s[1]) < 0) {
                        success = false;
                        return;
                    }

                }

            });

            if (!success) {
                return;
            }

        });

        if (!success) {
            jQuery(r).hide();
        }

    });
}

function RclForm(form) {

    this.form = form;
    this.errors = {};

    this.validate = function () {

        var valid = true;

        for (var objKey in this.checkForm) {

            var chekObject = this.checkForm[objKey];

            if (!chekObject.isValid.call(this)) {

                valid = false;

                break;

            }

        }
        ;

        if (this.errors) {
            for (var k in this.errors) {
                this.showError(this.errors[k]);
            }
            ;
        }

        return valid;

    };

    this.addChekForm = function (id, data) {
        this.checkForm[id] = data;
    };

    this.addChekFields = function (id, data) {
        this.checkFields[id] = data;
    };

    this.addError = function (id, error) {
        this.errors[id] = error;
    };

    this.shake = function (shakeBox) {
        shakeBox.css('box-shadow', 'red 0px 0px 5px 1px inset').animateCss('shake');
    };

    this.noShake = function (shakeBox) {
        shakeBox.css('box-shadow', 'none');
    };

    this.showError = function (error) {
        rcl_notice(error, 'error', 10000);
    };

    this.checkForm = {
        checkFields: {
            isValid: function () {

                var valid = true;
                var parent = this;

                this.form.find('input,select,textarea').each(function () {

                    var field = jQuery(this);
                    var typeField = field.attr('type');

                    if (field.is('textarea')) {
                        typeField = 'textarea';
                    }

                    var checkFields = rcl_apply_filters('rcl_form_check_rules', parent.checkFields, parent);

                    for (var objKey in checkFields) {

                        var chekObject = checkFields[objKey];

                        if (chekObject.types.length && jQuery.inArray(typeField, chekObject.types) < 0) {
                            continue;
                        }

                        var shakeBox = jQuery.inArray(typeField, ['radio',
                            'checkbox']) < 0 ? field : field.next('label');

                        if (!chekObject.isValid(field)) {

                            parent.shake(shakeBox);
                            parent.addError(objKey, chekObject.errorText());
                            valid = false;
                            return;

                        } else {
                            parent.noShake(shakeBox);
                        }

                    }
                    ;

                });

                return valid;

            }

        }

    };

    this.checkFields = {
        required: {
            types: [],
            isValid: function (field) {

                var required = true;

                if (!field.prop("required"))
                    return required;

                if (field.prop("disabled"))
                    return required;

                var value = false;

                if (field.attr('type') == 'checkbox') {
                    if (jQuery('input[name="' + field.attr('name') + '"]:checked').val())
                        value = true;
                } else if (field.attr('type') == 'radio') {
                    if (jQuery('input[name="' + field.attr('name') + '"]:checked').val())
                        value = true;
                } else {
                    if (field.val())
                        value = true;
                }

                if (!value) {
                    required = false;
                }

                return required;

            },
            errorText: function () {
                return Rcl.errors.required;
            }


        },
        numberRange: {
            types: ['number'],
            isValid: function (field) {
                var range = true;

                var val = field.val();

                if (val === '')
                    return true;

                val = parseInt(val);
                var min = parseInt(field.attr('min'));
                var max = parseInt(field.attr('max'));

                if (min != 'undefined' && min > val || max != 'undefined' && max < val) {
                    range = false;
                }

                return range;
            },
            errorText: function () {
                return Rcl.errors.number_range;
            }

        },
        pattern: {
            types: ['text', 'tel'],
            isValid: function (field) {

                var val = field.val();

                if (!val)
                    return true;

                var pattern = field.attr('pattern');

                if (!pattern)
                    return true;

                var re = new RegExp(pattern);

                return re.test(val);
            },
            errorText: function () {
                return Rcl.errors.pattern;
            }

        },
        fileMaxSize: {
            types: ['file'],
            isValid: function (field) {

                var valid = true;

                field.each(function () {

                    var maxsize = jQuery(this).data("size");
                    var fileInput = jQuery(this)[0];
                    var file = fileInput.files[0];

                    if (!file)
                        return;

                    var filesize = file.size / 1024 / 1024;

                    if (filesize > maxsize) {
                        valid = false;
                        return;
                    }

                });

                return valid;
            },
            errorText: function () {
                return Rcl.errors.file_max_size;
            }

        },
        fileAccept: {
            types: ['file'],
            isValid: function (field) {

                var valid = true;

                field.each(function () {

                    var fileInput = jQuery(this)[0];
                    var file = fileInput.files[0];
                    var accept = fileInput.accept.split(',');

                    if (!file)
                        return;

                    if (accept) {

                        var fileType = false;

                        if (file.type) {

                            for (var i in accept) {
                                if (accept[i] == file.type) {
                                    fileType = true;
                                    return;
                                }
                            }

                        }

                        var exts = jQuery(this).data("ext");

                        if (!exts)
                            return;

                        if (!fileType) {

                            var exts = exts.split(',');
                            var filename = file.name;

                            for (var i in exts) {
                                if (filename.indexOf('.' + exts[i]) + 1) {
                                    fileType = true;
                                    return;
                                }
                            }

                        }

                        if (!fileType) {
                            valid = false;
                            return;
                        }

                    }

                });

                return valid;
            },
            errorText: function () {
                return Rcl.errors.file_accept;
            }

        }
    };

    this.send = function (action, success) {

        if (!this.validate())
            return false;

        rcl_preloader_show(form);

        var sendData = {
            data: form.serialize() + '&action=' + action
        };

        if (success) {
            sendData.success = success;
        }

        rcl_ajax(sendData);

    };

}

function rcl_chek_form_field(e) {

    var field = jQuery(e);

    var rclFormFactory = new RclForm(field.parents('form'));

    var result = rclFormFactory.validate({
        check_fields: [field.data('slug')]
    });

    return result;

}

function rcl_init_iconpicker() {
    jQuery('.rcl-iconpicker').iconpicker();
}

/** new uploader scripts **/

var RclUploaders = [];

(function ($) {

    $(document).ready(function () {

        jQuery('body').on('drop', function (e) {
            return false;
        });
        jQuery(document.body).on("drop", function (e) {
            e.preventDefault();
        });

        RclUploaders.init();

    });

})(jQuery);

RclUploaders = new RclClassUploaders();

function RclClassUploaders() {

    this.uploaders = [];

    this.init = function () {

        this.uploaders.forEach(function (uploader, i) {
            uploader.init();
        });

    };

    this.add = function (props, sk) {

        this.uploaders.push(new RclUploader(props, sk));

    };

    this.get = function (uploader_id) {

        var k = false;

        this.uploaders.forEach(function (uploader, i) {

            if (uploader.uploader_id == uploader_id)
                k = i;
        });

        if (k !== false)
            return this.uploaders[k];

    }

    this.isset = function (uploader_id) {

        var k = false;

        this.uploaders.forEach(function (uploader, i) {

            if (uploader.uploader_id == uploader_id)
                k = i;
        });

        if (k !== false)
            return true;

        return false;

    }

}

function RclUploader(props, sk) {

    this.uploader_id = props.uploader_id;
    this.input = jQuery("#rcl-uploader-input-" + this.uploader_id);
    this.button = this.input.parent(".rcl-uploader-button");
    this.options = props;

    this.getFormData = function (uploader) {
        if (!uploader)
            uploader = this;

        var formData = {
            options: JSON.stringify(uploader.options),
            is_wp_admin_page: typeof adminpage ? 1 : 0,
            sk: sk
        };

        formData.action = uploader.options.action;
        formData.ajax_nonce = Rcl.nonce;

        return formData;

    };

    this.init = function () {

        if (this.options.dropzone)
            rcl_init_dropzone(jQuery("#rcl-dropzone-" + this.uploader_id));

        var uploader_id = this.options.uploader_id;
        var uploader = this;

        options = {
            dataType: 'json',
            type: 'POST',
            url: Rcl.ajaxurl,
            dropZone: this.options.dropzone ? jQuery("#rcl-dropzone-" + this.uploader_id) : false,
            formData: this.getFormData(uploader),
            loadImageMaxFileSize: this.options.max_size * 1024,
            autoUpload: this.options.auto_upload,
            singleFileUploads: false,
            /*limitMultiFileUploads: this.options.max_files,*/
            maxNumberOfFiles: this.options.max_files,
            imageMinWidth: this.options.min_width,
            imageMinHeight: this.options.min_height,
            imageMaxWidth: 1920,
            imageMaxHeight: 1080,
            imageCrop: false,
            imageForceResize: false,
            previewCrop: false,
            previewThumbnail: true,
            previewCanvas: true,
            previewMaxWidth: 900,
            previewMaxHeight: 900,
            disableExifThumbnail: true,
            progressall: function (e, data) {
                RclUploaders.get(uploader_id).progressall(e, data);
            },
            processstart: function (e, data) {
                RclUploaders.get(uploader_id).processstart(e, data);
            },
            processdone: function (e, data) {
                RclUploaders.get(uploader_id).processdone(e, data);
            },
            processfail: function (e, data) {
                RclUploaders.get(uploader_id).processfail(e, data);
            },
            add: function (e, data) {
                RclUploaders.get(uploader_id).add(e, data);
            },
            submit: function (e, data) {
                RclUploaders.get(uploader_id).submit(e, data);
            },
            done: function (e, data) {
                RclUploaders.get(uploader_id).done(e, data);
            }
        };

        this.input.fileupload(options);

        /*this.initSortable();*/

        rcl_do_action('rcl_uploader_init', uploader_id);

    };

    this.initSortable = function () {
        jQuery("#rcl-upload-gallery-" + this.uploader_id).sortable({
            //connectWith: "#rcl-upload-gallery-" + this.uploader_id,
            containment: "parent",
            //handle: ".field-control .control-move",
            cursor: "move",
            placeholder: "ui-sortable-placeholder",
            distance: 5
        });
    }

    this.processstart = function (e, data) {
        console.log('processstart');
    };

    this.processdone = function (e, data) {
        console.log('processdone');
    };

    this.processfail = function (e, data) {
        console.log('processfail');
    };

    this.progressall = function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        jQuery('#rcl-uploader-' + this.uploader_id + ' .rcl-uploader-progress').html('<div class="progress-bar" style="width:' + progress + '%;">' + progress + '%</div>');
    };

    this.add = function (e, data) {

        var uploader = this;
        var options = uploader.options;

        var errors = [];

        var inGalleryNow = jQuery('#rcl-upload-gallery-' + uploader.uploader_id + ' .gallery-attachment').length;

        jQuery.each(data.files, function (index, file) {

            inGalleryNow++;

            if (file.size > options.max_size * 1024) {
                errors.push(Rcl.errors.file_max_size + '. Max: ' + options.max_size + 'Kb');
            }

        });

        if (options.multiple && inGalleryNow > options.max_files) {
            errors.push(Rcl.errors.file_max_num + '. Max: ' + options.max_files);
        }

        errors = this.filterErrors(errors, data.files, uploader);

        if (errors.length) {
            errors.forEach(function (error, i) {
                rcl_notice(error, 'error', 10000);
            });
            return false;
        }

        if (parseInt(options.crop) != 0 && parseInt(options.multiple) == 0 && typeof jQuery.Jcrop != 'undefined') {
            if (jQuery.inArray(data.files[0].type, [
                'image/png',
                'image/jpg',
                'image/jpeg',
                'image/gif'
            ]) >= 0) {
                return this.crop(e, data);
            }
        }

        data.process().done(function () {
            data.submit();
        });

    };

    this.filterErrors = function (errors, files, uploader) {
        return errors;
    };

    this.submit = function (e, data) {

        this.animateLoading(true);

        if (this.options.crop) {
            return this.submitCrop(e, data);
        }

    };

    this.done = function (e, data) {

        rcl_preloader_hide();

        this.animateLoading(false);

        jQuery('#rcl-uploader-' + this.uploader_id + ' .rcl-uploader-progress').empty();

        if (data.result.error) {
            rcl_notice(data.result.error, 'error', 10000);
            return false;
        }

        if (data.result.success) {
            rcl_notice(data.result.success, 'success', 10000);
        }

        var uploader = this;

        if (this.options.multiple) {
            jQuery.each(data.result, function (index, file) {

                uploader.appendInGallery(file, uploader);

            });
        } else {

            jQuery('#rcl-upload-gallery-' + this.uploader_id).html('');

            uploader.appendInGallery(data.result, uploader);
        }

        this.afterDone(e, data);

        jQuery('#rcl-preview').remove();

    };

    this.appendInGallery = function (file) {

        if (file.html) {
            jQuery('#rcl-upload-gallery-' + this.uploader_id).append(file.html);
            jQuery('#rcl-gallery-' + this.uploader_id).append(file.html);
            jQuery('#rcl-upload-gallery-' + this.uploader_id + ' .gallery-attachment').last().animateCss('flipInX');
        }
    };

    this.afterDone = function (e, data) {

    };

    this.crop = function (e, data) {

        var uploader = this;
        var crop = uploader.options.crop;
        var minWidthCrop = uploader.options.min_width;
        var minHeightCrop = uploader.options.min_height;

        jQuery.each(data.files, function (index, file) {

            jQuery('#rcl-preview').remove();

            var maxSize = parseInt(uploader.options.max_size);

            if (file.size > maxSize * 1024) {
                rcl_notice(Rcl.errors.file_max_size + '. Max:' + ' ' + maxSize + 'Kb', 'error', 10000);
                return false;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                var jcrop_api;
                var imgUrl = event.target.result;

                var maxWidth = window.innerWidth * 0.9;
                var maxHeight = window.innerHeight * 0.8;

                jQuery('body > div').last().after('<div id=rcl-preview><img style="max-width:' + maxWidth + 'px;max-height:' + maxHeight + 'px;" src="' + imgUrl + '"></div>');

                var image = jQuery('#rcl-preview img');

                image.on('load', function () {

                    var img = jQuery(this);
                    var cf = 1;

                    if (img[0].naturalWidth > img.width()) {
                        cf = img.width() / img[0].naturalWidth;
                    }

                    minWidthCrop *= cf;
                    minHeightCrop *= cf;

                    var height = img.height();
                    var width = img.width();

                    if (height < minHeightCrop || width < minWidthCrop) {
                        rcl_notice(Rcl.errors.file_min_size + '. Min:' + ' ' + minWidthCrop + '*' + minHeightCrop + ' px', 'error', 10000);
                        return false;
                    }

                    var jcrop_api;

                    img.Jcrop({
                            aspectRatio: (typeof crop.ratio != 'undefined') ? crop.ratio : 1,
                            minSize: [minWidthCrop, minHeightCrop],
                            onSelect: function (c) {
                                img.attr('data-width', width).attr('data-height', height).attr('data-x', c.x).attr('data-y', c.y).attr('data-w', c.w).attr('data-h', c.h);
                            }
                        },
                        function () {
                            jcrop_api = this;
                        });

                    ssi_modal.show({
                        sizeClass: 'auto',
                        title: Rcl.local.title_image_upload,
                        className: 'rcl-hand-uploader',
                        buttons: [{
                            className: 'btn-success',
                            label: Rcl.local.upload,
                            closeAfter: true,
                            method: function () {
                                data.submit();
                            }
                        }, {
                            className: 'btn-cancel',
                            label: Rcl.local.cancel,
                            closeAfter: true,
                            method: function () {
                                jcrop_api.destroy();
                            }
                        }],
                        content: jQuery('#rcl-preview'),
                        extendOriginalContent: true
                    });

                });

            };

            reader.readAsDataURL(file);

        });

    };

    this.submitCrop = function (e, data) {

        data.formData = this.getFormData();

        var image = jQuery('#rcl-preview img');

        if (parseInt(image.data('w'))) {

            var width = image.data('width');
            var height = image.data('height');
            var x = image.data('x');
            var y = image.data('y');
            var w = image.data('w');
            var h = image.data('h');

            data.formData.crop_data = [x, y, w, h];
            data.formData.image_size = [width, height];

        }

    }

    this.animateLoading = function (status) {
        if (status)
            this.button.addClass('rcl-bttn__loading');
        else
            this.button.removeClass('rcl-bttn__loading');
    }

}

function rcl_init_uploader(props, securityKey) {
    RclUploaders.add(props, securityKey);
}

function rcl_init_dropzone(dropZone) {

    jQuery(document.body).bind("drop", function (e) {
        var node = e.target, found = false;

        if (dropZone[0]) {
            dropZone.removeClass('in-dropzone hover-dropzone');
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

    dropZone.bind('dragover', function (e) {
        var timeout = window.dropZoneTimeout;

        if (!timeout) {
            dropZone.addClass('in-dropzone');
        } else {
            clearTimeout(timeout);
        }

        var found = false, node = e.target;

        do {
            if (node === dropZone[0]) {
                found = true;
                break;
            }
            node = node.parentNode;
        } while (node != null);

        if (found) {
            dropZone.addClass('hover-dropzone');
        } else {
            dropZone.removeClass('hover-dropzone');
        }

        window.dropZoneTimeout = setTimeout(function () {
            window.dropZoneTimeout = null;
            dropZone.removeClass('in-dropzone hover-dropzone');
        }, 100);
    });
}

function rcl_delete_attachment(attachment_id, post_id, e) {

    if (e)
        rcl_preloader_show(jQuery(e).parents('.gallery-attachment'));

    var objectData = {
        action: 'rcl_ajax_delete_attachment',
        post_id: post_id,
        attach_id: attachment_id
    };

    rcl_ajax({
        rest: true,
        data: objectData,
        success: function (data) {

            jQuery('.gallery-attachment-' + attachment_id).animateCss('flipOutX', function (e) {
                jQuery(e).remove();
            });

        }
    });

    return false;
}

function rcl_add_attachment_in_editor(attach_id, editor_name, e) {

    var image = jQuery(e).data('html');
    var src = jQuery(e).data('src');

    if (src)
        image = '<a href="' + src + '">' + image + '</a>';

    jQuery("textarea[name=" + editor_name + "]").insertAtCaret(image + "&nbsp;");

    if (typeof tinyMCE != 'undefined') {
        tinyMCE.editors.forEach(function (editor) {

            if (editor.targetElm.name.length === editor_name.length) {
                editor.execCommand('mceInsertContent', false, image);
            }
        });
    }

    return false;
}

/** new uploader scripts end **/

function rcl_update_require_checkbox(e) {
    var name = jQuery(e).attr('name');
    var chekval = jQuery('form input[name="' + name + '"]:checked').val();
    if (chekval)
        jQuery('form input[name="' + name + '"]').attr('required', false);
    else
        jQuery('form input[name="' + name + '"]').attr('required', true);
}

function rcl_init_update_requared_checkbox() {

    jQuery('body form').find('.required-checkbox').each(function () {
        rcl_update_require_checkbox(this);
    });

    jQuery('body form').on('click', '.required-checkbox', function () {
        rcl_update_require_checkbox(this);
    });

}