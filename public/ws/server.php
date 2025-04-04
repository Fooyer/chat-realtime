<?php
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class ChatServer implements MessageComponentInterface {
    protected $clients;
    private $pdo;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        $this->clients = new \SplObjectStorage;

        // Conectar ao MariaDB
        $dsn = 'mysql:host=localhost;dbname=chatlive;charset=utf8mb4';
        $user = 'root';
        $password = 'root';

        try {
            $this->pdo = new \PDO($dsn, $user, $password);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            echo "Conectado ao banco de dados com sucesso.\n";
        } catch (\PDOException $e) {
            echo "Erro na conexão com o banco: " . $e->getMessage() . "\n";
            exit;
        }
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

            // Salvar no banco de dados
            try {
                $stmt = $this->pdo->prepare("INSERT INTO mensagens (username, message) VALUES (:username, :message)");
                $stmt->execute([
                    ':username' => $messageData['username'],
                    ':message' => $messageData['message']
                ]);
                echo "Mensagem salva no banco de dados.\n";
            } catch (\PDOException $e) {
                echo "Erro ao salvar mensagem: " . $e->getMessage() . "\n";
            }


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
