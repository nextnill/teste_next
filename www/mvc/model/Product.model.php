<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Product_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    public $weight_vol;
    
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

        if (is_null($this->weight_vol) || $this->weight_vol == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the weight/m³');
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
        $query = DB::query('SELECT id FROM product WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO product (name, weight_vol) VALUES (?, ?) ';
            $query = DB::exec($sql, array($this->name, $this->weight_vol));

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
                        product
                    SET
                        name = ?,
                        weight_vol = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    $this->weight_vol,
                    // where
                    $this->id
                ));

                return $this;
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
            $sql = 'UPDATE product SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));
            
            return $this;
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
                    weight_vol
                FROM
                    product
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);
                return $this;
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
            $this->weight_vol = (float)$row_query['weight_vol'];
        }
    }
    
    function get_list($excluido=false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');
        
        $query = DB::query('SELECT id, name, weight_vol, excluido FROM product WHERE excluido = ? ORDER BY name', array($excluido));
        
        return $query;
    }

    function get_by_quarry($quarry_id)
    {
        $sql = 'SELECT
                    id,
                    name,
                    weight_vol,
                    excluido
                FROM product
                INNER JOIN quarry_product AS qp ON qp.product_id = product.id AND qp.quarry_id = ?
                WHERE excluido = ?
                ORDER BY name';
        
        $params[0] = $quarry_id;
        $params[1] = 'N';

        $query = DB::query($sql, $params);

        return $query;
    }
    
}