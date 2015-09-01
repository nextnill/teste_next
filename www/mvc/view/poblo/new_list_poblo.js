
//elements
var div_list = $('#div_list');
var template_lot = $('[template-lot]');
var template_sobracolumay = $('[template-sobracolumay]');
var template_inspection = $('[template-inspection]');
var divisoria = $('<hr>').css('border-color', '#8b0305').css('border-width', '8px');

//vars
var cbo_filter_client = $('#cbo_filter_client');
var lot_number = null;
var quarry_name = null;
var inspection_name = null;
var poblo_status = null;
var reserved_client_code = [];
var colors_sobra_background = new Array();
var client_color = null;


// on load window
funcs_on_load.push(function() {

    render_poblo_status();
    init_list();
    get_data_poblo();
    listar_filter_client();


});

function listar_filter_client()
{

    cbo_filter_client.unbind('change');
    cbo_filter_client.change(function() {
        get_data_poblo();
    });

    cbo_filter_client.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_head_office/json/", function(response) {
        if (response_validation(response)) {
            add_option(cbo_filter_client, '-1', 'None');
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_filter_client, item.id, item.code + ' - ' + item.name);
            };

            cbo_filter_client.select2();
        }
    }).fail(ajaxError);
}


function get_data_poblo (){

    $.getJSON("<?= APP_URI ?>poblo/json/"  + (cbo_filter_client.val() ? cbo_filter_client.val() : '') , function(response_poblo) {

        div_list.html('');
        
        divisoria.clone().appendTo(div_list);
        load_sobracolumay(response_poblo.sobracolumay);
        divisoria.clone().appendTo(div_list);
        load_inspection_certificate(response_poblo.inspection_certificate);
        divisoria.clone().appendTo(div_list);
        load_lot(response_poblo.lot);
       

    });
}

function load_inspection_certificate (inspection)
{
    var new_template_inspection = '';
    var quality_name = '';

    var count_blocks_final = 0;
    var count_quality_blocks_final = 0;
    var sum_volume_final = 0;
    var sum_weight_final = 0;

    var count_blocks = 0;
    var count_quality_blocks = 0;
    var sum_volume = 0;
    var sum_weight = 0;

    $(inspection).each(function(i , item){

        if(i == 0){

            new_template_inspection = template_inspection.clone();
            render_header_inspection(new_template_inspection, item);
            inspection_name = item.inspection_name;
            quality_name = item.quality_name;

        }

        if(item.inspection_name != inspection_name){

            var block_count = {
                net_vol: sum_volume,
                tot_weight: sum_weight,
                block_number: count_blocks,
                quality_name: count_quality_blocks,
            }

            render_inspection(new_template_inspection, block_count, 'bg-warning');

            var block_count_final = {
                net_vol: sum_volume_final,
                tot_weight: sum_weight_final,
                block_number: count_blocks_final,
                quality_name: count_quality_blocks_final,
            }

            render_inspection(new_template_inspection, block_count_final, 'bg-info');

            count_blocks_final = 0;
            count_quality_blocks_final = 0;
            sum_volume_final = 0;
            sum_weight_final = 0;

            count_blocks = 0;
            count_quality_blocks = 0;
            sum_volume = 0;
            sum_weight = 0;

            new_template_inspection = template_inspection.clone();
            render_header_inspection(new_template_inspection, item);
            inspection_name = item.inspection_name;
            quality_name = item.quality_name;
        }

        if(quality_name != item.quality_name){

            var block_count = {
                net_vol: sum_volume,
                tot_weight: sum_weight,
                block_number: count_blocks,
                quality_name: count_quality_blocks,
            }

            render_inspection(new_template_inspection, block_count, 'bg-warning');

            quality_name = item.quality_name;
            count_blocks = 0;
            count_quality_blocks = 0;
            sum_volume = 0;
            sum_weight = 0;

        } 
           
        

        count_blocks_final++;
        count_quality_blocks_final++;
        sum_volume_final += parseFloat(item.net_vol) || 0;
        sum_weight_final += parseFloat(item.tot_weight) || 0;

        count_blocks++;
        count_quality_blocks++;
        sum_volume += parseFloat(item.net_vol) || 0;
        sum_weight += parseFloat(item.tot_weight) || 0;

        render_inspection(new_template_inspection, item);

        if(i >= inspection.length -1){

            var block_count = {
                net_vol: sum_volume,
                tot_weight: sum_weight,
                block_number: count_blocks,
                quality_name: count_quality_blocks,
            }

            render_inspection(new_template_inspection, block_count, 'bg-warning');


            var block_count_final = {
                net_vol: sum_volume_final,
                tot_weight: sum_weight_final,
                block_number: count_blocks_final,
                quality_name: count_quality_blocks_final,
            }

            render_inspection(new_template_inspection, block_count_final, 'bg-info');

        }

        new_template_inspection.appendTo(div_list);

    });
}

function render_header_inspection(new_template_inspection, item){

    // limpa trs, menos a primeira
    new_template_inspection.find('tbody').find("tr:gt(1)").remove();
    new_template_inspection.removeAttr("template-inspection");
    new_template_inspection.css("display", '');

    var field_inspection = new_template_inspection.find('[template-field="inspection"]');
    field_inspection.text(item.inspection_name);
    
}

function render_inspection(new_template_inspection, item, color){

    var table_inspection = $(new_template_inspection.find('.table_block_list'));
    var table_body_inspection = $(table_inspection).find('tbody');
    var template_row = table_body_inspection.find("tr:first");
    
    
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    if(color){
        new_row.addClass(color);
    }

    var field_date = new_row.find('[template-field="date"]');
    field_date.text(item.invoice_date_record ? item.invoice_date_record.format_date() : '');

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(item.block_number);
    field_block_number.addClass('color_client');
    field_block_number.attr('client_id', item.reserved_client_id);

    field_block_number.unbind('click');
    field_block_number.click(function() {
        show_dialog(FORMULARIO.VISUALIZAR, item.id);
    });


    var field_block_number_row = $(new_row.find("[template-row='block_number']"));
    field_block_number_row.css('background-color', item.cor_poblo_status);
    field_block_number_row.css('color', item.cor_poblo_status_texto);

    var block_number_selected = item.invoice_item_id;
    var ul_listagem = $(new_row.find('.ul_listagem'));
    var li = ul_listagem.find('[template-row]');
    var add_li = function(poblo_status_item){

        var new_li = li.clone();
        new_li.removeAttr('template-row');
        new_li.css("display", '');

        var field_poblo_status_option = $(new_li.find("[template-field='poblo_status_option']"));
        field_poblo_status_option.text(poblo_status_item.status);

        new_li.unbind('click');
        new_li.click(function(){
       
            field_block_number_row.css('background-color', poblo_status_item.cor);

            $.ajax({
                error: ajaxError,
                type: "POST",
                url: "<?= APP_URI ?>poblo_status/save_color/",
                data: {
                    invoice_item_id: block_number_selected,
                    poblo_status_id: poblo_status_item.poblo_status_id
                }
            });
        });

        new_li.appendTo(ul_listagem);
    }

    $(poblo_status).each(function(j, poblo_status_item){
        add_li(poblo_status_item);
    });

    var div_status = $(new_row.find(".div_status"));
    if(color){
        div_status.addClass('hidden');
    }


    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name || '');

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text(item.net_c ? item.invoice_sale_net_c.format_number(2) : '');
    
    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text(item.net_a ? item.invoice_sale_net_a.format_number(2) : '');

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text(item.net_l ? item.invoice_sale_net_l.format_number(2) : '');

    var field_net_vol = $(new_row.find("[template-field='net_vol']"));
    field_net_vol.text(item.net_vol ? item.net_vol.format_number(3) : '');

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight ? item.tot_weight.format_number(3) : '');

    var field_client = $(new_row.find("[template-field='client']"));
    field_client.text(item.sold_client_code ? item.sold_client_code : '');

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs_poblo ? item.obs_poblo : '');

    var btn_obs = $(new_row.find("[template-button='obs']"));
    btn_obs.unbind('click');
    btn_obs.click(function() {

        var callback = function(obs){
            field_obs.text(obs);
        }

        show_poblo_obs(item.block_id, item.block_number, callback);

    });

    var btn_edit = $(new_row.find("[template-button='btn_edit']"));
    btn_edit.unbind('click');
    btn_edit.click(function() {

        
        show_poblo_edit(item.block_id, item.invoice_item_id, null, item.invoice_item_nf, item.invoice_item_date_nf, item.invoice_item_price, null, 'insp');
    });
    
    if(color){
        btn_edit.addClass('hidden');
        btn_obs.addClass('hidden');
    }

    new_row.appendTo(table_body_inspection);

}


function load_sobracolumay (sobracolumay)
{
    var new_template_sobracolumay = '';
    var quality_name = '';

    var count_blocks_final = 0;
    var count_quality_blocks_final = 0;
    var sum_volume_final = 0;
    var sum_weight_final = 0;

    var count_blocks = 0;
    var count_quality_blocks = 0;
    var sum_volume = 0;
    var sum_weight = 0;

    $(sobracolumay).each(function(i , item){

        if(i == 0){

            new_template_sobracolumay = template_sobracolumay.clone();
            render_header_sobracolumay(new_template_sobracolumay, item);
            quarry_name = item.quarry_name;
            quality_name = item.quality_name;
        }

        if(item.quarry_name != quarry_name){

            var block_count_final = {
                net_vol: sum_volume_final,
                tot_weight: sum_weight_final,
                block_number: count_blocks_final,
                quality_name: count_quality_blocks_final,
            }

            render_sobracolumay(new_template_sobracolumay, block_count_final, 'bg-info');

            count_blocks_final = 0;
            count_quality_blocks_final = 0;
            sum_volume_final = 0;
            sum_weight_final = 0;

            new_template_sobracolumay = template_sobracolumay.clone();
            render_header_sobracolumay(new_template_sobracolumay, item);
            quarry_name = item.quarry_name;
            quality_name = item.quality_name;
        }

        if(quality_name != item.quality_name){

            var block_count = {
                net_vol: sum_volume,
                tot_weight: sum_weight,
                block_number: count_blocks,
                quality_name: count_quality_blocks,
            }

            render_sobracolumay(new_template_sobracolumay, block_count, 'bg-warning');

            quality_name = item.quality_name;
            count_blocks = 0;
            count_quality_blocks = 0;
            sum_volume = 0;
            sum_weight = 0;

        }

        count_blocks_final++;
        count_quality_blocks_final++;
        sum_volume_final += parseFloat(item.net_vol) || 0;
        sum_weight_final += parseFloat(item.tot_weight) || 0;

        count_blocks++;
        count_quality_blocks++;
        sum_volume += parseFloat(item.net_vol) || 0;
        sum_weight += parseFloat(item.tot_weight) || 0;

        render_sobracolumay(new_template_sobracolumay, item);

        if(i >= sobracolumay.length -1){

            var block_count = {
                net_vol: sum_volume,
                tot_weight: sum_weight,
                block_number: count_blocks,
                quality_name: count_quality_blocks,
            }

            render_sobracolumay(new_template_sobracolumay, block_count, 'bg-warning');


            var block_count_final = {
                net_vol: sum_volume_final,
                tot_weight: sum_weight_final,
                block_number: count_blocks_final,
                quality_name: count_quality_blocks_final,
            }

            render_sobracolumay(new_template_sobracolumay, block_count_final, 'bg-info');

        }

        new_template_sobracolumay.appendTo(div_list);

    });
}

function render_header_sobracolumay(new_template_sobracolumay, item){

    // limpa trs, menos a primeira
    new_template_sobracolumay.find('tbody').find("tr:gt(1)").remove();
    new_template_sobracolumay.removeAttr("template-sobracolumay");
    new_template_sobracolumay.css("display", '');

    var field_quarry = new_template_sobracolumay.find('[template-field="quarry"]');
    field_quarry.text(item.lot_number);
    
}

function render_sobracolumay(new_template_sobracolumay, item, color){

    var table_sobracolumay = $(new_template_sobracolumay.find('.table_block_list'));
    var table_body_sobracolumay = $(table_sobracolumay).find('tbody');
    var template_row = table_body_sobracolumay.find("tr:first");
    
    
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    if(color){
        new_row.addClass(color);
    }

    var field_production = new_row.find('[template-field="production"]');
    field_production.text(item.date_production ? item.date_production.format_date() : '');

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(item.block_number);
    field_block_number.addClass('color_client');
    field_block_number.attr('client_id', item.reserved_client_id);

    field_block_number.unbind('click');
    field_block_number.click(function() {
        show_dialog(FORMULARIO.VISUALIZAR, item.id);
    });


    $(field_block_number).each(function(){

        var value = []; 
        value = item.reserved_client_id;
        var found = false;

       for(i=0; i<reserved_client_code.length; i++){
            if(value == reserved_client_code[i]){

                found = true;
            }    
        }
        if(found == false && typeof value != 'undefined' && value){
            reserved_client_code.push(value);
        }
    });

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name || '');

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text(item.net_c ? item.net_c.format_number(2) : '');
    
    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text(item.net_a ? item.net_a.format_number(2) : '');

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text(item.net_l ? item.net_l.format_number(2) : '');

    var field_net_vol = $(new_row.find("[template-field='net_vol']"));
    field_net_vol.text(item.net_vol ? item.net_vol.format_number(3) : '');

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight ? item.tot_weight.format_number(3) : '');

    var field_reserved_client = $(new_row.find("[template-field='reserved_client']"));
    field_reserved_client.text(item.reserved_client_code ? item.reserved_client_code : '');

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs_poblo ? item.obs_poblo : '');

    var btn_obs = $(new_row.find("[template-button='obs']"));
    btn_obs.unbind('click');
    btn_obs.click(function() {

        var callback = function(obs){
            field_obs.text(obs);
        }

        show_poblo_obs(item.id, item.block_number, callback);

    });

    var btn_edit = $(new_row.find("[template-button='btn_edit']"));
    btn_edit.addClass('hidden');
    btn_edit.unbind('click');
    btn_edit.click(function() {
        
        show_poblo_edit(item.block_id, item.invoice_item_id);
    });

    if(color){
        btn_obs.addClass('hidden');
        btn_edit.addClass('hidden');
    }

    new_row.appendTo(table_body_sobracolumay);

    color_sobra();

}

function load_lot (lot){

    var new_template_lot = '';
    var quality_name = '';

    var count_blocks = 0;
    var sum_price = 0;
    var sum_volume = 0;
    var sum_weight = 0;
    var count_quality_blocks = 0

    var count_blocks_final = 0;
    var sum_price_final = 0;
    var sum_volume_final = 0;
    var sum_weight_final = 0;
    var count_quality_blocks_final = 0;

    $(lot).each(function(i , item){

        if(i == 0){

            new_template_lot = template_lot.clone();
            render_header_lot(new_template_lot, item);
            lot_number = item.lot_number;
            quality_name = item.quality_name;

        }

        if(item.lot_number != lot_number){

            var block_count = {
                block_number: count_blocks,
                quality_name: count_quality_blocks,
                net_vol: sum_volume,
                tot_weight: sum_weight,
                invoice_item_price: sum_price,
            }

            render_lot(new_template_lot, block_count, 'bg-warning');

            var block_count_final = {
                block_number: count_blocks_final,
                quality_name: count_quality_blocks_final,
                net_vol: sum_volume_final,
                tot_weight: sum_weight_final,
                invoice_item_price: sum_price_final,
            }

            render_lot(new_template_lot, block_count_final, 'bg-info');

            new_template_lot = template_lot.clone();
            render_header_lot(new_template_lot, item);
            lot_number = item.lot_number;

            quality_name = item.quality_name;

            
            count_blocks = 0;
            sum_price = 0;
            sum_volume = 0;
            sum_weight = 0;
            count_quality_blocks = 0;

            count_blocks_final = 0;
            sum_price_final = 0;
            sum_volume_final = 0;
            sum_weight_final = 0;
            count_quality_blocks_final = 0;
        }

        count_blocks++;
        sum_price += parseFloat(item.invoice_item_price) || 0;
        sum_volume += parseFloat(item.net_vol) || 0;
        sum_weight += parseFloat(item.tot_weight) || 0;
        count_quality_blocks++;

        if(item.quality_name != quality_name){
            
            var block_count = {
                block_number: count_blocks,
                quality_name: count_quality_blocks,
                net_vol: sum_volume,
                tot_weight: sum_weight,
                invoice_item_price: sum_price,
            }

            render_lot(new_template_lot, block_count, 'bg-warning');

            count_blocks = 0;
            sum_price = 0;
            sum_volume = 0;
            sum_weight = 0;
            count_quality_blocks = 0;

            quality_name = item.quality_name;
        }

        count_blocks_final++;
        count_quality_blocks_final++;
        sum_price_final += parseFloat(item.invoice_item_price) || 0;
        sum_volume_final += parseFloat(item.net_vol) || 0;
        sum_weight_final += parseFloat(item.tot_weight) || 0;

        render_lot(new_template_lot, item);

        if(i >= lot.length -1){

            var block_count = {
                block_number: count_blocks,
                quality_name: count_quality_blocks,
                net_vol: sum_volume,
                tot_weight: sum_weight,
                invoice_item_price: sum_price,
            }

            render_lot(new_template_lot, block_count, 'bg-warning');

            var block_count_final = {
                block_number: count_blocks_final,
                quality_name: count_quality_blocks_final,
                net_vol: sum_volume_final,
                tot_weight: sum_weight_final,
                invoice_item_price: sum_price_final,
            }

            render_lot(new_template_lot, block_count_final, 'bg-info');
        }

        new_template_lot.appendTo(div_list);    
    });
}


function render_poblo_status(){

   $.getJSON("<?= APP_URI ?>poblo_status/list/json/", function(response) {
        if (response_validation(response)) {
            poblo_status = response;
        }
    });
}


function render_header_lot(new_template_lot, item){

    // limpa trs, menos a primeira
    new_template_lot.find('tbody').find("tr:gt(1)").remove();
    new_template_lot.removeAttr("template-lot");
    new_template_lot.css("display", '');


    var field_lot = new_template_lot.find("[template-field='lot']");
    field_lot.text(item.lot_number);
    field_lot.attr('href', "<?= APP_URI ?>lots/detail/" + item.lot_transport_id);

    var field_client_name = new_template_lot.find("[template-field='client_name']");
    field_client_name.text(item.client_name);

    var field_vessel = new_template_lot.find("[template-field='vessel']")
    field_vessel.text(item.shipped_to || '');

    var field_status = new_template_lot.find("[template-field='status']");
    field_status.text(str_lot_transport_status(item.lot_transport_status) || '');

    switch (parseInt(item.lot_transport_status, 10))
    {
        case LOT_TRANSPORT_STATUS.DRAFT:
            field_status.addClass('label label-default');
            break;
        case LOT_TRANSPORT_STATUS.RELEASED:
            field_status.addClass('label label-info');
            break;
        case LOT_TRANSPORT_STATUS.TRAVEL_STARTED:
            field_status.addClass('label label-warning');
            break;
        case LOT_TRANSPORT_STATUS.DELIVERED:
            field_status.addClass('label label-success');
            break;
    }


    // packing list
    var btn_doc_packing_list = new_template_lot.find("[template-button='doc_packing_list']");
    btn_doc_packing_list.click(function() {
        init_down_packing_list(item.lot_transport_id);
        showModal('modal_down_packing_list');
    });

    if (item.down_packing_list == '1') { // cor
        btn_doc_packing_list.removeClass('btn-warning');
        btn_doc_packing_list.addClass('btn-default');
        var icone = btn_doc_packing_list.find('span');
        icone.removeClass('glyphicon-download-alt');
        icone.addClass('glyphicon-ok');
    }

    // draft
    var btn_doc_draft = new_template_lot.find("[template-button='doc_draft']");
    btn_doc_draft.click(function(){
        if(item.draft_file){
            window.location = '<?= APP_URI ?>/travel_plan/draft/download/?id=' + item.lot_transport_id;
        }
        else{
            show_dialog_send(item.lot_transport_id, item.lot_number);
        }

    });

    var btn_doc_draft_send = new_template_lot.find("[template-button='doc_draft_send']");
    btn_doc_draft_send.click(function() {
        show_dialog_send(item.lot_transport_id, item.lot_number);
    });

    if (item.down_draft == '1') { // cor
        btn_doc_draft.removeClass('btn-warning');
        btn_doc_draft.addClass('btn-default');
        var icone = btn_doc_draft.find('span');
        icone.removeClass('glyphicon-download-alt');
        icone.addClass('glyphicon-ok');
    }

    // commercial invoice
    var btn_doc_commercial_invoice = new_template_lot.find("[template-button='doc_commercial_invoice']");
    btn_doc_commercial_invoice.click(function() {
        init_down_commercial_invoice(item.lot_transport_id);
        showModal('modal_down_commercial_invoice');
    });

    if (item.down_commercial_invoice == '1') { // cor
        btn_doc_commercial_invoice.removeClass('btn-warning');
        btn_doc_commercial_invoice.addClass('btn-default');
        var icone = btn_doc_commercial_invoice.find('span');
        icone.removeClass('glyphicon-download-alt');
        icone.addClass('glyphicon-ok');
    }
}


//listagem somente do lot
function render_lot (new_template_lot, item, color){


    var table_lot = $(new_template_lot.find('.table_block_list'));
    var table_body_lot = $(table_lot).find('tbody');
    var template_row = table_body_lot.find("tr:first");
    
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    if(color){
        new_row.addClass(color);
    }

    var field_cores = $(new_row.find("[template-field='cores']"));
    field_cores.colorpicker();

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.css('background-color', item.cor_poblo_status);
    field_block_number.css('color', item.cor_poblo_status_texto);

    var field_block_number_a = $(new_row.find("[template-field='block_number_a']"));
    field_block_number_a.text(item.block_number || '');
    field_block_number_a.attr('template-client', item.reserved_client_id);


    field_block_number_a.unbind('click');
    field_block_number_a.click(function() {
        show_dialog(FORMULARIO.VISUALIZAR, item.block_id);
    });

    
    var block_number_selected = item.invoice_item_id;
    var ul_listagem = $(new_row.find('.ul_listagem'));
    var li = ul_listagem.find('[template-row]');
    var add_li = function(poblo_status_item){

        var new_li = li.clone();
        new_li.removeAttr('template-row');
        new_li.css("display", '');

        var field_poblo_status_option = $(new_li.find("[template-field='poblo_status_option']"));
        field_poblo_status_option.text(poblo_status_item.status);

        new_li.unbind('click');
        new_li.click(function(){
       
            field_block_number.css('background-color', poblo_status_item.cor);

            $.ajax({
                error: ajaxError,
                type: "POST",
                url: "<?= APP_URI ?>poblo_status/save_color/",
                data: {
                    invoice_item_id: block_number_selected,
                    poblo_status_id: poblo_status_item.poblo_status_id
                }
            });
        });

        new_li.appendTo(ul_listagem);
    }

    $(poblo_status).each(function(j, poblo_status_item){
        add_li(poblo_status_item);
    });

    var div_status = $(new_row.find(".div_status"));
    if(color){
        div_status.addClass('hidden');
    }

    
    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name || '');

    var field_nf = $(new_row.find("[template-field='nf']"));
    field_nf.text(item.invoice_item_nf ? item.invoice_item_nf : '');

    var field_data = $(new_row.find("[template-field='data']"));
    field_data.text(item.invoice_date_record ? item.invoice_date_record.format_date() : '');

    var field_price = $(new_row.find("[template-field='price']"));
    field_price.text(item.invoice_item_price ? item.invoice_item_price.format_number(2) : '');

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text(item.invoice_sale_net_c ? item.invoice_sale_net_c.format_number(2) : '');
    
    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text(item.invoice_sale_net_a ? item.invoice_sale_net_a.format_number(2) : '');

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text(item.invoice_sale_net_l ? item.invoice_sale_net_l.format_number(2) : '');
   
    var field_net_vol = $(new_row.find("[template-field='net_vol']"));
    field_net_vol.text(item.net_vol ? item.net_vol.format_number(3) : '');

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight ? item.tot_weight.format_number(3) : '');

    var field_wagon_number = $(new_row.find("[template-field='wagon_number']"));
    field_wagon_number.text(item.current_travel_plan_item_wagon_number ? item.current_travel_plan_item_wagon_number : '');

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs_poblo ? item.obs_poblo : '');

    var btn_obs = $(new_row.find("[template-button='obs']"));
    btn_obs.unbind('click');
    btn_obs.click(function() {

        var callback = function(obs){
            field_obs.text(obs);
        }

        show_poblo_obs(item.block_id, item.block_number, callback);
    });

    var btn_edit = $(new_row.find("[template-button='btn_edit']"));
    btn_edit.unbind('click');
    btn_edit.click(function() {


        var callback = function(nf, price, wagon_number){
            if(nf != ''){
                field_nf.text(nf);
            }
            
            if(price != ''){
                field_price.text(price)
            }

            if(wagon_number != ''){
                field_wagon_number.text(wagon_number);
            }
        }

        show_poblo_edit(item.block_id, item.invoice_item_id, callback, item.invoice_item_nf, item.invoice_date_nf, item.invoice_item_price.format_number(2), item.current_travel_plan_item_wagon_number);
    });

    if(color){
        btn_obs.addClass('hidden');
        btn_edit.addClass('hidden');
    }

    
   
    new_row.appendTo(table_body_lot);
}




function render_cores() {

    var template_cores = $('#template_cores');
    var keys = Object.keys(colors);

    for (var i = 0; i < keys.length; i++) {
        var option = $('<option>').val(keys[i]).css('background', keys[i]);
        option.appendTo(template_cores);
    };

}

//load block color according to the selected client

function init_list() {

    colors_sobra_background[0] = {cor: '#FFFF00', texto: '#000000'}
    colors_sobra_background[1] = {cor: '#00FF00', texto: '#000000'}        
    colors_sobra_background[2] = {cor: '#00AFFF', texto: '#000000'}          
    colors_sobra_background[3] = {cor: '#FFA500', texto: '#000000'}          
    colors_sobra_background[4] = {cor: '#FF0000', texto: '#FFFFFF'}                    
    colors_sobra_background[5] = {cor: '#FFFFE0', texto: '#000000'}
    colors_sobra_background[6] = {cor: '#90EE90', texto: '#000000'}
    colors_sobra_background[7] = {cor: '#00BFFF', texto: '#000000'}
    colors_sobra_background[8] = {cor: '#FFA07A', texto: '#000000'}
    colors_sobra_background[9] = {cor: '#01DFD7', texto: '#000000'} 
    colors_sobra_background[10] = {cor: '#FE9A2E', texto: '#000000'}      
    colors_sobra_background[11] = {cor: '#0404B4', texto: '#FFFFFF'}
    colors_sobra_background[12] = {cor: '#A9BCF5', texto: '#000000'}         
    colors_sobra_background[13] = {cor: '#F5A9A9', texto: '#000000'}          
    colors_sobra_background[14] = {cor: '#F7BE81', texto: '#000000'}         
    colors_sobra_background[15] = {cor: '#B18904', texto: '#000000'}         
    colors_sobra_background[16] = {cor: '#CEF6F5', texto: '#000000'}  
    colors_sobra_background[17] = {cor: '#0B4C5F', texto: '#FFFFFF'} 
    colors_sobra_background[18] = {cor: '#CECEF6', texto: '#000000'}      
    colors_sobra_background[19] = {cor: '#D0F5A9', texto: '#000000'}                  
    colors_sobra_background[20] = {cor: '#2E2EFE', texto: '#FFFFFF'}
    colors_sobra_background[21] = {cor: '#FA5882', texto: '#000000'}        
    colors_sobra_background[22] = {cor: '#F5ECCE', texto: '#000000'}          
    colors_sobra_background[23] = {cor: '#FF4000', texto: '#FFFFFF'}
    colors_sobra_background[24] = {cor: '#9ACD32', texto: '#000000'}   
}

function associate_sobra(){

    Array.prototype.associate = function (keys) {
          var result = [];
          var keys2 = [];

          keys.forEach(function (el, i) {
            if(typeof keys[i] != 'undefined' && keys[i])
                keys2.push(el);
          });

          this.forEach(function (el, i) {
            if(typeof keys2[i] != 'undefined')
               
                result.push({client_id:keys2[i], cor:el});
          });

          return result;
        };
  client_color = colors_sobra_background.associate(reserved_client_code); 
}

function color_sobra(){
    
    associate_sobra();

    var linhas = $('.color_client'); 
    var blocos = new Array();

    linhas.each(function(indice, linha) {

        var client_id = $(linha).attr('client_id');
        var cor_final = null;
        var cor_texto = null;

        $(client_color).each(function(indice_cliente, cor_cliente) {

            if(cor_cliente.client_id == client_id){
                cor_final = cor_cliente.cor.cor;
                cor_texto = cor_cliente.cor.texto;
            }
        });

        if(client_id > 0){
            $(linha).css('background-color', cor_final);
            $(linha).css('color', cor_texto);
        }else{
            $(linha).css('background-color', '');
            $(linha).css('color', ''); 
        } 

    });  

}