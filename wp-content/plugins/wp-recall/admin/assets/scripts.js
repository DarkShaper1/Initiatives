var RclFields = {};

jQuery(function ($) {

    rcl_init_cookie();

    if (rcl_url_params['rcl-addon-options']) {
        $('.wrap-recall-options').hide();
        $('#recall .title-option').removeClass('active');
        $('#options-' + rcl_url_params['rcl-addon-options']).show();
        $('#title-' + rcl_url_params['rcl-addon-options']).addClass('active');
    }

    /**/
    $(".wrap-recall-options").find(".parent-option").each(function () {
        $(this).find("input,select").each(function () {
            var id = $(this).attr('id');
            var val = $(this).val();
            $('.' + id + '-' + val).show();
        });
    });

    $('.parent-option select, .parent-option input').change(function () {
        var id = $(this).attr('id');
        $('.parent-' + id).hide();
        $('.' + id + '-' + $(this).val()).show();
    });
    /**/

    $("#recall").find(".parent-select").each(function () {
        var id = $(this).attr('id');
        var val = $(this).val();
        $('.child-select.' + id + '-' + val).show();
    });

    $('.wrap-recall-options .parent-select').change(function () {
        var id = $(this).attr('id');
        var val = $(this).val();
        $('.wrap-recall-options .child-select.' + id).slideUp();
        $('.wrap-recall-options .child-select.' + id + '-' + val).slideDown();
    });

    $('#rcl-custom-fields-editor').on('change', '.select-type-field', function () {
        rcl_get_custom_field_options(this);
    });

    $('#rcl-custom-fields-editor').on('click', '.field-delete', function () {
        var field = $(this).parents('.rcl-custom-field');

        if (field.hasClass('must-meta-delete')) {

            if (confirm($('#field-delete-confirm').text())) {
                var itemID = field.data('slug');
                var val = $('#rcl-deleted-fields').val();
                if (val)
                    itemID += ',';
                itemID += val;
                $('#rcl-deleted-fields').val(itemID);
            }

        }

        field.remove();

        return false;
    });

    $('.rcl-custom-fields-box').on('click', '.field-edit', function () {
        $(this).parents('.field-header').next('.field-settings').slideToggle();
        return false;
    });

    $('#recall').on('click', '.title-option', function () {

        if ($(this).hasClass('active'))
            return false;

        var titleSpan = $(this);

        var addonId = titleSpan.data('addon');
        var url = titleSpan.data('url');

        rcl_update_history_url(url);

        $('.wrap-recall-options').hide();
        $('#recall .title-option').removeClass('active');
        titleSpan.addClass('active');
        titleSpan.next('.wrap-recall-options').show();
        return false;
    });

    $('body').on('click', '.update-message .update-add-on', function () {
        if ($(this).hasClass("updating-message"))
            return false;
        var addon = $(this).data('addon');
        $('#' + addon + '-update .update-message').addClass('updating-message');
        var dataString = 'action=rcl_update_addon&addon=' + addon;
        $.ajax({
            type: 'POST',
            data: dataString,
            dataType: 'json',
            url: ajaxurl,
            success: function (data) {
                if (data.addon_id == addon) {
                    $('#' + addon + '-update .update-message').toggleClass('updating-message updated-message').html(data.success);
                }
                if (data.error) {

                    $('#' + addon + '-update .update-message').removeClass('updating-message');

                    var ssiOptions = {
                        className: 'rcl-dialog-tab rcl-update-error',
                        sizeClass: 'auto',
                        title: Rcl.local.error,
                        buttons: [{
                            label: Rcl.local.close,
                            closeAfter: true
                        }],
                        content: data.error
                    };

                    ssi_modal.show(ssiOptions);

                }
            }
        });
        return false;
    });

    $('#rcl-notice,body').on('click', 'a.close-notice', function () {
        rcl_close_notice(jQuery(this).parent());
        return false;
    });

    jQuery('body').on('click', '#rcl-addon-details .sections-menu .no-active-section', function () {
        var li = jQuery(this);

        li.parent().find('.active-section').each(function () {
            var tab = jQuery(this).data('tab');
            jQuery(this).removeClass('active-section');
            jQuery(this).addClass('no-active-section');

            var box = jQuery('#rcl-addon-details .section-content [data-box="' + tab + '"]');

            box.removeClass('active-box');
            box.addClass('no-active-box');
        });

        var tab = li.data('tab');

        li.removeClass('no-active-section');
        li.addClass('active-section');

        var box = jQuery('#rcl-addon-details .section-content [data-box="' + tab + '"]');

        box.removeClass('no-active-box');
        box.addClass('active-box');

        return false;

    });

    /* показ дочерних полей */
    $(".rcl-parent-field:not(.rcl-children-field)").find("input, select").each(function () {
        RclOptionsControl.showChildrens(RclOptionsControl.getId(this), $(this).val());
    });

    $('.rcl-parent-field select, .rcl-parent-field input').change(function () {
        RclOptionsControl.hideChildrens(RclOptionsControl.getId(this));
        RclOptionsControl.showChildrens(RclOptionsControl.getId(this), $(this).val());
    });
    /***/

});

var RclOptionsControl = {
    getId: function (e) {
        return jQuery(e).attr('type') == 'radio' && jQuery(e).is(":checked") ? jQuery(e).data('slug') : jQuery(e).attr('id');
    },
    showChildrens: function (parentId, parentValue) {

        var childrenBox = jQuery('[data-parent="' + parentId + '"][data-parent-value="' + parentValue + '"]');

        if (!childrenBox.length)
            return false;

        childrenBox.show();

        if (childrenBox.hasClass('rcl-parent-field')) {

            childrenBox.find("input, select").each(function () {

                RclOptionsControl.showChildrens(RclOptionsControl.getId(this), jQuery(this).val());

            });
        }

    },
    hideChildrens: function (parentId) {

        var childrenBox = jQuery('[data-parent="' + parentId + '"]');

        childrenBox.hide();

        if (childrenBox.hasClass('rcl-parent-field')) {

            childrenBox.find("input, select").each(function () {

                RclOptionsControl.hideChildrens(RclOptionsControl.getId(this));

            });
        }
    }

};

function rcl_get_details_addon(props, e) {

    rcl_preloader_show(jQuery(e).parents('.addon-box'));

    props.action = 'rcl_get_details_addon';

    rcl_ajax({
        data: props,
        success: function (data) {

            ssi_modal.show({
                className: 'rcl-dialog-tab rcl-addon-details',
                sizeClass: 'medium',
                title: data.title,
                buttons: [{
                    label: Rcl.local.close,
                    closeAfter: true
                }],
                content: data.content
            });

        }
    });

    return false;

}

function rcl_update_addon(props, e) {

    var button = jQuery(e);

    if (button.hasClass("updating-message") || button.hasClass("updated-message"))
        return false;

    button.addClass('updating-message');

    var dataString = 'action=rcl_update_addon&addon=' + props.slug;
    jQuery.ajax({
        type: 'POST',
        data: dataString,
        dataType: 'json',
        url: ajaxurl,
        success: function (data) {
            if (data.addon_id == props.slug) {
                button.addClass('button-disabled').toggleClass('updating-message updated-message').html(data.success);
            }
            if (data.error) {

                button.removeClass('updating-message');

                var ssiOptions = {
                    className: 'rcl-dialog-tab rcl-update-error',
                    sizeClass: 'auto',
                    title: Rcl.local.error,
                    buttons: [{
                        label: Rcl.local.close,
                        closeAfter: true
                    }],
                    content: data.error
                };

                ssi_modal.show(ssiOptions);

            }
        }
    });
    return false;

}

function rcl_update_history_url(url) {

    if (url != window.location) {
        if (history.pushState) {
            window.history.pushState(null, null, url);
        }
    }

}

function rcl_init_custom_fields(fields_type, primaryOptions, defaultOptions) {

    RclFields = {
        'type': fields_type,
        'primary': primaryOptions,
        'default': defaultOptions
    };

}

function rcl_get_custom_field_options(e) {

    var typeField = jQuery(e).val();
    var boxField = jQuery(e).parents('.rcl-custom-field');
    var oldType = boxField.attr('data-type');

    var multiVals = ['multiselect', 'checkbox'];

    if (jQuery.inArray(typeField, multiVals) >= 0 && jQuery.inArray(oldType, multiVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var multiVals = ['radio', 'select'];

    if (jQuery.inArray(typeField, multiVals) >= 0 && jQuery.inArray(oldType, multiVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var singleVals = ['date', 'time', 'email', 'url', 'dynamic', 'tel'];

    if (jQuery.inArray(typeField, singleVals) >= 0 && jQuery.inArray(oldType, singleVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var sliderVals = ['runner', 'range'];

    if (jQuery.inArray(typeField, sliderVals) >= 0 && jQuery.inArray(oldType, sliderVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    rcl_preloader_show(boxField);

    rcl_ajax({
        data: {
            action: 'rcl_get_custom_field_options',
            type_field: typeField,
            old_type: oldType,
            post_type: RclFields.type,
            primary_options: RclFields.primary,
            default_options: RclFields.default,
            slug: boxField.data('slug')
        },
        success: function (data) {

            if (data['content']) {

                boxField.find('.options-custom-field').html(data['content']);

                boxField.attr('data-type', typeField);

            }

        }
    });

    return false;

}

function rcl_get_new_custom_field() {

    rcl_preloader_show(jQuery('#rcl-custom-fields-editor'));

    rcl_ajax({
        data: {
            action: 'rcl_get_new_custom_field',
            post_type: RclFields.type,
            primary_options: RclFields.primary,
            default_options: RclFields.default
        },
        success: function (data) {

            if (data['content']) {
                jQuery("#rcl-custom-fields-editor ul").append(data['content']);
            }

        }
    });

    return false;

}

function rcl_enable_extend_options(e) {
    var extend = e.checked ? 1 : 0;
    jQuery.cookie('rcl_extends', extend);
    var options = jQuery('.rcl-options-form .extend-options');
    if (extend)
        options.show();
    else
        options.hide();
}

function rcl_update_options() {

    rcl_preloader_show(jQuery('.rcl-options-form'));

    if (typeof tinyMCE != 'undefined')
        tinyMCE.triggerSave();

    rcl_ajax({
        /*rest: {action: 'usp_update_options'},*/
        data: 'action=rcl_update_options&' + jQuery('.rcl-options-form').serialize()
    });

    return false;
}

function rcl_get_option_help(elem) {

    var help = jQuery(elem).children('.help-content');
    var title_dialog = jQuery(elem).parents('.rcl-option').children('rcl-field-title').text();

    var content = help.html();
    help.dialog({
        modal: true,
        dialogClass: 'rcl-help-dialog',
        resizable: false,
        minWidth: 400,
        title: title_dialog,
        open: function (e, data) {
            jQuery('.rcl-help-dialog .help-content').css({
                'display': 'block',
                'min-height': 'initial'
            });
        },
        close: function (e, data) {
            jQuery(elem).append('<span class="help-content">' + content + '</span>');
        }
    });
}

function rcl_onclick_options_label(e) {

    var label = jQuery(e);

    var viewBox = label.data('options');

    if (jQuery('#' + viewBox + '-options-box').hasClass('active'))
        return false;

    jQuery('.rcl-options .options-box').removeClass('active');
    jQuery('.rcl-options .rcl-menu > a').removeClass('rcl-bttn__active');

    jQuery('#' + viewBox + '-options-box').addClass('active');
    jQuery(e).addClass('rcl-bttn__active');

    rcl_update_history_url(label.attr('href'));

    jQuery('.rcl-options .active-menu-item .rcl-bttn__text').text(label.children('span.rcl-bttn__text').text());
    jQuery('.rcl-options .rcl-menu').removeClass('active-menu');

}

function rcl_show_options_menu(e) {
    jQuery('.rcl-options .rcl-menu').addClass('active-menu');
}

/** Fields Manager **/

var RclManagerFields = {};
var startDefaultbox = 0;

jQuery(function ($) {
    jQuery('.rcl-fields-manager').on('change', 'select[name*="[type]"]', function () {
        rcl_manager_get_custom_field_options(this);
    });
});

rcl_box_default_fields_init();

jQuery(window).scroll(function () {

    rcl_box_default_fields_init();

});

function rcl_init_manager_fields(props) {

    RclManagerFields = props;

}

function rcl_manager_field_switch(e) {
    jQuery(e).parents('.manager-field-header').next('.manager-field-settings').slideToggle();
}

function rcl_switch_view_settings_manager_group(e) {
    jQuery(e).parents('.group-primary-settings').next('.manager-group-settings').slideToggle();
}

function rcl_init_manager_sortable() {

    jQuery(".rcl-fields-manager .fields-box").sortable({
        connectWith: ".rcl-fields-manager .fields-box",
        handle: ".field-control .control-move",
        cursor: "move",
        placeholder: "ui-sortable-placeholder",
        distance: 15,
        receive: function (ev, ui) {
            /*if ( jQuery( ev.target ).hasClass( "rcl-active-fields" ) )
             return true;
             if ( !ui.item.hasClass( "default-field" ) )
             ui.sender.sortable( "cancel" );*/

            if (jQuery(ev.target).hasClass("rcl-active-fields")) {

                if (ui.item.hasClass("template-field")) {
                    var now = new Date();
                    ui.item.clone().appendTo(".rcl-template-fields");
                    ui.item.html(ui.item.html().replace(new RegExp(ui.item.data('id'), 'g'), 'id' + now.getTime()));
                }

                return true;
            } else if (ui.item.hasClass("template-field")) {
                ui.item.remove();
            }

            if (!jQuery(ev.target).hasClass("rcl-default-fields") && ui.item.hasClass("default-field"))
                ui.sender.sortable("cancel");

            if (jQuery(ev.target).hasClass("rcl-default-fields") && !ui.item.hasClass("default-field"))
                ui.sender.sortable("cancel");
        }
    });

    var parentGroup;
    jQuery(".rcl-fields-manager .manager-group-areas").sortable({
        connectWith: ".rcl-fields-manager .manager-group-areas",
        handle: ".rcl-areas-manager .area-move",
        cursor: "move",
        placeholder: "ui-sortable-area-placeholder",
        distance: 15,
        start: function (ev, ui) {
            parentGroup = ui.item.parents('.manager-group');
        },
        stop: function (ev, ui) {
            rcl_init_manager_group(ui.item.parents('.manager-group'), true);
            rcl_init_manager_group(parentGroup, true);
        }
    });

}

function rcl_init_manager_areas_resizable() {

    jQuery(".manager-group").each(function () {

        rcl_init_manager_group(jQuery(this));

    });

}

function rcl_init_manager_group(group, isDefault) {

    var container = group.find(".manager-group-areas");
    var areas = container.children('.manager-area');

    if (isDefault) {
        var defaultPercent = 100 / areas.length;
        areas.css('width', defaultPercent + '%');
        areas.children('.area-width').val(defaultPercent);
    }

    //var minWidth = (container.innerWidth())/5;
    //var maxWidth = container.innerWidth() - minWidth * (areas.length - 1);

    var sibTotalWidth;
    areas.resizable({
        //handles: 'e',
        //minWidth: minWidth,
        //maxWidth: maxWidth,
        start: function (event, ui) {
            sibTotalWidth = ui.originalSize.width + ui.originalElement.next().outerWidth();
            var nextCell = ui.originalElement.next();
            ui.originalElement.addClass('resizable-area');
            nextCell.addClass('resizable-area');
        },
        stop: function (event, ui) {
            var cellPercentWidth = 100 * ui.originalElement.outerWidth(true) / container.innerWidth();
            ui.originalElement.css('width', cellPercentWidth + '%');
            ui.originalElement.children('.area-width').val(Math.round(cellPercentWidth));
            ui.originalElement.removeClass('resizable-area');

            var nextCell = ui.originalElement.next();
            var nextPercentWidth = 100 * nextCell.outerWidth(true) / container.innerWidth();
            nextCell.css('width', nextPercentWidth + '%');
            nextCell.children('.area-width').val(Math.round(nextPercentWidth));
            nextCell.removeClass('resizable-area');
        },
        resize: function (event, ui) {
            ui.originalElement.next().width(sibTotalWidth - ui.size.width);

            var cellPercentWidth = 100 * ui.originalElement.outerWidth(true) / container.innerWidth();
            ui.originalElement.children('.area-width-content').text(Math.round(cellPercentWidth) + '%');

            var nextCell = ui.originalElement.next();
            var nextPercentWidth = 100 * nextCell.outerWidth(true) / container.innerWidth();

            nextCell.children('.area-width-content').text(Math.round(nextPercentWidth) + '%');
        }
    });

}

function rcl_box_default_fields_init() {

    var manager = jQuery('.rcl-fields-manager');
    var box = manager.children('.default-box');

    if (!box.length)
        return false;

    var structureEdit = manager.hasClass('structure-edit') ? true : false;

    var scroll = jQuery(window).scrollTop();

    if (!startDefaultbox) {

        var indent = structureEdit ? -30 : 20;

        if (scroll > box.offset().top + indent) {
            startDefaultbox = box.offset().top + indent;
            if (structureEdit)
                box.next().attr('style', 'margin-top:' + box.outerHeight(true) + 'px');

            box.addClass("fixed");
        }

    } else {

        if (scroll < startDefaultbox) {
            startDefaultbox = 0;
            if (structureEdit)
                box.next().attr('style', 'margin-top:' + 0 + 'px');
            box.removeClass("fixed");
        }

    }

}

function rcl_remove_manager_group(textConfirm, e) {

    if (!confirm(textConfirm))
        return false;

    var areasBox = jQuery(e).parents('.manager-group');

    rcl_preloader_show(areasBox);

    areasBox.remove();

    return false;

}

function rcl_remove_manager_area(textConfirm, e) {

    if (!confirm(textConfirm))
        return false;

    var areaBox = jQuery(e).parents('.manager-area');

    var areasBox = jQuery(e).parents('.manager-group');

    rcl_preloader_show(areaBox);

    areaBox.remove();

    var countAreas = areasBox.find('.manager-area').length;

    areasBox.find('.manager-area .rcl-areas-manager').hide();

    rcl_init_manager_group(areasBox, true);

    return false;

}

function rcl_manager_get_new_area(e) {

    var areasBox = jQuery(e).parents('.manager-group');

    rcl_preloader_show(areasBox);

    rcl_ajax({
        data: {
            action: 'rcl_manager_get_new_area',
            props: RclManagerFields
        },
        success: function (data) {

            areasBox.children('.manager-group-areas').append(data.content);

            rcl_init_manager_sortable();

            rcl_init_manager_group(areasBox, true);

        }
    });

    return false;
}

function rcl_manager_get_new_group(e) {

    var groupsBox = jQuery('.rcl-manager-groups');

    rcl_preloader_show(groupsBox);

    rcl_ajax({
        data: {
            action: 'rcl_manager_get_new_group',
            props: RclManagerFields
        },
        success: function (data) {

            groupsBox.append(data.content);

            rcl_init_manager_sortable();

        }
    });

    return false;
}

function rcl_manager_field_edit(e) {

    var field = jQuery(e).parents('.manager-field');

    field.toggleClass('settings-edit');

    /*ssi_modal.show({
     content: field,
     bodyElement: true,
     title: 'ssi-modal',
     extendOriginalContent: true,
     beforeShow: function(modal){
     field.remove();
     },
     });*/

}

function rcl_manager_field_delete(field_id, meta_delete, e) {

    var field = jQuery(e).parents('.manager-field');

    if (meta_delete) {

        if (confirm(jQuery('#rcl-manager-confirm-delete').text())) {
            jQuery('.rcl-fields-manager-form .submit-box').append('<input type="hidden" name="deleted_fields[]" value="' + field_id + '">');
        }

    }

    field.remove();

    return false;
}

function rcl_manager_get_custom_field_options(e) {

    var typeField = jQuery(e).val();
    var boxField = jQuery(e).parents('.manager-field');
    var oldType = boxField.attr('data-type');

    var multiVals = ['multiselect', 'checkbox'];

    if (jQuery.inArray(typeField, multiVals) >= 0 && jQuery.inArray(oldType, multiVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var multiVals = ['radio', 'select'];

    if (jQuery.inArray(typeField, multiVals) >= 0 && jQuery.inArray(oldType, multiVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var singleVals = ['date', 'time', 'email', 'number', 'url', 'dynamic', 'tel'
    ];

    if (jQuery.inArray(typeField, singleVals) >= 0 && jQuery.inArray(oldType, singleVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    var sliderVals = ['runner', 'range'];

    if (jQuery.inArray(typeField, sliderVals) >= 0 && jQuery.inArray(oldType, sliderVals) >= 0) {

        boxField.attr('data-type', typeField);
        return;

    }

    rcl_preloader_show(boxField);

    rcl_ajax({
        /*rest: true,*/
        data: {
            action: 'rcl_manager_get_custom_field_options',
            newType: typeField,
            oldType: oldType,
            manager: RclManagerFields,
            fieldId: boxField.data('id')
        },
        success: function (data) {

            if (data['content']) {

                boxField.find('.field-secondary-options').replaceWith(data['content']);

                boxField.attr('data-type', typeField);

                rcl_init_iconpicker();

            }

        }
    });

    return false;

}

function rcl_manager_get_new_field(e) {

    var area = jQuery(e).parents('.manager-area');

    rcl_preloader_show(area);

    rcl_ajax({
        /*rest: true,*/
        data: {
            action: 'rcl_manager_get_new_field',
            props: RclManagerFields
        },
        success: function (data) {

            if (data['content']) {
                area.find('.fields-box').append(data['content']);
                area.find('.fields-box').last().find('.rcl-field-core input').focus();
                rcl_init_iconpicker();
            }

        }
    });

    return false;

}

function rcl_manager_update_fields(newManagerId) {

    var newManagerId = newManagerId ? newManagerId : 0;

    rcl_preloader_show(jQuery('.rcl-fields-manager'));

    if (typeof tinyMCE != 'undefined')
        tinyMCE.triggerSave();

    rcl_ajax({
        /*rest: {action: 'rcl_update_fields'},*/
        data: 'action=rcl_manager_update_fields_by_ajax&copy=' + newManagerId + '&' + jQuery('.rcl-fields-manager-form').serialize()
    });

    return false;
}

function rcl_manager_copy_fields(newManagerId) {

    rcl_manager_update_fields(newManagerId);

    return false;
}

/** Fields Manager End**/