<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Defect_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    public $description;
    
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

        if (strlen($this->description) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the description');
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
        $query = DB::query('SELECT id FROM defect WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO defect (name, description) VALUES (?, ?) ';
            $query = DB::exec($sql, array($this->name, $this->description));

            $this->id = DB::last_insert_id();
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
                        defect
                    SET
                        name = ?,
                        description = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    $this->description,
                    // where
                    $this->id
                ));

                return $this->id;
            }
        }
        
        return $valid;
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
            $sql = 'UPDATE defect SET excluido = ? WHERE id = ? ';
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
                    description
                FROM
                    defect
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
            $this->description = (string)$row_query['description'];
        }
    }
    
    function get_list($excluido=false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');
        
        $query = DB::query('SELECT id, name, description, excluido FROM defect WHERE excluido = ? ORDER BY name', array($excluido));
        
        return $query;
    }

    function get_by_quarry($quarry_id)
    {        
        $sql = 'SELECT
                    defect.id,
                    defect.name,
                    defect.description
                FROM
                    quarry_defect
                INNER JOIN
                    defect ON (defect.id = quarry_defect.defect_id)
                WHERE
                    quarry_defect.quarry_id = ?
                    AND defect.excluido = ?
                ORDER BY
                    defect.name';

        $query = DB::query($sql, array($quarry_id, 'N'));
        
        return $query;
    }

    function get_by_poi($poi_id)
    {
        $sql = 'SELECT
                    defect_poi.id,
                    defect_poi.defect_id,
                    defect.name,
                    defect.description
                FROM
                    defect_poi
                INNER JOIN
                    defect ON (defect.id = defect_poi.defect_id)
                WHERE
                    defect_poi.production_order_item_id = ?
                    
                ORDER BY
                    defect.name
                ';
        $query = DB::query($sql, array($poi_id));

        return $query;
    }

    function get_by_block($block_id)
    {
        $sql = 'SELECT
                    block_defect.block_id,
                    block_defect.defect_id,
                    defect.name,
                    defect.description
                FROM
                    block_defect
                INNER JOIN
                    defect ON (defect.id = block_defect.defect_id)
                WHERE
                    block_defect.block_id = ?
                ORDER BY
                    defect.name
                ';
        $query = DB::query($sql, array($block_id));

        return $query;
    }
    
}