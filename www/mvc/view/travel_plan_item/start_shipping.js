var DLG_POINTING_TRAVEL = {
    CLIENT_REMOVED: 1,
    START_SHIPPING: 2,
    MARK_COMPLETED: 3
};

function abre_start_shipping(type)
{
    // altero o titulo da janela
    var modal_start_shipping_label = $('#modal_start_shipping_label');
    switch (type) {
        case DLG_POINTING_TRAVEL.CLIENT_REMOVED:
            modal_start_shipping_label.text('The Client Removed');
            break;
        case DLG_POINTING_TRAVEL.START_SHIPPING:
            modal_start_shipping_label.text('Start Transportation');
            break;
        case DLG_POINTING_TRAVEL.MARK_COMPLETED:
            modal_start_shipping_label.text('Mask as Completed');
            break;
    }


    var btn_client_removed = $('#btn_client_removed');
    var btn_start_shipping = $('#btn_start_shipping');
    var btn_mark_completed = $('#btn_mark_completed');

    // listar_locations(start_quarry_id, start_terminal_id);
    showModal('modal_start_shipping');
    render_start_shipping_list(type);

    // trato os botões
    switch (type) {
        case DLG_POINTING_TRAVEL.CLIENT_REMOVED:
            btn_client_removed.show();
            btn_start_shipping.hide();
            btn_mark_completed.hide();
            break;
        case DLG_POINTING_TRAVEL.START_SHIPPING:
            btn_client_removed.hide();
            btn_start_shipping.show();
            btn_mark_completed.hide();
            break;
        case DLG_POINTING_TRAVEL.MARK_COMPLETED:
            btn_client_removed.hide();
            btn_start_shipping.hide();
            btn_mark_completed.show();
            break;
    }
}

function render_start_shipping_list(type)
{    

    // trato campo de destino caso seja o cliente que irá remover na pedreira
    // eu altero este campo no template, e não na geração da tabela
    var header_destination = $("[template-table='start_shipping'] [template-header='destination']");
    var field_destination = $("[template-table='start_shipping'] [template-field='destination']");
    if (type == DLG_POINTING_TRAVEL.CLIENT_REMOVED) {
        header_destination.hide();
        field_destination.hide();
    }
    else {
        header_destination.show();
        field_destination.show();
    }

    // clono template
    var list = $('#list_start_shipping');
    var table = $('[template-table="start_shipping"]').clone();
    var table_body = $(table).find('table > tbody');
    var lot_number = '';

    // limpa a listagem
    list.html('');

    // limpa trs, menos a primeira
    table.find("tr:gt(1)").remove();
    table.removeAttr("template-table");
    table.css("display", '');

    $.each(arr_pending_blocks_selected, function(i, item) {
        // se for o primeiro registro, seta o título na tabela
        if (i == 0) {
            table.find("[template-title]").text(item.lot_number);
        }
        // se for uma nova pedreira
        if (item.lot_number != lot_number) {
            
            // se não for o primeiro registro
            if (i > 0) {
                // mostra a tabela
                table.appendTo(list);
            }
            table = $('[template-table="start_shipping"]').clone();
            table_body = $(table).find('table > tbody');

            table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
            table.removeAttr("template-table");
            table.css("display", '');
            table.find("[template-title]").text(item.lot_number);
        }

        add_row_shipping_list(table_body, item, type)
        lot_number = item.lot_number;
    });

    // mostra a tabela
    table.appendTo(list);

  
    $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});

    set_focus($('#modal_start_shipping').find('input[type=text],textarea,select').filter(':visible:first'));

}

function add_row_shipping_list(table_body, item, type)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', item.id);

    var field_block_number = $(new_row.find("[template-field='block_number_a']"));
    field_block_number.text(item.block_number);
    field_block_number.attr('template-ref', item.id);
    field_block_number.click(function() {
        var id = $(this).attr('template-ref');
        show_dialog(FORMULARIO.VISUALIZAR, id);
    });

    
    var field_nf = $(new_row.find("[template-field='nf']"));
    var field_nf_edit = $(new_row.find("[template-field='nf_edit']"));
    field_nf_edit.val(item.invoice_item_nf ? item.invoice_item_nf : '');
    // bloqueio edição se já existir número de NF
    if (item.invoice_item_nf) {
        field_nf.text(item.invoice_item_nf);
    }
    
    
    var field_price = $(new_row.find("[template-field='price']"));
    var field_price_edit = $(new_row.find("[template-field='price_edit']"));
    field_price_edit.val(item.invoice_item_price ? item.invoice_item_price : '');
    field_price_edit.unbind('change');
    field_price_edit.change(function() {
        //$(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
    });
    // bloqueio edição se já existir número de NF
    if (item.invoice_item_nf) {
        field_price.text(item.invoice_item_price);
    }

    var terminal_wagon = item.next_terminal_wagon_number;

    if(terminal_wagon == "N" || type == DLG_POINTING_TRAVEL.CLIENT_REMOVED){

        $(new_row.find("[template-field='wagon_number_edit']")).prop("disabled", true);
    }
    
        var field_wagon_number = $(new_row.find("[template-field='wagon_number']"));
        var field_wagon_number_edit = $(new_row.find("[template-field='wagon_number_edit']"));
        field_wagon_number_edit.val(item.current_travel_plan_item_wagon_number ? item.current_travel_plan_item_wagon_number : '');
        // bloqueio edição se já existir número do vagao
        if (item.current_travel_plan_item_wagon_number) {
            field_wagon_number.text(item.current_travel_plan_item_wagon_number);
        }
        

    var field_destination = $(new_row.find("[template-field='destination']"));

    var destination = item.current_location ? item.current_location : item.next_location;
    destination = destination ? destination : '';
    field_destination.text(destination);

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(str_travel_plan_status(item.current_travel_plan_status));

    switch (parseInt(item.current_travel_plan_status, 10))
    {
        case TRAVEL_PLAN_STATUS.PENDING:
            field_status.addClass('label label-default');
            break;
        case TRAVEL_PLAN_STATUS.STARTED:
            field_status.addClass('label label-success');
            break;
        case TRAVEL_PLAN_STATUS.COMPLETED:
            field_status.addClass('label label-info');
            break;
    }

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
    });
    
    new_row.appendTo(table_body);
}

function btn_start_shipping_click(type)
{

    // populo nf e preço com o valor dos fields
    for (var i = 0; i < arr_pending_blocks_selected.length; i++) {
        var row = $('#list_start_shipping table > tbody > [template-row-ref="' + arr_pending_blocks_selected[i].id + '"] > td');

        var nf_edit = row.find('[template-field="nf_edit"]');
        if (nf_edit.length > 0) {
            arr_pending_blocks_selected[i].invoice_item_nf = nf_edit.val();
        }

        var price_edit = row.find('[template-field="price_edit"]');
        if (price_edit.length > 0) {
            arr_pending_blocks_selected[i].invoice_item_price = price_edit.val();
        }

        var wagon_number_edit = row.find('[template-field="wagon_number_edit"]');
        if (wagon_number_edit.length > 0) {
            arr_pending_blocks_selected[i].invoice_item_wagon_number = wagon_number_edit.val();
        }
    }

    // verifico se existe algum bloco sem NF ou preço
    for (var i = 0; i < arr_pending_blocks_selected.length; i++) {
        var without_nf = false;
        var without_price = false;
        var without_wagon_number = false;

        if (!arr_pending_blocks_selected[i].invoice_item_nf) {
            without_nf = true;
        }
        if (!arr_pending_blocks_selected[i].invoice_item_price) {
            without_price = true;
        }

        var terminal_wagon = arr_pending_blocks_selected[i].next_terminal_wagon_number;
        if (terminal_wagon == "S" && !arr_pending_blocks_selected[i].invoice_item_wagon_number) {
            without_wagon_number = true;
        }    	
    }

    // validação
    var vld = new Validation();
    if (without_nf) {
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'There are blocks without NF'));
    }
    if (without_price) {
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'There are blocks without Price'));
    }
    if (without_wagon_number) {
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'There are blocks without Wagon Number'));
    }
    
    if (without_nf || without_price || without_wagon_number) {
        alert_modal('Validation', vld);
    }
    else {
        var url = (type == DLG_POINTING_TRAVEL.START_SHIPPING ? 'start_shipping' : 'client_removed');
        // post
        if (arr_pending_blocks_selected.length > 0) {
            $.ajax({
                error: ajaxError,
                type: "POST",
                url: "<?= APP_URI ?>travel_plan/pending/" + url + "/",
                data: { blocks: JSON.stringify(arr_pending_blocks_selected) },
                success: function (response) {
                    if (response_validation(response)) {
                        closeModal('modal_start_shipping');
                        listar_blocks();
                    }
                    
                }
            });
        }
    }
}

function btn_mark_completed_click()
{
    if (arr_pending_blocks_selected.length > 0) {
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>travel_plan/pending/mark_completed/",
            data: { blocks:  JSON.stringify(arr_pending_blocks_selected) },
            success: function (response) {
                if (response_validation(response)) {
                        closeModal('modal_start_shipping');
                        listar_blocks();
                }
                
            }
        });
    }
}

