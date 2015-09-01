var blocks = [];
var selected_block = null;

function init()
{
    $('.btn_bl_add').click(btn_add_click);
    $('.btn_bl_finish').click(btn_finish_click);

    listar_blocks();

    // clona menu
    $('#bl_menu_heading').clone(true).contents().appendTo($('#bl_menu_footer'));
}

function listar_blocks()
{
    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>inspection/blocks/json/<?= $client_id . '/' .   $invoice_id ?>", function(response) {
        if (response_validation(response)) {
            blocks = response;
            render_list();
        }
    }).fail(ajaxError);
}

function render_list()
{
    var list = $('#list');
    var table = $('[template-table]').clone();
    var table_body = $(table).find('table > tbody');
    var quarry_name = '';
    var quality_name = '';
    var block_count = 0;
    //var block_net_vol_sum = 0;
    //var block_tot_weight_sum = 0;

    // ordeno os blocos
    blocks = blocks.sort(function(obj1, obj2){
        return obj1.quality_order_number - obj2.quality_order_number;
    });

    list.html('');

    // limpa trs, menos a primeira
    table.find("tr:gt(1)").remove();
    table.removeAttr("template-table");
    table.css("display", '');

    $.each(blocks, function(i, item) {
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
                //add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);

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
            //block_net_vol_sum = 0;
            //block_tot_weight_sum = 0;
        }

        block_count++;
        //block_net_vol_sum += parseFloat(item.net_vol);
        //block_tot_weight_sum += parseFloat(item.tot_weight);

        add_row(table_body, item);
        quarry_name = item.quarry_name;
        quality_name = item.quality_name;

    });  

    // último registro
    // adiciono o totalizador
    //add_footer(table_body, block_count, block_net_vol_sum, block_tot_weight_sum);
    // mostra a tabela
    table.appendTo(list);      
    
    $(".cbo_head_office").select2();

}


function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', item.id);

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(item.block_number);

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name);

    var field_tot_c = $(new_row.find("[template-field='tot_c']"));
    field_tot_c.text(item.tot_c);

    var field_tot_a = $(new_row.find("[template-field='tot_a']"));
    field_tot_a.text(item.tot_a);

    var field_tot_l = $(new_row.find("[template-field='tot_l']"));
    field_tot_l.text(item.tot_l);

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.val(item.sale_net_c > 0 ? item.sale_net_c : item.net_c);
    field_sale_net_c.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.val(item.sale_net_a > 0 ? item.sale_net_a : item.net_a);
    field_sale_net_a.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.val(item.sale_net_l > 0 ? item.sale_net_l : item.net_l);
    field_sale_net_l.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_vol']"));
    field_sale_net_vol.prop("template-ref", item.net_vol);

    var field_sale_net_vol_diff = $(new_row.find("[template-field='sale_net_vol_diff']"));
    var field_sale_net_vol_diff_per = $(new_row.find("[template-field='sale_net_vol_diff_per']"));
    bl_calc_vol(
        field_sale_net_c.val(),
        field_sale_net_a.val(),
        field_sale_net_l.val(),
        field_sale_net_vol,
        field_sale_net_vol_diff,
        field_sale_net_vol_diff_per,
        false
    );

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight);

    var field_client_block_number = $(new_row.find("[template-field='client_block_number']"));
    field_client_block_number.val(item.client_block_number);

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs);



    // calc val liq m3
    field_sale_net_c.unbind('change');
    field_sale_net_c.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        bl_valida_tot_net(item.tot_c, field_sale_net_c, 'top');
        bl_calc_vol(
            field_sale_net_c.val(),
            field_sale_net_a.val(),
            field_sale_net_l.val(),
            field_sale_net_vol,
            field_sale_net_vol_diff,
            field_sale_net_vol_diff_per,
            true
        );
    });

    field_sale_net_a.unbind('change');
    field_sale_net_a.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        bl_valida_tot_net(item.tot_a, field_sale_net_a, 'bottom');
        bl_calc_vol(
            field_sale_net_c.val(),
            field_sale_net_a.val(),
            field_sale_net_l.val(),
            field_sale_net_vol,
            field_sale_net_vol_diff,
            field_sale_net_vol_diff_per,
            true
        );
    });

    field_sale_net_l.unbind('change');
    field_sale_net_l.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        bl_valida_tot_net(item.tot_l, field_sale_net_l, 'right');
        bl_calc_vol(
            field_sale_net_c.val(),
            field_sale_net_a.val(),
            field_sale_net_l.val(),
            field_sale_net_vol,
            field_sale_net_vol_diff,
            field_sale_net_vol_diff_per,
            true
        );
    });



    var field_confirmed = $(new_row.find("[template-field='confirmed'] > select"));
    field_confirmed.attr('template-ref', item.id);
    field_confirmed.attr('template-ref-block-number', item.block_number);
    field_confirmed.attr('template-ref-refuse-reason', item.refused_reason);
    field_confirmed.change(function() {
        var refuse_rec_id = $('#refuse_rec_id');
        refuse_rec_id.val(field_confirmed.attr('template-ref'));
        if ($(this).val() == "0") { // refused
            var edt_refuse_block_number = $('#edt_refuse_block_number');
            var edt_refuse_reason = $('#edt_refuse_reason');
            
            edt_refuse_block_number.val(field_confirmed.attr('template-ref-block-number'));
            edt_refuse_reason.val(field_confirmed.attr('template-ref-refuse-reason'));

            selected_block = $(this);

            var btn_refuse_cancel = $('#btn_refuse_cancel');
            btn_refuse_cancel.unbind('click');
            btn_refuse_cancel.click(function() {
                selected_block.val(1);
                closeModal('modal_refuse');
            });

            showModal('modal_refuse');

            set_focus(edt_refuse_reason);
        }
        else {
            for (var i = 0; i < blocks.length; i++) {
                var block = blocks[i];
                if (blocks[i].id == refuse_rec_id.val()) {
                    blocks[i].refused = false;
                    blocks[i].refused_reason = '';
                }
            };
        }
    });

    new_row.appendTo(table_body);
    new_row.fadeIn('normal', function() {
        bl_valida_tot_net(item.tot_c, field_sale_net_c, 'top');
        bl_valida_tot_net(item.tot_a, field_sale_net_a, 'bottom');
        bl_valida_tot_net(item.tot_l, field_sale_net_l, 'right');
    });
}

function bl_valida_tot_net(edt_tot_val, edt_net, placement) {
    if (!placement) { placement = 'bottom' }

    if (!isNaN(edt_tot_val) && !isNaN(edt_net.val())) {

        if (parseFloat(edt_tot_val) < parseFloat(edt_net.val())) {
            edt_net.tooltip({title: 'Net Meas can not be greater than Tot Meas', placement: placement, trigger: 'manual'});
            edt_net.tooltip('show');
        }
        else {
            edt_net.tooltip('destroy');
        }
    }
}

function bl_calc_vol(val_c, val_a, val_l, text_sale_vol, text_vol_diff, text_vol_diff_per, persist) {
    if (isNaN(val_c)
        || isNaN(val_a)
        || isNaN(val_l))
    {
        text_sale_vol.val('0.000');
        return;
    }

    if ((parseFloat(val_c) == 0)
        || (parseFloat(val_a) == 0)
        || (parseFloat(val_l) == 0))
    {
        text_sale_vol.text('0.000');
        return;
    }

    var result = val_c * val_a * val_l;
    text_sale_vol.text(arredondar3(result));
    var diff = result.toFixed(3) - parseFloat(text_sale_vol.prop("template-ref"));
    var per_diff = (diff / parseFloat(text_sale_vol.prop("template-ref"))) * 100;
    text_vol_diff.text(diff.toFixed(5));
    text_vol_diff_per.text(per_diff.toFixed(5) + '%');
    
    text_vol_diff.removeClass("text-danger");
    text_vol_diff.removeClass("text-muted");
    text_vol_diff_per.removeClass("text-danger");
    text_vol_diff_per.removeClass("text-muted");

    if (diff >= 0) {
        text_vol_diff.addClass("text-muted");
        text_vol_diff_per.addClass("text-muted");
    }
    else if (diff < 0) {
        text_vol_diff.addClass("text-danger");
        text_vol_diff_per.addClass("text-danger");
    }

    if (persist)
        persist_inputs();
}

function persist_inputs() {
    for (var i = 0; i < blocks.length; i++) {
        var row = $('#tbl_listagem > tbody > [template-row-ref="' + blocks[i].id + '"] > td');
        blocks[i].sale_net_a = row.find('[template-field="sale_net_a"]').val();
        blocks[i].sale_net_c = row.find('[template-field="sale_net_c"]').val();
        blocks[i].sale_net_l = row.find('[template-field="sale_net_l"]').val();
        blocks[i].sale_net_vol = row.find('[template-field="sale_net_vol"]').text();
        blocks[i].client_block_number = row.find('[template-field="client_block_number"]').val();
    }
}

function btn_add_click() {
    showModal('modal_add_block');
    ab_init(<?= $client_id ?>);
}

function btn_finish_click() {
    persist_inputs();
    init_confirm();
}

// on load window
funcs_on_load.push(function() {
    init(<?= $invoice_id ?>);
});