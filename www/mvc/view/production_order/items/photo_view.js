function abre_photo_view(title, photo, div_photo)
{
    var modal_detalhe_photo_view_label = $('#modal_detalhe_photo_view_label');
    var img_photo_view = $('#img_photo_view');
    var div_photo_obs = $('#div_photo_obs');

    modal_detalhe_photo_view_label.text(title);
    img_photo_view.attr('src', photo.large_url);
    div_photo_obs.text(photo.obs);

    //btn_photo_delete
    $('#btn_photo_delete').unbind('click');
    $('#btn_photo_delete').click(function() {
        delete_photo(title, photo.id, div_photo);
    });

    showModal('modal_detalhe_photo_view');
}

function delete_photo(block_number, id, div_photo)
{
	var delete_click = function() {
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>block/photo/delete/",
            data: { id: id },
            success: function (response) {
            	setTimeout(function() {
            		closeModal('alert_modal');

	                if (response_validation(response)) {
	                    closeModal('modal_detalhe_photo_view');
	                    div_photo.fadeOut();
	                }

                }, 800);
            }
        });
    }

    alert_modal('Delete photo', 'Delete this photo from ' + block_number + ' ?', 'Yes, delete this photo', delete_click, true);
}

			