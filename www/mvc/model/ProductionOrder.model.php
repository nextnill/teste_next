<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class ProductionOrder_Model extends \Sys\Model {
    
	const PO_STATUS_DRAFT = 0;
    const PO_STATUS_CONFIRMED = 1;

    public $id;
    public $excluido;

    public $quarry_id;
    public $date_production;
    public $product_id;
    public $block_type;
    public $status;

    protected $active_quarries;
    
    function __construct()
    {
        parent::__construct();

        $this->active_quarries = $this->SQLActiveQuarries();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (!$this->quarry_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid quarry');
        }

        if (is_null($this->date_production))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Inform production date');
        }

        if (!$this->product_id > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid product');
        }

        if (!$this->block_type > 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Invalid block type');
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
	                production_order
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
            $sql = 'INSERT INTO production_order (
	                    quarry_id,
	                    date_production,
                        product_id,
                        block_type,
	                    status
	                ) VALUES (
	                    ?,
	                    ?,
                        ?,
                        ?,
                        ?
	                ) ';
            $query = DB::exec($sql, array(
                // values
                $this->quarry_id,
                $this->date_production,
                $this->product_id,
                $this->block_type,
                self::PO_STATUS_DRAFT
            ));

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
	                        production_order
	                    SET
	                        quarry_id = ?,
	                        date_production = ?,
                            product_id = ?,
                            block_type = ?,
	                        status = ?
	                    WHERE
	                        id = ?
	                    ';
                $query = DB::exec($sql, array(
                    // set
                    $this->quarry_id,
                    $this->date_production,
                    $this->product_id,
                    $this->block_type,
                    $this->status,
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
	                    production_order
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
	                    quarry_id,
	                    date_production,
                        product_id,
                        block_type,
	                    status
	                FROM
	                    production_order
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

    function confirm()
    {
        try {

            // inicio a transação
            DB::begin_transaction();

            // copia itens e replica para os blocos
            $poi_model = $this->LoadModel('ProductionOrderItem', true);
            $items = $poi_model->get_by_po($this->id);

            foreach ($items as $key => $item) {

                $block_model = $this->LoadModel('Block', true);

                $block_model->quarry_id = $this->quarry_id;
                $block_model->product_id = $this->product_id;
                $block_model->quality_id = $item['quality_id'];
                $block_model->type = $this->block_type;
                $block_model->production_order_item_id = $item['id'];
                $block_model->block_number = $item['block_number'];
                $block_model->tot_c = $item['tot_c'];
                $block_model->tot_a = $item['tot_a'];
                $block_model->tot_l = $item['tot_l'];
                $block_model->tot_vol = $item['tot_vol'];
                $block_model->tot_weight = $item['tot_weight'];
                $block_model->net_c = $item['net_c'];
                $block_model->net_a = $item['net_a'];
                $block_model->net_l = $item['net_l'];
                $block_model->net_vol = $item['net_vol'];
                $block_model->obs = $item['obs'];
                $block_model->defects = $item['defects'];
                $block_model->defects_json = $item['defects_json'];
                $block_model->reserved = false;
                $block_model->sold = false;
                
                $block_model->save();
            }

            // atualiza status
            $this->status = self::PO_STATUS_CONFIRMED;
            $this->save();

            // confirmo a transação
            DB::commit();
        }
        catch (Exception $e) {
            
            // cancelo a transação
            DB::roll_back();

            throw $e;
        }

    }

    function fill($row_query)
    {
        if ($row_query)
        {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->quarry_id = (int)$row_query['quarry_id'];
            //$this->date_production = $this->field_fill_date($row_query['date_production']);
            $this->date_production = (string)$row_query['date_production'];
            $this->product_id = (int)$row_query['product_id'];
            $this->block_type = (int)$row_query['block_type'];
            $this->status = (int)$row_query['status'];
        }
    }
    
    function get_list($quarry_id=null, $block_type=null, $ano, $mes)
    {
        $params[] = 'N';

        $sql = "SELECT
                    production_order.id,
                    production_order.excluido,
                    production_order.quarry_id,
                    quarry.name AS quarry_name,
                    quarry.id AS quarry_id,
                    quarry.name AS quarry_name,
                    production_order_item.net_vol as production_order_item_net_vol,
                    block.net_vol as block_net_vol,
                    production_order.date_production,
                    production_order.product_id,
                    product.name AS product_name,
                    product.weight_vol AS product_weight_vol,
                    production_order.block_type,
                    production_order.status
                FROM
                    production_order
                INNER JOIN
                    quarry ON (quarry.id = production_order.quarry_id)
                INNER JOIN
                    product ON (product.id = production_order.product_id)
                INNER JOIN
                    production_order_item ON (production_order_item.production_order_id = production_order.id)
                LEFT JOIN
                    block ON (block.production_order_item_id = production_order_item.id)
                WHERE
                    production_order.quarry_id IN ({$this->active_quarries})
                    AND production_order.excluido = ? ";

        if (!is_null($quarry_id)) {
            $sql .= ' AND production_order.quarry_id = ? ';
            $params[] = $quarry_id;
        }

        if($block_type){
            $sql.= ' AND production_order.block_type = ?';
            $params[] = $block_type;
        }

        if($ano){
            $sql.= ' AND Year(production_order.date_production) = ?';
            $params[] = $ano;
        }

        if($mes){
            $sql.= ' AND Month(production_order.date_production) = ?';
            $params[] = $mes;
        }

        $sql .= "ORDER BY production_order.date_production DESC, production_order.id DESC ";

        $query = DB::query($sql, $params);
        
        return $query;
    }
    
}