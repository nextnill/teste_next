/* form */
var modal_detalhe = $('#modal_detalhe');
var post_tipo = $('#post_tipo');

/* fields */
var rec_id = $('#rec_id');
var edt_description = $('#edt_description');

var tbl_routes = $('#tbl_routes');
var tbl_routes_tbody = $('#tbl_routes > tbody');

/* buttons */
var btn_add_route = $('#btn_add_route');
var btn_save = $('#btn_save');

/* vars */
var rec_permite_alterar = false;
var arr_routes = [];/* [{
                            "id":"1", // travel_plan_template_item_id
                            "travel_route_id":"4",
                            "start_location": "Quarry X",
                            "end_location": "Terminal Y",
                            "start_quarry_id":"2",
                            "start_quarry_name":"Quarry B",
                            "start_terminal_id":null,
                            "start_terminal_name":null,
                            "end_quarry_id":null,
                            "end_quarry_name":null,
                            "end_terminal_id":"3",
                            "end_terminal_name":"Terminal A",
                            "shipping_time": "1",
                            "blocks":"1",
                            "removed": "false" // marcar como "true" caso seja pra excluir a rota do travel plan
                        },{...},...] */
var last_end_type = null;
var last_end_id = null;
var last_end = null;
var last_end_quarry_id = null;
var last_end_terminal_id = null;

/* functions */
function show_dialog(tipo, id)
{
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
    edt_description.prop("readonly", !valor);
    btn_add_route.attr("disabled", !valor);
    rec_permite_alterar = valor;
}

function limpa_formulario(tipo)
{
    arr_routes = [];
    render_routes();

    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_description.val('');

    set_focus(edt_description);

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_plan/template/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            if (response.hasOwnProperty('id')) {
                rec_id.val(response.id);
                edt_description.val(response.description);

                arr_routes = response.items;

                render_routes();
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var vld = new Validation();

    if (edt_description.val().length == 0) {
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the description'));
    }

    if (!vld.is_valid()) {
        alert_modal('Validation', vld);
    }

    return vld.is_valid();
}

function envia_detalhes()
{
    if (post_tipo.val() == FORMULARIO.EXCLUIR || valida_formulario())
    {
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>travel_plan/template/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                description: edt_description.val(),
                routes: arr_routes
            },
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe');
                    listar();

                    var tipo = parseInt(post_tipo.val(), 10);
                    switch (tipo)
                    {
                        case FORMULARIO.NOVO:
                            alert_saved($('#edt_description').val() + ' saved successfully');
                            break;
                        case FORMULARIO.EDITAR:
                            alert_saved($('#edt_description').val() + ' saved successfully');
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved($('#edt_description').val() + ' deleted successfully');
                            break;
                    }
                }
            }
        });
    }
}

function render_routes()
{
    last_end_type = null;
    last_end_id = null;
    last_end = null;
    last_end_quarry_id = null;
    last_end_terminal_id = null;

    // limpa trs, menos a primeira
    tbl_routes.find("tr:gt(1)").remove();
    var arr_routes_not_removed = arr_routes.filter(function(item) {
        return (!(item.removed) || (item.removed == 'false'));
    });

    $.each(arr_routes_not_removed, function(i, item) {
        routes_add_row(tbl_routes_tbody, item, (i+1 == arr_routes_not_removed.length));
    });
}

function routes_add_row(table_body, item, remove_button)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_start_location = $(new_row.find("[template-field='start_location']"));
    field_start_location.text(item.start_location);

    var field_end_location = $(new_row.find("[template-field='end_location']"));
    field_end_location.text(item.end_location);

    var field_shipping_time = $(new_row.find("[template-field='shipping_time']"));
    field_shipping_time.text(item.shipping_time);

    if (item.end_quarry_id) {
        last_end_type = 'q';
        last_end_id = item.end_quarry_id;

        last_end_quarry_id = item.end_quarry_id;
        last_end_terminal_id = null;
    }
    else if (item.end_terminal_id) {
        last_end_type = 't';
        last_end_id = item.end_terminal_id;

        last_end_quarry_id = null;
        last_end_terminal_id = item.end_terminal_id;
    }

    last_end = last_end_type + last_end_id;

    var button_remove = $(new_row.find("[template-button='remove']"));
    if ((remove_button) && (rec_permite_alterar)) {
        button_remove.attr('template-ref', item.id);
        button_remove.click(function() {
            var id = $(this).attr('template-ref');

            var remove_travel_route = function() {
                $.each(arr_routes, function(index, route) {
                    if (route == item) {
                        arr_routes[index].removed = 'true';
                    }
                });
                /*
                arr_routes.splice((function() {
                    for (var i = 0; i < arr_routes.length; i++) {
                        if (arr_routes[i] == item) {
                            return i;
                        }
                    }
                })(), 1);
                */
                closeModal('alert_modal');
                render_routes();
            }

            alert_modal('Remove Travel Route', 'Remove travel route: ' + item.start_location + ' to ' + item.end_location + ' ?', 'Yes, remove this travel route', remove_travel_route, true);
        });
    }
    else {
        button_remove.hide();
    }

    new_row.appendTo(table_body);
}