<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class ScheduleInspection_Model extends \Sys\Model {
    
    public $id;
    public $excluido;

    public $day;
    //public $time;
    public $quarries;
    public $client_id;
    
    function __construct()
    {
        parent::__construct();
    }

    function validation()
    {
        $validation = new Validation();
        
        if (is_null($this->day) || (strlen($this->day) == 0))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the day');
        }

        /*
        // remoção solicitada pelo Thiago Pozati, Monte Santo
        if (is_null($this->time) || (strlen($this->time) == 0))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the time');
        }
        */
        
        if (is_null($this->quarries) || sizeof($this->quarries) == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the quarry');
        }

        if (is_null($this->client_id) || !is_numeric($this->client_id) || $this->client_id == 0)
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the client');
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
        $query = DB::query('SELECT id FROM schedule_inspection WHERE id = ? ', array($this->id));
        
        if (DB::has_rows($query))
        {
            return true;
        }
        return false;
    }
    
    function insert()
    {
        $validation = $this->validation();
        //print_r($this );
        //print_r($validation );exit;
        if ($validation->isValid())
        {
            $sql = 'INSERT INTO schedule_inspection (day, time, client_id, obs) VALUES (?, ?, ?, ?) ';
            $query = DB::exec($sql, array(
                $this->day,
                $this->time,
                $this->client_id,
                $this->obs
            ));

            $this->id = DB::last_insert_id();

            $this->save_quarries();

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
                        schedule_inspection
                    SET
                        day = ?,
                        time = ?,
                        client_id = ?,
                        obs = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->day,
                    $this->time,
                    $this->client_id,
                    $this->obs,
                    // where
                    $this->id
                ));
                
                $this->save_quarries();

                return $this->id;
            }
        }
        
        return $valid;
    }

    function save_quarries()
    {
        $validation = $this->validation();
        
        if ($validation->isValid())
        {
            $sql = 'DELETE FROM schedule_inspection_quarry WHERE schedule_inspection_id = ? ';
            $query = DB::exec($sql, array($this->id));

            if (is_array($this->quarries)) {
                foreach ($this->quarries as $key => $quarry) {
                    $sql = 'INSERT INTO schedule_inspection_quarry (schedule_inspection_id, quarry_id) VALUES (?, ?) ';
                    $query = DB::exec($sql, array($this->id, $quarry));
                }
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
            $sql = 'UPDATE schedule_inspection SET excluido = ? WHERE id = ? ';
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
                    day,
                    time,
                    client_id,
                    obs
                FROM
                    schedule_inspection
                WHERE id = ?',
                array($id)
            );

            if (DB::has_rows($query))
            {
                $query_quarries = DB::query(
                    '   SELECT quarry_id
                        FROM schedule_inspection_quarry
                        WHERE schedule_inspection_id = ?',
                    array($id)
                );

                $this->fill($query[0], $query_quarries);
                return $this->id;
            }
        }

        return $validation;
    }

    function fill($row_query, $query_quarries)
    {
        if ($row_query)
        {
            $this->id = (int)$row_query['id'];
            $this->excluido = (string)$row_query['excluido'];

            $this->day = (string)$row_query['day'];
            $this->time = substr((string)$row_query['time'], 0, 5);
            $this->client_id = (int)$row_query['client_id'];
            $this->obs = (string)$row_query['obs'];

            foreach ($query_quarries as $key => $quarry) {
                $this->quarries[] = $quarry['quarry_id'];
            }
        }
    }
    
    function get_list($excluido=false, $ano, $mes)
    {
        
        $sql =  'SELECT
                    schedule_inspection.id,
                    schedule_inspection.excluido,
                    schedule_inspection.day,
                    schedule_inspection.time,
                    schedule_inspection.quarry_id,
                    schedule_inspection.client_id,
                    client.name AS client_name,
                    client.name AS title,
                    schedule_inspection.obs,
                    CONCAT(schedule_inspection.day) AS start
                    FROM schedule_inspection
                    INNER JOIN client ON (client.id = schedule_inspection.client_id)
                    WHERE schedule_inspection.excluido = ?
                    ';

        $params[] = ($excluido === true ? 'S' : 'N');
        
        if ($ano){

            $sql .= 'AND Year(schedule_inspection.day) = ?';
            $params[] = $ano;
        }

        if ($mes){

            $sql .= 'AND Month(schedule_inspection.day) = ?';
            $params[] = $mes;
        }

        
        $sql .= 'ORDER BY schedule_inspection.day, schedule_inspection.time';

        $query = DB::query($sql, $params);

        foreach ($query as $key => $row) {
            $query[$key]['allDay'] = true;
        }

        return $query;
    }   
}