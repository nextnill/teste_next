<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class TravelPlanTemplate_Model extends \Sys\Model {

    public $id;
    public $excluido;
    public $date_record;

    public $description;

    public $items;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();

        if (strlen($this->description) == 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the description');
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
                    travel_plan_template
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

            $sql = 'INSERT INTO travel_plan_template (
                        date_record,
                        description
	                ) VALUES (
	                    ?, ?
	                ) ';
                        
            $dt_now = new DateTime('now');
            $params[] = $dt_now->format('Y-m-d H:i:s');
            $params[] = (string)$this->description;

            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();

            // adiciono rotas (itens) ao template
            if (!is_null($this->items)) {
                foreach ($this->items as $key => $item) {
                    // verifico se é um item que não foi removido
                    if (!isset($item['removed']) || $item['removed'] != 'true') {
                        $travel_plan_template_item_model = $this->LoadModel('TravelPlanTemplateItem', true);
                        $travel_plan_template_item_model->travel_plan_template_id = $this->id;
                        $travel_plan_template_item_model->travel_route_id = $item['travel_route_id'];
                        $travel_plan_template_item_model->save();
                    }
                }
            }

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
	                        travel_plan_template
	                    SET
                            description = ?
	                    WHERE
	                        id = ?
	                    ';
                // set
                $params[] = (string)$this->description;
                // where
                $params[] = (int)$this->id;

                $query = DB::exec($sql, $params);

                // adiciono/removo rotas (itens) ao template
                if (!is_null($this->items)) {
                    foreach ($this->items as $key => $item) {
                        $travel_plan_template_item_model = $this->LoadModel('TravelPlanTemplateItem', true);

                        // se tiver id, popula model
                        if (isset($item['id']) && $item['id'] > 0) {
                            $travel_plan_template_item_model->populate($item['id']);

                            // verifico se é para excluir
                            if (isset($item['removed']) && $item['removed'] == 'true') {
                                $travel_plan_template_item_model->delete();
                            }
                        }
                        // se não tiver id
                        else {
                            // verifico se é um item que não foi removido
                            if (!isset($item['removed']) || $item['removed'] != 'true') {
                                $travel_plan_template_item_model->travel_plan_template_id = $this->id;
                                $travel_plan_template_item_model->travel_route_id = $item['travel_route_id'];
                                $travel_plan_template_item_model->save();
                            }
                        }
                    }
                }
                
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
	                    travel_plan_template
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
                        description
	                FROM
	                    travel_plan_template
	                WHERE
	                    id = ?
                ';
            $query = DB::query($sql, array(
                // where
                $id
            ));
            
            if (DB::has_rows($query)) {
                // campos
                $this->fill($query[0]);
                // itens
                $travel_plan_template_item_model = $this->LoadModel('TravelPlanTemplateItem', true);
                $this->items = $travel_plan_template_item_model->get_by_travel_plan_template($this->id);

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
            $this->description = (string)$row_query['description'];
        }
    }
    
    function get_list($excluido=false)
    {
        $sql = 'SELECT
                    id,
                    excluido,
                    date_record,
                    description
                FROM
                    travel_plan_template
                WHERE
                    excluido = ?
                ORDER BY
                    description
                ';

        $params[] = ($excluido === true ? 'S' : 'N');

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}