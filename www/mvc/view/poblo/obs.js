var btn_poblo_obs_cancel = $('#btn_poblo_obs_cancel');
var btn_poblo_obs_save = $('#btn_poblo_obs_save');

var var_callback = null;

function show_poblo_obs(block_id, block_number, callback) {

    if(callback){
        var_callback = callback;
    }
    

    $('#edt_poblo_obs').attr('block_id', block_id);
    $('#edt_poblo_obs_block_number').val(block_number);

	WS.get(
        "poblo/obs/json/",
        {
            block_id: block_id,
        },
        function (response) {
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
	
	var obs = $('#edt_poblo_obs').val();

	WS.post(
        "poblo/save/",
        {
            block_id: $('#edt_poblo_obs').attr('block_id'),
            obs: obs
        },
        function (response) {

            var_callback(response.obs);
			closeModal('modal_poblo_obs');
        }
    );
});
