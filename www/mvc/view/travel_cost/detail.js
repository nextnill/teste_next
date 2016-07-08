
//elementos
var edt_cost_tonne = $('#edt_cost_tonne');
var alerta_form = $('#alerta_form');
var btn_save = $('#btn_save');
var post_tipo = $('#post_tipo');
var rec_id = $('#rec_id');
var edt_name = $('#edt_name');
var cbo_type = $('#cbo_type');

function show_dialog(tipo, id)
{
    limpa_formulario(tipo);

    edt_cost_tonne.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});

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

    edt_name.prop("readonly", !valor);
    edt_cost_tonne.prop("readonly", !valor);
    cbo_type.prop("disabled", !valor);
}

function limpa_formulario(tipo)
{

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_name.val('');
    edt_cost_tonne.val('');
    cbo_type.val('');

    set_focus(edt_name);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_cost/detail/json/" + id, function(response) {
        if (response_validation(response)) {

            if (response.hasOwnProperty('id')) {
                rec_id.val(response.id);

                edt_name.val(response.name);
                cbo_type.val(response.type);

                edt_cost_tonne.maskMoney('mask', parseFloat(response.cost_per_tonne));
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{

    var valido = true;
    var msgs = new Array();

    /*
    if ((cbo_type.val() === null) || (cbo_type.val().length == 0))
    {
        valido = false;
        msgs.push('Enter the type');
    }
    */

    if (edt_name.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the name');
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

    if (post_tipo.val() == FORMULARIO.EXCLUIR || valida_formulario())
    {
        var modal_detalhe = $('#modal_detalhe');
       
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>travel_cost/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                name: edt_name.val(),
                type: 1,
                cost_per_tonne: edt_cost_tonne.maskMoney('unmasked')[0],
            },
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe');
                    listar();

                    var tipo = parseInt(post_tipo.val(), 10);
                    switch (tipo)
                    {
                        case FORMULARIO.NOVO:
                            alert_saved(edt_name.val() + ' saved successfully');
                            break;
                        case FORMULARIO.EDITAR:
                            alert_saved(edt_name.val() + ' saved successfully');
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved(edt_name.val() + ' deleted successfully');
                            break;
                    }
                }
            }
        });
    }
}