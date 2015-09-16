/* Modernizr 2.7.1 (Custom Build) | MIT & BSD
 * Build: http://modernizr.com/download/#-inputtypes-load
 */
;window.Modernizr=function(a,b,c){function u(a){i.cssText=a}function v(a,b){return u(prefixes.join(a+";")+(b||""))}function w(a,b){return typeof a===b}function x(a,b){return!!~(""+a).indexOf(b)}function y(a,b,d){for(var e in a){var f=b[a[e]];if(f!==c)return d===!1?a[e]:w(f,"function")?f.bind(d||b):f}return!1}function z(){e.inputtypes=function(a){for(var d=0,e,g,h,i=a.length;d<i;d++)j.setAttribute("type",g=a[d]),e=j.type!=="text",e&&(j.value=k,j.style.cssText="position:absolute;visibility:hidden;",/^range$/.test(g)&&j.style.WebkitAppearance!==c?(f.appendChild(j),h=b.defaultView,e=h.getComputedStyle&&h.getComputedStyle(j,null).WebkitAppearance!=="textfield"&&j.offsetHeight!==0,f.removeChild(j)):/^(search|tel)$/.test(g)||(/^(url|email)$/.test(g)?e=j.checkValidity&&j.checkValidity()===!1:e=j.value!=k)),n[a[d]]=!!e;return n}("search tel url email datetime date month week time datetime-local number range color".split(" "))}var d="2.7.1",e={},f=b.documentElement,g="modernizr",h=b.createElement(g),i=h.style,j=b.createElement("input"),k=":)",l={}.toString,m={},n={},o={},p=[],q=p.slice,r,s={}.hasOwnProperty,t;!w(s,"undefined")&&!w(s.call,"undefined")?t=function(a,b){return s.call(a,b)}:t=function(a,b){return b in a&&w(a.constructor.prototype[b],"undefined")},Function.prototype.bind||(Function.prototype.bind=function(b){var c=this;if(typeof c!="function")throw new TypeError;var d=q.call(arguments,1),e=function(){if(this instanceof e){var a=function(){};a.prototype=c.prototype;var f=new a,g=c.apply(f,d.concat(q.call(arguments)));return Object(g)===g?g:f}return c.apply(b,d.concat(q.call(arguments)))};return e});for(var A in m)t(m,A)&&(r=A.toLowerCase(),e[r]=m[A](),p.push((e[r]?"":"no-")+r));return e.input||z(),e.addTest=function(a,b){if(typeof a=="object")for(var d in a)t(a,d)&&e.addTest(d,a[d]);else{a=a.toLowerCase();if(e[a]!==c)return e;b=typeof b=="function"?b():b,typeof enableClasses!="undefined"&&enableClasses&&(f.className+=" "+(b?"":"no-")+a),e[a]=b}return e},u(""),h=j=null,e._version=d,e}(this,this.document),function(a,b,c){function d(a){return"[object Function]"==o.call(a)}function e(a){return"string"==typeof a}function f(){}function g(a){return!a||"loaded"==a||"complete"==a||"uninitialized"==a}function h(){var a=p.shift();q=1,a?a.t?m(function(){("c"==a.t?B.injectCss:B.injectJs)(a.s,0,a.a,a.x,a.e,1)},0):(a(),h()):q=0}function i(a,c,d,e,f,i,j){function k(b){if(!o&&g(l.readyState)&&(u.r=o=1,!q&&h(),l.onload=l.onreadystatechange=null,b)){"img"!=a&&m(function(){t.removeChild(l)},50);for(var d in y[c])y[c].hasOwnProperty(d)&&y[c][d].onload()}}var j=j||B.errorTimeout,l=b.createElement(a),o=0,r=0,u={t:d,s:c,e:f,a:i,x:j};1===y[c]&&(r=1,y[c]=[]),"object"==a?l.data=c:(l.src=c,l.type=a),l.width=l.height="0",l.onerror=l.onload=l.onreadystatechange=function(){k.call(this,r)},p.splice(e,0,u),"img"!=a&&(r||2===y[c]?(t.insertBefore(l,s?null:n),m(k,j)):y[c].push(l))}function j(a,b,c,d,f){return q=0,b=b||"j",e(a)?i("c"==b?v:u,a,b,this.i++,c,d,f):(p.splice(this.i++,0,a),1==p.length&&h()),this}function k(){var a=B;return a.loader={load:j,i:0},a}var l=b.documentElement,m=a.setTimeout,n=b.getElementsByTagName("script")[0],o={}.toString,p=[],q=0,r="MozAppearance"in l.style,s=r&&!!b.createRange().compareNode,t=s?l:n.parentNode,l=a.opera&&"[object Opera]"==o.call(a.opera),l=!!b.attachEvent&&!l,u=r?"object":l?"script":"img",v=l?"script":u,w=Array.isArray||function(a){return"[object Array]"==o.call(a)},x=[],y={},z={timeout:function(a,b){return b.length&&(a.timeout=b[0]),a}},A,B;B=function(a){function b(a){var a=a.split("!"),b=x.length,c=a.pop(),d=a.length,c={url:c,origUrl:c,prefixes:a},e,f,g;for(f=0;f<d;f++)g=a[f].split("="),(e=z[g.shift()])&&(c=e(c,g));for(f=0;f<b;f++)c=x[f](c);return c}function g(a,e,f,g,h){var i=b(a),j=i.autoCallback;i.url.split(".").pop().split("?").shift(),i.bypass||(e&&(e=d(e)?e:e[a]||e[g]||e[a.split("/").pop().split("?")[0]]),i.instead?i.instead(a,e,f,g,h):(y[i.url]?i.noexec=!0:y[i.url]=1,f.load(i.url,i.forceCSS||!i.forceJS&&"css"==i.url.split(".").pop().split("?").shift()?"c":c,i.noexec,i.attrs,i.timeout),(d(e)||d(j))&&f.load(function(){k(),e&&e(i.origUrl,h,g),j&&j(i.origUrl,h,g),y[i.url]=2})))}function h(a,b){function c(a,c){if(a){if(e(a))c||(j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}),g(a,j,b,0,h);else if(Object(a)===a)for(n in m=function(){var b=0,c;for(c in a)a.hasOwnProperty(c)&&b++;return b}(),a)a.hasOwnProperty(n)&&(!c&&!--m&&(d(j)?j=function(){var a=[].slice.call(arguments);k.apply(this,a),l()}:j[n]=function(a){return function(){var b=[].slice.call(arguments);a&&a.apply(this,b),l()}}(k[n])),g(a[n],j,b,n,h))}else!c&&l()}var h=!!a.test,i=a.load||a.both,j=a.callback||f,k=j,l=a.complete||f,m,n;c(h?a.yep:a.nope,!!i),i&&c(i)}var i,j,l=this.yepnope.loader;if(e(a))g(a,0,l,0);else if(w(a))for(i=0;i<a.length;i++)j=a[i],e(j)?g(j,0,l,0):w(j)?B(j):Object(j)===j&&h(j,l);else Object(a)===a&&h(a,l)},B.addPrefix=function(a,b){z[a]=b},B.addFilter=function(a){x.push(a)},B.errorTimeout=1e4,null==b.readyState&&b.addEventListener&&(b.readyState="loading",b.addEventListener("DOMContentLoaded",A=function(){b.removeEventListener("DOMContentLoaded",A,0),b.readyState="complete"},0)),a.yepnope=k(),a.yepnope.executeStack=h,a.yepnope.injectJs=function(a,c,d,e,i,j){var k=b.createElement("script"),l,o,e=e||B.errorTimeout;k.src=a;for(o in d)k.setAttribute(o,d[o]);c=j?h:c||f,k.onreadystatechange=k.onload=function(){!l&&g(k.readyState)&&(l=1,c(),k.onload=k.onreadystatechange=null)},m(function(){l||(l=1,c(1))},e),i?k.onload():n.parentNode.insertBefore(k,n)},a.yepnope.injectCss=function(a,c,d,e,g,i){var e=b.createElement("link"),j,c=i?h:c||f;e.href=a,e.rel="stylesheet",e.type="text/css";for(j in d)e.setAttribute(j,d[j]);g||(n.parentNode.insertBefore(e,n),m(c,0))}}(this,document),Modernizr.load=function(){yepnope.apply(window,[].slice.call(arguments,0))};

if (!Modernizr.inputtypes.date) {
	$("input[type='date']").datepicker();
	$("input[type='date']").datepicker("option", "dateFormat", "yy/mm/dd");
}

String.prototype.format_date = function() {
    var date_parts = this.substr(0, 10).match(/(\d+)/g);
    //return date_parts[2] + '/' + date_parts[1] + '/' + date_parts[0];
    return date_parts[0] + '/' + date_parts[1] + '/' + date_parts[2];
};

String.prototype.format_date_time = function() {
    
    return this.format_date() + ' ' + this.substr(11, 8);
};

// For todays date;
Date.prototype.today = function () { 
    return ((this.getDate() < 10)?"0":"") + this.getDate() +"/"+(((this.getMonth()+1) < 10)?"0":"") + (this.getMonth()+1) +"/"+ this.getFullYear();
}

// For the time now
Date.prototype.timeNow = function () {
     return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
}

function get_datepicker(datepicker_obj)
{
    if (!Modernizr.inputtypes.date) {

        if (datepicker_obj.datepicker("getDate")) {
            return datepicker_obj.datepicker("getDate").toISOString().substr(0, 10);
        }
    }
    else {
        return datepicker_obj.val();
    }
    return '';
}

/*function set_datepicker(datepicker_obj, json_value)
{
    console.log('1');
	var value = json_value.substr(0, 10);
	if (!Modernizr.inputtypes.date)
	{
        console.log('2');
		var dateParts = value.match(/(\d+)/g);
		realDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // months are 0-based!
		datepicker_obj.datepicker('setDate', realDate);
	}
	else
	{
        console.log('3');
		datepicker_obj.val(value);
	}
}

function get_datepicker(datepicker_obj)
{

    var date_picker = $(datepicker_obj).val();
    var data = '';

    if(date_picker != null && date_picker != ""){
        data = date_picker.substring(6, 10)+'-';
        data += date_picker.substring(3, 5)+'-';
        data += date_picker.substring(0, 2);
    }

    if (!Modernizr.inputtypes.date) {
        return data;
    }
    else {
        return $(datepicker_obj).val();
    }
    
    return '';
}*/

function set_datepicker(datepicker_obj, json_value)
{

    if (!Modernizr.inputtypes.date)
    {
        $(datepicker_obj).mask("99/99/9999");
        $(datepicker_obj).datepicker();        
    }

    if ((typeof json_value == 'undefined') || (!json_value) || (json_value == '0000-00-00' || json_value == '0000/00/00') || (json_value == '00-00-0000' || json_value == '00/00/0000')) {  
        $(datepicker_obj).val('0000-00-00');
        $(datepicker_obj).datepicker('setDate', null);
        return;
    }
    
    var value = json_value.substr(0, 10);
    if (!Modernizr.inputtypes.date)
    {
        var dateParts = value.match(/(\d+)/g);

        //if(dateParts[0] != '' && dateParts[1] != '' && dateParts[2] != '' && parseInt(dateParts[0]) > 0 && parseInt(dateParts[1]) > 0 && parseInt(dateParts[2]) > 0){
            console.log(parseInt(dateParts[0]));
            realDate = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]); // months are 0-based!
            $(datepicker_obj).datepicker('setDate', realDate);
        //}
    }
    else
    {
        $(datepicker_obj).val(value);
    }

}


// number
input_number_keypress = function(e) {
	var a = [];
    var k = e.which;
    
    // verifica setas
    if ((e.keyCode >= 37) && (e.keyCode <= 40)) { return true; }
    // verifica o tab
    if (e.keyCode == 9) { return true; }
    // verifica backspace e del
    if ((e.keyCode == 8) || (e.keyCode == 46) || (e.keyCode == 63272)) { return true; }

    for (i = 48; i < 58; i++)
        a.push(i);
    
    if (!(a.indexOf(k)>=0)) {
    	if (k != ('.').charCodeAt(0)) {
        	e.preventDefault();
        }
        else if (e.target.value.indexOf('.') >= 0) {
        	e.preventDefault();
        }
    }
}
$('.input_number').unbind('keypress');
$('.input_number').keypress(input_number_keypress);

// integer
input_integer_keypress = function(e) {
	var a = [];
    var k = e.which;

    // verifica setas
    if ((e.keyCode >= 37) && (e.keyCode <= 40)) { return true; }
    // verifica o tab
    if (e.keyCode == 9) { return true; }
    // verifica backspace e del
    if ((e.keyCode == 8) || (e.keyCode == 46) || (e.keyCode == 63272)) { return true; }
    
    for (i = 48; i < 58; i++)
        a.push(i);
    
    if (!(a.indexOf(k)>=0)) {
        e.preventDefault();
    }
}
$('.input_integer').unbind('keypress');
$('.input_integer').keypress(input_integer_keypress);

String.prototype.format_time = function() {
    return this.substr(0, 5);
};

input_time_keypress = function(e) {
    var regex = [   "[0-2]",
                    "[0-9]",
                    ":",
                    "[0-6]",
                    "[0-9]" ];

    var str = $(this).val() + String.fromCharCode(e.which),
    b = true;
    for (var i = 0; i < str.length; i++) {
        if (!new RegExp("^" + regex[i] + "$").test(str[i])) {
            b = false;
        }
    }

    // verifica setas
    if ((e.keyCode >= 37) && (e.keyCode <= 40)) { b = true; }
    // verifica o tab
    if (e.keyCode == 9) { b = true; }
    // verifica backspace e del
    if ((e.keyCode == 8) || (e.keyCode == 46) || (e.keyCode == 63272)) { b = true; }

    return b;
}

if (!Modernizr.inputtypes.time) {
    $("input[type='time']").keypress(input_time_keypress);
}

String.prototype.format_number = function(decimais) {
    return parseFloat(this).toFixed(decimais).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
};

Number.prototype.format_number = function(decimais) {
    return this.toString().format_number(decimais);
}

// Fix
// Select2 in Tabs of Bootstap Modal doing AJAX Call , not working as expected #1436
// https://github.com/ivaynberg/select2/issues/1436
$.fn.modal.Constructor.prototype.enforceFocus = function() {};