function listar_client(selected_value)
{
    var cbo_client = $('#cbo_client');
    cbo_client.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_head_office/json/", function(response) {
        if (response_validation(response)) {
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_client, item.id, item.code + ' - ' + item.name);
            };
            
            if (selected_value)
                cbo_client.val(selected_value).trigger("change");
        }
    }).fail(ajaxError);
}

function listar_quarry(selected_value)
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
                cbo_quarry.val(selected_value).trigger("change");
        }
    }).fail(ajaxError);
}

function show_dialog(tipo, id, start)
{
    var btn_save = $('#btn_save');
    limpa_formulario(tipo, id, start);

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
    var edt_day = $('#edt_day');
    var edt_time = $('#edt_time');
    var cbo_quarry = $('#cbo_quarry');
    var cbo_client = $('#cbo_client');
    var edt_obs = $('#edt_obs');

    edt_day.prop("readonly", !valor);
    edt_time.prop("readonly", !valor);
    cbo_quarry.select2("readonly", !valor);
    cbo_client.select2("readonly", !valor);
    edt_obs.prop("readonly", !valor);
}

function limpa_formulario(tipo, start)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_day = $('#edt_day');
    var edt_time = $('#edt_time');
    var cbo_quarry = $('#cbo_quarry');
    var cbo_client = $('#cbo_client');
    var edt_obs = $('#edt_obs');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_day.val('');
    edt_time.val('');
    edt_obs.val('');

    if (tipo == FORMULARIO.NOVO) {
        listar_client();
        listar_quarry();
        
        if(typeof start != 'undefined'){
            edt_day.val(start);
        }
    }

    set_focus(edt_day);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>schedule_inspection/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var edt_day = $('#edt_day');
            var edt_time = $('#edt_time');
            var cbo_quarry = $('#cbo_quarry');
            var cbo_client = $('#cbo_client');
            var edt_obs = $('#edt_obs');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                set_datepicker(edt_day, response.day);
                edt_time.val(response.time);
                
                listar_client(response.client_id);
                listar_quarry(response.quarry_id);

                edt_obs.val(response.obs);
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_day = $('#edt_day');
    var edt_time = $('#edt_time');
    var cbo_quarry = $('#cbo_quarry');
    var cbo_client = $('#cbo_client');

    var valido = true;
    var msgs = new Array();

    if (edt_day.val().length < 10)
    {
        valido = false;
        msgs.push('Invalid Day');
    }

    if (edt_time.val().length < 5)
    {
        valido = false;
        msgs.push('Invalid Time');
    }

    if (cbo_quarry.val() === '')
    {
        valido = false;
        msgs.push('Invalid Quarry');
    }

    if (cbo_client.val() === '')
    {
        valido = false;
        msgs.push('Invalid Client');
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
        var edt_day = $('#edt_day');
        var edt_time = $('#edt_time');
        var cbo_quarry = $('#cbo_quarry');
        var cbo_client = $('#cbo_client');
        var edt_obs = $('#edt_obs');

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>schedule_inspection/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                day: get_datepicker(edt_day),
                time: edt_time.val().format_time(),
                quarry_id: cbo_quarry.val(),
                client_id: cbo_client.val(),
                obs: edt_obs.val()
            },
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe');
                    listar();

                    var tipo = parseInt(post_tipo.val(), 10);
                    switch (tipo)
                    {
                        case FORMULARIO.NOVO:
                            alert_saved($('#edt_day').val().format_date() + ' ' + $('#edt_time').val().format_time() + ' saved successfully');
                            break;
                        case FORMULARIO.EDITAR:
                            alert_saved($('#edt_day').val().format_date() + ' ' + $('#edt_time').val().format_time() + ' saved successfully');
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved($('#edt_day').val().format_date() + ' ' + $('#edt_time').val().format_time() + ' deleted successfully');
                            break;
                    }
                }
            }
        });
    }
}

// on load window
funcs_on_load.push(function() {
    $("#cbo_quarry").select2();
    $("#cbo_client").select2();
});