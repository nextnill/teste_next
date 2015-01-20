function listar()
{
    // limpa trs, menos a primeira
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_cost/list/json/", function(response) {
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

    var field_name = $(new_row.find("[template-field='name']"));
    field_name.text(item.name);

    var field_type = $(new_row.find("[template-field='type']"));
    field_type.text(str_lot_travel_cost_type(item.type));

    var button_edit = new_row.find("[template-button='edit']");
    button_edit.attr('template-ref', item.id);
    button_edit.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.EDITAR, id);
        }
    );

    var button_visualize = new_row.find("[template-button='visualize']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        }
    );

    var button_delete = new_row.find("[template-button='delete']");
    button_delete.attr('template-ref', item.id);
    button_delete.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.EXCLUIR, id);
        }
    );

    new_row.appendTo(table_body);
}

listar();