var permissions = <?= json_encode(\Sys\Permissions::$permissions) ?>;
var user_permissions;

function per_show_dialog(user_id)
{
    //limpa_formulario();
    per_list_permission(user_id);
    
    showModal('modal_detalhe_permission');
}

function per_list_quarries(selected_values, readonly)
{
    var cbo_quarries = $('#cbo_quarries');
    cbo_quarries.find("option").remove();

    cbo_quarries.select2("readonly", false);

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quarry/list/json/", function(response) {
        
        $.each(response, function(i, item) {
            add_option(cbo_quarries, item.id, item.name);
        });

        cbo_quarries.select2();

        if ((readonly) && (readonly === true)) {
            cbo_quarries.select2("readonly", true);
        }

        if (selected_values)
            cbo_quarries.val(selected_values).trigger("change");
        
        set_focus(cbo_quarries);
        
    }).fail(ajaxError);
}

function per_list_permission(user_id)
{
    var btn_check_all = $('#tbl_permissions').find('[template-button="check_all"]');
    var btn_uncheck_all = $('#tbl_permissions').find('[template-button="uncheck_all"]');

    btn_check_all.unbind('click');
    btn_check_all.click(btn_permission_check_click);

    btn_uncheck_all.unbind('click');
    btn_uncheck_all.click(btn_permission_uncheck_click);

    // limpa trs, menos a primeira
    //
    $('#tbl_permissions').find("tr:gt(1)").remove();

    var table_body = $('#tbl_permissions > tbody');

    for (var key in permissions) {
        per_add_row(table_body, key, permissions[key]);
    }

    per_carrega_formulario(user_id);
}

function per_add_row(table_body, key, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', key);

    var field_name = $(new_row.find("[template-field='name']"));
    field_name.text(item.name);

    var field_description = $(new_row.find("[template-field='description']"));
    field_description.text(item.description);

    new_row.appendTo(table_body);
}

function per_carrega_formulario(user_id)
{
    var per_edt_user_name = $('#per_edt_user_name');
    var cbo_quarries = $('#cbo_quarries');

    // limpa os campos
    per_edt_user_name.text('');

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>user/detail/json/" + user_id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            
            
            if (response.hasOwnProperty('id'))
            {
                user_permissions = response.permissions;
                rec_id.val(response.id);
                per_edt_user_name.text(response.name);
                per_list_quarries(response.quarries, response.admin == true);
                per_compare();
            }
        }
    }).fail(ajaxError);
}

function per_compare()
{
    var rows = $('[template-row-ref]');

    $(rows).each(function() {
        var row = $(this);
        var row_permission_key = row.attr('template-row-ref');
        var row_checkbox = row.find('[type="checkbox"]');
        //row_checkbox.prop('checked', true);
        row_checkbox.prop('checked', false);
        for (var i = 0; i < user_permissions.length; i++) {
            if (user_permissions[i] == row_permission_key) {
                row_checkbox.prop('checked', true);
            }
        };

    });
}

function per_envia_detalhes()
{
    var post_tipo = $('#post_tipo');

    if (per_valida_formulario())
    {
        var modal_detalhe = $('#modal_detalhe');
        var rec_id = $('#rec_id');
        var cbo_quarries = $('#cbo_quarries');
        var per_edt_user_name = $('#per_edt_user_name');

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>user/permissions/save/",
            data: {
                id: rec_id.val(),
                quarries: cbo_quarries.val(),
                permissions: per_prepare_permissions()
            },
            success: function (response) {
                if (response_validation(response)) {
                    alert_saved(per_edt_user_name.text() + ' permissions saved successfully');
                    closeModal('modal_detalhe_permission');
                }
            }
        });
    }
}

function per_valida_formulario()
{
    var vld = new Validation();
    var cbo_quarries = $('#cbo_quarries');

    if (!cbo_quarries.val() || cbo_quarries.val().length == 0)
    {
        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Select at least one quarry'));
    }

    if (!vld.is_valid()) {
        alert_modal('Validation', vld);
    }

    return vld.is_valid();
}

function per_prepare_permissions()
{
    var selected_permissions = [];
    var rows = $('[template-row-ref]');

    $(rows).each(function() {
        var row = $(this);
        var row_permission_key = row.attr('template-row-ref');
        var row_checkbox = row.find('[type="checkbox"]');
        
        if (row_checkbox.prop('checked')) {
            selected_permissions.push(row_permission_key);
        }
    });

    return selected_permissions;
}

function btn_permission_check_click() {
    $('#tbl_permissions').find('[type="checkbox"]').prop('checked', true).trigger("change");
}

function btn_permission_uncheck_click() {
    $('#tbl_permissions').find('[type="checkbox"]').prop('checked', false).trigger("change");
}