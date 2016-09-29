<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;
use \Sys\Util;

class Invoice_Model extends \Sys\Model {

    public $id;
    public $excluido;

    public $client_id;
    //public $number;
    public $date_record;
    public $poblo_obs;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->client_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid client');
        }
        /*
        if (strlen($this->number) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid number');
        }
        
        if (is_null($this->date_record))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Inform record date');
        }
        */
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
	                invoice
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
            $sql = 'INSERT INTO invoice (
	                    client_id,
                        date_record,
                        poblo_obs
	                ) VALUES (
	                    ?, ?, ?
	                ) ';
                        
            $dt_now = new DateTime('now');
            $params[] = $this->client_id;
            $params[] = $dt_now->format('Y-m-d H:i:s');
            $params[] = $this->poblo_obs;

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
                $sql = 'UPDATE
	                        invoice
	                    SET
	                        client_id = ?,
                            date_record = ?,
                            poblo_obs = ?
	                    WHERE
	                        id = ?
	                    ';
                $query = DB::exec($sql, array(
                    // set
                    $this->client_id,
                    $this->date_record,
                    $this->poblo_obs,
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
        
        $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');

        if (!$this->exists())
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Id does not exists');
        }
        else
        {
            // ajusto os blocos para poderem ser inspecionados novamente
            $invoice_item_model = $this->LoadModel('InvoiceItem', true);
            $items = $invoice_item_model->get_by_invoice($this->id);
            foreach ($items as $key => $item) {
               // Não permitimos deletar se o block estiver em algum lote
                $sql = 'SELECT 
                            block_id
                        FROM lot_transport_item
                        INNER JOIN lot_transport on 
                        lot_transport.id = lot_transport_item.lot_transport_id 
                        AND lot_transport.excluido = "N"
                        AND block_id = ?
                        ';
                $query = DB::query($sql, array(
                    // where
                    $item['block_id']
                ));
                
                if (DB::has_rows($query))
                {
                    return 0;
                }

                $block_model = $this->LoadModel('Block', true);
                $block_model->populate($item['block_id']);
                $block_model->sale_net_c = 0;
                $block_model->sale_net_a = 0;
                $block_model->sale_net_l = 0;
                $block_model->sale_net_vol = 0;
                $block_model->sold = false;
                $block_model->sold_client_id = null;
                $block_model->save();
            }
            
            $sql = 'UPDATE
	                    invoice
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
	                    client_id,
	                    date_record,
                        poblo_obs
	                FROM
	                    invoice
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

            $this->client_id = (int)$row_query['client_id'];
            $this->date_record = (string)$row_query['date_record'];
            $this->poblo_obs = (string)$row_query['poblo_obs'];
        }
    }

    function reinspection($block_id, $tot_c, $tot_a, $tot_l, $net_c, $net_a, $net_l,
                            $sale_net_c, $sale_net_a, $sale_net_l, $tot_vol, $tot_weight, $net_vol, $sale_net_vol)
    {
        $block_model = $this->LoadModel('Block', true);
        
        if ($block_id > 0) {
            $block_model->populate($block_id);

            // salvo o bloco
            $block_model->tot_c = $tot_c;
            $block_model->tot_a = $tot_a;
            $block_model->tot_l = $tot_l;
            $block_model->tot_vol = $tot_vol;
            $block_model->tot_weight = $tot_weight;
            $block_model->net_c = $net_c;
            $block_model->net_a = $net_a;
            $block_model->net_l = $net_l;
            $block_model->net_vol = $net_vol;
            $block_model->sale_net_c = $sale_net_c;
            $block_model->sale_net_a = $sale_net_a;
            $block_model->sale_net_l = $sale_net_l;
            $block_model->sale_net_vol = $sale_net_vol;
            $block_model->reinspection = Util::DateNow();
            //print_r($block_model);exit;
            $block_model->save();

            // descubro invoice_item_id do bloco
            $sql = "SELECT id as invoice_item_id FROM invoice_item
                    WHERE block_id = :block_id
                    AND excluido = 'N' ";
            $params[':block_id'] = $block_id;

            $query = DB::query($sql, $params);

            $invoice_item_id = null;
            if (DB::has_rows($query)) {
                $invoice_item_id = $query[0]['invoice_item_id'];
            }

            // se encontrou o invoice item id
            if (!is_null($invoice_item_id)) {
                // salvo os novos valores na invoice_item
                $invoice_item_model = $this->LoadModel('InvoiceItem', true);
                $invoice_item_model->populate($invoice_item_id);
                
                $invoice_item_model->tot_c = $tot_c;
                $invoice_item_model->tot_a = $tot_a;
                $invoice_item_model->tot_l = $tot_l;
                $invoice_item_model->tot_vol = $tot_vol;
                $invoice_item_model->tot_weight = $tot_weight;
                $invoice_item_model->net_c = $net_c;
                $invoice_item_model->net_a = $net_a;
                $invoice_item_model->net_l = $net_l;
                $invoice_item_model->net_vol = $net_vol;
                $invoice_item_model->sale_net_c = $sale_net_c;
                $invoice_item_model->sale_net_a = $sale_net_a;
                $invoice_item_model->sale_net_l = $sale_net_l;
                $invoice_item_model->sale_net_vol = $sale_net_vol;
                
                $invoice_item_model->save();
            }
        }        
    }
    
    function get_list($excluido=false, $client_id=null, $ano, $mes)
    {
        
        
        $sql = 'SELECT
                    invoice.id,
                    invoice.excluido,
                    invoice.client_id,
                    client.code AS client_code,
                    client.name AS client_name,
                    invoice.date_record,
                    invoice.poblo_obs
                FROM invoice
                INNER JOIN client ON (client.id = invoice.client_id)
                WHERE
                    invoice.excluido = ?
                ';

         $params[] = ($excluido === true ? 'S' : 'N');

         if ($client_id) {
            $sql .= ' AND invoice.client_id = ? ';
            $params[] = $client_id;
        }

        if ($ano){

            $sql .= 'AND Year(invoice.date_record) = ?';
            $params[] = $ano;
        }

        if ($mes){

            $sql .= 'AND Month(invoice.date_record) = ?';
            $params[] = $mes;
        }

        $sql .= ' ORDER BY invoice.date_record DESC, invoice.id DESC';

        $query = DB::query($sql, $params);
        
        return $query;
    }

    function save_email_notification($arr_email_notification){

        $sql = 'DELETE FROM email_notification WHERE id > 0 ';

        $query = DB::exec($sql);
                                
        $sql = 'INSERT INTO email_notification 
                (
                    excluido,
                    email_notification,
                    client_group_id

                )
                VALUES
                (
                    ?,
                    ?,
                    ?
                )';
        
        foreach ($arr_email_notification as $key => $value) {

            if(is_object($value)){
                $value = (array)$value;
            }

            $query = DB::exec(
                                $sql, 
                                array(
                                        $value['excluido'],
                                        $value['email_notification'],
                                        $value['client_group_id']
                                     )
                               );
        }

        return $arr_email_notification;
    }

    function list_email_notification($client_id = -1, $just_mails = false){

        $params = array();

        $sql = 'SELECT 
                    email_notification.excluido,
                    email_notification.email_notification,
                    email_notification.client_group_id,
                    client_group.name
                FROM email_notification
                INNER JOIN client_group ON (client_group.id = email_notification.client_group_id)
                WHERE 
                    email_notification.excluido = "N" ';

                if($client_id > 0){
                    $sql .= ' AND client_group_id IN (
                                                        SELECT 
                                                            client_group_id
                                                        FROM client_group_client
                                                        WHERE client_id = :client_id 
                                                     )';
                    
                    $params[':client_id'] = $client_id;
                }


        $sql .= 'ORDER BY client_group.name';

        $group_clients_emails = DB::query($sql, $params);

        $sql = 'SELECT 
                    email_notification.excluido,
                    email_notification.email_notification
                FROM email_notification
                WHERE 
                    email_notification.excluido = "N" 
                    AND email_notification.client_group_id IS NULL';
                    

        $emails = DB::query($sql);

        $query = array_merge($emails, $group_clients_emails);

        if($just_mails === true){
            
            $emails = '';

            foreach ($query as $key => $value) {
                if($emails != ''){
                    $emails = $emails .';'.$value['email_notification'];
                }else{
                    $emails = $value['email_notification'];
                }
            }

           
            if (strlen($emails) > 0) {
                $emails = str_replace(",", ";", $emails);
                $array_emails = explode(';', $emails);

                $array_emails = array_unique($array_emails);
                // remove e-mails duplicados
                $emails = implode(';', $array_emails);
                return $emails;
            }
        }

        return $query;
    }

    function get_clients()
    {
        $sql = 'SELECT
                    invoice.client_id,
                    client.code AS client_code,
                    client.name AS client_name,
                    invoice.poblo_obs
                FROM invoice
                INNER JOIN client ON (client.id = invoice.client_id)
                WHERE
                    invoice.excluido = \'N\'
                GROUP BY invoice.client_id
                ORDER BY client.name';

        $query = DB::query($sql);

        return $query;
    }
    
}