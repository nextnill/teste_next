<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Agency_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $shipping_company;
    public $name;
    public $code;
    public $contact;
    public $telephone;
    public $mobile;
    public $fax;
    public $email;
    public $contact_other;
    public $obs;

    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        
        if (strlen($this->shipping_company) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the shipping company');
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
        $query = DB::query('SELECT id FROM agency WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO agency (
                        shipping_company,
                        name,
                        code,
                        contact,
                        telephone,
                        mobile,
                        fax,
                        email,
                        contact_other,
                        obs
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    ) ';

            $query = DB::exec($sql, array(
                // values
                $this->shipping_company,
                $this->name,
                $this->code,
                $this->contact,
                $this->telephone,
                $this->mobile,
                $this->fax,
                $this->email,
                $this->contact_other,
                $this->obs
            ));

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
                        agency
                    SET
                        shipping_company = ?,
                        name = ?,
                        code = ?,
                        contact = ?,
                        telephone = ?,
                        mobile = ?,
                        fax = ?,
                        email = ?,
                        contact_other = ?,
                        obs = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->shipping_company,
                    $this->name,
                    $this->code,
                    $this->contact,
                    $this->telephone,
                    $this->mobile,
                    $this->fax,
                    $this->email,
                    $this->contact_other,
                    $this->obs,
                    // where
                    $this->id

                ));

                return $this->id;
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
            $sql = 'UPDATE agency SET excluido = ? WHERE id = ? ';
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
                    shipping_company,
                    name,
                    code,
                    contact,
                    telephone,
                    mobile,
                    fax,
                    email,
                    contact_other,
                    obs
                FROM
                    agency
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

            $this->shipping_company = (string)$row_query['shipping_company'];
            $this->name = (string)$row_query['name'];
            $this->code = (string)$row_query['code'];
            $this->contact = (string)$row_query['contact'];
            $this->telephone = (string)$row_query['telephone'];
            $this->mobile = (string)$row_query['mobile'];
            $this->fax = (string)$row_query['fax'];
            $this->email = (string)$row_query['email'];
            $this->contact_other = (string)$row_query['contact_other'];
            $this->obs = (string)$row_query['obs'];
        }
    }
    
    function get_list($excluido=false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');
        
        $query = DB::query('SELECT id, excluido, shipping_company, name, code FROM agency WHERE excluido = ? ORDER BY name', array($excluido));
        
        return $query;
    }
    
}