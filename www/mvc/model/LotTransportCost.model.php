<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class LotTransportCost_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $lot_transport_id;
    public $travel_cost_id;
    public $value;
    
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

        if (!$this->travel_cost_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the travel cost');
        }
        
        return $validation;
    }
    
    function save($lot_transport_id=null, array $costs=null, array $costs_route=null)
    {
        // se os parmâmetros da função não foram informados, então é um insert normal,
        // onde os valores dos campos irão vir do proprio objeto
        if (is_null($lot_transport_id)) {
            if (!$this->exists()) {
                return $this->insert();
            }
            else {
                return $this->update();
            }
        }
        // senão, é um insert em lote, onde é verificado se já existe algum registro para
        // o tipo de custo para determinado lote, se existir atualiza, senão insere, com base
        // nos parametros informados na chamada da função
        else if (!is_null($lot_transport_id)) {
            if (!is_null($costs) && (sizeof($costs) > 0)) {
                foreach ($costs as $key => $item) {
                    $travel_cost_id = (int)$item['id'];
                    $value = (float)$item['value'];

                    $lot_transport_cost_model = new self;
                
                    $lot_transport_cost_model->lot_transport_id = $lot_transport_id;
                    $lot_transport_cost_model->travel_cost_id = $travel_cost_id;
                    $lot_transport_cost_model->value = $value;

                    $lot_transport_cost_model->id = $lot_transport_cost_model->exists_lot_transport_travel_cost($lot_transport_id, $travel_cost_id);

                    $lot_transport_cost_model->save();
                }
            }
            if (!is_null($costs_route) && (sizeof($costs_route) > 0)) {
                foreach ($costs_route as $key => $item) {
                    $travel_plan_id = (int)$item['id'];
                    $value = (float)$item['cost_value'];
                    $handle_in_out = (float)$item['cost_handle_in_out'];

                    $travel_plan_model = $this->LoadModel('TravelPlan', true);
                    $travel_plan_model->populate($travel_plan_id);
                    $travel_plan_model->cost_value = $value;
                    $travel_plan_model->cost_handle_in_out = $handle_in_out;
                    $travel_plan_model->save();
                }
            }
        }
    }
    
    function exists()
    {
        if (is_null($this->id)) {
            $this->id = 0;
        }
        $query = DB::query('SELECT id FROM lot_transport_cost WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query)) {
            return true;
        }
        return false;
    }

    function exists_lot_transport_travel_cost($lot_transport_id, $travel_cost_id)
    {
        $query = DB::query('SELECT id FROM lot_transport_cost
                            WHERE lot_transport_id = ?
                            AND travel_cost_id = ?
                            AND excluido = \'N\' ', array($lot_transport_id, $travel_cost_id));
        
        if (DB::has_rows($query)) {
            return $query[0]['id'];
        }
        return 0;
    }
    
    function insert()
    {
        $validation = $this->validation();

        if ($validation->isValid()) {
            $sql = 'INSERT INTO lot_transport_cost (
                        lot_transport_id,
                        travel_cost_id,
                        value
                    ) VALUES (
                        ?, ?, ?
                    ) ';
            
            $params[] = $this->lot_transport_id;
            $params[] = $this->travel_cost_id;
            $params[] = $this->value;
            
            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();
            return $this;
        }
        
        return $validation;
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
                            lot_transport_cost
                        SET
                            value = ?
                        WHERE
                            id = ?
                            AND lot_transport_id = ?
                            AND travel_cost_id = ?
                ';

                // set
                $params[] = $this->value;
                // where
                $params[] = $this->id;
                $params[] = $this->lot_transport_id;
                $params[] = $this->travel_cost_id;

                $query = DB::exec($sql, $params);
                //print_r($params);
                return $this;
            }
        }
        
        return $valid;
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else {
            $sql = 'UPDATE lot_transport_cost SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));
            $this->excluido = 'S';

            return $this;
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
                    lot_transport_id,
                    travel_cost_id,
                    value
                FROM
                    lot_transport_cost
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query)) {
                $this->fill($query[0]);
                return $this;
            }
        }

        return $validation;
    }

    function fill($row_query)
    {
        if ($row_query) {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->lot_transport_id = (int)$row_query['lot_transport_id'];
            $this->travel_cost_id = (int)$row_query['travel_cost_id'];
            $this->value = (float)$row_query['value'];
        }
    }
    
    function get_by_lot_transport($lot_transport_id)
    {
        
        $sql = 'SELECT
                    ltc.id AS lot_transport_cost_id,
                    ltc.lot_transport_id,
                    ltc.travel_cost_id,
                    travel_cost.name AS travel_cost_name,
                    travel_cost.type AS travel_cost_type,
                    ltc.value

                FROM lot_transport_cost ltc

                INNER JOIN travel_cost ON (travel_cost.id = ltc.travel_cost_id)

                WHERE
                    ltc.lot_transport_id = ?
                    AND ltc.excluido = \'N\'

                ORDER BY travel_cost.name ';

        $params[] = (int)$lot_transport_id;

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}