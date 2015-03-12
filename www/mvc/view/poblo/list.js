var arr_blocks = [];
var reserved_client_code = new Array();
var colors = new Array();
var colors = <?= json_encode(Sys\Util::Colors()); ?>;
var divisoria = $('<hr>').css('border-color', '#8b0305').css('border-width', '8px');



// on load window
funcs_on_load.push(function() {
    init_list();

});

function init_list() {
    render_cores();
    listar_blocks();
    colors = ['#FFFF00','#00FF00','#00AFFF','#FFA500','#FF0000','#FFFFE0','#90EE90','#00BFFF','#FFA07A','#FF4040'];  
}

function listar_blocks(callback_function)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>poblo/json/", function(response_poblo) {
        if (response_validation(response_poblo)) {
            var transport = response_poblo.transport;

            var list = $('#list');
            var table = $('[template-table="poblo"]').clone();
            var table_body = $(table).find('table > tbody');
            var lot_transport_id = null;
            var lot_number = '';
            var quarry_id = 0;
            var invoice_id = 0;
            var quarry_name = '';
            var quality_name = '';
            var count_blocks = 0;
            var sum_price = 0;
            var sum_volume = 0;
            var sum_weight = 0;
            var count_quality_blocks = 0;
            var sum_quality_price = 0;
            var sum_quality_volume = 0;
            var sum_quality_weight = 0;
            var poblo_obs = '';
            var poblo_obs_interim_sobra = '';
            var poblo_obs_final_sobra = '';
            var poblo_obs_invoice;
            var wagon_number = '';
            var primeiro_certificado_inspecao = 0;
            var primeiro_lot_transporte = 0;


            arr_blocks = transport;

            // limpa a listagem
            list.html('');
            //if(transport > 0){
                // limpa trs, menos a primeira
                table.find("tr:gt(1)").remove();
                table.removeAttr("template-table");
                table.css("display", '');

            //}
            var render_header = function(table, item) {
            		quarry_name = '';
            		quality_name = '';
            		count_blocks = 0;
					sum_price = 0;
					sum_volume = 0;
					sum_weight = 0;

					count_quality_blocks = 0;
					sum_quality_price = 0;
					sum_quality_volume = 0;
					sum_quality_weight = 0;
                    wagon_number = '';

                    

                    var is_sobra = item.lot_number ? item.lot_number.indexOf('Sobracolumay') >= 0 : false;
					var is_inspection_certificate = item.lot_number ? item.lot_number.indexOf('Inspection Certificate') >= 0 : false;
					var is_not_travel = (is_sobra || is_inspection_certificate);

                    if(primeiro_certificado_inspecao != 2 && is_inspection_certificate){

                        primeiro_certificado_inspecao = 1;
                    }

                    if(primeiro_certificado_inspecao == 1){

                        list.append(divisoria.clone());
                        primeiro_certificado_inspecao = 2;
                    }

            		var field_lot = table.find("[template-field='lot']");
            		field_lot.text(item.lot_number);
            		if (!is_not_travel) {
            			field_lot.attr('href', "<?= APP_URI ?>lots/detail/" + item.lot_transport_id);
            		}

                       if(primeiro_lot_transporte != 2 && !is_not_travel){

                        primeiro_lot_transporte = 1;
                    }

                    if(primeiro_lot_transporte == 1){

                        list.append(divisoria.clone());
                        primeiro_lot_transporte = 2;
                    }

                    table.find("[template-field='vessel']").text(item.shipped_to || '');
                    var field_status = table.find("[template-field='status']");
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
                    var btn_doc_packing_list = table.find("[template-button='doc_packing_list']");
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
                    var btn_doc_draft = table.find("[template-button='doc_draft']");
                    btn_doc_draft.click(function(){
                        if(item.draft_file){
                            window.location = '<?= APP_URI ?>/travel_plan/draft/download/?id=' + item.lot_transport_id;
                        }
                        else{
                            show_dialog_send(item.lot_transport_id, item.lot_number);
                        }

                    });

                    var btn_doc_draft_send = table.find("[template-button='doc_draft_send']");
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
                    var btn_doc_commercial_invoice = table.find("[template-button='doc_commercial_invoice']");
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

                    var th_quarry = table.find('.th_quarry');
                    if (is_not_travel) {
                    	var div_botoes = table.find('.div_botoes');
                    	div_botoes.hide();
                    	
                    	var th_data = table.find('.th_data');
                    	var th_wagon_number = table.find('.th_wagon_number');
                    	var th_nf = table.find('.th_nf');
                    	var th_price = table.find('.th_price');


                    	if (is_sobra) {
	                    	th_data.text('Production');
	                    	th_wagon_number.text('Reserved');
	                    	th_nf.hide();
                    		th_price.hide();
                          
                    	}
                    	else if (is_inspection_certificate) {
                    		th_wagon_number.text('Client');
                    		th_nf.hide();
                    		th_price.hide();
                           
                    	}
                    }
                    else {
                		th_quarry.hide();
                        
                	}
                    

            };

            var render_totalizador = function(lot_transport_id, lot_number, quarry_id, invoice_id) {
            	var is_sobra_interim = lot_number ? lot_number.indexOf('Iterim Sobracolumay') >= 0 : false;
            	var is_sobra_final = lot_number ? lot_number.indexOf('Final Sobracolumay') >= 0 : false;
                
				var is_inspection_certificate = lot_number ? lot_number.indexOf('Inspection Certificate') >= 0 : false;
				var is_transport = (!is_sobra_interim && !is_sobra_final && !is_inspection_certificate && !is_transport);

            	add_row(table_body, {
            		invoice_item_price: sum_price,
            		net_vol: sum_volume,
            		tot_weight: sum_weight,
            		block_number: count_blocks,
            		lot_number: lot_number
            	}, true, 'bg-success');

            	// total de linhas, menos um (template)
            	var linhas = table_body.find('tr').length - 1;

            	// pesquiso as duas primeiras linhas, e seleciono a segunda, pois a primeira é o template
            	var primeira_linha = table_body.find('tr:lt(2)')[1];

            	var template_obs = $('[template-obs]').clone();
                template_obs.removeAttr("template-obs");
                template_obs.css("display", '');

                var obs = '';
                if (is_transport) {
                	obs = poblo_obs ? poblo_obs : '';

                }
                else if (is_sobra_interim) {
                	obs = poblo_obs_interim_sobra ? poblo_obs_interim_sobra : '';
                }
                else if (is_sobra_final) {
                	obs = poblo_obs_final_sobra ? poblo_obs_final_sobra : '';
                }
                else if (is_inspection_certificate) {
                	obs = poblo_obs_invoice ? poblo_obs_invoice : '';
                }
                template_obs.find("[template-field='obs']").html(nl2br(obs));

                var btn_obs = table.find('[template-button="obs"]');
                btn_obs.unbind('click');
                btn_obs.click(function() {
                	show_poblo_obs(lot_number, lot_transport_id, quarry_id, invoice_id);

                });

            	var nova_coluna = $('<td rowspan="' + linhas + '"></td>');
            	nova_coluna.append(template_obs);
            	nova_coluna.appendTo(primeira_linha);



            }
            
            var render_quality_totalizador = function(lot_number) {
                
            	add_row(table_body, {
            		invoice_item_price: sum_quality_price,
            		net_vol: sum_quality_volume,
            		tot_weight: sum_quality_weight,
            		block_number: count_quality_blocks,
            		lot_number: lot_number
            	}, true, 'bg-warning');

            	count_quality_blocks = 0;
				sum_quality_price = 0;
				sum_quality_volume = 0;
				sum_quality_weight = 0;
            }

            var is_not_travel = false;
            
            $.each(arr_blocks, function(i, item) {

                // se for o primeiro registro, seta o título na tabela
                if (i == 0) {
				    render_header(table, item);

                }
                
                // se for um novo lote
                if (item.lot_number != lot_number) {

                	// imprimo o totalizador referente ao ultimo registro do lote
                	render_quality_totalizador(lot_number);
                	render_totalizador(lot_transport_id, lot_number, quarry_id, invoice_id);

                    // se não for o primeiro registro
                    if (i > 0) {
                        // mostra a tabela
                        //list.append(divisoria);
                        table.appendTo(list);

                        
                    }


                    table = $('[template-table="poblo"]').clone();
                    table_body = $(table).find('table > tbody');
                    table.find("tr:gt(1)").remove(); // limpa trs, menos a primeira
                    table.removeAttr("template-table");
                    table.css("display", '');
                    

                    render_header(table, item);

                }
                else {
                	if (item.quarry_name != quarry_name || item.quality_name != quality_name) {
                		render_quality_totalizador(lot_number);


                	}
                }

                add_row(table_body, item);

                lot_transport_id = item.lot_transport_id;
                lot_number = item.lot_number;
                is_not_travel = (item.lot_number ? item.lot_number.indexOf('Sobracolumay') >= 0 || item.lot_number.indexOf('Inspection Certificate') >= 0 : false);
                quarry_id = item.quarry_id;
                quarry_name = item.quarry_name;
                quality_name = item.quality_name;
                invoice_id = item.invoice_id;
                poblo_obs  = item.poblo_obs;
                poblo_obs_interim_sobra  = item.poblo_obs_interim_sobra;
                poblo_obs_final_sobra  = item.poblo_obs_final_sobra;
                poblo_obs_invoice  = item.invoice_poblo_obs;

                count_blocks++;
				sum_price += parseFloat(item.invoice_item_price) || 0;
				sum_volume += parseFloat(item.net_vol) || 0;
				sum_weight += parseFloat(item.tot_weight) || 0;

				count_quality_blocks++;
				sum_quality_price += parseFloat(item.invoice_item_price) || 0;
				sum_quality_volume += parseFloat(item.net_vol) || 0;
				sum_quality_weight += parseFloat(item.tot_weight) || 0;
                wagon_number  = item.wagon_number;


            });  
			
            render_quality_totalizador(lot_number);
            render_totalizador(lot_transport_id, lot_number, quarry_id, invoice_id);
            

            // mostra a tabela
            
            table.appendTo(list);


            if (callback_function) {
                callback_function();

            }
        }
    }).fail(ajaxError);
}

function add_row(table_body, item, bold, style_class)
{
	var is_sobra = item.lot_number ? item.lot_number.indexOf('Sobracolumay') >= 0 : false;
	var is_inspection_certificate = item.lot_number ? item.lot_number.indexOf('Inspection Certificate') >= 0 : false;
	var is_not_travel = (is_sobra || is_inspection_certificate);

    //var template_cores = $('#template_cores');
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');
    //var select_cores = template_cores.clone();
    //new_row.removeAttr("id");

    var field_cores = $(new_row.find("[template-field='cores']"));
    field_cores.colorpicker();

    var field_quarry = $(new_row.find("[template-field='quarry_name']"));
    if (is_not_travel) {
    	field_quarry.text(item.quarry_name || '');
    }

    var field_block_number = $(new_row.find("[template-field='block_number_a']"));
    field_block_number.text(item.block_number || '');
    field_block_number.attr('template-client', item.reserved_client_id);
    field_block_number.click(function() {
    	show_dialog(FORMULARIO.VISUALIZAR, !is_sobra ? item.block_id : item.id);
    });

    $(field_block_number).each(function(){
            reserved_client_code.push(item.reserved_client_id);
    });

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name || '');

    var field_nf = $(new_row.find("[template-field='nf']"));
    field_nf.text(item.invoice_item_nf ? item.invoice_item_nf : '');

    var field_data = $(new_row.find("[template-field='data']"));
    if (!is_sobra) {
    	field_data.text(item.invoice_date_record ? item.invoice_date_record.format_date() : '');
    }
    else {
    	field_data.text(item.date_production ? item.date_production.format_date() : '');
    }
    
    var field_price = $(new_row.find("[template-field='price']"));
    field_price.text(item.invoice_item_price ? item.invoice_item_price.format_number(2) : '');
    
    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    if (!is_not_travel) {
    	field_sale_net_c.text(item.invoice_sale_net_c ? item.invoice_sale_net_c.format_number(2) : '');
    }
    else {
    	field_sale_net_c.text(item.net_c ? item.net_c.format_number(2) : '');
    }

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    if (!is_not_travel) {
    	field_sale_net_a.text(item.invoice_sale_net_a ? item.invoice_sale_net_a.format_number(2) : '');
    }
    else {
    	field_sale_net_a.text(item.net_a ? item.net_a.format_number(2) : '');
    }

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    if (!is_not_travel) {
    	field_sale_net_l.text(item.invoice_sale_net_l ? item.invoice_sale_net_l.format_number(2) : '');
    }
    else {
    	field_sale_net_l.text(item.net_l ? item.net_l.format_number(2) : '');
    }

    var field_net_vol = $(new_row.find("[template-field='net_vol']"));
   	field_net_vol.text(item.net_vol ? item.net_vol.format_number(3) : '');



    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight ? item.tot_weight.format_number(3) : '');

    var field_wagon_number = $(new_row.find("[template-field='wagon_number']"));
    if (!is_not_travel) {
    	field_wagon_number.text(item.current_travel_plan_item_wagon_number ? item.current_travel_plan_item_wagon_number : '');
    }
    else {
    	if (is_sobra) {
    		field_wagon_number.text(item.reserved_client_code ? item.reserved_client_code : '');
    	}
    	else if (is_inspection_certificate) {
    		field_wagon_number.text(item.sold_client_code ? item.sold_client_code : '');
    	}
    	
    }

    /*
    var field_destination = $(new_row.find("[template-field='destination']"));

    var destination = item.current_location ? item.current_location : item.next_location;
    destination = destination ? destination : '';
    field_destination.text(destination);

    if (item.client_remove == '1') {
        field_destination.text('Client will remove the block from the quarry');
    }

    var travel_plan_status = item.current_travel_plan_id || item.next_travel_plan_id ? item.current_travel_plan_status : TRAVEL_PLAN_STATUS.COMPLETED;
    travel_plan_status = item.client_remove == '1' ? item.current_travel_plan_status : travel_plan_status;
    */

    if (bold) {
    	new_row.css('font-weight', 'bold');
    }

    if (style_class) {
    	new_row.addClass(style_class);
    }

    
    if (is_not_travel) {
    	field_nf.hide();
    	field_price.hide();

    }
    else {
    	field_quarry.hide();
    }

    new_row.appendTo(table_body);
    color_sobra();
}

function render_cores() {
    var template_cores = $('#template_cores');
    var keys = Object.keys(colors);

    for (var i = 0; i < keys.length; i++) {
        var option = $('<option>').val(keys[i]).css('background', keys[i]);
        option.appendTo(template_cores);
    };
}

function associate_sobra(){

    Array.prototype.associate = function (keys) {
          var result = [];

          this.forEach(function (el, i) {
            if(typeof keys[i] != 'undefined')
               // result[keys[i]] = el;
                result.push({client_id:keys[i], cor:el});
          });

          return result;
        };

  client_color = colors.associate(reserved_client_code); 

}

function color_sobra(){
   
    associate_sobra();

    var linhas = $('[template-field="block_number"]'); 
    var blocos = new Array();

    linhas.each(function(indice, linha) {

        var template_client = $(linha).find('[template-client]');
        if(template_client.length > 0){
            blocos.push(template_client);
        }
    });  

    $(blocos).each(function(indice, linha) {

        var client_id = $(linha).attr('template-client');
        var cor = null;

        $(client_color).each(function(indice_cliente, cor_cliente) {

            if(cor_cliente.client_id == client_id){
                cor = cor_cliente.cor;
            }
        });
        if(client_id > 0)
            $(linha).parent().css('background-color', cor);

        else{
           $(linha).parent().css('background-color', ''); 
        }        
    });
}