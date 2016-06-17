var lbl_price_client = $('#lbl_price_client');
var edt_price_date = $('#edt_price_date');
var edt_price_comments = $('#edt_price_comments');
var modal_detalhe_label = $('#modal_detalhe_label');
var list_price_products = $('#list_price_products');
var template_panel_product_price = $('[template-panel-product-price]');
var btn_save = $('#btn_save');
var btn_cancel = $('#btn_cancel');

var modal_price_tipo = null;
var modal_price_client = null;
var detail_price_list_id = null;
var price_onsave = null;
var price_oncancel = null;

function show_dialog(tipo, client, onsave, oncancel)
{
    modal_price_client = client;
    modal_price_tipo = tipo;

    detail_price_list_id = null;
    if (typeof client.price_list_id != 'undefined' && tipo != FORMULARIO.NOVO) {
        detail_price_list_id = client.price_list_id;
    }

    price_onsave = null;
    if (typeof onsave != 'undefined') {
        price_onsave = onsave;
    }

    price_oncancel = null;
    if (typeof oncancel != 'undefined') {
        price_oncancel = oncancel;
    }

    modal_detalhe_label.text('Price - ' + client.code + ' - ' + client.name);

    limpa_formulario(tipo);

    switch(tipo)
    {
        case FORMULARIO.NOVO:
            client.price_list_id = null;
            carrega_formulario(client);
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            break;
        case FORMULARIO.EDITAR:
            carrega_formulario(client);
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            break;
        case FORMULARIO.VISUALIZAR:
            carrega_formulario(client);
            btn_save.hide();
            btn_save.css('');
            break;
        case FORMULARIO.EXCLUIR:
            carrega_formulario(client);
            btn_save.text('Delete');
            btn_save.addClass('btn btn-danger');
            btn_save.css('');
            break;
    }

    showModal('modal_detalhe');
}

function permite_alterar(valor)
{    
    edt_price_date.prop("readonly", !valor);
    edt_price_comments.prop("readonly", !valor);
    list_price_products.find('input').prop("readonly", !valor);
}

function limpa_formulario(tipo)
{
    btn_save.removeAttr('disabled');
    btn_cancel.removeAttr('disabled');

    btn_save.removeClass();
    btn_save.show();
    edt_price_date.val('');
    set_focus(edt_price_date);
}

function carrega_produtos(client)
{
    list_price_products.html('');
    // percorro os produtos
    $(price_arr_products).each(function(product_index, product) {
        var panel = template_panel_product_price.clone();

        panel.data('product_id', product.id);
        panel.removeAttr("template-panel-product-price");
        panel.css("display", '');

        var table = panel.find('.table_client_products');
        var table_body = $('<tbody>').appendTo(table);
        var client_group_name = panel.find("[template-name]");
        client_group_name.text(product.name);

        // percorro as qualidades
        $(price_arr_qualities).each(function(quality_index, quality) {
            
            var tr_quality = $("<tr>");

            var td_name = $("<td>");
            td_name.text(quality.name);
            td_name.addClass('text-left');
            td_name.css('vertical-align', 'middle');
            td_name.appendTo(tr_quality);

            var td_value = $("<td>");
            td_value.addClass('text-right');
            td_value.css('vertical-align', 'middle');
            td_value.appendTo(tr_quality);

            var input_value = $("<input>");
            input_value.addClass('form-control');
            input_value.addClass('text-right');
            input_value.appendTo(td_value);
            input_value.data('product_id', product.id);
            input_value.data('quality_id', quality.id);

            var price_value = 0;
            // verifico se existe price definido para o produto + qualidade
            if (typeof client.values != 'undefined') {
                $(client.values).each(function(price_index, price) {
                    if ((parseInt(price.product_id, 10) == parseInt(product.id, 10)) && (parseInt(price.quality_id, 10) == parseInt(quality.id, 10))) {
                        price_value = parseFloat(price.value);
                        return;
                    }
                });
            }
            input_value.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});
            input_value.maskMoney('mask', price_value);

            tr_quality.appendTo(table_body);
        });

        panel.appendTo(list_price_products);
    });

    permite_alterar(!(modal_price_tipo == FORMULARIO.VISUALIZAR || modal_price_tipo == FORMULARIO.EXCLUIR));
}

function carrega_formulario(client)
{
    if (typeof client.date_ref != 'undefined' && modal_price_tipo != FORMULARIO.NOVO) {
        set_datepicker(edt_price_date, client.date_ref);
    }
    edt_price_comments.val(client.comments);
    carrega_produtos(client);
}

function envia_detalhes()
{
    btn_save.attr('disabled', 'disabled');
    btn_cancel.attr('disabled', 'disabled');
    // obtenho os valores informados nos inputs
    var price_values = [];
    list_price_products.find('input').each(function() {
        var value = $(this).maskMoney('unmasked')[0];
        if (value > 0) {
            price_values.push({
                product_id: $(this).data('product_id'),
                quality_id: $(this).data('quality_id'),
                value: value
            });
        }
    });

    var params = {
        id: detail_price_list_id,
        client_id: modal_price_client.id,
        date_ref: get_datepicker(edt_price_date),
        comments: edt_price_comments.val(),
        return_last_price: true,
        values: JSON.stringify(price_values)
    };
    

    $.ajax({
        error: ajaxError,
        type: "POST",
        url: "<?= APP_URI ?>price/" + (modal_price_tipo == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
        data: params,
        success: function (response) {
            if (response_validation(response)) {
                closeModal('modal_detalhe');

                switch (modal_price_tipo)
                {
                    case FORMULARIO.NOVO:
                        alert_saved('Saved successfully');
                        break;
                    case FORMULARIO.EDITAR:
                        alert_saved('Saved successfully');
                        break;
                    case FORMULARIO.EXCLUIR:
                        alert_saved('Deleted successfully');
                        break;
                }

                if (price_onsave) {
                    modal_price_client.price_list_id = response.price_list_id;
                    modal_price_client.comments = response.comments;
                    modal_price_client.date_ref = response.date_ref;
                    modal_price_client.values = response.values;
                    price_onsave(modal_price_client);
                }

            }
            btn_save.removeAttr('disabled');
            btn_cancel.removeAttr('disabled');
        }
    });
}

function cancela_modal()
{
    closeModal('modal_detalhe');

    if (typeof price_oncancel != 'undefined' && price_oncancel) {
        price_oncancel();
    }

}