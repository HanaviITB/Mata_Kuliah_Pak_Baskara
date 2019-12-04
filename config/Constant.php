<?php

class Constant {

    public function __construct(){}

    public function getIndex(){
        $result = array(
            "code"      => 200,
            "message"   => "Selamat Datang Di Layanan Klinik Menuju Indonesia 1 Data Kesehatan ~ Developer By : HANAVI ~ Magister ITB",
            "data"      => array() 
        );
        return $result;
    }

    public function notAllowed($method){
        $result = array(
            "code"      => 405,
            "message"   => "method '" . $method . "' not allowed!",
            "data"      => array() 
        );
        return $result;
    }

    public function pageNotFound($link){
        $result = array(
            "code"      => 404,
            "message"   => "page not found",
            "data"      => array(array("path" => $link))   
        );          
        return $result;
    }

    public function invalidKey(){
        $result = array(
            "code"      => 403,
            "message"   => "key is expired or invalid",
            "data"      => array() 
        );          
        return $result;
    }

    public function renderJSON($result){
		header("Access-Control-Allow-Origin: *");
		header("Content-Type: application/json; charset=UTF-8");

		echo json_encode($result);
    }

    public function base_url(){
    	$root = (isset($_SERVER['HTTPS']) ? "https://" : "http://").$_SERVER['HTTP_HOST'];
    	$root .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);

    	return $root;
    }

}


?>