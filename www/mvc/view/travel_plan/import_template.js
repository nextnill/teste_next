/* fields */
var tbl_templates = $('#tbl_templates');
var tbl_templates_tbody = $('#tbl_templates > tbody');

/* vars */
var arr_templates = [];

/* funções */
function show_dialog_import_template()
{
    listar_templates();
    showModal('modal_detalhe_import_template');
}

function listar_templates()
{
    arr_templates = [];

    // limpa trs, menos a primeira
    tbl_templates.find("tr:gt(1)").remove();

    // pesquisa a listagem em json
    $.getJSON("<?= APP_URI ?>travel_plan/template/list/json/", function(response) {
        if (response_validation(response)) {
            arr_templates = response;

            $.each(arr_templates, function(i, item) {
                add_row_template(tbl_templates_tbody, item);
            });
        }        
    }).fail(ajaxError);
}

function add_row_template(table_body, item)
{
    var template_row = table_body.find("tr:first");
    var new_row = template_row.clone();
    new_row.removeAttr("template-row");
    new_row.css("display", '');

    var field_id = $(new_row.find("[template-field='id']"));
    field_id.text(item.id);

    var field_description = $(new_row.find("[template-field='description']"));
    field_description.text(item.description);
    
    var button_select = new_row.find("[template-button='select']");
    button_select.click(function() {

        var data = {
            lot_transport_id: lot_transport_id,
            travel_plan_template_id: item.id
        };

        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>travel_plan/import_template/",
            data: data,
            success: function (response) {
                if (response_validation(response)) {
                    closeModal('modal_detalhe_import_template');
                    alert_saved('Template successfully imported');
                    tp_listar();
                }
                
            }
        });

    });

    new_row.appendTo(table_body);
}