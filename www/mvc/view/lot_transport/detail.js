var lot_transport_id = <?= $lot_transport_id ?>;
var lot_transport_status = <?= $lot_transport->status ? $lot_transport->status : $lot_transport::LOT_TRANSPORT_STATUS_DRAFT ?>;
var lot_transport;
var lot_blocks = [];

function init_lots() {
    var lt_detail_title = $('#lt_detail_title');
    var btn_travel_plan = $('#btn_travel_plan');

    // novo lote
    if (lot_transport_id == 0) {
        btn_travel_plan.hide();
    }
    // lote salvo
    else {
        btn_travel_plan.show();
    }

    refresh_btn_release();

    $('.btn_lt_add').click(btn_add_click);
    $('.btn_lt_finish').click(btn_finish_click);

    // clona menu
    $('#bl_menu_heading').clone(true).contents().appendTo($('#bl_menu_footer'));

    listar_lots();
}

function refresh_btn_release() {
    var button_release = $('#btn_release');
    button_release.unbind('click');
    button_release.click(function() {
        
        var release_action = function() {
            closeModal('alert_modal');
            $.ajax({
                error: ajaxError,
                type: "POST",
                url: "<?= APP_URI ?>lots/release/",
                data: {
                    id: lot_transport_id,
                    release: (lot_transport_status == 0 ? true : false)
                },
                dataType: 'json',
                success: function (response) {
                    setTimeout(function() { 
                        if (response_validation(response)) {
                            // desabilito validação de saida da página
                            valid_onunload(false);
                            // recarrego a pagina
                            window.location = '<?= APP_URI ?>lots/detail/' + response.id;
                            alert_saved('Lot ' + response.lot_number + ' ' + (lot_transport_status == 0 ? 'release' : 'undo release') + ' successfully.');
                        }
                    }, 800);
                }
            });
        };
        var type = (lot_transport_status == 0 ? 'Release' : 'Undo release');
        alert_modal('Lot', type + ' ' + $('#edt_lot_number').val() + '?', type, release_action, true);
    });

    if (lot_transport_id == 0) {
        button_release.addClass('disabled');
        button_release.unbind('click');
    }
    else {
        if (lot_transport_status == 0) {
            button_release.removeClass('btn-default');
            button_release.addClass('btn-primary');
        }
        else if (lot_transport_status == 1) {
            button_release.removeClass('btn-default');
            button_release.removeClass('btn-primary');
            button_release.addClass('btn-info');
        }
        else {
            button_release.addClass('disabled');
            button_release.unbind('click');
        }
    }
    
}

function listar_lots()
{
    var edt_lot_number = $('#edt_lot_number');
    set_focus(edt_lot_number);
    if (lot_transport_id > 0)
    {
        // pesquisa a listagem em json
        $.getJSON("<?= APP_URI ?>lots/detail/json/" + lot_transport_id, function(response) {
            if (response_validation(response)) {
                lot_transport = response;
                lot_blocks = response.items;

                render_header();
                render_lots();
            }
        }).fail(ajaxError);
    }
    else {
        edt_lot_number.val('<?= $lot_transport->lot_number ?>');
    }

    edt_lot_number.unbind('change');
    edt_lot_number.change(function() {
        edt_lot_number.tooltip('destroy');
        $(".btn_lt_finish").attr('disabled', true);
        
        // pesquisa a listagem em json
        $.getJSON("<?= APP_URI ?>lots/exists/lot_number/json/", { 'lot_number': $(this).val() }, function(response) {
            if (response_validation(response)) {
                if ((response.exists) && (response.exists > 0) && (response.exists != lot_transport_id)) {
                    edt_lot_number.tooltip({title: 'This lot number is already in use', placement: 'bottom', trigger: 'manual'});
                    edt_lot_number.tooltip('show');
                    $(".btn_lt_finish").attr('disabled', true);
                }
                else {
                    edt_lot_number.tooltip('destroy');
                    $(".btn_lt_finish").attr('disabled', false);
                }
            }            
        }).fail(ajaxError);

        // habilito o botão salvar
        $(".btn_lt_finish").attr('disabled', false);
        $("#btn_release").attr('disabled', true);

        // habilito validação de saida da página
        valid_onunload(true);
    });

    // desabilito o botão salvar
    $(".btn_lt_finish").attr('disabled', true);

    // desabilito validação de saida da página
    valid_onunload(false);
}

function render_header() {
    var edt_lot_number = $('#edt_lot_number');
    var edt_client_name = $('#edt_client_name');
    var btn_client_change = $('#btn_client_change');
    var lbl_status = $('#lbl_status');

    edt_lot_number.val(lot_transport.lot_number);
    
    ab_sel_client_id = lot_transport.client_id;
    ab_sel_client_name = lot_transport.client_name;
    edt_client_name.val(lot_transport.client_name);

    lbl_status.text(str_lot_transport_status(lot_transport.status));

    if (lot_transport.status > 0) {
        $('#bl_menu_heading').hide();
        $('#bl_menu_footer').hide();
        edt_lot_number.prop('readonly', true);
        btn_client_change.attr('disabled', true);
    }
}

function render_lots() {
    // ordeno os blocos
    lot_blocks = lot_blocks.sort(function(obj1, obj2){
        return obj1.invoice_date_record > obj2.invoice_date_record;
    });

    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();

    var table_body = $('#tbl_listagem > tbody');

    $.each(lot_blocks, function(i, item) {
        add_row(table_body, item);
    });
}

function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    //new_row.css("display", '');

    new_row.attr('template-row-ref', item.block_id);

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

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight);


    var field_destination = $(new_row.find("[template-field='destination']"));
    field_destination.text(item.last_end_location ? item.last_end_location : '');

    if (item.client_remove == '1') {
        field_destination.text('Client will remove the block from the quarry');
    }

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(item.last_end_location ? str_travel_plan_status(item.status) : '');

    if (item.last_end_location) {
        switch (parseInt(item.status, 10)) {
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
    }

    var field_dismembered = $(new_row.find("[template-field='dismembered']"));
    field_dismembered.text(item.dismembered_lot_transport_lot_number ? 'Dismembered for lot ' + item.dismembered_lot_transport_lot_number : '');
    if (item.dismembered_lot_transport_lot_number) {
        field_dismembered.addClass('label label-warning');
        field_status.hide();
    }   

    var button_remove = $(new_row.find("[template-button='remove']"));
    if ((typeof lot_transport !== 'undefined' && lot_transport.status > 0) || (item.dismembered_lot_transport_lot_number)) {
        button_remove.hide();
    }
    else {
        button_remove.attr('template-ref', item.block_id);
        button_remove.click(
            function () {
                var id = $(this).attr('template-ref');
                
                // removo blocks "adicionais" da reserva do cliente, que foram desmarcados do Sobracolumay
                var bloco_remover = null;
                
                for (var i = 0; i < lot_blocks.length; i++) {
                    var bloco = lot_blocks[i];
                    if (bloco.block_id == id) {
                        bloco_remover = bloco;
                    }
                }

                if (bloco_remover) {

                    var remove_block = function () {
                        var bloco_index = lot_blocks.indexOf(bloco_remover);
                        lot_blocks.splice(bloco_index, 1);
                        
                        var localizado = false;
                        // removo do array de blocos
                        for (var i = 0; i < ab_blocks.length; i++) {
                            if (ab_blocks[i].block_id == bloco_remover.block_id) {
                                ab_blocks[i].selected = false;
                                localizado = true;
                            }
                        };

                        // se não foi localizado, adiciona na lista de blocos a serem adicionados
                        if (!localizado) {
                            ab_blocks.push(bloco_remover);

                            var table_body = $('#table_block_list_add > tbody');
                            ab_add_row(table_body, bloco_remover);
                        }

                        // removo da table
                        var row = $('#tbl_listagem > tbody > [template-row-ref="' + bloco_remover.block_id + '"]');
                        row.fadeOut('normal', function() {
                            $(this).remove();
                        });

                        // habilito o botão salvar
                        $(".btn_lt_finish").attr('disabled', false);
                        $("#btn_release").attr('disabled', true);

                        // habilito validação de saida da página
                        valid_onunload(true);

                        closeModal('alert_modal');
                    }

                    alert_modal('Remove Block', 'Remove block ' + bloco_remover.block_number + ' ?', 'Yes, remove this block', remove_block, true);
                    
                }

            }
        );
    }
    

    new_row.appendTo(table_body);
    new_row.fadeIn('normal');
}

function btn_add_click() {
    if (ab_sel_client_id && ab_sel_client_name) {
        ab_init(ab_sel_client_id, ab_sel_client_name);
    }
    else {
        cl_listar();
    }
}

function valida_lots()
{
    var edt_lot_number = $('#edt_lot_number');

    var valido = true;
    var vld = new Validation();

    if (lot_blocks.length == 0) {
        valido = false;
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Add at least one block to save the lot'));
    }

    if (edt_lot_number.val().length == 0)
    {
        valido = false;
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the lot number'));
    }

    if (!valido)
    {
        alert_modal('Validation', vld);
    }

    return valido;
}


function btn_finish_click() {

    if (valida_lots()) {
        var edt_lot_number = $('#edt_lot_number');

        // trato botões
        $(".btn_lt_finish").attr('disabled', true);

        // chamar json de reserva
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>lots/save/",
            data: {
                id: lot_transport_id,
                lot_number: edt_lot_number.val(),
                client_id: ab_sel_client_id,
                blocks: lot_blocks
            },
            dataType: 'json',
            success: function (response) {
                if (response_validation(response)) {

                    // se for novo, recarrega a página
                    if (lot_transport_id == 0) {
                        // desabilito validação de saida da página
                        valid_onunload(false);
                        // recarrego a pagina
                        window.location = '<?= APP_URI ?>lots/detail/' + response.id;
                        alert_saved('Lot ' + response.lot_number + ' saved successfully.');
                    }
                    else {
                        // exibir alerta 
                        var dt = new Date();
                        $('.log_save').hide().html('Saved at ' + dt.timeNow()).fadeIn('slow', function() {
                            $(this).fadeOut(1000);
                        });

                        // desabilito o botão salvar
                        $(".btn_lt_finish").attr('disabled', true);
                        $("#btn_release").attr('disabled', false);

                        // desabilito validação de saida da página
                        valid_onunload(false);
                    }

                }
            }
        });

    }
}

// on load window
funcs_on_load.push(function() {
    init_lots();
});