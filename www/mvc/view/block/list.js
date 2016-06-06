var cbo_filter_client = $('#cbo_filter_client');
var edt_filter_block = $('.edt_filter_block');
var btn_listar = $('.btn_listar');
var tbl_listagem = $('#tbl_listagem');
var parametro = <?php echo json_encode($data); ?>;

// controle do scroll infinito
var limit_blocks_list = 0;
var num_max_carregados = false;

// on load window
funcs_on_load.push(function() {
    limit_blocks_list = 0;
    tbl_listagem.find("tr:gt(1)").remove();
    num_max_carregados = false;

    if (typeof parametro.parametros != 'undefined' && typeof parametro.parametros.block_number != 'undefined') {
        edt_filter_block.val(parametro.parametros.block_number);
    }

    list_filter_client();
});


function listar()
{
    // limpa trs, menos a primeira
    //
    if (limit_blocks_list == 0) {
        tbl_listagem.find("tr:gt(1)").remove();
    }
    btn_listar.attr('disabled', true);
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>block/list/json/",
        {
            block_number: edt_filter_block.val(),
            limit: limit_blocks_list,
            client_id: cbo_filter_client.val()
        },
        function(response) {

            num_max_carregados = (response &&  response.length < 50 ? true : false);

            if (response_validation(response)) {

                var table_body = $('#tbl_listagem > tbody');

                $.each(response, function(i, item) {
                    add_row(table_body, item);
                });
            }
            btn_listar.attr('disabled', false);
        }
    ).fail(ajaxError);
}

function list_filter_client()
{

    cbo_filter_client.unbind('change');
    cbo_filter_client.change(function() {
        limit_blocks_list = 0;
        num_max_carregados = false;
        listar();
    });

    cbo_filter_client.find("option").remove();

    var selecionado = null;

    if (typeof parametro.parametros != 'undefined' && typeof parametro.parametros.client_id != 'undefined') {
        selecionado = parseInt(parametro.parametros.client_id, 10);
    }

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_head_office/json/", function(response) {
        if (response_validation(response)) {
            add_option(cbo_filter_client, '-1', 'None');
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_filter_client, item.id, item.code + ' - ' + item.name);
            };

            if (selecionado) {
                cbo_filter_client.val(selecionado);
            }

            cbo_filter_client.select2();
            listar();
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


edt_filter_block.keyup(
    $.debounce(1000, function() {
        num_max_carregados = false;
        limit_blocks_list = 0;
        
        listar();
    })
);

$(window).scroll(function(){

    
    var scroll = $(document).scrollTop() + $(this).height();

    if(scroll == document.body.scrollHeight){

        if(!num_max_carregados){

            limit_blocks_list = limit_blocks_list + 50;
            listar();
        }
    } 

});

