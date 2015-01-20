<?php

class Home_Controller extends \Sys\Controller
{

    function index_action($params)
    {
        $this->RenderView(array('masterpage'), array('home'));
    }

}
