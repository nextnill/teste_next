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
        $_SESSION[SPRE.'po_ano_filtro'] = null;
        $_SESSION[SPRE.'po_mes_filtro'] = null;
        $_SESSION[SPRE.'po_block_type'] = null;
        $_SESSION[SPRE.'po_quarry_id'] = null;
        $_SESSION[SPRE.'ic_ano_filtro'] = null;
        $_SESSION[SPRE.'ic_mes_filtro'] = null;
        $_SESSION[SPRE.'ic_client_id'] = null;
        $_SESSION[SPRE.'bl_block_number'] = null;
        $_SESSION[SPRE.'bl_client_id'] = null;
        $_SESSION[SPRE.'lo_ano_filtro'] = null;
        $_SESSION[SPRE.'lo_mes_filtro'] = null;
        $_SESSION[SPRE.'lo_client_id'] = null;
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