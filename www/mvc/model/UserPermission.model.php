<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class UserPermission_Model extends \Sys\Model {
    
    public $id;
    public $user_id;
    public $ref;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (strlen($this->user_id) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the user');
        }

        if (strlen($this->ref) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the permission ref');
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
        $query = DB::query('SELECT id FROM user_permission WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }

    function exists_user_ref($user_id, $ref)
    {
        if (empty($this->user_id) || empty($this->ref))
        {
            $validation = new Validation();
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the user id and permission ref');
            return array('validation' => $validation);
        }

        $query = DB::query('SELECT id FROM user_permission WHERE user_id = ? AND ref != ? ', array($user_id, $ref));
        
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
            $sql = 'INSERT INTO user_permission (user_id, ref) VALUES (?, ?) ';
            $query = DB::exec($sql, array($this->user_id, $this->ref));

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
                            user_permission
                        SET
                            user_id = ?,
                            ref = ?
                        WHERE
                            id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->user_id,
                    $this->ref,
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
            $sql = 'DELETE FROM user_permission WHERE id = ? ';
            $query = DB::exec($sql, array($this->id));
            
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
                    user_id,
                    ref
                FROM
                    user_permission
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

            $this->user_id = (int)$row_query['user_id'];
            $this->ref = (string)$row_query['ref'];
        }
    }

    function get_by_user($user_id)
    {
        $query = DB::query('SELECT id, user_id, ref FROM user_permission WHERE user_id = ? ORDER BY ref', array($user_id));
        
        return $query;
    }
    
}