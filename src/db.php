<?php
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'tienda';
$user = getenv('DB_USER') ?: 'tienda_user';
$pass = getenv('DB_PASSWORD') ?: 'tienda_password';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("<h3>Error de conexión a la base de datos:</h3> <p>" . htmlspecialchars($e->getMessage()) . "</p><p><em>Nota: Si acabas de levantar los contenedores, MySQL puede tardar unos segundos en inicializar la base de datos. Por favor recarga en unos instantes.</em></p>");
}
