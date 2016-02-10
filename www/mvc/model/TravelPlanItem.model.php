<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class TravelPlanItem_Model extends \Sys\Model {
    
    const TRAVEL_PLAN_STATUS_PENDING = 0;
    const TRAVEL_PLAN_STATUS_STARTED = 1;
    const TRAVEL_PLAN_STATUS_COMPLETED = 2;

    public $id;
    public $excluido;

    public $date_record;
    public $travel_plan_id;
    public $lot_transport_id;
    public $lot_transport_item_id;
    public $block_id;
    public $status;
    public $date_completed;
    public $client_removed;
    public $wagon_number;

    protected $active_quarries;

    function __construct()
    {
        parent::__construct();

        $this->active_quarries = $this->SQLActiveQuarries();
    }

    function validation()
    {
        $validation = new Validation();
        
        if ((!$this->client_removed) && (!$this->travel_plan_id > 0))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the travel plan');
        }
        
        if (!$this->lot_transport_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the lot transport');
        }

        if (!$this->lot_transport_item_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the lot transport item');
        }

        if (!$this->block_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the block');
        }

        if (!$this->status > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the status');
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
        $query = DB::query('SELECT id FROM travel_plan_item WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO travel_plan_item (
                        date_record,
                        travel_plan_id,
                        lot_transport_id,
                        lot_transport_item_id,
                        block_id,
                        status,
                        date_completed,
                        client_removed,
                        wagon_number
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?
                    ) ';
            
            $dt_now = new DateTime('now');
            $dt_now = $dt_now->format('Y-m-d H:i:s');
            $params[] = $dt_now;
            $params[] = ((int)$this->travel_plan_id > 0 ? (int)$this->travel_plan_id : null);
            $params[] = (int)$this->lot_transport_id;
            $params[] = (int)$this->lot_transport_item_id;
            $params[] = (int)$this->block_id;
            $params[] = (int)$this->status;
            $params[] = (!empty($this->date_completed) ? $this->date_completed : null);
            $params[] = ($this->client_removed == 'true' ? 1 : 0);
            $params[] = $this->wagon_number;
            
            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();
            $this->date_record = $dt_now;

            return $this;
        }
        
        return array('validation' => $validation);
    }

    function update_wagon_number($block_id, $wagon_number){

        $sql = 'UPDATE
                    travel_plan_item
                SET
                    wagon_number = ?
                WHERE
                    block_id = ? ';

        // set
        $params[] = $wagon_number;
        $params[] = (int)$block_id;
        
        $query = DB::exec($sql, $params);

        return $block_id;
    }

    function start_shipping($travel_plan_id, $lot_transport_id, $lot_transport_item_id, $block_id, $invoice_item_id, $nf, $price, $travel_route_id=null, $wagon_number=null, $date_nf=null, $truck_id)
    {
        if (!empty($nf)) {

            // update NF e Price na Invoice
            if (isset($invoice_item_id) && ($invoice_item_id > 0)) {
                $invoice_item_model = $this->LoadModel('InvoiceItem', true);
                $invoice_item_model->populate($invoice_item_id);
                $invoice_item_model->nf = $nf;
                $invoice_item_model->price = $price;
                $invoice_item_model->date_nf = $date_nf;

                $invoice_item_model->save();


            }



            // update lot_transport_item
            $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
            $lot_transport_item_model->populate($lot_transport_item_id);
            $lot_transport_item_model->status = self::TRAVEL_PLAN_STATUS_STARTED;
            $lot_transport_item_model->last_travel_route_id = $travel_route_id;

           
            $lot_transport_item_model->save();

            // start shipping
            $this->travel_plan_id = $travel_plan_id;
            $this->lot_transport_id = $lot_transport_id;
            $this->lot_transport_item_id = $lot_transport_item_id;
            $this->block_id = $block_id;
            $this->wagon_number = $wagon_number;
            $this->status = self::TRAVEL_PLAN_STATUS_STARTED;

            //block
            $block_model = $this->LoadModel('Block', true);
            $block_model->populate($block_id);
            $block_model->truck_id = $truck_id;
            $block_model->save();
             
            return $this->insert();
        }
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
                            travel_plan_item
                        SET
                            travel_plan_id = ?,
                            lot_transport_id = ?,
                            lot_transport_item_id = ?,
                            block_id = ?,
                            status = ?,
                            date_completed = ?,
                            client_removed = ?,
                            wagon_number = ?
                        WHERE
                            id = ? ';
                // set
                $params[] = ((int)$this->travel_plan_id > 0 ? (int)$this->travel_plan_id : null);
                $params[] = (int)$this->lot_transport_id;
                $params[] = (int)$this->lot_transport_item_id;
                $params[] = (int)$this->block_id;
                $params[] = (int)$this->status;
                $params[] = (!empty($this->date_completed) ? $this->date_completed : null);
                $params[] = ($this->client_removed == 'true' ? true : false);
                $params[] = $this->wagon_number;

                // where
                $params[] = (int)$this->id;

                $query = DB::exec($sql, $params);

                return $this;
            }
        }
        
        return array('validation' => $validation);
    }

    function mark_completed()
    {
        // update lot_transport_item
        $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
        $lot_transport_item_model->populate($this->lot_transport_item_id);
        $lot_transport_item_model->status = self::TRAVEL_PLAN_STATUS_COMPLETED;
        $lot_transport_item_model->save();

        $this->status = self::TRAVEL_PLAN_STATUS_COMPLETED;
        $dt_now = new DateTime('now');
        $dt_now = $dt_now->format('Y-m-d H:i:s');
        $this->date_completed = $dt_now;

        return $this->update();
    }

    function client_removed($lot_transport_id, $lot_transport_item_id, $block_id, $invoice_item_id, $nf, $price, $wagon_number)
    {

        if (!empty($nf)) {
            // update NF e Price na Invoice
            if (isset($invoice_item_id) && ($invoice_item_id > 0)) {

                $invoice_item_model = $this->LoadModel('InvoiceItem', true);
                $invoice_item_model->populate($invoice_item_id);

                $invoice_item_model->nf = $nf;
                $invoice_item_model->price = $price;

                $invoice_item_model->save();

            }

            // update lot_transport_item
            $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
            $lot_transport_item_model->populate($lot_transport_item_id);
            $lot_transport_item_model->status = self::TRAVEL_PLAN_STATUS_COMPLETED;
            $lot_transport_item_model->client_removed = 'true';
            $lot_transport_item_model->save();

            // pointing
            $this->travel_plan_id = null;
            $this->lot_transport_id = $lot_transport_id;
            $this->lot_transport_item_id = $lot_transport_item_id;
            $this->block_id = $block_id;
            $this->status = self::TRAVEL_PLAN_STATUS_COMPLETED;
            $dt_now = new DateTime('now');
            $dt_now = $dt_now->format('Y-m-d H:i:s');
            $this->date_completed = $dt_now;
            $this->client_removed = 'true';
            

            return $this->insert();
        }        
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
            $sql = 'UPDATE travel_plan_item SET excluido = ? WHERE id = ? ';
            
            $params[] = 'S';
            $params[] = $this->id;

            $query = DB::exec($sql, $params);
            
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
                    date_record,
                    travel_plan_id,
                    lot_transport_id,
                    lot_transport_item_id,
                    block_id,
                    status
                FROM
                    travel_plan_item
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
            $this->date_record = (string)$row_query['date_record'];
            $this->travel_plan_id = (empty($row_query['travel_plan_id']) ? null : (int)$row_query['travel_plan_id']);
            $this->lot_transport_id = (int)$row_query['lot_transport_id'];
            $this->lot_transport_item_id = (int)$row_query['lot_transport_item_id'];
            $this->block_id = (int)$row_query['block_id'];
            $this->status = (int)$row_query['status'];
            
            
        }
    }

    // listar blocos do lote para apontamento
    function get_list_pending()
    {
        $sql = "SELECT

                    lot_transport_item.*,
                    
                    -- local anterior
                    previous_travel_route.id AS previous_travel_route_id,
                    IF (previous_travel_route.end_quarry_id IS NOT NULL, previous_quarry.name, previous_terminal.name) AS previous_location,
                    previous_travel_route.end_quarry_id AS previous_quarry_id,
                    previous_quarry.name AS previous_quarry_name,
                    previous_travel_route.end_terminal_id AS previous_terminal_id,
                    previous_terminal.name AS previous_terminal_name,
                    previous_terminal.wagon_number AS previous_terminal_wagon_number,
                    
                    -- destino atual
                    current_travel_route.id AS current_travel_route_id,
                    IF (current_travel_route.end_quarry_id IS NOT NULL, current_quarry.name, current_terminal.name) AS current_location,
                    current_travel_route.end_quarry_id AS current_quarry_id,
                    current_quarry.name AS current_quarry_name,
                    current_travel_route.end_terminal_id AS current_terminal_id,
                    current_terminal.name AS current_terminal_name,
                    current_terminal.wagon_number AS current_terminal_wagon_number,
                    
                    -- proximo destino
                    next_travel_route.id AS next_travel_route_id,
                    IF (next_travel_route.end_quarry_id IS NOT NULL, next_quarry.name, next_terminal.name) AS next_location,
                    next_travel_route.end_quarry_id AS next_quarry_id,
                    next_quarry.name AS next_quarry_name,
                    next_travel_route.end_terminal_id AS next_terminal_id,
                    next_terminal.name AS next_terminal_name,
                    next_terminal.wagon_number AS next_terminal_wagon_number,
                    client_id, 
                    client_name

                FROM (
                    SELECT
                        lot_transport_item.id,
                        lot_transport_item.id AS lot_transport_item_id,
                        lot_transport_item.excluido,
                        lot_transport_item.lot_transport_id,
                        lot_transport.date_record,
                        lot_transport.lot_number,
                        lot_transport.client_id,
                        client.name AS client_name,
                        lot_transport.client_remove,
                        lot_transport.down_packing_list,
                        lot_transport.down_commercial_invoice,
                        lot_transport.down_draft,
                        lot_transport.draft_file,
                        coalesce(lot_transport.order_number, 99999999) AS order_number,
                        lot_transport_item.block_id,
                        block.block_number,
                        block.tot_weight,
                        block.quarry_id,
                        quarry.name AS quarry_name,
                        block.product_id,
                        block.quality_id,
                        block.tot_c,
                        block.tot_a,
                        block.tot_l,
                        block.tot_vol,
                        block.sale_net_c,
                        block.sale_net_a,
                        block.sale_net_l,
                        block.sale_net_vol,
                        block.truck_id,
                        quality.name AS quality_name,
                        production_order.date_production,
                        invoice.id AS invoice_id,
                        invoice_item.id AS invoice_item_id,
                        invoice_item.nf AS invoice_item_nf,
                        invoice_item.date_nf AS invoice_date_nf,
                        invoice_item.price AS invoice_item_price,
                        invoice.date_record AS invoice_date_record,
                    
                        
                        -- viagem anterior do bloco
                        (
                            SELECT previous_tpi.travel_plan_id FROM travel_plan_item AS previous_tpi
                            WHERE previous_tpi.lot_transport_item_id = lot_transport_item.id
                            AND previous_tpi.status = 2
                            AND previous_tpi.excluido = 'N'
                            ORDER BY previous_tpi.id ASC
                            LIMIT 0, 1
                        ) AS previous_travel_plan_id,

                        -- viagem atual do bloco
                        (
                            SELECT current_tpi.travel_plan_id FROM travel_plan_item AS current_tpi
                            WHERE current_tpi.lot_transport_item_id = lot_transport_item.id
                            AND (current_tpi.status = 0 OR current_tpi.status = 1)
                            AND current_tpi.excluido = 'N'
                            ORDER BY current_tpi.id ASC
                            LIMIT 0, 1
                        ) AS current_travel_plan_id,
                        (
                            SELECT current_tpi.id FROM travel_plan_item AS current_tpi
                            WHERE current_tpi.lot_transport_item_id = lot_transport_item.id
                            AND (current_tpi.status = 0 OR current_tpi.status = 1)
                            AND current_tpi.excluido = 'N'
                            ORDER BY current_tpi.id ASC
                            LIMIT 0, 1
                        ) AS current_travel_plan_item_id,
                            (
                            SELECT current_tpi.wagon_number FROM travel_plan_item AS current_tpi
                            WHERE current_tpi.lot_transport_item_id = lot_transport_item.id
                            AND (current_tpi.status = 0 OR current_tpi.status = 1)
                            AND current_tpi.excluido = 'N'
                            ORDER BY current_tpi.id ASC
                            LIMIT 0, 1
                        ) AS current_travel_plan_item_wagon_number,
                        COALESCE((

                            SELECT current_tpi.status FROM travel_plan_item AS current_tpi
                            WHERE current_tpi.lot_transport_item_id = lot_transport_item.id
                            AND ((current_tpi.status = 0 OR current_tpi.status = 1) OR lot_transport.client_remove = TRUE)
                            AND current_tpi.excluido = 'N'
                            ORDER BY current_tpi.id ASC
                            LIMIT 0, 1
                        ), 0) AS current_travel_plan_status,
                        
                        -- proximo apontamento para o bloco
                        (
                            SELECT travel_plan.id
                            FROM travel_plan
                            WHERE travel_plan.lot_transport_id = lot_transport_item.lot_transport_id
                            AND travel_plan.excluido = 'N'
                            AND travel_plan.id NOT IN (
                                SELECT tpi.travel_plan_id FROM travel_plan_item AS tpi
                                WHERE tpi.lot_transport_item_id = lot_transport_item.id
                                AND tpi.excluido = 'N'
                            )
                            ORDER BY travel_plan.id ASC
                            LIMIT 0, 1
                        ) AS next_travel_plan_id

                    FROM lot_transport_item
                    INNER JOIN lot_transport ON (lot_transport.id = lot_transport_item.lot_transport_id)
                    INNER JOIN block ON (block.id = lot_transport_item.block_id AND block.quarry_id IN ({$this->active_quarries}))
                    INNER JOIN production_order_item ON (production_order_item.id = block.production_order_item_id)
                    INNER JOIN production_order ON (production_order.id = production_order_item.production_order_id)
                    INNER JOIN quarry ON (quarry.id = block.quarry_id)
                    INNER JOIN quality ON (quality.id = block.quality_id)
                    INNER JOIN invoice_item ON (invoice_item.block_id = block.id AND invoice_item.excluido = 'N')
                    INNER JOIN invoice ON (invoice.id = invoice_item.invoice_id)
                    INNER JOIN client ON (client.id = lot_transport.client_id)
                    WHERE
                        lot_transport.status != 0 -- não exibo LOTES rascunhos
                        AND (lot_transport.status != 3 -- não exibo LOTES entregues
                            OR lot_transport.down_commercial_invoice = FALSE -- ou se possui download do commercial invoice para realizar
                            OR lot_transport.down_packing_list = FALSE -- ou se possui download do packing list para realizar
                        )
                        AND lot_transport_item.dismembered != TRUE

                        /*AND (lot_transport_item.status != 2
                            OR (
                                SELECT travel_plan.id
                                FROM travel_plan
                                WHERE travel_plan.lot_transport_id = lot_transport_item.lot_transport_id
                                AND travel_plan.excluido = 'N'
                                AND travel_plan.id NOT IN (
                                    SELECT tpi.travel_plan_id FROM travel_plan_item AS tpi
                                    WHERE tpi.lot_transport_item_id = lot_transport_item.id
                                    AND tpi.excluido = 'N'
                                )
                                LIMIT 0, 1
                            ) IS NOT NULL
                        )*/
                        
                        AND lot_transport.excluido = 'N'
                        AND lot_transport_item.excluido = 'N'

                        -- somente blocos que possuem roteiro de viagem
                        AND (lot_transport_item.lot_transport_id IN (
                                SELECT travel_plan.lot_transport_id
                                FROM travel_plan
                                WHERE travel_plan.excluido = 'N'
                            )
                            -- ou se cliente irá remover o lote na pedreira
                            OR lot_transport.client_remove = true
                        )

                    ORDER BY
                        lot_transport_item.lot_transport_id ASC,
                        lot_transport_item.id ASC
                ) AS lot_transport_item

                LEFT JOIN travel_plan AS previous_travel_plan ON (previous_travel_plan.id = lot_transport_item.previous_travel_plan_id)
                LEFT JOIN travel_route AS previous_travel_route ON (previous_travel_route.id = previous_travel_plan.travel_route_id)
                LEFT JOIN quarry AS previous_quarry ON (previous_quarry.id = previous_travel_route.end_quarry_id)
                LEFT JOIN terminal AS previous_terminal ON (previous_terminal.id = previous_travel_route.end_terminal_id)

                LEFT JOIN travel_plan AS current_travel_plan ON (current_travel_plan.id = lot_transport_item.current_travel_plan_id)
                LEFT JOIN travel_route AS current_travel_route ON (current_travel_route.id = current_travel_plan.travel_route_id)
                LEFT JOIN quarry AS current_quarry ON (current_quarry.id = current_travel_route.end_quarry_id)
                LEFT JOIN terminal AS current_terminal ON (current_terminal.id = current_travel_route.end_terminal_id)

                LEFT JOIN travel_plan AS next_travel_plan ON (next_travel_plan.id = lot_transport_item.next_travel_plan_id)
                LEFT JOIN travel_route AS next_travel_route ON (next_travel_route.id = next_travel_plan.travel_route_id)
                LEFT JOIN quarry AS next_quarry ON (next_quarry.id = next_travel_route.end_quarry_id)
                LEFT JOIN terminal AS next_terminal ON (next_terminal.id = next_travel_route.end_terminal_id)

                ORDER BY
                    order_number, lot_transport_id ASC,
                    lot_transport_item_id ASC
                ";

        $query = DB::query($sql);


        foreach ($query as $key => $row) {
            $sql = "SELECT client_group_id, client_group.name FROM client_group_client
                    INNER JOIN client_group on (client_group.id = client_group_client.client_group_id)
                    WHERE client_id = :client_id and excluido ='N'";

            $params = array();
            $params['client_id'] = $row['client_id'];

            $query_client_group = DB::query($sql, $params);

            $query[$key]['client_groups'] = $query_client_group;
        }

        
        return $query;
    }

    // listar blocos do lote para apontamento
    function get_history($lot_transport_id)
    {
        $sql = "SELECT
                    tpi.id, tpi.date_record AS date_history, tpi.travel_plan_id, tpi.lot_transport_item_id, tpi.block_id,
                    IF (tpi.client_removed = true, 2, 1) AS status,
                    travel_route.id AS travel_route_id,
                    -- start
                    IF (travel_route.start_quarry_id IS NOT NULL, start_quarry.name, start_terminal.name) AS start_location,
                    travel_route.start_quarry_id AS start_quarry_id,
                    start_quarry.name AS start_quarry_name,
                    travel_route.start_terminal_id AS start_terminal_id,
                    start_terminal.name AS start_terminal_name,
                    -- end
                    IF (travel_route.end_quarry_id IS NOT NULL, end_quarry.name, end_terminal.name) AS end_location,
                    travel_route.end_quarry_id AS end_quarry_id,
                    end_quarry.name AS end_quarry_name,
                    travel_route.end_terminal_id AS end_terminal_id,
                    end_terminal.name AS end_terminal_name,
                    block.block_number,
                    tpi.client_removed
                FROM travel_plan_item AS tpi
                INNER JOIN block ON (block.id = tpi.block_id AND block.quarry_id IN ({$this->active_quarries}))
                LEFT JOIN travel_plan ON (travel_plan.id = tpi.travel_plan_id)
                LEFT JOIN travel_route AS travel_route ON (travel_route.id = travel_plan.travel_route_id)
                LEFT JOIN quarry AS end_quarry ON (end_quarry.id = travel_route.end_quarry_id)
                LEFT JOIN terminal AS end_terminal ON (end_terminal.id = travel_route.end_terminal_id)
                LEFT JOIN quarry AS start_quarry ON (start_quarry.id = travel_route.start_quarry_id)
                LEFT JOIN terminal AS start_terminal ON (start_terminal.id = travel_route.start_terminal_id)
                WHERE
                    tpi.excluido = 'N'
                    AND tpi.lot_transport_id = ?
                UNION ALL

                -- status 2
                SELECT tpi.id, tpi.date_completed AS date_history, tpi.travel_plan_id, tpi.lot_transport_item_id, tpi.block_id,
                    tpi.status,
                    travel_route.id AS travel_route_id,
                    -- start
                    IF (travel_route.start_quarry_id IS NOT NULL, start_quarry.name, start_terminal.name) AS start_location,
                    travel_route.start_quarry_id AS start_quarry_id,
                    start_quarry.name AS start_quarry_name,
                    travel_route.start_terminal_id AS start_terminal_id,
                    start_terminal.name AS start_terminal_name,
                    -- end
                    IF (travel_route.end_quarry_id IS NOT NULL, end_quarry.name, end_terminal.name) AS end_location,
                    travel_route.end_quarry_id AS end_quarry_id,
                    end_quarry.name AS end_quarry_name,
                    travel_route.end_terminal_id AS end_terminal_id,
                    end_terminal.name AS end_terminal_name,
                    block.block_number,
                    0 AS client_removed
                FROM travel_plan_item AS tpi
                INNER JOIN block ON (block.id = tpi.block_id AND block.quarry_id IN ({$this->active_quarries}))
                LEFT JOIN travel_plan ON (travel_plan.id = tpi.travel_plan_id)
                LEFT JOIN travel_route AS travel_route ON (travel_route.id = travel_plan.travel_route_id)
                LEFT JOIN quarry AS end_quarry ON (end_quarry.id = travel_route.end_quarry_id)
                LEFT JOIN terminal AS end_terminal ON (end_terminal.id = travel_route.end_terminal_id)
                LEFT JOIN quarry AS start_quarry ON (start_quarry.id = travel_route.start_quarry_id)
                LEFT JOIN terminal AS start_terminal ON (start_terminal.id = travel_route.start_terminal_id)

                WHERE
                    tpi.status = 2
                    AND tpi.excluido = 'N'
                    AND tpi.lot_transport_id = ?
                    AND tpi.client_removed = 0

                ORDER BY date_history DESC, id DESC, status, block_number
                ";
        
        // -- AND (DATE(tpi.date_record) BETWEEN ? AND ?)
        // $params[] = $start_date;
        // $params[] = $end_date;
        
        // $params[] = $start_date;
        // $params[] = $end_date;

        $params[] = $lot_transport_id;
        $params[] = $lot_transport_id;

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}