var modal_price_history_title = $('#modal_price_history_title');
var table_client_history = $('#table_client_history');
var modal_price_history_client = null;
var modal_price_history_client_group_id = null;
var modal_price_history_onsave = null;

// abrir modal de histórico de preços
function show_history_dialog(client, client_group_id, onsave)
{
    modal_price_history_title.text('Price History - ' + client.code + ' - ' + client.name)
    modal_price_history_client = client;
    modal_price_history_client_group_id = client_group_id;
    modal_price_history_onsave = onsave;

    list_client_history();
    showModal('modal_price_history');
}

function list_client_history()
{
    table_client_history.find('thead').remove()
    table_client_history.find('tbody').remove()

    var table_thead = $('<thead>').appendTo(table_client_history);
    var table_body = $('<tbody>').appendTo(table_client_history);

    var tr_1 = $("<tr>");
    var tr_2 = $("<tr>");

    var price_arr_qualities_length = price_arr_qualities.length;
    // ignorar qualidades MI e LD quando o client group não for Brasil
    // obs: cliente optou por chumbar no fonte esta regra
    if (modal_price_history_client_group_id != 5) { // 5 = BRASIL
        price_arr_qualities_length = price_arr_qualities.length - 2;
    }

    // percorro os produtos
    $.each(price_arr_products, function(product_index, product) {
        var th_product = $("<th>");
        th_product.text(product.name);
        th_product.data('product_id', product.id);
        th_product.attr('width', '200px');
        th_product.attr('colspan', price_arr_qualities_length);
        th_product.addClass('text-center');
        th_product.css('vertical-align', 'bottom');
        th_product.appendTo(tr_1);

        // percorro as qualidades
        $.each(price_arr_qualities, function(quality_index, quality) {
            // ignorar qualidades MI e LD quando o client group não for Brasil
            // obs: cliente optou por chumbar no fonte esta regra
            // percorro as qualidades
            if (modal_price_history_client_group_id != 5 && (quality.id == 4 || quality.id == 5)) { // 5 = BRASIL
                return;
            }

            var th_quality = $("<th>");
            th_quality.text(quality.name);
            th_quality.data('product_id', product.id);
            th_quality.data('quality_id', quality.id);
            th_quality.attr('width', '200px');
            th_quality.addClass('text-center info');
            th_quality.css('vertical-align', 'bottom');
            th_quality.appendTo(tr_2);
        });
    });

    var th_comments = $("<th>");
    th_comments.text('Comments');
    th_comments.attr('width', '200px');
    th_comments.attr('rowspan', '2');
    th_comments.addClass('text-center');
    th_comments.css('vertical-align', 'bottom');
    th_comments.appendTo(tr_1);

    var th_date_ref = $("<th>");
    th_date_ref.text('Date Ref');
    th_date_ref.css('min-width', '80px');
    th_date_ref.attr('rowspan', '2');
    th_date_ref.addClass('text-center');
    th_date_ref.css('vertical-align', 'bottom');
    th_date_ref.appendTo(tr_1);

    var th_buttons = $("<th>");
    th_buttons.text('');
    th_buttons.css('min-width', '70px');
    th_buttons.attr('rowspan', '2');
    th_buttons.addClass('text-center');
    th_buttons.css('vertical-align', 'bottom');
    th_buttons.appendTo(tr_1);

    var btn_new = $('<button>');
    btn_new.attr('title', 'New Price');
    btn_new.addClass('btn btn-xs btn-primary small');
    btn_new.attr('id', 'modal_price_history_btn_new');
    btn_new.append($('<span>').addClass('glyphicon glyphicon-plus'));
    btn_new.append(' New');
    btn_new.hide();
    btn_new.appendTo(th_buttons);
    btn_new.click(
        function () {
            closeModal('modal_price_history');

            var onsave = function() {
                list_client_history_price(table_body);
                modal_price_history_onsave(modal_price_history_client);
                show_history_dialog(modal_price_history_client, modal_price_history_client_group_id, modal_price_history_onsave);
            }
            show_dialog(FORMULARIO.NOVO, modal_price_history_client, modal_price_history_client_group_id, onsave);
        }
    );

    tr_1.appendTo(table_thead);
    tr_2.appendTo(table_thead);

    list_client_history_price(table_body);
}

function list_client_history_price(table_body) {
    $.getJSON(
        "<?= APP_URI ?>price/history/json/",
        {
            client_id: modal_price_history_client.id
        },
        function(response) {
            if (response_validation(response)) {
                
                $(response).each(function(client_index, client) {
                    
                    // atribuo à variavel client do each os demais dados do cliente, recebidos na abertura do modal
                    client.id = modal_price_history_client.id;
                    client.name = modal_price_history_client.name;
                    client.code = modal_price_history_client.code;

                    var tr_client_price = $("<tr>");

                    var arr_td_quality_price = [];

                    // percorro os produtos
                    $.each(price_arr_products, function(product_index, product) {
                       
                        // percorro as qualidades
                        $.each(price_arr_qualities, function(quality_index, quality) {
                            // ignorar qualidades MI e LD quando o client group não for Brasil
                            // obs: cliente optou por chumbar no fonte esta regra
                            // percorro as qualidades
                            if (modal_price_history_client_group_id != 5 && (quality.id == 4 || quality.id == 5)) { // 5 = BRASIL
                                return;
                            }

                            var price_value = 0;

                            // verifico se existe price definido para o produto + qualidade
                            if (typeof client.values != 'undefined') {
                                $(client.values).each(function(price_index, price) {
                                    if ((parseInt(price.product_id, 10) == parseInt(product.id, 10)) && (parseInt(price.quality_id, 10) == parseInt(quality.id, 10))) {
                                        price_value = price.value.format_number(2);
                                        return;
                                    }
                                });
                            }

                            var td_quality_price = $("<td>");
                            td_quality_price.data('product_id', product.id);
                            td_quality_price.data('quality_id', quality.id);
                            td_quality_price.addClass('text-right');
                            td_quality_price.css('vertical-align', 'middle');
                            td_quality_price.text(price_value == 0 ? '-' : price_value);
                            td_quality_price.appendTo(tr_client_price);
                            arr_td_quality_price.push(td_quality_price);
                        });
                    });

                    var td_comments = $("<td>");
                    td_comments.addClass('text-left');
                    td_comments.css('vertical-align', 'middle');

                    var div_comments = $("<div>");
                    div_comments.addClass('hideextra');
                    div_comments.css('width', '80px');
                    div_comments.text(typeof client.comments != 'undefined' ? client.comments : '-');
                    if (typeof client.comments != 'undefined') {
                        div_comments.tooltip('destroy');
                        div_comments.tooltip({title: nl2br('<p align="left">' + client.comments + '</p>'), placement: 'bottom', html: true});
                    }

                    div_comments.appendTo(td_comments);
                    td_comments.appendTo(tr_client_price);

                    var td_date_ref = $("<td>");
                    td_date_ref.addClass('text-center');
                    td_date_ref.css('vertical-align', 'middle');
                    td_date_ref.text(typeof client.date_ref != 'undefined' ? client.date_ref.format_date() : '-');
                    td_date_ref.appendTo(tr_client_price);

                    var td_buttons = $("<td>");
                    td_buttons.addClass('text-center');
                    td_buttons.css('vertical-align', 'middle');

                    var btn_group = $("<div>").addClass('btn-group');

                    var btn_delete = $('<button>');
                    btn_delete.attr('title', 'Delete');
                    btn_delete.addClass('btn btn-xs btn-default');
                    btn_delete.append($('<span>').addClass('glyphicon glyphicon-trash'));
                    btn_delete.appendTo(btn_group);
                    btn_delete.click(function() {
                        closeModal('modal_price_history');
                        // ao salvar, abro novamente a listagem de históricos do cliente
                        var onsave = function(client_price_saved) {
                            show_history_dialog(modal_price_history_client, modal_price_history_client_group_id, modal_price_history_onsave);
                            modal_price_history_onsave(client_price_saved);
                        }
                        var oncancel = function() {
                            show_history_dialog(modal_price_history_client, modal_price_history_client_group_id, modal_price_history_onsave);
                        }
                        show_dialog(FORMULARIO.EXCLUIR, client, modal_price_history_client_group_id, onsave, oncancel);
                    });

                    var btn_edit = $('<button>');
                    btn_edit.attr('title', 'Edit Price');
                    btn_edit.addClass('btn btn-xs btn-default');
                    btn_edit.append($('<span>').addClass('glyphicon glyphicon-pencil'));
                    btn_edit.appendTo(btn_group);
                    btn_edit.click(function() {
                        closeModal('modal_price_history');
                        // ao salvar, abro novamente a listagem de históricos do cliente
                        var onsave = function(client_price_saved) {
                            show_history_dialog(modal_price_history_client, modal_price_history_client_group_id, modal_price_history_onsave);
                            modal_price_history_onsave(client_price_saved);
                        }
                        var oncancel = function() {
                            show_history_dialog(modal_price_history_client, modal_price_history_client_group_id, modal_price_history_onsave);
                        }
                        show_dialog(FORMULARIO.EDITAR, client, modal_price_history_client_group_id, onsave, oncancel);
                    });

                    btn_group.appendTo(td_buttons);
                    td_buttons.appendTo(tr_client_price);

                    // adiciona a linha na tabela
                    tr_client_price.appendTo(table_body);
                });

                $('#modal_price_history_btn_new').show();

            }
        }
    );
}