<?php

use \Sys\Validation;
use \Sys\Session;

class Login_Controller extends \Sys\Controller
{
    private $session;

    function enter_action($params)
    {
        $this->RenderView('masterpage', 'login/enter');
    }

    function enter_json($params)
    {
        $name = (string)$this->ReadPost('user');
        $password = (string)$this->ReadPost('pass');

        $user_model = $this->LoadModel('User', true);
        $ret = $user_model->validate_login($name, $password);

        if (isset($ret['validation'])) {
            $this->print_json($ret);
        }
        else if (isset($ret['id']) && ((int)$ret['id'] > 0)) {
            $session = new Session($ret);
            $session->register($ret);
            $this->print_json($ret);
        }
        
    }

    function logout_action($params)
    {
        $session = new Session();
        $session->destroy();
    	header('Location: ' . APP_URI . 'login/');
    }

}