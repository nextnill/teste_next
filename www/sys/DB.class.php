<?php
namespace Sys;

use PDO;

class DB
{    
    private static $instance;
    private $conn;
    private $dns;
    private $user;
    private $pass;
    //private $dns = 'mysql:host=dbmy0105.whservidor.com;dbname=nextsi;charset=utf8'; //uolhosts
    //private $user = 'nextsi';  //uolhosts
    //private $pass = 'Hus*s34iP{3f'; //uolhosts
    
    static function getInstance()
    {
        if (self::$instance == null) {
            $className = __CLASS__;
            self::$instance = new $className();
        }
        return self::$instance;
    }

    function connect($force=false)
    {
        if (is_null($this->conn) || $force)
        {
            try
            {
                $this->dns = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8';
                $this->user = DB_USER;
                $this->pass = DB_PASSWORD;
                $this->conn = new PDO($this->dns, $this->user, $this->pass);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->exec("set names utf8");
            }
            catch (PDOException $e)
            {
                print_r(array(
                    'DB::connect',
                    'não conectou',
                    array(
                        'pdo_exception' => 
                            array( 
                                'code' => $e->getCode(),
                                'message' => $e->getMessage(), 
                                'file' => $e->getFile(), 
                                'line' => $e->getLine()
                            ),
                        'query_sql' => $sql,
                        'query_params' => $params
                    )
                ));
                return false;
            }
        }
        // conectou
        return true;
    }
    
    static function query($sql, $params=null)
    {
        if (self::getInstance()->connect())
        {
            try
            {
                $stmt = self::getInstance()->conn->prepare($sql);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            catch (PDOException $e)
            {
                print_r(array(
                    'DB::query',
                    'erro na query',
                    array(
                        'pdo_exception' => 
                            array( 
                                'code' => $e->getCode(),
                                'message' => $e->getMessage(), 
                                'file' => $e->getFile(), 
                                'line' => $e->getLine()
                            ),
                        'query_sql' => $sql,
                        'query_params' => $params
                    )
                ));
                return false;
            }
        }
    }
    
    static function exec($sql, $params=null)
    {
        if (self::getInstance()->connect())
        {
            try
            {
                $stmt = self::getInstance()->conn->prepare($sql);
                return $stmt->execute($params);
            }
            catch (PDOException $e)
            {
                print_r(array(
                    'DB::query',
                    'erro na query',
                    array(
                        'pdo_exception' => 
                            array( 
                                'code' => $e->getCode(),
                                'message' => $e->getMessage(), 
                                'file' => $e->getFile(), 
                                'line' => $e->getLine()
                            ),
                        'query_sql' => $sql,
                        'query_params' => $params
                    )
                ));
                return false;
            }
        }
    }

    static function begin_transaction()
    {
        if (self::getInstance()->connect())
        {
            self::getInstance()->conn->beginTransaction();
        }
    }

    static function roll_back()
    {
        self::getInstance()->conn->rollBack();
    }

    static function commit()
    {
        self::getInstance()->conn->commit();
    }

    static function in_transaction()
    {
        return self::getInstance()->conn->inTransaction();
    }
    
    static function last_insert_id()
    {
        return (int)self::getInstance()->conn->lastInsertId();
    }

    static function has_rows(&$array)
    {
        if (sizeof($array) > 0)
        {
            return true;
        }
        return false;
    }

    static function check_to_sql($val)
    {
        return ($val == 'true' ? 'S' : 'N');
    }
    
}