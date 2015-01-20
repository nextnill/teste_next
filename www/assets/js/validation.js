function valid_onunload(active) {
    if ((active) && (active == true)) {
        window.onbeforeunload = valid_onunload_alert;
    }
    else {
        window.onbeforeunload = null;
    }
}
function valid_onunload_alert() {
    return 'Data was not saved.'; //'\n'
}

// object
var Validation = function(id)
{

    Validation.CODES = {
        ERR: -1,
        ERR_FIELD: 0,
        ERR_NOT_EXISTS: 1
    };

    this.messages = [];

    this.add = function()
    {
        var args = Array.prototype.slice.call(arguments, 0);

        var validation_message = null;

        var code = Validation.CODES.ERR;
        var message = '';

        if (args.length == 1)
        {
            validation_message = args[0];
        }

        if (args.length == 2)
        {
            code = args[0];
            message = args[1];

            if (code && message)
            {
                validation_message = new ValidationMessage(code, message);
            }
        }
        
        if (validation_message)
        {
            this.messages.push(validation_message);
        }
    }

    this.is_valid = function()
    {
        return (this.messages.length == 0);
    }

}

var ValidationMessage = function(code, message, ref)
{
    this.code = null;
    this.message = null;
    this.ref = null;

    this.code = code;
    this.message = message;
    
    if (ref)
        this.ref = ref;
    else
        this.ref = '';
}