<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class TravelPlanTemplateItem_Model extends \Sys\Model {

    public $id;
    public $excluido;
    public $date_record;

    public $travel_plan_template_id;
    public $travel_route_id;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();

        if (!$this->travel_plan_template_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the travel plan template');
        }
        if (!$this->travel_route_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the travel route');
        }
        
        return $validation;
    }
    
    function save()
    {
        if (!$this->exists()) {
            return $this->insert();
        }
        else {
            return $this->update();
        }
    }
    
    function exists()
    {
        if (is_null($this->id)) {
            $this->id = 0;
        }
        $sql = 'SELECT
                    id
                FROM
                    travel_plan_template_item
                WHERE
                    id = ?
                ';
        $query = DB::query($sql, array(
            // where
            $this->id
        ));
        
        if (DB::has_rows($query)) {
            return true;
        }
        return false;
    }
    
    function insert()
    {
        $validation = $this->validation();

        if ($validation->isValid()) {

            $sql = 'INSERT INTO travel_plan_template_item (
                        date_record,
                        travel_plan_template_id,
                        travel_route_id
                    ) VALUES (
                        ?, ?, ?
                    ) ';
                        
            $dt_now = new DateTime('now');
            $params[] = $dt_now->format('Y-m-d H:i:s');
            $params[] = (int)$this->travel_plan_template_id;
            $params[] = (int)$this->travel_route_id;

            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();

            return $this;
        }
        
        return array('validation' => $validation);
    }
    
    function update()
    {
        $validation = $this->validation();
        
        if ($validation->isValid()) {

            $validation = new Validation();
            if (!$this->exists()) {
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
            }
            else
            {
                $sql = 'UPDATE
                            travel_plan_template_item
                        SET
                            travel_plan_template_id = ?,
                            travel_route_id = ?
                        WHERE
                            id = ?
                        ';
                // set
                $params[] = (int)$this->travel_plan_template_id;
                $params[] = (int)$this->travel_route_id;
                // where
                $params[] = (int)$this->id;

                $query = DB::exec($sql, $params);
                
                return $this;
            }
        }
        
        return array('validation' => $validation);
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else {
            $sql = 'UPDATE
                        travel_plan_template_item
                    SET
                        excluido = ?
                    WHERE
                        id = ? 
                    ';
            $query = DB::exec($sql, array(
                // set
                'S',
                // where
                $this->id
            ));

            return $this;
        }
        
        return array('validation' => $validation);
    }
    
    function populate($id)
    {
        $validation = new Validation();
        
        if ($id) {
            $this->id = $id;
        }
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else {
            $sql = 'SELECT
                        id,
                        excluido,
                        date_record,
                        travel_plan_template_id,
                        travel_route_id
                    FROM
                        travel_plan_template_item
                    WHERE
                        id = ?
                ';
            $query = DB::query($sql, array(
                // where
                $id
            ));
            
            if (DB::has_rows($query)) {
                $this->fill($query[0]);
                return $this;
            }
        }

        return array('validation' => $validation);
    }

    function fill($row_query)
    {
        if ($row_query) {
            // padrão
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];
            $this->date_record = (string)$row_query['date_record'];
            // tabela
            $this->travel_plan_template_id = (int)$row_query['travel_plan_template_id'];
            $this->travel_route_id = (int)$row_query['travel_route_id'];
        }
    }
    
    function get_by_travel_plan_template($travel_plan_template_id)
    {
        $sql = 'SELECT
                    travel_plan_template_item.id,
                    travel_plan_template_item.excluido,
                    travel_plan_template_item.date_record,
                    travel_plan_template_item.travel_plan_template_id,
                    travel_plan_template_item.travel_route_id,
                    IF (travel_route.start_quarry_id IS NOT NULL, start_quarry.name, start_terminal.name) AS start_location,
                    IF (travel_route.end_quarry_id IS NOT NULL, end_quarry.name, end_terminal.name) AS end_location,
                    travel_route.start_quarry_id,
                    start_quarry.name AS start_quarry_name,
                    travel_route.start_terminal_id,
                    start_terminal.name AS start_terminal_name,
                    travel_route.end_quarry_id,
                    end_quarry.name AS end_quarry_name,
                    travel_route.end_terminal_id,
                    end_terminal.name AS end_terminal_name,
                    travel_route.shipping_time
                FROM
                    travel_plan_template_item
                    INNER JOIN travel_route ON (travel_route.id = travel_plan_template_item.travel_route_id)
                    LEFT JOIN quarry AS start_quarry ON (start_quarry.id = travel_route.start_quarry_id)
                    LEFT JOIN terminal AS start_terminal ON (start_terminal.id = travel_route.start_terminal_id)
                    LEFT JOIN quarry AS end_quarry ON (end_quarry.id = travel_route.end_quarry_id)
                    LEFT JOIN terminal AS end_terminal ON (end_terminal.id = travel_route.end_terminal_id)
                WHERE
                    travel_plan_template_item.excluido = \'N\'
                    AND travel_plan_template_item.travel_plan_template_id = ?
                ORDER BY
                    travel_plan_template_item.id
                ';

        $params[] = (int)$travel_plan_template_id;

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}