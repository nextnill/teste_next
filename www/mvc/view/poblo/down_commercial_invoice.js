var edt_ci_date = $('#edt_ci_date');
var edt_ci_number = $('#edt_ci_number');
var edt_ci_dv = $('#edt_ci_dv');
var edt_ci_client_notify_address = $('#edt_ci_client_notify_address');
var edt_ci_client_consignee = $('#edt_ci_client_consignee');
var edt_ci_shipped_from = $('#edt_ci_shipped_from');
var edt_ci_shipped_to = $('#edt_ci_shipped_to');
var edt_ci_vessel = $('#edt_ci_vessel');
var btn_save_comercial_invoice = $('.btn_save_comercial_invoice');

var tbl_ci_products = $('#tbl_ci_products');
var tbl_ci_products_body = tbl_ci_products.find('tbody');

var down_ci_lot_transport_id = 0;
var arr_ci_products;
var call_back_dow_commercial_invoice = null;

function clear_packing_list(){

    set_datepicker(edt_ci_date, '00-00-0000');
    edt_ci_number.val('');
    edt_ci_dv.val('');
    edt_ci_vessel.val('');
    edt_ci_client_notify_address
    edt_ci_client_consignee.val('');
    edt_ci_shipped_from.val('');
    edt_ci_shipped_to.val('');
}

function init_down_commercial_invoice(lot_transport_id, call_back) {
	down_ci_lot_transport_id = lot_transport_id;
    call_back_dow_commercial_invoice = call_back;
	clear_packing_list();

	WS.get(
        "lots/detail/json/" + down_ci_lot_transport_id, {},
        function (response) {

            set_datepicker(edt_ci_date, response.commercial_invoice_date);
            edt_ci_number.val(response.commercial_invoice_number);
            edt_ci_dv.val(response.packing_list_ref);
            edt_ci_client_notify_address.val(response.client_notify_address);
            edt_ci_client_consignee.val(response.client_consignee);
            edt_ci_shipped_from.val(response.shipped_from);
            edt_ci_shipped_to.val(response.shipped_to);
            edt_ci_vessel.val(response.vessel);
        }
    );

    list_ci_products();
}

function list_ci_products() {
    // limpa trs, menos a primeira
    tbl_ci_products.find("tr:gt(1)").remove();

    WS.get(
        "travel_plan/commercial_invoice/products/json/",
        { lot_transport_id: down_ci_lot_transport_id },
        function (response) {
            arr_ci_products = response;
            $.each(response, function(i, item) {
                add_row_ci_products(item);
            });
        }
    );
}

function add_row_ci_products(item) {
    var template_row = tbl_ci_products_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', item.product_id + '_' + item.quality_id);

    $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});

    var field_blocks = $(new_row.find("[template-field='blocks']"));
    field_blocks.text(item.blocks);

    var field_product_name = $(new_row.find("[template-field='product_name']"));
    field_product_name.text(item.product_name);

    var field_quality_name = $(new_row.find("[template-field='quality_name']"));
    field_quality_name.text(item.quality_name);

    var field_value = $(new_row.find("[template-field='value']"));
    field_value.unbind('change');
    field_value.change(function() {
       // $(this).val(Number($(this).val()).toFixed(2)); // arredondo pra 2 casas
    });

    if ((item.value) && (parseFloat(item.value) > 0)) {
        field_value.val(item.value);
    }

    var field_last_value = $(new_row.find("[template-field='last_value']"));
    field_last_value.text(item.last_value ? item.last_value : '');
    field_last_value.css('cursor', 'pointer');
    field_last_value.unbind('click');
    field_last_value.click(function(){
        if (!isNaN(parseFloat(item.last_value))) {
            var value = field_value.val();
            if ((value.trim() == '') || (isNaN(parseFloat(value)))) {
                field_value.val(parseFloat(item.last_value));
            }            
        }
    });

    new_row.appendTo(tbl_ci_products_body);
    $('.input_number').maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});

}

function persist_inputs_ci_values() {
    if (arr_ci_products) {
        for (var i = 0; i < arr_ci_products.length; i++) {
            var row = tbl_ci_products_body.find('[template-row-ref="' + arr_ci_products[i].product_id + '_' + arr_ci_products[i].quality_id + '"] > td');
            if (row.length > 0) {
                arr_ci_products[i].value = row.find('[template-field="value"]').val();
            }
        }
    }
}

function save_commercial_invoice(download){
    persist_inputs_ci_values();
    // chamar webservice que atualiza esses dados
    WS.post(
        "travel_plan/commercial_invoice/save/",
        {
            lot_transport_id: down_ci_lot_transport_id,
            commercial_invoice_date: (get_datepicker(edt_ci_date) != '' ? get_datepicker(edt_ci_date) : null),
            commercial_invoice_number: edt_ci_number.val(),
            packing_list_ref: edt_ci_dv.val(),
            client_notify_address: edt_ci_client_notify_address.val(),
            client_consignee: edt_ci_client_consignee.val(),
            shipped_from: edt_ci_shipped_from.val(),
            shipped_to: edt_ci_shipped_to.val(),
            vessel: edt_ci_vessel.val(),
            products: arr_ci_products
        },
        function (response) {
            

            if(response){
                if(download === true){
                    window.open(
                      '<?= APP_URI ?>travel_plan/commercial_invoice/download/?lot_transport_id=' + down_ci_lot_transport_id,
                      '_blank'
                    );
                }

                if(typeof call_back_dow_commercial_invoice != 'undefined' && call_back_dow_commercial_invoice){
                    call_back_dow_commercial_invoice(download);
                }

                closeModal('modal_down_commercial_invoice');
            }else{
                closeModal('modal_down_commercial_invoice');
            }
            
            
        }
    );
}

btn_save_comercial_invoice.unbind('click');
btn_save_comercial_invoice.click(function(){
    save_commercial_invoice(false);
});

function btn_down_commercial_invoice_click() {
	
    save_commercial_invoice(true);
}