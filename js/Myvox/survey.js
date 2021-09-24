'use strict';

$(document).ready(function()
{
    $('body').on('click', function(e)
    {
        e.stopPropagation();
    });

    $('.tbl_stl_5').find('[data-level]').find('input[type="radio"]').on('change', function()
    {
        var label = $(this).parent();
        var level = $(this).parents('[data-level]');
        var content = level.parent();
        var type = level.find('> div');

        if (type.attr('class') == 'rate' || type.attr('class') == 'twin')
        {
            if ($(this).val() <= 3 || $(this).val() == 'yes')
                content.find('[data-parent="' + $(this).attr('name') + '"]').parents('[data-level]').addClass('open');
            else
            {
                if (level.data('level') == '1')
                {
                    content.find('[data-level="2"]').find('input[type="radio"]').prop('checked', false);
                    content.find('[data-level="2"]').find('label').removeClass('focus');
                    content.find('[data-level="2"]').removeClass('open');
                    content.find('[data-level="3"]').find('input[type="radio"]').prop('checked', false);
                    content.find('[data-level="3"]').find('label').removeClass('focus');
                    content.find('[data-level="3"]').removeClass('open');
                }
                else if (level.data('level') == '2')
                {
                    content.find('[data-parent="' + $(this).attr('name') + '"]').prop('checked', false);
                    content.find('[data-parent="' + $(this).attr('name') + '"]').parents('label').removeClass('focus');
                    content.find('[data-parent="' + $(this).attr('name') + '"]').parents('[data-level]').removeClass('open');
                }
            }
        }

        type.find('label').removeClass('focus');
        label.addClass('focus');
    });

    var signature = document.getElementById('signature');

    if (signature)
    {
        var signature_canvas = signature.querySelector('canvas');
        var signature_pad = new SignaturePad(signature_canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });

        resize_canvas(signature_canvas);

        $('[data-action="clean_signature"]').on('click', function()
        {
            signature_pad.clear();
        });
    }

    $('form[name="new_survey_answer"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);
        var data = new FormData(form[0]);

        if (signature)
        {
            signature_pad.fromData(signature_pad.toData());
            data.append('signature', ((signature_pad.isEmpty()) ? '' : signature_pad.toDataURL('image/jpeg')));
        }

        data.append('action', 'new_survey_answer');

        $.ajax({
            type: 'POST',
            data: data,
            contentType: false,
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    let _;

                    if (response.widget == false)
                    {
                        _ = response.path;
                    }
                    else if (response.widget == true)
                    {
                        _ = 'fast';
                        $('[data-modal="widget"]').addClass('view');
                    }

                    show_modal_success(response.message, 1500, _);
                }
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });
});
