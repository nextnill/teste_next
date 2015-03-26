<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class PobloStatus_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $status;
    public $color;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (strlen($this->status) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter with a status');
        }

        if (strlen($this->color) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter with a color');
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
        $query = DB::query('SELECT id FROM poblo_status WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }

    function exists_status($name, $id=0)
    {       
        $query = DB::query('SELECT id FROM poblo_status WHERE status = ? AND id != ? AND excluido = \'N\' ', array($status, $id));
        
        if (DB::has_rows($query)) {
            return true;
        }

        return false;
    }
    
    function insert()
    {
        $validation = $this->validation();

        if ($validation->isValid())
        {
            if ($this->exists_status($this->status))
            {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'This status is in use');
            }
            else
            {
                $sql = 'INSERT INTO poblo_status (status, cor) VALUES (?, ?) ';
                $query = DB::exec($sql, array($this->status, $this->cor));

                $this->id = DB::last_insert_id();
                
                return $this;
            }
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
            else if ($this->exists_status($this->status, $this->id))
            {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'This status is in use');
            }
            else
            {
                $sql = 'UPDATE
                            poblo_status
                        SET
                            status = ?,
                            cor = ?
                        WHERE
                            id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->status,
                    $this->cor,
                    // where
                    $this->id
                ));

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
            $sql = 'UPDATE poblo_status SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));
            $this->excluido = 'S';
            
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
                    status,
                    color
                FROM
                    poblo_status
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);
                return $this;
            }
        }

        return array('validation' => $validation);
    }

    function fill($row_query)
    {
        if ($row_query)
        {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->status = (string)$row_query['status'];
            $this->cor = (string)$row_query['cor'];
        }
    }

    // lists
    function get_list($excluido=false)
    {
        
        $sql = 'SELECT id, excluido, status, cor FROM poblo_status WHERE excluido = ? ORDER BY status';

        $params[] = ($excluido === true ? 'S' : 'N');

        $query = DB::query($sql, $params);

        return $query;
    }
    
}