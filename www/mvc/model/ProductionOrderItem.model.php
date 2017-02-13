<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class ProductionOrderItem_Model extends \Sys\Model {
    
    public $id;
    public $block_id;
    public $excluido;

    public $production_order_id;
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
    public $quality_id;
    public $type;
    public $obs;
    public $defects_json;

    public $defects;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        

        if (is_null($this->production_order_id) || empty($this->production_order_id) || $this->production_order_id == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe a ordem de produção');
        }

        if (strlen($this->block_number) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Informe o número do bloco');
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
        $query = DB::query('SELECT id FROM production_order_item WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO production_order_item (
                        quality_id,
                        production_order_id,
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
                        obs,
                        defects_json
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ';

            
            $query = DB::exec($sql, array(
                $this->quality_id,
                $this->production_order_id,
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
                $this->obs,
                $this->defects_json
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
        $op_id = $this->production_order_id;
        //print_r($this->production_order_id);exit();


        if ($validation->isValid())
        {

            $status = ' SELECT 
                    production_order.status 
                   FROM 
                    production_order
                   WHERE
                    production_order.id =                     
                    '.$op_id;

            $query_status = DB::query($status, array($op_id)); 

        
            if($query_status[0]['status'] == 0){

                $sql = 'UPDATE production_order_item
                        SET
                            quality_id = ?,
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
                            obs = ?,
                            defects_json = ?
                        WHERE id = ? ';

                $query = DB::exec($sql, array(
                    $this->quality_id,
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
                    $this->obs,
                    $this->defects_json,
                    $this->id
                ));

                $this->save_defects();

                return $this;
            }
            else{

                $user = $this->ActiveUser();
                $permissions = $user['permissions'];

                if(in_array('block', $permissions)){

                    $block_model = $this->LoadModel('Block', true);
                    $block_model->populate($this->block_id);

                    $block_model->quality_id = $this->quality_id;
                    $block_model->block_number = $this->block_number;
                    $block_model->tot_c = $this->tot_c;
                    $block_model->tot_a = $this->tot_a;
                    $block_model->tot_l = $this->tot_l;
                    $block_model->tot_vol = $this->tot_vol;
                    $block_model->tot_weight = $this->tot_weight;
                    $block_model->net_c = $this->net_c;
                    $block_model->net_a = $this->net_a;
                    $block_model->net_l = $this->net_l;
                    $block_model->net_vol = $this->net_vol;
                    $block_model->obs = $this->obs;
                    $block_model->defects_json = $this->defects_json;
                    $block_model->defects = $this->defects;
                    $block_model->id = $this->block_id;

                    //Carrega model da tabela 'quality' para pegar o id do 'block_model' e salvar na tabela 'block'
                    $quality_model = $this->LoadModel('Quality', true);
                    $quality_model->populate($block_model->quality_id);

                    if((int)$block_type > 0){
                        $block_model->type = $quality_model->block_type;
                    }

                    $block_model->save();

                    return $this;
                }
                else{

                    exit();
                }
            }

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
        $sql = 'DELETE FROM defect_poi WHERE production_order_item_id = ?';
        $params[0] = $this->id;
        $query = DB::exec($sql, $params);
    }

    private function insert_defects()
    {
        if (!is_null($this->defects) && !empty($this->defects))
        {
            foreach ($this->defects as $key => $value) {
                $sql = 'INSERT INTO defect_poi (production_order_item_id, defect_id) VALUES (?, ?)';
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
            $sql = 'UPDATE production_order_item SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));

            //$this->delete_defects();
            $this->excluido = 'S';
            return $this;
        }
        
        return $validation;
    }
    
    function get_header($id)
    {   
        $user = $this->ActiveUser();
        $permissions = $user['permissions'];

        if(in_array('block', $permissions)){

            $permissao = 1;
        }
        else{

            $permissao = 0;
        }

        $sql = 'SELECT
                    production_order.id,
                    production_order.excluido,
                    production_order.quarry_id,
                    quarry.name AS quarry_name,
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
                WHERE
                    production_order.excluido = "N"
                    AND production_order.id = ?
                ';
        $query = DB::query($sql, array($id));
        $query[0]['permissao'] = $permissao;
        return $query[0];
    }

    function get_by_po($po_id)
    {

        $status = 'SELECT 
                    production_order.status 
                   FROM 
                    production_order
                   WHERE
                    production_order.id = ?
                    ';

        $query_status = DB::query($status, array($po_id)); 

        
        if($query_status[0]['status'] == 0){

            $sql = 'SELECT
                        production_order_item.id,
                        production_order_item.excluido,
                        production_order_item.production_order_id,
                        production_order_item.block_number,
                        production_order_item.tot_c,
                        production_order_item.tot_a,
                        production_order_item.tot_l,
                        production_order_item.tot_vol,
                        production_order_item.tot_weight,
                        production_order_item.net_c,
                        production_order_item.net_a,
                        production_order_item.net_l,
                        production_order_item.net_vol,
                        production_order_item.quality_id,
                        quality.name AS quality_name,
                        production_order_item.obs,
                        production_order_item.defects_json
                        
                    FROM
                        production_order_item
                    LEFT JOIN
                        quality ON (quality.id = production_order_item.quality_id)
                    WHERE
                        production_order_item.production_order_id = ?
                        AND production_order_item.excluido = ?
                    ORDER BY
                        production_order_item.block_number
                    ';

                    $user = $this->ActiveUser();
                    $permissions = $user['permissions'];

             $query = DB::query($sql, array($po_id, 'N'), $permissao);     

            // carrega os defeitos dos blocos
            $defect_model = $this->LoadModel('Defect', true);
            $block_photo_model = $this->LoadModel('BlockPhoto', true);
            foreach ($query as $key => $row) {
                $query[$key]['defects'] = $defect_model->get_by_poi($row['id']);
                $query[$key]['photos'] = $block_photo_model->get_by_poi($row['id']);
            }
            
            return $query;

            }

        else{

           $sql = 'SELECT
                        block.id as block_id,
                        block.excluido,
                        block.production_order_item_id as id,
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
                        block.quality_id,
                        quality.name AS quality_name,
                        block.obs,
                        block.defects_json
                        
                   FROM
                        block
                    LEFT JOIN
                        quality ON (quality.id = block.quality_id)
                    INNER JOIN 
                          production_order_item ON (production_order_item.id = block.production_order_item_id)
                    INNER JOIN
                         production_order ON (production_order.id = production_order_item.production_order_id)
                    WHERE
                        production_order_item.production_order_id = ?
                        AND block.excluido = ?
                    ORDER BY
                        block.block_number
                    ';

            $query = DB::query($sql, array($po_id, 'N'));     

        // carrega os defeitos dos blocos
        $defect_model = $this->LoadModel('Defect', true);
        $block_photo_model = $this->LoadModel('BlockPhoto', true);
        foreach ($query as $key => $row) {

            $query[$key]['defects'] = $defect_model->get_by_block($row['block_id']);
            $query[$key]['photos'] = $block_photo_model->get_by_poi($row['id']);
        }
        
        return $query;
    }    


        
    }

    
}