// FUNCOES
function listar_filter_quarry()
{
    var cbo_filter_quarry = $('#cbo_filter_quarry');

    cbo_filter_quarry.unbind('change');
    cbo_filter_quarry.change(function() {
        listar();
    });

    cbo_filter_quarry.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>/quarry/list/json/", function(response) {
        if (response_validation(response)) {
            add_option(cbo_filter_quarry, '-1', 'None');
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_filter_quarry, item.id, item.name);
            };

            cbo_filter_quarry.select2();
        }
    }).fail(ajaxError);
}

function listar()
{
    var cbo_filter_quarry = $('#cbo_filter_quarry');
    var cbo_filter_type = $('#cbo_filter_type');
    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');

    // limpa trs, menos a primeira
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>po/list/json/" + (cbo_filter_quarry.val() ? cbo_filter_quarry.val() : ''), 
        {block_type: cbo_filter_type.val(),ano: edt_year.val(), mes: cbo_month_filter.val()}, function(response) {
        if (response_validation(response)) {
            var table_body = $('#tbl_listagem > tbody');
            $.each(response, function(i, item) {
                add_row(table_body, item);
            });
        }
    }).fail(ajaxError);
}

function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_quarry_name = $(new_row.find("[template-field='quarry_name']"));
    field_quarry_name.text(item.quarry_name);

    var field_date_production = $(new_row.find("[template-field='date_production']"));
    field_date_production.text(item.date_production.format_date());

    var field_product_name = $(new_row.find("[template-field='product_name']"));
    field_product_name.text(item.product_name);

    var field_block_type = $(new_row.find("[template-field='block_type']"));
    field_block_type.text(str_block_type(item.block_type));

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(str_production_status(item.status));
    if (item.status == PRODUCTION_STATUS.CONFIRMED) {
        field_status.addClass('label label-success');
    }
    else {
        field_status.addClass('label label-default');
    }

    var button_edit = new_row.find("[template-button='edit']");
    button_edit.attr('template-ref', item.id);

    button_edit.click(function() {
        var id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>po/items/' + id;
    });

    var button_visualize = new_row.find("[template-button='visualize']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.click(function () {
        var id = $(this).attr('template-ref');
        show_dialog(FORMULARIO.VISUALIZAR, id);
    });

    var button_delete = new_row.find("[template-button='delete']");
    button_delete.attr('template-ref', item.id);
    button_delete.click(function () {
        var id = $(this).attr('template-ref');
        show_dialog(FORMULARIO.EXCLUIR, id);
        
    });

    new_row.appendTo(table_body);
}

funcs_on_load.push(function(){

    listar_filter_quarry();

    var agora = new Date();
    var mes = ("0" + (agora.getMonth() + 1)).slice(-2);
    var ano = agora.getFullYear();


    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');
      
    edt_year.val(ano);
    cbo_month_filter.val(mes);

    listar();
});