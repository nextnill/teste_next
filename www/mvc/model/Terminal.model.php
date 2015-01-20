<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Terminal_Model extends \Sys\Model {
    
    const TERMINAL_TYPE_RAIL = 1;
    const TERMINAL_TYPE_PORT = 2;
    // const TERMINAL_TYPE_PORT_OF_LOADING = 2;
    // const TERMINAL_TYPE_PORT_OF_DISCHARGE = 3;

    public $id;
    public $excluido;

    public $type;
    public $name;
    public $code;
    public $shipping_cost_ton;
    public $shipping_cost_fixed;
    public $country;
    public $contact;
    public $telephone;
    public $mobile;
    public $fax;
    public $email;
    public $contact_other;
    public $obs;
    public $wagon_number;

    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (strlen($this->type) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the type');
        }

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
        $query = DB::query('SELECT id FROM terminal WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO terminal (
                        type,
                        name,
                        code,
                        shipping_cost_ton,
                        shipping_cost_fixed,
                        country,
                        contact,
                        telephone,
                        mobile,
                        fax,
                        email,
                        contact_other,
                        obs,
                        wagon_number
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    ) ';
            
            $params[] = $this->type;
            $params[] = $this->name;
            $params[] = $this->code;
            $params[] = $this->shipping_cost_ton;
            $params[] = $this->shipping_cost_fixed;
            $params[] = $this->country;
            $params[] = $this->contact;
            $params[] = $this->telephone;
            $params[] = $this->mobile;
            $params[] = $this->fax;
            $params[] = $this->email;
            $params[] = $this->contact_other;
            $params[] = $this->obs;
            $params[] = $this->wagon_number;
            
            $query = DB::exec($sql, $params);

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
                        terminal
                    SET
                        type = ?,
                        name = ?,
                        code = ?,
                        shipping_cost_ton = ?,
                        shipping_cost_fixed = ?,
                        country = ?,
                        contact = ?,
                        telephone = ?,
                        mobile = ?,
                        fax = ?,
                        email = ?,
                        contact_other = ?,
                        obs = ?,
                        wagon_number = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->type,
                    $this->name,
                    $this->code,
                    $this->shipping_cost_ton,
                    $this->shipping_cost_fixed,
                    $this->country,
                    $this->contact,
                    $this->telephone,
                    $this->mobile,
                    $this->fax,
                    $this->email,
                    $this->contact_other,
                    $this->obs,
                    $this->wagon_number,
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
            $sql = 'UPDATE terminal SET excluido = ? WHERE id = ? ';
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
                    type,
                    name,
                    code,
                    shipping_cost_ton,
                    shipping_cost_fixed,
                    country,
                    contact,
                    telephone,
                    mobile,
                    fax,
                    email,
                    contact_other,
                    obs,
                    wagon_number
                FROM
                    terminal
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
            $this->type = (string)$row_query['type'];
            $this->name = (string)$row_query['name'];
            $this->code = (string)$row_query['code'];
            $this->shipping_cost_ton = (float)$row_query['shipping_cost_ton'];
            $this->shipping_cost_fixed = (float)$row_query['shipping_cost_fixed'];
            $this->country = (string)$row_query['country'];
            $this->contact = (string)$row_query['contact'];
            $this->telephone = (string)$row_query['telephone'];
            $this->mobile = (string)$row_query['mobile'];
            $this->fax = (string)$row_query['fax'];
            $this->email = (string)$row_query['email'];
            $this->contact_other = (string)$row_query['contact_other'];
            $this->obs = (string)$row_query['obs'];
            $this->wagon_number = (string)$row_query['wagon_number'];
        }
    }
    
    function get_list($excluido=false, $type=null)
    {
       
        $sql = 'SELECT
                    id,
                    type,
                    name,
                    code,
                    shipping_cost_ton,
                    shipping_cost_fixed,
                    country,
                    excluido,
                    wagon_number
                FROM terminal
                ';

        // excluido
        $sql .= 'WHERE excluido = ? ';
        $params[] = ($excluido === true ? 'S' : 'N');

        // type
        if (!is_null($type)) {
            $sql .= 'AND type = ? ';
            $params[] = $type;
        }

        $sql .= 'ORDER BY name ';

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}