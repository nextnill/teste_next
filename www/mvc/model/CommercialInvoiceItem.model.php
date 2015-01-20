<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class CommercialInvoiceItem_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $date_record;

    public $lot_transport_id;
    public $product_id;
    public $quality_id;
    public $value;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->lot_transport_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the lot transport');
        }

        if (!$this->product_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the product');
        }

        if (!$this->quality_id > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the quality');
        }
        
        return $validation;
    }
    
    function save($lot_transport_id=null, array $products=null)
    {
        // se os parmâmetros da função não foram informados, então é um insert normal,
        // onde os valores dos campos irão vir do proprio objeto
        if (is_null($lot_transport_id)) {
            if (!$this->exists()) {
                return $this->insert();
            }
            else {
                return $this->update();
            }
        }
        // senão, é um insert em lote, onde é verificado se já existe algum registro do
        // produto e qualidade para determinado lote, se existir atualiza, senão insere, com base
        // nos parametros informados na chamada da função
        else if (!is_null($lot_transport_id)) {
            
            $lot_transport_model = $this->LoadModel('LotTransport', true);
            $lot_transport_model->populate($lot_transport_id);

            if (!is_null($products) && (sizeof($products) > 0)) {
                foreach ($products as $key => $item) {
                    $product_id = (int)$item['product_id'];
                    $quality_id = (int)$item['quality_id'];
                    $value = (float)$item['value'];

                    $commercial_invoice_item_model = new self;
                
                    $commercial_invoice_item_model->lot_transport_id = $lot_transport_id;
                    $commercial_invoice_item_model->client_id = $lot_transport_model->client_id;
                    $commercial_invoice_item_model->product_id = $product_id;
                    $commercial_invoice_item_model->quality_id = $quality_id;
                    $commercial_invoice_item_model->value = $value;
                    
                    $commercial_invoice_item_model->id = $commercial_invoice_item_model->exists_lot_transport_product_quality($lot_transport_id, $product_id, $quality_id);

                    $commercial_invoice_item_model->save();
                }
            }
            
        }
    }
    
    function exists()
    {
        if (is_null($this->id)) {
            $this->id = 0;
        }
        $query = DB::query('SELECT id FROM commercial_invoice_item WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query)) {
            return true;
        }
        return false;
    }

    function exists_lot_transport_product_quality($lot_transport_id, $product_id, $quality_id)
    {
        $query = DB::query('SELECT id FROM commercial_invoice_item
                            WHERE
                                lot_transport_id = ?
                                AND product_id = ?
                                AND quality_id = ?
                            AND excluido = \'N\' ', array($lot_transport_id, $product_id, $quality_id));
        
        if (DB::has_rows($query)) {
            return $query[0]['id'];
        }
        return 0;
    }
    
    function insert()
    {
        $validation = $this->validation();

        if ($validation->isValid()) {
            $sql = 'INSERT INTO commercial_invoice_item (
                        date_record,
                        lot_transport_id,
                        client_id,
                        product_id,
                        quality_id,
                        value
                    ) VALUES (
                        ?, ?, ?,
                        ?, ?, ?
                    ) ';
            
            $dt_now = new DateTime('now');
            $dt_now = $dt_now->format('Y-m-d H:i:s');
            $params[] = $dt_now;

            $params[] = $this->lot_transport_id;
            $params[] = $this->client_id;
            $params[] = $this->product_id;
            $params[] = $this->quality_id;
            $params[] = $this->value;

            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();
            return $this;
        }
        
        return $validation;
    }
    
    function update()
    {
        $validation = $this->validation();
        
        if ($validation->isValid()) {
            if (!$this->exists()) {
                $validation = new Validation();
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
            }
            else {
                $sql = 'UPDATE
                            commercial_invoice_item
                        SET
                            value = ?
                        WHERE
                            id = ?
                            AND lot_transport_id = ?
                            AND product_id = ?
                            AND quality_id = ?
                ';

                // set
                $params[] = $this->value;
                // where
                $params[] = $this->id;
                $params[] = $this->lot_transport_id;
                $params[] = $this->product_id;
                $params[] = $this->quality_id;

                $query = DB::exec($sql, $params);

                return $this;
            }
        }
        
        return $validation;
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else {
            $sql = 'UPDATE commercial_invoice_item SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));
            $this->excluido = 'S';

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
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else {
            $query = DB::query(
                'SELECT
                    id,
                    excluido,
                    date_record,
                    lot_transport_id,
                    client_id,
                    product_id,
                    quality_id,
                    value
                FROM
                    commercial_invoice_item
                WHERE id = ?',
                array($id)
            );
            
            if (DB::has_rows($query)) {
                $this->fill($query[0]);
                return $this;
            }
        }

        return $validation;
    }

    function fill($row_query)
    {
        if ($row_query) {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->date_record = (string)$row_query['date_record'];

            $this->lot_transport_id = (int)$row_query['lot_transport_id'];
            $this->client_id = (int)$row_query['client_id'];
            $this->product_id = (int)$row_query['product_id'];
            $this->quality_id = (int)$row_query['quality_id'];
            $this->value = (float)$row_query['value'];
        }
    }
    
}