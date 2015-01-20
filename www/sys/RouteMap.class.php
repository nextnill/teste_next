<?php
namespace Sys;

class RouteMap
{
    static public $routes = array();

    function __construct()
    {
        if (method_exists($this, 'Register')) { 
            $this->Register();
        }
    }
    
    function Process()
    {
        $uri = $this->TrataUri($_SERVER['REQUEST_URI']);

        $route_key = $this->ValidRoutes($uri, self::$routes);

        if (is_null($route_key)) {
            // dispatch exception
        }
        
        $route_key = (empty($route_key) ? '/' : $route_key);
        $controller_class = self::$routes[$route_key][0];

        $action = isset(self::$routes[$route_key][1]) ? self::$routes[$route_key][1] : 'IndexAction';
        $params = $this->GetParams($uri, $route_key);

        $permission_key = isset(self::$routes[$route_key][2]) ? self::$routes[$route_key][2] : null;

        return array('controller_class' => $controller_class, 'action' => $action, 'permission_key' => $permission_key, 'params' => $params);
    }

    private function ValidRoutes($uri)
    {
        foreach (array_reverse(self::$routes) as $key => $value) {
            
            /*
            $http_pos = strpos(APP_URI, 'http://');
            if ($http_pos !== false && $http_pos == 0) {
                $http_pos = 7;
            }
            else {
                $http_pos = 0;
            }
            */

            //$pos = strpos($uri, $this->TrataUri(substr(APP_URI, $http_pos) . $key));
            $pos = strpos($uri, $this->TrataUri(APP_URI . $key));

            //print_r(array("http_pos" => $http_pos));
            //exit;

            //print_r(array("key" => $key, "value" => $value, "uri" => $uri, "pos" => $pos, "trata_uri_param" => APP_URI . $key, "trata_uri" => $this->TrataUri(substr(APP_URI, $http_pos) . $key)));

            if ($pos !== false && $pos == 0) {
                //return ($http_pos !== false && $http_pos == 0 ? 'http://' : '') . $this->TrataUri($key);
                return $this->TrataUri($key);
            }
        }

        //exit;
    }

    private function GetParams($uri, $route_key)
    {   
        $pos = strpos($uri, $route_key) + strlen($route_key);
        $params = substr($uri, $pos);
        $params = ltrim(rtrim($params, '/'), '/');
        $params = explode('/', $params);
        
        if (sizeof($params) == 1 && empty($params[0]))
            $params = array_shift ($params);

        return $params;
    }

    private function TrataUri($uri)
    {
        //print_r(array("uri" => $uri, "return" => str_replace('//', '/', '/' . ltrim(rtrim($uri, '/') . '/', '/'))));

        return str_replace('//', '/', '/' . ltrim(rtrim($uri, '/') . '/', '/'));
    }
}