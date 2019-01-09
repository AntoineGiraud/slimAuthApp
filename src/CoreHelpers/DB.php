<?php

namespace CoreHelpers;
use \PDO;

class DB{

    private $host     = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'visulignes';
    private $db;

    public function __construct($host = null, $username = null, $password = null, $database = null){
        if(!empty($host) && !empty($username) && !empty($database)){
            $this->host     = $host;
            $this->username = $username;
            $this->password = $password;
            $this->database = $database;
        }

        try{
            $this->db = new PDO('mysql:host='.$this->host.';dbname='.$this->database, $this->username, $this->password, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING
            ));
        }catch(PDOException $e){
            die('<h1>Impossible de se connecter a la base de donnee</h1><p>'.$e->getMessage().'<p>');
        }
        $this->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");
    }
    public function quote($str){
        return $this->db->quote($str);
    }

    public function query($sql, $data = array(),$fetch = PDO::FETCH_ASSOC){
        try{
            $req = $this->db->prepare($sql);
            $req->execute($data);
            if(!preg_match('/^(INSERT|UPDATE|DELETE|TRUNCATE|DROP|CREATE)/', $sql)){
                if (!empty($fetch))
                    return $req->fetchAll($fetch);
                else
                    return $req->fetchAll();
            }else
                return $this->db->lastInsertId();
        }catch(PDOException $e){
            die('<h3>Erreur : </h3><p>'.$e->getMessage().'</p>');
        }
    }

    public function exec($sql){
        try{
            return $this->db->exec($sql);
        }catch(PDOException $e){
            die('<h3>Erreur : </h3><p>'.$e->getMessage().'</p>');
        }
    }

    public function queryFirst($sql, $data = array(),$fetch = PDO::FETCH_ASSOC){
        try{
            $req = $this->db->prepare($sql);
            $req->execute($data);

            if (!empty($fetch))
                $return = $req->fetch($fetch);
            else
                $return = $req->fetch();
            if(!empty($return))
                return $return;
            else
                return array();
        }catch(PDOException $e){
            die('<h3>Erreur : </h3><p>'.$e->getMessage().'</p>');
        }
    }
}