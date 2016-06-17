<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Price_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $client_id;
    public $date_ref;
    public $comments;
    public $values = array();

    protected $active_user;
    
    function __construct()
    {
        parent::__construct();

        $this->active_user = $this->ActiveUser();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (strlen($this->client_id) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the client');
        }

        if (strlen($this->date_ref) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the date ref');
        }

        if ($this->exists_date_ref()) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Operation canceled.');
            $validation->add(Validation::VALID_ERR_FIELD, 'There is already registered to the "Date Ref" informed for this client.');
            $validation->add(Validation::VALID_ERR_FIELD, 'To change old price records use the Price History option.');
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
        $query = DB::query('SELECT id FROM price_list WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }

    function exists_date_ref()
    {
        $sql = "SELECT id
                FROM price_list 
                WHERE client_id = ? 
                AND date_ref = ?
                AND excluido = ? ";

        $params[0] = $this->client_id;
        $params[1] = $this->date_ref;
        $params[2] = 'N';

        // ignoro o id atual
        if ((int)$this->id > 0)
        {
            $sql .= " AND id != ? ";
            $params[3] = $this->id;
        }

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

            $sql = 'INSERT INTO price_list (
                        client_id, 
                        date_ref, 
                        comments
                    ) VALUES (?, ?, ?) ';
            $query = DB::exec($sql, array(
                $this->client_id,
                $this->date_ref,
                $this->comments
            ));

            $this->id = DB::last_insert_id();

            $this->save_values();

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
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Price List Id does not exists');
            }
            else
            {
                $sql = '
                    UPDATE
                        price_list
                    SET
                        date_ref = ?,
                        comments = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->date_ref,
                    $this->comments,
                    // where
                    $this->id

                ));

                $this->save_values();

                return $this->id;
            }
        }
        
        return $validation;
    }

    // values
    private function save_values()
    {
        $this->delete_values();
        $this->insert_values();
    }

    private function delete_values()
    {
        $sql = 'DELETE FROM price_list_quality WHERE price_list_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_values()
    {
        if (!is_null($this->values) && !empty($this->values) && is_array($this->values))
        {
            foreach ($this->values as $key => $value) {
                $sql = 'INSERT INTO price_list_quality (price_list_id, product_id, quality_id, value) VALUES (?, ?, ?, ?)';
                $params[0] = $this->id;
                $params[1] = $value->product_id;
                $params[2] = $value->quality_id;
                $params[3] = $value->value;
                $query = DB::exec($sql, $params);
            }
        }
    }

   
    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Price List Id does not exists');
        }
        else
        {
            $sql = 'UPDATE price_list SET excluido = ? WHERE id = ? ';
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
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Price List Id does not exists');
        }
        else
        {
            $query = DB::query(
                "SELECT
                    id,
                    excluido,
                    client_id, 
                    date_ref, 
                    comments
                FROM
                    price_list
                WHERE
                    id = ?
                ",
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

    function populate_by_client_id($client_id)
    {
        $ret = $this->get_last_by_client_id($client_id);
        $values = $this->get_by_price_list_id($ret['price_list_id']);
        $this->fill($ret, $values);
    }

    function fill($row_query, $values=null)
    {
        if ($row_query)
        {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];
            $this->price_list_id = (string)$row_query['price_list_id'];
            $this->client_id = (string)$row_query['client_id'];
            $this->date_ref = (string)$row_query['date_ref'];
            $this->comments = (string)$row_query['comments'];

            if (!is_null($values)) {
                $this->values = $values;
            }
        }
    }

    function get_last_by_client_id($client_id) {
        // encontro o preço mais atual do cliente
        $query = DB::query(
            "SELECT
                price_list.id,
                price_list.id AS price_list_id,
                price_list.client_id AS client_id,
                price_list.date_ref,
                price_list.comments
            FROM price_list
            INNER JOIN client ON (client.id = price_list.client_id)
            WHERE
                price_list.client_id = ?
                AND date_ref = (SELECT MAX(pl2.date_ref) FROM price_list AS pl2 WHERE pl2.client_id = price_list.client_id AND pl2.excluido = 'N')
                AND price_list.excluido = 'N'
            GROUP BY price_list.client_id
            ORDER BY client.code ",
            array((int)$client_id)
        );

        return isset($query[0]) ? $query[0] : null;
    }

    function get_by_price_list_id($price_list_id) {
        // encontro os valores das qualidades
        // encontro o preço mais atual do cliente
        $query = DB::query(
            "SELECT
                product_id,
                quality_id,
                value
            FROM price_list_quality
            INNER JOIN quality ON quality.id = price_list_quality.quality_id
            WHERE price_list_id = ?
            ORDER BY quality.order_number ",
            array((int)$price_list_id)
        );
        return $query;
    }
    
    function get_by_client_group($client_group_id=null)
    {
        $return = array();

        $client_model = $this->LoadModel('Client', true);
        $clients = $client_model->get_list_by_client_group((int)$client_group_id);

        $return['clients'] = $clients;
        
        foreach ($clients as $client_key => $client) {
            
            $last_price = $this->get_last_by_client_id((int)$client['id']);

            if (!is_null($last_price)) {
                $return['clients'][$client_key]['price_list_id'] = $last_price['price_list_id'];
                $return['clients'][$client_key]['date_ref'] = $last_price['date_ref'];
                $return['clients'][$client_key]['comments'] = $last_price['comments'];

                // encontro os valores das qualidades
                // referente ao price_list_id mais recente retornado na consulta anterior
                $return['clients'][$client_key]['values'] = $this->get_by_price_list_id((int)$return['clients'][$client_key]['price_list_id']);
            }                
        }

        return $return;
    }

    function get_history($client_id)
    {
        // encontro o preço mais atual do cliente
        $history = DB::query(
            "SELECT
                price_list.id AS price_list_id,
                price_list.client_id AS client_id,
                price_list.date_ref,
                price_list.comments
            FROM price_list
            WHERE
                price_list.client_id = ?
                AND price_list.excluido = 'N'
            ORDER BY date_ref DESC",
            array((int)$client_id)
        );

        foreach ($history as $price_key => $price) {
            $history[$price_key]['values'] = $this->get_by_price_list_id((int)$price['price_list_id']);
        }


        return $history;
        
    }
    
}