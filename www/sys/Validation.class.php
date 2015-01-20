<?php
namespace Sys;

class Validation
{
    const VALID_NOTICE = -2;
    const VALID_ERR = -1;
    
    const VALID_ERR_FIELD = 0;
    const VALID_ERR_NOT_EXISTS = 1;

	public $messages;

    function __construct()
    {
    	$this->messages = array();
    }

    public function add()
    {
    	$validation_message = null; 
    	$code = null;
    	$message = null;

    	if (func_num_args() == 1)
    	{
    		$validation_message = func_get_arg(0);
    	}

    	if (func_num_args() == 2)
    	{
    		$code = (string)func_get_arg(0);
    		$message = (string)func_get_arg(1);

    		if (!is_null($code) && !is_null($message))
	    	{
	    		$validation_message = new ValidationMessage($code, $message);
	    	}
    	}
    	
    	if (!is_null($validation_message))
    	{
    		$this->messages[] = $validation_message;
    	}
    }

    public function isValid()
    {
        return (sizeof($this->messages) == 0);
    }

    public function getJSON()
    {
    	return json_encode($this->messages);
    }
}

class ValidationMessage
{
	public $code;
	public $message;
	public $ref;

	function __construct($code, $message, $ref=null)
    {
    	$this->code = $code;
    	$this->message = $message;
    	$this->ref = $ref;
    }
}