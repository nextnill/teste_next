

var cbo_carrier = $('.cbo_carrier');
var edt_new_truck = $('.edt_new_truck');

var div_trucks = null;


function show_modal_truck(div_trucks_template){
	clear();
	render_carrier_truck();
	div_trucks = div_trucks_template;
	showModal('modal_detalhe');
}

function clear(){
	edt_new_truck.val('');
	cbo_carrier.val('').trigger('change');
}

function render_carrier_truck(){
	$.getJSON("<?= APP_URI ?>truck_carrier/list/json/", function(response) {
        if (response_validation(response)) {

            cbo_carrier.find('option').remove();

            add_option(cbo_carrier, '', '- Select -');
            
            $(response).each(function(i, item) {
                add_option(cbo_carrier, item.id, item.name);
            });

            cbo_carrier.selectpicker('refresh');
        }
    }).fail(ajaxError);
}



function send_new_truck (){

	$.ajax({
        error: ajaxError,
        type: "POST",
        url: "<?= APP_URI ?>truck_carrier/save_one_truck/",
        data: {
            carrier_id: cbo_carrier.val(),
            truck_id: edt_new_truck.val(),
        },
        success: function (response) {

            if (response_validation(response)) {
                closeModal('modal_detalhe');
                list_trucks();
                alert_saved('saved successfully');
            }
        }
    });
}