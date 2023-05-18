var file_uploader_fields = {};

jQuery(document).ready(function ($) {
    "use strict";
    /**
     * @type object
     */
    var translation_strings = fpsml_js_obj.translation_strings;
    function initialize_uploaders() {
        $('.fpsml-file-uploader').each(function () {
            var form_alias = $(this).closest('form').data('alias');
            var selector = $(this);
            var attr_element_id = $(this).attr('id');
            var arr_element_id = attr_element_id.split('-');
            var uploader_name = arr_element_id[2];
            var extensions = $(this).data('extensions');
            var extensions_error = $(this).data('extensions-error-message');
            var extensions_array = extensions.split('|');
            var sizeLimit = $(this).data('file-size-limit');
            var sizeLimit_error = $(this).data('upload-filesize-error-message');
            sizeLimit = parseInt(sizeLimit) * 1000 * 1000;
            var multiple_upload = $(this).data('multiple');
            var limit_flag = 0;
            var upload_limit = $(this).data('multiple-upload-limit');
            var uploader_label = $(this).data('upload-label');
            var upload_limit_message = $(this).data('multiple-upload-error-message');
            var field_name = $(this).data('field-name');
            file_uploader_fields[uploader_name] = new qq.FileUploader({
                element: document.getElementById(attr_element_id),
                action: fpsml_js_obj.ajax_url,
                params: {
                    action: 'fpsml_file_upload_action',
                    _wpnonce: fpsml_js_obj.ajax_nonce,
                    form_alias: form_alias,
                    field_name: field_name
                },
                allowedExtensions: extensions_array,
                sizeLimit: sizeLimit,
                minSizeLimit: 50,
                uploadButtonText: $(this).data('label'),
                onSubmit: function (id, fileName) {
                    selector.closest('.fpsml-field-wrap').find('.fpsml-error').html('');
                    if (multiple_upload == true && upload_limit != -1) {
                        var upload_count = selector.parent().find('.fpsml-upload-count').val();
                        var current_upload_count = upload_count;
                        upload_count++;
                        selector.closest('.fpsml-field').find('.fpsml-upload-count').val(upload_count);
                        if (upload_count > upload_limit) {
                            upload_limit_message = (upload_limit_message) ? upload_limit_message : 'Maximum number of files allowed is ' + upload_limit;
                            selector.closest('.fpsml-field-wrap').find('.fpsml-error').html(upload_limit_message);
                            selector.closest('.fpsml-field').find('.fpsml-upload-count').val(current_upload_count);
                            return false;
                        }
                    } else {
                        // Just to delete the file that has been already uploaded
                        if (selector.closest('.fpsml-field').find('.fpsml-media-delete-button').length > 0) {
                            selector.closest('.fpsml-field').find('.fpsml-media-delete-button').click();
                        }
                    }
                },
                onProgress: function (id, fileName, loaded, total) {},
                onComplete: function (id, fileName, responseJSON) {

                    if (responseJSON.success) {
                        if (selector.hasClass('fpsml-custom-media-upload-button')) {
                            switch (responseJSON.media_type) {
                                case 'image':
                                    var insert_content_html = '<img class="alignnone size-full wp-image-' + responseJSON.media_id + '" src="' + responseJSON.media_full_url + '" alt="' + responseJSON.media_name + '"/>';
                                    break;
                                case 'video':
                                    var insert_content_html = '[video width="100%" height="500" ' + responseJSON.media_extension + '="' + responseJSON.media_full_url + '"][/video]';
                                    break;
                                case 'audio':
                                    var insert_content_html = '[audio ' + responseJSON.media_extension + '="' + responseJSON.media_full_url + '"][/audio]';
                                    break;
                                case 'others':
                                    var insert_content_html = '<a href="' + responseJSON.media_full_url + '">' + responseJSON.media_name + '</a>';
                                    break;
                            }
                            var tinymce_id = selector.data('tinymce-id');
                            // tinyMCE.get('fpsml_login_require_form').triggerSave();
                            tinyMCE.get(tinymce_id).execCommand('mceInsertContent', false, insert_content_html);
                        } else {
                            var data = {media_url: responseJSON.media_url, media_id: responseJSON.media_id, media_name: responseJSON.media_name, media_key: responseJSON.media_key}

                            var file_preview_template = wp.template('upload-preview');
                            if (multiple_upload) {
                                var media_id = selector.closest('.fpsml-field').find('.fpsml-media-id').val();
                                if (media_id != '') {
                                    var media_id_array = media_id.split(',');
                                    media_id_array.push(responseJSON.media_id);
                                    var media_id = media_id_array.join(',');
                                } else {
                                    media_id = responseJSON.media_id;
                                }

                                selector.closest('.fpsml-field').find('.fpsml-media-id').val(media_id);
                                selector.closest('.fpsml-field').find('.fpsml-file-preview-wrap').append(file_preview_template(responseJSON));
                            } else {
                                selector.closest('.fpsml-field').find('.fpsml-media-id').val(responseJSON.media_id);
                                selector.closest('.fpsml-field').find('.fpsml-file-preview-wrap').html(file_preview_template(responseJSON));
                            }
                        }

                    } else {

                        console.log(responseJSON);
                    }


                },
                onCancel: function (id, fileName) {},
                onError: function (id, fileName, xhr) {},
                messages: {
                    typeError: (extensions_error == '')?translation_strings.typeError:extensions_error,
                    sizeError: (sizeLimit_error!='')?sizeLimit_error:translation_strings.sizeError,
                    minSizeError: translation_strings.minSizeError,
                    emptyError: translation_strings.emptyError,
                    onLeave: translation_strings.onLeave,
                },
                showMessage: function (message) {
                    alert(message);
                },
                multiple: multiple_upload
            });
        });
    }
    /**
     * Scrolls to the first error of the form
     *
     */
    function fpsml_scroll_to_error(form) {
        form.find('.fpsml-error').each(function () {
            var in_selector = $(this);
            if (in_selector.is(':visible') && in_selector.html() != '') {
                $('html,body').animate({
                    scrollTop: in_selector.closest('.fpsml-field-wrap').offset().top - 100},
                        'slow');
                return false;
            }
            ;
        });

    }

    /**
     * Reset forms
     */
    function fpsml_reset_form(form) {
        form[0].reset();
        form.find('.fpsml-file-preview-wrap').html('');
        form.find('.fpsml-media-id').val('');
        form.find('.fpsml-upload-count').val(0);
        form.find('.fpsml-error').html('').hide();
        if (form.find('#g-recaptcha-response').length > 0) {
            grecaptcha.reset();
        }
    }

    $('body').on('click', '.fpsml-media-delete-button', function () {
        var selector = $(this);
        var media_id = $(this).data('media-id');
        var media_key = $(this).data('media-key');
        var edit = $(this).data('edit');
        if (edit == 'no') {
            $.ajax({
                type: 'post',
                url: fpsml_js_obj.ajax_url,
                data: {
                    _wpnonce: fpsml_js_obj.ajax_nonce,
                    media_id: media_id,
                    media_key: media_key,
                    action: 'fpsml_media_delete_action'
                },
                success: function (res) {
                    res = $.parseJSON(res);
                    if (res.status == 200) {
                        media_id = media_id.toString();
                        var upload_count = selector.closest('.fpsml-field').find('.fpsml-upload-count').val();
                        upload_count--;
                        selector.closest('.fpsml-field').find('.fpsml-upload-count').val(upload_count);
                        var pre_saved_value = selector.closest('.fpsml-field').find('.fpsml-media-id').val();
                        var pre_saved_value_array = pre_saved_value.split(',');
                        if (pre_saved_value_array.length > 1) {
                            console.log(pre_saved_value_array.indexOf(media_id));
                            pre_saved_value_array.splice(pre_saved_value_array.indexOf(media_id), 1);
                            pre_saved_value = pre_saved_value_array.join(',');
                            selector.closest('.fpsml-field').find('.fpsml-media-id').val(pre_saved_value);
                        } else {
                            selector.closest('.fpsml-field').find('.fpsml-media-id').val('');
                        }
                        selector.closest('.fpsml-file-preview-row').remove();
                    } else {
                        alert(res.message);
                    }
                }
            });
        } else {
            media_id = media_id.toString();
            var upload_count = selector.closest('.fpsml-field').find('.fpsml-upload-count').val();
            upload_count--;
            selector.closest('.fpsml-field').find('.fpsml-upload-count').val(upload_count);
            var pre_saved_value = selector.closest('.fpsml-field').find('.fpsml-media-id').val();
            var pre_saved_value_array = pre_saved_value.split(',');
            if (pre_saved_value_array.length > 1) {
                console.log(pre_saved_value_array.indexOf(media_id));
                pre_saved_value_array.splice(pre_saved_value_array.indexOf(media_id), 1);
                pre_saved_value = pre_saved_value_array.join(',');
                selector.closest('.fpsml-field').find('.fpsml-media-id').val(pre_saved_value);
            } else {
                selector.closest('.fpsml-field').find('.fpsml-media-id').val('');
            }
            selector.closest('.fpsml-file-preview-row').remove();
        }
    });

    $('.fpsml-auto-complete-field').each(function () {
        var available_tags = $(this).next('.fpsml-available-tags').val();
        available_tags = available_tags.split(',');
        $(this).autocomplete({
            source: available_tags
        });
    });

    $('body').on('keypress', '.fpsml-auto-complete-field', function (event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == '13') {
            var tag = $(this).val();
            if (tag == '') {
                return;
            }
            var added_tags = $(this).parent().find('.fpsml-auto-complete-values').val();
            if (added_tags == '') {
                added_tags = [];
            } else {
                added_tags = added_tags.split(',');
            }

            if (added_tags.indexOf(tag) == -1) {
                added_tags.push(tag);
                added_tags = added_tags.join(',');
                $(this).parent().find('.fpsml-auto-complete-values').val(added_tags);
                var tag_html = '<div class="fpsml-each-tag"><span class="fpsml-tag-text">' + tag + '</span><span class="fpsml-tag-remove-trigger"><i class="fas fa-times-circle"></i></span></div>';
                $(this).parent().find('.fpsml-auto-complete-tags').append(tag_html);
                $(this).val('');
                $(".ui-autocomplete").hide();
            }
            event.stopPropagation();
            return false;

        }
    });

    $('body').on('click', '.fpsml-tag-remove-trigger', function () {
        var tag = $(this).parent().find('.fpsml-tag-text').html();
        var added_tags = $(this).closest('.fpsml-field').find('.fpsml-auto-complete-values').val();
        added_tags = added_tags.split(',');
        var tag_index = added_tags.indexOf(tag)
        added_tags.splice(tag_index, 1)
        added_tags = added_tags.join(',');
        $(this).closest('.fpsml-field').find('.fpsml-auto-complete-values').val(added_tags);
        $(this).closest('.fpsml-each-tag').remove();

    });

    $('body').on('submit', '.fpsml-front-form', function (e) {
        e.preventDefault();
        var selector = $(this);
        // If auto complete textfield is filled but auto complete is not done
        selector.find('.fpsml-auto-complete-field').each(function () {
            var filled_tag = $(this).val();
            if (filled_tag != '') {
                var auto_complete_value = $(this).closest('.fpsml-field').find('.fpsml-auto-complete-values').val();
                if (auto_complete_value == '') {
                    auto_complete_value = [];
                } else {
                    auto_complete_value = auto_complete_value.split(',');
                }
                if (auto_complete_value.indexOf(filled_tag) == -1) {
                    auto_complete_value.push(filled_tag);
                    auto_complete_value = auto_complete_value.join(',');
                    $(this).parent().find('.fpsml-auto-complete-values').val(auto_complete_value);
                }
            }
        });
        var form_data = selector.serialize();
        $.ajax({
            type: 'post',
            url: fpsml_js_obj.ajax_url,
            data: {
                action: 'fpsml_form_process',
                form_data: form_data,
                _wpnonce: fpsml_js_obj.ajax_nonce
            },
            beforeSend: function (xhr) {
                selector.find('.fpsml-form-message').slideUp();
                selector.find('.fpsml-error').slideUp();
                selector.find('.fpsml-ajax-loader').show();
            },
            success: function (data, textStatus, jqXHR) {
                selector.find('.fpsml-ajax-loader').hide();
                data = $.parseJSON(data);
                if (data.status == 200) {
                    selector.find('.fpsml-form-message').removeClass('fpsml-form-error').addClass('fpsml-form-success').html(data.message).slideDown('slow');
                    if (selector.find('.fpsml-edit-post-id').length == 0) {
                        fpsml_reset_form(selector);
                        if (data.redirect_url) {
                            window.location = data.redirect_url;
                            exit;
                        }
                    } else {
                        if (data.redirect_url) {
                            window.location = data.redirect_url;
                            exit;
                        }
                        if (selector.find('#g-recaptcha-response').length > 0) {
                            grecaptcha.reset();
                        }
                    }
                } else {
                    selector.find('.fpsml-form-message').removeClass('fpsml-form-success').addClass('fpsml-form-error').html(data.message).slideDown('slow', function () {
                        var error_details = data.error_details;
                        for (var field_key in error_details) {
                            if (selector.find('[data-field-key="' + field_key + '"] .fpsml-error').length > 0) {
                                selector.find('[data-field-key="' + field_key + '"] .fpsml-error').html(error_details[field_key]).slideDown('slow');
                            } else {
                                selector.find('[data-field-key="' + field_key + '"]').append('<div class="fpsml-error">' + error_details[field_key] + '</div>');
                            }

                        }
                        if (selector.find('#g-recaptcha-response').length > 0) {
                            grecaptcha.reset();
                        }
                        fpsml_scroll_to_error(selector);
                    });

                }
            }
        });
    });
    initialize_uploaders();

    $('.fpsml-each-term-checkbox label').on('click', function () {
        $(this).toggleClass('checked');
    });

    
    /**
     * Clear error
     */

    $('body').on('keyup', '.fpsml-front-form input[type="text"], .fpsml-front-form textarea', function () {
        $(this).closest('.fpsml-field-wrap').find('.fpsml-error').slideUp('fast');
    });
    $('body').on('click', '.fpsml-front-form input[type="checkbox"], .fpsml-front-form select,.fpsml-front-form input[type="radio"]', function () {

        $(this).closest('.fpsml-field-wrap').find('.fpsml-error').slideUp('fast');
    });

    /*
     * Are you sure js 
     */
    
    if (!fpsml_js_obj.fpsml_settings.disable_are_you_sure_js) {
        $('.fpsml-front-form').areYouSure(
                {
                    message: translation_strings.are_your_sure
                }
        );
    }


});