var edt_pl_shipped_from = $('#edt_pl_shipped_from');
var edt_pl_client_notify_address = $('#edt_pl_client_notify_address');
var edt_pl_bl = $('#edt_pl_bl');
var edt_pl_dated = $('#edt_pl_dated');
var edt_pl_vessel = $('#edt_pl_vessel');
var edt_pl_commercial_invoice_number = $('#edt_pl_commercial_invoice_number');
var edt_pl_packing_list_ref = $('#edt_pl_packing_list_ref');

var down_pl_lot_transport_id = 0;

function init_down_packing_list(lot_transport_id) {
	down_pl_lot_transport_id = lot_transport_id;
	
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

function btn_down_packing_list_click() {
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
        	listar_blocks();
			window.open(
			  '<?= APP_URI ?>travel_plan/packing_list/download/?lot_transport_id=' + down_pl_lot_transport_id,
			  '_blank'
			);
			closeModal('modal_down_packing_list');
        }
    );

}