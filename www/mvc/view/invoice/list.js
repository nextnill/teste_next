function listar_filter_client(selected)
{
    var cbo_filter_client = $('#cbo_filter_client');

    cbo_filter_client.find("option").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client/list_head_office/json/", function(response) {
        if (response_validation(response)) {
            add_option(cbo_filter_client, '-1', 'None');
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_filter_client, item.id, item.code + ' - ' + item.name);
            };

            cbo_filter_client.select2();

            if(selected != -1){
                cbo_filter_client.val(selected).trigger('change');
            }

            cbo_filter_client.unbind('change');
            cbo_filter_client.change(function() {
                listar();
            });

            listar();
        }
    }).fail(ajaxError);
}

function listar()
{
    var cbo_filter_client = $('#cbo_filter_client');
    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');
        
    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();
    
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>inspection_certificate/list/json/" + (cbo_filter_client.val() ? cbo_filter_client.val() : ''), 
        {ano: edt_year.val(), mes: cbo_month_filter.val()}, function(response) {
        if (response_validation(response)) {
            var table_body = $('#tbl_listagem > tbody');

            $.each(response, function(i, item) {
                add_row(table_body, item);
            });
        }
    }).fail(ajaxError);
}

function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_date_record = $(new_row.find("[template-field='date_record']"));
    field_date_record.text(item.date_record.format_date());

    var field_client_name = $(new_row.find("[template-field='client_name']"));
    field_client_name.text(item.client_name);

    var button_visualize = new_row.find("[template-button='blocks']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.attr('href', '<?= APP_URI ?>inspection_certificate/detail/' + item.id);

    new_row.appendTo(table_body);

    var button_select_excel = new_row.find("[template-button='select_excel']");
    button_select_excel.attr('template-ref', item.id);
    button_select_excel.click(function() {
        var invoice_id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>inspection_certificate/download_excel/?invoice_id='+invoice_id;
    });    

    var button_select = new_row.find("[template-button='select']");
    button_select.attr('template-ref', item.id);
    button_select.click(function() {
        var invoice_id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>inspection_certificate/download/?invoice_id='+invoice_id;
    });

    var button_edit = new_row.find("[template-button='edit']");
    button_edit.attr('template-ref', item.id);
    button_edit.click(function() {
        var invoice_id = $(this).attr('template-ref');
        window.location = '<?= APP_URI ?>inspection/blocks/' + item.client_id + '/' + invoice_id;
    });
    
    var button_email = new_row.find("[template-button='email']");
    button_email.attr('template-ref', item.id);
    button_email.click(function(){
        var invoice_id = $(this).attr('template-ref');
  
        $.ajax({

		error: ajaxError,
		type: "GET",
		url: '<?= APP_URI ?>inspection_certificate/download/?invoice_id='+invoice_id+'&send_email=1',
		success: function(response){
			if(response_validation(response)){
                if(response.email_enviado == true){
                    alert_saved('Email successfully sent');   
                }
				else{
                     alert_error('Error sending e-mail');
                }
				
			}
		}
	});
    
    });
    
    var button_delete = new_row.find("[template-button='delete']");
    button_delete.attr('template-ref', item.id);
    button_delete.click(function() {
        var id = $(this).attr('template-ref');
        var delete_action = function() {
            $.ajax({
                error: ajaxError,
                type: "POST",
                url: "<?= APP_URI ?>inspection_certificate/delete/",
                data: {
                    id: id
                },
                dataType: 'json',                
                success: function (response) {
                    setTimeout(function() {
                        if (response_validation(response)) {
                            if (response == '0'){
                                var vld = new Validation();
                                vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'There are blocks linked to lots in this invoice. Not permitted exclusion'));
                                alert_modal('Validation', vld);                                
                            } else{
                                closeModal('alert_modal');
                                listar();
                            }                            
                        }
                    }, 800);
                }
            });            
        };        
        alert_modal('Inspection', 'Delete Inspection Certificate #' + id + '?', 'Delete', delete_action, true);
    });
}

funcs_on_load.push(function() {

    var parametros = <?php echo json_encode($data); ?>;
    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');

    if(!parametros.ano && !parametros.mes){

        var agora = new Date();
        var mes = ("0" + (agora.getMonth() + 1)).slice(-2);
        var ano = agora.getFullYear();

        edt_year.val(ano);
        cbo_month_filter.val(mes);
        listar_filter_client();

    }
    else{

        edt_year.val(parametros.ano);
        cbo_month_filter.val(parametros.mes);
        listar_filter_client(parametros.client_id);  
    }
});
