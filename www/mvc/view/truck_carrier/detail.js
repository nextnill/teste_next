

// elements
var edt_name = $('#edt_name');
var btn_save = $('#btn_save');
var tbl_list_trucks = $('.tbl_list_trucks');
var btn_add_truck = $('.btn_add_truck');

var array_trucks = [];
var tipo = null;
var carrier_id = null;

// FUNCOES
function show_dialog(tipo_param, id)
{

	carrier_id = id;
	tipo = tipo_param;
    limpa_form();

    switch(tipo)
    {
        case FORMULARIO.NOVO:
            btn_save.show();
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            break;
        case FORMULARIO.EDITAR:
            btn_save.show();
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            break;
        case FORMULARIO.VISUALIZAR:
            permite_alterar(false);
            btn_save.hide();
            btn_save.css('');
            
            break;
        case FORMULARIO.EXCLUIR:
            permite_alterar(false);
            btn_save.text('Delete');
            btn_save.addClass('btn btn-danger');
            btn_save.show();
            btn_save.css('');
           
            break;
    }

    if(carrier_id > 0){
        carrega_formulario();
    }

    showModal('modal_detalhe');
}

function add_row(truck_id){

	var new_row = tbl_list_trucks.find('[template-row]').clone();
	new_row.removeAttr('template-row');
	new_row.css('display', '');

	var edt_truck_code = $(new_row.find('[template-field="license_plate"]'));
	edt_truck_code.val(truck_id ? truck_id : '');

	edt_truck_code.unbind('change');
	edt_truck_code.change(function(){
		array_trucks.push(this.value);
	});

	var btn_remove = $(new_row.find('[template-button="btn_remove"]'));
	btn_remove.unbind('click');
	btn_remove.click(function(){
		new_row.remove();

		array_trucks = array_trucks.filter(function(item, i){
			return (item != edt_truck_code.val() ? true : false);
		});
	});

	new_row.appendTo(tbl_list_trucks.find('tbody'));

}

function carrega_formulario()
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>truck_carrier/detail/json/" + carrier_id, function(response) {


        edt_name.val(response.name);

        array_trucks = response.code_trucks;
        $(response.code_trucks).each(function(i, item){
            add_row(item);
        });
    });
}

function permite_alterar(valor){

    $('.input').prop('redonly', !valor);
    $('.btn_carrier').prop('disabled', !valor);

}

function limpa_form (){

	edt_name.val('');
	array_trucks = [];
    tbl_list_trucks.find("tr:gt(1)").remove();
}


function envia_detalhes()
{
    $.ajax({
        error: ajaxError,
        type: "POST",
        url: "<?= APP_URI ?>truck_carrier/" + (tipo == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
        data: {
            id: carrier_id,
            name: edt_name.val(),
            code_trucks: JSON.stringify(array_trucks),
        },
        success: function (response) {
            if (response_validation(response)) {
                closeModal('modal_detalhe');
                listar();

                
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

// events

btn_add_truck.unbind('click');
btn_add_truck.click(function(){
	add_row();
});

