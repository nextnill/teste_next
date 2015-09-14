var arr_locations = [];

function listar_terminals(start_quarry_id, start_terminal_id, end_quarry_id, end_terminal_id)
{
    var cbo_start = $('#cbo_start');
    var cbo_end = $('#cbo_end');
    
    cbo_start.find("option").remove();
    cbo_end.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_route/list/locations/json/", function(response) {
        if (response_validation(response)) {
            arr_locations = response;
        
            if ((arr_locations.quarry) && (arr_locations.quarry.length > 0)) {
                
                // start

                // pedreiras
                var opt_quarries = $('<optGroup/>').attr('label', 'Quarries');
                for (var i = 0; i < arr_locations.quarry.length; i++) {
                    var opt_item = $('<option/>').attr('value', 'q' + arr_locations.quarry[i].id).text(arr_locations.quarry[i].name);
                    opt_item.appendTo(opt_quarries);
                }

                // terminais
                var opt_terminals = $('<optGroup/>').attr('label', 'Terminals');
                for (var i = 0; i < arr_locations.terminal.length; i++) {
                    var opt_item = $('<option/>').attr('value', 't' + arr_locations.terminal[i].id).text(arr_locations.terminal[i].name);
                    opt_item.appendTo(opt_terminals);
                }

                opt_quarries.clone().appendTo(cbo_start);
                opt_terminals.clone().appendTo(cbo_start);

                if ((start_quarry_id) && (start_quarry_id > 0)) {
                    cbo_start.val('q' + start_quarry_id).trigger("change");;
                }

                if ((start_terminal_id) && (start_terminal_id > 0)) {
                    cbo_start.val('t' + start_terminal_id).trigger("change");;
                }

                // end

                // pedreiras
                var opt_quarries = $('<optGroup/>').attr('label', 'Quarries');
                for (var i = 0; i < arr_locations.quarry.length; i++) {
                    var opt_item = $('<option/>').attr('value', 'q' + arr_locations.quarry[i].id).text(arr_locations.quarry[i].name);
                    opt_item.appendTo(opt_quarries);
                }

                // terminais
                var opt_terminals = $('<optGroup/>').attr('label', 'Terminals');
                for (var i = 0; i < arr_locations.terminal.length; i++) {
                    var opt_item = $('<option/>').attr('value', 't' + arr_locations.terminal[i].id).text(arr_locations.terminal[i].name);
                    opt_item.appendTo(opt_terminals);
                }

                opt_quarries.clone().appendTo(cbo_end);
                opt_terminals.clone().appendTo(cbo_end);

                if ((end_quarry_id) && (end_quarry_id > 0)) {
                    cbo_end.val('q' + end_quarry_id).trigger("change");;
                }

                if ((end_terminal_id) && (end_terminal_id > 0)) {
                    cbo_end.val('t' + end_terminal_id).trigger("change");;
                }
            }

            cbo_start.select2();
            cbo_end.select2();

            set_focus(cbo_start);
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

            var div_hide = $('.div_hide');

            div_hide.addClass('hidden');
           

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
    var cbo_start = $('#cbo_start');
    var cbo_end = $('#cbo_end');
    var edt_shipping_time = $('#edt_shipping_time');
    var edt_blocks = $('#edt_blocks');

    cbo_start.prop("disabled", !valor);
    cbo_end.prop("disabled", !valor);
    edt_shipping_time.prop("readonly", !valor);
    edt_blocks.prop("readonly", !valor);
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_shipping_time = $('#edt_shipping_time');
    var edt_blocks = $('#edt_blocks');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

     if (tipo == FORMULARIO.NOVO) {
        listar_terminals();
    }
    
    edt_shipping_time.val('');
    edt_blocks.val('');

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_route/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var edt_shipping_time = $('#edt_shipping_time');
            var edt_blocks = $('#edt_blocks');
            
            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                listar_terminals(response.start_quarry_id, response.start_terminal_id, response.end_quarry_id, response.end_terminal_id)

                edt_shipping_time.val(response.shipping_time);
                edt_blocks.val(response.blocks);
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var cbo_start = $('#cbo_start');
    var cbo_end = $('#cbo_end');

    var edt_shipping_time = $('#edt_shipping_time');
    var edt_blocks = $('#edt_blocks');

    var valido = true;
    var vld = new Validation();

    if (cbo_start.val() == cbo_end.val()) {
        valido = false;
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'The start and the end must be different'));
    }

    /*if (edt_shipping_time.val() != null && (edt_shipping_time.val().length == 0) || (isNaN(edt_shipping_time.val())))
    {
        valido = false;
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the shipping time'));
    }

    if (edt_blocks.val() != null && (edt_blocks.val().length == 0) || (isNaN(edt_blocks.val())) || (edt_blocks.val() < 1))
    {
        valido = false;
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the number of blocks'));
    }*/

    if (!valido)
    {
        alert_modal('Validation', vld);
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

        var cbo_start = $('#cbo_start');
        var cbo_end = $('#cbo_end');

        var cbo_start_val = cbo_start.val();
        var cbo_end_val = cbo_end.val();
        var edt_shipping_time = $('#edt_shipping_time');
        var edt_blocks = $('#edt_blocks');

        var start_quarry_id = cbo_start_val.substr(0, 1) == 'q' ? cbo_start_val.substr(1, cbo_start_val.length - 1) : 0;
        var start_terminal_id = cbo_start_val.substr(0, 1) == 't' ? cbo_start_val.substr(1, cbo_start_val.length - 1) : 0;
        
        var end_quarry_id = cbo_end_val.substr(0, 1) == 'q' ? cbo_end_val.substr(1, cbo_end_val.length - 1) : 0;
        var end_terminal_id = cbo_end_val.substr(0, 1) == 't' ? cbo_end_val.substr(1, cbo_end_val.length - 1) : 0;
        
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>travel_route/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                start_quarry_id: start_quarry_id,
                start_terminal_id: start_terminal_id,
                end_quarry_id: end_quarry_id,
                end_terminal_id: end_terminal_id,
                shipping_time: edt_shipping_time.val(),
                blocks: JSON.stringify(edt_blocks.val())
            },
            success: function (response) {

                if (response_validation(response)) {

                    closeModal('modal_detalhe');
                    listar();

                    var tipo = parseInt(post_tipo.val(), 10);
                    switch (tipo) {
                        case FORMULARIO.NOVO:
                            alert_saved(cbo_start.find('option:selected').text() + ' to ' + cbo_end.find('option:selected').text() + ' saved successfully');
                            break;
                        case FORMULARIO.EDITAR:
                            alert_saved(cbo_start.find('option:selected').text() + ' to ' + cbo_end.find('option:selected').text() + ' saved successfully');
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved(cbo_start.find('option:selected').text() + ' to ' + cbo_end.find('option:selected').text() + ' deleted successfully');
                            break;
                    }
                }
                
            }
        });
        
    }
}