<?php
namespace Sys;

class Session
{
    const STT_ACTIVE = 0;
    const STT_LOGOUT = 1;
    const STT_EXPIRED = 2;    

    function __construct()
    {
        
    }

    function register($params)
    {
    	$_SESSION[SPRE.'start'] = time();
    	$_SESSION[SPRE.'last_activity'] = time();
        $_SESSION[SPRE.'user'] = serialize($params);
        $_SESSION[SPRE.'status'] = self::STT_ACTIVE;
    }
    
    function destroy($status=null)
    {
    	$_SESSION[SPRE.'start'] = null;
    	$_SESSION[SPRE.'last_activity'] = null;
        $_SESSION[SPRE.'user'] = null;
        $_SESSION[SPRE.'status'] = (!is_null($status) ? $status : self::STT_LOGOUT);
    }

    function refresh($user)
    {
        $_SESSION[SPRE.'user'] = serialize($user);
    }

    function get_user()
    {
        if (isset($_SESSION[SPRE.'user'])) {
            return unserialize($_SESSION[SPRE.'user']);
        }
        return null;
    }

    function validate()
    {
		if (time() - $_SESSION[SPRE.'last_activity'] > 3000) {
			$this->destroy(STT_EXPIRED);
			echo('Sessão Expirada! Por favor, faça novamente o login!');
		}
    }

}