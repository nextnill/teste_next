<?php
namespace Sys;

class Controller
{
    private $session;

    function __construct()
    {
        $action = null;
        $params = array();

        if (func_num_args() >= 1) {
            $action = func_get_arg(0);
        }
        if (func_num_args() >= 2) {
            $params = func_get_arg(1);
        }

        if (!is_null($action)) {
            if (method_exists($this, $action)) { 
                $this->$action($params);
            }
        }

        $user = $this->ActiveUser();
        if (!is_null($user)) {
            // pesquisa usuário
            $user_model = $this->LoadModel('User', true);
            $user_model->populate($user['id']);
            // atualiza sessão
            $this->Session();
            $this->session->refresh((array)$user_model);
        }        
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

    function RenderView($master_page, $view_file, array $data=array())
    {
        $master_pages = array();
        $view_files = array();

        if (!is_array($master_page))
            $master_pages[] = $master_page;
        else
            $master_pages = $master_page;
        
        if (!is_array($view_file))
            $view_files[] = $view_file;
        else
            $view_files = $view_file;

        // MASTER PAGE VIEWS
        $buffer_master_page = '';
        $count_master_page = sizeof($master_pages);
        for ($i = 0; $i < $count_master_page; $i++) { 
            if ($i == 0) {
                $buffer_master_page = $this->RequireView($master_pages[$i], $data);
            }
            else {
                $buffer_master_page = str_replace('<[content]>', $buffer_master_page, $this->RequireView($master_pages[$i], $data));
            }
        }

        // VIEWS
        $buffer_views = '';
        $count_views = sizeof($view_files);
        for ($i = 0; $i < $count_views; $i++) {
            $buffer_views .= $this->RequireView($view_files[$i], $data);
        }

        if (!empty($buffer_master_page))
        {
            $buffer_views = str_replace('<[content]>', $buffer_views, $buffer_master_page);
        }

        print $buffer_views;
    }

    private function RequireView($view_file_required, array $data=array())
    {
        $file_name = 'mvc/view/' . $view_file_required . '.phtml';
        $buffer = '';
        if (file_exists($file_name)) {

            // transforma keys do array data em variáveis
            foreach ($data as $key => $value) {
                $$key = $value;
            }

            ob_start();
            require($file_name);
            $buffer = ob_get_clean();
        }
        else {
            //disparar exceção
            print_r('View não encontrada: ' . $file_name);
            exit;
        }

        // require JS View
        $file_name = 'mvc/view/' . $view_file_required . '.js';
        if (file_exists($file_name)) {
            ob_start();
            $buffer .= '<script type="text/javascript">' . "\n";
            require($file_name);
            $buffer .= ob_get_clean();
            $buffer .= "\n" . '</script>';
        }

        /*
        // require CSS View
        $file_name = 'mvc/view/' . $view_file_required . '.css';
        if (file_exists($file_name)) {
            ob_start();
            $buffer .= '<script type="text/javascript">' . "\n";
            require($file_name);
            $buffer .= ob_get_clean();
            $buffer .= "\n" . '</script>';
        }
        */

        return $buffer;
    }

    function ReadPost($name, $default=null)
    {

        if (isset($_POST[$name])) {
            return $_POST[$name];
        }
        else {
            return $default;
        }
    }

    function ReadGet($name, $default=null)
    {
        if (isset($_GET[$name])) {
            return $_GET[$name];
        }
        else {
            return $default;
        }
    }

    function ReadFiles()
    {
        if (isset($_FILES))
        {
            return Util::FilesArray($_FILES[]);
        }
        return array();
    }

    function print_json($obj)
    {
        header('Content-type: application/json; charset=utf-8');
        print json_encode($obj);
    }
}