function add_option(select, value, text)
{
    select.append("<option value='" + value + "'>" + text + "</option>");
}

function set_focus(obj) {
	setTimeout(function() { $(obj).focus() }, 700);
}

function arredondar3(numero){	
	return parseFloat((numero).toFixed(4)).toFixed(3);   
	
}

function nl2br (str, is_xhtml) {   
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}