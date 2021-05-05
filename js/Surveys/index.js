'use strict';

$(document).ready(function()
{
    var id = null;
    var edit = false;

    $('[data-modal="new_survey"]').modal().onCancel(function()
    {
        id = null;
        edit = false;

        clean_form($('form[name="new_survey"]'));
    });

    $('form[name="new_survey"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        if (edit == false)
            var data = '&action=new_survey';
        else if (edit == true)
            var data = '&id=' + id + '&action=edit_survey';

        $.ajax({
            type: 'POST',
            data: form.serialize() + data,
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 600);
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });

    $('[data-action="edit_survey"]').on('click', function()
    {
        id = $(this).data('id');
        edit = true;

        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=get_survey',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    $('[name="name_es"]').val(response.data.name.es);
                    $('[name="name_en"]').val(response.data.name.en);
                    $('[name="text_es"]').val(response.data.text.es);
                    $('[name="text_en"]').val(response.data.text.en);
                    $('[name="signature"]').prop('checked', ((response.data.signature == true) ? true : false));
                    $('[name="main"]').prop('checked', ((response.data.main == true) ? true : false));

                    required_focus('form', $('form[name="new_survey"]'), null);

                    $('[data-modal="new_survey"]').addClass('view');
                }
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[data-action="deactivate_survey"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="deactivate_survey"]').addClass('view');
    });

    $('[data-modal="deactivate_survey"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=deactivate_survey',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 600);
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[data-action="activate_survey"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="activate_survey"]').addClass('view');
    });

    $('[data-modal="activate_survey"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=activate_survey',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 600);
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[data-action="delete_survey"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="delete_survey"]').addClass('view');
    });

    $('[data-modal="delete_survey"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=delete_survey',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 600);
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });
});
