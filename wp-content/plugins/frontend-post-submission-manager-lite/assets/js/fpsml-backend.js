jQuery(document).ready(function ($) {
    "use strict";
    /**
     *
     * @type object
     */
    var notice_timeout;

    /**
     * @type object
     */
    var translation_strings = fpsml_backend_obj.translation_strings;
    /**
     * Generates required notice
     *
     * @param {string} info_text
     * @param {string} info_type
     *
     */
    function fpsml_generate_info(info_text, info_type) {
        clearTimeout(notice_timeout);
        switch (info_type) {
            case 'error':
                var info_html = '<p class="fpsml-error">' + info_text + '</p>';
                break;
            case 'info':
                var info_html = '<p class="fpsml-info">' + info_text + '</p>';
                break;
            case 'ajax':
                var info_html = '<p class="fpsml-ajax"><img src="' + fpsml_backend_obj.plugin_url + '/assets/images/ajax-loader.gif" class="fpsml-ajax-loader"/>' + info_text + '</p>';
            default:
                break;

        }
        $('.fpsml-form-message').html(info_html).show();
        if (info_type != 'ajax') {
            notice_timeout = setTimeout(function () {
                $('.fpsml-form-message').slideUp(1000);
            }, 5000);
        }

    }

    /**
     * Performs clipboard copy action
     * 
     * @param {object} element
     * @returns null
     */
    function fpsml_copyToClipboard(element) {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($(element).text()).select();
        document.execCommand("copy");
        $temp.remove();
    }

    function fpsml_title_to_alias(str) {
        str = str.replace(/^\s+|\s+$/g, ''); // trim
        str = str.toLowerCase();

        // remove accents, swap ñ for n, etc
        var from = "àáäâèéëêìíïîòóöôùúüûñç·/,:;";
        var to = "aaaaeeeeiiiioooouuuunc------";
        for (var i = 0, l = from.length; i < l; i++) {
            str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
        }

        str = str.replace(/[^a-z0-9 _]/g, '') // remove invalid chars
                .replace(/\s+/g, '_') // collapse whitespace and replace by _
                .replace(/_+/g, '_'); // collapse dashes

        return str;
    }

    /**
     * Initialize checkbox as toggle switch
     * 
     * @since 1.0.0
     */

    function initialize_checkbox_toggle() {

        $('.fpsml-field input[type="checkbox"]').each(function () {
            if (!$(this).parent().hasClass('fpsml-checkbox-toggle') && !$(this).hasClass('fpsml-disable-checkbox-toggle')) {
                var input_name = $(this).attr('name');
                $(this).parent().addClass('fpsml-checkbox-toggle');
                $('<label></label>').insertAfter($(this));
            }
        });
    }

    /**
     * 
     * Check if string has white space
     * 
     * @since 1.0.0
     * 
     */
    function fpsml_hasWhiteSpace(s) {
        return s.indexOf(' ') >= 0;
    }

    /**
     * Check if string has special characters
     * 
     * @since 1.0.0
     */
    function fpsml_has_special_characters(string) {
        var format = /[!@#$%^&*()+\-=\[\]{};':"\\|,.<>\/?]+/;

        if (format.test(string)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Initialize checkbox as toggle on page load
     * 
     * @since 1.0.0
     */
    initialize_checkbox_toggle();

    /**
     * Settings section show hide
     * 
     * @since 1.0.0
     */
    $('body').on('click', '.fpsml-nav-item', function () {
        var tab = $(this).data('tab');
        $('.fpsml-nav-item').removeClass('fpsml-active-nav');
        $(this).addClass('fpsml-active-nav');
        $('.fpsml-settings-each-section').hide();
        $('.fpsml-settings-each-section[data-tab="' + tab + '"]').show();

    });

    $('body').on('submit', '.fpsml-edit-form', function (e) {
        e.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            type: 'post',
            url: fpsml_backend_obj.ajax_url,
            data: {
                action: 'fpsml_form_edit_action',
                _wpnonce: fpsml_backend_obj.ajax_nonce,
                form_data: form_data
            },
            beforeSend: function (xhr) {
                fpsml_generate_info(translation_strings.ajax_message, 'ajax');
            },
            success: function (res) {
                res = $.parseJSON(res);
                if (res.status == 200) {
                    fpsml_generate_info(res.message, 'info');
                    if (res.redirect_url) {
                        window.location = res.redirect_url;
                        exit;
                    }
                } else {
                    fpsml_generate_info(res.message, 'error');
                }
            }
        });

    });

    /**
     * Global Settings save
     * 
     * @since 1.0.0
     */
    $('body').on('submit', '.fpsml-settings-form', function (e) {
        e.preventDefault();
        var form_data = $(this).serialize();
        $.ajax({
            type: 'post',
            url: fpsml_backend_obj.ajax_url,
            data: {
                action: 'fpsml_settings_save_action',
                _wpnonce: fpsml_backend_obj.ajax_nonce,
                form_data: form_data
            },
            beforeSend: function (xhr) {
                fpsml_generate_info(translation_strings.ajax_message, 'ajax');
            },
            success: function (res) {
                res = $.parseJSON(res);
                if (res.status == 200) {
                    fpsml_generate_info(res.message, 'info');

                } else {
                    fpsml_generate_info(res.message, 'error');
                }
            }
        });

    });

    /**
     * Shortcode clipboard copy
     * 
     * @since 1.0.0
     */
    $('body').on('click', '.fpsml-clipboard-copy', function () {
        var copy_element = $(this).parent().find('.fpsml-shortcode-preview').select();
        fpsml_copyToClipboard(copy_element);
        fpsml_generate_info(translation_strings.clipboad_copy_message, 'info');
    });



    /**
     * Show hide toggle for Select and Radio
     * 
     * @since 1.0.0
     */
    $('body').on('change', '.fpsml-toggle-trigger', function () {

        var toggle_ref = $(this).val();
        var toggle_class = $(this).data('toggle-class');
        $('.' + toggle_class).hide();
        $('.' + toggle_class + '[data-toggle-ref="' + toggle_ref + '"]').show();

    });

    $('body').on('click', '.fpsml-checkbox-toggle-trigger', function () {
        var toggle_class = $(this).data('toggle-class');
        var toggle_type = ($(this).data('toggle-type')) ? $(this).data('toggle-type') : 'on';
        switch (toggle_type) {
            case 'on':
                if ($(this).is(':checked')) {
                    $('.' + toggle_class).show();
                    $('.' + toggle_class).removeClass('fpsml-display-none');

                } else {
                    $('.' + toggle_class).hide();
                    $('.' + toggle_class).addClass('fpsml-display-none');
                }
                break;
            case 'off':
                if ($(this).is(':checked')) {
                    $('.' + toggle_class).addClass('fpsml-display-none');
                    $('.' + toggle_class).hide();
                } else {
                    $('.' + toggle_class).removeClass('fpsml-display-none');
                    $('.' + toggle_class).show();

                }
                break;
        }

    });

    $('body').on('click', '.fpsml-field-title', function () {
        $(this).closest('.fpsml-each-form-field').find('.fpsml-field-body').slideToggle(500);
        if ($(this).find('span.dashicons').hasClass('dashicons-arrow-up')) {
            $(this).find('span.dashicons').removeClass('dashicons-arrow-up').addClass('dashicons-arrow-down');
        } else {
            $(this).find('span.dashicons').removeClass('dashicons-arrow-down').addClass('dashicons-arrow-up');
        }
    });

    $('body').on('click', '.fpsml-form-save', function () {
        var form = $(this).data('form');
        $('.' + form).submit();
    });

    
    $('.fpsml-sortable').sortable({
        placeholder: "fpsml-sortable-placeholder",
        forcePlaceholderSize: true,
        handle: '.fpsml-field-head'
    });
    $('.fpsml-dropdown-list-wrap').sortable({
        placeholder: "fpsml-sortable-placeholder",
        forcePlaceholderSize: true
    });

    /**
     * Custom field adder
     * 
     * @since 1.0.0
     */
    $('body').on('click', '.fpsml-custom-field-add-trigger', function () {
        var custom_field_label = $('#fpsml-custom-field-label').val();
        var custom_field_meta_key = $('#fpsml-custom-field-meta-key').val();
        var custom_field_key = '_custom_field|' + custom_field_meta_key;
        if (custom_field_label == '' || custom_field_meta_key == '') {
            fpsml_generate_info(translation_strings.custom_field_error, 'error');
        } else {
            if (fpsml_hasWhiteSpace(custom_field_meta_key) || fpsml_has_special_characters(custom_field_meta_key)) {
                fpsml_generate_info(translation_strings.custom_field_space_error, 'error');
                return;
            }
            if ($('.fpsml-show-fields-ref-' + custom_field_meta_key).length > 0) {
                fpsml_generate_info(translation_strings.custom_field_key_available_error, 'error');
                return;
            }
            var field_type = $('#fpsml-custom-field-type').val();
            var data = {label: custom_field_label, field_key: custom_field_key, meta_key: custom_field_meta_key, field_type: field_type};
            var field_template = wp.template('custom-' + field_type);
            $('.fpsml-form-fields-wrap > .fpsml-form-fields-list > .fpsml-sortable').append(field_template(data));
            initialize_checkbox_toggle();
            $('#fpsml-custom-field-label').val('');
            $('#fpsml-custom-field-meta-key').val('');
            $('body,html').animate({
                scrollTop: $('.fpsml-form-fields-list .fpsml-each-form-field').last().offset().top + 100
            }, 'slow');
            $('.fpsml-form-fields-list .fpsml-each-form-field h3.fpsml-field-title').last().click();
            $('.fpsml-sortable').sortable({
                placeholder: "fpsml-sortable-placeholder",
                forcePlaceholderSize: true
            });
            $('.fpsml-dropdown-list-wrap').sortable({
                placeholder: "fpsml-sortable-placeholder",
                forcePlaceholderSize: true
            });

        }


    });

    /**
     * Custom field remover
     * 
     * @since 1.0.0
     */
    $('body').on('click', '.fpsml-field-remove-trigger', function () {
        if (confirm(translation_strings.custom_field_delete_confirm)) {
            $(this).closest('.fpsml-each-form-field').remove();

        }
    });

    
    /**
     * Radio button checked trigger
     * 
     * @since 1.0.0
     */
    $('body').on('click', '.fpsml-checked-radio-ref', function () {
        $(this).closest('.fpsml-dropdown-list-wrap').find('.fpsml-checked-radio-val').val(0);
        $(this).next('input[type="hidden"]').val(1);
    });

    /**
     * Editor change options toggle
     * 
     * @since 1.0.0
     */

    $('body').on('change', '.fpsml-editor-type', function () {
        var media_editors = ['visual', 'rich'];
        var editor_type = $(this).val();
        if (media_editors.indexOf(editor_type) != -1) {
            $(this).closest('.fpsml-each-form-field').find('.fpsml-editor-type-ref').show();
        } else {
            $(this).closest('.fpsml-each-form-field').find('.fpsml-editor-type-ref').hide();
        }
    });

    
    $('body').on('click', '.fpsml-custom-field-type-trigger-btn', function () {
        var field_type = $(this).data('field-type');
        $('#fpsml-custom-field-type option').removeAttr('selected');
        $('#fpsml-custom-field-type option[value="' + field_type + '"]').attr('selected', 'selected');
        $('.fpsml-custom-field-type-trigger-btn').removeClass('btn-selected');
        $(this).addClass('btn-selected');
    });
    $('.fpsml-edit-form').areYouSure(
            {
                message: translation_strings.are_your_sure
            }
    );

    $('body').on('change', '.fpsml-form-template', function () {
        var template = $(this).val();
        $('.fpsml-form-template-preview-img').hide();
        $('.fpsml-form-template-preview-img[data-template-id="' + template + '"]').show();
        if (template == 'template-7' || template == 'template-12' || template == 'template-18' || template == 'template-22') {
            $('.fpsml-label-background-ref').show();
        } else {
            $('.fpsml-label-background-ref').hide();

        }
    });
    $('body').on('change', '.fpsml-custom-field-template-trigger', function () {
        var template = $(this).val();
        $('.fpsml-post-template-preview-img').hide();
        $('.fpsml-post-template-preview-img[data-template-id="' + template + '"]').show();

    });

    /**
     * Open Media Uploader
     */
    $('body').on('click', '.fpsml-media-uploader', function () {

        var selector = $(this);

        var image = wp.media({
            title: 'Upload Image',
            // mutiple: true if you want to upload multiple files at once
            multiple: false
        }).open()
                .on('select', function (e) {
                    // This will return the selected image from the Media Uploader, the result is an object
                    var uploaded_image = image.state().get('selection').first();
                    // We convert uploaded_image to a JSON object to make accessing it easier
                    // Output to the console uploaded_image
                    // console.log(uploaded_image);
                    var image_url = uploaded_image.toJSON().url;
                    var image_id = uploaded_image.toJSON().id;
                    // Let's assign the url value to the input field
                    selector.parent().find('input[type="text"]').val(image_url);
                    selector.parent().find('input[type="hidden"]').val(image_id);
                    selector.parent().find('.fpsml-media-preview').html('<img src="' + uploaded_image.toJSON().sizes.thumbnail.url + '"/>');
                });
    });

    $('body').on('keyup', 'input[name="form_title"]', function () {
        var form_title = $(this).val();
        var form_alias = fpsml_title_to_alias(form_title);
        if ($('input[name="form_alias"]').attr('readonly') != 'readonly') {
            $('input[name="form_alias"]').val(form_alias);

        }
    });

    $('body').on('click', '.fpsml-alias-force-edit', function () {
        $(this).parent().find('input[type="text"]').removeAttr('readonly');
    });



});