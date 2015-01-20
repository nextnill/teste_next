function listar()
{
    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>inspection_certificate/list/json/", function(response) {
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

    var field_date_record = $(new_row.find("[template-field='date_record']"));
    field_date_record.text(item.date_record.format_date());

    var field_client_name = $(new_row.find("[template-field='client_name']"));
    field_client_name.text(item.client_name);

    var button_visualize = new_row.find("[template-button='blocks']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.attr('href', '<?= APP_URI ?>inspection_certificate/detail/' + item.id);

    new_row.appendTo(table_body);

    var button_select_excel = new_row.find("[template-button='select_excel']");
    button_select_excel.attr('template-ref', item.id);
    button_select_excel.click(function() {
        var invoice_id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>inspection_certificate/download_excel/?invoice_id='+invoice_id;
    });    

    var button_select = new_row.find("[template-button='select']");
    button_select.attr('template-ref', item.id);
    button_select.click(function() {
        var invoice_id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>inspection_certificate/download/?invoice_id='+invoice_id;
    });

    var button_delete = new_row.find("[template-button='delete']");
    button_delete.attr('template-ref', item.id);
    button_delete.click(function() {
        var id = $(this).attr('template-ref');
        
        var delete_action = function() {
            closeModal('alert_modal');
            $.ajax({
                error: ajaxError,
                type: "POST",
                url: "<?= APP_URI ?>inspection_certificate/delete/",
                data: {
                    id: id
                },
                dataType: 'json',
                success: function (response) {
                    setTimeout(function() {
                        if (response_validation(response)) {
                            listar();
                        }
                    }, 800);
                }
            });

            listar();
        };

        alert_modal('Inspection', 'Delete Inspection Certificate #' + id + '?', 'Delete', delete_action, true);
    });
}


listar();