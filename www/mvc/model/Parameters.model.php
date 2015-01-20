<?php
/**
 *
 * @author Bighetti
 *
 */

use \Sys\DB;
use \Sys\Validation;

class Parameters_Model extends \Sys\Model {
    
    function __construct()
    {
        parent::__construct();
    }

    static function get($field)
    {
        $sql = "SELECT {$field} FROM parameters WHERE excluido = ? ";
        // where
        $params[] = 'N';

        $query = DB::query($sql, $params);

        if (DB::has_rows($query)) {
            return $query[0][$field];
        }

        return null;
    }

    static function set($field, $value)
    {
        $sql = "UPDATE parameters SET {$field} = ? WHERE excluido = ? ";
        // set
        $params[] = $value;
        // where
        $params[] = 'N';

        return $query = DB::exec($sql, $params);
    }

    static function next_val($field)
    {
        $sql = "UPDATE parameters SET {$field} = {$field}+1 WHERE excluido = ? ";
        $params[] = 'N';
        $query = DB::exec($sql, $params);

        return self::get($field);
    }
    
}