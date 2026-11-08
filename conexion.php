<?php
$host = "localhost";
$bd = "naildata";
$usuario = "root";
$clave = "";

try {
    $conexion = new PDO(
        "mysql:host=$host;dbname=$bd;charset=utf8mb4",
        $usuario,$clave
    );
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    exit("Error al conectar con la base de datos.");
}
?>