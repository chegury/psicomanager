<?php
require_once __DIR__ . '/../classes/EnvLoader.php';
EnvLoader::load(__DIR__ . '/../.env');

// Configuração do Banco de Dados
$host = 'localhost';
$dbname = 'psicomanager';
$username = 'root';
$password = ''; // Senha padrão do XAMPP é vazia

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Configura o retorno dos dados como array associativo
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>
