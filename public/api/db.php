<?php

class DB {
    protected $dbh;
    private $transaction;

    function __construct() {
        try {
            $this->dbh = new PDO('mysql:host=localhost;dbname=chatlive', 'root', 'root');
            $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception('Erro ao conectar ao banco de dados: ' . $e->getMessage());
        }
    }

    function query($query, $params = array()) {
        try {
            $stmt = $this->dbh->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC); // Utiliza o modo de fetch passado
        } catch (PDOException $e) {
            throw new Exception('Erro ao executar a consulta: ' . $e->getMessage());
        }
    }

    // Inicia uma transação
    function TStart() {
        try {
            $this->dbh->beginTransaction();
            $this->transaction = true;
        } catch (PDOException $e) {
            throw new Exception('Erro ao iniciar a transação: ' . $e->getMessage());
        }
    }

    // Faz commit da transação
    function TCommit() {
        try {
            $this->dbh->commit();
            $this->transaction = false;
        } catch (PDOException $e) {
            throw new Exception('Erro ao comitar a transação: ' . $e->getMessage());
        }
    }

    // Faz rollback da transação
    function TRollback() {
        try {
            if ($this->transaction == true) {
                return;
            }
            $this->dbh->rollBack();
        } catch (PDOException $e) {
            throw new Exception('Erro ao fazer rollback da transação: ' . $e->getMessage());
        }
    }

    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}

?>