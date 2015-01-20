var ab_blocks = [];
var ab_sel_client_id = null;
var ab_sel_client_name = null;

function ab_init(client_id, client_name)
{
    if ((ab_sel_client_id) && (ab_sel_client_id != client_id)) {

        var change_client = function() {
            ab_sel_client_id = null;
            ab_sel_client_name = '';
            lot_blocks = [];
            render_lots();
            ab_init(client_id, client_name);
            closeModal('alert_modal');
        }

        alert_modal('Remove Block', 'You can not add blocks of different clients in the same lot. <br>If you proceed, the block list will be cleared.',
                                    'Yes, change the client and clear the list of blocks', change_client, true);
    }
    else {
        ab_listar_blocks(client_id);
        ab_sel_client_id = client_id;
        ab_sel_client_name = client_name;

        $('#edt_client_name').val(client_name);

        showModal('modal_add_block');

        $('#modal_add_block_client_name').text(client_name);

        $('#btn_add_block_cancel').unbind('click');
        $('#btn_add_block_cancel').click(btn_add_block_cancel_click);

        $('#btn_add_block_add').unbind('click');
        $('#btn_add_block_add').click(ab_add_selected);
    }
}

function ab_listar_blocks(client_id)
{   
    // prepara cabeçalho

    var btn_check_all = $('#table_block_list_add').find('[template-button="check_all"]');
    var btn_uncheck_all = $('#table_block_list_add').find('[template-button="uncheck_all"]');

    btn_check_all.unbind('click');
    btn_check_all.click(btn_add_block_check_click);

    btn_uncheck_all.unbind('click');
    btn_uncheck_all.click(btn_add_block_uncheck_click);
    
    // limpa trs, menos a primeira
    //
    $('#table_block_list_add').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>block/without_lot/" + client_id + "/", function(response) {
        if (response_validation(response)) {
            ab_blocks = response;

            var table_body = $('#table_block_list_add > tbody');

            $.each(ab_blocks, function(i, item) {
                ab_add_row(table_body, item);
            });

            // checkbox

            $('[template-field="selected"]').each(function(i, item) {
                $(item).find('[type="checkbox"]').prop('checked', false);

                var ref = $(item).attr('template-ref');
                if (ref !== undefined) {
                    for (var j = 0; j < lot_blocks.length; j++) {
                        if (lot_blocks[j].block_id == ref) {
                            $(item).find('[type="checkbox"]').prop('checked', true);
                        }
                    };

                }
            });
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
    field_block_number.attr('template-ref', item.block_id);
    field_block_number.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        }
    );

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name);

    var field_date_production = $(new_row.find("[template-field='date_production']"));
    field_date_production.text(item.date_production.format_date());

    var field_invoice_id = $(new_row.find("[template-field='invoice_id']"));
    field_invoice_id.text('#' + item.invoice_id);

    var field_invoice_date_record = $(new_row.find("[template-field='invoice_date_record']"));
    field_invoice_date_record.text(item.invoice_date_record.format_date());

    var field_selected = $(new_row.find("[template-field='selected']"));
    field_selected.attr('template-ref', item.block_id);
    field_selected.change(function() {
        var selected = $(this).find('[type="checkbox"]').prop('checked');
        var id = $(this).attr('template-ref');

        for (var i = 0; i < ab_blocks.length; i++) {
            if (ab_blocks[i].block_id == id) {
                ab_blocks[i].selected = selected;
            }
        };
    });
    
    new_row.appendTo(table_body);
}

function btn_add_block_check_click() {
    $('#table_block_list_add').find('[type="checkbox"]').prop('checked', true).trigger("change");
}

function btn_add_block_uncheck_click() {
    $('#table_block_list_add').find('[type="checkbox"]').prop('checked', false).trigger("change");
}

function btn_add_block_cancel_click() {
    closeModal('modal_add_block');
}

function ab_add_selected() {
    ab_add_finish();
}

function ab_add_finish() {

    // adiciono blocos marcados e não existentes ao blocklist
    for (var i = 0; i < ab_blocks.length; i++) {
        var bloco_selecionado = ab_blocks[i];
        if (bloco_selecionado.selected == true) {
            var ja_existe = false;

            for (var j = 0; j < lot_blocks.length; j++) {
                var bloco = lot_blocks[j];

                if (bloco_selecionado.block_id == bloco.block_id) {
                    ja_existe = true;
                }
            };

            if (!ja_existe) {
                //add_row($('#tbl_listagem > tbody'), bloco_selecionado);
                bloco_selecionado.adicional = true;
                lot_blocks.push(bloco_selecionado);
            }
        }
    };

    // removo blocks "adicionais" da reserva do cliente, que foram desmarcados do Sobracolumay
    var blocos_remover = [];
    for (var i = 0; i < lot_blocks.length; i++) {
        var bloco = lot_blocks[i];
        if (bloco.adicional) {
            for (var j = 0; j < ab_blocks.length; j++) {
                var bloco_add = ab_blocks[j];
                if (bloco_add.block_id == bloco.block_id) {
                    if (bloco_add.selected == false) {
                        blocos_remover.push(bloco);
                    }
                }
            };
        }
    }
    for (var i = 0; i < blocos_remover.length; i++) {
        var bloco_index = lot_blocks.indexOf(blocos_remover[i]);
        lot_blocks.splice(bloco_index, 1);
        
        // removo da table
        //var row = $('#tbl_listagem > tbody > [template-row-ref="' + blocos_remover[i].block_id + '"]');
        //row.fadeOut('normal', function() {
        //    $(this).remove();
        //});
    }

    render_lots();

    // habilito o botão salvar
    $(".btn_lt_finish").attr('disabled', false);
    $("#btn_release").attr('disabled', true);

    // habilito validação de saida da página
    valid_onunload(true);
    
    closeModal('modal_add_block');
}