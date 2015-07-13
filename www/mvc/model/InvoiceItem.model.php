<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class InvoiceItem_Model extends \Sys\Model {

    public $id;
    public $excluido;

    public $invoice_id;
    public $block_id;
    public $client_id;
    //public $date_production;
    public $nf;
    public $price;
    public $block_number;
    public $tot_c;
    public $tot_a;
    public $tot_l;
    public $tot_vol;
    public $net_c;
    public $net_a;
    public $net_l;
    public $net_vol;
    public $sale_net_c;
    public $sale_net_a;
    public $sale_net_l;
    public $sale_net_vol;
    public $weight;
    public $obs;
    public $client_block_number;
    public $poblo_status_id;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->invoice_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid invoice');
        }

        if (!$this->client_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid client');
        }

        if (!$this->block_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid block');
        }
        /*
        if (is_null($this->date_production))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid production date');
        }
        */
        if (strlen($this->block_number) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid block number');
        }

        if (!$this->tot_c > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid tot c');
        }

        if (!$this->tot_a > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid tot a');
        }

        if (!$this->tot_l > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid tot l');
        }

        if (!$this->tot_vol > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid tot vol');
        }

        if (!$this->net_c > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid net c');
        }

        if (!$this->net_a > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid net a');
        }

        if (!$this->net_l > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid net l');
        }

        if (!$this->net_vol > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid net vol');
        }

        if (!$this->sale_net_c > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid sale net c');
        }

        if (!$this->sale_net_a > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid sale net a');
        }

        if (!$this->sale_net_l > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid sale net l');
        }

        if (!$this->sale_net_vol > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid sale net vol');
        }

        if (!$this->tot_weight > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid weight');
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
        $sql = 'SELECT
	                id
	            FROM
	                invoice_item
	            WHERE
	                id = ?
	            ';
        $query = DB::query($sql, array(
            // where
            $this->id
        ));
        
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
            $sql = 'INSERT INTO invoice_item (
	                    invoice_id,
                        block_id,
                        client_id,
                        nf,
                        price,
                        block_number, 
                        tot_c,
                        tot_a,
                        tot_l,
                        tot_vol,
                        tot_weight,
                        net_c,
                        net_a,
                        net_l,
                        net_vol,
                        sale_net_c,
                        sale_net_a,
                        sale_net_l,
                        sale_net_vol,
                        obs,
                        client_block_number,
                        poblo_status_id
	                ) VALUES (
	                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
	                ) ';
            $query = DB::exec($sql, array(
                // values
                $this->invoice_id,
                $this->block_id,
                $this->client_id,
                $this->nf,
                $this->price,
                $this->block_number,
                $this->tot_c,
                $this->tot_a,
                $this->tot_l,
                $this->tot_vol,
                $this->tot_weight,
                $this->net_c,
                $this->net_a,
                $this->net_l,
                $this->net_vol,
                $this->sale_net_c,
                $this->sale_net_a,
                $this->sale_net_l,
                $this->sale_net_vol,
                $this->obs,
                $this->client_block_number,
                $this->poblo_status_id
            ));

            $this->id = DB::last_insert_id();

            // atualizo o bloco (venda)
            if ($this->id > 0) {
                $block_model = $this->LoadModel('Block', true);
                $block_model->sell(
                    $this->block_id,
                    $this->client_id,
                    $this->block_number,
                    $this->sale_net_c,
                    $this->sale_net_a,
                    $this->sale_net_l,
                    $this->sale_net_vol,
                    $this->client_block_number,
                    $this->block_number_interim
                );
            }

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
                $sql = 'UPDATE
	                        invoice_item
	                    SET
	                        invoice_id = ?,
                            block_id = ?,
                            client_id = ?,
                            nf = ?,
                            price = ?,
                            block_number = ?, 
                            tot_c = ?,
                            tot_a = ?,
                            tot_l = ?,
                            tot_vol = ?,
                            tot_weight = ?,
                            net_c = ?,
                            net_a = ?,
                            net_l = ?,
                            net_vol = ?,
                            sale_net_c = ?,
                            sale_net_a = ?,
                            sale_net_l = ?,
                            sale_net_vol = ?,
                            obs = ?,
                            poblo_status_id = ?
	                    WHERE
	                        id = ?
	                    ';
                $query = DB::exec($sql, array(
                    // set
                    $this->invoice_id,
                    $this->block_id,
                    $this->client_id,
                    $this->nf,
                    $this->price,
                    $this->block_number,
                    $this->tot_c,
                    $this->tot_a,
                    $this->tot_l,
                    $this->tot_vol,
                    $this->tot_weight,
                    $this->net_c,
                    $this->net_a,
                    $this->net_l,
                    $this->net_vol,
                    $this->sale_net_c,
                    $this->sale_net_a,
                    $this->sale_net_l,
                    $this->sale_net_vol,
                    $this->obs,
                    $this->poblo_status_id,
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
            $sql = 'UPDATE
	                    invoice_item
	                SET
	                    excluido = ?
	                WHERE
	                    id = ? 
	                ';
            $query = DB::exec($sql, array(
                // set
                'S',
                // where
                $this->id

            ));

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
            $sql = 'SELECT
	                    id,
                        excluido,
                        invoice_id,
                        block_id,
                        client_id,
                        nf,
                        price,
                        block_number, 
                        tot_c,
                        tot_a,
                        tot_l,
                        tot_vol,
                        tot_weight,
                        net_c,
                        net_a,
                        net_l,
                        net_vol,
                        sale_net_c,
                        sale_net_a,
                        sale_net_l,
                        sale_net_vol,
                        obs,
                        poblo_status_id
	                FROM
	                    invoice_item
	                WHERE
	                    id = ?
                ';
            $query = DB::query($sql, array(
                // where
                $id
            ));
            
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

            $this->invoice_id = (int)$row_query['invoice_id'];
            $this->client_id = (int)$row_query['client_id'];
            
            $this->block_id = (int)$row_query['block_id'];
            //$this->date_production = (string)$row_query['date_production'];
            $this->nf = (string)$row_query['nf'];
            $this->price = (float)$row_query['price'];
            $this->block_number = (string)$row_query['block_number'];
            $this->tot_c = (float)$row_query['tot_c'];
            $this->tot_a = (float)$row_query['tot_a'];
            $this->tot_l = (float)$row_query['tot_l'];
            $this->tot_vol = (float)$row_query['tot_vol'];
            $this->net_c = (float)$row_query['net_c'];
            $this->net_a = (float)$row_query['net_a'];
            $this->net_l = (float)$row_query['net_l'];
            $this->net_vol = (float)$row_query['net_vol'];
            $this->sale_net_c = (float)$row_query['sale_net_c'];
            $this->sale_net_a = (float)$row_query['sale_net_a'];
            $this->sale_net_l = (float)$row_query['sale_net_l'];
            $this->sale_net_vol = (float)$row_query['sale_net_vol'];
            $this->tot_weight = (float)$row_query['tot_weight'];
            $this->obs = (string)$row_query['obs'];
            $this->poblo_status_id = $row_query['poblo_status_id'] == '' ? null:(int)$row_query['poblo_status_id'];
        }
    }
    
    function get_by_invoice($invoice_id)
    {

        $sql = 'SELECT
                    invoice_item.id,
                    invoice_item.excluido,
                    invoice_item.invoice_id,
                    invoice.client_id,
                    invoice.date_record,
                    invoice_item.block_id,
                    block.quarry_id,
                    quarry.name AS quarry_name,
                    block.product_id,
                    block.quality_id,
                    production_order.date_production,
                    quality.name AS quality_name,
                    invoice_item.nf,
                    invoice_item.price,
                    invoice_item.block_number, 
                    invoice_item.tot_c,
                    invoice_item.tot_a,
                    invoice_item.tot_l,
                    invoice_item.tot_vol,
                    invoice_item.net_c,
                    invoice_item.net_a,
                    invoice_item.net_l,
                    invoice_item.net_vol,
                    invoice_item.sale_net_c,
                    invoice_item.sale_net_a,
                    invoice_item.sale_net_l,
                    invoice_item.sale_net_vol,
                    invoice_item.tot_weight,
                    invoice_item.obs,
                    invouce_item.poblo_status_id
                FROM invoice_item
                INNER JOIN block ON (block.id = invoice_item.block_id)
                INNER JOIN production_order_item ON (production_order_item.id = block.production_order_item_id)
                INNER JOIN production_order ON (production_order.id = production_order_item.production_order_id)
                INNER JOIN invoice ON (invoice.id = invoice_item.invoice_id)
                INNER JOIN quarry ON (quarry.id = block.quarry_id)
                INNER JOIN quality ON (quality.id = block.quality_id)
                
                WHERE
                    invoice_item.invoice_id = ?
                    AND invoice_item.excluido = ?
                ORDER BY
                	quarry.name, quality.order_number, invoice_item.block_number
                ';

        $query = DB::query($sql, array($invoice_id, 'N'));
        
        return $query;
    }
    
}