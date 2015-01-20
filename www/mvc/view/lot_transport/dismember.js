/* fields */
var tbl_dis_orig = $('#tbl_dis_orig');
var tbl_dis_orig_tbody = $('#tbl_dis_orig > tbody');

var tbl_dis_new = $('#tbl_dis_new');
var tbl_dis_new_tbody = $('#tbl_dis_new > tbody');

var dis_title_lot_number_orig = $('#dis_title_lot_number_orig');

/* vars */
var obj_dis_lot_transport = null;
var arr_dis_lot_blocks = [];
var arr_dis_new_lot_blocks = [];

/* funções */
function dis_sort_blocks(arr_blocks) {
    // ordeno os blocos
    return arr_blocks.sort(function(obj1, obj2) {
        return obj1.block_number > obj2.block_number;
    });
}

function show_dialog_dismember(lot_transport_id, lot_number)
{
    dis_title_lot_number_orig.text(lot_number);
    dis_listar_blocos(lot_transport_id);

    showModal('modal_detalhe_dismember');
}

function dis_listar_blocos(lot_transport_id)
{
    arr_dis_lot_blocks = [];
    arr_dis_new_lot_blocks = [];

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>lots/detail/json/" + lot_transport_id, function(response) {
        if (response_validation(response)) {
            obj_dis_lot_transport = response;

            arr_dis_lot_blocks = response.items.filter(function(item) {
                return parseInt(item.dismembered, 10) == false;
            });
            //arr_dis_lot_blocks = response.items;

            dis_render_blocks();
        }
    }).fail(ajaxError);
}

function dis_render_blocks()
{
    dis_render_blocks_orig();
    dis_render_blocks_new();
}

function dis_render_blocks_orig()
{
    // limpa trs, menos a primeira
    tbl_dis_orig.find("tr:gt(1)").remove();

    // ordeno os blocos
    arr_dis_lot_blocks = dis_sort_blocks(arr_dis_lot_blocks);

    $.each(arr_dis_lot_blocks, function(i, item) {
        add_row_dis_orig(tbl_dis_orig_tbody, item);
    });
}

function add_row_dis_orig(table_body, item)
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

    var field_destination = $(new_row.find("[template-field='destination']"));
    field_destination.text(item.last_end_location ? item.last_end_location : '');

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(item.last_end_location ? str_travel_plan_status(item.status) : '');

    if (item.last_end_location) {
        switch (parseInt(item.status, 10)) {
            case TRAVEL_PLAN_STATUS.PENDING:
                field_status.addClass('label label-default');
                break;
            case TRAVEL_PLAN_STATUS.STARTED:
                field_status.addClass('label label-warning');
                break;
            case TRAVEL_PLAN_STATUS.COMPLETED:
                field_status.addClass('label label-success');
                break;
        }
    }
    
    var button_select = new_row.find("[template-button='select']");
    button_select.click(function() {
        arr_dis_new_lot_blocks.push(item);

        var index_of_orig = arr_dis_lot_blocks.indexOf(item);
        arr_dis_lot_blocks.splice(index_of_orig, 1);

        dis_render_blocks();
    });

    new_row.appendTo(table_body);
}

function dis_render_blocks_new()
{
    // limpa trs, menos a primeira
    tbl_dis_new.find("tr:gt(1)").remove();

    // ordeno os blocos
    arr_dis_new_lot_blocks = dis_sort_blocks(arr_dis_new_lot_blocks);

    $.each(arr_dis_new_lot_blocks, function(i, item) {
        add_row_dis_new(tbl_dis_new_tbody, item);
    });
}

function add_row_dis_new(table_body, item)
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

    var field_destination = $(new_row.find("[template-field='destination']"));
    field_destination.text(item.last_end_location ? item.last_end_location : '');

    var field_status = $(new_row.find("[template-field='status']"));
    field_status.text(item.last_end_location ? str_travel_plan_status(item.status) : '');

    if (item.last_end_location) {
        switch (parseInt(item.status, 10)) {
            case TRAVEL_PLAN_STATUS.PENDING:
                field_status.addClass('label label-default');
                break;
            case TRAVEL_PLAN_STATUS.STARTED:
                field_status.addClass('label label-warning');
                break;
            case TRAVEL_PLAN_STATUS.COMPLETED:
                field_status.addClass('label label-success');
                break;
        }
    }
    
    var button_select = new_row.find("[template-button='select']");
    button_select.click(function() {
        arr_dis_lot_blocks.push(item);

        var index_of_new = arr_dis_new_lot_blocks.indexOf(item);
        arr_dis_new_lot_blocks.splice(index_of_new, 1);

        dis_render_blocks();
    });

    new_row.appendTo(table_body);
}