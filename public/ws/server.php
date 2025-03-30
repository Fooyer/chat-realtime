<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "Nova conexão ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo "Mensagem recebida do cliente {$from->resourceId}: ";
        var_dump($msg); // Exibe a mensagem bruta recebida no terminal

        $messageData = json_decode($msg, true);

        // Verifica se a decodificação foi bem-sucedida
        if (!is_array($messageData)) {
            echo "Erro: Mensagem recebida não é um JSON válido.\n";
            return;
        }

        // Verifica se é uma mensagem de chat
        if (isset($messageData['type']) && $messageData['type'] === 'message') {
            if (!isset($messageData['username']) || !isset($messageData['message'])) {
                echo "Erro: Mensagem inválida. JSON recebido:\n";
                var_dump($messageData);
                return;
            }

            // Criar resposta formatada
            $response = json_encode([
                'username' => $messageData['username'],
                'message' => $messageData['message'],
                'timestamp' => date('H:i:s')
            ]);

            // Enviar mensagem para todos os clientes conectados
            foreach ($this->clients as $client) {
                $client->send($response);
            }

        } else {
            echo "Mensagem ignorada (não é do tipo 'message').\n";
        }
    }


    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Conexão {$conn->resourceId} foi fechada\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Erro: {$e->getMessage()}\n";
        $conn->close();
    }
}

$server = \Ratchet\Server\IoServer::factory(
    new \Ratchet\Http\HttpServer(
        new \Ratchet\WebSocket\WsServer(
            new ChatServer()
        )
    ),
    8080
);

echo "Servidor WebSocket rodando na porta 8080...\n";
$server->run();
