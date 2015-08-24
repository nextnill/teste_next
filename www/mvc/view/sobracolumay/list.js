var head_office = [];
var selected_combo = null;
var campos_marcados = new Array();
var reserved_client_code = new Array();
var colors = new Array();
var client_color = new Array();
var reserved_client = new Array();

$('.btn_reserve').hide();




function init()
{   
    listar_head_office();

    colors[0] = {cor: '#FFFF00', texto: '#000000'}
    colors[1] = {cor: '#00FF00', texto: '#000000'}        
    colors[2] = {cor: '#00AFFF', texto: '#000000'}          
    colors[3] = {cor: '#FFA500', texto: '#000000'}          
    colors[4] = {cor: '#FF0000', texto: '#FFFFFF'}                    
    colors[5] = {cor: '#FFFFE0', texto: '#000000'}
    colors[6] = {cor: '#90EE90', texto: '#000000'}
    colors[7] = {cor: '#00BFFF', texto: '#000000'}
    colors[8] = {cor: '#FFA07A', texto: '#000000'}
    colors[9] = {cor: '#01DFD7', texto: '#000000'} 
    colors[10] = {cor: '#FE9A2E', texto: '#000000'}      
    colors[11] = {cor: '#0404B4', texto: '#FFFFFF'}
    colors[12] = {cor: '#A9BCF5', texto: '#000000'}         
    colors[13] = {cor: '#F5A9A9', texto: '#000000'}          
    colors[14] = {cor: '#F7BE81', texto: '#000000'}         
    colors[15] = {cor: '#B18904', texto: '#000000'}         
    colors[16] = {cor: '#CEF6F5', texto: '#000000'}  
    colors[17] = {cor: '#0B4C5F', texto: '#FFFFFF'} 
    colors[18] = {cor: '#CECEF6', texto: '#000000'}      
    colors[19] = {cor: '#D0F5A9', texto: '#000000'}                  
    colors[20] = {cor: '#2E2EFE', texto: '#FFFFFF'}
    colors[21] = {cor: '#FA5882', texto: '#000000'}        
    colors[22] = {cor: '#F5ECCE', texto: '#000000'}          
    colors[23] = {cor: '#FF4000', texto: '#FFFFFF'}
    colors[24] = {cor: '#9ACD32', texto: '#000000'}                    
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
function listar(){

    campos_marcados = [];
    reserved_client = [];
    listar_blocks();
        
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
            
            $('cbo_head_office').select2();

            color();

    
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
    field_block_number.attr('template-client', item.reserved_client_id);
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

    var chk_alteracao = $(new_row.find(".chk_alteracao"));
    chk_alteracao.attr('value', item.id); 

    //var field_reserved = $(new_row.find("[template-field='reserved']"));
    //field_reserved.text(item.reserved_client_code ? item.reserved_client_code : '');
    
    chk_alteracao.change(function(e){
        
        if(this.checked){
        campos_marcados.push($(this).val());
        }
        else{
         var indice = campos_marcados.indexOf($(this).val());
         campos_marcados.splice(indice, 1);  
        }

         if(campos_marcados.length > 0){
            $('.btn_reserve').show();    
        }
        else{
            $('.btn_reserve').hide();  
        }
     
    });


    var cbo_reserved_client = $(new_row.find("[template-field='reserved'] > select"));
    cbo_reserved_client.find("option").remove();

    add_option(cbo_reserved_client, '-1', 'None');
    for (var i = 0; i < head_office.length; i++) {
        var ho_item = head_office[i];
        add_option(cbo_reserved_client, ho_item.id, ho_item.code);
        

    };

    //reserved_client_code.push('-1');
   
    if (item.reserved_client_id){
        reserved_client_code.push(item.reserved_client_id);
        cbo_reserved_client.val(item.reserved_client_id).trigger("change");
    }    
    cbo_reserved_client.attr('template-ref', item.id);
    cbo_reserved_client.attr('template-ref-bn', item.block_number);
    cbo_reserved_client.attr('template-ref-default', item.reserved_client_id);
    cbo_reserved_client.attr('template-ref-active', "false");    

    cbo_reserved_client.on("change", function(e) {
        // se nao tiver nenhuma janela de reserva ativa
        if ($(this).attr('template-ref-active') == "false") {
            // marca janela como ativa
            $(this).attr('template-ref-active', "true");

            var valor = $(this).val();
            var teste = false;
            
            for(i=0; i<reserved_client_code.length; i++){
                if(valor == reserved_client_code[i]){

                    teste = true;
                }    
            }
            if(teste == false&& typeof valor != 'undefined' && valor){
                 reserved_client_code.push(valor);
            }

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
                                field_block_number.attr('template-client', $(selected_combo).val());
                                color();    
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

function associate(){

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
  client_color = colors.associate(reserved_client_code);       
}

function color(){
   
    associate();

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
                cor = cor_cliente.cor.cor;
                texto = cor_cliente.cor.texto;
            }
        });
        if(client_id > 0){
            $(linha).parent().css('background-color', cor);
            $(linha).css('color', texto);
        }    
        else{
           $(linha).parent().css('background-color', ''); 
           $(linha).css('color', ''); 
        }        
    });
    


    /*
    client_color = colors.associate(reserved_client_code);
    //for(i=0; i<reserved_client.length; i++){
        cor = client_color[reserved_client];
        $('.block_number').css('background-color', cor);
   // }*/
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

function abrir_modal_reserve(){
    
    $('.btn_reserve').unbind('click');
   // limpar_formulario_alterar_atividade()
    showModal('modal_reserve_selected');

    var cbo_reserved_client = $('#client_select');
    cbo_reserved_client.find("option").remove();

    add_option(cbo_reserved_client, '-1', 'None');
    for (var i = 0; i < head_office.length; i++) {
        var ho_item = head_office[i];
        add_option(cbo_reserved_client, ho_item.id, ho_item.name);
    };

    var btn_confirm_reserve = $('#btn_confirm_reserve');

    btn_confirm_reserve.unbind('click');
            btn_confirm_reserve.click(function() {
               
                    // chamar json de reserva
                    $.ajax({
                        error: ajaxError,
                        type: "POST",
                        url: "<?= APP_URI ?>block/reserve_selected/",
                        data: {
                            id: JSON.stringify(campos_marcados),
                            client_block_number: null,
                            reserved_client_id: cbo_reserved_client.val()
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response_validation(response)) {
                                closeModal('modal_reserve_selected');
                                listar_blocks();
                            }
                        }
                    });
                
            });         
}


// on load window
funcs_on_load.push(function() {
    init();
});