// inputs = seletor com a input que receberá o block_number
// function_ok = evento que será executado após selecionar o bloco
function block_search(value, input_selector, function_select) {

	showModal('modal_search_block');

	// colocar valor inicial padrão da pesquisa
	$('#edt_search_block_number').val(value);

	// seta foco na caixa do block number
	set_focus($('#edt_search_block_number'));

	// função click do botão
	var btn_search_click = function() {
		block_search_listar($('#edt_search_block_number').val(), input_selector, function_select);
	}

	// evento de click do botão
	$('#btn_search_block_number').click(btn_search_click);

	// evento de click de tecla, verifica se é enter e chama botão de pesquisa
	$('#edt_search_block_number').keypress(function(e) {
	    if(e.which == 13) {
	        btn_search_click();
	    }
	});

	// se tiver valor inicial, já pesquisa
	if (value) {
		btn_search_click();
	}

}

function block_search_listar(value, input_selector, function_select)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>block/with_lot/" + value, function(response) {
        if (response_validation(response)) {
            // limpa trs, menos a primeira
        	$('#tbl_search_block').find("tr:gt(1)").remove();

            var table_body = $('#tbl_search_block > tbody');

            $.each(response, function(i, item) {
                block_search_add_row(table_body, item, input_selector, function_select);
            });
        }
    }).fail(ajaxError);
}

function block_search_add_row(table_body, item, input_selector, function_select)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(item.block_number);

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name);

    var field_sold_client_code = $(new_row.find("[template-field='sold_client_code']"));
    field_sold_client_code.text(item.sold_client_code);

    var field_lot = $(new_row.find("[template-field='lot']"));
    field_lot.text('#' + item.current_lot_transport_id);

    var button_select = new_row.find("[template-button='select']");
    button_select.attr('template-ref', item.block_number);
    button_select.click(
        function () {
            var block_number = $(this).attr('template-ref');
            input_selector.val(block_number);
            closeModal('modal_search_block');
            if (function_select) {
                function_select();
            }
        }
    );

    new_row.appendTo(table_body);
}