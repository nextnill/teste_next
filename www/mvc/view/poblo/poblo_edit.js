var edt_date_nf = $('#edt_date_nf');
var edt_nf = $('#edt_nf');
var edt_price = $('#edt_price');
var edt_wagon_number = $('#edt_wagon_number');
var btn_poblo_edit_cancel = $('#btn_poblo_edit_cancel');
var btn_poblo_edit_save = $('#btn_poblo_edit_save');
var div_wagon = $('.div_wagon');

var var_callback = null;
var block_id = null;
var invoice_item_id = null;

function show_poblo_edit(block_id_param, invoice_item_id_param, callback, nf, date_nf, price, wagon_number, type) {

    clear_edit();

    if(type == 'insp'){
        div_wagon.addClass('hidden');
    }else{
        div_wagon.removeClass('hidden');
    }

    if(nf){
        edt_nf.val(nf);
    }
    
    set_datepicker(edt_date_nf);


    setTimeout(function(){
        if(date_nf){
            set_datepicker(edt_date_nf, date_nf);
            //edt_date_nf.val(date_nf);
        }   
    }, 150);
    
    if(price){
        edt_price.val(price);
    }

    if(wagon_number){
        edt_wagon_number.val(wagon_number);
    }

    block_id = block_id_param;
    invoice_item_id = invoice_item_id_param;

    if(callback){
        var_callback = callback;
    }
    
	edt_price.maskMoney({thousands:'', decimal:'.', allowZero:true, suffix: '', precision:2});
    showModal('modal_poblo_edit');
      
}

function clear_edit(){

    edt_date_nf.val('');
    edt_wagon_number.val('');
    edt_nf.val('');
    edt_price.val('');
}

btn_poblo_edit_cancel.unbind('click');
btn_poblo_edit_cancel.click(function() {
	closeModal('modal_poblo_edit');
});

btn_poblo_edit_save.unbind('click');
btn_poblo_edit_save.click(function() {


	WS.post(
        "poblo/save_edit/",
        {
            block_id: block_id,
            invoice_item_id: invoice_item_id,
            date_nf: get_datepicker(edt_date_nf),
            nf: edt_nf.val(),
            price: edt_price.val(),
            wagon_number: edt_wagon_number.val()
        },
        function (response) {

            if(var_callback){
                var_callback(edt_nf.val(), edt_price.val(), edt_wagon_number.val(), get_datepicker(edt_date_nf));
            }
			closeModal('modal_poblo_edit');
        }
    );
});
