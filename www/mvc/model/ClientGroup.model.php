<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class ClientGroup_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    
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
        $query = DB::query('SELECT id FROM client_group WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO client_group (name) VALUES (?) ';
            $query = DB::exec($sql, array($this->name));

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
                        client_group
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
            $sql = 'UPDATE client_group SET excluido = ? WHERE id = ? ';
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
                    client_group
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
        }
    }
    
    function get_list($excluido=false, $just_id =false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');

        $sql = "SELECT
                    client_group.id,
                    client_group.excluido,
                    client_group.name
                FROM client_group
                WHERE
                ";
        
       // $query = DB::query('SELECT id, name, excluido FROM client_group WHERE excluido = ? ORDER BY name', array($excluido));
        
        if ($just_active_quarries === true) {
            $sql .= "   client_group.id IN ({$this->active_client_groups}) AND ";
        }

        $sql .="    client_group.excluido = 'N'
                ORDER BY client_group.name ";
        
        $query = DB::query($sql);

        // just_id = true
        if (isset($just_id) && ($just_id == true)) {
            $new_query = array();
            foreach ($query as $key => $row) {
                $new_query[] = $row['id'];
            }
            $query = $new_query;
        }

        return $query;
    }

    function get_by_user($user_id, $just_id)
    {
        $sql = 'SELECT
                    user_client_group.user_id,
                    user_client_group.client_group_id,
                    client_group.name AS client_group_name
                FROM
                    user_client_group
                INNER JOIN
                    client_group ON (client_group.id = user_client_group.client_group_id)
                WHERE
                    user_client_group.user_id = ?
                    AND client_group.excluido = "N"
                ORDER BY
                    client_group.name
                ';

        $query = DB::query($sql, array($user_id));

        // just_id = true
        if (isset($just_id) && ($just_id == true)) {
            $new_query = array();
            foreach ($query as $key => $row) {
                $new_query[] = $row['client_group_id'];
            }
            $query = $new_query;
        }
        

        return $query;
    }
    
}