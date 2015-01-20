var btn_poblo_obs_cancel = $('#btn_poblo_obs_cancel');
var btn_poblo_obs_save = $('#btn_poblo_obs_save');

function show_poblo_obs(lot_transport_id, lot_number) {

	WS.get(
        "poblo/obs/json/",
        {
            lot_transport_id: lot_transport_id
        },
        function (response) {
        	$('#poblo_obs_rec_id').val(lot_transport_id);
			$('#edt_poblo_obs_lot_number').val(lot_number);
			$('#edt_poblo_obs').val(response.obs);
        	showModal('modal_poblo_obs');
        }
    );
}

btn_poblo_obs_cancel.unbind('click');
btn_poblo_obs_cancel.click(function() {
	closeModal('modal_poblo_obs');
});

btn_poblo_obs_save.unbind('click');
btn_poblo_obs_save.click(function() {
	
	var lot_transport_id = $('#poblo_obs_rec_id').val();
	var obs = $('#edt_poblo_obs').val();
	WS.post(
        "poblo/save/",
        {
            lot_transport_id: lot_transport_id,
            obs: obs
        },
        function (response) {
        	listar_blocks();
			closeModal('modal_poblo_obs');
        }
    );
});