<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Quality_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $order_number;
    public $name;
    
    function __construct()
    {
        parent::__construct();
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
        $query = DB::query('SELECT id FROM quality WHERE id = ? ', array($this->id));
        
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
            $sql = 'INSERT INTO quality (name, order_number) VALUES (?,
                        (SELECT coalesce(max(b.order_number), 0) + 1 FROM quality b WHERE b.excluido = \'N\')
                    ) ';
            $query = DB::exec($sql, array($this->name));

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
                $sql = '
                    UPDATE
                        quality
                    SET
                        name = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->name,
                    // where
                    $this->id
                ));

                return $this->id;
            }
        }
        
        return $valid;
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
            $sql = 'UPDATE quality SET excluido = ? WHERE id = ? ';
            $query = DB::exec($sql, array('S', $this->id));

            // ajusto a ordem dos registros com order number maior que o registro excluido (order_number -1)
            $sql = 'UPDATE quality SET order_number = order_number - 1 WHERE order_number > ? AND excluido = \'N\' ';
            $query = DB::exec($sql, array($this->order_number));
            
            return $this->id;
        }
        
        return $validation;
    }

    function change_order($type)
    {
        if ($type == 'up') {
            // verifica se não é o primeiro
            $query = DB::query('SELECT id FROM quality WHERE excluido = \'N\' AND order_number < ?', array($this->order_number));
            if (!DB::has_rows($query)) {
                // se for retorna uma notificação
                $validation = new Validation();
                $validation->add(Validation::VALID_NOTICE, 'Invalid change');
                return $validation;
                
            }
        }
        else if ($type == 'down') {
            // verifica se não é o último
            $query = DB::query('SELECT id FROM quality WHERE excluido = \'N\' AND order_number > ?', array($this->order_number));
            if (!DB::has_rows($query)) {
                // se for retorna uma notificação
                $validation = new Validation();
                $validation->add(Validation::VALID_NOTICE, 'Invalid change');
                return $validation;
            }
        }
        
        // id do item da posição atual
        $id_pos_atual = $this->id;

        // inicio as variaveis da posição atual e da nova posição
        $pos_atual = $this->order_number;
        $pos_nova = $pos_atual;

        // se for pra subir, a nova posição é do item anterior
        if ($type == 'up') {
            $pos_nova--;
        }
        // se for pra descer, a nova oposição é do item posterir
        else if ($type == 'down') {
            $pos_nova++;
        }

        // pesquiso id do item que está na futura nova posição
        $query = DB::query('SELECT id FROM quality WHERE excluido = \'N\' AND order_number = ?', array($pos_nova));
        $id_pos_nova = (int)$query[0]['id'];
        
        // sql para update dos registros
        $sql = 'UPDATE quality SET order_number = ? WHERE id = ?';

        // update do item que está na posição atual, com order_number do item que está na futura nova posição
        $params[0] = $pos_nova;
        $params[1] = $id_pos_atual;
        $query = DB::exec($sql, $params);

        // update do item que está na futura nova posição, com order_number do item que está posição atual
        $params[0] = $pos_atual;
        $params[1] = $id_pos_nova;
        $query = DB::exec($sql, $params);

        // atualizo o objeto atual
        $this->populate($id_pos_atual);

        // retorno o objeto
        return $this;
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
                    order_number,
                    name
                FROM
                    quality
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

            $this->order_number = (int)$row_query['order_number'];
            $this->name = (string)$row_query['name'];
        }
    }
    
    function get_list($excluido=false)
    {
        $excluido = ($excluido === true ? 'S' : 'N');
        
        $query = DB::query('SELECT id, excluido, order_number, name FROM quality WHERE excluido = ? ORDER BY order_number, name', array($excluido));
        
        return $query;
    }
    
}