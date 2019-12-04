<?php

require 'config/Database.php';
use Database\Database;

class Obat {

    private $db;
    private $table_name = "tbobat";

    public function __construct(){
        $this->db = new Database();
        $this->db->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function execute($query){
        $stmt = $this->db->query($query);
        if($stmt){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => $stmt->fetchAll(PDO::FETCH_ASSOC)
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );            
        }
        return $result;
    }

    public function all(){
        $fields = "*";
        $query = $this->db->select($this->table_name, $fields);
        if($query){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => $query
          );
        }else{
            if(count($query) < 1){
              $result = array(
                "code"      => 404,
                "message"   => "Data tidak ditemukan",
                "data"      => $query
              );                
            }else{
              $result = array(
                "code" => 400,
                "message" => $this->db->lasterror,
                "data" => array()
              );
            }
        }
        return $result;
    }

    public function get($id){
        $fields = "*";
        $query = $this->db->select($this->table_name, $fields, array("Kode_Obat" => $id) );
        if($query){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => $query
          );
        }else{
            if(count($query) < 1){
              $result = array(
                "code"      => 404,
                "message"   => "Data tidak ditemukan",
                "data"      => $query
              );                
            }else{
              $result = array(
                "code" => 400,
                "message" => $this->db->lasterror,
                "data" => array()
              );
            }
        }
        return $result;
    }

    public function save($data){
        $query = $this->db->insert($this->table_name, $data);
        if($query){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => array($data)
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );
        }
        return $result;
    }

    public function update($data, $id){
        $put = $this->db->update($this->table_name, $data, array('Kode_Obat' => $id ));
        if($put){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => array($data)
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );
        }
        return $result;
    }

    public function delete($id){
        $delete = $this->db->delete($this->table_name, array('Kode_Obat' => $id ));
        if($delete){
          $result = array(
            "code"      => 200,
            "message"   => "OK",
            "data"      => array()
          );
        }else{
          $result = array(
            "code" => 400,
            "message" => $this->db->lasterror,
            "data" => array()
          );
        }
        return $result;
    }

}