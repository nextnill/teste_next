/* fields */
var cbo_start = $('#cbo_start');

var tbl_list_end = $('#tbl_list_end');
var tbl_list_end_tbody = $('#tbl_list_end > tbody');

/* vars */
var arr_locations = [];
var arr_end_locations = [];

function show_dialog_route(start_quarry_id, start_terminal_id)
{
    cbo_start.unbind('change');
    cbo_start.change(function() {
        listar_end_locations();
    });

    listar_locations(start_quarry_id, start_terminal_id);
    showModal('modal_detalhe_add_route');

    cbo_start.prop("disabled", last_end_type ? true : false);
}

// start
function listar_locations(start_quarry_id, start_terminal_id)
{
    cbo_start.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_route/list/locations/json/", function(response) {
        if (response_validation(response)) {
            arr_locations = response;
            
            if ((arr_locations.quarry) && (arr_locations.quarry.length > 0)) {

                // pedreiras
                var opt_quarries = $('<optGroup/>').attr('label', 'Quarries');
                for (var i = 0; i < arr_locations.quarry.length; i++) {
                    var opt_item = $('<option/>').attr('value', 'q' + arr_locations.quarry[i].id).text(arr_locations.quarry[i].name);
                    opt_item.appendTo(opt_quarries);
                }

                // terminais
                var opt_terminals = $('<optGroup/>').attr('label', 'Terminals');
                for (var i = 0; i < arr_locations.terminal.length; i++) {
                    var opt_item = $('<option/>').attr('value', 't' + arr_locations.terminal[i].id).text(arr_locations.terminal[i].name);
                    opt_item.appendTo(opt_terminals);
                }

                opt_quarries.clone().appendTo(cbo_start);
                opt_terminals.clone().appendTo(cbo_start);

                if ((start_terminal_id) && (start_terminal_id > 0)) {
                    cbo_start.val('t' + start_terminal_id).trigger("change");
                }

                if ((start_quarry_id) && (start_quarry_id > 0)) {
                    cbo_start.val('q' + start_quarry_id).trigger("change");
                }

                if (!start_quarry_id && !start_terminal_id) {
                    cbo_start.trigger("change");
                }

            }

            cbo_start.select2();
            set_focus(cbo_start);
        }
    }).fail(ajaxError);
}

// end
function listar_end_locations() {
    var cbo_start_val = cbo_start.val();

    var start_type = (last_end_type ? last_end_type : cbo_start_val.substr(0, 1));
    var start_id = (last_end_id ? last_end_id : cbo_start_val.substr(1, cbo_start_val.length - 1));

    arr_end_locations = [];

    // limpa trs, menos a primeira
    tbl_list_end.find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_route/list/locations/start/json/" + start_type + "/" + start_id, function(response) {
        if (response_validation(response)) {
            arr_end_locations = response;
            $.each(arr_end_locations, function(i, item) {
                add_row_end_locations(tbl_list_end_tbody, item);
            });
        }
    }).fail(ajaxError);
}

function add_row_end_locations(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_end_location = $(new_row.find("[template-field='end_location']"));
    field_end_location.text(item.end_location);
    
    var button_select = new_row.find("[template-button='select']");
    button_select.attr('template-ref', item.id);
    button_select.click(function() {
        var data = {
            id: null,
            travel_route_id: item.id,
            start_location: item.start_location,
            end_location: item.end_location,
            start_quarry_id: item.start_quarry_id,
            start_quarry_name: item.start_quarry_name,
            start_terminal_id: item.start_terminal_id,
            start_terminal_name: item.start_terminal_name,
            end_quarry_id: item.end_quarry_id,
            end_quarry_name: item.end_quarry_name,
            end_terminal_id: item.end_terminal_id,
            end_terminal_name: item.end_terminal_name,
            shipping_time: item.shipping_time,
            blocks: JSON.stringify(item.blocks),
            removed: 'false'
        };

        arr_routes.push(data);
        render_routes();
        closeModal('modal_detalhe_add_route');
    });

    new_row.appendTo(table_body);
}