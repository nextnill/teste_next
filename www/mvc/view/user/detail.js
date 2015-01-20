var user_admin = <?= ($user['admin'] === true ? 'true' : 'false') ?>;

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
    var edt_password = $('#edt_password');
    var chk_blocked = $('#chk_blocked');
    var chk_admin = $('#chk_admin');

    edt_name.prop("readonly", !valor);
    edt_password.prop("readonly", !valor);
    chk_blocked.prop("disabled", !valor);

    if (user_admin === true) {
        chk_admin.prop("disabled", !valor);
    }
    else {
        chk_admin.prop("disabled", true);
    }
    
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_name = $('#edt_name');
    var edt_password = $('#edt_password');
    var chk_blocked = $('#chk_blocked');
    var chk_admin = $('#chk_admin');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_name.val('');
    edt_password.val('');
    chk_blocked.prop('checked', false);
    chk_admin.prop('checked', false);

    estado_senha(true);

    set_focus(edt_name);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>user/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var edt_name = $('#edt_name');
            var edt_password = $('#edt_password');
            var chk_blocked = $('#chk_blocked');
            var chk_admin = $('#chk_admin');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                edt_name.val(response.name);
                edt_password.val(response.password);
                if (response.blocked === true)
                    chk_blocked.prop('checked', true);
                if (response.admin === true)
                    chk_admin.prop('checked', true);
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_name = $('#edt_name');
    var edt_password = $('#edt_password');

    var valido = true;
    var msgs = new Array();

    if (edt_name.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the name');
    }

    if (edt_password.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the password');
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
        var edt_password = $('#edt_password');
        var chk_blocked = $('#chk_blocked');
        var chk_admin = $('#chk_admin');

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>user/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                name: edt_name.val(),
                password: edt_password.val(),
                blocked: chk_blocked.prop('checked'),
                admin: chk_admin.prop('checked')
            },
            success: function(response) {
                setTimeout(function() {
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
                }, 800);
            }
        });
    }
}

function estado_senha(close)
{
    var edt_senha = $('#edt_password');
    var ico_senha = $('#ico_password');

    ico_senha.removeClass();

    if ((edt_senha.attr('type') == 'password') && (!close)) {
        edt_senha.attr('type', 'text');
        ico_senha.addClass('glyphicon glyphicon-eye-close');
    }
    else {
        edt_senha.attr('type', 'password');
        ico_senha.addClass('glyphicon glyphicon-eye-open');
    }
}