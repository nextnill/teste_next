<?php

class Permissions_Config extends \Sys\Permissions
{
    function Register()
    {
        
        parent::$permissions = array(

            'user' => array('name' => 'Users', 'description' => ''),
            'quarry' => array('name' => 'Quarries', 'description' => ''),
            'product' => array('name' => 'Products', 'description' => ''),
            'quality' => array('name' => 'Qualities', 'description' => ''),
            'block' => array('name' => 'Blocks', 'description' => 'Change the characteristics of the blocks produced'),
            'defect' => array('name' => 'Defects', 'description' => ''),
            'client' => array('name' => 'Clients', 'description' => ''),
            'terminal' => array('name' => 'Terminals', 'description' => ''),
            'agency' => array('name' => 'Agencies', 'description' => ''),
            'production_order' => array('name' => 'Production Orders', 'description' => ''),
            'sobracolumay' => array('name' => 'Sobracolumay', 'description' => ''),
            'schedule_inspection' => array('name' => 'Schedule Inspection', 'description' => ''),
            'inspection' => array('name' => 'Inspection of Blocks', 'description' => ''),
            'reinspection' => array('name' => 'Reinspection of Blocks', 'description' => ''),
            'travel_cost' => array('name' => 'Travel Cost', 'description' => ''),
            'travel_route' => array('name' => 'Travel Route', 'description' => ''),
            'lot' => array('name' => 'Lot Transport', 'description' => ''),
            'travel_plan' => array('name' => 'Travel Plan', 'description' => ''),
            'pointing_travel' => array('name' => 'Pointing Travel', 'description' => ''),
            'poblo' => array('name' => 'Poblo', 'description' => 'Position of the blocks'),
            'truck_carrier' => array('name' => 'Truck Carrier', 'description' => ''),
            
            
        );        
    }
}