var head_office = [];
var selected_combo = null;

function init()
{
    listar_head_office();
}

function listar_head_office()
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_head_office/json/", function(response) {
        if (response_validation(response)) {
            $.each(response, function(i, item) {
                head_office.push(item);
            });
            
            listar_blocks();
        }
    }).fail(ajaxError);
}

function listar_blocks()
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>sobracolumay/list/json/<?= $type ?>", function(response) {
        if (response_validation(response)) {
            var list = $('#list');
            var table = $('[template-table]').clone();
            var table_body = $(table).find('table > tbody');
            var quarry_name = '';
            var quality_name = '';
            var block_count = 0;
            var block_net_vol_sum = 0;
            var block_tot_weight_sum = 0;

            list.html('');

            // limpa trs, menos a primeira
            table.find("tr:gt(1)").remove();
            table.removeAttr("template-table");
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
                        add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
                        // mostra a tabela
                        table.appendTo(list);
                    }
                    table = $('[template-table]').clone();
                    table_body = $(table).find('table > tbody');

                    table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
                    table.removeAttr("template-table");
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

                add_row(table_body, item);
                quarry_name = item.quarry_name;
                quality_name = item.quality_name;

            });  

            // último registro
            // adiciono o totalizador
            add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
            // mostra a tabela
            table.appendTo(list);      
            
            $(".cbo_head_office").select2();
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
    field_block_number.click(function() {
        var id = $(this).attr('template-ref');
        show_dialog(FORMULARIO.VISUALIZAR, id);
    });

    var button_delete = $(new_row.find('#delete_block'));
    button_delete.attr('id', item.id);
    button_delete.click(
        function () {
            var id = $(this).attr('id');
            show_dialog(FORMULARIO.EXCLUIR, id);
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

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight);

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs);

    //var field_reserved = $(new_row.find("[template-field='reserved']"));
    //field_reserved.text(item.reserved_client_code ? item.reserved_client_code : '');
    
    

    var cbo_reserved_client = $(new_row.find("[template-field='reserved'] > select"));
    cbo_reserved_client.find("option").remove();

    add_option(cbo_reserved_client, '-1', 'None');
    for (var i = 0; i < head_office.length; i++) {
        var ho_item = head_office[i];
        add_option(cbo_reserved_client, ho_item.id, ho_item.code);
    };
    
    if (item.reserved_client_id)
        cbo_reserved_client.val(item.reserved_client_id).trigger("change");

    cbo_reserved_client.attr('template-ref', item.id);
    cbo_reserved_client.attr('template-ref-bn', item.block_number);
    cbo_reserved_client.attr('template-ref-default', item.reserved_client_id);
    cbo_reserved_client.attr('template-ref-active', "false");

    cbo_reserved_client.on("change", function(e) {
        // se nao tiver nenhuma janela de reserva ativa
        if ($(this).attr('template-ref-active') == "false") {
            // marca janela como ativa
            $(this).attr('template-ref-active', "true");

            var id = $(this).attr('template-ref');
            var select_default = $(this).attr('template-ref-default');
            var select_val = $(this).val();
            var block_number = $(this).attr('template-ref-bn');
            var modal_reserve_label = $('#modal_reserve_label');
            var edt_block_number = $('#edt_reserve_block_number');
            var edt_client_block_number = $('#edt_reserve_client_block_number');
            var div_client_block_number = $('#reserve_client_block_number');
            var btn_reserve_save = $('#btn_reserve_save');
            var btn_reserve_cancel = $('#btn_reserve_cancel');

            btn_reserve_save.css('');
            btn_reserve_save.removeClass();

            if (select_val == "-1") {
                $('#modal_reserve_label').text('Clear reserve');
                div_client_block_number.hide();
                btn_reserve_save.text('Clear');
                btn_reserve_save.addClass('btn btn-danger');
            }
            else {
                $('#modal_reserve_label').text('Reserve to ' + $(this).find('option:selected').text());
                div_client_block_number.show();
                btn_reserve_save.text('Save');
                btn_reserve_save.addClass('btn btn-primary');
            }
            
            edt_block_number.val(block_number);
            selected_combo = this;
            
            btn_reserve_save.unbind('click');
            btn_reserve_save.click(function() {
                if (selected_combo) {
                    var edt_client_block_number = $('#edt_reserve_client_block_number');

                    // chamar json de reserva
                    $.ajax({
                        error: ajaxError,
                        type: "POST",
                        url: "<?= APP_URI ?>block/reserve/",
                        data: {
                            id: $(selected_combo).attr('template-ref'),
                            client_block_number: edt_client_block_number.val(),
                            reserved_client_id: $(selected_combo).val()
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response_validation(response)) {
                                closeModal('modal_reserve');
                                $(selected_combo).attr('template-ref-default', $(selected_combo).val());
                                // desmarca janela como ativa
                                $(selected_combo).attr('template-ref-active', "false");
                            }
                        }
                    });

                }
            });

            select_default = (select_default ? select_default : '-1');
            btn_reserve_cancel.attr('template-ref-default', select_default);
            btn_reserve_cancel.unbind('click');
            btn_reserve_cancel.click(function() {
                if (selected_combo) {
                    closeModal('modal_reserve');

                    $(selected_combo).val($(this).attr('template-ref-default'));
                    $(selected_combo).change();
                    // desmarca janela como ativa
                    $(selected_combo).attr('template-ref-active', "false");
                }
            });

            showModal('modal_reserve');
        }
    });
    
    
    new_row.appendTo(table_body);
}

function add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum)
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

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(block_tot_weight_sum.toFixed(3));

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text('');

    var field_reserved = $(new_row.find("[template-field='reserved']"));
    field_reserved.text('');

    var button_edit = new_row.find("[template-button='reserved']");
    button_edit.hide();
    
    new_row.appendTo(table_body);
}



// on load window
funcs_on_load.push(function() {
    init();
});