var edt_pl_shipped_from = $('#edt_pl_shipped_from');
var edt_pl_client_notify_address = $('#edt_pl_client_notify_address');
var edt_pl_bl = $('#edt_pl_bl');
var edt_pl_dated = $('#edt_pl_dated');
var edt_pl_vessel = $('#edt_pl_vessel');
var edt_pl_commercial_invoice_number = $('#edt_pl_commercial_invoice_number');
var edt_pl_packing_list_ref = $('#edt_pl_packing_list_ref');
var btn_save_packing_list = $('.btn_save_packing_list');

var down_pl_lot_transport_id = 0;
var call_back_down_packing_list = null;

function clear_packing_list(){

    set_datepicker(edt_pl_dated, '00-00-0000');
    edt_pl_vessel.val('');
    edt_pl_dated.val('');
    edt_pl_bl.val('');
    edt_pl_shipped_from
    edt_pl_packing_list_ref.val('');
    edt_pl_commercial_invoice_number.val('');
    edt_pl_client_notify_address.val('');
}

function init_down_packing_list(lot_transport_id, call_back) {
    call_back_down_packing_list = call_back;
	down_pl_lot_transport_id = lot_transport_id;
	clear_packing_list();
	WS.get(
        "lots/detail/json/" + down_pl_lot_transport_id, {},
        function (response) {
        	edt_pl_shipped_from.val(response.shipped_from);
            edt_pl_client_notify_address.val(response.client_notify_address);
            edt_pl_bl.val(response.bl);
            set_datepicker(edt_pl_dated, response.packing_list_dated);
            edt_pl_vessel.val(response.vessel);
            edt_pl_commercial_invoice_number.val(response.commercial_invoice_number);
            edt_pl_packing_list_ref.val(response.packing_list_ref);
        }
    );

}

function save_packing_list(download){

    // chamar webservice que atualiza esses dados
    WS.post(
        "travel_plan/packing_list/save/",
        {
            lot_transport_id: down_pl_lot_transport_id,
            shipped_from: edt_pl_shipped_from.val(),
            client_notify_address: edt_pl_client_notify_address.val(),
            bl: edt_pl_bl.val(),
            packing_list_dated: get_datepicker(edt_pl_dated),
            vessel: edt_pl_vessel.val(),
            commercial_invoice_number: edt_pl_commercial_invoice_number.val(),
            packing_list_ref: edt_pl_packing_list_ref.val()
        },
        function (response) {
            

            if(response){
                if(download === true){
                    window.open(
                      '<?= APP_URI ?>travel_plan/packing_list/download/?lot_transport_id=' + down_pl_lot_transport_id,
                      '_blank'
                    );

                    if(typeof call_back_down_packing_list != 'undefined' && call_back_down_packing_list){
                        call_back_down_packing_list(download);
                    }
                    
                }
                closeModal('modal_down_packing_list');
            }else{
                closeModal('modal_down_packing_list');
            }
        }
    );

}

btn_save_packing_list.unbind('click');
btn_save_packing_list.click(function(){
    
    save_packing_list(false);
    
});

function btn_down_packing_list_click() {
	// chamar webservice que atualiza esses dados
	save_packing_list(true);

}