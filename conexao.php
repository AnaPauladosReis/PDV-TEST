<?php

require_once('config.php');

date_default_timezone_set('America/Sao_Paulo');

try {
    $pdo = new PDO("mysql:host=$servidor;dbname=$banco", "$usuario", "$senha");
    
    // ☆ Boa prática: Força o PDO a lançar exceções em caso de erro.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // ☆ Se a conexão falhar, não há nada que o resto do script possa fazer.
    die('Erro ao conectar com o banco de dados: ' . $e->getMessage());
}
