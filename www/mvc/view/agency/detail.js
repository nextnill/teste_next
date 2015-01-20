// FUNCOES
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
    var edt_shipping_company = $('#edt_shipping_company');
    var edt_name = $('#edt_name');
    var edt_code = $('#edt_code');
    var edt_contact = $('#edt_contact');
    var edt_telephone = $('#edt_telephone');
    var edt_mobile = $('#edt_mobile');
    var edt_fax = $('#edt_fax');
    var edt_email = $('#edt_email');
    var edt_contact_other = $('#edt_contact_other');
    var edt_obs = $('#edt_obs');

    edt_shipping_company.prop("readonly", !valor);
    edt_name.prop("readonly", !valor);
    edt_code.prop("readonly", !valor);
    edt_contact.prop("readonly", !valor);
    edt_telephone.prop("readonly", !valor);
    edt_mobile.prop("readonly", !valor);
    edt_fax.prop("readonly", !valor);
    edt_email.prop("readonly", !valor);
    edt_contact_other.prop("readonly", !valor);
    edt_obs.prop("readonly", !valor);
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_shipping_company = $('#edt_shipping_company');
    var edt_name = $('#edt_name');
    var edt_code = $('#edt_code');
    var edt_contact = $('#edt_contact');
    var edt_telephone = $('#edt_telephone');
    var edt_mobile = $('#edt_mobile');
    var edt_fax = $('#edt_fax');
    var edt_email = $('#edt_email');
    var edt_contact_other = $('#edt_contact_other');
    var edt_obs = $('#edt_obs');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_shipping_company.val('');
    edt_name.val('');
    edt_code.val('');
    edt_contact.val('');
    edt_telephone.val('');
    edt_mobile.val('');
    edt_fax.val('');
    edt_email.val('');
    edt_contact_other.val('');
    edt_obs.val('');

    set_focus(edt_shipping_company);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    WS.get(
        "agency/detail/json/" + id,
        null,
        function(response) {
            var rec_id = $('#rec_id');
            var edt_shipping_company = $('#edt_shipping_company');
            var edt_name = $('#edt_name');
            var edt_code = $('#edt_code');
            var edt_contact = $('#edt_contact');
            var edt_telephone = $('#edt_telephone');
            var edt_mobile = $('#edt_mobile');
            var edt_fax = $('#edt_fax');
            var edt_email = $('#edt_email');
            var edt_contact_other = $('#edt_contact_other');
            var edt_obs = $('#edt_obs');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                edt_shipping_company.val(response.shipping_company);
                edt_name.val(response.name);
                edt_code.val(response.code);
                edt_contact.val(response.contact);
                edt_telephone.val(response.telephone);
                edt_mobile.val(response.mobile);
                edt_fax.val(response.fax);
                edt_email.val(response.email);
                edt_contact_other.val(response.contact_other);
                edt_obs.val(response.obs);
            }
        }
    );
    
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_shipping_company = $('#edt_shipping_company');
    var edt_name = $('#edt_name');
    var edt_code = $('#edt_code');

    var valido = true;
    var msgs = new Array();

    if (edt_shipping_company.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the shipping company');
    }

    if (edt_name.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the name');
    }

    if (edt_code.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the code');
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
        var edt_shipping_company = $('#edt_shipping_company');
        var edt_name = $('#edt_name');
        var edt_code = $('#edt_code');
        var edt_contact = $('#edt_contact');
        var edt_telephone = $('#edt_telephone');
        var edt_mobile = $('#edt_mobile');
        var edt_fax = $('#edt_fax');
        var edt_email = $('#edt_email');
        var edt_contact_other = $('#edt_contact_other');
        var edt_obs = $('#edt_obs');

        WS.post(
            "agency/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            {
                id: rec_id.val(),
                shipping_company: edt_shipping_company.val(),
                name: edt_name.val(),
                code: edt_code.val(),
                contact: edt_contact.val(),
                telephone: edt_telephone.val(),
                mobile: edt_mobile.val(),
                fax: edt_fax.val(),
                email: edt_email.val(),
                contact_other: edt_contact_other.val(),
                obs: edt_obs.val()
            },
            function (response) {
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
        );
    }
}