var btn_refuse_ok = $('#btn_refuse_ok');
btn_refuse_ok.click(btn_refuse_ok_click);

function btn_refuse_ok_click() {
	var refuse_rec_id = $('#refuse_rec_id');
	var edt_refuse_block_number = $('#edt_refuse_block_number');
    var edt_refuse_reason = $('#edt_refuse_reason');

    if (edt_refuse_reason.val().trim().length > 0) {
        $(selected_block).attr('template-ref-refuse-reason', edt_refuse_reason.val());

        for (var i = 0; i < blocks.length; i++) {
            var block = blocks[i];
            if (blocks[i].id == refuse_rec_id.val()) {
                blocks[i].refused = true;
                blocks[i].refused_reason = edt_refuse_reason.val();
            }
        };

        closeModal('modal_refuse');
    }
    else {
        alert_modal('Validation', 'Invalid refuse reason.');
    }

}
