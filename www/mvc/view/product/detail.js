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
    var edt_weight_vol = $('#edt_weight_vol');
    
    edt_name.prop("readonly", !valor);
    edt_weight_vol.prop("readonly", !valor);
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_name = $('#edt_name');
    var edt_weight_vol = $('#edt_weight_vol');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_name.val('');
    edt_weight_vol.val('');

    set_focus(edt_name);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>product/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var edt_name = $('#edt_name');
            var edt_weight_vol = $('#edt_weight_vol');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                edt_name.val(response.name);
                $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:3});
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_name = $('#edt_name');
    var edt_weight_vol = $('#edt_weight_vol');

    var valido = true;
    var msgs = new Array();

    if (edt_name.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the name');
    }

    if (edt_weight_vol.val() == '' || isNaN(edt_weight_vol.val()) || (parseFloat(edt_weight_vol.val()) == 0)) {
        valido = false;
        msgs.push('Enter the weight/m³');
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
        var edt_weight_vol = $('#edt_weight_vol');

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>product/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                name: edt_name.val(),
                weight_vol: parseFloat(edt_weight_vol.val())
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
    var edt_weight_vol = $('#edt_weight_vol');

    edt_weight_vol.unbind('change');
    edt_weight_vol.change(function() {
        $(this).maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:3}); // arredondo pra 3 casas
    });
});