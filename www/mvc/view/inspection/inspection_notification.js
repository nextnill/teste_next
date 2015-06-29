// FUNCOES
function show_dialog()
{
   
    showModal('modal_inspection_notification');
}

function close_modal(){

	closeModal('modal_inspection_notification');
	//window.location.replace('<?= APP_URI ?>poblo/list/');		
}

function carrega_email(){	

	 $.getJSON("<?= APP_URI ?>inspection/load_email/", function(response) {
        if (response_validation(response)) {
           var edt_email = $('.edt_email');
          
            edt_email.val(response);
        }
        else{

        	edt_email.val();
        }
    }).fail(ajaxError);

}

function save_inspection_notification(){

	var edt_email = $('.edt_email');

	$.ajax({

		error: ajaxError,
		type: "POST",
		url: "<?= APP_URI ?>inspection/inspection_notification/save/",
		data: {

			email_notification: edt_email.val()
		},
		success: function(response){
			if(response_validation(response)){

				closeModal('modal_inspection_notification');
				alert_saved('saved successfully');
				//window.location.replace('<?= APP_URI ?>poblo/list/');
			}
		}
	});
}

funcs_on_load.push(function() {

	show_dialog();  
	carrega_email(); 
});