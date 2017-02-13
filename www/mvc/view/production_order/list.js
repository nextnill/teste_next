// FUNCOES
var tot_vol = 0;
var tot_block = 0;
var tot_vol_aproximado = 0;

function listar_filter_quarry(selected)
{
    var cbo_filter_quarry = $('#cbo_filter_quarry');

    cbo_filter_quarry.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>/quarry/list/json/", function(response) {
        if (response_validation(response)) {
            add_option(cbo_filter_quarry, '-1', 'None');
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_filter_quarry, item.id, item.name);
            };

            cbo_filter_quarry.select2();

            if(typeof selected != 'undefined'){

                cbo_filter_quarry.val(selected).trigger('change');
            }

            cbo_filter_quarry.unbind('change');
            cbo_filter_quarry.change(function() {
                listar();
            });
            listar();
        }
    }).fail(ajaxError);
}

function listar_filter_quality(selected)
{
    var cbo_filter_quality = $('#cbo_filter_quality');

    cbo_filter_quality.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quality/list/json/", function(response) {
        if (response_validation(response)) {
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_filter_quality, item.id, item.name);
            };

            cbo_filter_quality.select2();

            if(typeof selected != 'undefined'){

                cbo_filter_quality.val(selected).trigger('change');
            }

            cbo_filter_quality.unbind('change');
            cbo_filter_quality.change(function() {
                listar();
            });
            listar();
        }
    }).fail(ajaxError);
}

function listar()
{
    var cbo_filter_quarry = $('#cbo_filter_quarry');
    var cbo_filter_type = $('#cbo_filter_type');
    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');
    var cbo_filter_quality = $('#cbo_filter_quality');

    // limpa trs, menos a primeira

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>po/list/json/", 
        {
            block_type: cbo_filter_type.val(),
            ano: edt_year.val(),
            mes: cbo_month_filter.val(),
            quality: JSON.stringify(cbo_filter_quality.val())
        }, function(response) {
        if (response_validation(response)) {
            
            $('#tbl_listagem').find("tr:gt(1)").remove();
            // zero os totalizadores
            tot_vol = 0;
            tot_block = 0;
            tot_vol_aproximado = 0;

            var table_body = $('#tbl_listagem > tbody');
            $.each(response, function(i, item) {
                add_row(table_body, item);

                if(item.status == 1){
                    tot_vol = tot_vol + parseFloat(Math.round(item.block_net_vol * 100)/100);
                    tot_vol_aproximado = tot_vol.toFixed(3);
                    tot_block = tot_block + parseInt(item.count_blocks, 10);    
                }
            });
            var objeto_total = {
                id: '', 
                quarry_name: '',
                date_production: '',
                product_name: '',
                block_type: '',
                status: '1',
                block_net_vol: tot_vol_aproximado,
                count_blocks: tot_block 
            };
            add_row(table_body, objeto_total, true);
        }
    }).fail(ajaxError);
}



function add_row(table_body, item, totalizador)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_quarry_name = $(new_row.find("[template-field='quarry_name']"));
    field_quarry_name.text(item.quarry_name);

    var field_date_production = $(new_row.find("[template-field='date_production']"));
    if(item.date_production != ''){
        field_date_production.text(item.date_production.format_date());    
    }
    

    var field_product_name = $(new_row.find("[template-field='product_name']"));
    field_product_name.text(item.product_name);

    var field_vol = $(new_row.find("[template-field='vol']"));

    var field_count = $(new_row.find("[template-field='count']"));

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(str_production_status(item.status));
    if (item.status == PRODUCTION_STATUS.CONFIRMED) {
        field_status.addClass('label label-success');
        field_vol.text(item.block_net_vol);
        field_count.text(item.count_blocks);
    }
    else {
        field_status.addClass('label label-default');
        field_vol.text(item.production_order_item_net_vol);
        field_count.text(item.count_blocks_po);
    }


    var div_botoes = $(new_row.find('.div_botoes'));
    
    var button_edit = new_row.find("[template-button='edit']");
    button_edit.attr('template-ref', item.id);

    button_edit.click(function() {
        var id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>po/items/' + id;
    });

    var button_visualize = new_row.find("[template-button='visualize']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.click(function () {
        var id = $(this).attr('template-ref');
        show_dialog(FORMULARIO.VISUALIZAR, id);
    });

    var button_delete = new_row.find("[template-button='delete']");
    button_delete.attr('template-ref', item.id);
    button_delete.click(function () {
        var id = $(this).attr('template-ref');
        show_dialog(FORMULARIO.EXCLUIR, id);
        
    });

    // se for um totalizador
    if (typeof totalizador != 'undefined' && totalizador) {
        if (!div_botoes.hasClass('hidden')) {
            div_botoes.addClass('hidden');
        }
        field_status.addClass('hidden');
        new_row.addClass('bg-info');
    }

    new_row.appendTo(table_body);
}

funcs_on_load.push(function(){

    
    var parametros = <?php echo json_encode($data); ?>;
    
    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');
    var cbo_filter_quarry = $('#cbo_filter_quarry');
    var cbo_filter_type = $('#cbo_filter_type');

    listar_filter_quality();

    if(!parametros.ano && !parametros.mes){
        var agora = new Date();
        var mes = ("0" + (agora.getMonth() + 1)).slice(-2);
        var ano = agora.getFullYear();
        
        edt_year.val(ano);
        cbo_month_filter.val(mes);
        listar_filter_quarry();
        listar();
    }

    else{
    
    edt_year.val(parametros.ano);
    cbo_month_filter.val(parametros.mes);
    listar_filter_quarry(parametros.quarry_id);
    cbo_filter_type.val(parametros.block_type);
    
    }

      
    
});