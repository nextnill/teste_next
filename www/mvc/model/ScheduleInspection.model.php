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
    public $time;
    public $quarry_id;
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

        if (is_null($this->time) || (strlen($this->time) == 0))
        {
            $validation->add(Validation::VALID_ERR_FIELD, 'Enter the time');
        }

        if (is_null($this->quarry_id) || !is_numeric($this->quarry_id) || $this->quarry_id == 0)
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

        if ($validation->isValid())
        {
            $sql = 'INSERT INTO schedule_inspection (day, time, quarry_id, client_id, obs) VALUES (?, ?, ?, ?, ?) ';
            $query = DB::exec($sql, array(
                $this->day,
                $this->time,
                $this->quarry_id,
                $this->client_id,
                $this->obs
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
                $sql = '
                    UPDATE
                        schedule_inspection
                    SET
                        day = ?,
                        time = ?,
                        quarry_id = ?,
                        client_id = ?,
                        obs = ?
                    WHERE
                        id = ? ';
                $query = DB::exec($sql, array(
                    // set
                    $this->day,
                    $this->time,
                    $this->quarry_id,
                    $this->client_id,
                    $this->obs,
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
                    quarry_id,
                    client_id,
                    obs
                FROM
                    schedule_inspection
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

            $this->day = (string)$row_query['day'];
            $this->time = substr((string)$row_query['time'], 0, 5);
            $this->quarry_id = (int)$row_query['quarry_id'];
            $this->client_id = (int)$row_query['client_id'];
            $this->obs = (string)$row_query['obs'];
        }
    }
    
    function get_list($excluido=false, $quarry_id=null, $ano, $mes)
    {
        
        

        $sql =  'SELECT
                    schedule_inspection.id,
                    schedule_inspection.excluido,
                    schedule_inspection.day,
                    schedule_inspection.time,
                    schedule_inspection.quarry_id,
                    quarry.name AS quarry_name,
                    schedule_inspection.client_id,
                    client.name AS client_name,
                    client.name AS title,
                    schedule_inspection.obs,
                    CONCAT(schedule_inspection.day, " ", schedule_inspection.time) AS start    
                    FROM schedule_inspection
                    INNER JOIN quarry ON (quarry.id = schedule_inspection.quarry_id)
                    INNER JOIN client ON (client.id = schedule_inspection.client_id)
                    WHERE schedule_inspection.excluido = ?
                    ';

        $params[] = ($excluido === true ? 'S' : 'N');

        if ($quarry_id) {
            $sql .= ' AND schedule_inspection.quarry_id = ? ';
            $params[] = $quarry_id;
        }
        
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

        return $query;
    }   
}