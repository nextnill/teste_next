var lbl_reinspection_block_number;
var rec_reinspection_id;
var edt_reinspection_old_tot_c;
var edt_reinspection_old_tot_a;
var edt_reinspection_old_tot_l;
var edt_reinspection_new_tot_c;
var edt_reinspection_new_tot_a;
var edt_reinspection_new_tot_l;
var edt_reinspection_old_net_c;
var edt_reinspection_old_net_a;
var edt_reinspection_old_net_l;
var edt_reinspection_new_net_c;
var edt_reinspection_new_net_a;
var edt_reinspection_new_net_l;
var edt_reinspection_new_net_vol;
var edt_reinspection_old_sale_net_c;
var edt_reinspection_old_sale_net_a;
var edt_reinspection_old_sale_net_l;
var edt_reinspection_new_sale_net_c;
var edt_reinspection_new_sale_net_a;
var edt_reinspection_new_sale_net_l;
var edt_reinspection_new_sale_net_vol;
var edt_reinspection_vol;
var edt_reinspection_weight;
var product_weight_vol;

var btn_reinspection_block_confirm;

function show_dialog_reinspection_block(block_id)
{
    showModal('modal_detalhe_reinspection_block');

    lbl_reinspection_block_number = $('#lbl_reinspection_block_number');

    rec_reinspection_id = $('#rec_reinspection_id');
    product_weight_vol = $('#product_weight_vol');

    edt_reinspection_old_tot_c = $('#edt_reinspection_old_tot_c');
	edt_reinspection_old_tot_a = $('#edt_reinspection_old_tot_a');
	edt_reinspection_old_tot_l = $('#edt_reinspection_old_tot_l');
	edt_reinspection_new_tot_c = $('#edt_reinspection_new_tot_c');
	edt_reinspection_new_tot_a = $('#edt_reinspection_new_tot_a');
	edt_reinspection_new_tot_l = $('#edt_reinspection_new_tot_l');
	edt_reinspection_old_net_c = $('#edt_reinspection_old_net_c');
	edt_reinspection_old_net_a = $('#edt_reinspection_old_net_a');
	edt_reinspection_old_net_l = $('#edt_reinspection_old_net_l');
	edt_reinspection_new_net_c = $('#edt_reinspection_new_net_c');
	edt_reinspection_new_net_a = $('#edt_reinspection_new_net_a');
	edt_reinspection_new_net_l = $('#edt_reinspection_new_net_l');
	edt_reinspection_new_net_vol = $('#edt_reinspection_new_net_vol');
	edt_reinspection_old_sale_net_c = $('#edt_reinspection_old_sale_net_c');
	edt_reinspection_old_sale_net_a = $('#edt_reinspection_old_sale_net_a');
	edt_reinspection_old_sale_net_l = $('#edt_reinspection_old_sale_net_l');
	edt_reinspection_new_sale_net_c = $('#edt_reinspection_new_sale_net_c');
	edt_reinspection_new_sale_net_a = $('#edt_reinspection_new_sale_net_a');
	edt_reinspection_new_sale_net_l = $('#edt_reinspection_new_sale_net_l');
	edt_reinspection_new_sale_net_vol = $('#edt_reinspection_new_sale_net_vol');
	
	edt_reinspection_vol = $('#edt_reinspection_vol');
	edt_reinspection_weight = $('#edt_reinspection_weight');

	btn_reinspection_block_confirm = $('#btn_reinspection_block_confirm');

	rec_reinspection_id.val(block_id);

	preencher();
}

function preencher() {
	$.getJSON("<?= APP_URI ?>block/detail/json/" + rec_reinspection_id.val() + '/',function(response) {
        if (response_validation(response)) {
        	lbl_reinspection_block_number.text(response.block_number);
        	
        	product_weight_vol.val(response.product.weight_vol);

			edt_reinspection_old_tot_c.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.tot_c));
			edt_reinspection_old_tot_a.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.tot_a));
			edt_reinspection_old_tot_l.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.tot_l));
			edt_reinspection_new_tot_c.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.tot_c));
			edt_reinspection_new_tot_a.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.tot_a));
			edt_reinspection_new_tot_l.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.tot_l));

			edt_reinspection_old_net_c.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.net_c));
			edt_reinspection_old_net_a.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.net_a));
			edt_reinspection_old_net_l.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.net_l));
			edt_reinspection_new_net_c.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.net_c));
			edt_reinspection_new_net_a.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.net_a));
			edt_reinspection_new_net_l.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.net_l));

			edt_reinspection_old_sale_net_c.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.sale_net_c));
			edt_reinspection_old_sale_net_a.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.sale_net_a));
			edt_reinspection_old_sale_net_l.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.sale_net_l));
			edt_reinspection_new_sale_net_c.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.sale_net_c));
			edt_reinspection_new_sale_net_a.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.sale_net_a));
			edt_reinspection_new_sale_net_l.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2}).maskMoney('mask', parseFloat(response.sale_net_l));

			fnc_change_tot = function() {
				calc_vol(
		            edt_reinspection_new_tot_c.maskMoney('unmasked')[0],
		            edt_reinspection_new_tot_a.maskMoney('unmasked')[0],
		            edt_reinspection_new_tot_l.maskMoney('unmasked')[0],
		            edt_reinspection_vol,
		            edt_reinspection_weight
		        );
			}
			edt_reinspection_new_tot_c.unbind('change');
		    edt_reinspection_new_tot_c.change(fnc_change_tot);

		    edt_reinspection_new_tot_a.unbind('change');
		    edt_reinspection_new_tot_a.change(fnc_change_tot);

		    edt_reinspection_new_tot_l.unbind('change');
		    edt_reinspection_new_tot_l.change(fnc_change_tot);
			
			fnc_change_net = function() {
				calc_vol(
		            edt_reinspection_new_net_c.maskMoney('unmasked')[0],
		            edt_reinspection_new_net_a.maskMoney('unmasked')[0],
		            edt_reinspection_new_net_l.maskMoney('unmasked')[0],
		            edt_reinspection_new_net_vol
		        );
			}
			edt_reinspection_new_net_c.unbind('change');
		    edt_reinspection_new_net_c.change(fnc_change_net);

		    edt_reinspection_new_net_a.unbind('change');
		    edt_reinspection_new_net_a.change(fnc_change_net);

		    edt_reinspection_new_net_l.unbind('change');
		    edt_reinspection_new_net_l.change(fnc_change_net);

			fnc_change_sale_net = function() {
				calc_vol(
		            edt_reinspection_new_sale_net_c.maskMoney('unmasked')[0],
		            edt_reinspection_new_sale_net_a.maskMoney('unmasked')[0],
		            edt_reinspection_new_sale_net_l.maskMoney('unmasked')[0],
		            edt_reinspection_new_sale_net_vol
		        );
			}
			edt_reinspection_new_sale_net_c.unbind('change');
		    edt_reinspection_new_sale_net_c.change(fnc_change_sale_net);

		    edt_reinspection_new_sale_net_a.unbind('change');
		    edt_reinspection_new_sale_net_a.change(fnc_change_sale_net);

		    edt_reinspection_new_sale_net_l.unbind('change');
		    edt_reinspection_new_sale_net_l.change(fnc_change_sale_net);

			fnc_change_tot();
			fnc_change_net();
			fnc_change_sale_net();

			btn_reinspection_block_confirm.unbind('click');
			btn_reinspection_block_confirm.click(function() {
				WS.post(
		            "reinspection/blocks/save/",
		            {
		                id: rec_reinspection_id.val(),
		                tot_c: edt_reinspection_new_tot_c.maskMoney('unmasked')[0],
		                tot_a: edt_reinspection_new_tot_a.maskMoney('unmasked')[0],
		                tot_l: edt_reinspection_new_tot_l.maskMoney('unmasked')[0],
		                tot_vol: edt_reinspection_vol.val(),
		                tot_weight: edt_reinspection_weight.val(),
		                net_c: edt_reinspection_new_net_c.maskMoney('unmasked')[0],
		                net_a: edt_reinspection_new_net_a.maskMoney('unmasked')[0],
		                net_l: edt_reinspection_new_net_l.maskMoney('unmasked')[0],
		                net_vol: edt_reinspection_new_net_vol.val(),
		                sale_net_c: edt_reinspection_new_sale_net_c.maskMoney('unmasked')[0],
		                sale_net_a: edt_reinspection_new_sale_net_a.maskMoney('unmasked')[0],
		                sale_net_l: edt_reinspection_new_sale_net_l.maskMoney('unmasked')[0],
		                sale_net_vol: edt_reinspection_new_sale_net_vol.val()
		            },
		            function (response) {
		                closeModal('modal_detalhe_reinspection_block');
		                
		                if (typeof btn_block_number_search != 'undefined') {
		                	btn_block_number_search.trigger('click');
		                }
		            }
		        );
			});
	    }
    }).fail(ajaxError);
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
    if ((edt_reinspection_weight) && (product_weight_vol)) {
        var weight = result * parseFloat(product_weight_vol.val());
        edt_reinspection_weight.val(weight.toFixed(3));
    }
}