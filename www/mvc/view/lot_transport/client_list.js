function cl_listar()
{
    // limpa trs, menos a primeira
    //
    $('#tbl_client_list').find("tr:gt(1)").remove();

    showModal('modal_client_list');

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_without_lot/json/", function(response) {
        if (response_validation(response)) {
            var table_body = $('#tbl_client_list > tbody');

            $.each(response, function(i, item) {
                cl_add_row(table_body, item);
            });
        }        
    }).fail(ajaxError);
}

function cl_add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_client_code = $(new_row.find("[template-field='client_code']"));
    field_client_code.text(item.client_code);

    var field_client_name = $(new_row.find("[template-field='client_name']"));
    field_client_name.text(item.client_name);

    var button_select = new_row.find("[template-button='select']");
    button_select.attr('template-ref', item.client_id);
    button_select.attr('template-ref-name', item.client_name);
    button_select.click(function() {
        var client_id = $(this).attr('template-ref');
        var client_name = $(this).attr('template-ref-name');
        closeModal('modal_client_list');
        ab_init(client_id, client_name);
    });

    new_row.appendTo(table_body);
}