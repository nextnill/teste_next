function init_invoice() {
    listar_invoices();
}

function listar_invoices() {
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>inspection_certificate/detail/blocks/json/<?= $invoice_id ?>", processa_invoices).fail(ajaxError);
}

function processa_invoices(response) {
    if (response_validation(response)) {
        var list = $('#list_invoice');
        var table = $('[template-table-invoice]').clone();
        var table_body = $(table).find('table > tbody');
        var quarry_name = '';
        var quality_name = '';
        var block_count = 0;
        var block_price_sum = 0;
        var block_net_vol_sum = 0;
        var block_tot_weight_sum = 0;
        var block_all_count = 0;
        var block_all_price_sum = 0;
        var block_all_net_vol_sum = 0;
        var block_all_tot_weight_sum = 0;

        var blocks = response;

        list.html('');

        // limpa trs, menos a primeira
        table.find("tr:gt(1)").remove();
        table.removeAttr("template-table-invoice");
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
                    ab_add_footer(table_body, block_count, block_price_sum, block_net_vol_sum, block_tot_weight_sum);
                    // mostra a tabela
                    table.appendTo(list);
                }
                table = $('[template-table-invoice]').clone();
                table_body = $(table).find('table > tbody');

                table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
                table.removeAttr("template-table-invoice");
                table.css("display", '');
                table.find("[template-title]").text(item.quarry_name);
                table.find("[template-quality]").text(item.quality_name);

                // zero os contadores
                block_count = 0;
                block_net_vol_sum = 0;
                block_tot_weight_sum = 0;
            }

            block_count++;
            block_price_sum += !item.price || isNaN(item.price) ? 0 : parseFloat(item.price);
            block_net_vol_sum += parseFloat(item.sale_net_vol);
            block_tot_weight_sum += parseFloat(item.tot_weight);

            block_all_count++;
            block_all_price_sum += !item.price || isNaN(item.price) ? 0 : parseFloat(item.price);
            block_all_net_vol_sum += parseFloat(item.sale_net_vol);
            block_all_tot_weight_sum += parseFloat(item.tot_weight);

            ab_add_row(table_body, item);
            quarry_name = item.quarry_name;
            quality_name = item.quality_name;

        });  

        // último registro
        // adiciono o totalizador
        ab_add_footer(table_body, block_count, block_price_sum, block_net_vol_sum, block_tot_weight_sum);
        // mostra a tabela
        table.appendTo(list);
        
        // tabela com totalizador geral
        table = $('[template-table-invoice]').clone();
        table_body = $(table).find('table > tbody');

        table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
        table.removeAttr("template-table-invoice");
        table.css("display", '');
        table.find("[template-title]").text('Total');
        table.find("[template-quality]").hide();

        var template_row = table_body.find("tr:first");
        var new_row = template_row.clone();
        new_row.removeAttr("template-row");
        new_row.css("display", '');

        var field_nf = $(new_row.find("[template-field='nf']"));
        field_nf.text('');

        var field_date_production = $(new_row.find("[template-field='date_production']"));
        field_date_production.text('');

        var field_price = $(new_row.find("[template-field='price']"));
        field_price.text(block_all_price_sum.format_number(2));

        var field_block_number = $(new_row.find("[template-field='block_number']"));
        field_block_number.text(block_all_count.format_number(0));
        field_block_number.css("text-align", "center");

        var field_quality_name = $(new_row.find("[template-field='quality_name']"));
        field_quality_name.text('');

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
        field_sale_net_vol.text(block_all_net_vol_sum.format_number(3));

        var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
        field_tot_weight.text(block_all_tot_weight_sum.format_number(3));

        var field_obs = $(new_row.find("[template-field='obs']"));
        field_obs.text('');

        table.appendTo(list);

        new_row.appendTo(table_body);
    }
}

function ab_add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_nf = $(new_row.find("[template-field='nf']"));
    field_nf.text(item.nf ? item.nf : '');

    var field_date_production = $(new_row.find("[template-field='date_production']"));
    field_date_production.text(item.date_production.format_date());

    var field_price = $(new_row.find("[template-field='price']"));
    field_price.text(item.price ? item.price.format_number(2) : '');

    var field_block_number = $(new_row.find("[template-field='block_number_a']"));
    field_block_number.text(item.block_number);
    field_block_number.attr('template-ref', item.block_id);
    field_block_number.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        }
    );

    var field_tot_c = $(new_row.find("[template-field='tot_c']"));
    field_tot_c.text(item.tot_c.format_number(2));

    var field_tot_a = $(new_row.find("[template-field='tot_a']"));
    field_tot_a.text(item.tot_a.format_number(2));

    var field_tot_l = $(new_row.find("[template-field='tot_l']"));
    field_tot_l.text(item.tot_l.format_number(2));

    var field_net_c = $(new_row.find("[template-field='net_c']"));
    field_net_c.text(item.net_c.format_number(2));

    var field_net_a = $(new_row.find("[template-field='net_a']"));
    field_net_a.text(item.net_a.format_number(2));

    var field_net_l = $(new_row.find("[template-field='net_l']"));
    field_net_l.text(item.net_l.format_number(2));

    var field_net_vol = $(new_row.find("[template-field='net_vol']"));
    field_net_vol.text(item.net_vol.format_number(3));

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text(item.sale_net_c.format_number(2));

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text(item.sale_net_a.format_number(2));

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text(item.sale_net_l.format_number(2));

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_vol']"));
    field_sale_net_vol.text(item.sale_net_vol.format_number(3));

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight.format_number(3));

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs);
    
    new_row.appendTo(table_body);
}

function ab_add_footer(table_body, block_count, block_price_sum, block_net_vol_sum, block_tot_weight_sum)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_nf = $(new_row.find("[template-field='nf']"));
    field_nf.text('');

    var field_date_production = $(new_row.find("[template-field='date_production']"));
    field_date_production.text('');

    var field_price = $(new_row.find("[template-field='price']"));
    field_price.text(block_price_sum.format_number(2));

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(block_count.format_number(0));
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
    field_net_vol.text(block_net_vol_sum.format_number(3));

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text('');

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text('');

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text('');

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_vol']"));
    field_sale_net_vol.text(block_net_vol_sum.format_number(3));

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(block_tot_weight_sum.format_number(3));

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text('');

    var field_reserved = $(new_row.find("[template-field='reserved']"));
    field_reserved.text('');

    var field_selected = $(new_row.find("[template-field='selected']"));
    field_selected.text('');
    
    new_row.appendTo(table_body);
}

// on load window
funcs_on_load.push(function() {
    init_invoice();
});