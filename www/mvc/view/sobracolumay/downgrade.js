var btn_downgrade_cancel = $('#btn_downgrade_cancel');
var btn_downgrade_confirm = $('#btn_downgrade_confirm');

var var_callback = null;

function show_downgrade(block_id, block_number, block_number_interim, quarry_id) {

    $('#edt_downgrade_block_number').attr('block_id', block_id);
    $('#edt_downgrade_block_number').attr('block_number', block_number);
    $('#edt_downgrade_block_number').val(block_number_interim);

    if (block_number_interim != '') {
        $('#edt_downgrade_block_number').val(block_number_interim);        
    } else {    
        $.getJSON("<?= APP_URI ?>quarry/nextval/interim/" + quarry_id, function(response) {
            if (response_validation(response)) {
                $('#edt_downgrade_block_number').val(response[0].block_number);
            }
        }).fail(ajaxError);
    }    

    showModal('modal_downgrade');
}

btn_downgrade_confirm.unbind('click');
btn_downgrade_confirm.click(function() {

        WS.post(
            "block/downgrade/",
            {
                block_id: $('#edt_downgrade_block_number').attr('block_id'),
                block_number_interim: $('#edt_downgrade_block_number').val()
            },
            function (response) {
               closeModal('modal_downgrade');
               listar_downgrade();
            }
        );
});


btn_downgrade_cancel.unbind('click');
btn_downgrade_cancel.click(function() {
    closeModal('modal_downgrade');
});
