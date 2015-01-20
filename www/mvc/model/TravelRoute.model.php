<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class TravelRoute_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $start_quarry_id;
    public $start_terminal_id;
    public $end_quarry_id;
    public $end_terminal_id;
    public $shipping_time;
    public $blocks;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if ((!$this->start_quarry_id > 0) && (!$this->start_terminal_id > 0))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the start location');
        }

        if ((!$this->end_quarry_id > 0) && (!$this->end_terminal_id > 0))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the end location');
        }

        if ((!$this->blocks > 0) && (!$this->blocks > 0))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the number of blocks');
        }

        if ($this->exists_equal())
        {
            $validation->add(Validation::VALID_ERR, 'This route already exists');
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
        $query = DB::query('SELECT id FROM travel_route WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }

    function exists_equal()
    {
        $sql = 'SELECT id
                FROM travel_route
                WHERE
                    (coalesce(start_quarry_id, 0) = ?
                    AND coalesce(start_terminal_id, 0) = ?
                    AND coalesce(end_quarry_id, 0) = ?
                    AND coalesce(end_terminal_id, 0) = ?)
                    AND (id != ?) ';

        $params[] = (int)$this->start_quarry_id;
        $params[] = (int)$this->start_terminal_id;
        $params[] = (int)$this->end_quarry_id;
        $params[] = (int)$this->end_terminal_id;
        $params[] = $this->id;

        $query = DB::query($sql, $params);
        
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
            $sql = 'INSERT INTO travel_route (
                        start_quarry_id,
                        start_terminal_id,
                        end_quarry_id,
                        end_terminal_id,
                        shipping_time,
                        blocks
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?
                    ) ';
            
            $params[] = ($this->start_quarry_id > 0 ? (int)$this->start_quarry_id : null);
            $params[] = ($this->start_terminal_id > 0 ? (int)$this->start_terminal_id : null);
            $params[] = ($this->end_quarry_id > 0 ? (int)$this->end_quarry_id : null);
            $params[] = ($this->end_terminal_id > 0 ? (int)$this->end_terminal_id : null);
            $params[] = (int)$this->shipping_time;
            $params[] = (int)$this->blocks;

            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();
            return $this;
        }
        
        return array('validation' => $validation);
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
                $sql = 'UPDATE
                            travel_route
                        SET
                            start_quarry_id = ?,
                            start_terminal_id = ?,
                            end_quarry_id = ?,
                            end_terminal_id = ?,
                            shipping_time = ?,
                            blocks = ?
                        WHERE
                            id = ? ';
                // set
                $params[] = ($this->start_quarry_id > 0 ? (int)$this->start_quarry_id : null);
                $params[] = ($this->start_terminal_id > 0 ? (int)$this->start_terminal_id : null);
                $params[] = ($this->end_quarry_id > 0 ? (int)$this->end_quarry_id : null);
                $params[] = ($this->end_terminal_id > 0 ? (int)$this->end_terminal_id : null);
                $params[] = (int)$this->shipping_time;
                $params[] = (int)$this->blocks;
                
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
        
        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else
        {
            $sql = 'UPDATE travel_route SET excluido = ? WHERE id = ? ';
            
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
                    start_quarry_id,
                    start_terminal_id,
                    end_quarry_id,
                    end_terminal_id,
                    shipping_time,
                    blocks
                FROM
                    travel_route
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

            $this->start_quarry_id = (empty($row_query['start_quarry_id']) ? null : (int)$row_query['start_quarry_id']);
            $this->start_terminal_id = (empty($row_query['start_terminal_id']) ? null : (int)$row_query['start_terminal_id']);
            $this->end_quarry_id = (empty($row_query['end_quarry_id']) ? null : (int)$row_query['end_quarry_id']);
            $this->end_terminal_id = (empty($row_query['end_terminal_id']) ? null : (int)$row_query['end_terminal_id']);
            $this->shipping_time = (int)$row_query['shipping_time'];
            $this->blocks = (int)$row_query['blocks'];
        }
    }
    
    function get_list($excluido=false)
    {
        $sql = 'SELECT
                    travel_route.id,
                    travel_route.excluido,
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
                    travel_route.blocks
                FROM travel_route
                LEFT JOIN quarry AS start_quarry ON (start_quarry.id = travel_route.start_quarry_id)
                LEFT JOIN terminal AS start_terminal ON (start_terminal.id = travel_route.start_terminal_id)
                LEFT JOIN quarry AS end_quarry ON (end_quarry.id = travel_route.end_quarry_id)
                LEFT JOIN terminal AS end_terminal ON (end_terminal.id = travel_route.end_terminal_id)
                WHERE travel_route.excluido = ?
                ';

        $params[] = ($excluido === true ? 'S' : 'N');

        $query = DB::query($sql, $params);
        
        return $query;
    }

    function get_by_start($start_quarry_id=null, $start_terminal_id=null)
    {
        $sql = 'SELECT
                    travel_route.id,
                    travel_route.excluido,
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
                    travel_route.blocks
                FROM travel_route
                LEFT JOIN quarry AS start_quarry ON (start_quarry.id = travel_route.start_quarry_id)
                LEFT JOIN terminal AS start_terminal ON (start_terminal.id = travel_route.start_terminal_id)
                LEFT JOIN quarry AS end_quarry ON (end_quarry.id = travel_route.end_quarry_id)
                LEFT JOIN terminal AS end_terminal ON (end_terminal.id = travel_route.end_terminal_id)
                WHERE travel_route.excluido = \'N\'
                ';

        if (!is_null($start_quarry_id)) {
            $sql .= ' AND travel_route.start_quarry_id = ? ';
            $params[] = $start_quarry_id;
        }

        if (!is_null($start_terminal_id)) {
            $sql .= ' AND travel_route.start_terminal_id = ? ';
            $params[] = $start_terminal_id;
        }

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}