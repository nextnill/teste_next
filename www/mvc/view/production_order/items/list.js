
var ProductOrderItems = function(id)
{
    var block_id = null;
    // static attributes
    ProductOrderItems.production_order_id = id;

    ProductOrderItems.quarry_id = null;
    ProductOrderItems.block_type = null;
    ProductOrderItems.status = null;

    ProductOrderItems.product_weight_vol = null;

    ProductOrderItems.defects = [];
    ProductOrderItems.qualities = [];
    
    ProductOrderItems.blocks = [];
    ProductOrderItems.blocks_to_save = [];

    ProductOrderItems.po_detail = $("#po_detail");
    ProductOrderItems.po_detail_title = $("#po_detail_title");
    ProductOrderItems.po_detail_quarry_name = $("#po_detail_quarry_name");
    ProductOrderItems.po_detail_production_date = $("#po_detail_production_date");
    ProductOrderItems.po_detail_product_name = $("#po_detail_product_name");
    ProductOrderItems.po_detail_block_type = $("#po_detail_block_type");

    ProductOrderItems.po_items = $("#po_items");
    ProductOrderItems.poi_menu_heading = $("#poi_menu_heading");
    ProductOrderItems.poi_menu_footer = $("#poi_menu_footer");
    ProductOrderItems.btn_po_edit = $("#btn_po_edit");
    ProductOrderItems.btn_po_confirm = $("#btn_po_confirm");
    ProductOrderItems.btn_po_add = $("#btn_po_add");
    ProductOrderItems.btn_po_refresh = $("#btn_po_refresh");
    ProductOrderItems.btn_po_save = $("#btn_po_save");


    ProductOrderItems.load_defects = function(callback_function) {
        // pesquisa a listagem em json
        $.getJSON("<?= APP_URI ?>defect/list/json/" + ProductOrderItems.quarry_id, function(response) {
            if (response_validation(response)) {
                ProductOrderItems.defects = [];

                for (var i = 0; i < response.length; i++) {
                    ProductOrderItems.defects.push(response[i]);
                };

                ProductOrderItems.load_qualities();

                if (callback_function && typeof(callback_function) == "function") { callback_function(); }
            }            
        }).fail(ajaxError);
    }

    ProductOrderItems.load_qualities = function(callback_function) {
        // pesquisa a listagem em json
        $.getJSON("<?= APP_URI ?>quality/list/json/", function(response) {
            if (response_validation(response)) {
                ProductOrderItems.qualities = [];

                for (var i = 0; i < response.length; i++) {
                    ProductOrderItems.qualities.push(response[i]);
                };

                ProductOrderItems.load_blocks();

                if (callback_function && typeof(callback_function) == "function") { callback_function(); }
            }            
        }).fail(ajaxError);
    }

    ProductOrderItems.load_blocks = function(show_log) {
        // pesquisa a listagem em json
        $.getJSON("<?= APP_URI ?>po/items/blocks/json/" + ProductOrderItems.production_order_id, function(response) {
            if (response_validation(response)) {
                ProductOrderItems.blocks = [];

                for (var i = 0; i < response.length; i++) {
                    ProductOrderItems.blocks.push(response[i]);
                };

                ProductOrderItems.render_blocks();

                if (show_log) {
                    var dt = new Date();
                    $('.log_refresh').hide().html('Refreshed at ' + dt.timeNow()).fadeIn('slow', function() {
                        $(this).fadeOut('slow');
                    });
                }
            }
        }).fail(ajaxError);
    }

    ProductOrderItems.render_blocks = function() {
        
        $('#po_items').empty();

        for (var i = 0; i < ProductOrderItems.blocks.length; i++) {
            var item = ProductOrderItems.blocks[i];
            
            //function(id, block_number, tot_c, tot_a, tot_l, tot_vol, net_c, net_a, net_l, net_vol, quality_id, obs, defects)

            poi.clone_item_template(
                item.id,
                item.block_number,
                item.tot_c,
                item.tot_a,
                item.tot_l,
                item.tot_vol,
                item.tot_weight,
                item.net_c,
                item.net_a,
                item.net_l,
                item.net_vol,
                item.quality_id,
                item.obs,
                item.defects_json,
                item.defects,
                item.photos,
                item.block_id
            );
        };

        
        if (ProductOrderItems.status == PRODUCTION_STATUS.CONFIRMED) {
         
            $("[template-button='remove']").hide();
            $(".btn_po_add,.btn_po_confirm, .btn_po_edit").hide();
           // $(".btn_po_add").attr('disabled', true);
        }
        
    }

    ProductOrderItems.calc_vol = function(val_c, val_a, val_l, edt_vol, edt_tot_weight)
    {
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

        if ((edt_tot_weight) && (ProductOrderItems.product_weight_vol)) {
            var weight = result * ProductOrderItems.product_weight_vol;
            edt_tot_weight.val(weight.toFixed(3));
        }
    }

    // functions
    this.populate_po_detail = function(just_header)
    {
        $.getJSON("<?= APP_URI ?>po/items/header/json/" + this.po_id, function(response) {
            if (response_validation(response)) {
                if (response.hasOwnProperty('id')) {
                    ProductOrderItems.quarry_id = response.quarry_id;
                    ProductOrderItems.block_type = response.block_type;
                    ProductOrderItems.status = response.status;
                    ProductOrderItems.product_weight_vol = response.product_weight_vol;
                    ProductOrderItems.po_detail_title.text(response.id);
                    ProductOrderItems.po_detail_quarry_name.text(response.quarry_name);
                    ProductOrderItems.po_detail_production_date.text(response.date_production.format_date());
                    ProductOrderItems.po_detail_product_name.text(response.product_name);
                    ProductOrderItems.po_detail_block_type.text(str_block_type(response.block_type));

                    if (!just_header) {
                        ProductOrderItems.load_defects();
                    }

                    $(".btn_po_confirm").attr('disabled', false);
                    $(".btn_po_save").attr('disabled', false);
                }
            }
        }).fail(ajaxError);
    };

    this.set_value = function(obj, field, value)
    {
        obj.find("[template-field='"+field+"']").text(value);
    }

    this.get_ref = function(obj, ref)
    {
        return obj.find("[template-ref='"+ref+"']");
    }

    this.add_item = function(scroll_bottom) {
        var url_block_type = (ProductOrderItems.block_type == BLOCK_TYPE.FINAL ? 'final' : 'interim');
        var main = this;

        $(".btn_po_add").attr('disabled', true);

        $.getJSON("<?= APP_URI ?>quarry/nextval/" + url_block_type + "/" + ProductOrderItems.quarry_id, function(response) {
            if (response_validation(response)) {
                main.clone_item_template(0, response[0].block_number);
                if (scroll_bottom) {
                    //window.scrollTo(0,document.body.scrollHeight);
                }
                $(".btn_po_add").attr('disabled', false);
                poi.save();
            }
        }).fail(ajaxError);
    }

    ProductOrderItems.valida_tot_net = function(edt_tot, edt_net) {

        if (!isNaN(edt_tot.val()) && !isNaN(edt_net.val())) {

            if (parseFloat(edt_tot.val()) < parseFloat(edt_net.val())) {
                edt_net.tooltip({title: 'Net Meas can not be greater than Tot Meas', placement: 'bottom', trigger: 'manual'});
                edt_net.tooltip('show');
            }
            else {
                edt_net.tooltip('destroy');
            }
        }
    }

    // id = 0 = novo
    this.clone_item_template = function(id, block_number, tot_c, tot_a, tot_l, tot_vol, tot_weight, net_c, net_a, net_l, net_vol, quality_id, obs, defects_json, defects, photos, block_id)
    {
        var new_item = $("#item_template").clone();
        var new_item_id = "block_item_" + block_number;
        new_item.attr("id", new_item_id);
        new_item.css("display", "");
        new_item.css("padding-top", "20px");
        new_item.css("padding-bottom", "5px");

        var rec_id = this.get_ref(new_item, 'rec_id');
        var rec_block_id = this.get_ref(new_item, 'rec_block_id');
        var rec_removed = this.get_ref(new_item, 'rec_removed');
        var rec_block_number_exists = this.get_ref(new_item, 'rec_block_number_exists');

        var edt_block = this.get_ref(new_item, 'edt_block');

        var edt_tot_c = this.get_ref(new_item, 'edt_tot_c');
        var edt_tot_a = this.get_ref(new_item, 'edt_tot_a');
        var edt_tot_l = this.get_ref(new_item, 'edt_tot_l');
        var edt_tot_vol = this.get_ref(new_item, 'edt_tot_vol');
        var edt_tot_weight = this.get_ref(new_item, 'edt_tot_weight');
        var edt_net_c = this.get_ref(new_item, 'edt_net_c');
        var edt_net_a = this.get_ref(new_item, 'edt_net_a');
        var edt_net_l = this.get_ref(new_item, 'edt_net_l');
        var edt_net_vol = this.get_ref(new_item, 'edt_net_vol');

        // valida se o blocknumber existe
        edt_block.unbind('change');
        edt_block.change(function() {
            WS.get(
                "block/exists/json/block_number/" + edt_block.val() + "/", {},
                function (response) {
                    var exists = response.exists || false;

                    rec_block_number_exists.val(exists);

                    if (exists) {
                        edt_block.tooltip({title: 'This block number is currently in use', placement: 'top', trigger: 'manual'});
                        edt_block.tooltip('show');
                    }
                    else {
                        edt_block.tooltip('destroy');
                    }

                }
            );
        });

        // calc val bruto m3
        edt_tot_c.unbind('change');
        edt_tot_c.change(function() {
           // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
            ProductOrderItems.valida_tot_net(edt_tot_c, edt_net_c);
            ProductOrderItems.calc_vol(
                edt_tot_c.val(),
                edt_tot_a.val(),
                edt_tot_l.val(),
                edt_tot_vol,
                edt_tot_weight
            );
            // habilito validação de saida da página
            valid_onunload(true);
        });

        edt_tot_a.unbind('change');
        edt_tot_a.change(function() {
            //$(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
            ProductOrderItems.valida_tot_net(edt_tot_a, edt_net_a);
            ProductOrderItems.calc_vol(
                edt_tot_c.val(),
                edt_tot_a.val(),
                edt_tot_l.val(),
                edt_tot_vol,
                edt_tot_weight
            );
            // habilito validação de saida da página
            valid_onunload(true);
        });

        edt_tot_l.unbind('change');
        edt_tot_l.change(function() {
           // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
            ProductOrderItems.valida_tot_net(edt_tot_l, edt_net_l);
            ProductOrderItems.calc_vol(
                edt_tot_c.val(),
                edt_tot_a.val(),
                edt_tot_l.val(),
                edt_tot_vol,
                edt_tot_weight
            );
            // habilito validação de saida da página
            valid_onunload(true);
        });

        // calc val liq m3
        edt_net_c.unbind('change');
        edt_net_c.change(function() {
           // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
            ProductOrderItems.valida_tot_net(edt_tot_c, edt_net_c);
            ProductOrderItems.calc_vol(
                edt_net_c.val(),
                edt_net_a.val(),
                edt_net_l.val(),
                edt_net_vol
            );
            // habilito validação de saida da página
            valid_onunload(true);
        });

        edt_net_a.unbind('change');
        edt_net_a.change(function() {
          //  $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
            ProductOrderItems.valida_tot_net(edt_tot_a, edt_net_a);
            ProductOrderItems.calc_vol(
                edt_net_c.val(),
                edt_net_a.val(),
                edt_net_l.val(),
                edt_net_vol
            );
            // habilito validação de saida da página
            valid_onunload(true);
        });

        edt_net_l.unbind('change');
        edt_net_l.change(function() {
           // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
            ProductOrderItems.valida_tot_net(edt_tot_l, edt_net_l);
            ProductOrderItems.calc_vol(
                edt_net_c.val(),
                edt_net_a.val(),
                edt_net_l.val(),
                edt_net_vol
            );
            // habilito validação de saida da página
            valid_onunload(true);
        });

        var edt_observations = this.get_ref(new_item, 'edt_observations');
        edt_observations.val(obs);
        edt_observations.unbind('change');
        edt_observations.change(function() {
            // habilito validação de saida da página
            valid_onunload(true);
        });

        // qualities
        var cbo_quality = this.get_ref(new_item, 'cbo_quality');
        add_option(cbo_quality, '', '');
        
        for (var i = 0; i < ProductOrderItems.qualities.length; i++) {
            var item = ProductOrderItems.qualities[i];
            add_option(cbo_quality, item.id, item.name);
        };

        cbo_quality.unbind('change');
        cbo_quality.change(function() {
            // habilito validação de saida da página
            valid_onunload(true);
        });

        // defects
        var cbg_defects = this.get_ref(new_item, 'cbg_defects');
        
        var cbg_defect_items = new_item.find("[template-ref='cbg_defect_items']");
        var cbg_defect_template = new_item.find("[template-ref='cbg_defects'] > [template-row]");

        cbg_defect_items.text('');

        for (var i = 0; i < ProductOrderItems.defects.length; i++) {
            var defect = ProductOrderItems.defects[i];
            var new_defect_item = cbg_defect_template.clone();
            
            new_defect_item.attr("id", "cbo_defect_" + defect.id);
            new_defect_item.css("display", "");

            new_defect_item.find("[template-field='id']").attr('template-ref', defect.id);
            new_defect_item.find("[template-field='id']").attr('value', defect.id);
            new_defect_item.find("[template-field='description']").text(defect.name + ' - ' + defect.description);

            if (defects) {
                for (var j = 0; j < defects.length; j++) {
                    if (defects[j].defect_id == defect.id) {
                        new_defect_item.find("[template-field='id']").prop("checked", true);
                    }
                }
            }

            new_defect_item.unbind('change');
            new_defect_item.change(function() {
                // habilito validação de saida da página
                valid_onunload(true);
            });

            cbg_defect_items.append(new_defect_item);
        };

        // marker defects
        var rec_defects_json = this.get_ref(new_item, 'rec_defects_json');
        var canvas_id = String((new Date()).getTime()) + String(Math.round(Math.random() * 10000));
        var img_defect_marker = new_item.find("[template='defect_marker']");
        img_defect_marker.attr("id", canvas_id);
        img_defect_marker.css('cursor', 'pointer');
        img_defect_marker.unbind('click');
        img_defect_marker.click(function(){
            abre_defects_marker(block_number, rec_defects_json, img_defect_marker);
            // habilito validação de saida da página
            valid_onunload(true);
        });

        // adiciono os defeitos no svg do bloco (thumb)

        
        // fotos
        var div_photos = new_item.find("[template='photos']");
        var div_photo_template = new_item.find("[template='photo']");

        div_photos.text('');
        if (photos) {
            $.each(photos, function(i, photo) {
                var new_photo = div_photo_template.clone();
                new_photo.attr("template", "");
                new_photo.css("display", "");
                new_photo.find("img").css('cursor', 'pointer');
                new_photo.find("img").attr('src', photo.small_url);
                new_photo.find("img").click(function() {
                    abre_photo_view(block_number, photo, new_photo);
                });

                div_photos.append(new_photo);
            });        
        }

        // btn send photo
        var btn_send_photo = new_item.find("[template-button='send_photo']");
        btn_send_photo.attr('template-ref', id);
        btn_send_photo.click(function() {
            //var id = $(this).attr('template-ref');            
            var id = new_item.find("[template-ref='rec_id']").val();
            var block_id = new_item.find("[template-ref='rec_block_id']").val();
            abre_photo_upload(block_id, block_number, id, div_photos, div_photo_template);
        });

        // btn remover
        var btn_remove = new_item.find("[template-button='remove']");
        btn_remove.attr('template-ref', id);
        btn_remove.click(function() {
            var id = $(this).attr('template-ref');
            
            var remove_block = function() {
                rec_removed.val(true);
                closeModal('alert_modal');
                new_item.fadeOut('slow');
                // habilito validação de saida da página
                valid_onunload(true);
            }

            alert_modal('Remove Block', 'Remove block ' + edt_block.val() + ' ?', 'Yes, remove this block', remove_block, true);
        });        

        if (id) { rec_id.val(id); }
        if (block_id) { rec_block_id.val(block_id); }
        if (block_number) { edt_block.val(block_number); }
        if (tot_c) { edt_tot_c.val(tot_c); }
        if (tot_a) { edt_tot_a.val(tot_a); }
        if (tot_l) { edt_tot_l.val(tot_l); }
        if (tot_vol) { edt_tot_vol.val(tot_vol); }
        if (tot_weight) { edt_tot_weight.val(tot_weight); }
        if (net_c) { edt_net_c.val(net_c); }
        if (net_a) { edt_net_a.val(net_a); }
        if (net_l) { edt_net_l.val(net_l); }
        if (net_vol) { edt_net_vol.val(net_vol); }
        if (quality_id) { cbo_quality.val(quality_id); }
        if (defects_json) { rec_defects_json.val(defects_json); }

        new_item.hide();
        ProductOrderItems.po_items.append(new_item);
        //$('.input_number').unbind('keypress');
        //$('.input_number').keypress(input_number_keypress);
        $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});


        new_item.fadeIn('slow', function() {
            ProductOrderItems.valida_tot_net(edt_tot_c, edt_net_c);
            ProductOrderItems.valida_tot_net(edt_tot_a, edt_net_a);
            ProductOrderItems.valida_tot_net(edt_tot_l, edt_net_l);
        });
        
        //gerar thumb
        var defect_marker = new fabric.StaticCanvas(canvas_id);
        if ((defects_json) && (defects_json != '')) {

            var defects_thumb = JSON.parse(defects_json);
            resize(defects_thumb);
            defect_marker.loadFromJSON(JSON.stringify(defects_thumb)).renderAll();
        }
         
        
        // se for novo
        if (id == 0) {
            set_focus(edt_block);
        }
        
    };

    this.push_blocks_to_save = function() {
        
        ProductOrderItems.blocks_to_save = [];

        // for each para percorrer todos os blocks
        $("[template-ref='div_block']").each(function() {
            var edt_block = $(this).find("[template-ref='edt_block']");
            
            if (edt_block.val().trim() != "") {

                var rec_id = $(this).find("[template-ref='rec_id']");
                var rec_block_id = $(this).find("[template-ref='rec_block_id']");
                var rec_removed = $(this).find("[template-ref='rec_removed']");
                var rec_block_number_exists = $(this).find("[template-ref='rec_block_number_exists']");
                
                var rec_defects_json = $(this).find("[template-ref='rec_defects_json']");

                var edt_tot_c = $(this).find("[template-ref='edt_tot_c']");
                var edt_tot_a = $(this).find("[template-ref='edt_tot_a']");
                var edt_tot_l = $(this).find("[template-ref='edt_tot_l']");
                var edt_tot_vol = $(this).find("[template-ref='edt_tot_vol']");
                var edt_tot_weight = $(this).find("[template-ref='edt_tot_weight']");

                var edt_net_c = $(this).find("[template-ref='edt_net_c']");
                var edt_net_a = $(this).find("[template-ref='edt_net_a']");
                var edt_net_l = $(this).find("[template-ref='edt_net_l']");
                var edt_net_vol = $(this).find("[template-ref='edt_net_vol']");

                var edt_observations = $(this).find("[template-ref='edt_observations']");

                var cbo_quality = $(this).find("[template-ref='cbo_quality']");

                var cbg_defect_items = $(this).find("[template-ref='cbg_defect_items'] [type='checkbox']");
                var defects = [];

                $(cbg_defect_items).each(function() {
                    if ($(this).prop("checked")) {
                        defects.push($(this).val());
                    }
                });

                var block = {
                    id: rec_id.val(),
                    block_id: rec_block_id.val(),
                    removed: rec_removed.val(),
                    block_number: edt_block.val(),
                    block_number_exists: rec_block_number_exists.val(),
                    quality_id: cbo_quality.val(),
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
                    defects_json: rec_defects_json.val(),
                    defects: defects
                };

                ProductOrderItems.blocks_to_save.push(block);
            }
            
        });

    }

    this.valid_blocks_to_confirm = function() {
        var vld = new Validation();

        if (ProductOrderItems.blocks_to_save && ProductOrderItems.blocks_to_save.length > 0) {

            for (var i = 0; i < ProductOrderItems.blocks_to_save.length; i++) {
                var block = ProductOrderItems.blocks_to_save[i];

                if (block.removed == 'false') {

                    if (block.block_number.length == 0) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the block number'));
                    }

                    if (block.block_number_exists == 'true') {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'This block number is currently in use', block.block_number));
                    }

                    if (isNaN(block.tot_c) || (block.tot_c == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Tot Meas C', block.block_number));
                    }

                    if (isNaN(block.tot_a) || (block.tot_a == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Tot Meas A', block.block_number));
                    }

                    if (isNaN(block.tot_l) || (block.tot_l == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Tot Meas L', block.block_number));
                    }

                    if (isNaN(block.tot_vol) || (block.tot_vol == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Tot Meas Vol', block.block_number));
                    }

                    if (isNaN(block.tot_weight) || (block.tot_weight == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Weight', block.block_number));
                    }

                    if (isNaN(block.net_c) || (block.net_c == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Net Meas C', block.block_number));
                    }

                    if (isNaN(block.net_a) || (block.net_a == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Net Meas A', block.block_number));
                    }

                    if (isNaN(block.net_l) || (block.net_l == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Net Meas L', block.block_number));
                    }

                    if (isNaN(block.net_vol) || (block.net_vol == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Enter the Net Meas Vol', block.block_number));
                    }

                    if ((!isNaN(block.tot_c) && !isNaN(block.net_c)) && (block.tot_c < block.net_c)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Net Meas C can not be greater than Tot Meas C', block.block_number));
                    }

                    if ((!isNaN(block.tot_a) && !isNaN(block.net_a)) && (block.tot_a < block.net_a)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Net Meas A can not be greater than Tot Meas A', block.block_number));
                    }

                    if ((!isNaN(block.tot_l) && !isNaN(block.net_l)) && (block.tot_l < block.net_l)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Net Meas L can not be greater than Tot Meas L', block.block_number));
                    }
                    
                    if (isNaN(block.quality_id) || (block.quality_id == 0)) {
                        vld.add(new ValidationMessage(Validation.CODES.ERR_FIELD, 'Informe a classificação', block.block_number));
                    }

                }

            };

        }
        else {
            vld.add(new ValidationMessage(Validation.CODES.ERR, 'Nenhum bloco encontrado'));
        }

        return vld;
    }

    this.save = function(confirm) {
        
        $(".btn_po_confirm").attr('disabled', true);
        $(".btn_po_save").attr('disabled', true);

        this.push_blocks_to_save();

        var main = this;
        
        //blocks
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>po/items/save/",
            data: { production_order_id: ProductOrderItems.production_order_id, blocks: ProductOrderItems.blocks_to_save, confirm: (confirm === true) },
            dataType: 'json',
            success: function (response) {
                if (response_validation(response)) {
                    for (var i = 0; i < response.length; i++) {
                        // update ids
                        $("[template-ref='div_block']").each(function() {
                            var update_rec_id = $(this).find("[template-ref='rec_id']");
                            var update_rec_block_id = $(this).find("[template-ref='rec_block_id']");
                            var update_edt_block = $(this).find("[template-ref='edt_block']");

                            if (update_edt_block.val().trim() == response[i].block_number.trim()) {
                                update_rec_id.val(response[i].id);
                                update_rec_block_id.val(response[i].block_id);
                            }
                        });
                        var dt = new Date();
                        $('.log_save').hide().html('Saved at ' + dt.timeNow()).fadeIn('slow', function() {
                            $(this).fadeOut(1000);
                        });

                        main.push_blocks_to_save();
                        ProductOrderItems.blocks = ProductOrderItems.blocks_to_save;
                        
                        // desabilito validação de saida da página
                        valid_onunload(false);
                    };
                    poi.populate_po_detail(!((confirm) && (confirm === true)));
                }
            }
        });

    }

    // editar cabeçalho da op
    ProductOrderItems.btn_po_edit.unbind('click');
    ProductOrderItems.btn_po_edit.click(function() {
        show_dialog(FORMULARIO.EDITAR, id);
    });

    // confirmar OP
    ProductOrderItems.btn_po_confirm.unbind('click');
    ProductOrderItems.btn_po_confirm.click(function() {
        $(".btn_po_confirm").attr('disabled', true);

        poi.push_blocks_to_save();
        var vld = poi.valid_blocks_to_confirm();

        if (!vld.is_valid()) {
            alert_modal('Validation', vld);
            $(".btn_po_confirm").attr('disabled', false);
        }
        else {
            poi.save(true);
        }

    });

    // add novo blocl
    ProductOrderItems.btn_po_add.unbind('click');
    ProductOrderItems.btn_po_add.click(function() {
        poi.add_item(true);
    });

    // atualizar blocos
    ProductOrderItems.btn_po_refresh.unbind('click');
    ProductOrderItems.btn_po_refresh.click(function() {
        ProductOrderItems.load_blocks(true);
    });

    // salvar blocos
    ProductOrderItems.btn_po_save.unbind('click');
    ProductOrderItems.btn_po_save.click(function() {
        poi.save();
    });

    // constructor
    this.po_id = id;

    if (!isNaN(this.po_id)) {
        this.populate_po_detail();

        // clona menu
        ProductOrderItems.poi_menu_heading.clone(true).contents().appendTo(ProductOrderItems.poi_menu_footer);
    }
}

// init
var poi = null;

// on load window
funcs_on_load.push(function() {
    poi = new ProductOrderItems(<?= $po_id ?>);
});

function resize(desenho){
    if (typeof desenho.objects != 'undefined') {
        for(var i=0; i < desenho.objects.length; i++){

            desenho.objects[i].left = desenho.objects[i].left/2;
            desenho.objects[i].top = desenho.objects[i].top/2;
            desenho.objects[i].scaleX = desenho.objects[i].scaleX/2;
            desenho.objects[i].scaleY = desenho.objects[i].scaleY/2;
        }
    }
}



