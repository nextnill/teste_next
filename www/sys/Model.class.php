<?php
namespace Sys;

class Model
{
    private $session;

	function __construct()
    {
    	
    }

    function Session()
    {
        if (is_null($this->session)) {
            $this->session = new Session;
        }
        return $this->session;
    }

    function ActiveUser()
    {
        $session = $this->Session();
        return $session->get_user();
    }

    function SQLActiveQuarries()
    {
        $quarries = '';
        $active_user = $this->ActiveUser();
        if (!is_null($active_user) && isset($active_user['quarries'])) {
            $quarries = implode(',', $active_user['quarries']);
        }

        // evita erros no mysql com a instrução IN
        if (is_null($quarries) || empty($quarries) || (sizeof($quarries) == 0)) {
            $quarries = '-1';
        }

        return $quarries;
    }
    

    function LoadModel($model_class_required, $auto_construct=false)
    {
        $file_name = 'mvc/model/' . $model_class_required . '.model.php';
        if (file_exists($file_name)) {
            require_once($file_name);
            $model_class_required = $model_class_required . '_Model';
            if (class_exists($model_class_required) && $auto_construct) {
                return new $model_class_required();
            }
        } else {
            print_r('Model não encontrado: ' . $file_name);
            exit;
        }
    }

    function field_fill_date($query_date_field)
    {
        return is_null($query_date_field) ? null : date_create((string)$query_date_field);
    }

}