<?php


use \Sys\DB;
use \Sys\Validation;

class Truck_Carrier_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    public $code_trucks;

    
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
        $query = DB::query('SELECT id FROM carrier WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO carrier (
                        name
                    ) VALUES (
                        ?
                    ) ';

            $query = DB::exec($sql, array(
                // values
                $this->name,
            ));

            $this->id = DB::last_insert_id();
            
            $this->save_trucks($this->id, $this->code_trucks);

            return $this->id;
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
                        carrier
                    SET
                        name = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    // where
                    $this->id
                ));

                $this->save_trucks($this->id, $this->code_trucks);

                return $this->id;
            }
        }
        
        return $valid;
    }

 
    private function save_trucks($carrier_id, $trucks)
    {

        $sql = '
            DELETE
            FROM carrier_truck
            WHERE
                carrier_id = ? ';
        $query = DB::exec($sql, array($carrier_id));

        foreach ($trucks as $key => $value) {
            
            $sql = "INSERT INTO carrier_truck (carrier_id, truck_id) VALUES (?, ?) ";
            $query = DB::exec($sql, array($carrier_id, $value));
        }
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
            $sql = 'UPDATE carrier SET excluido = ? WHERE id = ? ';
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
                    name
                FROM
                    carrier
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);

                //populate trucks_carrier
                $sql = 'SELECT truck_id FROM carrier_truck WHERE carrier_id = ?';
                $params[0] = $this->id;
                $query_truck = DB::query($sql, $params);
                $this->code_trucks = array();
                foreach ($query_truck as $row) {
                    $this->code_trucks[] = (int)$row['truck_id'];
                }


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
        }
    }
    
    function get_list($excluido=false)
    {
        $sql = 'SELECT
                    id,
                    name,
                    excluido
                FROM carrier
                WHERE excluido = ?
                ORDER BY id';

        $params[] = ($excluido === true ? 'S' : 'N');

        $query = DB::query($sql, $params);

        return $query;
    }
    
}