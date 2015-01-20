<?php
namespace Sys;

class Permissions
{
    static public $permissions = array();

    function __construct()
    {
        if (method_exists($this, 'Register')) { 
            $this->Register();
        }
    }

}