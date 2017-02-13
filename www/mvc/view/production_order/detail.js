function listar_quarry(selected_value, product_selected_value)
{
    var cbo_quarry = $('#cbo_quarry');
    cbo_quarry.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quarry/list/json/", function(response) {
        if (response_validation(response)) {
            $.each(response, function(i, item) {
                add_option(cbo_quarry, item.id, item.name);
            });
            
            if (selected_value)
            {
                cbo_quarry.val(selected_value);
            }
            listar_product(cbo_quarry.val(), product_selected_value);
        }
    }).fail(ajaxError);
}

function listar_product(quarry, selected_value)
{
    var cbo_product = $('#cbo_product');
    cbo_product.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>product/list/json/", { 'quarry': quarry }, function(response) {
        if (response_validation(response)) {
            $.each(response, function(i, item) {
                add_option(cbo_product, item.id, item.name);
            });
            
            if (selected_value)
                cbo_product.val(selected_value);
        }
    }).fail(ajaxError);
}

function show_dialog(tipo, id)
{
    var btn_save = $('#btn_save');
    var edt_date_production = $('#edt_date_production');
    limpa_formulario(tipo);
    
    switch(tipo)
    {
        case FORMULARIO.NOVO:
            btn_save.text('Save and continue');
            btn_save.addClass('btn btn-primary');
            break;
        case FORMULARIO.EDITAR:
            carrega_formulario(id);
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            permite_alterar(false);
            edt_date_production.prop("readonly", false);
            break;
        case FORMULARIO.VISUALIZAR:
            carrega_formulario(id);
            btn_save.hide();
            btn_save.css('');
            permite_alterar(false);
            break;
        case FORMULARIO.EXCLUIR:
            carrega_formulario(id);
            btn_save.text('Delete');
            btn_save.addClass('btn btn-danger');
            btn_save.css('');
            permite_alterar(false);
            break;
    }

    showModal('modal_detalhe');
}

function permite_alterar(valor)
{
    var cbo_quarry = $('#cbo_quarry');
    var edt_date_production = $('#edt_date_production');
    var cbo_product = $('#cbo_product');

    cbo_quarry.prop("disabled", !valor);
    edt_date_production.prop("readonly", !valor);
    cbo_product.prop("disabled", !valor);
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var cbo_quarry = $('#cbo_quarry');
    var edt_date_production = $('#edt_date_production');
    var cbo_product = $('#cbo_product');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    if (tipo == FORMULARIO.NOVO)
        listar_quarry();

    edt_date_production.val('');
    cbo_product.val('');

    set_focus(cbo_quarry);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>po/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var cbo_quarry = $('#cbo_quarry');
            var edt_date_production = $('#edt_date_production');
            var cbo_product = $('#cbo_product');
            var edt_status = $('#edt_status');
            var btn_save = $('#btn_save');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);
                listar_quarry(response.quarry_id, response.product_id);
                set_datepicker(edt_date_production, response.date_production);

                edt_status.val(str_production_status(response.status));

                if (response.status == PRODUCTION_STATUS.CONFIRMED) {
                    permite_alterar(false);
                    btn_save.addClass('disabled');
                }
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var cbo_quarry = $('#cbo_quarry');
    var edt_date_production = $('#edt_date_production');
    var cbo_product = $('#cbo_product');

    var valido = true;
    var msgs = new Array();

    if (cbo_quarry.val() === '')
    {
        valido = false;
        msgs.push('Invalid Quarry');
    }

    if (edt_date_production.val().length < 10)
    {
        valido = false;
        msgs.push('Invalid date production');
    }

    if (cbo_product.val() === '')
    {
        valido = false;
        msgs.push('Invalid Product');
    }

    if (!valido)
    {
        alerta_form.html(msgs.join('<br>'));
        alerta_form.show();
    }

    return valido;
}

function envia_detalhes()
{
    if (valida_formulario())
    {
        var modal_detalhe = $('#modal_detalhe');
        var btn_save = $('#btn_save');
        var post_tipo = $('#post_tipo');
        var rec_id = $('#rec_id');
        var cbo_quarry = $('#cbo_quarry');
        var edt_date_production = $('#edt_date_production');
        var cbo_product = $('#cbo_product');

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>po/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                quarry_id: cbo_quarry.val(),
                date_production: get_datepicker(edt_date_production),
                product_id: cbo_product.val()
            },
            dataType: 'json',
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe');
                
                    if (typeof poi !== 'undefined') { // usado no apontamento dos blocos
                        poi.populate_po_detail(true);
                    }

                    var tipo = parseInt(post_tipo.val(), 10);
                    
                    var redirect = function() {
                        window.location = '<?= APP_URI ?>po/items/' + response.id;
                    }

                    switch (tipo)
                    {
                        case FORMULARIO.NOVO:
                            redirect();
                            break;
                        case FORMULARIO.EDITAR:
                            if (typeof listar == 'function') { // usado na listagem de ops
                                alert_modal('Production Order', 'Production Order #' + response.id + ' saved successfully.', 'Edit blocks', redirect, true, 'Ok');
                            }
                            else if (poi) { // usado no apontamento dos blocos
                                alert_saved('Production Order #' + response.id + ' saved successfully');
                            }
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved('Production Order #' + response.id + ' deleted successfully');
                            listar(); 
                            break;
                    }
                }
            }
        });
    }
}

// on load window
funcs_on_load.push(function() {
    $("#cbo_quarry").change(
        function() {
            listar_product(this.value);
        }
    );
});