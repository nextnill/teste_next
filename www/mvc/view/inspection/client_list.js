function listar()
{
    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>block/clients/reservations/json/", function(response) {
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

    var field_client_code = $(new_row.find("[template-field='client_code']"));
    field_client_code.text(item.client_code);

    var field_client_name = $(new_row.find("[template-field='client_name']"));
    field_client_name.text(item.client_name);

    var field_blocks = $(new_row.find("[template-field='blocks']"));
    field_blocks.text(item.blocks);

    var field_vol = $(new_row.find("[template-field='vol']"));
    field_vol.text(item.net_vol);

    var button_select = new_row.find("[template-button='select']");
    button_select.attr('template-ref', item.client_id);
    button_select.click(function() {
        var client_id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>inspection/blocks/' + client_id;
    });

    new_row.appendTo(table_body);
}

listar();
