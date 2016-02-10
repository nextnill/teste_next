<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class LotTransportItem_Model extends \Sys\Model {

    public $id;
    public $excluido;

    public $lot_transport_id;
    public $block_id;
    public $invoice_item_id;
    public $status;
    public $client_removed;
    public $last_travel_route_id;
    public $dismembered;
    public $dismembered_lot_transport_id;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->lot_transport_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid lot transport');
        }

        if (!$this->block_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid block');
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
	                lot_transport_item
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
            $sql = 'INSERT INTO lot_transport_item (
	                    lot_transport_id,
                        block_id,
                        invoice_item_id,
                        client_removed 
	                ) VALUES (
	                    ?, ?, ?, ?
	                ) ';
            $query = DB::exec($sql, array(
                // values
                $this->lot_transport_id,
                $this->block_id,
                $this->invoice_item_id,
                ($this->client_removed == 'true' ? 1 : 0)
            ));

            $this->id = DB::last_insert_id();

            // atualizo o bloco (venda)
            if ($this->id > 0) {
                $block_model = $this->LoadModel('Block', true);
                $block_model->set_current_lot_transport($this->block_id, $this->lot_transport_id);
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
	                        lot_transport_item
	                    SET
	                        lot_transport_id = ?,
                            block_id = ?,
                            invoice_item_id = ?,
                            status = ?,
                            client_removed = ?,
                            last_travel_route_id = ?,
                            dismembered = ?,
                            dismembered_lot_transport_id = ?
	                    WHERE
	                        id = ?
	                    ';
                $query = DB::exec($sql, array(
                    // set
                    $this->lot_transport_id,
                    $this->block_id,
                    $this->invoice_item_id,
                    $this->status,
                    ($this->client_removed != '' && $this->client_removed == 'true' ? 1 : 0),
                    ($this->last_travel_route_id > 0 ? $this->last_travel_route_id : null),
                    ($this->dismembered != '' && $this->dismembered == 'true' ? 1 : 0),
                    ($this->dismembered_lot_transport_id > 0 ? $this->dismembered_lot_transport_id : null),
                    // where
                    $this->id

                ));
                

                
                return $this->id;
            }
        }
        
        return $validation;
    }

    function delete_by_lot_transport($logic, $lot_transport)
    {
        $validation = new Validation();

        if ($lot_transport->status > 0) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Is not possible to delete a lot with the current status');
        }
        else {
            // removo o lote dos blocos
            $lot_items = $this->get_by_lot_transport($lot_transport->id);
            foreach ($lot_items as $key => $lot_item) {
                $block_model = $this->LoadModel('Block', true);
                $block_model->set_current_lot_transport($lot_item['block_id'], null);
            }

            // se não for lógico, removo com delete
            if (!$logic) {
                $sql = 'DELETE FROM lot_transport_item
                        WHERE lot_transport_id = ? ';
                $query = DB::exec($sql, array(
                    // where
                    $lot_transport->id
                ));

                return $query;
            }
            else {
                $sql = 'UPDATE
                            lot_transport_item
                        SET
                            excluido = ?
                        WHERE
                            lot_transport_id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    'S',
                    // where
                    $lot_transport->id
                ));

                return $query;
            }
        }

        return array('validation' => $validation);
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else {
            $sql = 'UPDATE
                        lot_transport_item
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
            $sql = 'SELECT
	                    id,
                        excluido,
                        lot_transport_id,
                        block_id,
                        invoice_item_id,
                        status,
                        client_removed,
                        last_travel_route_id,
                        dismembered,
                        dismembered_lot_transport_id
	                FROM
	                    lot_transport_item
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

            $this->lot_transport_id = (int)$row_query['lot_transport_id'];            
            $this->block_id = (int)$row_query['block_id'];
            $this->invoice_item_id = (int)$row_query['invoice_item_id'];
            $this->status = (int)$row_query['status'];
            $this->client_removed = (int)$row_query['client_removed'];
            $this->last_travel_route_id = (empty($row_query['last_travel_route_id']) ? null : (int)$row_query['last_travel_route_id']);
            $this->dismembered = (int)$row_query['dismembered'];
            $this->dismembered_lot_transport_id = (empty($row_query['dismembered_lot_transport_id']) ? null : (int)$row_query['dismembered_lot_transport_id']);
        }
    }
    
    function get_by_lot_transport($lot_transport_id)
    {
        $sql = 'SELECT
                    lot_transport_item.id,
                    lot_transport_item.excluido,
                    lot_transport_item.lot_transport_id,
                    lot_transport.date_record,
                    lot_transport_item.block_id,
                    lot_transport_item.invoice_item_id,
                    lot_transport_item.status,
                    block.block_number,
                    block.tot_weight,
                    block.quarry_id,
                    quarry.name AS quarry_name,
                    block.product_id,
                    product.name AS product_name,
                    block.quality_id,
                    quality.name AS quality_name,
                    block.tot_weight,
                    block.sale_net_c,
                    block.sale_net_a,
                    block.sale_net_l,
                    block.sale_net_vol,
                    production_order.date_production,
                    lot_transport.draft_file,
                    lot_transport.draft_type,
                    lot_transport.draft_size,
                    -- invoice
                    invoice.id AS invoice_id,
                    invoice_item.id AS invoice_item_id,
                    invoice.date_record AS invoice_date_record,
                    -- lots
                    lot_transport_item.last_travel_route_id,
                    IF (last_travel_route.end_quarry_id IS NOT NULL, last_end_quarry.name, last_end_terminal.name) AS last_end_location,
                    last_travel_route.end_quarry_id AS end_quarry_id,
                    last_end_quarry.name AS end_quarry_name,
                    last_travel_route.end_terminal_id AS end_terminal_id,
                    last_end_terminal.name AS end_terminal_name,
                    -- dismembered
                    lot_transport_item.dismembered,
                    lot_transport_item.dismembered_lot_transport_id,
                    dismembered_lot_transport.lot_number AS dismembered_lot_transport_lot_number
                FROM lot_transport_item
                INNER JOIN lot_transport ON (lot_transport.id = lot_transport_item.lot_transport_id)
                INNER JOIN block ON (block.id = lot_transport_item.block_id)
                INNER JOIN product ON (product.id = block.product_id)
                INNER JOIN production_order_item ON (production_order_item.id = block.production_order_item_id)
                INNER JOIN production_order ON (production_order.id = production_order_item.production_order_id)
                INNER JOIN quarry ON (quarry.id = block.quarry_id)
                INNER JOIN quality ON (quality.id = block.quality_id)
                INNER JOIN invoice_item ON (invoice_item.id = lot_transport_item.invoice_item_id)
                INNER JOIN invoice ON (invoice.id = invoice_item.invoice_id)
                LEFT JOIN travel_route AS last_travel_route ON (last_travel_route.id = lot_transport_item.last_travel_route_id)
                LEFT JOIN quarry AS last_end_quarry ON (last_end_quarry.id = last_travel_route.end_quarry_id)
                LEFT JOIN terminal AS last_end_terminal ON (last_end_terminal.id = last_travel_route.end_terminal_id)
                LEFT JOIN lot_transport AS dismembered_lot_transport ON (dismembered_lot_transport.id = lot_transport_item.dismembered_lot_transport_id)
                WHERE
                    lot_transport_item.lot_transport_id = ?
                    AND lot_transport_item.excluido = ?
                ORDER BY
                	product.name, quality.order_number, block.block_number
                ';

        $query = DB::query($sql, array($lot_transport_id, 'N'));
        
        return $query;
    }

    function get_by_lot_transport_group_by_product_quality($lot_transport_id)
    {
        $sql = "SELECT
                    COUNT(block.id) blocks,
                    block.product_id,
                    product.name AS product_name,
                    block.quality_id,
                    quality.name AS quality_name,
                    SUM(block.sale_net_vol) AS sale_net_vol,
                    SUM(block.tot_weight) AS tot_weight,
                    cii.value,
                    (
                        SELECT cii_old.value
                        FROM commercial_invoice_item AS cii_old
                        WHERE cii_old.lot_transport_id != lot_transport.id
                                AND cii_old.client_id = lot_transport.client_id
                                AND cii_old.product_id = product.id
                                AND cii_old.quality_id = quality.id
                                AND cii_old.excluido = 'N'
                        ORDER BY cii_old.date_record
                        LIMIT 0, 1
                    ) AS last_value
                FROM lot_transport_item
                INNER JOIN lot_transport ON (lot_transport.id = lot_transport_item.lot_transport_id)
                INNER JOIN block ON (block.id = lot_transport_item.block_id)
                INNER JOIN product ON (product.id = block.product_id)
                INNER JOIN quality ON (quality.id = block.quality_id)
                LEFT JOIN commercial_invoice_item AS cii ON (cii.lot_transport_id = lot_transport.id AND cii.product_id = product.id AND cii.quality_id = quality.id AND cii.excluido = 'N')
                WHERE
                    lot_transport_item.lot_transport_id = ?
                    AND lot_transport_item.excluido = 'N'
                GROUP BY
                    block.product_id, block.quality_id
                ORDER BY
                    product.name, quality.order_number, block.block_number
                ";

        $params[] = $lot_transport_id;

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}