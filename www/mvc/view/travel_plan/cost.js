var tbl_list_cost = $('#tbl_list_cost');
var tbl_list_cost_body = tbl_list_cost.find('tbody');

var tbl_list_cost_route = $('#tbl_list_cost_route');
var tbl_list_cost_route_body = tbl_list_cost_route.find('tbody');

var arr_travel_cost;
var arr_travel_cost_route;
var tot_weight = 0;

function abre_costs(tot_weight_params) {
    list_travel_cost();
    list_travel_cost_route();
    tot_weight = tot_weight_params;
    showModal('cost_modal_detalhe');
}

/* FOBBINGS */
function list_travel_cost() {
    // limpa trs, menos a primeira
    tbl_list_cost.find("tr:gt(1)").remove();

    $.getJSON("<?= APP_URI ?>travel_cost/list/json/", function(response) {    
        if (response_validation(response)) {
            arr_travel_cost = response;
            compare_list_lot_cost();
        }        
    }).fail(ajaxError);
}

function compare_list_lot_cost() {
    $.getJSON("<?= APP_URI ?>travel_plan/cost/list/json/" + lot_transport_id, function(response) {    
        if (response_validation(response)) {
            var arr_lot_transport_cost = response;

            for (var i = 0; i < arr_travel_cost.length; i++) {
                arr_travel_cost[i].value = 0;
                for (var j = 0; j < arr_lot_transport_cost.length; j++) {
                    if (arr_travel_cost[i].id == arr_lot_transport_cost[j].travel_cost_id) {
                        arr_travel_cost[i].value = arr_lot_transport_cost[j].value;
                    }
                };
            };
            
            render_travel_cost();
        }
    }).fail(ajaxError);
}

function render_travel_cost() {
    $.each(arr_travel_cost, function(i, item) {
        add_row_travel_cost(tbl_list_cost_body, item);
    });

    set_focus($('#cost_modal_detalhe').find('input[type=text],textarea,select').filter(':visible:first'));
    $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});
    calc_total();
}

function add_row_travel_cost(table_body, item) {
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', item.id);

    var field_cost = $(new_row.find("[template-field='cost']"));
    field_cost.text(item.name);

    var field_value = $(new_row.find("[template-field='value']"));
    field_value.attr('template-ref', item.id);

    field_value.unbind('change');
    field_value.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_total();
    });

    if ((item.value) && (parseFloat(item.value) > 0)) {
        field_value.val(item.value);
    }

    var field_cost_preview = $(new_row.find("[template-field='cost_preview']"));
    field_cost_preview.text(item.cost_per_tonne != null ? parseFloat(tot_weight).format_number(2)*parseFloat(item.cost_per_tonne).format_number(2) : '' );

    field_cost_preview.unbind('click');
    field_cost_preview.click(function(){
        field_value.val(this.textContent);
    });

    

    new_row.appendTo(table_body);
}

function calc_total() {
    persist_inputs_costs();
    
    var total = 0;
    if (arr_travel_cost) {
        for (var i = 0; i < arr_travel_cost.length; i++) {
            if (!isNaN(parseFloat(arr_travel_cost[i].value))) {
                total += parseFloat(arr_travel_cost[i].value);
            }
        };
    }
    
    if (arr_travel_cost_route) {
        for (var i = 0; i < arr_travel_cost_route.length; i++) {
            if (!isNaN(parseFloat(arr_travel_cost_route[i].cost_value))) {
                total += parseFloat(arr_travel_cost_route[i].cost_value);
            }
            if (!isNaN(parseFloat(arr_travel_cost_route[i].cost_handle_in_out))) {
                total += parseFloat(arr_travel_cost_route[i].cost_handle_in_out);
            }
        };
    }
    
    $('#edt_total').text(total.format_number(2));
}

function persist_inputs_costs() {
    if (arr_travel_cost) {
        for (var i = 0; i < arr_travel_cost.length; i++) {
            var row_cost = $('#tbl_list_cost > tbody > [template-row-ref="' + arr_travel_cost[i].id + '"] > td');
            if (row_cost.length > 0) {
                arr_travel_cost[i].value = row_cost.find('[template-field="value"]').val();
            }
        }
    }
    
    if (arr_travel_cost_route) {
        for (var i = 0; i < arr_travel_cost_route.length; i++) {
            var row_cost_route = $('#tbl_list_cost_route > tbody > [template-row-ref="' + arr_travel_cost_route[i].id + '"] > td');
            if (row_cost_route.length > 0) {
                arr_travel_cost_route[i].cost_value = row_cost_route.find('[template-field="cost_value"]').val();
                arr_travel_cost_route[i].cost_handle_in_out = row_cost_route.find('[template-field="cost_handle_in_out"]').val();
            }
        }
    }
}


/* COST ROUTE */
function list_travel_cost_route() {
    // limpa trs, menos a primeira
    tbl_list_cost_route.find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_plan/list/json/" + lot_transport_id, function(response) {
        if (response_validation(response)) {
            arr_travel_cost_route = response;
            render_travel_cost_route();
        }
    }).fail(ajaxError);
}

function render_travel_cost_route() {
    if (arr_travel_cost_route.length > 0) {
        $.each(arr_travel_cost_route, function(i, item) {
            add_row_travel_cost_route(tbl_list_cost_route_body, item);
        });
        tbl_list_cost_route.show();
    }
    else {
        tbl_list_cost_route.hide();
    }

    calc_total();
}

function add_row_travel_cost_route(table_body, item) {
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', item.id);

    var field_start_location = $(new_row.find("[template-field='start_location']"));
    field_start_location.text(item.start_location);

    var field_end_location = $(new_row.find("[template-field='end_location']"));
    field_end_location.text(item.end_location);

    var field_cost_value = $(new_row.find("[template-field='cost_value']"));
    field_cost_value.attr('template-ref', item.id);

    field_cost_value.unbind('change');
    field_cost_value.change(function() {
        //$(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_total();
    });

    if ((item.cost_value) && (parseFloat(item.cost_value) > 0)) {
        field_cost_value.val(item.cost_value);
    }

    var field_last_cost_value = $(new_row.find("[template-field='last_cost_value']"));
    field_last_cost_value.text(item.last_cost_value ? item.last_cost_value.format_number(2): '');
    field_last_cost_value.css('cursor', 'pointer');
    field_last_cost_value.unbind('click');
    field_last_cost_value.click(function(){
        if (!isNaN(parseFloat(item.last_cost_value))) {
            var value = field_last_cost_value.val();
            if ((value.trim() == '') || (isNaN(parseFloat(value)))) {
                field_cost_value.val(parseFloat(item.last_cost_value).format_number(2));
            }            
        }

        calc_total();
    });

    var field_cost_handle_in_out = $(new_row.find("[template-field='cost_handle_in_out']"));
    field_cost_handle_in_out.attr('template-ref', item.id);

    field_cost_handle_in_out.unbind('change');
    field_cost_handle_in_out.change(function() {
        //$(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_total();
    });

    if ((item.cost_handle_in_out) && (parseFloat(item.cost_handle_in_out) > 0)) {
        field_cost_handle_in_out.val(item.cost_handle_in_out);
    }
    
    new_row.appendTo(table_body);
}


// save
function costs_envia_detalhes() {
    persist_inputs_costs();
    // post
    if (arr_travel_cost.length > 0) {
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>travel_plan/cost/save/",
            data: {
                lot_transport_id: lot_transport_id,
                costs: arr_travel_cost,
                costs_route: arr_travel_cost_route
            },
            success: function (response) {
                if (response_validation(response)) {
                    alert_saved('Costs saved successfully');
                    closeModal('cost_modal_detalhe');
                }
            }
        });
    }
}