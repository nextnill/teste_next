var defects = [];
var qualities = [];

function load_defects(quarry_id, selected_values) {
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>defect/list/json/" + quarry_id, function(response) {
        if (response_validation(response)) {
            defects = [];

            for (var i = 0; i < response.length; i++) {
                defects.push(response[i]);
            };

            var template = $("[template-ref='div_block']");
            var cbg_defects = template.find('[template-ref="cbg_defects"]');
            var cbg_defect_items = template.find("[template-ref='cbg_defect_items']");
            var cbg_defect_template = template.find("[template-ref='cbg_defects'] > [template-row]");

            cbg_defect_items.text('');

            for (var i = 0; i < defects.length; i++) {
                var item = defects[i];
                var new_defect_item = cbg_defect_template.clone();
                
                new_defect_item.attr("id", "cbo_defect_" + item.id);
                new_defect_item.css("display", "");

                new_defect_item.find("[template-field='id']").attr('template-ref', item.id);
                new_defect_item.find("[template-field='id']").attr('value', item.id);
                new_defect_item.find("[template-field='description']").text(item.name + ' - ' + item.description);

                if (selected_values) {
                    for (var j = 0; j < selected_values.length; j++) {
                        if (selected_values[j].defect_id == item.id) {
                            new_defect_item.find("[template-field='id']").prop("checked", true);
                        }
                    }
                }

                cbg_defect_items.append(new_defect_item);
            };
        }
    }).fail(ajaxError);
}

function load_qualities(selected_value) {
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quality/list/json/", function(response) {
        if (response_validation(response)) {
            qualities = [];

            for (var i = 0; i < response.length; i++) {
                qualities.push(response[i]);
            };

            var template = $("[template-ref='div_block']");
            var cbo_quality = template.find('[template-ref="cbo_quality"]');
            cbo_quality.find("option").remove();
        
            $.each(response, function(i, item) {
                add_option(cbo_quality, item.id, item.name);
            });
            
            if (selected_value)
                cbo_quality.val(selected_value).trigger("change");
        }
    }).fail(ajaxError);
}

function show_dialog(tipo, id)
{
    var btn_save = $('#btn_save');
    limpa_formulario(tipo);

    switch(tipo)
    {
        case FORMULARIO.NOVO: // inclusão de blocos somente com ordem de produção
            btn_save.hide();
            permite_alterar(false);
            break;
        case FORMULARIO.EDITAR:
            carrega_formulario(id, tipo);
            btn_save.text('Save');
            btn_save.addClass('btn btn-primary');
            permite_alterar(true);
            break;
        case FORMULARIO.VISUALIZAR:
            carrega_formulario(id, tipo);
            btn_save.hide();
            btn_save.css('');
            permite_alterar(false);
            break;
        case FORMULARIO.EXCLUIR:
            carrega_formulario(id, tipo);
            btn_save.text('Delete');
            btn_save.addClass('btn btn-danger');
            btn_save.css('');
            permite_alterar(false);
            break;
    }

    showModal('modal_detalhe');
}

function permite_alterar(valor)
{
    $("#item_template").find("[template-ref][type='text']").prop("readonly", !valor);
    $("#item_template").find("[template-ref]textarea").prop("readonly", !valor);
    $("#item_template").find("[template-ref]select").prop("disabled", !valor);
    $("#item_template").find("[template-ref] [type='checkbox']").prop("disabled", !valor);

    var template = $("[template-ref='div_block']");
    //var edt_block = template.find('[template-ref="edt_block"]');
    var edt_tot_vol = template.find('[template-ref="edt_tot_vol"]');
    var edt_net_vol = template.find('[template-ref="edt_net_vol"]');
    //edt_block.prop("disabled", true);
    edt_tot_vol.prop("disabled", true);
    edt_net_vol.prop("disabled", true);

    // photo
    if (valor) {
        $("[template-button='send_photo']").show();
        $("#btn_photo_delete").show();
    }
    else {
        $("[template-button='send_photo']").hide();
        $("#btn_photo_delete").hide();
    }
}

function limpa_formulario(tipo)
{
    var template = $("[template-ref='div_block']");
    var btn_remove = template.find("[template-button='remove']");

    var alerta_form = $('#alerta_form');
    var btn_save = $('#btn_save');
    var post_tipo = $('#post_tipo');
    var rec_id = $('#rec_id');
    var rec_defects_json = $('#rec_defects_json');

    var edt_block = template.find('[template-ref="edt_block"]');
    var edt_tot_c = template.find('[template-ref="edt_tot_c"]');
    var edt_tot_a = template.find('[template-ref="edt_tot_a"]');
    var edt_tot_l = template.find('[template-ref="edt_tot_l"]');
    var edt_tot_vol = template.find('[template-ref="edt_tot_vol"]');
    var edt_tot_weight = template.find('[template-ref="edt_tot_weight"]');
    var edt_net_c = template.find('[template-ref="edt_net_c"]');
    var edt_net_a = template.find('[template-ref="edt_net_a"]');
    var edt_net_l = template.find('[template-ref="edt_net_l"]');
    var edt_net_vol = template.find('[template-ref="edt_net_vol"]');
    var edt_observations = template.find('[template-ref="edt_observations"]');
    var cbo_quality = template.find('[template-ref="cbo_quality"]');
    var cbg_defects = template.find('[template-ref="cbg_defects"]');

    template.css('display', '');
    btn_remove.hide();

    alerta_form.hide();
    btn_save.removeClass();
    btn_save.show();
    post_tipo.val(tipo);
    rec_id.val('');
    rec_defects_json.val('');

    edt_block.val('');
    edt_tot_c.val('');
    edt_tot_a.val('');
    edt_tot_l.val('');
    edt_tot_vol.val('');
    edt_tot_weight.val('');
    edt_net_c.val('');
    edt_net_a.val('');
    edt_net_l.val('');
    edt_net_vol.val('');
    edt_observations.val('');
    cbo_quality.val('').trigger('change');

    set_focus(edt_block);

    permite_alterar(true);
}

function carrega_formulario(id, tipo)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>block/detail/json/" + id, function(response) {
        if (response_validation(response)) {
            var rec_id = $('#rec_id');
            var rec_defects_json = $('#rec_defects_json');
            var product_weight_vol = $('#product_weight_vol');
            var template = $("[template-ref='div_block']");
            var edt_block = template.find('[template-ref="edt_block"]');
            var edt_tot_c = template.find('[template-ref="edt_tot_c"]');
            var edt_tot_a = template.find('[template-ref="edt_tot_a"]')
            var edt_tot_l = template.find('[template-ref="edt_tot_l"]');
            var edt_tot_vol = template.find('[template-ref="edt_tot_vol"]');
            var edt_tot_weight = template.find('[template-ref="edt_tot_weight"]');
            var edt_net_c = template.find('[template-ref="edt_net_c"]')
            var edt_net_a = template.find('[template-ref="edt_net_a"]');
            var edt_net_l = template.find('[template-ref="edt_net_l"]');
            var edt_net_vol = template.find('[template-ref="edt_net_vol"]');
            var edt_observations = template.find('[template-ref="edt_observations"]');
            var cbo_quality = template.find('[template-ref="cbo_quality"]');
            var cbg_defects = template.find('[template-ref="cbg_defects"]');

            if (response.hasOwnProperty('id'))
            {
                rec_id.val(response.id);
                rec_defects_json.val(response.defects_json);

                product_weight_vol.val(response.product.weight_vol);

                $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});
                $('.input_number3').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:3});
                
                edt_block.val(response.block_number);
                edt_tot_c.maskMoney('mask', parseFloat(response.tot_c));
                edt_tot_a.maskMoney('mask', parseFloat(response.tot_a));
                edt_tot_l.maskMoney('mask', parseFloat(response.tot_l));
                edt_tot_vol.maskMoney('mask', parseFloat(response.tot_vol));
                edt_tot_weight.maskMoney('mask', parseFloat(response.tot_weight));
                edt_net_c.maskMoney('mask', parseFloat(response.net_c));
                edt_net_a.maskMoney('mask', parseFloat(response.net_a));
                edt_net_l.maskMoney('mask', parseFloat(response.net_l));
                edt_net_vol.maskMoney('mask', parseFloat(response.net_vol));
                edt_observations.val(response.obs);
                
                cbo_quality.val();
                
                load_qualities(response.quality_id);
                load_defects(response.quarry_id, response.defects);

                $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});
                $('.input_number3').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:3});
                
                // marker defects
                
                var canvas_id = String((new Date()).getTime()) + String(Math.round(Math.random() * 10000));
                var img_defect_marker = $("[template='defect_marker']");
                img_defect_marker.attr("id", canvas_id);
                img_defect_marker.css('cursor', 'pointer');
                img_defect_marker.unbind('click');
                img_defect_marker.click(function(){
                    abre_defects_marker(response.block_number, rec_defects_json, img_defect_marker, (tipo != FORMULARIO.EDITAR));
                });

                // adiciono os defeitos no svg do bloco (thumb)
                //img_defect_marker.find('[editor-type="defect"]').remove();
                var defect_marker = new fabric.StaticCanvas(canvas_id);
                if ((response.defects_json) && (response.defects_json != '')) {
                    var defects_thumb = JSON.parse(response.defects_json);
                    resize(defects_thumb);
                    defect_marker.loadFromJSON(JSON.stringify(defects_thumb)).renderAll();
                }

                // fotos
                var div_photos = $("[template='photos']");
                var div_photo_template = $("[template='photo']");

                div_photos.text('');
                if (response.photos) {
                    $.each(response.photos, function(i, photo) {
                        var new_photo = div_photo_template.clone();
                        
                        new_photo.attr("template", "");
                        new_photo.css("display", "");
                        new_photo.find("img").css('cursor', 'pointer');
                        new_photo.find("img").attr('src', photo.small_url);
                        new_photo.find("img").click(function() {
                            abre_photo_view(response.block_number, photo, new_photo);
                        });

                        div_photos.append(new_photo);
                    });        
                }

                // btn send photo
                var btn_send_photo = $("[template-button='send_photo']");
                btn_send_photo.click(function() {
                    abre_photo_upload(response.id, response.block_number, response.production_order_item_id, div_photos, div_photo_template);
                });

            }
        }
    }).fail(ajaxError);
}

function valida_formulario()
{
    var alerta_form = $('#alerta_form');

    var edt_name = $('#edt_name');

    var valido = true;
    var msgs = new Array();

    /*
    if (edt_name.val().length == 0)
    {
        valido = false;
        msgs.push('Enter the name');
    }
    */

    if (!valido)
    {
        alerta_form.html(msgs.join('<br>'));
        alerta_form.show();
    }

    return valido;
}

function envia_detalhes()
{
    var post_tipo = $('#post_tipo');

    if (post_tipo.val() == FORMULARIO.EXCLUIR || valida_formulario())
    {
        var modal_detalhe = $('#modal_detalhe');
        var btn_save = $('#btn_save');
        var rec_id = $('#rec_id');
        var rec_defects_json = $('#rec_defects_json');
        var template = $("[template-ref='div_block']");
        var edt_block = template.find('[template-ref="edt_block"]');
        var edt_tot_c = template.find('[template-ref="edt_tot_c"]');
        var edt_tot_a = template.find('[template-ref="edt_tot_a"]');
        var edt_tot_l = template.find('[template-ref="edt_tot_l"]');
        var edt_tot_vol = template.find('[template-ref="edt_tot_vol"]');
        var edt_tot_weight = template.find('[template-ref="edt_tot_weight"]');
        var edt_net_c = template.find('[template-ref="edt_net_c"]');
        var edt_net_a = template.find('[template-ref="edt_net_a"]');
        var edt_net_l = template.find('[template-ref="edt_net_l"]');
        var edt_net_vol = template.find('[template-ref="edt_net_vol"]');
        var edt_observations = template.find('[template-ref="edt_observations"]');
        var cbo_quality = template.find('[template-ref="cbo_quality"]');
        var cbg_defects = template.find('[template-ref="cbg_defects"]');
        var cbg_defect_items = template.find("[template-ref='cbg_defect_items'] [type='checkbox']");

        var item_defects = [];

        $(cbg_defect_items).each(function() {
            if ($(this).prop("checked")) {
                item_defects.push($(this).val());
            }
        });

        var block = {
            id: rec_id.val(),
            block_number: edt_block.val(),
            tot_c: edt_tot_c.maskMoney('unmasked')[0],
            tot_a: edt_tot_a.maskMoney('unmasked')[0],
            tot_l: edt_tot_l.maskMoney('unmasked')[0],
            tot_vol: edt_tot_vol.maskMoney('unmasked')[0],
            tot_weight: edt_tot_weight.maskMoney('unmasked')[0],
            net_c: edt_net_c.maskMoney('unmasked')[0],
            net_a: edt_net_a.maskMoney('unmasked')[0],
            net_l: edt_net_l.maskMoney('unmasked')[0],
            net_vol: edt_net_vol.maskMoney('unmasked')[0],
            obs: edt_observations.val(),
            quality_id: cbo_quality.val(),
            defects: item_defects,
            defects_json: rec_defects_json.val()
        };

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>block/" + (post_tipo.val() == FORMULARIO.EXCLUIR ? "delete" : "save") + "/",
            data: block,
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe');
                    listar();

                    var tipo = parseInt(post_tipo.val(), 10);
                    switch (tipo)
                    {
                        case FORMULARIO.EDITAR:
                            alert_saved(edt_block.val() + ' saved successfully');
                            break;
                        case FORMULARIO.EXCLUIR:
                            alert_saved(edt_block.val() + ' deleted successfully');
                            break;
                    }
                }
            }
        });
    }
}

function calc_vol(val_c, val_a, val_l, edt_vol, edt_tot_weight) {
    if (isNaN(val_c)
        || isNaN(val_a)
        || isNaN(val_l))
    {
        edt_vol.val('0.000');
        return;
    }

    if ((parseFloat(val_c) == 0)
        || (parseFloat(val_a) == 0)
        || (parseFloat(val_l) == 0))
    {
        edt_vol.val('0.000');
        return;
    }

    var result = val_c * val_a * val_l;
    edt_vol.val(arredondar3(result));

    var product_weight_vol = $('#product_weight_vol');
    if ((edt_tot_weight) && (product_weight_vol)) {
        var weight = result * parseFloat(product_weight_vol.val());
        edt_tot_weight.val(weight.toFixed(3));
    }
}

// on load window
funcs_on_load.push(function() {
    var template = $("[template-ref='div_block']");
    var edt_tot_c = template.find('[template-ref="edt_tot_c"]');
    var edt_tot_a = template.find('[template-ref="edt_tot_a"]');
    var edt_tot_l = template.find('[template-ref="edt_tot_l"]');
    var edt_tot_vol = template.find('[template-ref="edt_tot_vol"]');
    var edt_tot_weight = template.find('[template-ref="edt_tot_weight"]');
    var edt_net_c = template.find('[template-ref="edt_net_c"]');
    var edt_net_a = template.find('[template-ref="edt_net_a"]');
    var edt_net_l = template.find('[template-ref="edt_net_l"]');
    var edt_net_vol = template.find('[template-ref="edt_net_vol"]');

    // calc val bruto m3
    edt_tot_c.unbind('change');
    edt_tot_c.change(function() {
        //$(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_vol(
            edt_tot_c.val(),
            edt_tot_a.val(),
            edt_tot_l.val(),
            edt_tot_vol,
            edt_tot_weight
        );
    });

    edt_tot_a.unbind('change');
    edt_tot_a.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_vol(
            edt_tot_c.val(),
            edt_tot_a.val(),
            edt_tot_l.val(),
            edt_tot_vol,
            edt_tot_weight
        );
    });

    edt_tot_l.unbind('change');
    edt_tot_l.change(function() {
        //$(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_vol(
            edt_tot_c.val(),
            edt_tot_a.val(),
            edt_tot_l.val(),
            edt_tot_vol,
            edt_tot_weight
        );
    });

    // calc val liq m3
    edt_net_c.unbind('change');
    edt_net_c.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_vol(
            edt_net_c.val(),
            edt_net_a.val(),
            edt_net_l.val(),
            edt_net_vol
        );
    });

    edt_net_a.unbind('change');
    edt_net_a.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_vol(
            edt_net_c.val(),
            edt_net_a.val(),
            edt_net_l.val(),
            edt_net_vol
        );
    });

    edt_net_l.unbind('change');
    edt_net_l.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
        calc_vol(
            edt_net_c.val(),
            edt_net_a.val(),
            edt_net_l.val(),
            edt_net_vol
        );
    });

});