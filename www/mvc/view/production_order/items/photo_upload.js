var btn_upload_photo = $('#btn_upload_photo');

var upload_files;
var photo_production_order_item_id;
var photo_block_id;
var photo_block_number;
var div_photos_active;
var div_photo_template_active;

function abre_photo_upload(block_id, block_number, production_order_item_id, div_photos, div_photo_template)
{
    div_photos_active = div_photos;
    photo_production_order_item_id = production_order_item_id;
    photo_block_id = block_id;
    photo_block_number = block_number;
    div_photo_template_active = div_photo_template;

    $('#anexo_input').val('');
    $('#edt_obs').val('');
    $('#progress_photo_upload').hide();

    showModal('modal_detalhe_photo_upload');

    $('#modal_detalhe_photo_upload_label').text('Upload a photo of ' + block_number);

    $('#modal_detalhe_photo_upload input[type=file]').on('change', prepare_upload);

    set_focus($('#anexo_input'));
}

// Grab the files and set them to our variable
function prepare_upload(event)
{
    upload_files = event.target.files;
}

// Catch the form submit and upload the files
function upload_photo(event)
{
    //event.stopPropagation(); // Stop stuff happening
    //event.preventDefault(); // Totally stop stuff happening

    btn_upload_photo.prop('disabled', true);
    $('#progress_photo_upload').show();

    // Create a formdata object and add the files
    var data = new FormData();
    $.each(upload_files, function(key, value) {
        data.append(key, value);
    });

    data.append('production_order_item_id', photo_production_order_item_id);
    data.append('block_id', photo_block_id);
    data.append('obs', $('#edt_obs').val());

    $.ajax({
        url: APP_URI + 'block/photo/upload/',
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false, // Don't process the files
        contentType: false, // Set content type to false as jQuery will tell the server its a query string request
        success: function(response) {
            if (response_validation(response)) {
                if (div_photos_active && div_photo_template_active && response.photos) {
                    setTimeout(function() {
                        closeModal('modal_detalhe_photo_upload');
                    }, 800);
                    $.each(response.photos, function(i, photo) {
                        //adiciono nova foto na listagem
                        var new_photo = div_photo_template_active.clone();

                        new_photo.attr("template", "");
                        new_photo.css("display", "");
                        new_photo.find("img").css('cursor', 'pointer');
                        new_photo.find("img").attr('src', photo.small_url);
                        new_photo.find("img").click(function() {
                            abre_photo_view(photo_block_number, photo, new_photo);
                        });

                        div_photos_active.append(new_photo);
                    });                    
                }
                $('#progress_photo_upload').hide();
                btn_upload_photo.prop('disabled', false);
            }
        },
        error: ajaxError
    });
}