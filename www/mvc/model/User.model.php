<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class User_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    public $password;
    public $blocked;
    public $admin;

    public $quarries;
    public $client_groups;
    public $permissions;
    
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

        if (strlen($this->password) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the password');
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
        $query = DB::query('SELECT id FROM user WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }

    function exists_name($name, $id=0)
    {       
        $query = DB::query('SELECT id FROM user WHERE name = ? AND id != ? AND excluido = \'N\' ', array($name, $id));
        
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
            if ($this->exists_name($this->name))
            {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'This username is in use');
            }
            else
            {
                $sql = 'INSERT INTO user (name, password, blocked, admin) VALUES (?, ?, ?, ?) ';
                $query = DB::exec($sql, array($this->name, $this->password, ($this->blocked == 'true' ? 1 : 0), ($this->admin == 'true' ? 1 : 0)));

                $this->id = DB::last_insert_id();

                $this->save_quarries();
               // $this->save_client_groups();
                $this->save_permissions();
                
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
            else if ($this->exists_name($this->name, $this->id))
            {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'This username is in use');
            }
            else
            {
                $sql = 'UPDATE
                            user
                        SET
                            name = ?,
                            password = ?,
                            blocked = ?,
                            admin = ?
                        WHERE
                            id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    $this->password,
                    ($this->blocked == 'true' ? 1 : 0),
                    ($this->admin == 'true' ? 1 : 0),
                    // where
                    $this->id
                ));

                $this->save_quarries();
                $this->save_client_groups();
                $this->save_permissions();

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
            $sql = 'UPDATE user SET excluido = ? WHERE id = ? ';
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
                    name,
                    password,
                    blocked,
                    admin
                FROM
                    user
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

            $this->name = (string)$row_query['name'];
            $this->password = (string)$row_query['password'];

            $this->blocked = (bool)$row_query['blocked'];
            $this->admin = (bool)$row_query['admin'];

            $quarry_model = $this->LoadModel('Quarry', true);
            $client_group_model = $this->LoadModel('ClientGroup', true);

            // carrego as pedreiras, grupos de clientes e permissões do usuário logado
            
            // se for administrador
            if ($this->admin === true) {
                // carrega todas as pedreiras
                $this->quarries = $quarry_model->get_list(false, true);
                // carrega todos os grupos de cliente
                $this->client_groups = $client_group_model->get_list(false, true);
                // carrega todas as permissões
                $this->permissions = array();
                foreach (\Sys\Permissions::$permissions as $key => $permission) {
                    $this->permissions[] = $key;
                }
            }
            // se não for administrador
            else {
                // carrega as pedreiras do usuário logado
                $this->quarries = $quarry_model->get_by_user($this->id, true);
                // carrega os grupos de cliente do usuário logado
                $this->client_groups = $client_group_model->get_by_user($this->id, true);
                // carrega as permissões do usuário logado
                $this->permissions = $this->get_permissions_by_user($this->id);
            }

        }
    }

    function validate_login($name, $password)
    {
        if (empty($name) || empty($password)) {
            $validation = new Validation();
            $validation->add(Validation::VALID_ERR, 'Invalid user or password');
            return array('validation' => $validation);
        }

        $query = DB::query('SELECT id, name, password
                            FROM user
                            WHERE name = ?
                                AND blocked = false
                                AND excluido = \'N\'', array($name)
        );
        
        if (DB::has_rows($query))
        {
            if ($query[0]['password'] === $password)
            {
                return (array)$this->populate($query[0]['id']);
            }
            else
            {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR, 'Invalid user or password');
                return array('validation' => $validation);
            }
        }
        else
        {
            $validation = new Validation();
            $validation->add(Validation::VALID_ERR, 'Invalid user or password');
            return array('validation' => $validation);
        }
    }

    // quarries
    private function save_quarries()
    {
        $this->delete_quarries();
        $this->insert_quarries();
    }

    // client_groups
    private function save_client_groups()
    {
        $this->delete_client_groups();
        $this->insert_client_groups();
    }

    private function delete_quarries()
    {
        $sql = 'DELETE FROM user_quarry WHERE user_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_quarries()
    {
        if (!is_null($this->quarries) && !empty($this->quarries))
        {
            foreach ($this->quarries as $key => $item) {
                $sql = 'INSERT INTO user_quarry (user_id, quarry_id) VALUES (?, ?)';
                $params[0] = $this->id;
                $params[1] = is_array($item) ? $item['quarry_id'] : $item;
                $query = DB::exec($sql, $params);
            }
        }
    }

    private function delete_client_groups()
    {
        $sql = 'DELETE FROM user_client_group WHERE user_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_client_groups()
    {
        if (!is_null($this->client_groups) && !empty($this->client_groups))
        {
            foreach ($this->client_groups as $key => $item) {
                $sql = 'INSERT INTO user_client_group (user_id, client_group_id) VALUES (?, ?)';
                $params[0] = $this->id;
                $params[1] = is_array($item) ? $item['client_group_id'] : $item;
                $query = DB::exec($sql, $params);
            }
        }
    }

    // permissions
    private function save_permissions()
    {
        $this->delete_permissions();
        if (!is_null($this->permissions)) {
            $this->insert_permissions();
        }
    }

    private function delete_permissions()
    {
        $sql = 'DELETE FROM user_permission WHERE user_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_permissions()
    {
        if (!is_null($this->permissions) && !empty($this->permissions))
        {
            foreach ($this->permissions as $key => $item) {
                $sql = 'INSERT INTO user_permission (user_id, permission_key) VALUES (?, ?)';
                $params[0] = $this->id;
                $params[1] = is_array($item) ? $item['permission_key'] : $item;
                $query = DB::exec($sql, $params);
            }
        }
    }

    // lists
    function get_permissions_by_user($user_id)
    {
        $sql = 'SELECT permission_key
                FROM user_permission
                WHERE user_id = ?
                ORDER BY permission_key ';
        $query = DB::query($sql, array($user_id));

        $new_query = array();
        foreach ($query as $key => $row) {
            $new_query[] = $row['permission_key'];
        }
        $query = $new_query;        

        return $query;
    }

    function get_list($excluido=false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');
        
        $query = DB::query('SELECT id, excluido, name, blocked, admin FROM user WHERE excluido = ? ORDER BY name', array($excluido));
        
        return $query;
    }
    
}