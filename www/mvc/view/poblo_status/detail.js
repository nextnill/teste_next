function Colors(index)
{
    var arr_cores = [];

    arr_cores.push({hex: '#FFCCCC', nome:'Vermelho 1', nome_hex: '#000'});
    arr_cores.push({hex: '#FF6666', nome:'Vermelho 2', nome_hex: '#000'});    
    arr_cores.push({hex: '#FF0000', nome:'Vermelho 3', nome_hex: '#fff'});    
    arr_cores.push({hex: '#CC0000', nome:'Vermelho 4', nome_hex: '#fff'});    
    arr_cores.push({hex: '#990000', nome:'Vermelho 5', nome_hex: '#fff'});    
    arr_cores.push({hex: '#660000', nome:'Vermelho 6', nome_hex: '#fff'});    
    arr_cores.push({hex: '#330000', nome:'Vermelho 7', nome_hex: '#fff'});                        

    arr_cores.push({hex: '#FFCC99', nome:'Laranja 1', nome_hex: '#000'});
    arr_cores.push({hex: '#FFCC33', nome:'Laranja 2', nome_hex: '#000'});    
    arr_cores.push({hex: '#FF9900', nome:'Laranja 3', nome_hex: '#fff'});    
    arr_cores.push({hex: '#FF6600', nome:'Laranja 4', nome_hex: '#fff'});    
    arr_cores.push({hex: '#CC6600', nome:'Laranja 5', nome_hex: '#fff'});    
    arr_cores.push({hex: '#993300', nome:'Laranja 6', nome_hex: '#fff'});    
    arr_cores.push({hex: '#663300', nome:'Laranja 7', nome_hex: '#fff'});                        
    
    arr_cores.push({hex: '#FFFFCC', nome:'Amarelo 1', nome_hex: '#000'});
    arr_cores.push({hex: '#FFFF99', nome:'Amarelo 2', nome_hex: '#000'});    
    arr_cores.push({hex: '#FFFF00', nome:'Amarelo 3', nome_hex: '#000'});    
    arr_cores.push({hex: '#FFCC00', nome:'Amarelo 4', nome_hex: '#000'});    
    arr_cores.push({hex: '#999900', nome:'Amarelo 5', nome_hex: '#fff'});    
    arr_cores.push({hex: '#666600', nome:'Amarelo 6', nome_hex: '#fff'});    
    arr_cores.push({hex: '#333300', nome:'Amarelo 7', nome_hex: '#fff'});                        

    arr_cores.push({hex: '#99FF99', nome:'Verde 1', nome_hex: '#000'});
    arr_cores.push({hex: '#66FF99', nome:'Verde 2', nome_hex: '#000'});    
    arr_cores.push({hex: '#33ff33', nome:'Verde 3', nome_hex: '#000'});    
    arr_cores.push({hex: '#00CC00', nome:'Verde 4', nome_hex: '#fff'});    
    arr_cores.push({hex: '#009900', nome:'Verde 5', nome_hex: '#fff'});    
    arr_cores.push({hex: '#006600', nome:'Verde 6', nome_hex: '#fff'});    
    arr_cores.push({hex: '#003300', nome:'Verde 7', nome_hex: '#fff'}); 

    arr_cores.push({hex: '#CCFFFF', nome:'Azul 1', nome_hex: '#000'});
    arr_cores.push({hex: '#66FFFF', nome:'Azul 2', nome_hex: '#000'});    
    arr_cores.push({hex: '#33CCFF', nome:'Azul 3', nome_hex: '#000'});    
    arr_cores.push({hex: '#3366FF', nome:'Azul 4', nome_hex: '#fff'});    
    arr_cores.push({hex: '#3333FF', nome:'Azul 5', nome_hex: '#fff'});    
    arr_cores.push({hex: '#000099', nome:'Azul 6', nome_hex: '#fff'});    
    arr_cores.push({hex: '#000066', nome:'Azul 7', nome_hex: '#fff'});

    arr_cores.push({hex: '#FFCCFF', nome:'Roxo 1', nome_hex: '#000'});
    arr_cores.push({hex: '#FF99FF', nome:'Roxo 2', nome_hex: '#000'});    
    arr_cores.push({hex: '#CC66CC', nome:'Roxo 3', nome_hex: '#000'});    
    arr_cores.push({hex: '#CC33CC', nome:'Roxo 4', nome_hex: '#fff'});    
    arr_cores.push({hex: '#993366', nome:'Roxo 5', nome_hex: '#fff'});    
    arr_cores.push({hex: '#663366', nome:'Roxo 6', nome_hex: '#fff'});    
    arr_cores.push({hex: '#330033', nome:'Roxo 7', nome_hex: '#fff'});                           

    arr_cores.push({hex: '#FFFFFF', nome:'Branco', nome_hex: '#000'});
    arr_cores.push({hex: '#CCCCCC', nome:'Cinza 1', nome_hex: '#000'});    
    arr_cores.push({hex: '#C0C0C0', nome:'Cinza 2', nome_hex: '#000'});    
    arr_cores.push({hex: '#999999', nome:'Cinza 3', nome_hex: '#fff'});    
    arr_cores.push({hex: '#666666', nome:'Cinza 4', nome_hex: '#fff'});    
    arr_cores.push({hex: '#333333', nome:'Cinza 5', nome_hex: '#fff'});    
    arr_cores.push({hex: '#000000', nome:'Preto', nome_hex: '#fff'});     
    
    return index? arr_cores[index]: arr_cores;
}


function show_dialog(tipo, id)
{
    var btn_save = $('#btn_save');
    limpa_formulario(tipo);

    var colors = new Colors();
    var cbo_color = $('#cbo_color');

        for (var i = 0; i < colors.length; i++) {                 
            cbo_color.append(
                '<option value="'+colors[i].hex+'" style="background:'+colors[i].hex+'; color: '+colors[i].nome_hex+'">'+colors[i].nome+'</option>'                
            );
        }

    switch(tipo)
    {
        case FORMULARIO.NOVO:
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            carrega_cor();
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

function carrega_cor(){
    
    var cbo_color = $('#cbo_color');

    $('#cor_exemplo').css('background', cbo_color.val() ? cbo_color.val() : '');
}

function permite_alterar(valor)
{
    var edt_status = $('#edt_status');
    var cbo_color = $('#cbo_color');
    
    edt_status.prop("readonly", !valor);
    cbo_color.select2("readonly", !valor);
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_status = $('#edt_status');
    var cbo_color = $('#cbo_color');
    
    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_status.val('');
    cbo_color.val('').trigger('change');
    
    set_focus(edt_status);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>poblo_status/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var edt_status = $('#edt_status');
            var cbo_color = $('#cbo_color');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                edt_status.val(response.status);
                cbo_color.val(response.cor);
                $('#cor_exemplo').css('background', cbo_color.val() ? cbo_color.val() : '');
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_status = $('#edt_status');
    var cbo_color = $('#cbo_color');
    
    var valido = true;
    var msgs = new Array();

    if (edt_status.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the status');
    }

    if (cbo_color.val().length == 0)
    {
        valido = false;
        msgs.push('Select a color');
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
        var edt_status = $('#edt_status');
        var cbo_color = $('#cbo_color');
        
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>poblo_status/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                status: edt_status.val(),
                cor: cbo_color.val()
            },
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe');
                    listar();

                    var tipo = parseInt(post_tipo.val(), 10);
                    switch (tipo)
                    {
                        case FORMULARIO.NOVO:
                            alert_saved($('#edt_status').val() + ' saved successfully');
                            break;
                        case FORMULARIO.EDITAR:
                            alert_saved($('#edt_status').val() + ' saved successfully');
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved($('#edt_status').val() + ' deleted successfully');
                            break;
                    }
                }
            }
        });
    }
}
