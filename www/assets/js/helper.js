function add_option(select, value, text)
{
    select.append("<option value='" + value + "'>" + text + "</option>");
}

function set_focus(obj) {
	setTimeout(function() { $(obj).focus() }, 700);
}

Number.prototype.toFixedB = function toFixed ( precision ) {
    var multiplier = Math.pow( 10, precision + 1 ),
        wholeNumber = Math.floor( this * multiplier );
    return (Math.round( wholeNumber / 10 ) * 10 / multiplier).toFixed(precision);
}

Number.prototype.toFixed10 = function(precision) {
    return Math.round10(this, -precision).toFixed(precision);
}

function arredondar3(numero){	
	//return parseFloat((numero).toFixed(4)).toFixed(3);   
	return (numero).toFixedB(3);   
}

function nl2br (str, is_xhtml) {   
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';    
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ breakTag +'$2');
}

