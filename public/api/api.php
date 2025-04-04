<?php

include_once  'db.php';

class Api {

    function __construct(){
        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json; charset=UTF-8");
        header("Access-Control-Allow-Methods: *");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit;
        }
    }

    function method($method){
        if ($method !== $_SERVER['REQUEST_METHOD']){
            throw new InvalidArgumentException('Method not allowed');
        }
    }

    function sendResponse($code, $data){
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        $this->end();
    }
    
    function getJson(){
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data === null) {
            throw new InvalidArgumentException('Invalid JSON data');
        }
        return $data;
    }

    function validarCamposObrigatoriosBody($camposObrigatorios, $data) {
        $camposFaltando = array();
        foreach ($camposObrigatorios as $campo) {
            if ((!isset($data[$campo]) || empty($data[$campo]))) {
                $camposFaltando[] = $campo;
            }
        }

        if (!empty($camposFaltando)) {
            throw new InvalidArgumentException('Preencha os campos obrigatórios');
        }
    }

    function end(){
        exit();
    }

    function obterBody(){
        $request_body = file_get_contents('php://input');
        if (empty($request_body)) {
            throw new InvalidArgumentException('No data provided');
        }
        $data = json_decode($request_body, true);
        if ($data === null) {
            throw new InvalidArgumentException('Invalid JSON data');
        }
        return $data;
    }

    function obterParametro($parametro, $obrigatorio = false){

        if ($obrigatorio && (!isset($_GET[$parametro]) || empty($_GET[$parametro]))){
            throw new InvalidArgumentException('Parâmetro não informado: ' . $parametro);
        }

        if (!isset($_GET[$parametro])){
            return null;
        }

        return $_GET[$parametro];
    }

    public function tratarException(Exception $exception) {
        if ($exception instanceof InvalidArgumentException) {
            $this->sendResponse(400, array('message' => $exception->getMessage(), 'success' => false));
        } else {
            $this->sendResponse(500, array('message' => $exception->getMessage(), 'success' => false));
        }
    }

    public function obterHeader($header){
        $headers = getallheaders();
        if (!isset($headers[$header])){
            throw new InvalidArgumentException('Header não informado: ' . $header);
        }
        return $headers[$header];
    }

    public function validarToken(){
        $token = $this->obterHeader('Authorization');
        if (empty($token)){
            throw new InvalidArgumentException('Token não informado');
        }
        $db = new DB();
        $query = "SELECT * FROM usuario WHERE token = :token";
        $params = array(':token' => $token);
        $result = $db->query($query, $params);
        if (count($result) == 0){
            throw new InvalidArgumentException('Token inválido');
        }
    }

}

?>