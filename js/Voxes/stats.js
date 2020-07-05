'use strict';

$(document).ready(function()
{
    $('form[name="filter_voxes_stats"]').on('submit', function(e)
    {
        e.preventDefault();

        var form = $(this);

        $.ajax({
            type: 'POST',
            data: form.serialize() + '&action=filter_voxes_stats',
            processData: false,
            cache: false,
            dataType: 'json',
            success: function(response)
            {
                if (response.status == 'success')
                {
                    v_oa_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    v_oa_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    v_oa_chart.data.labels = response.data.v.oa.labels;
                    v_oa_chart.update();

                    v_o_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    v_o_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    v_o_chart.data.labels = response.data.v.oa.labels;
                    v_o_chart.update();

                    v_l_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    v_l_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    v_l_chart.data.labels = response.data.v.oa.labels;
                    v_l_chart.update();

                    ar_oa_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    ar_oa_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    ar_oa_chart.data.labels = response.data.v.oa.labels;
                    ar_oa_chart.update();

                    ar_o_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    ar_o_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    ar_o_chart.data.labels = response.data.v.oa.labels;
                    ar_o_chart.update();

                    ar_l_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    ar_l_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    ar_l_chart.data.labels = response.data.v.oa.labels;
                    ar_l_chart.update();

                    c_oa_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    c_oa_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    c_oa_chart.data.labels = response.data.v.oa.labels;
                    c_oa_chart.update();

                    c_o_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    c_o_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    c_o_chart.data.labels = response.data.v.oa.labels;
                    c_o_chart.update();

                    c_l_chart.data.datasets[0].data = response.data.v.oa.datasets.data;
                    c_l_chart.data.datasets[0].backgroundColor = response.data.v.oa.datasets.colors;
                    c_l_chart.data.labels = response.data.v.oa.labels;
                    c_l_chart.update();

                    $('[data-modal="filter_voxes_stats"]').removeClass('view');
                }
                else if (response.status == 'error')
                    show_form_errors(form, response);
            }
        });
    });
});
