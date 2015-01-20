<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class TravelPlan_Model extends \Sys\Model {
    
    const TRAVEL_PLAN_STATUS_PENDING = 0;
    const TRAVEL_PLAN_STATUS_STARTED = 1;
    const TRAVEL_PLAN_STATUS_COMPLETED = 2;
    
    public $id;
    public $excluido;

    public $date_record;
    public $lot_transport_id;
    public $travel_route_id;
    public $status;
    public $cost_value;
    public $cost_handle_in_out;

    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->lot_transport_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the lot transport');
        }
        
        if (!$this->travel_route_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the route');
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
        $query = DB::query('SELECT id FROM travel_plan WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query)) {
            return true;
        }
        return false;
    }

    /*
    function exists_pointing_travel()
    {
        if (is_null($this->id))
        {
            $this->id = 0;
        }
        $query = DB::query('SELECT id FROM travel_plan_item WHERE lot_transport_id = ? AND excluido = \'N\' ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }
    */
    
    function insert()
    {
        $validation = $this->validation();

        if ($validation->isValid()) {
            $sql = 'INSERT INTO travel_plan (
                        date_record,
                        lot_transport_id,
                        travel_route_id,
                        status,
                        cost_value,
                        cost_handle_in_out
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?
                    ) ';
            
            $dt_now = new DateTime('now');
            $dt_now = $dt_now->format('Y-m-d H:i:s');
            $params[] = $dt_now;
            $params[] = (int)$this->lot_transport_id;
            $params[] = (int)$this->travel_route_id;
            $params[] = (int)$this->status;
            $params[] = (float)$this->cost_value;
            $params[] = (float)$this->cost_handle_in_out;

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
            if (!$this->exists()) {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
            }
            else {
                $sql = 'UPDATE
                            travel_plan
                        SET
                            lot_transport_id = ?,
                            travel_route_id = ?,
                            status = ?,
                            cost_value = ?,
                            cost_handle_in_out = ?
                        WHERE
                            id = ? ';
                // set
                $params[] = (int)$this->lot_transport_id;
                $params[] = (int)$this->travel_route_id;
                $params[] = (int)$this->status;
                $params[] = (float)$this->cost_value;
                $params[] = (float)$this->cost_handle_in_out;
                // where
                $params[] = (int)$this->id;

                $query = DB::exec($sql, $params);

                return $this;
            }
        }
        
        return array('validation' => $validation);
    }

    function import_template($lot_transport_id, $travel_plan_template_id)
    {
        $travel_plan_template_model = $this->LoadModel('TravelPlanTemplate', true);
        $travel_plan_template_model->populate($travel_plan_template_id);

        $ret = array();
        if (!is_null($travel_plan_template_model->items)) {
            foreach ($travel_plan_template_model->items as $key => $template_item) {
                $travel_plan_model = new self;
                $travel_plan_model->lot_transport_id = $lot_transport_id;
                $travel_plan_model->travel_route_id = $template_item['travel_route_id'];
                $ret[] = $travel_plan_model->save();
            }
        }

        return $ret;
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else {
            $sql = 'UPDATE travel_plan SET excluido = ? WHERE id = ? ';
            
            $params[] = 'S';
            $params[] = $this->id;

            $query = DB::exec($sql, $params);
            
            return $this->id;
        }
        
        return $validation;
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
            $query = DB::query(
                'SELECT
                    id,
                    excluido,
                    date_record,
                    lot_transport_id,
                    travel_route_id,
                    status,
                    cost_value,
                    cost_handle_in_out
                FROM
                    travel_plan
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query)) {
                $this->fill($query[0]);
                return $this->id;
            }
        }

        return $validation;
    }

    function fill($row_query)
    {
        if ($row_query) {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->date_record = (string)$row_query['date_record'];
            $this->lot_transport_id = (int)$row_query['lot_transport_id'];
            $this->travel_route_id = (int)$row_query['travel_route_id'];
            $this->status = (int)$row_query['status'];
            $this->cost_value = (float)$row_query['cost_value'];
            $this->cost_handle_in_out = (float)$row_query['cost_handle_in_out'];
        }
    }

    function get_by_lot_transport($lot_transport_id)
    {
        $sql = 'SELECT
                    travel_plan.id,
                    travel_plan.excluido,
                    travel_plan.date_record,
                    travel_plan.lot_transport_id,
                    travel_plan.travel_route_id,
                    travel_plan.status,
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
                    travel_route.shipping_time,
                    travel_plan.cost_value,
                    travel_plan.cost_handle_in_out,
                    (SELECT last_tp.cost_value FROM travel_plan AS last_tp
                        WHERE last_tp.travel_route_id = travel_plan.travel_route_id
                            AND last_tp.lot_transport_id != travel_plan.lot_transport_id
                            AND last_tp.cost_value > 0
                        ORDER BY last_tp.id DESC LIMIT 0,1) AS last_cost_value
                FROM travel_plan
                INNER JOIN travel_route ON (travel_route.id = travel_plan.travel_route_id)
                LEFT JOIN quarry AS start_quarry ON (start_quarry.id = travel_route.start_quarry_id)
                LEFT JOIN terminal AS start_terminal ON (start_terminal.id = travel_route.start_terminal_id)
                LEFT JOIN quarry AS end_quarry ON (end_quarry.id = travel_route.end_quarry_id)
                LEFT JOIN terminal AS end_terminal ON (end_terminal.id = travel_route.end_terminal_id)
                WHERE travel_plan.lot_transport_id = ?
                AND travel_plan.excluido = \'N\'
                ORDER BY travel_plan.id
                ';

        $params[] = (int)$lot_transport_id;

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}