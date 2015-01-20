var arr_pending_blocks = [];
var arr_pending_blocks_selected = [];

function init_list() {
    var menu_heading = $('#pt_menu_heading');
    var menu_footer = $('#pt_menu_footer');

    // clona menu
    menu_heading.clone(true).contents().appendTo(menu_footer);

    listar_blocks();
}

function listar_blocks(callback_function)
{
    var btn_pt_client_removed = $('.btn_pt_client_removed');
    btn_pt_client_removed.unbind('click');
    btn_pt_client_removed.click(btn_pt_client_removed_click);

    var btn_pt_start = $('.btn_pt_start');
    btn_pt_start.unbind('click');
    btn_pt_start.click(btn_pt_start_click);

    var btn_pt_complete = $('.btn_pt_complete');
    btn_pt_complete.unbind('click');
    btn_pt_complete.click(btn_pt_complete_click);

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_plan/pending/json/", function(response_pending) {
        if (response_validation(response_pending)) {
            var list = $('#list');
            var table = $('[template-table="pending"]').clone();
            var table_body = $(table).find('table > tbody');
            var lot_number = '';
            var client_name = '';
            var client_groups = [];

            arr_pending_blocks = response_pending;

            // limpa a listagem
            list.html('');

            // limpa trs, menos a primeira
            table.find("tr:gt(1)").remove();
            table.removeAttr("template-table");
            table.css("display", '');

            $.each(arr_pending_blocks, function(i, item) {
                // se for o primeiro registro, seta o título na tabela
                if (i == 0) {
                    table.find("[template-title]").text(item.lot_number);
                    table.find('[template-field="client"]').text(item.client_name);
                    var group_names = '';
                    for(var j=0; j<item.client_groups.length; j++){
                        if(j>0){
                            group_names += ', ';
                        }
                        group_names += item.client_groups[j].name;
                    }

                    table.find('[template-field="group"]').text(group_names);
                    
                }
                // se for uma nova pedreira
                if (item.lot_number != lot_number) {
                    
                    // se não for o primeiro registro
                    if (i > 0) {
                        // mostra a tabela
                        table.appendTo(list);
                    }
                    table = $('[template-table="pending"]').clone();
                    table_body = $(table).find('table > tbody');

                    table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
                    table.removeAttr("template-table");
                    table.css("display", '');
                    table.find("[template-title]").text(item.lot_number);
                    table.find('[template-field="client"]').text(item.client_name);
                   
                   var group_names = '';
                    for(var j=0; j<item.client_groups.length; j++){
                        if(j>0){
                            group_names += ', ';
                        }
                        group_names += item.client_groups[j].name;
                    }

                    table.find('[template-field="group"]').text(group_names);

                    //order_up
                    var button_order_up = table.find("[template-button='order_up']");
                    button_order_up.attr('template-ref', item.lot_transport_id);
                    button_order_up.click(function () {
                        var id = $(this).attr('template-ref');
                        $.ajax({
                            error: ajaxError,
                            type: "POST",
                            url: "<?= APP_URI ?>lots/change_order/",
                            data: { id: id, type: 'up' },
                            success: function (response) {
                                if (response_validation(response)) {
                                    listar_blocks(function(){
                                        if ((response) && (response.id)) {
                                            posiciona_botao(response.id, 'up');
                                        }
                                    });
                                }
                            }
                        });
                    });

                    //order_down
                    var button_order_down = table.find("[template-button='order_down']");
                    button_order_down.attr('template-ref', item.lot_transport_id);
                    button_order_down.click(function () {
                        var id = $(this).attr('template-ref');
                        $.ajax({
                            error: ajaxError,
                            type: "POST",
                            url: "<?= APP_URI ?>lots/change_order/",
                            data: { id: id, type: 'down' },
                            success: function (response) {
                                if (response_validation(response)) {
                                    listar_blocks(function() {
                                        if ((response) && (response.id)) {
                                            posiciona_botao(response.id, 'down');
                                        }
                                    });
                                }
                            }
                        });
                    });
                }

                add_row(table_body, item);
                lot_number = item.lot_number;

                // prepara os botoes de check
                var btn_check_all = table.find('[template-button="check_all"]');
                var btn_uncheck_all = table.find('[template-button="uncheck_all"]');
                var table_btn = table;

                btn_check_all.unbind('click');
                btn_check_all.click(function() {
                    table_btn.find('[type="checkbox"]:not(:disabled)').prop('checked', true).trigger("change");
                });

                btn_uncheck_all.unbind('click');
                btn_uncheck_all.click(function() {
                    table_btn.find('[type="checkbox"]:not(:disabled)').prop('checked', false).trigger("change");
                });
            });  

            
            // mostra a tabela
            table.appendTo(list);
            
            verifica_btns_pt();

            $(".cbo_head_office").select2();

            if (callback_function) {
                callback_function();
            }
        }
    }).fail(ajaxError);
}

function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_block_number = $(new_row.find("[template-field='block_number_a']"));
    field_block_number.text(item.block_number);
    field_block_number.attr('template-ref', item.id);
    field_block_number.click(
        function() {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        }
    );

    var field_nf = $(new_row.find("[template-field='nf']"));
    field_nf.text(item.invoice_item_nf ? item.invoice_item_nf : '');

    var field_tot_c = $(new_row.find("[template-field='tot_c']"));
    field_tot_c.text(item.tot_c);

    var field_tot_a = $(new_row.find("[template-field='tot_a']"));
    field_tot_a.text(item.tot_a);

    var field_tot_l = $(new_row.find("[template-field='tot_l']"));
    field_tot_l.text(item.tot_l);

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text(item.sale_net_c);

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text(item.sale_net_a);

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text(item.sale_net_l);

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_vol']"));
    field_sale_net_vol.text(item.sale_net_vol);

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight);

    var field_destination = $(new_row.find("[template-field='destination']"));

    var destination = item.current_location ? item.current_location : item.next_location;
    destination = destination ? destination : '';
    field_destination.text(destination);

    if (item.client_remove == '1') {
        field_destination.text('Client will remove the block from the quarry');
    }

    var travel_plan_status = item.current_travel_plan_id || item.next_travel_plan_id ? item.current_travel_plan_status : TRAVEL_PLAN_STATUS.COMPLETED;
    travel_plan_status = item.client_remove == '1' ? item.current_travel_plan_status : travel_plan_status;

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(str_travel_plan_status(travel_plan_status));

    switch (parseInt(travel_plan_status, 10))
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

    var field_selected = $(new_row.find("[template-field='selected']"));
    field_selected.attr('template-ref', item.id);
    field_selected.change(function() {
        var selected = $(this).find('[type="checkbox"]').prop('checked');
        var id = $(this).attr('template-ref');

        for (var i = 0; i < arr_pending_blocks.length; i++) {
            if (arr_pending_blocks[i].id == id) {
                arr_pending_blocks[i].selected = selected;
            }
        };

        verifica_btns_pt();
    });

    if (parseInt(travel_plan_status, 10) == TRAVEL_PLAN_STATUS.COMPLETED) {
        field_selected.find('[type="checkbox"]').attr('disabled', true);
    }

    new_row.appendTo(table_body);
}

function btn_pt_client_removed_click()
{
    // verifico se existe algum bloco selecionado sem nf
    var selecionados = 0;
    var item_not_client_remove = false;
    arr_pending_blocks_selected = [];
    for (var i = 0; i < arr_pending_blocks.length; i++) {
        var item = arr_pending_blocks[i];
        if (item.selected === true) {
            selecionados++;
            arr_pending_blocks_selected.push(item);
            if (parseInt(item.client_remove, 10) != 1) {
                item_not_client_remove = true;
            }
        }
    };
    
    // verifico se há item selecionado
    if (selecionados == 0) {
        var vld = new Validation();
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Select at least one block'));
        alert_modal('Validation', vld);
    }
    // alerto caso exista bloco que o cliente não vai remover
    else if (item_not_client_remove) {
        var vld = new Validation();
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Select only blocks that the client will remove'));
        alert_modal('Validation', vld);
    }
    else {
        // abre janela para confirmar o apontamento
        abre_start_shipping(DLG_POINTING_TRAVEL.CLIENT_REMOVED);
    }
}

function btn_pt_start_click()
{
    // verifico se existe algum bloco selecionado sem nf
    var selecionados = 0;
    var item_not_pending = false;
    arr_pending_blocks_selected = [];
    for (var i = 0; i < arr_pending_blocks.length; i++) {
        var item = arr_pending_blocks[i];
        if (item.selected === true) {
            selecionados++;
            arr_pending_blocks_selected.push(item);
            if (parseInt(item.current_travel_plan_status, 10) != TRAVEL_PLAN_STATUS.PENDING) {
                item_not_pending = true;
            }
        }
    };
    
    // verifico se há item selecionado
    if (selecionados == 0) {
        var vld = new Validation();
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Select at least one block'));
        alert_modal('Validation', vld);
    }
    // alerto caso exista bloco com transporte não pendente
    else if (item_not_pending) {
        var vld = new Validation();
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Select only blocks pending shipping'));
        alert_modal('Validation', vld);
    }
    else {
        // abre janela para confirmar o apontamento
        abre_start_shipping(DLG_POINTING_TRAVEL.START_SHIPPING);
    }
}

function btn_pt_complete_click()
{
    // verifico se os blocos selecionados estão com o status iniciado
    var selecionados = 0;
    var item_not_started = false;
    arr_pending_blocks_selected = [];
    for (var i = 0; i < arr_pending_blocks.length; i++) {
        var item = arr_pending_blocks[i];
        if (item.selected === true) {
            selecionados++;
            arr_pending_blocks_selected.push(item);
            if (parseInt(item.current_travel_plan_status, 10) != TRAVEL_PLAN_STATUS.STARTED) {
                item_not_started = true;
            }
        }

    };
    // verifico se há item selecionado
    if (selecionados == 0) {
        var vld = new Validation();
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Select at least one block'));
        alert_modal('Validation', vld);
    }
    // alerto caso exista bloco com transporte não iniciado
    else if (item_not_started) {
        var vld = new Validation();
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Select only blocks with shipping started'));
        alert_modal('Validation', vld);
    }
    // se estiver ok abro janela par confirmar o apontamento
    else {
        abre_start_shipping(DLG_POINTING_TRAVEL.MARK_COMPLETED);
    }
}

function verifica_btns_pt() {
    var btn_pt_client_removed = $('.btn_pt_client_removed');
    var btn_pt_start = $('.btn_pt_start');
    var btn_pt_complete = $('.btn_pt_complete');

    var item_started = false;
    var item_pending = false;
    var item_client_remove = false;

    for (var i = 0; i < arr_pending_blocks.length; i++) {
        var item = arr_pending_blocks[i];
        if (item.selected === true) {
            if ((parseInt(item.current_travel_plan_status, 10) == TRAVEL_PLAN_STATUS.STARTED) && (parseInt(item.client_remove, 10) == 0)) {
                item_started = true;
            }

            if ((parseInt(item.current_travel_plan_status, 10) == TRAVEL_PLAN_STATUS.PENDING) && (parseInt(item.client_remove, 10) == 0)) {
                item_pending = true;
            }

            if (parseInt(item.client_remove, 10) == 1) {
                item_client_remove = true;
            }
        }
    };
    
    if (item_client_remove) {
        btn_pt_client_removed.removeClass('disabled');
    }
    else {
        btn_pt_client_removed.addClass('disabled');
    }

    if (item_started) {
        btn_pt_complete.removeClass('disabled');
    }
    else {
        btn_pt_complete.addClass('disabled');
    }

    if (item_pending) {
        btn_pt_start.removeClass('disabled');
    }
    else {
        btn_pt_start.addClass('disabled');
    }
    
    

}

function posiciona_botao(id, type) {
    var button = $("#list").find("[template-button='order_" + type + "'][template-ref='" + id + "']");
    $(button).focus();
}

init_list();