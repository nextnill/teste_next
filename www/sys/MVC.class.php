<?php
namespace Sys;

use \Sys\Session;
use \Sys\Validation;

class MVC
{
    function __construct()
    {
        $this->Start();
    }

    function Start()
    {
        require FRAMEWORK_FOLDER . 'sys/Model.class.php';
        require FRAMEWORK_FOLDER . 'sys/Controller.class.php';

        App::LoadRouteMapConfig();
        App::LoadPermissionsConfig();
        App::LoadLoginValidationConfig();

        $permissions = new \Permissions_Config();
        
        $route = new \RouteMap_Config();
        $request = $route->Process();

        $this->ExecuteController($request['controller_class'], $request['action'], $request['permission_key'], $request['params']);
    }

    function ExecuteController($controller_class, $action, $permission_key, $params)
    {
        $validation = new Validation();

        if (!is_null($permission_key)) {
            $allowed = false;
            $session = new Session();
            $user = $session->get_user();
            foreach ($user['permissions'] as $key => $permission) {
                if ($permission == $permission_key) {
                    $allowed = true;
                }
            }
            if (!$allowed) {
                $validation->add(Validation::VALID_ERR, 'Access denied');
            }
        }
        if (!$validation->isValid()) {
            if (strpos($action, 'json') !== false) {
                header('Content-type: application/json; charset=utf-8');
                echo json_encode(array('validation' => $validation));
            }
            else {
                print('Access denied');
            }
            exit;
        }

        $file_name = 'mvc/controller/' . $controller_class . '.controller.php';
        if (file_exists($file_name)) {
            require_once($file_name);
            $controller_class = $controller_class . '_Controller';
            if (class_exists($controller_class)) {
                new $controller_class($action, $params);
            }
        } else {
            print_r('Controller não encontrado: ' . $file_name);
            exit;
        }        
    }

}