<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;
use \Sys\Util;

class LotTransport_Model extends \Sys\Model {

    const LOT_TRANSPORT_STATUS_DRAFT = 0;
    const LOT_TRANSPORT_STATUS_RELEASED = 1;
    const LOT_TRANSPORT_STATUS_TRAVEL_STARTED = 2;
    const LOT_TRANSPORT_STATUS_DELIVERED = 3;

    public $id;
    public $excluido;

    public $date_record;
    public $lot_number;
    public $client_id;
    public $client_remove;
    public $local_market;
    public $status;
    public $order_number;

    public $client_name;
    public $items;
    public $tot_weight;

    public $down_packing_list;
    public $down_commercial_invoice;
    public $down_draft;

    public $shipped_from;
    public $shipped_to;

    public $client_notify_address;
    public $client_consignee;
    public $bl;
    public $packing_list_dated;
    public $vessel;
    public $commercial_invoice_date;
    public $commercial_invoice_number;
    public $packing_list_ref;

    public $draft_file;
    public $draft_type;
    public $draft_size;
    
    function __construct()
    {
        parent::__construct();

        $this->active_quarries = $this->SQLActiveQuarries();
    }

    function validation()
    {
        $validation = new Validation();

        if (strlen($this->lot_number) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid lot number');
        }

        if (!$this->client_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid client');
        }
        
        return $validation;
    }
    
    function save($file = null, $ignore_itens = false)
    {


        if (!$this->exists())
        {
            return $this->insert($ignore_itens);
        }
        else
        {
            return $this->update($file, $ignore_itens);
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
                    lot_transport
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

    function exists_lot_number($lot_number)
    {
        $sql = 'SELECT
                    id
                FROM
                    lot_transport
                WHERE
                    left(lot_number, 9) = ?
                ';
        $query = DB::query($sql, array(
            // where
            substr($lot_number, 0, 9)
        ));
        
        if (DB::has_rows($query))
        {
            return $query[0]['id'];
        }
        return false;
    }
    
    function insert($ignore_itens = false)
    {
        $validation = $this->validation();

        if ($validation->isValid())
        {
            $sql = 'INSERT INTO lot_transport (
                        date_record,
                        lot_number,
                        client_id,
                        client_remove
	                ) VALUES (
	                    ?, ?, ?, ?
	                ) ';
                        
            $dt_now = new DateTime('now');
            $params[] = $dt_now->format('Y-m-d H:i:s');
            $params[] = (string)$this->lot_number;
            $params[] = (int)$this->client_id;
            $params[] = ($this->client_remove == 'true' ? 1 : 0);

            $query = DB::exec($sql, $params);

            $this->id = DB::last_insert_id();

            if($ignore_itens == false){
                $this->save_items();
            }
            

            return $this;
        }
        
        return array('validation' => $validation);
    }
    
    function update($file, $ignore_itens = false)
    {
        $validation = $this->validation();
        if(!is_null($file)){
            $this->draft_file = $file['name'];;
            $this->draft_type = $file['type'];
            $this->draft_size = $file['size'];
        }
        
        if ($validation->isValid())
        {
            $validation = new Validation();

            if (!$this->exists()) {
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
            }
            else if( ($this->status > 0) && (is_null($file))) {
                $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Is not possible to delete a lot with the current status');
            }
            else
            {

                if(!is_null($file)){
                    $destino = DRAFT_PATH . '/anx_' . $this->id;

                    // mover arquivo temporario para o destino
                    move_uploaded_file($file['tmp_name'], $destino);

                }
                $sql = 'UPDATE
	                        lot_transport
	                    SET
                            lot_number = ?,
                            client_id = ?,
                            client_remove = ?,
                            local_market = ?,
                            status = ?,
                            draft_file = ?,
                            draft_type = ?,
                            draft_size = ?
	                    WHERE
	                        id = ?
	                    ';
                // set
                $params[] = (string)$this->lot_number;
                $params[] = (int)$this->client_id;
                $params[] = ($this->client_remove == 'true' ? 1 : 0);
                $params[] = ($this->local_market == 'true' ? 1 : 0);
                $params[] = (int)$this->status;
                $params[] = (string)$this->draft_file;
                $params[] = (string)$this->draft_type;
                $params[] = (int)$this->draft_size;
                // where
                $params[] = (int)$this->id;

                $query = DB::exec($sql, $params);

                if($ignore_itens == false){
                    $this->save_items();
                }

                return $this;
            }
        }
        
        return array('validation' => $validation);
    }

    function save_items() {
        // incluo os itens do lote
        if (($this->id > 0) && (!is_null($this->items))) {
            // apago os itens do lote
            $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
            $return_delete = $lot_transport_item_model->delete_by_lot_transport(false, $this);

            // se não existiu nenhum erro
            if (!(is_array($return_delete) && isset($return_delete['validation']))) {
                
                // adiciono os itens (lot_transport_item)
                foreach ($this->items as $key => $block) {
                    if(is_array($block)){
                        (object)$block;
                    }

                    $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
                    $lot_transport_item_model->lot_transport_id = $this->id;
                    $lot_transport_item_model->block_id = $block->block_id;
                    $lot_transport_item_model->invoice_item_id = $block->invoice_item_id;
                    $lot_transport_item_model->save();
                }
            }
        }
    }

    function validation_release($release_value)
    {
        $validation = new Validation();

        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else if ($this->status > 1) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Is not possible to change a lot with the current status');
        }
        else if ($release_value == 'true') {
            // verifico se existe alguma viagem para o lote ou está marcado com retirada pelo cliente
            $sql = 'SELECT id from lot_transport
                    WHERE lot_transport.id = ?
                    -- blocos que possuem roteiro de viagem
                    AND (lot_transport.id IN (
                        SELECT travel_plan.lot_transport_id
                        FROM travel_plan
                        WHERE travel_plan.excluido = \'N\')
                        -- ou se cliente irá remover o lote na pedreira
                        OR lot_transport.client_remove = true) ';
            $params[] = (int)$this->id;
            $query = DB::query($sql, $params);

            if (!DB::has_rows($query)) {
                $validation->add(Validation::VALID_ERR_FIELD, 'Set the travel plan of the lot before releasing');
            }
        }

        return $validation;
    }

    function set_down_packing_list() {
        $sql = 'UPDATE lot_transport SET down_packing_list = true WHERE id = ? ';
        $params[] = (int)$this->id;
        $query = DB::exec($sql, $params);
    }

    function set_down_commercial_invoice() {
        $sql = 'UPDATE lot_transport SET down_commercial_invoice = true WHERE id = ? ';
        $params[] = (int)$this->id;
        $query = DB::exec($sql, $params);
    }

    function set_down_draft() {
        $sql = 'UPDATE lot_transport SET down_draft = true WHERE id = ? ';
        $params[] = (int)$this->id;
        $query = DB::exec($sql, $params);
    }

    function set_packing_list($shipped_from, $client_notify_address, $bl, $dated, $vessel,
                              $commercial_invoice_number, $packing_list_ref) {
        $sql = 'UPDATE lot_transport SET
                    shipped_from = ?,
                    client_notify_address = ?,
                    bl = ?,
                    packing_list_dated = ?,
                    vessel = ?,
                    commercial_invoice_number = ?,
                    packing_list_ref = ?
                WHERE id = ? ';
        
        // set
        $params[] = (string)$shipped_from;
        $params[] = (string)$client_notify_address;
        $params[] = (string)$bl;
        $params[] = (!is_null($dated)  && $dated != '' ? (string)$dated : null);
        $params[] = (string)$vessel;
        $params[] = (string)$commercial_invoice_number;
        $params[] = (string)$packing_list_ref;
        // where
        $params[] = (int)$this->id;

        // executo o update no banco
        $query = DB::exec($sql, $params);

        // atualizo o objeto
        $this->shipped_from = (string)$shipped_from;
        $this->client_notify_address = (string)$client_notify_address;
        $this->bl = (string)$bl;
        $this->packing_list_dated = (!is_null($dated)  && $dated != '' ? (string)$dated : null);
        $this->vessel = (string)$vessel;

        $this->set_down_packing_list();

        // retorno o objeto
        return $this;
    }

    function set_commercial_invoice($shipped_from, $shipped_to, $client_notify_address,
                                    $client_consignee, $commercial_invoice_date, $vessel,
                                    $commercial_invoice_number, $packing_list_ref, $products) {
        $sql = 'UPDATE lot_transport SET
                    shipped_from = ?,
                    shipped_to = ?,
                    client_notify_address = ?,
                    client_consignee = ?,
                    commercial_invoice_date = ?,
                    commercial_invoice_number = ?,
                    vessel = ?,
                    packing_list_ref = ?
                WHERE id = ? ';

        // set
        $params[] = (string)$shipped_from;
        $params[] = (string)$shipped_to;
        $params[] = (string)$client_notify_address;
        $params[] = (string)$client_consignee;
        $params[] = (!is_null($commercial_invoice_date)  && $commercial_invoice_date != '' ? (string)$commercial_invoice_date : null);
        $params[] = (string)$commercial_invoice_number;
        $params[] = (string)$vessel;
        $params[] = (string)$packing_list_ref;
        // where
        $params[] = (int)$this->id;

        // executo o update no banco
        $query = DB::exec($sql, $params);

        // atualizo o objeto
        $this->shipped_from = (string)$shipped_from;
        $this->shipped_to = (string)$shipped_to;
        $this->client_notify_address = (string)$client_notify_address;
        $this->client_consignee = (string)$client_consignee;
        $this->commercial_invoice_date = (!is_null($commercial_invoice_date)  && $commercial_invoice_date != '' ? (string)$commercial_invoice_date : null);
        $this->commercial_invoice_number = (string)$commercial_invoice_number;
        $this->vessel = (string)$vessel;
        $this->packing_list_ref = (string)$packing_list_ref;

        // atualizo os itens da commercial invoice
        $commercial_invoice_item_model = $this->LoadModel('CommercialInvoiceItem', true);
        $commercial_invoice_item_model->save($this->id, $products);

        $this->set_down_commercial_invoice();

        // retorno o objeto
        return $this;
    }

    function release($value)
    {
        $validation = $this->validation_release($value);

        if ($validation->isValid())
        {
            // status update
            $sql = 'UPDATE lot_transport SET status = ? WHERE id = ? ';
            $params[] = ($value == 'true' ? self::LOT_TRANSPORT_STATUS_RELEASED : self::LOT_TRANSPORT_STATUS_DRAFT);
            $params[] = (int)$this->id;
            $query = DB::exec($sql, $params);

            // order
            if ($value == 'true') {
                $sql = 'UPDATE lot_transport AS a SET a.order_number = (SELECT new_order FROM (SELECT coalesce(max(b.order_number), 0) + 1 AS new_order FROM lot_transport AS b WHERE b.excluido = \'N\') AS b) WHERE a.id = ? ';
            }
            else {
                $sql = 'UPDATE lot_transport SET order_number = null WHERE id = ? ';
            }
            $params = array();
            $params[] = (int)$this->id;
            $query = DB::exec($sql, $params);

            return $this;
        }

        return array('validation' => $validation);
        
    }

    function change_order($type)
    {
        // id do item da posição atual
        $id_pos_atual = $this->id;

        // inicio as variaveis da posição atual e da nova posição
        $pos_atual = $this->order_number;

        // pesquiso id do item que está na futura nova posição
        $sql = 'SELECT id, order_number FROM lot_transport
                WHERE excluido = \'N\' 
                AND order_number ' . ($type == 'up' ? '<' : '>') . ' ?
                AND status IN (1,2)
                ORDER BY order_number ' . ($type == 'up' ? 'DESC' : 'ASC') . ' LIMIT 0, 1 ';
        $params = array();
        $params[] = $pos_atual;
        $query = DB::query($sql, $params);

        if (DB::has_rows($query))
        {
            $id_pos_nova = (int)$query[0]['id'];
            $pos_nova = (int)$query[0]['order_number'];
            // update do item que está na futura nova posição, com order_number do item que está posição atual
            $sql = 'UPDATE lot_transport SET order_number = ? WHERE id = ?';
            $params = array();
            $params[] = $pos_atual;
            $params[] = $id_pos_nova;
            $query = DB::exec($sql, $params);

            // update do item que está na posição atual, com order_number do item que está na futura nova posição
            $sql = 'UPDATE lot_transport SET order_number = ? WHERE id = ?';
            $params = array();
            $params[] = $pos_nova;
            $params[] = $id_pos_atual;
            $query = DB::exec($sql, $params);
        }

        // atualizo o objeto atual
        $this->populate($id_pos_atual);

        // retorno o objeto
        return $this;
    }

    function delete()
    {
        $validation = new Validation();
        
        if (!$this->exists()) {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else if ($this->status > 0) {
            $validation->add(Validation::VALID_ERR_FIELD, 'Is not possible to delete a lot with the current status');
        }
        else {
            $sql = 'UPDATE
	                    lot_transport
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

            // apago os itens do lote
            $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
            $lot_transport_item_model->delete_by_lot_transport(true, $this);

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
            $sql = "SELECT
	                    lot_transport.id,
	                    lot_transport.excluido,
	                    lot_transport.date_record,
                        lot_transport.lot_number,
                        lot_transport.client_id,
                        client.name AS client_name,
                        lot_transport.client_remove,
                        lot_transport.local_market,
                        lot_transport.status,
                        lot_transport.order_number,
                        lot_transport.tot_weight,
                        lot_transport.down_packing_list,
                        lot_transport.down_commercial_invoice,
                        lot_transport.down_draft,
                        lot_transport.shipped_from,
                        lot_transport.shipped_to,
                        IF (COALESCE(lot_transport.client_notify_address, '') != '', lot_transport.client_notify_address, client.notify_address) AS client_notify_address,
                        IF (COALESCE(lot_transport.client_consignee, '') != '', lot_transport.client_consignee, client.consignee) AS client_consignee,
                        lot_transport.bl,
                        lot_transport.packing_list_dated,
                        lot_transport.vessel,
                        lot_transport.commercial_invoice_date,
                        lot_transport.commercial_invoice_number,
                        lot_transport.packing_list_ref,
                        lot_transport.draft_file,
                        lot_transport.draft_type,
                        lot_transport.draft_size
	                FROM
	                    lot_transport
                    INNER JOIN client ON (client.id = lot_transport.client_id)
	                WHERE
	                    lot_transport.id = ?
                ";
            $query = DB::query($sql, array(
                // where
                $id
            ));
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);

                $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
                $this->items = $lot_transport_item_model->get_by_lot_transport($this->id);

                return $this->id;
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

            $this->date_record = (string)$row_query['date_record'];
            $this->lot_number = (string)$row_query['lot_number'];
            $this->client_id = (int)$row_query['client_id'];
            $this->client_name = (string)$row_query['client_name'];
            $this->client_remove = (bool)$row_query['client_remove'];
            $this->local_market = (bool)$row_query['local_market'];
            $this->status = (int)$row_query['status'];
            $this->order_number = (empty($row_query['order_number']) ? null : (int)$row_query['order_number']);
            $this->tot_weight = (float)$row_query['tot_weight'];
            
            $this->down_packing_list = (bool)$row_query['down_packing_list'];
            $this->down_commercial_invoice = (bool)$row_query['down_commercial_invoice'];
            $this->down_draft = (bool)$row_query['down_draft'];

            $this->shipped_from = (string)$row_query['shipped_from'];
            $this->shipped_to = (string)$row_query['shipped_to'];

            $this->client_notify_address = (string)$row_query['client_notify_address'];
            $this->client_consignee = (string)$row_query['client_consignee'];
            
            $this->bl = (string)$row_query['bl'];
            $this->packing_list_dated = (string)$row_query['packing_list_dated'];
            $this->vessel = (string)$row_query['vessel'];
            $this->commercial_invoice_date = (string)$row_query['commercial_invoice_date'];

            $this->commercial_invoice_number = (string)$row_query['commercial_invoice_number'];
            $this->packing_list_ref = (string)$row_query['packing_list_ref'];

            $this->draft_file = (String)$row_query['draft_file'];
            $this->draft_type = (String)$row_query['draft_type'];
            $this->draft_size = (String)$row_query['draft_size'];

        }
    }

    function next_val_lot_number()
    {
        $this->LoadModel('Parameters', false);

        $lot_prefix = Parameters_Model::get('lot_prefix');
        $lot_seq_ref = Parameters_Model::get('lot_seq_ref');
        $lot_seq = Parameters_Model::get('lot_seq');

        $lot_ref_atual = date('y');
        $dt_atual = date('Y-m-d');

        $lot_seq_ref = self::field_fill_date($lot_seq_ref);
        $lot_seq_ref = date_format($lot_seq_ref, 'y');

        $next_lot_seq = null;
        
        // verifico se é o mesmo ano anterior, se não for, atualizo a referencia e reinicio a sequencia
        if ($lot_ref_atual != $lot_seq_ref) {
            Parameters_Model::set('lot_seq_ref', $dt_atual);
            Parameters_Model::set('lot_seq', 1);
            $next_lot_seq = 1;
        }
        // se não só incremento a sequencia
        else
        {
            $next_lot_seq = Parameters_Model::next_val('lot_seq');
        }

        return $lot_prefix . $lot_ref_atual . '-' . str_pad($next_lot_seq, 3, "0", STR_PAD_LEFT);
    }

    function dismember($lot_number, $items)
    {
        // listar itens do lote original
        $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
        $orig_items = $lot_transport_item_model->get_by_lot_transport($this->id);

        // removo os que não foram selecionados
        for ($i=0; $i < sizeof($orig_items); $i++) { 
            $block = $orig_items[$i];
            $existe = false;
            foreach ($items as $key_item => $item) {
                if ($block['id'] == $item['id']) {
                    $existe = true;
                }
            }
            if (!$existe) {
                $orig_items[$i] = null;
            }
        }
        $orig_items = array_filter($orig_items);

        // veirifico se todos os que foram selecionados estão com o mesmo status e travel route
        $last_travel_route_id = null;
        $status = null;
        $validation = new Validation();
        foreach ($orig_items as $key => $item) {
            if (!is_null($last_travel_route_id)) {
                if (($last_travel_route_id != $item['last_travel_route_id']) || ($status != $item['status'])) {
                    $validation->add(Validation::VALID_ERR_FIELD, 'The blocks of the new lot should be in the same location and have the same status.');
                }
            }
            $last_travel_route_id = $item['last_travel_route_id'];
            $status = $item['status'];
        }

        if ($validation->isValid()) {
            
            // gerar novo lote com os itens selecionados
            $lot_transport_model = $this->LoadModel('LotTransport', true);
            $lot_transport_model->lot_number = $lot_number;
            $lot_transport_model->client_id = $this->client_id;
            $lot_transport_model->items = $orig_items;

            $new_lot = $lot_transport_model->save();

            if ($new_lot->id > 0) {
                foreach ($orig_items as $key => $item) {
                    // marco os itens do lote origem como desmembrado
                    $lot_transport_item_model = $this->LoadModel('LotTransportItem', true);
                    $lot_transport_item_model->populate($item['id']);
                    $lot_transport_item_model->dismembered = true;
                    $lot_transport_item_model->dismembered_lot_transport_id = $new_lot->id;
                    $lot_transport_item_model->save();
                }
            }
            
            // retornar o novo lote
            return $new_lot;
        }

        return array('validation' => $validation);
    }
    
    function get_list($excluido=false, $client_id=null, $limit = -1, $ano, $mes)
    {
        $sql = 'SELECT
                    lot_transport.id,
                    lot_transport.excluido,
                    lot_transport.date_record,
                    lot_transport.lot_number,
                    lot_transport.client_id,
                    client.name AS client_name,
                    lot_transport.client_remove,
                    lot_transport.local_market,
                    lot_transport.status,
                    lot_transport.order_number,
                    EXISTS (SELECT id FROM travel_plan_item AS tpi WHERE tpi.lot_transport_id = lot_transport.id AND excluido = \'N\') AS has_travel,
                    lot_transport.items_count,
                    lot_transport.down_packing_list,
                    lot_transport.down_commercial_invoice,
                    lot_transport.down_draft
                FROM lot_transport
                INNER JOIN client ON (client.id = lot_transport.client_id)
                WHERE
                    lot_transport.excluido = ?
                ';
        $params[] = ($excluido === true ? 'S' : 'N');

        if ($client_id) {
            $sql .= ' AND lot_transport.client_id = ? ';
            $params[] = $client_id;
        }

        if ($ano){

            $sql .= ' AND Year(lot_transport.date_record) = ? ';
            $params[] = $ano;
        }

        if ($mes){

            $sql .= ' AND Month(lot_transport.date_record) = ? ';
            $params[] = $mes;
        }

        $sql .= ' ORDER BY lot_transport.date_record DESC, lot_transport.id DESC ';
        
        if($limit > -1){
          
            $sql .= " LIMIT ". $limit ." , 50 ";
        }
      //  print_r($params);exit();

        $query = DB::query($sql, $params);
        
        return $query;
    }

    function get_poblo_obs($lot_number, $id, $quarry_id, $invoice_id) {
    	
    	$is_sobra_interim = strpos($lot_number, 'Iterim Sobracolumay') !== false;
    	$is_sobra_final = strpos($lot_number, 'Final Sobracolumay') !== false;
    	$is_inspection_certificate = strpos($lot_number, 'Inspection Certificate') !== false;
    	$is_transport = (!$is_sobra_interim && !$is_sobra_final && !$is_inspection_certificate);
        
        // se for sobra
        if ($is_sobra_interim || $is_sobra_final) {
            $quarry_model = $this->LoadModel('Quarry', true);
            $quarry_model->populate($quarry_id);
            // se for interim
            if ($is_sobra_interim == true) {
                return $quarry_model->poblo_obs_interim_sobra;
            }
            // se for final
            else if ($is_sobra_final == true) {
                return $quarry_model->poblo_obs_final_sobra;
            }
        }
        // se for certificado de inspeção
        else if ($is_inspection_certificate) {
            $invoice_model = $this->LoadModel('Invoice', true);
            $invoice_model->populate($invoice_id);
            return $invoice_model->poblo_obs;
        }
    	// se for transporte
    	else if ($is_transport) {
    		$sql = "SELECT
	                    lot_transport.poblo_obs
	                FROM 
	                    lot_transport
	                WHERE
                        lot_transport.excluido = 'N'
	                    AND lot_transport.id = :lot_transport_id
	                ";

	        $params[':lot_transport_id'] = $id;

	        $query = DB::query($sql, $params);

	        if (DB::has_rows($query)) {
	            return $query[0]['poblo_obs'];
	        }
    	}
        
        return '';
    }

    function set_poblo_obs($lot_number, $id, $quarry_id, $invoice_id, $obs) {
        
        $is_sobra_interim = strpos($lot_number, 'Iterim Sobracolumay') !== false;
        $is_sobra_final = strpos($lot_number, 'Final Sobracolumay') !== false;
        $is_inspection_certificate = strpos($lot_number, 'Inspection Certificate') !== false;
    	$is_transport = (!$is_sobra_interim && !$is_sobra_final && !$is_inspection_certificate);

        // se for sobra
        if ($is_sobra_interim || $is_sobra_final) {
            $quarry_model = $this->LoadModel('Quarry', true);
            $quarry_model->populate($quarry_id);

            // se for interim
            if ($is_sobra_interim) {
                $quarry_model->poblo_obs_interim_sobra = $obs;
                $quarry_model->save();
            }

            // se for final
            else if ($is_sobra_final) {
                $quarry_model->poblo_obs_final_sobra = $obs;
                $quarry_model->save();
            }
        }
        // se for certificado de inspeção
    	else if ($is_inspection_certificate) {
            $invoice_model = $this->LoadModel('Invoice', true);
            $invoice_model->populate($invoice_id);
            $invoice_model->poblo_obs = $obs;
            $invoice_model->save();
        }
        // se for transporte
    	else if ($is_transport) {
	        $sql = "UPDATE
	                    lot_transport
	                SET
	                    poblo_obs = :poblo_obs
	                WHERE
	                    lot_transport.id = :lot_transport_id";
	        
	        $params[':lot_transport_id'] = $id;
	        $params[':poblo_obs'] = $obs;

	        DB::exec($sql, $params);
	    }

	    return $this->get_poblo_obs($lot_number, $id, $quarry_id, $invoice_id);
    }


    function get_inspection_certificate($client_id = -1){

         $sql = "SELECT
                    block.id AS block_id,
                    block.block_number,
                    block.tot_weight,
                    block.quarry_id,
                    block.product_id,
                    block.quality_id,
                    block.tot_c,
                    block.tot_a,
                    block.tot_l,
                    block.tot_vol,
                    block.net_c,
                    block.net_a,
                    block.net_l,
                    block.net_vol,
                    block.sold,
                    block.obs_poblo,
                    block.sold_client_id,
                    sold_client.code AS sold_client_code,
                    sold_client.name AS sold_client_name,
                    quality.name AS quality_name,
                    production_order.date_production,
                    invoice.id AS invoice_id,
                    invoice_item.id AS invoice_item_id,
                    invoice_item.nf AS invoice_item_nf,
                    invoice_item.date_nf AS invoice_item_date_nf,
                    invoice_item.price AS invoice_item_price,
                    invoice_item.sale_net_c AS invoice_sale_net_c,
                    invoice_item.sale_net_a AS invoice_sale_net_a,
                    invoice_item.sale_net_l AS invoice_sale_net_l,
                    invoice_item.sale_net_vol AS invoice_sale_net_vol,
                    invoice_item.poblo_status_id,
                    invoice.date_record AS invoice_date_record,
                    invoice.poblo_obs AS invoice_poblo_obs,
                    CONCAT('Inspection Certificate #', invoice.id, ' - ', DATE_FORMAT(invoice.date_record, '%Y/%m/%d')) AS inspection_name,
                    poblo_status.cor AS cor_poblo_status
                    FROM block
                    LEFT JOIN client AS sold_client ON (sold_client.id = block.sold_client_id)
                    INNER JOIN production_order_item ON (production_order_item.id = block.production_order_item_id)
                    INNER JOIN production_order ON (production_order.id = production_order_item.production_order_id)
                    INNER JOIN quality ON (quality.id = block.quality_id)
                    INNER JOIN invoice_item ON (invoice_item.block_id = block.id AND invoice_item.excluido = 'N')
                    INNER JOIN invoice ON (invoice.id = invoice_item.invoice_id)
                    LEFT JOIN poblo_status ON (poblo_status.id = invoice_item.poblo_status_id)
                WHERE
                    block.quarry_id IN ({$this->active_quarries}) 
                    AND block.excluido = 'N' 
                    AND block.sold = 1 
                    AND block.current_lot_transport_id IS NULL ";

                $params = array();
                if($client_id > 0){
                    $sql .= " AND block.sold_client_id = :client_id ";
                    $params[':client_id'] = $client_id;
                }

                $sql .= " ORDER BY
                    invoice.date_record ASC,
                    block.quarry_id,
                    quality.order_number,
                    block.block_number ";

        $query = DB::query($sql , $params);
        foreach($query as $key => $row){

           $cor = Util::Colors($row['cor_poblo_status']);
           $query[$key]['cor_poblo_status_texto'] = $cor[1];
        }

        return $query;

    }

    function get_sobracolumay($client_id = -1){

        $block_model = $this->LoadModel('Block', true);

        $final_sobracolumay = $block_model->get_sobracolumay($block_model::BLOCK_TYPE_FINAL, null, $client_id);


        foreach ($final_sobracolumay as $key => $block) {
            $final_sobracolumay[$key]['lot_number'] = 'Final Sobracolumay - ' . $block['quarry_name'];
        }


        return $final_sobracolumay;

    }


    function get_lot($client_id = -1) {

        $sql = "SELECT
                    lot_transport_item.id,
                    lot_transport_item.id AS lot_transport_item_id,
                    lot_transport_item.excluido,
                    lot_transport_item.lot_transport_id,
                    lot_transport.status AS lot_transport_status,
                    lot_transport.poblo_obs,
                    lot_transport.date_record,
                    CONCAT('Lot Number ', lot_transport.lot_number) AS lot_number,
                    lot_transport.client_remove,
                    lot_transport.down_packing_list,
                    lot_transport.down_commercial_invoice,
                    lot_transport.down_draft,
                    lot_transport.draft_file,
                    lot_transport.vessel,
                    lot_transport.shipped_to,
                    lot_transport.packing_list_dated,
                    coalesce(lot_transport.order_number, 99999999) AS order_number,
                    block.id AS block_id,
                    block.block_number,
                    block.tot_weight,
                    block.quarry_id,
                    quarry.name AS quarry_name,
                    quarry.poblo_obs_interim_sobra,
                    quarry.poblo_obs_final_sobra,
                    quarry.poblo_obs_inspected_without_lot,
                    block.product_id,
                    block.quality_id,
                    block.tot_c,
                    block.tot_a,
                    block.tot_l,
                    block.tot_vol,
                    block.net_c,
                    block.net_a,
                    block.net_l,
                    block.net_vol,
                    block.sold,
                    block.sold_client_id,
                    block.obs_poblo,
                    sold_client.code AS sold_client_code,
                    sold_client.name AS sold_client_name,
                    quality.name AS quality_name,
                    production_order.date_production,
                    invoice.id AS invoice_id,
                    invoice_item.id AS invoice_item_id,
                    invoice_item.nf AS invoice_item_nf,
                    invoice_item.price AS invoice_item_price,
                    invoice_item.sale_net_c AS invoice_sale_net_c,
                    invoice_item.sale_net_a AS invoice_sale_net_a,
                    invoice_item.sale_net_l AS invoice_sale_net_l,
                    invoice_item.sale_net_vol AS invoice_sale_net_vol,
                    invoice_item.poblo_status_id,
                    invoice_item.date_nf AS invoice_date_nf,
                    invoice.date_record AS invoice_date_record,
                    invoice.poblo_obs AS invoice_poblo_obs,
                    poblo_status.cor AS cor_poblo_status,
                    lot_transport.client_id AS client_id,
                    client.code AS client_code,
                    client.name AS client_name,
                    block.wagon_number As block_wagon_number,
                    (
                        SELECT MIN(invoice.date_record)
                        from lot_transport_item
                        INNER JOIN invoice_item on invoice_item.id = lot_transport_item.invoice_item_id
                        INNER JOIN invoice on invoice.id = invoice_item.invoice_id
                        WHERE lot_transport_id = lot_transport.id
                        AND lot_transport_item.excluido = 'N'
                    ) AS menor_date_record,
                    (SELECT 
                         CONCAT('Inspection Certificate ' ,GROUP_CONCAT(DISTINCT CONCAT('#', i2.id, ' - ', DATE_FORMAT(i2.date_record, '%Y/%m/%d')) SEPARATOR ', '))
                    FROM block b2 
                        INNER JOIN lot_transport_item lti2 ON (lti2.block_id = b2.id)
                        INNER JOIN lot_transport lt2 ON (lt2.id = lti2.lot_transport_id)
                        LEFT JOIN invoice_item ii2 ON (ii2.block_id = b2.id AND ii2.excluido = 'N')
                        LEFT JOIN invoice i2 ON (i2.id = ii2.invoice_id)
                        WHERE lt2.id = lot_transport.id                       
                    )  AS inspection_name                                        
                    FROM block
                    LEFT JOIN client AS sold_client ON (sold_client.id = block.sold_client_id)
                    INNER JOIN lot_transport_item ON (lot_transport_item.block_id = block.id)
                    INNER JOIN lot_transport ON (lot_transport.id = lot_transport_item.lot_transport_id)
                    LEFT JOIN client ON (client.id = lot_transport.client_id)
                    INNER JOIN production_order_item ON (production_order_item.id = block.production_order_item_id)
                    INNER JOIN production_order ON (production_order.id = production_order_item.production_order_id)
                    INNER JOIN quarry ON (quarry.id = block.quarry_id)
                    INNER JOIN quality ON (quality.id = block.quality_id)
                    LEFT JOIN invoice_item ON (invoice_item.block_id = block.id AND invoice_item.excluido = 'N')
                    LEFT JOIN invoice ON (invoice.id = invoice_item.invoice_id)
                    LEFT JOIN poblo_status ON (poblo_status.id = invoice_item.poblo_status_id)
                WHERE
                    block.quarry_id IN ({$this->active_quarries})  
                    AND block.excluido = 'N' 
                    AND lot_transport.excluido = 'N'
                    AND lot_transport_item.excluido = 'N' ";

                    $params = array();
                    if($client_id > 0){

                        $sql .= " AND block.sold_client_id = :client_id ";
                        $params[':client_id'] = $client_id;

                    }else{
                        $sql .= "   AND (

                                            /*AND ((
                                                block.sold = 1 and block.current_lot_transport_id IS NULL
                                            )
                                            OR*/

                                            (
                                                lot_transport.client_remove = 1
                                                AND (SELECT COUNT(*) 
                                                     FROM lot_transport_item 
                                                     WHERE lot_transport_id = lot_transport.id) != (SELECT COUNT(*) 
                                                                                                   FROM lot_transport_item 
                                                                                                   WHERE lot_transport_id = lot_transport.id
                                                                                                   AND lot_transport_item.status = 2)
                                            )
                                    
                                            OR

                                            (
                                                lot_transport.client_remove != 1
                                                AND lot_transport.status != 0 -- não exibo LOTES rascunhos
                                                AND (
                                                        lot_transport.status != 3 -- não exibo LOTES entregues
                                                        OR lot_transport.down_commercial_invoice = FALSE -- ou se possui download do commercial invoice para realizar
                                                        OR lot_transport.down_packing_list = FALSE -- ou se possui download do packing list para realizar
                                                    )
                                                AND lot_transport_item.dismembered != TRUE
                                                AND lot_transport.excluido = 'N'
                                                AND lot_transport_item.excluido = 'N'
                                            )
                                        ) ";
                    }

                    

                $sql .= " ORDER BY

                    menor_date_record,
                    lot_transport.order_number,
                    block.quarry_id,
                    quality.order_number,
                    block.block_number
                    
                ";
   
        $query = DB::query($sql, $params);
        foreach($query as $key => $row){

           $cor = Util::Colors($row['cor_poblo_status']);
           $query[$key]['cor_poblo_status_texto'] = $cor[1];
        }

        return $query;
    }
    
}