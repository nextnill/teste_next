function listar_filter_client()
{
    var cbo_filter_client = $('#cbo_filter_client');

    cbo_filter_client.unbind('change');
    cbo_filter_client.change(function() {
        listar();
    });

    cbo_filter_client.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_head_office/json/", function(response) {
        if (response_validation(response)) {
            add_option(cbo_filter_client, '-1', 'None');
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_filter_client, item.id, item.code + ' - ' + item.name);
            };

            cbo_filter_client.select2();
        }
    }).fail(ajaxError);
}

function listar()
{
    var cbo_filter_client = $('#cbo_filter_client');

    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>lots/list/json/" + (cbo_filter_client.val() ? cbo_filter_client.val() : ''), function(response) {
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

    var field_lot_number = $(new_row.find("[template-field='lot_number']"));
    field_lot_number.text(item.lot_number);

    var field_client_name = $(new_row.find("[template-field='client_name']"));
    field_client_name.text(item.client_name);

    var field_date_record = $(new_row.find("[template-field='date_record']"));
    field_date_record.text(item.date_record.format_date());

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(str_lot_transport_status(item.status));

    var field_items_count = $(new_row.find("[template-field='items_count']"));
    field_items_count.text(item.items_count);

    switch (parseInt(item.status, 10))
    {
        case LOT_TRANSPORT_STATUS.DRAFT:
            field_status.addClass('label label-default');
            break;
        case LOT_TRANSPORT_STATUS.RELEASED:
            field_status.addClass('label label-info');
            break;
        case LOT_TRANSPORT_STATUS.TRAVEL_STARTED:
            field_status.addClass('label label-warning');
            break;
        case LOT_TRANSPORT_STATUS.DELIVERED:
            field_status.addClass('label label-success');
            break;
    }

    var button_visualize = new_row.find("[template-button='blocks']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.attr('href', '<?= APP_URI ?>lots/detail/' + item.id);

    var button_release = new_row.find("[template-button='release']");
    button_release.attr('template-ref', item.id);
    button_release.attr('template-ref-status', item.status);
    button_release.click(function() {
        var id = $(this).attr('template-ref');
        var status = $(this).attr('template-ref-status');
        
        var release_action = function() {
            closeModal('alert_modal');
            $.ajax({
                error: ajaxError,
                type: "POST",
                url: "<?= APP_URI ?>lots/release/",
                data: {
                    id: id,
                    release: (status == 0 ? true : false)
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
        };

        alert_modal('Lot', (status == 0 ? 'Release' : 'Undo release') + ' ' + item.lot_number + '?', 'Release', release_action, true);
    });

    var button_travel_plan = new_row.find("[template-button='travel_plan']");
    button_travel_plan.attr('template-ref', item.id);
    button_travel_plan.attr('href', '<?= APP_URI ?>travel_plan/list/' + item.id);

    var button_pointing_location = new_row.find("[template-button='pointing_location']");
    button_pointing_location.attr('template-ref', item.id);
    button_pointing_location.attr('href', '<?= APP_URI ?>lots/location/' + item.id);

    var button_dismember = new_row.find("[template-button='dismember']");
    button_dismember.click(function() {
        show_dialog_dismember(item.id, item.lot_number);
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
                url: "<?= APP_URI ?>lots/delete/",
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

        alert_modal('Lot', 'Delete Lot #' + id + '?', 'Delete', delete_action, true);
    });
    
    // se estiver entregue, desabilito o desmembramento de lote
    if (item.status == LOT_TRANSPORT_STATUS.DRAFT || item.status == LOT_TRANSPORT_STATUS.DELIVERED) {
        var menu_dismember = new_row.find("[template-menu='dismember']");
        menu_dismember.addClass('disabled');
        button_dismember.unbind('click');
    }
        
    if (item.status == 0) {
        button_visualize.removeClass('btn-default');
        button_travel_plan.removeClass('btn-default');
        button_release.removeClass('btn-default');
        button_visualize.addClass('btn-primary');
        button_travel_plan.addClass('btn-primary');
        button_release.addClass('btn-primary');
    }
    else if (item.status > 0) {
        var menu_delete = new_row.find("[template-menu='delete']");
        menu_delete.addClass('disabled');
        button_delete.unbind('click');

        if (item.status == 1) {
            button_release.removeClass('btn-default');
            button_release.removeClass('btn-primary');
            button_release.addClass('btn-info');
        }
        else {
            button_release.addClass('disabled');
            button_release.unbind('click');
        }
    }

    new_row.appendTo(table_body);
}

function btn_lt_new_click() {
    window.location = '<?= APP_URI ?>lots/detail/';
}

$('.btn_lt_new').click(btn_lt_new_click);

listar();
listar_filter_client();