var head_office = [];
var selected_combo = null;
var campos_marcados = new Array();
var reserved_client_code = new Array();
var client_color = new Array();
var reserved_client = new Array();

var colors_sobra_background = [
    { background: '#FFE082', texto: '#000'},
    { background: '#EF9A9A', texto: '#000'},
    { background: '#81D4FA', texto: '#000'},
    { background: '#FFAB91', texto: '#000'},
    { background: '#E6EE9C', texto: '#000'},
    { background: '#BCAAA4', texto: '#000'},
    
    { background: '#795548', texto: '#fff'},
    { background: '#FFB300', texto: '#000'},
    { background: '#E53935', texto: '#fff'},
    { background: '#039BE5', texto: '#fff'},
    { background: '#F4511E', texto: '#fff'},
    { background: '#C0CA33', texto: '#000'},
    { background: '#6D4C41', texto: '#fff'},

    { background: '#FFD54F', texto: '#000'},
    { background: '#E57373', texto: '#000'},
    { background: '#4FC3F7', texto: '#000'},
    { background: '#FF8A65', texto: '#000'},
    { background: '#DCE775', texto: '#000'},
    { background: '#A1887F', texto: '#fff'},
    
    { background: '#FFCA28', texto: '#000'},
    { background: '#EF5350', texto: '#fff'},
    { background: '#29B6F6', texto: '#000'},
    { background: '#FF7043', texto: '#000'},
    { background: '#D4E157', texto: '#000'},
    { background: '#8D6E63', texto: '#fff'},
    
    { background: '#FFECB3', texto: '#000'},
    { background: '#FFCDD2', texto: '#000'},
    { background: '#B3E5FC', texto: '#000'},
    { background: '#FFCCBC', texto: '#000'},
    { background: '#F0F4C3', texto: '#000'},
    { background: '#D7CCC8', texto: '#000'},

    { background: '#FFC107', texto: '#000'},
    { background: '#F44336', texto: '#fff'},
    { background: '#03A9F4', texto: '#000'},
    { background: '#FF5722', texto: '#fff'},
    { background: '#CDDC39', texto: '#000'},
    
    { background: '#FFA000', texto: '#000'},
    { background: '#D32F2F', texto: '#fff'},
    { background: '#0288D1', texto: '#fff'},
    { background: '#E64A19', texto: '#fff'},
    { background: '#AFB42B', texto: '#000'},
    { background: '#5D4037', texto: '#fff'},
    { background: '#FF8F00', texto: '#000'},
    { background: '#C62828', texto: '#fff'},
    { background: '#0277BD', texto: '#fff'},
    { background: '#D84315', texto: '#fff'},
    { background: '#9E9D24', texto: '#000'},
    { background: '#4E342E', texto: '#fff'},
    { background: '#FF6F00', texto: '#000'},
    { background: '#B71C1C', texto: '#fff'},
    { background: '#01579B', texto: '#fff'},
    { background: '#BF360C', texto: '#fff'},
    { background: '#827717', texto: '#fff'},
    { background: '#3E2723', texto: '#fff'}
];

$('.btn_reserve').hide();


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
function listar(){
    client_color = [];
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
                    table.find("[template-quality]").text(item.quality_name);
                    table.find("[template-title]").text(item.quarry_name);
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

            //color();

    
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
    if (item.total_blocks != null) {
        cbo_reserved_client.tooltip({title: 'Blocks Reserved: ' + item.total_blocks + ' / Vol: ' + item.total_net_vol, placement: 'top'});
    }

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
                                //color();
                                // desmarca janela como ativa
                                $(selected_combo).attr('template-ref-active', "false");
                                listar();
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
    
    color_sobra(new_row, item);
    
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

function color_sobra(row, item) {
    if (item.reserved_client_id) {
        
        // verifico se já existe o item.client_color no client_color
        var existe = false;
        var cor = null;

        (function f(){
            for (var i = 0; i < client_color.length; i++) {
            if (parseInt(client_color[i].client_id, 10) == parseInt(item.reserved_client_id, 10)) {
                existe = true;
                cor = client_color[i];
                return;
            }
        }})();

        // se não existe, adiciono nova cor do cliente em client_color
        if (!existe) {
            var new_client_color = {
                client_id: parseInt(item.reserved_client_id, 10),
                background: colors_sobra_background[client_color.length+1].background,
                texto: colors_sobra_background[client_color.length+1].texto
            };
            client_color.push(new_client_color);
            cor = new_client_color;
        }

        // pinto a cor da linha com a cor atribuida para o cliente
        row.find("[template-field='block_number']")
            .css('background-color', cor.background)
            .css('color', cor.texto)
            .find('a')
                .css('color', cor.texto);
    }
}

function listar_downgrade_blocks()
{    
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>sobracolumay/list/json/final/", function(response) {
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
                    table.find("[template-quality]").text(item.quality_name);
                    table.find("[template-title]").text(item.quarry_name);
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

            //color();

    
        }
    }).fail(ajaxError);
}

function listar_downgrade(){
    client_color = [];
    campos_marcados = [];
    reserved_client = [];
    listar_downgrade_blocks();
}