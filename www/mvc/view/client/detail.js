function listar_head_office(selected_value, client_id)
{
    var cbo_head_office = $('#cbo_head_office');
    cbo_head_office.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_head_office/json/", function(response) {
        add_option(cbo_head_office, '', 'None');
        if (response_validation(response)) {
            for (var i = 0; i < response.length; i++) {
                var item = response[i];

                if ((!client_id) || (client_id != item.id)) {
                    add_option(cbo_head_office, item.id, item.code + ' - ' + item.name);
                }
                
            };
            
            if (selected_value)
                cbo_head_office.val(selected_value).trigger("change");
        }        
    }).fail(ajaxError);
}

function listar_client_groups(selected_values)
{
    var cbo_client_groups = $('#cbo_client_groups');
    cbo_client_groups.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client_group/list/json/", function(response) {
        if (response_validation(response)) {
            $.each(response, function(i, item) {
                add_option(cbo_client_groups, item.id, item.name);
            });
            
            if (selected_values)
                cbo_client_groups.val(selected_values).trigger("change");
        }
    }).fail(ajaxError);
}

function listar_agencies(selected_values)
{
    var cbo_agencies = $('#cbo_agencies');
    cbo_agencies.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>agency/list/json/", function(response) {
        if (response_validation(response)) {
            $.each(response, function(i, item) {
                add_option(cbo_agencies, item.id, item.name);
            });

            if (selected_values)
                cbo_agencies.val(selected_values).trigger("change");
        }
    }).fail(ajaxError);
}

function listar_portos(loading_selected_values, discharge_selected_values)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>terminal/list/json/port", function(response) {
        if (response_validation(response)) {
            listar_port_of_loading(loading_selected_values, response);
            //listar_port_of_discharge(discharge_selected_values, response);
        }
    }).fail(ajaxError);
}

/*
function listar_port_of_discharge(selected_values, response)
{
    var cbo_port_of_discharge = $('#cbo_port_of_discharge');
    cbo_port_of_discharge.find("option").remove();

    $.each(response, function(i, item) {
        add_option(cbo_port_of_discharge, item.id, item.name);
    });

    if (selected_values)
        cbo_port_of_discharge.val(selected_values).trigger("change");
}
*/

function listar_port_of_loading(selected_values, response)
{
    var cbo_port_of_loading = $('#cbo_port_of_loading');
    cbo_port_of_loading.find("option").remove();

    $.each(response, function(i, item) {
        add_option(cbo_port_of_loading, item.id, item.name);
    });

    if (selected_values)
        cbo_port_of_loading.val(selected_values).trigger("change");
}

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
    var edt_name = $('#edt_name');
    var edt_code = $('#edt_code');
    var chk_com_inv = $('#chk_com_inv');
    var chk_pack_list = $('#chk_pack_list');
    var chk_bl = $('#chk_bl');
    var chk_certif_orig = $('#chk_certif_orig');
    var chk_proforma_invoice = $('#chk_proforma_invoice');
    var chk_fumigation_certificate = $('#chk_fumigation_certificate');
    var chk_bill_of_lading = $('#chk_bill_of_lading');
    var cbo_head_office = $('#cbo_head_office');
    var edt_terms_of_payment = $('#edt_terms_of_payment');
    var edt_contact = $('#edt_contact');
    var edt_telephone = $('#edt_telephone');
    var edt_mobile = $('#edt_mobile');
    var edt_fax = $('#edt_fax');
    var edt_email = $('#edt_email');
    var edt_contact_other = $('#edt_contact_other');
    var edt_eori = $('#edt_eori');
    var cbo_client_groups = $('#cbo_client_groups');
    var cbo_agencies = $('#cbo_agencies');
    var cbo_ports = $('#cbo_ports');
    var edt_consignee = $('#edt_consignee');
    var edt_notify_address = $('#edt_notify_address');
    var edt_marks = $('#edt_marks');
    var edt_destination_port = $('#edt_destination_port');
    //var cbo_port_of_discharge = $('#cbo_port_of_discharge');
    var cbo_port_of_loading = $('#cbo_port_of_loading');
    var edt_obs_body_of_bl = $('#edt_obs_body_of_bl');
    var edt_desc_of_goods = $('#edt_desc_of_goods');
    var edt_obs = $('#edt_obs');

    edt_name.prop("readonly", !valor);
    edt_code.prop("readonly", !valor);
    chk_com_inv.prop("disabled", !valor);
    chk_pack_list.prop("disabled", !valor);
    chk_bl.prop("disabled", !valor);
    chk_certif_orig.prop("disabled", !valor);
    chk_proforma_invoice.prop("disabled", !valor);
    chk_fumigation_certificate.prop("disabled", !valor);
    chk_bill_of_lading.prop("disabled", !valor);
    cbo_head_office.select2("readonly", !valor);
    edt_terms_of_payment.prop("readonly", !valor);
    edt_contact.prop("readonly", !valor);
    edt_telephone.prop("readonly", !valor);
    edt_mobile.prop("readonly", !valor);
    edt_fax.prop("readonly", !valor);
    edt_email.prop("readonly", !valor);
    edt_contact_other.prop("readonly", !valor);
    edt_eori.prop("readonly", !valor);
    cbo_client_groups.select2("readonly", !valor);
    cbo_agencies.select2("readonly", !valor);
    cbo_ports.select2("readonly", !valor);
    edt_consignee.prop("readonly", !valor);
    edt_notify_address.prop("readonly", !valor);
    edt_marks.prop("readonly", !valor);
    edt_destination_port.prop("readonly", !valor);
    //cbo_port_of_discharge.select2("readonly", !valor);
    cbo_port_of_loading.select2("readonly", !valor);
    edt_obs_body_of_bl.prop("readonly", !valor);
    edt_desc_of_goods.prop("readonly", !valor);
    edt_obs.prop("readonly", !valor);
}

function limpa_formulario(tipo)
{
    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');

    var edt_name = $('#edt_name');
    var edt_code = $('#edt_code');
    var chk_com_inv = $('#chk_com_inv');
    var chk_pack_list = $('#chk_pack_list');
    var chk_bl = $('#chk_bl');
    var chk_certif_orig = $('#chk_certif_orig');
    var chk_proforma_invoice = $('#chk_proforma_invoice');
    var chk_fumigation_certificate = $('#chk_fumigation_certificate');
    var chk_bill_of_lading = $('#chk_bill_of_lading');
    var cbo_head_office = $('#cbo_head_office');
    var lbl_branch_offices = $('#lbl_branch_offices');
    var edt_terms_of_payment = $('#edt_terms_of_payment');
    var edt_contact = $('#edt_contact');
    var edt_telephone = $('#edt_telephone');
    var edt_mobile = $('#edt_mobile');
    var edt_fax = $('#edt_fax');
    var edt_email = $('#edt_email');
    var edt_contact_other = $('#edt_contact_other');
    var edt_eori = $('#edt_eori');
    var cbo_client_groups = $('#cbo_client_groups');
    var cbo_agencies = $('#cbo_agencies');
    var cbo_ports = $('#cbo_ports');
    var edt_consignee = $('#edt_consignee');
    var edt_notify_address = $('#edt_notify_address');
    var edt_marks = $('#edt_marks');
    var edt_destination_port = $('#edt_destination_port');
    //var cbo_port_of_discharge = $('#cbo_port_of_discharge');
    var cbo_port_of_loading = $('#cbo_port_of_loading');
    var edt_obs_body_of_bl = $('#edt_obs_body_of_bl');
    var edt_desc_of_goods = $('#edt_desc_of_goods');
    var edt_obs = $('#edt_obs');

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');

    edt_name.val('');
    edt_code.val('');
    chk_com_inv.prop('checked', false);
    chk_pack_list.prop('checked', false);
    chk_bl.prop('checked', false);
    chk_certif_orig.prop('checked', false);
    chk_proforma_invoice.prop('checked', false);
    chk_fumigation_certificate.prop('checked', false);
    chk_bill_of_lading.prop('checked', false);
    cbo_head_office.val('').trigger('change');
    lbl_branch_offices.text('');
    edt_terms_of_payment.val('');
    edt_contact.val('');
    edt_telephone.val('');
    edt_mobile.val('');
    edt_fax.val('');
    edt_email.val('');
    edt_contact_other.val('');
    edt_eori.val('');
    cbo_client_groups.val('').trigger('change');
    cbo_agencies.val('').trigger('change');
    cbo_ports.val('').trigger('change');
    edt_consignee.val('');
    edt_notify_address.val('');
    edt_marks.val('');
    edt_destination_port.val('');
    //cbo_port_of_discharge.val('');
    cbo_port_of_loading.val('').trigger('change');;
    edt_obs_body_of_bl.val('');
    edt_desc_of_goods.val('');
    edt_obs.val('');

    set_focus(edt_name);

    if (tipo == FORMULARIO.NOVO) {
        listar_head_office();
        listar_client_groups();
        listar_agencies();
        listar_portos();
        //listar_port_of_discharge();
        //listar_port_of_loading();
    }

    permite_alterar(true);
}

function carrega_formulario(id)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var edt_name = $('#edt_name');
            var edt_code = $('#edt_code');
            var chk_com_inv = $('#chk_com_inv');
            var chk_pack_list = $('#chk_pack_list');
            var chk_bl = $('#chk_bl');
            var chk_certif_orig = $('#chk_certif_orig');
            var chk_proforma_invoice = $('#chk_proforma_invoice');
            var chk_fumigation_certificate = $('#chk_fumigation_certificate');
            var chk_bill_of_lading = $('#chk_bill_of_lading');
            var pnl_head_office = $('#pnl_head_office');
            var cbo_head_office = $('#cbo_head_office');
            var pnl_branch_offices = $('#pnl_branch_offices');
            var lbl_branch_offices = $('#lbl_branch_offices');
            var edt_terms_of_payment = $('#edt_terms_of_payment');
            var edt_contact = $('#edt_contact');
            var edt_telephone = $('#edt_telephone');
            var edt_mobile = $('#edt_mobile');
            var edt_fax = $('#edt_fax');
            var edt_email = $('#edt_email');
            var edt_contact_other = $('#edt_contact_other');
            var edt_eori = $('#edt_eori');
            var cbo_client_groups = $('#cbo_client_groups');
            var cbo_agencies = $('#cbo_agencies');
            var cbo_ports = $('#cbo_ports');
            var edt_consignee = $('#edt_consignee');
            var edt_notify_address = $('#edt_notify_address');
            var edt_marks = $('#edt_marks');
            var edt_destination_port = $('#edt_destination_port');
            //var cbo_port_of_discharge = $('#cbo_port_of_discharge');
            var cbo_port_of_loading = $('#cbo_port_of_loading');
            var edt_obs_body_of_bl = $('#edt_obs_body_of_bl');
            var edt_desc_of_goods = $('#edt_desc_of_goods');
            var edt_obs = $('#edt_obs');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);

                edt_name.val(response.name);
                edt_code.val(response.code);
                
                if (response.doc_exig_com_inv == "S")
                    chk_com_inv.prop('checked', 'checked');

                if (response.doc_exig_pack_list == "S")
                    chk_pack_list.prop('checked', 'checked');
                
                if (response.doc_exig_bl == "S")
                    chk_bl.prop('checked', 'checked');
                
                if (response.doc_exig_certif_orig == "S")
                    chk_certif_orig.prop('checked', 'checked');

                if (response.doc_exig_proforma_invoice == "S")
                    chk_proforma_invoice.prop('checked', 'checked');

                if (response.doc_exig_fumigation_certificate == "S")
                    chk_fumigation_certificate.prop('checked', 'checked');

                if (response.doc_exig_bill_of_lading == "S")
                    chk_bill_of_lading.prop('checked', 'checked');

                if ((!response.branch_offices) || (response.branch_offices.length == 0))
                {
                    pnl_head_office.show();
                    pnl_branch_offices.hide();
                    listar_head_office(response.head_office_id, response.id);
                }
                else
                {
                    pnl_head_office.hide();
                    pnl_branch_offices.show();
                    var branch_offices = '';
                    for (var i = 0; i < response.branch_offices.length; i++) {
                        if (i > 0) {
                            branch_offices += ', ';
                        }
                        branch_offices += response.branch_offices[i].name;
                    };
                    lbl_branch_offices.text(branch_offices);
                }
                
                edt_terms_of_payment.val(response.terms_of_payment);
                edt_contact.val(response.contact);
                edt_telephone.val(response.telephone);
                edt_mobile.val(response.mobile);
                edt_fax.val(response.fax);
                edt_email.val(response.email);
                edt_contact_other.val(response.contact_other);
                edt_eori.val(response.eori);
                listar_client_groups(response.client_groups);
                listar_agencies(response.agencies);
                
                edt_consignee.val(response.consignee);
                edt_notify_address.val(response.notify_address);
                edt_marks.val(response.marks);
                edt_destination_port.val(response.destination_port);
                
                listar_portos(response.port_of_loading/*, response.port_of_discharge*/);
                //listar_port_of_discharge(response.port_of_discharge);
                //listar_port_of_loading(response.port_of_loading);

                edt_obs_body_of_bl.val(response.obs_body_of_bl);
                edt_desc_of_goods.val(response.desc_of_goods);
                edt_obs.val(response.obs);
            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_name = $('#edt_name');
    var edt_code = $('#edt_code');

    var valido = true;
    var msgs = new Array();

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
        var edt_name = $('#edt_name');
        var edt_code = $('#edt_code');
        var chk_com_inv = $('#chk_com_inv');
        var chk_pack_list = $('#chk_pack_list');
        var chk_bl = $('#chk_bl');
        var chk_certif_orig = $('#chk_certif_orig');
        var chk_proforma_invoice = $('#chk_proforma_invoice');
        var chk_fumigation_certificate = $('#chk_fumigation_certificate');
        var chk_bill_of_lading = $('#chk_bill_of_lading');
        var cbo_head_office = $('#cbo_head_office');
        var edt_terms_of_payment = $('#edt_terms_of_payment');
        var edt_contact = $('#edt_contact');
        var edt_telephone = $('#edt_telephone');
        var edt_mobile = $('#edt_mobile');
        var edt_fax = $('#edt_fax');
        var edt_email = $('#edt_email');
        var edt_contact_other = $('#edt_contact_other');
        var edt_eori = $('#edt_eori');
        var cbo_client_groups = $('#cbo_client_groups');
        var cbo_agencies = $('#cbo_agencies');
        var cbo_ports = $('#cbo_ports');
        var edt_consignee = $('#edt_consignee');
        var edt_notify_address = $('#edt_notify_address');
        var edt_marks = $('#edt_marks');
        var edt_destination_port = $('#edt_destination_port');
        //var cbo_port_of_discharge = $('#cbo_port_of_discharge');
        var cbo_port_of_loading = $('#cbo_port_of_loading');
        var edt_obs_body_of_bl = $('#edt_obs_body_of_bl');
        var edt_desc_of_goods = $('#edt_desc_of_goods');
        var edt_obs = $('#edt_obs');

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>client/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: {
                id: rec_id.val(),
                name: edt_name.val(),
                code: edt_code.val(),
                doc_exig_com_inv: chk_com_inv.prop('checked'),
                doc_exig_pack_list: chk_pack_list.prop('checked'),
                doc_exig_bl: chk_bl.prop('checked'),
                doc_exig_certif_orig: chk_certif_orig.prop('checked'),
                doc_exig_proforma_invoice: chk_proforma_invoice.prop('checked'),
                doc_exig_fumigation_certificate: chk_fumigation_certificate.prop('checked'),
                doc_exig_bill_of_lading: chk_bill_of_lading.prop('checked'),
                head_office: cbo_head_office.val(),
                terms_of_payment: edt_terms_of_payment.val(),
                contact: edt_contact.val(),
                telephone: edt_telephone.val(),
                mobile: edt_mobile.val(),
                fax: edt_fax.val(),
                email: edt_email.val(),
                contact_other: edt_contact_other.val(),
                eori: edt_eori.val(),
                client_groups: cbo_client_groups.val(),
                agencies: cbo_agencies.val(),
                ports: cbo_ports.val(),
                consignee: edt_consignee.val(),
                notify_address: edt_notify_address.val(),
                marks: edt_marks.val(),
                destination_port: edt_destination_port.val(),
                //port_of_discharge: cbo_port_of_discharge.val(),
                port_of_loading: cbo_port_of_loading.val(),
                obs_body_of_bl: edt_obs_body_of_bl.val(),
                desc_of_goods: edt_desc_of_goods.val(),
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

function btn_rd_check_click() {
    $('#div_rd').find('[type="checkbox"]').prop('checked', true).trigger("change");
}

function btn_rd_uncheck_click() {
    $('#div_rd').find('[type="checkbox"]').prop('checked', false).trigger("change");
}

// on load window
funcs_on_load.push(function() {
    $("#cbo_head_office").select2();
    $("#cbo_agencies").select2();
    $("#cbo_client_groups").select2();
    //$("#cbo_ports").select2();
    $("#cbo_port_of_loading").select2();
    $("#cbo_port_of_discharge").select2();

    var btn_check_all = $('#div_rd').find('[template-button="check_all"]');
    var btn_uncheck_all = $('#div_rd').find('[template-button="uncheck_all"]');

    btn_check_all.unbind('click');
    btn_check_all.click(btn_rd_check_click);

    btn_uncheck_all.unbind('click');
    btn_uncheck_all.click(btn_rd_uncheck_click);
});