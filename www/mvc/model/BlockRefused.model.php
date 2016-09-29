<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class BlockRefused_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $block_id;
    public $client_id;
    public $date_refuse;
    public $reason;
    public $invoice_id;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->block_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid block');
        }
        
        if (!$this->client_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid client');
        }

        if (strlen($this->reason) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid reason');
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
        $query = DB::query('SELECT id FROM block_refused WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO block_refused (
                        block_id,
                        client_id,
                        date_refuse,
                        reason
                    ) VALUES (
                        ?, ?, ?, ?
                    ) ';
            
            $dt_now = new DateTime('now');
            $query = DB::exec($sql, array(
                // values
                $this->block_id,
                $this->client_id,
                $dt_now->format('Y-m-d H:i:s'),
                $this->reason
            ));

            $this->id = DB::last_insert_id();


            if($this->invoice_id > 0){
                $this->delete_from_invoice($this->invoice_id, $this->block_id);
            } else {                    
                $this->delete_reservation($this->block_id);                        
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
                $sql = '
                    UPDATE
                        block_refused
                    SET
                        block_id = ?,
                        client_id = ?,
                        date_refuse = ?,
                        reason = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->block_id,
                    $this->client_id,
                    $this->date_refuse,
                    $this->reason,
                    // where
                    $this->id

                ));


                if($this->invoice_id > 0){
                    $this->delete_from_invoice($this->invoice_id, $this->block_id);
                } else {                    
                    $this->delete_reservation($this->block_id);                        
                }

                return $this->id;
            }
        }
        
        return $valid;
    }

    function delete_from_invoice($invoice_id, $block_id){
        $sql = "UPDATE invoice_item 
              SET excluido = 'S'
              WHERE invoice_id = ? 
              AND block_id = ? ";

        $query = DB::exec($sql, array($invoice_id, $block_id));

        $this->delete_reservation($block_id);        
    }

    function delete_reservation($block_id){
        $sql = " UPDATE block 
                  SET 
                  sold = 0,
                  sold_client_id = NULL,
                  reserved_client_id = NULL,
                  reserved = 0
              WHERE  id = " . $block_id;


        $query = DB::exec($sql);
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
            $sql = 'UPDATE block_refused SET excluido = ? WHERE id = ? ';
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
                    block_id,
                    client_id,
                    date_refuse,
                    reason
                FROM
                    block_refused
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

            $this->block_id = (int)$row_query['block_id'];
            $this->client_id = (int)$row_query['client_id'];
            $this->date_refuse = (string)$row_query['date_refuse'];
            $this->reason = (string)$row_query['reason'];
        }
    }
    
    function get_list($excluido=false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');
        
        $query = DB::query('SELECT id, excluido, block_id, client_id, date_refuse, reason FROM block_refused WHERE excluido = ? ORDER BY date_refuse, block_id', array($excluido));
        
        return $query;
    }
    
}