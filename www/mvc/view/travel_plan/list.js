/* fields */
var div_travel_plan = $('#div_travel_plan');
var div_chk_client_remove = $('#div_chk_client_remove');
var chk_client_remove = $('#chk_client_remove');
var div_chk_local_market = $('#div_chk_local_market');
var chk_local_market = $('#chk_local_market');
var tbl_tp_listagem = $('#tbl_tp_listagem');
var tbl_tp_listagem_tbody = $('#tbl_tp_listagem > tbody');
var btn_add_travel = $('#btn_add_travel');
var btn_add_travel = $('#btn_add_travel');
var btn_import_template = $('#btn_import_template');
var btn_costs = $('#btn_costs');
var btn_travel_refresh = $('#btn_travel_refresh');
var list_history = $('#list_history');
var template_history = $('#template_history');

/* vars */
var client_remove = <?= $lot_transport->client_remove ? 'true' : 'false' ?>;
var local_market = <?= $lot_transport->local_market ? 'true' : 'false' ?>;
var arr_travel_plan = [];
var last_end_type = null;
var last_end_id = null;
var last_end = null;
var last_end_quarry_id = null;
var last_end_terminal_id = null;

// FUNCOES
function tp_listar()
{
    if (lot_transport_id == 0) {
        div_travel_plan.hide();
        btn_costs.hide();
    }
    if (lot_transport_id > 0) {
        div_travel_plan.show();
        arr_travel_plan = [];
        last_end_type = null;
        last_end_id = null;
        last_end = null;
        last_end_quarry_id = null;
        last_end_terminal_id = null;

        // limpa trs, menos a primeira
        tbl_tp_listagem.find("tr:gt(1)").remove();

        // pesquisa a listagem em json
        $.getJSON("<?= APP_URI ?>travel_plan/list/json/" + lot_transport_id, function(response) {
            if (response_validation(response)) {
                arr_travel_plan = response;

                $.each(arr_travel_plan, function(i, item) {
                    tp_add_row(tbl_tp_listagem_tbody, item, (i+1 == arr_travel_plan.length));
                });

                var cbo_start = $('#cbo_start');
                cbo_start.prop("disabled", last_end_type ? true : false);
                
                if (arr_travel_plan.length == 0) {
                    div_chk_client_remove.show();
                }
                else if (arr_travel_plan.length > 0) {
                    div_chk_client_remove.hide();
                }

                change_client_remove();

                tp_listar_history();
            }        
        }).fail(ajaxError);
    }
}

function tp_add_row(table_body, item, remove_button)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_start_location = $(new_row.find("[template-field='start_location']"));
    field_start_location.text(item.start_location);

    var field_end_location = $(new_row.find("[template-field='end_location']"));
    field_end_location.text(item.end_location);

    var field_shipping_time = $(new_row.find("[template-field='shipping_time']"));
    field_shipping_time.text(item.shipping_time);

    if (item.end_quarry_id) {
        last_end_type = 'q';
        last_end_id = item.end_quarry_id;

        last_end_quarry_id = item.end_quarry_id;
        last_end_terminal_id = null;
    }
    else if (item.end_terminal_id) {
        last_end_type = 't';
        last_end_id = item.end_terminal_id;

        last_end_quarry_id = null;
        last_end_terminal_id = item.end_terminal_id;
    }

    last_end = last_end_type + last_end_id;

    var button_remove = $(new_row.find("[template-button='remove']"));
    if ((remove_button) && (lot_transport_status == 0)) {
        button_remove.attr('template-ref', item.id);
        button_remove.click(function() {
            var id = $(this).attr('template-ref');

            var remove_travel_route = function() {
                closeModal('alert_modal');

                $.ajax({
                    error: ajaxError,
                    type: "POST",
                    url: "<?= APP_URI ?>travel_plan/delete/",
                    data: { id: id },
                    success: function (response) {
                        if (response_validation(response)) {
                            tp_listar();
                            //alert_modal('Travel Route', 'Travel route removed successfully.');
                        }
                        
                    }
                });
            }

            alert_modal('Remove Travel Route', 'Remove travel route: ' + item.start_location + ' to ' + item.end_location + ' ?', 'Yes, remove this travel route', remove_travel_route, true);
        });
    }
    else {
        button_remove.hide();
    }

    new_row.appendTo(table_body);
}

function change_client_remove() {
    if (lot_transport_id > 0) {
        if (chk_client_remove.prop('checked') == false) {
            // permite cadastrar viagens
            btn_add_travel.show();
            if (arr_travel_plan.length == 0) {
                btn_import_template.prop("disabled", false);
            }
            else {
                btn_import_template.prop("disabled", true);
            }
            btn_costs.show();

            tbl_tp_listagem.show();
            btn_travel_refresh.show();
        }
        else {
            // bloqueio cadastro de viagens
            btn_add_travel.hide();
            btn_import_template.prop("disabled", true);
            tbl_tp_listagem.hide();
            btn_travel_refresh.hide();
            btn_costs.hide();
        }
    }

    if (lot_transport_status > 0) {
        btn_add_travel.hide();
        btn_import_template.hide();
        chk_client_remove.prop("disabled", true);
        chk_local_market.prop("disabled", true);
    }
}

function update_client_remove() {
    chk_client_remove.prop("disabled", true);
    $.ajax({
        error: ajaxError,
        type: "POST",
        url: "<?= APP_URI ?>lots/client_remove/",
        data: { lot_transport_id: lot_transport_id,
                client_remove: chk_client_remove.prop('checked')
        },
        success: function (response) {
            if (response_validation(response)) {
                // tp_listar();
                client_remove = response.client_remove;
                chk_client_remove.prop("disabled", false);
            }
        }
    });
}

function update_local_market() {
    chk_local_market.prop("disabled", true);
    $.ajax({
        error: ajaxError,
        type: "POST",
        url: "<?= APP_URI ?>lots/local_market/",
        data: { lot_transport_id: lot_transport_id,
                local_market: chk_local_market.prop('checked')
        },
        success: function (response) {
            if (response_validation(response)) {
                // tp_listar();
                local_market = response.local_market;
                chk_local_market.prop("disabled", false);
            }
        }
    });
}

function tp_listar_history() {
    // list_history
    // template_history

    // limpa os itens
    list_history.html();

    WS.get("travel_plan/history/json/",
        { lot_transport_id: lot_transport_id },
        function(response) {
            if (response.length == 0) {
                list_history.html('none');
            }
            else {
                $.each(response, function(i, item) {
                    var new_row = template_history.clone();
                    new_row.removeAttr("id");
                    new_row.css("display", '');

                    var field_date = $(new_row.find("[template-field='date']"));
                    field_date.text(item.date_history ? item.date_history.format_date_time() : '');

                    var field_block_number = $(new_row.find("[template-field='block_number']"));
                    field_block_number.text(item.block_number);

                    var field_destination = $(new_row.find("[template-field='destination']"));
                    field_destination.text(item.end_location);

                    if (item.client_removed == '1') {
                        field_destination.text('Client will remove the block from the quarry');
                    }

                    var field_status = $(new_row.find("[template-field='status']"));
                    field_status.text(str_travel_plan_status(item.status));

                    switch (parseInt(item.status, 10))
                    {
                        case TRAVEL_PLAN_STATUS.PENDING:
                            field_status.addClass('label label-default');
                            break;
                        case TRAVEL_PLAN_STATUS.STARTED:
                            field_status.addClass('label label-warning');
                            break;
                        case TRAVEL_PLAN_STATUS.COMPLETED:
                            field_status.addClass('label label-success');
                            break;
                    }

                    new_row.appendTo(list_history);
                });
                
                var bg_color = $('.table-striped>tbody>tr:nth-child(odd)>td').css('background-color');

                list_history.find('.row:nth-of-type(even)').css('background', bg_color);

            }
        }
    );
}

// on load window
funcs_on_load.push(function() {
    tp_listar();

    chk_client_remove.unbind('change');
    chk_client_remove.prop('checked', client_remove);
    chk_client_remove.change(function() {
        change_client_remove();
        update_client_remove();
    });
    change_client_remove();

    chk_local_market.unbind('change');
    chk_local_market.prop('checked', local_market);
    chk_local_market.change(function() {
        update_local_market();
    });
});