'use strict';

$(document).ready(function()
{
    $('[data-action="filter_menu_products_by_category"]').on('click', function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + $(this).data('id') + '&action=filter_menu_products_by_category',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    $('[data-menu-products]').html(response.html);
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[data-action="preview_menu_product"]').on('click', function()
    {
        $.ajax({
            type: 'POST',
            data: 'id=' + $(this).data('id') + '&action=preview_menu_product',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    $('[data-modal="preview_menu_product"]').find('main > figure > img').attr('src', '../uploads/' + response.data.avatar);
                    $('[data-modal="preview_menu_product"]').find('main > h2').html(response.data.name);
                    $('[data-modal="preview_menu_product"]').find('main > span').html(response.data.price);
                    $('[data-modal="preview_menu_product"]').find('main > p').html(response.data.description);

                    $('[data-modal="preview_menu_product"]').addClass('view');
                }
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $(document).on('click', '[data-action="remove_to_menu_order"]', function()
    {
        var target = $(this);

        $.ajax({
            type: 'POST',
            data: 'id=' + $(this).data('id') + '&action=remove_to_menu_order',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    target.parent().find('span').html(response.data.quantity);
                    $('[data-total] > span').html(response.data.total);
                }
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $(document).on('click', '[data-action="add_to_menu_order"]', function()
    {
        var target = $(this);

        $.ajax({
            type: 'POST',
            data: 'id=' + $(this).data('id') + '&action=add_to_menu_order',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    target.parent().find('span').html(response.data.quantity);
                    $('[data-total] > span').html(response.data.total);
                }
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $(document).on('click', '[data-action="delete_to_menu_order"]', function()
    {
        var target = $(this);

        $.ajax({
            type: 'POST',
            data: 'id=' + $(this).data('id') + '&action=delete_to_menu_order',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    target.parent().parent().remove();
                    $('[data-total] > span').html(response.data.total);
                }
                else if (response.status == 'error')
                    show_modal_error(response.message);
            }
        });
    });

    $('[name="type_service"]').on('change', function()
    {
        if ($(this).val() == 'restaurant')
        {
            $('[name="owner"]').parent().parent().parent().addClass('hidden');
            $('[name="address"]').parent().parent().parent().addClass('hidden');
            $('[name="firstname"]').parent().parent().parent().addClass('hidden');
            $('[name="lastname"]').parent().parent().parent().addClass('hidden');
            $('[name="email"]').parent().parent().parent().addClass('hidden');
            $('[name="phone_lada"]').parent().parent().parent().addClass('hidden');
            $('[name="phone_number"]').parent().parent().parent().addClass('hidden');
            $('button[type="submit"]').parent().parent().addClass('hidden');
        }
        else if ($(this).val() == 'home')
        {
            $('[name="owner"]').parent().parent().parent().addClass('hidden');
            $('[name="address"]').parent().parent().parent().removeClass('hidden');
            $('[name="firstname"]').parent().parent().parent().removeClass('hidden');
            $('[name="lastname"]').parent().parent().parent().removeClass('hidden');
            $('[name="email"]').parent().parent().parent().removeClass('hidden');
            $('[name="phone_lada"]').parent().parent().parent().removeClass('hidden');
            $('[name="phone_number"]').parent().parent().parent().removeClass('hidden');
            $('button[type="submit"]').parent().parent().removeClass('hidden');
        }

        required_focus('form', $('form[name="new_menu_order"]'), null);
    });

    $('[name="owner"]').on('change', function()
    {
        $.ajax({
            type: 'POST',
            data: 'owner=' + $(this).val() + '&action=get_owner',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response) { }
        });
    });

    $('form[name="new_menu_order"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            type: 'POST',
            data: form.serialize() + '&action=new_menu_order',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                    show_modal_success(response.message, 8000, response.path);
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });
});
