var blocks = [];
var selected_block = null;
var edt_block_number_search = null;
var btn_block_number_search = null;

function init()
{
    edt_block_number_search = $('#edt_block_number_search');
    btn_block_number_search = $('#btn_block_number_search');

    btn_block_number_search.unbind('click');
    btn_block_number_search.click(function() {
        listar_blocks(edt_block_number_search.val());
    });
}

function listar_blocks(block_number)
{
    if (block_number.trim() == '') {
        alert_modal('Validation', 'Enter the block number.');
        return;
    }

    // limpa trs, menos a primeira
    //
    $('#tbl_listagem').find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>block/list/json/",
        {
            block_number: block_number,
            client_id: -1
        },
        function(response) {
        if (response_validation(response)) {
            blocks = response;
            render_list();
        }
    }).fail(ajaxError);
}

function render_list()
{
    var table_body = $('#tbl_listagem > tbody');

    $.each(blocks, function(i, item) {
        add_row(table_body, item);
    });
}

function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_block_number = $(new_row.find("[template-field='block_number']"));
    field_block_number.text(item.block_number);

    var field_tot_c = $(new_row.find("[template-field='tot_c']"));
    field_tot_c.text(item.tot_c ? item.tot_c.format_number(2) : '');

    var field_tot_a = $(new_row.find("[template-field='tot_a']"));
    field_tot_a.text(item.tot_a ? item.tot_a.format_number(2) : '');

    var field_tot_l = $(new_row.find("[template-field='tot_l']"));
    field_tot_l.text(item.tot_l ? item.tot_l.format_number(2) : '');

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_vol.text('');

    var field_sale_net_c = $(new_row.find("[template-field='sale_net_c']"));
    field_sale_net_c.text(item.sale_net_c ? item.sale_net_c.format_number(2) : '');

    var field_sale_net_a = $(new_row.find("[template-field='sale_net_a']"));
    field_sale_net_a.text(item.sale_net_a ? item.sale_net_a.format_number(2) : '');

    var field_sale_net_l = $(new_row.find("[template-field='sale_net_l']"));
    field_sale_net_l.text(item.sale_net_l ? item.sale_net_l.format_number(2) : '');

    var field_sale_net_vol = $(new_row.find("[template-field='sale_net_vol']"));
    field_sale_net_vol.text(item.sale_net_vol ? item.sale_net_vol.format_number(3) : '');

    var field_tot_weight = $(new_row.find("[template-field='tot_weight']"));
    field_tot_weight.text(item.tot_weight ? item.tot_weight.format_number(3) : '');

    var field_obs = $(new_row.find("[template-field='obs']"));
    field_obs.text(item.obs);

    var field_reinspection = $(new_row.find("[template-field='reinspection']"));
    field_reinspection.text(item.reinspection ? item.reinspection.format_date_time() : '');

    var button_edit = new_row.find("[template-button='edit']");
    button_edit.attr('template-ref', item.id);
    button_edit.click(function() {
        var id = $(this).attr('template-ref');
        show_dialog_reinspection_block(item.id);
    });

    new_row.appendTo(table_body);
}

// on load window
funcs_on_load.push(function() {
    init();
});