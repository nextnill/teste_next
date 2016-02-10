var cbo_filter_client = $('#cbo_filter_client');
var edt_filter_block = $('.edt_filter_block');
var limit_blocks_list = 0;
var num_max_carregados = false;
var block_number_param = null;

// on load window
funcs_on_load.push(function() {
    limit_blocks_list = 0;
    block_number_param = null;
    $('#tbl_listagem').find("tr:gt(1)").remove();
    num_max_carregados = false;
    listar();
    list_filter_client();

});


function listar(block_number)
{
    // limpa trs, menos a primeira
    //

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>block/list/json/"+block_number+'/'+limit_blocks_list, function(response) {

        num_max_carregados = (response &&  response.length < 50 ? true : false);

        if (response_validation(response)) {

            
            var table_body = $('#tbl_listagem > tbody');


            if(edt_filter_block.val() != ''){
                response = response.filter(function(item, i){
                    if(item.block_number.indexOf(edt_filter_block.val()) > -1){
                        return item;
                    }
                });
            }

            if(cbo_filter_client.val() > 0){
                response = response.filter(function(item, i){
                    if(item.reserved_client_id == cbo_filter_client.val() || item.sold_client_id == cbo_filter_client.val()){
                        return item;
                    }

                });
            }

            $.each(response, function(i, item) {
                add_row(table_body, item);
            });
        }
    }).fail(ajaxError);
}

function list_filter_client()
{

    cbo_filter_client.unbind('change');
    cbo_filter_client.change(function() {
        listar();
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

function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(item.block_number);

    var field_type = $(new_row.find("[template-field='type']"));
    field_type.text(str_block_type(item.type));

    var field_reserved = $(new_row.find("[template-field='reserved']"));
    field_reserved.html(str_yes_no(item.reserved, item.reserved_client_code));

    var field_sold = $(new_row.find("[template-field='sold']"));
    field_sold.html(str_yes_no(item.sold, item.sold_client_code));

    var button_edit = new_row.find("[template-button='edit']");
    button_edit.attr('template-ref', item.id);
    button_edit.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.EDITAR, id);
        }
    );

    var button_visualize = new_row.find("[template-button='visualize']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        }
    );

    var button_delete = new_row.find("[template-button='delete']");
    button_delete.attr('template-ref', item.id);
    button_delete.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.EXCLUIR, id);
        }
        
    );

    new_row.appendTo(table_body);
}


edt_filter_block.keyup(function(){
    $('#tbl_listagem').find("tr:gt(1)").remove();
    block_number_param = (this.value != '' ? this.value : null);
    if(block_number_param == null){
        num_max_carregados = false;
        limit_blocks_list = 0;
    }
    listar(block_number_param);
});

$(window).scroll(function(){

    
    var scroll = $(document).scrollTop() + $(this).height();

    if(scroll == document.body.scrollHeight){

        if(!num_max_carregados){

            limit_blocks_list = limit_blocks_list + 50;
            listar(block_number_param);
        }
    } 

});

