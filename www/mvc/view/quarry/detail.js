function listar_products(selected_values)
{
    var cbo_products = $('#cbo_products');
    cbo_products.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>product/list/json/", function(response) {
        if (response_validation(response)) {
            $.each(response, function(i, item) {
                add_option(cbo_products, item.id, item.name);
            });
            
            if (selected_values)
                cbo_products.val(selected_values).trigger("change");
        }
    }).fail(ajaxError);
}

function listar_defects(selected_values)
{
    var cbo_defects = $('#cbo_defects');
    cbo_defects.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>defect/list/json/", function(response) {
        if (response_validation(response)) {
            $.each(response, function(i, item) {
                add_option(cbo_defects, item.id, item.name);
            });

            if (selected_values)
                cbo_defects.val(selected_values).trigger("change");
        }
    }).fail(ajaxError);
}

function show_dialog(tipo, id)
{
    var btn_save = $('#btn_save');
    limpa_formulario(tipo);

    switch(tipo)
    {
        case FORMULARIO.NOVO:
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            break;
        case FORMULARIO.EDITAR:
            carrega_formulario(id);
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
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
    var edt_name = $('#edt_name');
    var cbo_products = $('#cbo_products');
    var cbo_defects = $('#cbo_defects');
    var edt_final_block_number = $('#edt_final_block_number');
    var edt_interim_block_number = $('#edt_interim_block_number');
    var edt_seq_final = $('#edt_seq_final');
    var edt_seq_interim = $('#edt_seq_interim');

    edt_name.prop("readonly", !valor);
    cbo_products.select2("readonly", !valor);
    cbo_defects.select2("readonly", !valor);
    edt_final_block_number.prop("readonly", !valor);
    edt_interim_block_number.prop("readonly", !valor);
    edt_seq_final.prop("readonly", !valor);
    edt_seq_interim.prop("readonly", !valor);
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_name = $('#edt_name');
    var cbo_products = $('#cbo_products');
    var cbo_defects = $('#cbo_defects');
    var edt_final_block_number = $('#edt_final_block_number');
    var edt_interim_block_number = $('#edt_interim_block_number');
    var edt_seq_final = $('#edt_seq_final');
    var edt_seq_interim = $('#edt_seq_interim');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_name.val('');
    cbo_products.val('').trigger('change');
    cbo_defects.val('').trigger('change');
    edt_final_block_number.val('');
    edt_interim_block_number.val('');
    edt_seq_final.val('');
    edt_seq_interim.val('');

    if (tipo == FORMULARIO.NOVO) {
        listar_products();
        listar_defects();
    }

    set_focus(edt_name);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quarry/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var edt_name = $('#edt_name');
            var cbo_products = $('#cbo_products');
            var cbo_defects = $('#cbo_defects');
            var edt_final_block_number = $('#edt_final_block_number');
            var edt_interim_block_number = $('#edt_interim_block_number');
            var edt_seq_final = $('#edt_seq_final');
            var edt_seq_interim = $('#edt_seq_interim');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                edt_name.val(response.name);
                listar_products(response.products);
                listar_defects(response.defects);
                edt_final_block_number.val(response.final_block_number);
                edt_interim_block_number.val(response.interim_block_number);
                edt_seq_final.val(response.seq_final);
                edt_seq_interim.val(response.seq_interim);
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_name = $('#edt_name');
    var edt_final_block_number = $('#edt_final_block_number');
    var edt_interim_block_number = $('#edt_interim_block_number');

    var valido = true;
    var msgs = new Array();

    if (edt_name.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the name');
    }

    if (edt_final_block_number.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the final block number');
    }

    if (edt_interim_block_number.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the interim block number');
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
    var post_tipo = $('#post_tipo');

    if (post_tipo.val() == FORMULARIO.EXCLUIR || valida_formulario())
    {
        var modal_detalhe = $('#modal_detalhe');
        var btn_save = $('#btn_save');
        var rec_id = $('#rec_id');
        var edt_name = $('#edt_name');
        var cbo_products = $('#cbo_products');
        var cbo_defects = $('#cbo_defects');
        var edt_final_block_number = $('#edt_final_block_number');
        var edt_interim_block_number = $('#edt_interim_block_number');
        var edt_seq_final = $('#edt_seq_final');
        var edt_seq_interim = $('#edt_seq_interim');
        
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>quarry/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                name: edt_name.val(),
                products: cbo_products.val(),
                defects: cbo_defects.val(),
                final_block_number: edt_final_block_number.val(),
                interim_block_number: edt_interim_block_number.val(),
                seq_final: edt_seq_final.val(),
                seq_interim: edt_seq_interim.val()
            },
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe');
                    listar();

                    var tipo = parseInt(post_tipo.val(), 10);
                    switch (tipo)
                    {
                        case FORMULARIO.NOVO:
                            alert_saved($('#edt_name').val() + ' saved successfully');
                            break;
                        case FORMULARIO.EDITAR:
                            alert_saved($('#edt_name').val() + ' saved successfully');
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved($('#edt_name').val() + ' deleted successfully');
                            break;
                    }
                }
            }
        });
    }
}

// on load window
funcs_on_load.push(function() {
    $("#cbo_products").select2();
    $("#cbo_defects").select2();
});
