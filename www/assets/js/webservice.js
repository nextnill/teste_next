function ajaxError(jqXHR, textStatus, errorThrown) {
    var msg = '';

    var decodeEntities = (function() {
	  // this prevents any overhead from creating the object each time
	  var element = document.createElement('div');

	  function decodeHTMLEntities (str) {
	    if (str && typeof str === 'string') {
	      // strip script/html tags
	      str = str.replace(/<script[^>]*>([\S\s]*?)<\/script>/gmi, '');
	      str = str.replace(/<\/?\w(?:[^"'>]|"[^"]*"|'[^']*')*>/gmi, '');
	      element.innerHTML = str;
	      str = element.textContent;
	      element.textContent = '';
	    }

	    return str;
	  }

	  return decodeHTMLEntities;
	})();

    msg += 'Status: ' + textStatus;
    msg += '<br>';
    msg += errorThrown;
    msg += '<br>';
    msg += 'Response Text: <pre>' + decodeEntities(jqXHR.responseText) + '</pre>';
    
    alert_modal('Ajax Error', msg);
}

// object
var WS = function()
{
	WS.TYPE = {
        POST: "post",
        GET: "get"
    };
    
	WS.request = function(route, type, data, func_response) {
		if ((typeof data == 'undefined') || (!data)) {
			data = [];
		}

		$.ajaxSetup({ cache: false });
		
		$.ajax({
            error: ajaxError,
            type: type,
            url: APP_URI + route,
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response_validation(response)) {
                    func_response(response);
                }
            }
        });
	}

	WS.get = function(route, data, func_response) {
		WS.request(route, WS.TYPE.GET, data, func_response);
	}

	WS.post = function(route, data, func_response) {
		WS.request(route, WS.TYPE.POST, data, func_response);
	}
	
}

new WS;