var btn_upload_draft = $('#btn_upload_draft');
var upload_files;
var draft_lot_id;

function show_dialog_send(id, lot_number)
{

    draft_lot_id = id;

    $('#anexo_input').val('');
    $('#progress_up_draft').hide();

    showModal('modal_up_draft');
    

    $('#modal_up_draft_label').text('Upload a draft file of ' + lot_number);

    $('#modal_up_draft input[type=file]').on('change', prepare_upload); 
    
    set_focus($('#anexo_input'));
}

function prepare_upload(event)
{
    upload_files = event.target.files;
}


function upload_file(event)
{
    //event.stopPropagation(); // Stop stuff happening
    //event.preventDefault(); // Totally stop stuff happening

    btn_upload_draft.prop('disabled', true);
    $('#progress_up_draft').show(); 

    // Create a formdata object and add the files
    var data = new FormData();
    $.each(upload_files, function(key, value) {
        data.append(key, value);
    });

    data.append('id', draft_lot_id);
    
    $.ajax({
        url: APP_URI + '/travel_plan/draft/save/',
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function(response) {
            if (response_validation(response)) {
                    setTimeout(function() {
                        closeModal('modal_up_draft');
                        listar_blocks();
                    }, 800);                 
                $('#progress_up_draft').hide();
                btn_upload_draft.prop('disabled', false);
            }
            else{
                btn_upload_draft.prop('disabled', false);
                $('#progress_up_draft').hide();
            }
        },
        error: ajaxError
    });
}
