/* fields */
var tbl_dic_new = $('#tbl_dic_new');
var tbl_dic_new_tbody = $('#tbl_dic_new > tbody');

var dic_title_lot_number_orig = $('#dis_title_lot_number_orig');
var edt_dic_lot_number = $('#edt_dic_lot_number');
var btn_dic_confirm = $('#btn_dic_confirm');

/* vars */
var obj_dic_lot_transport = null;
var arr_dic_lot_blocks = [];
var arr_dic_new_lot_blocks = [];

/* funções */
function dic_show_dialog_confirm()
{
    // validação de rota e status igual para todos selecionados
    var last_travel_route_id = null;
    var status = null;
    var error = false;

    for (var i = 0; i < arr_dis_new_lot_blocks.length; i++) {

        if ((i > 0)
            && ((last_travel_route_id != arr_dis_new_lot_blocks[i].last_travel_route_id)
                    || (status != arr_dis_new_lot_blocks[i].status))) {
            error = true;
        }

        last_travel_route_id = arr_dis_new_lot_blocks[i].last_travel_route_id;
        status = arr_dis_new_lot_blocks[i].status;
    };

    if (error) {
        alert_modal('Validation', 'The blocks of the new lot should be in the same location and have the same status.');
        return;
    }

    // prepara e abre tela de confirmação
    dic_title_lot_number_orig.text(obj_dis_lot_transport.lot_number);
    dic_render_blocks_new();

    dic_get_lot_number();

    edt_dic_lot_number.unbind('change');
    edt_dic_lot_number.change(edt_dic_lot_number_change);

    set_focus(edt_dic_lot_number);

    closeModal('modal_detalhe_dismember');
    showModal('modal_detalhe_dismember_confirm');
}

function edt_dic_lot_number_change() {
    edt_dic_lot_number.tooltip('destroy');
    btn_dic_confirm.attr('disabled', true);

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>lots/exists/lot_number/json/", { 'lot_number': $(this).val() }, function(response) {
        if (response_validation(response)) {
            if ((response.exists) && (response.exists > 0)) {
                edt_dic_lot_number.tooltip({title: 'This lot number is already in use', placement: 'right', trigger: 'manual'});
                edt_dic_lot_number.tooltip('show');
                btn_dic_confirm.attr('disabled', true);
            }
            else {
                edt_dic_lot_number.tooltip('destroy');
                btn_dic_confirm.attr('disabled', false);
            }
        }
    }).fail(ajaxError);
}

function dic_get_lot_number()
{
    edt_dic_lot_number.val('');
    $.ajaxSetup({ cache: false });
    $.getJSON("<?= APP_URI ?>lots/nextval/lot_number/json/", function(response) {
        if (response_validation(response)) {
            if (response.lot_number) {
                edt_dic_lot_number.val(response.lot_number);
            }
        }
    }).fail(ajaxError);
}

function dic_render_blocks_new()
{
    // limpa trs, menos a primeira
    tbl_dic_new.find("tr:gt(1)").remove();

    $.each(arr_dis_new_lot_blocks, function(i, item) {
        add_row_dic_new(tbl_dic_new_tbody, item);
    });
}

function add_row_dic_new(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    new_row.attr('template-row-ref', item.id);

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(item.block_number);

    var field_quality = $(new_row.find("[template-field='quality']"));
    field_quality.text(item.quality_name);

    new_row.appendTo(table_body);
}

function dic_confirm() {
    // 
    // arr_dis_new_lot_blocks

    $.ajax({
        error: ajaxError,
        type: "POST",
        url: "<?= APP_URI ?>lots/dismember/",
        data: {
            orig_lot_transport_id: obj_dis_lot_transport.id,
            lot_number: edt_dic_lot_number.val(),
            items: arr_dis_new_lot_blocks
        },
        success: function (response) {
            if (response_validation(response)) {
                closeModal('modal_detalhe_dismember_confirm');
                listar();
            }
        }
    });
}