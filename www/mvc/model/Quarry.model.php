<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Quarry_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $name;
    public $products;
    public $defects;
    public $final_block_number;
    public $interim_block_number;
    public $seq_final;
    public $seq_interim;
    public $poblo_obs_interim_sobra;
    public $poblo_obs_final_sobra;
    public $poblo_obs_inspected_without_lot;

    //protected $active_user;
    protected $active_quarries;
    
    function __construct()
    {
        parent::__construct();

        //$this->active_user = $this->ActiveUser();
        $this->active_quarries = $this->SQLActiveQuarries();
    }

    function validation()
    {
        $validation = new Validation();
        
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
        $query = DB::query('SELECT id FROM quarry WHERE id = ? ', array($this->id));
        
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

            $sql = 'INSERT INTO quarry (
                        name, 
                        final_block_number, 
                        interim_block_number, 
                        seq_final, 
                        seq_interim,
                        poblo_obs_interim_sobra,
                        poblo_obs_final_sobra,
                        poblo_obs_inspected_without_lot
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ';
            $query = DB::exec($sql, array(
                $this->name,
                $this->final_block_number,
                $this->interim_block_number,
                $this->seq_final,
                $this->seq_interim,
                $this->poblo_obs_interim_sobra,
                $this->poblo_obs_final_sobra,
                $this->poblo_obs_inspected_without_lot
            ));

            $this->id = DB::last_insert_id();

            $this->save_products();
            $this->save_defects();

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
                        quarry
                    SET
                        name = ?,
                        final_block_number = ?,
                        interim_block_number = ?,
                        seq_final = ?,
                        seq_interim = ?,
                        poblo_obs_interim_sobra = ?,
                        poblo_obs_final_sobra = ?,
                        poblo_obs_inspected_without_lot = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    $this->final_block_number,
                    $this->interim_block_number,
                    $this->seq_final,
                    $this->seq_interim,
                    $this->poblo_obs_interim_sobra,
                    $this->poblo_obs_final_sobra,
                    $this->poblo_obs_inspected_without_lot,
                    // where
                    $this->id

                ));

                $this->save_products();
                $this->save_defects();

                return $this->id;
            }
        }
        
        return $valid;
    }

    // products
    private function save_products()
    {
        $this->delete_products();
        $this->insert_products();
    }

    private function delete_products()
    {
        $sql = 'DELETE FROM quarry_product WHERE quarry_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_products()
    {
        if (!is_null($this->products) && !empty($this->products))
        {
            foreach ($this->products as $key => $value) {
                $sql = 'INSERT INTO quarry_product (quarry_id, product_id) VALUES (?, ?)';
                $params[0] = $this->id;
                $params[1] = is_array($value) ? $value['product_id'] : $value;
                $query = DB::exec($sql, $params);
            }
        }
    }

    // defects
    private function save_defects()
    {
        $this->delete_defects();
        $this->insert_defects();
    }

    private function delete_defects()
    {
        $sql = 'DELETE FROM quarry_defect WHERE quarry_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_defects()
    {
        if (!is_null($this->defects) && !empty($this->defects))
        {
            foreach ($this->defects as $key => $value) {
                $sql = 'INSERT INTO quarry_defect (quarry_id, defect_id) VALUES (?, ?)';
                $params[0] = $this->id;
                $params[1] = is_array($value) ? $value['defect_id'] : $value;
                $query = DB::exec($sql, $params);
            }
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
            $sql = 'UPDATE quarry SET excluido = ? WHERE id = ? ';
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
                "SELECT
                    id,
                    excluido,
                    name,
                    final_block_number,
                    interim_block_number,
                    seq_final,
                    seq_interim,
                    poblo_obs_interim_sobra,
                    poblo_obs_final_sobra,
                    poblo_obs_inspected_without_lot
                FROM
                    quarry
                WHERE
                    id = ?
                    AND id IN ({$this->active_quarries}) ",
                array($id)
            );
            
            if (DB::has_rows($query))
            {
                $this->fill($query[0]);

                //populate client_groups
                $sql = 'SELECT product_id FROM quarry_product WHERE quarry_id = ?';
                $params[0] = $this->id;
                $query_product = DB::query($sql, $params);
                $this->products = array();
                foreach ($query_product as $row) {
                    $this->products[] = (int)$row['product_id'];
                }

                //populate agencies
                $sql = 'SELECT defect_id FROM quarry_defect WHERE quarry_id = ?';
                $params[0] = $this->id;
                $query_defect = DB::query($sql, $params);
                $this->defects = array();
                foreach ($query_defect as $row) {
                    $this->defects[] = (int)$row['defect_id'];
                }

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

            $this->name = (string)$row_query['name'];
            $this->final_block_number = (string)$row_query['final_block_number'];
            $this->interim_block_number = (string)$row_query['interim_block_number'];
            $this->seq_final = (int)$row_query['seq_final'];
            $this->seq_interim = (int)$row_query['seq_interim'];
            $this->poblo_obs_interim_sobra = (string)$row_query['poblo_obs_interim_sobra'];
            $this->poblo_obs_final_sobra = (string)$row_query['poblo_obs_final_sobra'];
            $this->poblo_obs_inspected_without_lot = (string)$row_query['poblo_obs_inspected_without_lot'];            
        }
    }
    
    function get_list($just_active_quarries=true, $just_id=false)
    {
        $sql = "SELECT
                    quarry.id,
                    quarry.excluido,
                    quarry.name,
                    quarry.final_block_number,
                    quarry.interim_block_number,
                    quarry.seq_final,
                    quarry.seq_interim
                FROM quarry
                WHERE
                ";

        if ($just_active_quarries === true) {
            $sql .= "   quarry.id IN ({$this->active_quarries}) AND ";
        }

        $sql .="    quarry.excluido = 'N'
                ORDER BY quarry.name ";
        
        $query = DB::query($sql);

        // just_id = true
        if (isset($just_id) && ($just_id == true)) {
            $new_query = array();
            foreach ($query as $key => $row) {
                $new_query[] = $row['id'];
            }
            $query = $new_query;
        }

        return $query;
    }

    static function next_val_final($quarry_id)
    {
        $query = DB::query('SELECT final_block_number, seq_final_ref, seq_final FROM quarry WHERE id = ?', array($quarry_id));

        $seq_final_ref_atual = date('y-m');
        $dt_atual = date('Y-m-d');

        $seq_final_ref = '';
        if (isset($query[0]['seq_final_ref'])) {
            $seq_final_ref = self::field_fill_date($query[0]['seq_final_ref']);
            $seq_final_ref = date_format($seq_final_ref, 'y-m');
        }

        // verifico se é o mesmo ano/mes anterior, se não for, atualizo a referencia e reinicio a sequencia
        if ($seq_final_ref_atual != $seq_final_ref) {
            $sql = 'UPDATE quarry SET seq_final_ref = ?, seq_final = ? WHERE id = ? ';
            $query = DB::exec($sql, array($dt_atual, 1, $quarry_id));
        }
        // se não só incremento a sequencia
        else
        {
            $sql = 'UPDATE quarry SET seq_final = seq_final+1 WHERE id = ? ';
            $query = DB::exec($sql, array($quarry_id));
        }

        $query = DB::query('SELECT final_block_number, seq_final FROM quarry WHERE id = ?', array($quarry_id));
        return $query[0]['final_block_number'] . $seq_final_ref_atual . '.' . str_pad($query[0]['seq_final'], 3, "0", STR_PAD_LEFT);
    }

    static function next_val_interim($quarry_id)
    {
        $sql = 'UPDATE quarry SET seq_interim = seq_interim+1 WHERE id = ? ';
        $query = DB::exec($sql, array($quarry_id));

        $query = DB::query('SELECT interim_block_number, seq_interim FROM quarry WHERE id = ?', array($quarry_id));
        return $query[0]['interim_block_number'] . str_pad($query[0]['seq_interim'], 10, "0", STR_PAD_LEFT);
    }

    /*
    static function next_val_invoice($quarry_id)
    {
        $query = DB::query('SELECT final_block_number, seq_invoice_ref, seq_invoice FROM quarry WHERE id = ?', array($quarry_id));

        $seq_invoice_ref_atual = date('y');
        $dt_atual = date('Y-m-d');

        $seq_invoice_ref = '';
        if (isset($query[0]['seq_invoice_ref'])) {
            $seq_invoice_ref = self::field_fill_date($query[0]['seq_invoice_ref']);
            $seq_invoice_ref = date_format($seq_invoice_ref, 'y');
        }

        // verifico se é o mesmo ano anterior, se não for, atualizo a referencia e reinicio a sequencia
        if ($seq_invoice_ref_atual != $seq_invoice_ref) {
            $sql = 'UPDATE quarry SET seq_invoice_ref = ?, seq_invoice = ? WHERE id = ? ';
            $query = DB::exec($sql, array($dt_atual, 1, $quarry_id));
        }
        // se não só incremento a sequencia
        else
        {
            $sql = 'UPDATE quarry SET seq_invoice = seq_invoice+1 WHERE id = ? ';
            $query = DB::exec($sql, array($quarry_id));
        }

        $query = DB::query('SELECT final_block_number, seq_invoice FROM quarry WHERE id = ?', array($quarry_id));
        return $query[0]['final_block_number'] . $seq_invoice_ref_atual . '-' . str_pad($query[0]['seq_invoice'], 3, "0", STR_PAD_LEFT);
    }
    */

    function get_by_user($user_id, $just_id)
    {
        $sql = 'SELECT
                    user_quarry.user_id,
                    user_quarry.quarry_id,
                    quarry.name AS quarry_name
                FROM
                    user_quarry
                INNER JOIN
                    quarry ON (quarry.id = user_quarry.quarry_id)
                WHERE
                    user_quarry.user_id = ?
                    AND quarry.excluido = "N"
                ORDER BY
                    quarry.name
                ';

        $query = DB::query($sql, array($user_id));

        // just_id = true
        if (isset($just_id) && ($just_id == true)) {
            $new_query = array();
            foreach ($query as $key => $row) {
                $new_query[] = $row['quarry_id'];
            }
            $query = $new_query;
        }
        

        return $query;
    }
    
}