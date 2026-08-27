<?php

/**
 * ============================================================
 * CONEXIÓN A LA BASE DE DATOS - elicell
 * ============================================================
 */

$host = "localhost";
$dbname = "elicell";
$username = "root";
$password = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );

} catch (PDOException $e) {

    die(
        "No se pudo conectar con la base de datos. " .
        "Verifica que MySQL esté encendido y que exista la base de datos 'elicell'."
    );
}