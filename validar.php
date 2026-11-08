<?php
session_start();
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.html");
    exit;
}

$user = trim($_POST['usuario']);
$pass = trim($_POST['clave']);

if ($user === "" || $pass === "") {
    header("Location: index.html");
    exit;
}

try {
    // Validamos directamente usuario y contrasena (texto plano como en tus INSERTS de NailData)
    $sql = "SELECT id_usuario, usuario, rol FROM usuario WHERE usuario = :u AND contrasena = :p";
    $stmt = $conexion->prepare($sql);
    $stmt->execute(['u' => $user, 'p' => $pass]);
    
    $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cuenta) {
        // Renovar ID por seguridad y guardar sesiones
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $cuenta['id_usuario'];
        $_SESSION['usuario'] = $cuenta['usuario'];
        $_SESSION['rol'] = $cuenta['rol'];
        
        header("Location: inicio.php");
        exit;
    } else {
        header("Location: index.html?error=1");
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    exit("Ocurrió un error de base de datos.");
}
?>