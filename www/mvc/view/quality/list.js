function listar(callback_function)
{
    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quality/list/json/", function(response) {
        if (response_validation(response)) {
            var table_body = $('#tbl_listagem > tbody');

            $.each(response, function(i, item) {
                add_row(table_body, i, item);
            });

            if (callback_function) {
                callback_function();
            }
        }
    }).fail(ajaxError);
}

function add_row(table_body, i, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='order']"));
    field_id.text(i+1);

    //order_up
    var button_order_up = new_row.find("[template-button='order_up']");
    button_order_up.attr('template-ref', item.id);
    button_order_up.click(function () {
        var id = $(this).attr('template-ref');
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>quality/change_order/",
            data: { id: id, type: 'up' },
            success: function (response) {
                if (response_validation(response)) {
                    listar(function() {
                        if ((response) && (response.id)) {
                            posiciona_botao(response.id, 'up');
                        }
                    });
                }
            }
        });
    });

    //order_down
    var button_order_down = new_row.find("[template-button='order_down']");
    button_order_down.attr('template-ref', item.id);
    button_order_down.click(function () {
        var id = $(this).attr('template-ref');
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>quality/change_order/",
            data: { id: id, type: 'down' },
            success: function (response) {
                if (response_validation(response)) {
                    listar(function() {
                        if ((response) && (response.id)) {
                            posiciona_botao(response.id, 'down');
                        }
                    });
                }
            }
        });
    });

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_name = $(new_row.find("[template-field='name']"));
    field_name.text(item.name);

    var block_type = $(new_row.find("[template-field='block_type']"));
    if(item.block_type == BLOCK_TYPE.FINAL){
        block_type.text("Final Block");
    }else if(item.block_type == BLOCK_TYPE.INTERIM){
        block_type.text("Interim Block");
    }else{
        block_type.text("?");
    }

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

function posiciona_botao(id, type) {
    var button = $("#tbl_listagem").find("[template-button='order_" + type + "'][template-ref='" + id + "']");
    $(button).focus();
    //setTimeout(function() { $('#edt_search_block_number').focus() }, 800);
}

listar();
