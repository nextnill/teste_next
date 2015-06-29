var btn_confirm_ok = $('#btn_confirm_ok');
btn_confirm_ok.click(btn_confirm_ok_click);

var btn_confirm_cancel = $('#btn_confirm_cancel');
btn_confirm_cancel.click(btn_confirm_cancel_click);


var cb_get_block_number_count = 0;

var invoice_id = null;

function init_confirm() {
    var blocks_accepted = [];
    var blocks_accepted_interim = [];

    for (var i = 0; i < blocks.length; i++) {
        if ((blocks[i].refused === undefined) || (blocks[i].refused == false)) {
            blocks_accepted.push(blocks[i]);
            if (parseInt(blocks[i].type, 10) == BLOCK_TYPE.INTERIM) {
                blocks_accepted_interim.push(blocks[i]); // copio os blocos provisórios em um array
                if(blocks[i].block_number_interim == null){
                    blocks[i].block_number_interim = blocks[i].block_number;
                }
            }
        }
    };

    // se não existir blocos provisórios, já exibo a lista para confirmação
    if (blocks_accepted_interim.length == 0) {
        render_list_cb(blocks_accepted);
    }
    // caso não exista blocos provisórios, pego novos números de blocos definitivos para eles
    else {
        cb_get_block_number_count = 0;

        $.each(blocks_accepted, function(i, item) {
            if (parseInt(blocks_accepted[i].type, 10) == BLOCK_TYPE.INTERIM) {
                $.ajaxSetup({ cache: false });
                $.getJSON("<?= APP_URI ?>quarry/nextval/final/" + item.quarry_id, function(response) {
                    if (response_validation(response)) {
                        if ((!blocks_accepted[i].block_number_old) || (blocks_accepted[i].block_number_old == '')) {
                            blocks_accepted[i].block_number_old = blocks_accepted[i].block_number;
                        }
                        blocks_accepted[i].block_number = response[0].block_number;
                        cb_get_block_number_count++;
                        if (cb_get_block_number_count == blocks_accepted_interim.length) {
                            render_list_cb(blocks_accepted);
                        }
                    }
                }).fail(ajaxError);
            }
        });
    }
}



function render_list_cb(blocks_accepted) {

    // pesquisa a listagem em json

    var list = $('#list_confirm');
    var table = $('[template-table-confirm]').clone();
    var table_body = $(table).find('table > tbody');
    var quarry_name = '';
    var quality_name = '';
    var block_count = 0;
    var block_net_vol_sum = 0;
    var block_tot_weight_sum = 0;
    var block_all_count = 0;
    var block_all_net_vol_sum = 0;
    var block_all_tot_weight_sum = 0;

    list.html('');

    // limpa trs, menos a primeira
    table.find("tr:gt(1)").remove();
    table.removeAttr("template-table-confirm");
    table.css("display", '');

    $.each(blocks_accepted, function(i, item) {

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
                cb_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
                // mostra a tabela
                table.appendTo(list);
            }
            table = $('[template-table-confirm]').clone();
            table_body = $(table).find('table > tbody');

            table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
            table.removeAttr("template-table-confirm");
            table.css("display", '');
            table.find("[template-title]").text(item.quarry_name);
            table.find("[template-quality]").text(item.quality_name);

            // zero os contadores
            block_count = 0;
            block_net_vol_sum = 0;
            block_tot_weight_sum = 0;
        }

        block_count++;
        block_net_vol_sum += parseFloat(item.sale_net_vol);
        block_tot_weight_sum += parseFloat(item.tot_weight);

        block_all_count++;
        block_all_net_vol_sum += parseFloat(item.sale_net_vol);
        block_all_tot_weight_sum += parseFloat(item.tot_weight);

        cb_add_row(table_body, item);
        quarry_name = item.quarry_name;
        quality_name = item.quality_name;

    });  

    // último registro
    // adiciono o totalizador
    cb_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
    // mostra a tabela
    table.appendTo(list);
    
    // tabela com totalizador geral
    table = $('[template-table-confirm]').clone();
    table_body = $(table).find('table > tbody');

    table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
    table.removeAttr("template-table-confirm");
    table.css("display", '');
    table.find("[template-title]").text('Total');
    table.find("[template-quality]").hide();

	var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(block_all_count.toFixed(0));
    field_block_number.css("text-align", "center");

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text('');

    var field_block_number_interim = $(new_row.find("[template-field='block_number_interim']"));
    field_block_number_interim.text('');

    var field_tot_c = $(new_row.find("[template-field='tot_c']"));
    field_tot_c.text('');

    var field_tot_a = $(new_row.find("[template-field='tot_a']"));
    field_tot_a.text('');

    var field_tot_l = $(new_row.find("[template-field='tot_l']"));
    field_tot_l.text('');

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_vol.text('');

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text('');

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text('');

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text('');

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_vol']"));
    field_sale_net_vol.text(block_all_net_vol_sum.toFixed(3));

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(block_all_tot_weight_sum.toFixed(3));

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text('');

    table.appendTo(list);

    new_row.appendTo(table_body);
	
	showModal('modal_confirm');
}

function cb_listar_blocks(client_id)
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

            cb_blocks = response;

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
                        cb_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
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

                cb_add_row(table_body, item);
                quarry_name = item.quarry_name;
                quality_name = item.quality_name;

            });  

            // último registro
            // adiciono o totalizador
            cb_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
            // mostra a tabela
            table.appendTo(list);
        }
    }).fail(ajaxError);
}


function cb_add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', item.id);

    if (parseInt(item.type, 10) == BLOCK_TYPE.INTERIM) {
        var field_new_block_number = $(new_row.find("[template-field='new_block_number']"));
        field_new_block_number.val(item.block_number);
        field_new_block_number.attr('placeholder', item.block_number);
        field_new_block_number.attr('template-ref', item.id);
    }
    else {
        var field_block_number = $(new_row.find("[template-field='block_number']"));
        field_block_number.text(item.block_number);
        field_block_number.attr('template-ref', item.id);
        field_block_number.click(function() {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        });
    }
    
    var field_block_number_interim = $(new_row.find("[template-field='block_number_interim']"));

    if(item.block_number_interim != null){
        
        field_block_number_interim.text(item.block_number_interim);
    }
    else{

        field_block_number_interim.text('');
    }
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

        for (var i = 0; i < cb_blocks.length; i++) {
            if (cb_blocks[i].id == id) {
                cb_blocks[i].selected = selected;
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

function cb_add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum)
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

    var field_block_number_interim = $(new_row.find("[template-field='block_number_interim']"));
    field_block_number_interim.text('');

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

function btn_confirm_ok_click() {
    // pegar block number dos fields
    for (var i = 0; i < blocks.length; i++) {
        var row = $('#tbl_listagem_confirm > tbody > [template-row-ref="' + blocks[i].id + '"] > td');
        var new_block_number = row.find('[template-field="new_block_number"]');
        if (new_block_number.length > 0) {
            // se estiver em branco, prevalece o valor do objeto do array
            if (new_block_number.val().trim() != '') {
                blocks[i].block_number = new_block_number.val();
            }
        }
    }

    if ((blocks) && (blocks.length > 0)) {
        btn_confirm_ok.addClass('disabled');
        // chamar json de reserva
       
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>inspection/save/",
            data: {
                client_id: <?= $client_id ?>,
                blocks: blocks
            },
            dataType: 'json',
            success: function (response) {
                if (response_validation(response)) {
                    invoice_id = response.invoice_id;
                    closeModal('modal_confirm');
                    window.location = '<?= APP_URI ?>inspection_certificate/detail/' + response.invoice_id;
                }
            }
        });
    }
}

function btn_confirm_cancel_click() {

	closeModal('modal_confirm');
}