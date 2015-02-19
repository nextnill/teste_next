var visualizacao_selecionada = 'calendario';

function listar_filter_quarry(selected_value)
{
    
    var cbo_quarry = $("#cbo_quarry_filter");
    cbo_quarry.find("option").remove();

    cbo_quarry.unbind('change');
    cbo_quarry.change(function() {
        listar(); 
    });

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>quarry/list/json/", function(response) {
        if (response_validation(response)) {
            add_option(cbo_quarry, '-1', 'Filter quarry');
            for (var i = 0; i < response.length; i++) {
                var item = response[i];
                add_option(cbo_quarry, item.id, item.name);
            };

            cbo_quarry.select2();
        }
    }).fail(ajaxError);
}

function btn_visualizar(tipo) {
    visualizacao_selecionada = tipo;

    var btn_calendar = $('#btn_calendar');
    var btn_list = $('#btn_list');

    btn_calendar.removeClass('btn-default');
    btn_calendar.removeClass('btn-primary');
    btn_list.removeClass('btn-default');
    btn_list.removeClass('btn-primary');

    if (visualizacao_selecionada == 'calendario') {
        btn_calendar.addClass('btn-primary');
        btn_list.addClass('btn-default');
    }
    else {
        btn_calendar.addClass('btn-default');
        btn_list.addClass('btn-primary');
    }

    listar();
}

function listar()
{
    // limpa trs, menos a primeira
    $('#tbl_listagem').find("tr:gt(1)").remove();
    // destruo o calendário
    $('#calendar').fullCalendar('destroy');

    if(visualizacao_selecionada == 'calendario'){
        $('#calendar').show();
        $('#div_listagem').hide();
    }
    else {
        $('#calendar').hide();
        $('#div_listagem').show();
    }    

    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');
    var cbo_quarry = $("#cbo_quarry_filter");
            
    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>schedule_inspection/list/json/" + (cbo_quarry.val() ? cbo_quarry.val() : ''),
        {ano: edt_year.val(), mes: cbo_month_filter.val()}, function(response) {
            if (response_validation(response)) {
                
                if (visualizacao_selecionada == 'calendario') {
                    /*
                    var events = [];

                    $(response).each(function(index, item) {
                        // [{"id":"2","excluido":"N","day":"2015-01-20","time":"08:00:00","quarry_id":"2","quarry_name":"Giallo California Classic","client_id":"6","client_name":"FREE TRUE MARBLE LIMITED","title":"FREE TRUE MARBLE LIMITED","obs":"teste","start":"2015-01-20 08:00:00"},{"id":"1","excluido":"N","day":"2015-01-13","time":"08:00:00","quarry_id":"3","quarry_name":"Giallo California F","client_id":"6","client_name":"FREE TRUE MARBLE LIMITED","title":"FREE TRUE MARBLE LIMITED","obs":"teste","start":"2015-01-13 08:00:00"}]
                        events.push({
                            title: item.title,
                            start: item.start
                        });
                    });
                    */
                    calendar(response);
                }
                else {
                    var table_body = $('#tbl_listagem > tbody');
                    $.each(response, function(i, item) {
                        add_row(table_body, item);
                    });
                }
                    

        }
    }).fail(ajaxError);
}

function calendar(events) {
    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');
    var cbo_quarry = $('#cbo_quarry_filter');

    $('#calendar').fullCalendar('removeEvents');
    $('#calendar').fullCalendar('removeEventSources');
    
    var date = new Date(edt_year.val(), cbo_month_filter.val() - 1, 1);
    var calendar = $('#calendar').fullCalendar({
        header: false,
        editable: false,
        eventDurationEditable: true,
        eventStartEditable: false,
        eventLimit: false,
        timeFormat: 'H:mm',
        eventColor : '#7f0000',
        events: events,
        selectable: true,
        selectHelper: true,
        data:{
            ano: edt_year.val(),
            mes: cbo_month_filter.val()
        },
        
        select: function(start){
            start = $.fullCalendar.moment(start).format('YYYY-MM-DD');
            show_dialog(FORMULARIO.NOVO, start); 

            $('#calendar').fullCalendar('unselect');
            $('.fc-event-container').css('cursor', 'pointer');
       },
        eventClick: function(event){
            show_dialog(FORMULARIO.EDITAR, event.id);
            $('#calendar').fullCalendar('updateEvent', event);
            $('.fc-event-container').css('cursor', 'pointer');
        },
    }).fullCalendar('gotoDate', date);

    $('.fc-event-container').css('cursor', 'pointer');
}                      

function add_row(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_day = $(new_row.find("[template-field='day']"));
    field_day.text(item.day.format_date());

    /*
    var field_time = $(new_row.find("[template-field='time']"));
    field_time.text(item.time.format_time());
    */

    var field_client_name = $(new_row.find("[template-field='client_name']"));
    field_client_name.text(item.client_name);

    /*
    var field_quarry_name = $(new_row.find("[template-field='quarry_name']"));
    field_quarry_name.text(item.quarry_name);
    */

    var button_edit = new_row.find("[template-button='edit']");
    button_edit.attr('template-ref', item.id);
    button_edit.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.EDITAR, id);
        }
    );

    var button_visualize = new_row.find("[template-button='visualize']");
    button_visualize.attr('template-ref', item.id);
    button_visualize.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.VISUALIZAR, id);
        }
    );

    var button_delete = new_row.find("[template-button='delete']");
    button_delete.attr('template-ref', item.id);
    button_delete.click(
        function () {
            var id = $(this).attr('template-ref');
            show_dialog(FORMULARIO.EXCLUIR, id);
        }
    );

    new_row.appendTo(table_body);
}

funcs_on_load.push(function() {

    listar_filter_quarry();

    var agora = new Date();
    var mes = ("0" + (agora.getMonth() + 1)).slice(-2);
    var ano = agora.getFullYear();

    var edt_year = $('#edt_year');
    var cbo_month_filter = $('#cbo_month_filter');
    var cbo_quarry = $('#cbo_quarry_filter');

    edt_year.val(ano);
    cbo_month_filter.val(mes);

    listar();

});
