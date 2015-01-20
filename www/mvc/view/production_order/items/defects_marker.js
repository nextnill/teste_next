var selected_tool = null;
var posicoes = [];
var arr_elem_defects = [];
var dm_rec_defects_json = null;
var dm_svg_thumb = null;

var canvas = new fabric.Canvas('canvas');

function abre_defects_marker(block_number, rec_defects_json, svg_thumb, readonly)
{
    dm_rec_defects_json = rec_defects_json;
    dm_svg_thumb = svg_thumb;

    clear_defects();

    if (dm_rec_defects_json.val() != '') {
        canvas.loadFromJSON(JSON.parse(rec_defects_json.val())).renderAll();
    }

    // somente leitura
    if ((readonly) && (readonly == true)) {
        $("#btn_dm_save").hide();
        $("#btn_dm_new").hide();
        $("#dm_tools > button").prop("disabled", true);
    }

    // abro a janela
    showModal('modal_detalhe_defects_marker');
    // altero o titulo
    $('#modal_detalhe_defects_marker_label').text('Marker Defects of ' + block_number);
    // marco a ferramenta de desenhar circulo
}

function draw_defects(arr_defects, append_to, desenho) {
    $.each(arr_defects, function(i, defect) {
        switch (defect.type) {
            case 'circle':
                desenha_circulo();
                break;
            case 'line':
                desenha_reta();
                break;
        }
    });
}

$('#btn_tool_circle').unbind('click');
$('#btn_tool_circle').click(function() {
    desenha_circulo();
    
});


$('#btn_tool_line').unbind('click');
$('#btn_tool_line').click(function() {
    desenha_reta();
        
});


$('#btn_tool_rubber').unbind('click');
$('#btn_tool_rubber').click(function() {
    var activeObject = canvas.getActiveObject(),
    activeGroup = canvas.getActiveGroup();

    if(activeObject == null){
         alert_modal('Warning', 'First select a defect to erase.');              
    }

    if (activeGroup) {
        var objectsInGroup = activeGroup.getObjects();
        canvas.discardActiveGroup();
        objectsInGroup.forEach(function(object) {
            canvas.remove(object);
        });
    }
    else if (activeObject) {
        canvas.remove(activeObject);
        }
    });


function desenha_circulo(){

   var circle = new fabric.Circle({radius: 30 , fill: '', top: 10, left: 10, stroke: 10, scaleX: 1, scaleY: 1});

   circle.toObject = (function(toObject) {
    return function() {
    return fabric.util.object.extend(toObject.call(this), {
      name: this.name
    });
  };
    })(circle.toObject);

   circle.name = get_new_ref();
   canvas.add(circle).renderAll();

}

function desenha_reta(){

    var reta = new fabric.Rect({width: 100, height: 2, fill: 'black', stroke: 10, top: 50, left: 50, scaleX: 1, scaleY: 1});

    reta.toObject = (function(toObject) {
    return function() {
        return fabric.util.object.extend(toObject.call(this), {
            name: this.name
        });
    };
    })(reta.toObject);

   reta.name = get_new_ref();
   canvas.add(reta).renderAll();

}
    
function clear_defects() {
   canvas.clear();
}

function block_editor_new() {
    var ok_click = function() {
        clear_defects();
        closeModal('alert_modal');
    };

    alert_modal('Clear all', 'Want to clear everything?', '', ok_click, true);    
}

function get_new_ref() {
    var max_ref = 0;
    var desenho = canvas.toDatalessJSON();
    
    if (typeof desenho.objects != 'undefined') {
        for(var i=0; i < desenho.objects.length; i++){
            if (parseInt(desenho.objects[i].name, 10) > max_ref) {
                max_ref = parseInt(desenho.objects[i].name, 10);
            }
        };
    }
    
    return max_ref+1;
}

/* save */
function dm_save() {
    dm_rec_defects_json.val(JSON.stringify(canvas.toDatalessJSON()));

    // atualizo o thumb do bloco
    var defect_marker = new fabric.StaticCanvas(dm_svg_thumb.attr("id"));
    var defects_thumb = JSON.parse(dm_rec_defects_json.val());
    resize(defects_thumb);
    defect_marker.loadFromJSON(JSON.stringify(defects_thumb)).renderAll();

    // fecho a janela
    closeModal('modal_detalhe_defects_marker');
}

function resize(desenho){
    if (typeof desenho.objects != 'undefined') {
        for(var i=0; i < desenho.objects.length; i++){

            desenho.objects[i].left = desenho.objects[i].left/2;
            desenho.objects[i].top = desenho.objects[i].top/2;
            desenho.objects[i].scaleX = desenho.objects[i].scaleX/2;
            desenho.objects[i].scaleY = desenho.objects[i].scaleY/2;
        }
    }
}
