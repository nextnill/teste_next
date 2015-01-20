var Login = function()
{
    // attr
    Login.user = $('#user');
    Login.password = $('#password');
    Login.btn_enter = $('#btn_enter');

    Login.alert = $('#signin_alert');

    // methods
    this.enter = function()
    {
        $.ajax({
            error: ajaxError,
            type: "POST",
            url: "<?= APP_URI ?>login/enter/json/",
            data: {
                user: Login.user.val(),
                pass: Login.password.val()
            },
            dataType: 'json',
            success: function (response) {
                if (response_validation(response)) {
                    if (response.id > 0) {
                        loading.show();
                        window.location = '<?= APP_URI ?>';
                    }
                }
            }
        });
    }

    //events
    Login.btn_enter_click = function() {
        login.enter();
    }

}

// init
var login = new Login();