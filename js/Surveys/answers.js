'use strict';

$(document).ready(function()
{
    var reload = true;

    setTimeout(function() {

        if (reload == true)
            location.reload();

    }, 60000);

    $('form[name="filter_surveys_answers"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            type: 'POST',
            data: form.serialize() + '&action=filter_surveys_answers',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    location.reload();
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });

    var id = null;

    $('[data-action="preview_survey_answer"]').on('click', function()
    {
        id = $(this).data('id');
        reload = false;

        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=preview_survey_answer',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    $('[data-modal="preview_survey_answer"]').find('main').find('.survey_answer_preview').html(response.html);
                    $('[data-modal="preview_survey_answer"]').addClass('view');
                }
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });

    $('[data-modal="preview_survey_answer"]').modal().onCancel(function()
    {
        id = null;
        reload = true;
    });

    $('[data-button-modal="filter_surveys_answers"]').on('click', function()
    {
        reload = false;
    });

    $('[data-modal="filter_surveys_answers"]').modal().onCancel(function()
    {
        reload = true;
    });

    $('[data-action="edit_reservation"]').on('click', function()
    {
        id = $(this).data('id');
        reload = false;

        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=get_survey_reservation',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    if (response.data.reservation)
                    {
                        $('[name="firstname"]').val(response.data.reservation.firstname);
                        $('[name="lastname"]').val(response.data.reservation.lastname);
                        $('[name="guest_id"]').val(response.data.reservation.guest_id);
                        $('[name="reservation_number"]').val(response.data.reservation.reservation_number);
                        $('[name="check_in"]').val(response.data.reservation.check_in);
                        $('[name="check_out"]').val(response.data.reservation.check_out);
                        $('[name="nationality"]').val(response.data.reservation.nationality);
                        $('[name="input_channel"]').val(response.data.reservation.input_channel);
                        $('[name="traveler_type"]').val(response.data.reservation.traveler_type);
                        $('[name="age_group"]').val(response.data.reservation.age_group);
                    }

                    $('[data-modal="edit_reservation"]').addClass('view');
                }
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });

    $('[data-modal="edit_reservation"]').modal().onCancel(function()
    {
        id = null;
        reload = true;

        clean_form($('form[name="edit_reservation"]'));
    });

    $('form[name="edit_reservation"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        var data = '&id=' + id + '&action=edit_reservation';

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

    $('[data-action="print_survey_answer"]').on('click', function()
    {
        id = $(this).data('id');
        reload = false;

        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=print_survey_answer',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    var print = window.open(' ', 'popimpr');

                    print.document.write(response.html);
                    print.document.close();

                    print.print();
                    print.close();
                }
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });

    $('[data-action="public_survey_comment"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="public_survey_comment"]').addClass('view');
    });

    $('[data-modal="public_survey_comment"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=public_survey_comment',
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

    $('[data-action="unpublic_survey_comment"]').on('click', function()
    {
        id = $(this).data('id');

        $('[data-modal="unpublic_survey_comment"]').addClass('view');
    });

    $('[data-modal="unpublic_survey_comment"]').modal().onSuccess(function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + id + '&action=unpublic_survey_comment',
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
