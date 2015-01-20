var ab_blocks = [];

function ab_init(client_id)
{
    ab_listar_blocks(client_id);

    $('#btn_add_block_cancel').unbind('click');
    $('#btn_add_block_cancel').click(btn_add_block_cancel_click);

    $('#btn_add_block_add').unbind('click');
    $('#btn_add_block_add').click(ab_add_selected);

    $('#btn_blocks_with_reserves_cancel').unbind('click');
    $('#btn_blocks_with_reserves_cancel').click(btn_blocks_with_reserves_cancel_click);

    $('#btn_blocks_with_reserves_continue').unbind('click');
    $('#btn_blocks_with_reserves_continue').click(btn_blocks_with_reserves_continue_click);
}

function ab_listar_blocks(client_id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>sobracolumay/list/json/final/" + client_id, function(response) {
        if (response_validation(response)) {
            var list = $('#list_add');
            var table = $('[template-table-add]').clone();
            var table_body = $(table).find('table > tbody');
            var quarry_name = '';
            var quality_name = '';
            var block_count = 0;
            var block_net_vol_sum = 0;
            var block_tot_weight_sum = 0;

            ab_blocks = response;

            list.html('');

            // limpa trs, menos a primeira
            table.find("tr:gt(1)").remove();
            table.removeAttr("template-table-add");
            table.css("display", '');

            $.each(response, function(i, item) {

                // se for o primeiro registro, seta o título na tabela
                if (i == 0) {
                    table.find("[template-title]").text(item.quarry_name);
                    table.find("[template-quality]").text(item.quality_name);
                }
                // se for uma nova pedreira
                if (item.quarry_name+item.quality_name != quarry_name+quality_name) {
                    
                    // se não for o primeiro registro
                    if (i > 0) {
                        // adiciono o totalizador
                        ab_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
                        // mostra a tabela
                        table.appendTo(list);
                    }
                    table = $('[template-table-add]').clone();
                    table_body = $(table).find('table > tbody');

                    table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
                    table.removeAttr("template-table-add");
                    table.css("display", '');
                    table.find("[template-title]").text(item.quarry_name);
                    table.find("[template-quality]").text(item.quality_name);

                    // zero os contadores
                    block_count = 0;
                    block_net_vol_sum = 0;
                    block_tot_weight_sum = 0;
                }

                block_count++;
                block_net_vol_sum += parseFloat(item.net_vol);
                block_tot_weight_sum += parseFloat(item.tot_weight);

                ab_add_row(table_body, item);
                quarry_name = item.quarry_name;
                quality_name = item.quality_name;

            });  

            // último registro
            // adiciono o totalizador
            ab_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
            // mostra a tabela
            table.appendTo(list);      
        }
    }).fail(ajaxError);
}


function ab_add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_block_number = $(new_row.find("[template-field='block_number_a']"));
    field_block_number.text(item.block_number);
    field_block_number.attr('template-ref', item.id);
    field_block_number.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        }
    );

    var field_tot_c = $(new_row.find("[template-field='tot_c']"));
    field_tot_c.text(item.tot_c);

    var field_tot_a = $(new_row.find("[template-field='tot_a']"));
    field_tot_a.text(item.tot_a);

    var field_tot_l = $(new_row.find("[template-field='tot_l']"));
    field_tot_l.text(item.tot_l);

    var field_net_c = $(new_row.find("[template-field='net_c']"));
    field_net_c.text(item.net_c);

    var field_net_a = $(new_row.find("[template-field='net_a']"));
    field_net_a.text(item.net_a);

    var field_net_l = $(new_row.find("[template-field='net_l']"));
    field_net_l.text(item.net_l);

    var field_net_vol = $(new_row.find("[template-field='net_vol']"));
    field_net_vol.text(item.net_vol);

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_vol.text(item.sale_net_c);

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

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs);

    var field_reserved = $(new_row.find("[template-field='reserved']"));
    field_reserved.text(item.reserved_client_code ? item.reserved_client_code : '');

    var field_selected = $(new_row.find("[template-field='selected']"));
    field_selected.attr('template-ref', item.id);
    field_selected.change(function() {
        var selected = $(this).find('[type="checkbox"]').prop('checked');
        var id = $(this).attr('template-ref');

        for (var i = 0; i < ab_blocks.length; i++) {
            if (ab_blocks[i].id == id) {
                ab_blocks[i].selected = selected;
            }
        };
    });

    for (var i = 0; i < blocks.length; i++) {
        if (blocks[i].id == item.id) {
            field_selected.find('[type="checkbox"]').prop('checked', true);
            item.selected = true;
        }
    };
    
    new_row.appendTo(table_body);
}

function ab_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(block_count.toFixed(0));
    field_block_number.css("text-align", "center");

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text('');

    var field_tot_c = $(new_row.find("[template-field='tot_c']"));
    field_tot_c.text('');

    var field_tot_a = $(new_row.find("[template-field='tot_a']"));
    field_tot_a.text('');

    var field_tot_l = $(new_row.find("[template-field='tot_l']"));
    field_tot_l.text('');


    var field_net_c = $(new_row.find("[template-field='net_c']"));
    field_net_c.text('');

    var field_net_a = $(new_row.find("[template-field='net_a']"));
    field_net_a.text('');

    var field_net_l = $(new_row.find("[template-field='net_l']"));
    field_net_l.text('');

    var field_net_vol = $(new_row.find("[template-field='net_vol']"));
    field_net_vol.text(block_net_vol_sum.toFixed(3));

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text('');

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text('');

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text('');

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_vol']"));
    field_sale_net_vol.text(block_net_vol_sum.toFixed(3));

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(block_tot_weight_sum.toFixed(3));

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text('');

    var field_reserved = $(new_row.find("[template-field='reserved']"));
    field_reserved.text('');

    var field_selected = $(new_row.find("[template-field='selected']"));
    field_selected.text('');
    
    new_row.appendTo(table_body);
}

function btn_add_block_cancel_click() {
    closeModal('modal_add_block');
}

function ab_add_selected() {
    var qtd = 0;
    var com_reserva = [];

    for (var i = 0; i < ab_blocks.length; i++) {
        var block = ab_blocks[i];
        if (block.selected === true) {
            qtd++;

            if (block.reserved_client_id && block.reserved_client_id > 0) {
                com_reserva.push(block);
            }
        }
    };

    if (com_reserva.length > 0) {
        ab_valid(com_reserva);
    }
    else {
        ab_add_finish();
    }
}

function ab_valid(com_reserva) {

    var tbl_blocks_with_reserves = $('#tbl_blocks_with_reserves');
    var table_body = tbl_blocks_with_reserves.find('tbody');
    var template_row = table_body.find("tr:first");
    
    // limpa trs, menos a primeira
    tbl_blocks_with_reserves.find("tr:gt(1)").remove();

    for (var i = 0; i < com_reserva.length; i++) {
        var bloco = com_reserva[i];

        var new_row = template_row.clone();
        new_row.removeAttr("template-row");
        new_row.css("display", '');

        var block_number = new_row.find("[template-field='block_number']");
        block_number.text(bloco.block_number);

        var quality_name = new_row.find("[template-field='quality_name']");
        quality_name.text(bloco.quality_name);

        var reserved_client_code = new_row.find("[template-field='reserved_client_code']");
        reserved_client_code.text(bloco.reserved_client_code);

        new_row.appendTo(table_body);
    };

    showModal('modal_blocks_with_reserves');
}

function ab_add_finish() {

    // adiciono blocos marcados e não existentes ao blocklist
    for (var i = 0; i < ab_blocks.length; i++) {
        var bloco_selecionado = ab_blocks[i];
        if (bloco_selecionado.selected == true) {
            var ja_existe = false;

            for (var j = 0; j < blocks.length; j++) {
                var bloco = blocks[j];

                if (bloco_selecionado.id == bloco.id) {
                    ja_existe = true;
                }
            };

            if (!ja_existe) {
                bloco_selecionado.adicional = true;
                blocks.push(bloco_selecionado);
            }
        }
    };

    // removo blocks "adicionais" da reserva do cliente, que foram desmarcados do Sobracolumay
    var blocos_remover = [];
    for (var i = 0; i < blocks.length; i++) {
        var bloco = blocks[i];
        if (bloco.adicional) {
            for (var j = 0; j < ab_blocks.length; j++) {
                var bloco_add = ab_blocks[j];
                if (bloco_add.id == bloco.id) {
                    if (bloco_add.selected == false) {
                        blocos_remover.push(bloco);
                    }
                }
            };
        }
    }
    for (var i = 0; i < blocos_remover.length; i++) {
        var bloco_index = blocks.indexOf(blocos_remover[i]);
        blocks.splice(bloco_index, 1);
    }
    
    render_list();

    closeModal('modal_add_block');
}

function btn_blocks_with_reserves_cancel_click() {
    closeModal('modal_blocks_with_reserves');
}

function btn_blocks_with_reserves_continue_click() {
    closeModal('modal_blocks_with_reserves', ['modal_add_block']);
    ab_add_finish();
}