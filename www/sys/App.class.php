<?php
namespace Sys;

define('VERSION', '15.05.04.01'); //yy.mm.dd.[nn] - nn = número da versão do dia inicio

class App
{
    function __construct()
    {
        $this->Start();
    }

    function Start()
    {
        require FRAMEWORK_FOLDER . 'sys/MVC.class.php';
        require FRAMEWORK_FOLDER . 'sys/Validation.class.php';
        require FRAMEWORK_FOLDER . 'sys/DB.class.php';
        require FRAMEWORK_FOLDER . 'sys/Session.class.php';
        require FRAMEWORK_FOLDER . 'sys/Util.class.php';
        
        error_reporting(E_ERROR);
        @session_start();
        
        $mvc = new MVC();
    }

    static function LoadRouteMapConfig()
    {
        require FRAMEWORK_FOLDER . 'sys/RouteMap.class.php';
        if (file_exists('config/RouteMap.config.php')) {
            require 'config/RouteMap.config.php';
        }
    }

    static function LoadPermissionsConfig()
    {
        require FRAMEWORK_FOLDER . 'sys/Permissions.class.php';
        if (file_exists('config/Permissions.config.php')) {
            require 'config/Permissions.config.php';
        }
    }

    static function LoadLoginValidationConfig()
    {
        if (file_exists('config/LoginValidation.config.php')) {
            require 'config/LoginValidation.config.php';
        }
    }

}