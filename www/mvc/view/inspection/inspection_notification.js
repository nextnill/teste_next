
var tab_list_notification_group = $('.tab_list_notification_group');
var edt_email = $('.edt_email');


var arr_email = [];


// FUNCOES
function show_dialog()
{
   
    showModal('modal_inspection_notification');
}

function close_modal(){

	closeModal('modal_inspection_notification');
	//window.location.replace('<?= APP_URI ?>poblo/list/');		
}

function list_groups_client(email_notification){

	tab_list_notification_group.find("tr:gt(1)").remove();

	var table_body = tab_list_notification_group.find('tbody');
    var template_row = table_body.find("tr:first");

    var add_row = function(item){

    	var new_row = template_row.clone();
	    new_row.removeAttr("template-row");
	    new_row.css("display", '');
	    new_row.attr('id', (item.id > 0 ? item.id : item.client_group_id));
	    new_row.addClass('group_email');

	    var field_name = $(new_row.find("[template-field='group_name']"));
    	field_name.text(item.name);

    	var field_email = $(new_row.find("[template-field='edt_email']"));
    	if(typeof item.email_notification != 'undefined'){
    		field_email.val(item.email_notification);
    	}

    	new_row.appendTo(table_body);
    }


    if(email_notification && email_notification.length > 0){
    	$.each(email_notification, function(i, item) {
        	if((typeof item.name == 'undefined' || item.name == null || item.name == '')&&(item.client_group_id == null)){
        		edt_email.val(item.email_notification);
        	}else{
        		add_row(item);
        	}
            
        });
    }else{
    	// pesquisa a listagem em json
	    $.getJSON("<?= APP_URI ?>client_group/list/json/", function(response) {
	        if (response_validation(response)) {


	            $.each(response, function(i, item) {
	            	
	            		add_row(item);
	            	
	                
	            });
	        }

	    }).fail(ajaxError);
    }
    

}

function carrega_email(){	

	 $.getJSON("<?= APP_URI ?>inspection/load_email/", function(response) {
        if (response_validation(response)) {
           
           list_groups_client(response);
            //edt_email.val(response);
        }
        else{

        	//edt_email.val();
        }
    }).fail(ajaxError);

}

function save_inspection_notification(){

	var obj_email = {
		id: null,
		email_notification: null,
		client_group_id: null,
		excluido: 'N'
	}; 

	obj_email.email_notification = edt_email.val();
	arr_email.push(obj_email);

	$(tab_list_notification_group.find('.group_email')).each(function(i, item){

		var obj_email = {
			id: null,
			email_notification: null,
			client_group_id: null,
			excluido: 'N'
		}; 

		var client_group_id = $(item).attr('id');
		var email = $(item).find('input').val();

		obj_email.client_group_id = client_group_id;
		obj_email.email_notification = email;

		arr_email.push(obj_email);
	});

	$.ajax({

		error: ajaxError,
		type: "POST",
		url: "<?= APP_URI ?>inspection/inspection_notification/save/",
		data: {

			email_notification: JSON.stringify(arr_email),
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