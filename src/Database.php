<?php
/**
 * Created by PhpStorm.
 * User: raphaeu
 * Date: 29/11/18
 * Time: 16:18
 */

namespace raphaeu;


class Database
{
    public $host;
    public $user;
    public $password;
    public $db;
    public $link;

    function __construct($host,$user, $password , $db)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password= $password;
        $this->db = $db;
    }

    public function connect()
    {
        if (is_null($this->link))
        {
            try {
                $this->link = new \PDO("mysql:host=".$this->host.";dbname=".$this->db,$this->user, $this->password);
                $this->link->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch(PDOException $e) {
                throw new Exception("Erro ao conectar com: {$this->host} msg:" . $e->getMessage());
            }

        }
        return $this->link;
    }
}
