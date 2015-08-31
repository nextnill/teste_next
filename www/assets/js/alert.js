function alert_modal(titulo, mensagem, btn_label, btn_ok, cancel_button, cancel_label)
{
    var alert_modal_mensagem = $('#alert_modal_mensagem');
    if (typeof mensagem == 'string') {
        alert_modal_mensagem.html(mensagem);
    }
    else if (typeof mensagem == 'object') {
        var groups = [];

        for (var i = 0; i < mensagem.messages.length; i++) {
            var msg = mensagem.messages[i];
            
            var grp_exists = false;
            for (var j = 0; j < groups.length; j++) {
                var grp = groups[j];
                if (grp.ref == msg.ref) {
                    grp_exists = true;
                    grp.messages.push(msg.message);
                }
            };
            if (!grp_exists) {
                var grp = {ref: msg.ref, messages: [msg.message]};
                groups.push(grp);
            }

        };
        
        alert_modal_mensagem.html('');

        for (var i = 0; i < groups.length; i++) {
            var grp = groups[i];
            var ul = $("<ul class=\"text-danger\">" + grp.ref + "</ul>");

            for (var j = 0; j < grp.messages.length; j++) {
                var msg = grp.messages[j];
                ul.append("<li class=\"text-info\">" + msg + "</li>");
            };
            alert_modal_mensagem.append(ul);
        };
    }

    $('#alert_modal_title').html(titulo);
    
    if (btn_label) {
        $("#alert_modal_ok").html(btn_label);
    }
    else {
        $("#alert_modal_ok").html('Ok');
    }

    $("#alert_modal_ok").unbind('click');

    if (!btn_ok) {
        btn_ok = function() { closeModal('alert_modal'); };
    }

    if ((cancel_button) && (cancel_button === true)) {
        $("#alert_modal_cancel").show();
        $("#alert_modal_close").show();
    }
    else {
        $("#alert_modal_cancel").hide();
        $("#alert_modal_close").hide();
    }

    if (cancel_label) {
        $("#alert_modal_cancel").html(cancel_label);
    }
    else {
        $("#alert_modal_cancel").html('Cancel');
    }


    $("#alert_modal_ok").click(btn_ok);

    showModal('alert_modal');
}

function response_validation(result) {
    if (result.validation) {
        var vld_result = new Validation();

        if (result.validation.messages) {
            for (var i = 0; i < result.validation.messages.length; i++) {
                vld_result.add(new ValidationMessage(result.validation.messages[i].code, result.validation.messages[i].message));
            };
        }

        alert_modal('Validation', vld_result);
        return false;
    }else if(result.messages){
        var vld_result = new Validation();
       
        for (var i = 0; i < result.messages.length; i++) {
            vld_result.add(new ValidationMessage(result.messages[i].code, result.messages[i].message));
        };
        

        alert_modal('Validation', vld_result);
        return false;
    }

    return true;
}