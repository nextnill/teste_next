var cbo_client_groups = $('#cbo_client_groups');
var div_price_list = $('#div_price_list');
var price_list = $('#price_list');
var price_arr_client_groups = [];
var price_arr_products = [];
var price_arr_qualities = [];

funcs_on_load.push(function() {
    load_client_groups();
});

function load_client_groups(selected_values, readonly)
{   
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>client_group/get_by_user/", function(response) {
        if (response_validation(response)) {
            price_arr_client_groups = response;
            list_client_groups();
            load_products();
        }
    }).fail(ajaxError);
}

function load_products(selected_values)
{
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>product/list/json/", function(response) {
        if (response_validation(response)) {
            price_arr_products = response;
            load_qualities();
        }
    }).fail(ajaxError);
}

function load_qualities(selected_value) {
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quality/list/json/", function(response) {
        if (response_validation(response)) {
            price_arr_qualities = response;
            list_client_groups_tables();
        }
    }).fail(ajaxError);
}

function list_client_groups(selected_values, readonly)
{   
    cbo_client_groups.unbind('change');

    cbo_client_groups.find("option").remove();
    cbo_client_groups.select2("readonly", false);

    add_option(cbo_client_groups, '-1', 'All Client Groups');
    $.each(price_arr_client_groups, function(i, item) {
        add_option(cbo_client_groups, item.id, item.name);
    });

    cbo_client_groups.select2();

    if ((readonly) && (readonly === true)) {
        cbo_client_groups.select2("readonly", true);
    }

    if (selected_values)
        cbo_client_groups.val(selected_values).trigger("change");
    
    cbo_client_groups.change(function() {
        
        $('.panel_client_group:not([template-panel-client-group])').each(function(index, elem) {
            var exibir = (cbo_client_groups.val() == $(elem).data('client_group_id')) || cbo_client_groups.val() == '-1';
            
            if (exibir) {
                setTimeout(function() {
                    $(elem).fadeIn();
                }, 1000);
            }
            else {
                $(elem).fadeOut(500);
            }
            
        });
    });

    set_focus(cbo_client_groups);
}

function list_client_groups_tables() {
    price_list.html('');

    $.each(price_arr_client_groups, function(client_group_index, client_group) {
        var panel = div_price_list.find('[template-panel-client-group]').clone();
        var table = panel.find('.table_client_group_list');
        var table_thead = $('<thead>').appendTo(table);
        var table_body = $('<tbody>').appendTo(table);
        var client_group_name = panel.find("[template-name]");

        panel.data('client_group_id', client_group.id);
        panel.removeAttr("template-panel-client-group");
        panel.css("display", '');

        // nome no grupo do cliente
        client_group_name.text(client_group.name);
        
        var tr_1 = $("<tr>");
        var tr_2 = $("<tr>");

        var th_client = $("<th>");
        th_client.text('Client');
        th_client.attr('width', '200px');
        th_client.attr('rowspan', '2');
        th_client.addClass('text-center');
        th_client.css('vertical-align', 'bottom');
        th_client.appendTo(tr_1);

        var price_arr_qualities_length = price_arr_qualities.length;
        // ignorar qualidades MI e LD quando o client group não for Brasil
        // obs: cliente optou por chumbar no fonte esta regra
        if (client_group.id != 5) { // 5 = BRASIL
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

            $.each(price_arr_qualities, function(quality_index, quality) {
                // ignorar qualidades MI e LD quando o client group não for Brasil
                // obs: cliente optou por chumbar no fonte esta regra
                // percorro as qualidades
                if (client_group.id != 5 && (quality.id == 4 || quality.id == 5)) { // 5 = BRASIL
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
        th_buttons.css('min-width', '90px');
        th_buttons.attr('rowspan', '2');
        th_buttons.addClass('text-center');
        th_buttons.css('vertical-align', 'bottom');
        th_buttons.appendTo(tr_1);

        tr_1.appendTo(table_thead);
        tr_2.appendTo(table_thead);
    
        panel.appendTo(price_list);

        list_price(panel, table_body);
    });

}


function list_price(panel, table_body) {
    var client_group_id = panel.data('client_group_id');

    // pesquisa a listagem em json
    $.getJSON(
        "<?= APP_URI ?>price/list/json/",
        {
            client_group_id: client_group_id
        },
        function(response) {
            if (response_validation(response)) {
                
                if (typeof response.clients != 'undefined') {
                    
                    $(response.clients).each(function(client_index, client) {
                        
                        var tr_client_price = $("<tr>");

                        var td_client = $("<td>");
                        td_client.append($("<span>").text(client.code).tooltip({title: client.name, placement: 'right'}));
                        td_client.addClass('text-center');
                        td_client.css('vertical-align', 'middle');
                        td_client.appendTo(tr_client_price);

                        var arr_td_quality_price = [];

                        // percorro os produtos
                        $.each(price_arr_products, function(product_index, product) {
                           
                            // percorro as qualidades
                            $.each(price_arr_qualities, function(quality_index, quality) {
                                // ignorar qualidades MI e LD quando o client group não for Brasil
                                // obs: cliente optou por chumbar no fonte esta regra
                                // percorro as qualidades
                                if (client_group_id != 5 && (quality.id == 4 || quality.id == 5)) { // 5 = BRASIL
                                    return;
                                }

                                var td_quality_price = $("<td>");
                                td_quality_price.data('product_id', product.id);
                                td_quality_price.data('quality_id', quality.id);
                                td_quality_price.addClass('text-right');
                                td_quality_price.css('vertical-align', 'middle');
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
                        div_comments.appendTo(td_comments);

                        td_comments.appendTo(tr_client_price);

                        var td_date_ref = $("<td>");
                        td_date_ref.addClass('text-center');
                        td_date_ref.css('vertical-align', 'middle');
                        td_date_ref.appendTo(tr_client_price);

                        var td_buttons = $("<td>");
                        td_buttons.addClass('text-center');
                        td_buttons.css('vertical-align', 'middle');

                        var btn_group = $("<div>").addClass('btn-group');

                        var btn_new = $('<button>');
                        btn_new.attr('title', 'New Price');
                        btn_new.addClass('btn btn-xs btn-default');
                        btn_new.append($('<span>').addClass('glyphicon glyphicon-plus'));
                        btn_new.appendTo(btn_group);

                        var btn_edit = $('<button>');
                        btn_edit.attr('title', 'Edit Price');
                        btn_edit.addClass('btn btn-xs btn-default');
                        btn_edit.append($('<span>').addClass('glyphicon glyphicon-pencil'));
                        btn_edit.appendTo(btn_group);

                        var btn_history = $('<button>');
                        btn_history.attr('title', 'Price History');
                        btn_history.addClass('btn btn-xs btn-default');
                        btn_history.append($('<span>').addClass('glyphicon glyphicon-calendar'));
                        btn_history.appendTo(btn_group);

                        btn_group.appendTo(td_buttons);
                        td_buttons.appendTo(tr_client_price);

                        // imprime os dados da linha
                        paint_row_values(client, client_group_id, arr_td_quality_price, div_comments, td_date_ref, btn_new, btn_edit, btn_history);

                        // adiciona a linha na tabela
                        tr_client_price.appendTo(table_body);

                    });
                }
                        
            }
        }
    ).fail(ajaxError);
}

// imprime os dados da linha
function paint_row_values(client, client_group_id, arr_td_quality_price, div_comments, td_date_ref, btn_new, btn_edit, btn_history) {
    //console.log(client);
    
    // percorro os produtos
    arr_td_quality_price_index = 0;
    $.each(price_arr_products, function(product_index, product) {
        
        // percorro as qualidades
        $.each(price_arr_qualities, function(quality_index, quality) {
            
            // ignorar qualidades MI e LD quando o client group não for Brasil
            // obs: cliente optou por chumbar no fonte esta regra
            // percorro as qualidades
            if (client_group_id != 5 && (quality.id == 4 || quality.id == 5)) { // 5 = BRASIL
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

            $(arr_td_quality_price[arr_td_quality_price_index]).text(price_value == 0 ? '-' : price_value);
            arr_td_quality_price_index++;
        });
    });

    td_date_ref.text(typeof client.date_ref != 'undefined' ? client.date_ref.format_date() : '-');
    div_comments.text(typeof client.comments != 'undefined' ? client.comments : '-');
    if (typeof client.comments != 'undefined') {
        div_comments.tooltip('destroy');
        div_comments.tooltip({title: nl2br('<p align="left">' + client.comments + '</p>'), placement: 'bottom', html: true});
    }

    var onsave = function(client_price_saved) {
        paint_row_values(client_price_saved, client_group_id, arr_td_quality_price, div_comments, td_date_ref, btn_new, btn_edit, btn_history);
    }

    btn_new.unbind('click');
    btn_new.click(
        function () {
            show_dialog(FORMULARIO.NOVO, client, client_group_id, onsave);
        }
    );

    btn_edit.unbind('click');
    btn_history.unbind('click');

    if (typeof client.price_list_id != 'undefined') {
        btn_edit.removeAttr('disabled');
        btn_edit.click(
            function () {
                show_dialog(FORMULARIO.EDITAR, client, client_group_id, onsave);
            }
        );
        btn_history.removeAttr('disabled');
        btn_history.click(
            function () {
                show_history_dialog(client, client_group_id, onsave);
            }
        );
    }
    else {
        btn_edit.attr('disabled', 'disabled');
        btn_history.attr('disabled', 'disabled');
    }

    
}