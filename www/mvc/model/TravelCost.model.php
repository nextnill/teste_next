<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class TravelCost_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    public $type;
    public $cost_per_tonne;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (strlen($this->name) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the name');
        }

        if ($this->type <= 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the type');
        }
        
        return $validation;
    }
    
    function save()
    {
        if (!$this->exists())
        {
            return $this->insert();
        }
        else
        {
            return $this->update();
        }
    }
    
    function exists()
    {
        if (is_null($this->id))
        {
            $this->id = 0;
        }
        $query = DB::query('SELECT id FROM travel_cost WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }
    
    function insert()
    {
        $validation = $this->validation();

        if ($validation->isValid())
        {
            $sql = 'INSERT INTO travel_cost (name, type, cost_per_tonne) VALUES (?, ?, ?) ';
            $query = DB::exec($sql, array($this->name, $this->type, $this->cost_per_tonne));

            $this->id = DB::last_insert_id();
            return $this;
        }
        
        return $validation;
    }
    
    function update()
    {
        $validation = $this->validation();
        
        if ($validation->isValid())
        {
            if (!$this->exists())
            {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
            }
            else
            {
                $sql = '
                    UPDATE
                        travel_cost
                    SET
                        name = ?,
                        type = ?,
                        cost_per_tonne = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    $this->type,
                    $this->cost_per_tonne,
                    // where
                    $this->id
                ));

                return $this;
            }
        }
        
        return $validation;
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else
        {
            $sql = 'UPDATE travel_cost SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));
            
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
        
        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else
        {
            $query = DB::query(
                'SELECT
                    id,
                    excluido,
                    name,
                    type,
                    cost_per_tonne
                FROM
                    travel_cost
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);
                return $this->id;
            }
        }

        return $validation;
    }

    function fill($row_query)
    {
        if ($row_query)
        {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->name = (string)$row_query['name'];
            $this->cost_per_tonne = $row_query['cost_per_tonne'];
            $this->type = (int)$row_query['type'];
        }
    }
    
    function get_list($excluido=false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');

        $sql = "SELECT  
                    id, 
                    excluido, 
                    name, 
                    type, 
                    cost_per_tonne 
                FROM travel_cost 
                WHERE excluido = ? 
                ORDER BY name ";
        
        $query = DB::query($sql, array($excluido));
        
        return $query;
    }
    
}