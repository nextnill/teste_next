<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Block_Model extends \Sys\Model {
    
    const BLOCK_TYPE_FINAL = 1;
    const BLOCK_TYPE_INTERIM = 2;

    public $id;
    public $excluido;

    public $quarry_id;
    public $product_id;
    public $quality_id;
    public $type;
    public $production_order_item_id;

    public $block_number;
    public $tot_c;
    public $tot_a;
    public $tot_l;
    public $tot_vol;
    public $tot_weight;
    public $net_c;
    public $net_a;
    public $net_l;
    public $net_vol;
    public $sale_net_c;
    public $sale_net_a;
    public $sale_net_l;
    public $sale_net_vol;
    
    public $obs;
    public $defects_json;
    public $reinspection;
    public $block_number_interim;

    public $obs_poblo;

    public $defects;

    public $photos;

    public $reserved;
    public $reserved_client_id;
    public $sold;
    public $sold_client_id;
    public $client_block_number;
    public $current_lot_transport_id;

    protected $active_quarries;
    
    function __construct()
    {
        parent::__construct();

        $this->active_quarries = $this->SQLActiveQuarries();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (is_null($this->production_order_item_id) || empty($this->production_order_item_id) || $this->production_order_item_id == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o item da ordem de produção');
        }

        if (strlen($this->block_number) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o número do bloco');
        }

        if (is_null($this->tot_c) || empty($this->tot_c) || $this->tot_c == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VB C');
        }

        if (is_null($this->tot_a) || empty($this->tot_a) || $this->tot_a == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VB A');
        }

        if (is_null($this->tot_l) || empty($this->tot_l) || $this->tot_l == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VB L');
        }

        if (is_null($this->tot_vol) || empty($this->tot_vol) || $this->tot_vol == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VB Vol');
        }

        if (is_null($this->net_c) || empty($this->net_c) || $this->net_c == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VL C');
        }

        if (is_null($this->net_a) || empty($this->net_a) || $this->net_a == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VL A');
        }

        if (is_null($this->net_l) || empty($this->net_l) || $this->net_l == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VL L');
        }

        if (is_null($this->net_vol) || empty($this->net_vol) || $this->net_vol == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o VL Vol');
        }
        
        if (is_null($this->quality_id) || empty($this->quality_id) || $this->quality_id == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe a classificação');
        }

        if (strlen($this->obs_poblo) > 200)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Texto de observação muito grande');
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
        // verificar id
        if (is_null($this->id))
        {
            $this->id = 0;
        }
        $query = DB::query('SELECT id FROM block WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }

        // verificar block number
        if (is_null($this->block_number))
        {
            $this->block_number = '';
        }
        $query = DB::query('SELECT id FROM block WHERE block_number = ? AND excluido = ? ', array($this->block_number, 'N'));
        
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
            $sql = 'INSERT INTO block (
                        quarry_id,
                        product_id,
                        quality_id,
                        type,
                        production_order_item_id,
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
                        defects_json,
                        reserved,
                        reserved_client_id,
                        sold,
                        sold_client_id,
                        current_lot_transport_id,
                        obs_poblo
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                              ?, ?, ?, ?, ?, ?, ?, ?, ?,
                              ?, ?, ?, ?, ?, ?, ?, ?) ';
            
            $query = DB::exec($sql, array(
                $this->quarry_id,
                $this->product_id,
                $this->quality_id,
                $this->type,
                $this->production_order_item_id,
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
                $this->defects_json,
                (int)$this->reserved,
                $this->reserved_client_id,
                (int)$this->sold,
                $this->sold_client_id,
                $this->current_lot_transport_id,
                $this->obs_poblo
            ));

            $this->id = DB::last_insert_id();

            $this->save_defects();
            
            return $this;
        }
        
        return $validation;
    }

    function update()
    {
        $validation = $this->validation();

        if ($validation->isValid())
        {
            $this->insert_history();
            
            $sql = 'UPDATE block
                    SET
                        block_number = ?,
                        quality_id = ?,
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
                        defects_json = ?,
                        reinspection = ?,
                        block_number_interim = ?,
                        reserved = ?,
                        reserved_client_id = ?,
                        sold = ?,
                        sold_client_id = ?,
                        client_block_number = ?,
                        current_lot_transport_id = ?,
                        obs_poblo = ?
                    WHERE id = ? ';

            $query = DB::exec($sql, array(
                $this->block_number,
                $this->quality_id,
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
                $this->defects_json,
                trim($this->reinspection) == '' ? null : $this->reinspection,
                trim($this->block_number_interim) == '' ? null : $this->block_number_interim,
                (int)$this->reserved,
                $this->reserved_client_id,
                (int)$this->sold,
                $this->sold_client_id,
                $this->client_block_number,
                $this->current_lot_transport_id,
                $this->obs_poblo,
                $this->id
            ));

            $this->save_defects();
            
            return $this;
        }
        
        return $validation;
    }

    // defects
    private function save_defects()
    {
        $this->delete_defects();
        $this->insert_defects();
    }

    private function delete_defects()
    {
        $sql = 'DELETE FROM block_defect WHERE block_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_defects()
    {
        if (!is_null($this->defects) && !empty($this->defects))
        {
            foreach ($this->defects as $key => $value) {
                $sql = 'INSERT INTO block_defect (block_id, defect_id) VALUES (?, ?)';
                $params[0] = $this->id;
                $params[1] = is_array($value) ? $value['defect_id'] : $value;
                $query = DB::exec($sql, $params);
            }
        }
    }

    function insert_history()
    {
        $block_now = $this->LoadModel('Block', true);
        $block_now->populate($this->id);

        $sql = 'INSERT INTO block_history (
                    block_id,
                    quarry_id,
                    product_id,
                    date_changed,
                    quality_id,
                    type,
                    production_order_item_id,
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
                    defects_json,
                    reinspection,
                    reserved,
                    reserved_client_id,
                    sold,
                    sold_client_id,
                    client_block_number,
                    current_lot_transport_id,
                    obs_poblo
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ';
        
        $dt_now = new DateTime('now');
        $query = DB::exec($sql, array(
            $block_now->id,
            $block_now->quarry_id,
            $block_now->product_id,
            $dt_now->format('Y-m-d H:i:s'),
            $block_now->quality_id,
            $block_now->type,
            $block_now->production_order_item_id,
            $block_now->block_number,
            $block_now->tot_c,
            $block_now->tot_a,
            $block_now->tot_l,
            $block_now->tot_vol,
            $block_now->tot_weight,
            $block_now->net_c,
            $block_now->net_a,
            $block_now->net_l,
            $block_now->net_vol,
            $block_now->sale_net_c,
            $block_now->sale_net_a,
            $block_now->sale_net_l,
            $block_now->sale_net_vol,
            $block_now->obs,
            $block_now->defects_json,
            trim($block_now->reinspection) == '' ? null : $block_now->reinspection,
            (int)$block_now->reserved,
            $block_now->reserved_client_id,
            (int)$block_now->sold,
            $block_now->sold_client_id,
            $block_now->client_block_number,
            $block_now->current_lot_transport_id,
            $block_now->obs_poblo
        ));

        $history_id = DB::last_insert_id();
        $this->save_history_defects($history_id);
        
        return $history_id;
    }

    // history defects
    private function save_history_defects($history_id)
    {
        $this->delete_history_defects($history_id);
        $this->insert_history_defects($history_id);
    }

    private function delete_history_defects($history_id)
    {
        $sql = 'DELETE FROM block_history_defect WHERE block_history_id = ?';
        $params[] = $history_id;
        $query = DB::exec($sql, $params);
    }

    private function insert_history_defects($history_id)
    {
        if (!is_null($this->defects) && !empty($this->defects))
        {
            foreach ($this->defects as $key => $value) {
                $sql = 'INSERT INTO block_history_defect (block_history_id, defect_id) VALUES (?, ?)';
                $params[0] = $history_id;
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
            $sql = 'UPDATE block SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));

            //$this->delete_defects();
            $this->excluido = 'S';
            return $this;
        }
        
        return $validation;
    }

    function populate($id, $block_number=null)
    {
        $validation = new Validation();
        
        if ($id) {
            $this->id = $id;
        }

        $sql = 'SELECT
                    id,
                    excluido,
                    quarry_id,
                    product_id,
                    quality_id,
                    type,
                    production_order_item_id,
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
                    defects_json,
                    reinspection,
                    block_number_interim,
                    reserved,
                    reserved_client_id,
                    sold,
                    sold_client_id,
                    client_block_number,
                    current_lot_transport_id,
                    obs_poblo
                FROM
                    block
                WHERE ';

        if (!empty($id)) {
            $sql .= ' id = ? ';
        }
        else {
            $sql .= ' block_number = ? ';
        }

        $params[] = (!empty($id) ? $id : $block_number);

        $query = DB::query($sql, $params);
        
        if (DB::has_rows($query))
        {
            $this->fill($query[0]);
            return true;
        }
        else
        {
            $validation->add(Validation::VALID_ERR_NOT_EXISTS, 'Block does not exists');
        }

        return $validation;
    }

    function reserve($block_id, $reserved_client_id, $client_block_number='')
    {
        $this->populate($block_id);

        $validation = $this->validation();

        if ($validation->isValid())
        {
            $this->insert_history();
            
            $sql = 'UPDATE block
                    SET
                        client_block_number = ?,
                        reserved = ?,
                        reserved_client_id = ?
                    WHERE id = ? ';

            $params[] = $client_block_number;
            $params[] = ($reserved_client_id > 0);
            $params[] = ($reserved_client_id > 0 ? $reserved_client_id : null);
            $params[] = $block_id;

            $query = DB::exec($sql, $params);

            $this->populate($block_id);
            return $this;
        }
        
        return $validation;
    }

    function sell($block_id, $sold_client_id, $block_number, $sale_net_c, $sale_net_a, $sale_net_l, $sale_net_vol, $client_block_number='', $block_number_interim)
    {
        $this->populate($block_id);

        $validation = $this->validation();

        if ($validation->isValid())
        {
            $this->insert_history();
            
            $sql = 'UPDATE block
                    SET
                        block_number = ?,
                        type = ?,
                        sale_net_c = ?,
                        sale_net_a = ?,
                        sale_net_l = ?,
                        sale_net_vol = ?,
                        client_block_number = ?,
                        sold = ?,
                        sold_client_id = ?,
                        block_number_interim = ?
                    WHERE id = ? ';

            $params[] = $block_number;
            $params[] = self::BLOCK_TYPE_FINAL;
            $params[] = $sale_net_c;
            $params[] = $sale_net_a;
            $params[] = $sale_net_l;
            $params[] = $sale_net_vol;
            $params[] = $client_block_number;
            $params[] = ($sold_client_id > 0);
            $params[] = ($sold_client_id > 0 ? $sold_client_id : null);
            $params[] = $block_number_interim;
            $params[] = $block_id;

            $query = DB::exec($sql, $params);

            $this->populate($block_id);
            return $this;
        }
        
        return $validation;
    }

    function set_current_lot_transport($block_id, $lot_transport_id)
    {
        if (isset($block_id) && !is_null($block_id)) {
            $this->populate($block_id);
        }

        $validation = $this->validation();

        if ($validation->isValid())
        {
            $this->insert_history();
            
            $sql = 'UPDATE block
                    SET current_lot_transport_id = ?
                    WHERE id = ? ';

            $params[] = $lot_transport_id;
            $params[] = $this->id;

            $query = DB::exec($sql, $params);

            $this->populate($this->id);
            return $this;
        }
        
        return $validation;
    }

    function fill($row_query)
    {
        if ($row_query)
        {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->quarry_id = (int)$row_query['quarry_id'];
            $this->product_id = (int)$row_query['product_id'];
            $this->quality_id = (int)$row_query['quality_id'];
            $this->type = (int)$row_query['type'];
            $this->production_order_item_id = (int)$row_query['production_order_item_id'];
            $this->block_number = (string)$row_query['block_number'];
            $this->tot_c = (float)$row_query['tot_c'];
            $this->tot_a = (float)$row_query['tot_a'];
            $this->tot_l = (float)$row_query['tot_l'];
            $this->tot_vol = (float)$row_query['tot_vol'];
            $this->tot_weight = (float)$row_query['tot_weight'];
            $this->net_c = (float)$row_query['net_c'];
            $this->net_a = (float)$row_query['net_a'];
            $this->net_l = (float)$row_query['net_l'];
            $this->net_vol = (float)$row_query['net_vol'];
            $this->sale_net_c = (float)$row_query['sale_net_c'];
            $this->sale_net_a = (float)$row_query['sale_net_a'];
            $this->sale_net_l = (float)$row_query['sale_net_l'];
            $this->sale_net_vol = (float)$row_query['sale_net_vol'];
            $this->obs = (string)$row_query['obs'];
            $this->defects_json = (string)$row_query['defects_json'];
            $this->reinspection = (string)$row_query['reinspection'];
            $this->block_number_interim = (string)$row_query['block_number_interim'];
            $this->reserved = (int)$row_query['reserved'] === 1;
            $this->reserved_client_id = (empty($row_query['reserved_client_id']) ? null : (int)$row_query['reserved_client_id']);
            $this->sold = (int)$row_query['sold'] === 1;
            $this->sold_client_id = (empty($row_query['sold_client_id']) ? null : (int)$row_query['sold_client_id']);
            $this->client_block_number = (string)$row_query['client_block_number'];
            $this->current_lot_transport_id = (empty($row_query['current_lot_transport_id']) ? null : (int)$row_query['current_lot_transport_id']);
            $this->obs_poblo = (string)$row_query['obs_poblo'];
            
            // carrega os defeitos dos blocos
            $defect_model = $this->LoadModel('Defect', true);
            $this->defects = $defect_model->get_by_block($this->id);

            // carrega dados do produto
            $product_model = $this->LoadModel('Product', true);
            $product_model->populate($this->product_id);
            $this->product = $product_model;

            $block_photo_model = $this->LoadModel('BlockPhoto', true);
            $this->photos = $block_photo_model->get_by_poi($this->production_order_item_id);
        }
    }
    
    function get_list($block_number=null)
    {
        $sql = "SELECT
                    block.id,
                    block.excluido,
                    block.quarry_id,
                    block.product_id,
                    block.quality_id,
                    quality.name AS quality_name,
                    block.type,
                    block.production_order_item_id,
                    block.block_number,
                    block.tot_c,
                    block.tot_a,
                    block.tot_l,
                    block.tot_vol,
                    block.tot_weight,
                    block.net_c,
                    block.net_a,
                    block.net_l,
                    block.net_vol,
                    block.sale_net_c,
                    block.sale_net_a,
                    block.sale_net_l,
                    block.sale_net_vol,
                    block.obs,
                    block.defects_json,
                    block.reserved,
                    block.reserved_client_id,
                    reserved_client.code AS reserved_client_code,
                    reserved_client.name AS reserved_client_name,
                    block.sold,
                    block.sold_client_id,
                    sold_client.code AS sold_client_code,
                    sold_client.name AS sold_client_name,
                    block.client_block_number,
                    block.block_number_interim,
                    block.obs_poblo
                FROM
                    block
                LEFT JOIN quality ON (quality.id = block.quality_id)
                LEFT JOIN client AS reserved_client ON (reserved_client.id = block.reserved_client_id)
                LEFT JOIN client AS sold_client ON (sold_client.id = block.sold_client_id)
                WHERE
                    block.quarry_id IN ({$this->active_quarries})
                    AND block.excluido = :excluido ";

        if (!is_null($block_number)) {
            $sql .= " AND block.block_number LIKE :block_number ";
            $params[':block_number'] = '%' . $block_number . '%';
        }

        $sql .= "
                ORDER BY
                    block.block_number ";

        $params[':excluido'] = 'N';

        $query = DB::query($sql, $params);

        return $query;
    }

    function get_sobracolumay($type, $client_except=null, $client_id = -1)
    {
        $sql = "SELECT
                    block.id,
                    block.excluido,
                    block.quarry_id,
                    quarry.name AS quarry_name,
                    quarry.poblo_obs_interim_sobra,
                    quarry.poblo_obs_final_sobra,
                    quarry.poblo_obs_inspected_without_lot,
                    block.product_id,
                    product.weight_vol AS product_weight_vol,
                    block.quality_id,
                    quality.name AS quality_name,
                    quality.order_number AS quality_order_number,
                    block.type,
                    block.production_order_item_id,
                    production_order.date_production,
                    block.block_number,
                    block.tot_c,
                    block.tot_a,
                    block.tot_l,
                    block.tot_vol,
                    -- ROUND((block.tot_c * block.tot_a * block.tot_l * product.weight_vol), 3) AS tot_weight,
                    block.tot_weight,
                    block.net_c,
                    block.net_a,
                    block.net_l,
                    block.net_vol,
                    block.sale_net_c,
                    block.sale_net_a,
                    block.sale_net_l,
                    block.sale_net_vol,
                    block.obs,
                    block.defects_json,
                    block.reserved,
                    block.reserved_client_id,
                    reserved_client.code AS reserved_client_code,
                    reserved_client.name AS reserved_client_name,
                    block.sold,
                    block.client_block_number,
                    block.obs_poblo
                FROM block
                INNER JOIN production_order_item ON (production_order_item.id = block.production_order_item_id AND production_order_item.excluido = 'N')
                INNER JOIN production_order ON (production_order.id = production_order_item.production_order_id AND production_order.excluido = 'N')
                INNER JOIN quarry ON (quarry.id = block.quarry_id)
                INNER JOIN quality ON (quality.id = block.quality_id)
                INNER JOIN product ON (product.id = block.product_id)
                LEFT JOIN client AS reserved_client ON (reserved_client.id = block.reserved_client_id)
                WHERE
                    block.quarry_id IN ({$this->active_quarries})
                    AND block.excluido = ?
                    AND block.type = ?
                    AND block.sold = ? ";


        if ($client_except) {
            $sql .= ' AND (block.reserved_client_id IS NULL OR block.reserved_client_id != ?) ';
        }

        if($client_id > 0){
            $sql .= ' AND (block.reserved_client_id = ?) ';
        }

        $sql .= 'ORDER BY quarry.name, quality.order_number, block.block_number ';

        $params[] = 'N';
        $params[] = $type;
        $params[] = false;

        if ($client_except) {
            $params[] = $client_except;
        }

        if($client_id > 0){
            $params[] = $client_id;
        }

        
        $query = DB::query($sql, $params);

        $defect_model = $this->LoadModel('Defect', true);

        for ($i=0; $i < sizeof($query); $i++) { 
            $defect_model = new Defect_Model();
            $defects = $defect_model->get_by_block($query[$i]['id']);
            foreach ($defects as $key_defect => $defect) {
                if (strlen($query[$i]['obs']) > 0)
                    $query[$i]['obs'] .= '/';
                
                $query[$i]['obs'] .= $defect['name'];
            }
        }       

        return $query;
    }

    function get_clients_with_reservations()
    {
        $sql = "SELECT
                    block.reserved_client_id AS client_id,
                    reserved_client.code AS client_code,
                    reserved_client.name AS client_name,
                    count(block.id) blocks,
                    sum(block.net_vol) as net_vol
                FROM block
                INNER JOIN client AS reserved_client ON (reserved_client.id = block.reserved_client_id)
                WHERE
                    block.quarry_id IN ({$this->active_quarries})
                    AND block.excluido = ?
                    AND block.sold = ?
                    AND block.reserved = ?
                GROUP BY
                    block.reserved_client_id
                ORDER BY
                    reserved_client.code ";

        $params[] = 'N';
        $params[] = false;
        $params[] = true;

        return $query = DB::query($sql, $params);
    }

    function get_client_reservations($client_id)
    {
        $sql = "SELECT
                    block.id,
                    block.excluido,
                    block.quarry_id,
                    quarry.name AS quarry_name,
                    block.product_id,
                    product.weight_vol AS product_weight_vol,
                    block.quality_id,
                    quality.name AS quality_name,
                    quality.order_number AS quality_order_number,
                    block.type,
                    block.production_order_item_id,
                    block.block_number,
                    block.tot_c,
                    block.tot_a,
                    block.tot_l,
                    block.tot_vol,
                    -- ROUND((block.tot_c * block.tot_a * block.tot_l * product.weight_vol), 3) AS tot_weight,
                    block.tot_weight,
                    block.net_c,
                    block.net_a,
                    block.net_l,
                    block.net_vol,
                    block.sale_net_c,
                    block.sale_net_a,
                    block.sale_net_l,
                    block.sale_net_vol,
                    block.obs,
                    block.defects_json,
                    block.reserved,
                    block.reserved_client_id,
                    block.sold,
                    block.block_number_interim,
                    block.client_block_number,
                    block.obs_poblo
                FROM block
                INNER JOIN quarry ON (quarry.id = block.quarry_id)
                INNER JOIN quality ON (quality.id = block.quality_id)
                INNER JOIN product ON (product.id = block.product_id)
                WHERE
                    block.quarry_id IN ({$this->active_quarries})
                    AND block.excluido = ?
                    AND block.sold = ?
                    AND block.reserved_client_id = ?
                ORDER BY
                    quarry.name, quality.order_number, block.block_number ";

        $params[] = 'N';
        $params[] = false;
        $params[] = $client_id;

        $query = DB::query($sql, $params);

        $defect_model = $this->LoadModel('Defect', true);

        for ($i=0; $i < sizeof($query); $i++) { 
            $defect_model = new Defect_Model();
            $defects = $defect_model->get_by_block($query[$i]['id']);
            foreach ($defects as $key_defect => $defect) {
                if (strlen($query[$i]['obs']) > 0)
                    $query[$i]['obs'] .= '/';
                
                $query[$i]['obs'] .= $defect['name'];
            }
        }       

        return $query;
    }

    function get_clients_without_lot()
    {
        $params = array();

        $sql = "SELECT
                    invoice_item.client_id,
                    client.code AS client_code,
                    client.name AS client_name
                FROM invoice_item
                INNER JOIN client ON (client.id = invoice_item.client_id)
                INNER JOIN block ON (block.id = invoice_item.block_id AND block.excluido = 'N'
                                        AND block.quarry_id IN ({$this->active_quarries}))

                WHERE invoice_item.block_id NOT IN (
                    SELECT lot_transport_item.block_id FROM lot_transport_item
                    WHERE lot_transport_item.excluido = 'N'
                        AND lot_transport_item.block_id = invoice_item.block_id
                )
                AND invoice_item.excluido = 'N'

                GROUP BY invoice_item.client_id
                ORDER BY client.name ";
        
        $query = DB::query($sql, $params);   

        return $query;
    }

    function get_without_lot($client_id=null)
    {
        $params = array();

        $sql = "SELECT
                    invoice_item.id AS invoice_item_id,
                    invoice_item.invoice_id,
                    invoice.date_record AS invoice_date_record,
                    block.id block_id,
                    block.block_number,
                    block.quarry_id,
                    quarry.name AS quarry_name,
                    block.product_id,
                    block.quality_id,
                    quality.name AS quality_name,
                    block.tot_weight,
                    production_order.date_production
                FROM invoice_item
                INNER JOIN invoice ON (invoice.id = invoice_item.invoice_id AND invoice.excluido = 'N')
                INNER JOIN block ON (block.id = invoice_item.block_id AND block.excluido = 'N')
                INNER JOIN production_order_item ON (production_order_item.id = block.production_order_item_id AND production_order_item.excluido = 'N')
                INNER JOIN production_order ON (production_order.id = production_order_item.production_order_id AND production_order.excluido = 'N')
                INNER JOIN quarry ON (quarry.id = block.quarry_id AND quarry.excluido = 'N')
                INNER JOIN quality ON (quality.id = block.quality_id AND quality.excluido = 'N')
                WHERE
                block.quarry_id IN ({$this->active_quarries})
                AND invoice_item.block_id NOT IN (
                    SELECT lot_transport_item.block_id FROM lot_transport_item
                    WHERE lot_transport_item.excluido = 'N'
                        AND lot_transport_item.dismembered = FALSE
                        AND lot_transport_item.block_id = invoice_item.block_id
                )
                AND invoice_item.excluido = 'N' ";

        if (isset($client_id) && ($client_id > 0)) {
            $sql .= ' AND invoice_item.client_id = ? ';
            $params[] = $client_id;
        }

        $sql .= ' ORDER BY invoice.date_record, invoice.id, invoice_item.block_number ';

        $query = DB::query($sql, $params);   

        return $query;
    }

    function get_with_lot($block_number)
    {
        $sql = "SELECT
                    block.id,
                    block.excluido,
                    block.quarry_id,
                    block.product_id,
                    block.quality_id,
                    quality.name AS quality_name,
                    block.type,
                    block.production_order_item_id,
                    block.block_number,
                    block.obs,
                    block.sold,
                    block.sold_client_id,
                    sold_client.code AS sold_client_code,
                    sold_client.name AS sold_client_name,
                    block.client_block_number,
                    block.current_lot_transport_id
                    
                FROM
                    block
                INNER JOIN quality ON (quality.id = block.quality_id)
                INNER JOIN client AS sold_client ON (sold_client.id = block.sold_client_id)
                WHERE
                    block.quarry_id IN ({$this->active_quarries}) 
                    AND block.excluido = 'N'
                    AND block.current_lot_transport_id > 0
                    AND block.block_number LIKE ?
                ORDER BY
                    block.block_number ";
        
        $params[] = '%'.$block_number.'%';

        $query = DB::query($sql, $params);

        return $query;
    }

    function get_poblo_obs($block_id) {
        
        $sql = "SELECT
                    block.obs_poblo
                FROM 
                    block
                WHERE
                    block.excluido = 'N'
                    AND block.id = :block_id
                ";

        $params[':block_id'] = $block_id;

        $query = DB::query($sql, $params);



        if (DB::has_rows($query)) {
            return $query[0]['obs_poblo'];
        }
        
        
        return '';
    }

    function set_poblo_obs($block_id, $obs) {
        
        
        $sql = "UPDATE
                    block
                SET
                    obs_poblo = :obs
                WHERE
                    block.id = :block_id";
        
        $params[':block_id'] = $block_id;
        $params[':obs'] = $obs;

        DB::exec($sql, $params);
        

        return $this->get_poblo_obs($block_id);
    }

}