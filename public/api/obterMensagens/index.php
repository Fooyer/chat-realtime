<?php

include_once '../api.php';
include_once '../db.php';

try {
    $api = new Api(false);
    $db = new DB();

    $api->method('GET');

    $query = "SELECT * FROM mensagens";

    $db->TStart();

    $resultado = $db->query($query);
    
    $db->TCommit();
    
    $api->sendResponse(200, $resultado);

} catch (Exception $e) {
    $api->tratarException($e);
}

?>