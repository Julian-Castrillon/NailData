<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION['rol'] !== 'administradora') {
    header("Location: inicio.php");
    exit;
}

$nombre = $_SESSION["usuario"];
$clienta = null;
$error = null;
$id_buscado = '';

if (isset($_GET['id']) && trim($_GET['id']) !== '') {
    $id_buscado = trim($_GET['id']);
    try {
        $sql = "SELECT id_clienta, nombre, telefono, preferencias FROM clientas WHERE id_clienta = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id' => $id_buscado]);
        $clienta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$clienta) {
            $error = "No se encontró ninguna clienta con el ID: " . htmlspecialchars($id_buscado);
        }
    } catch (PDOException $e) {
        $error = "Error al ejecutar la consulta: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Clienta - NailData</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --color-primario: #e3a8b4; --color-primario-hover: #d195a1; --color-texto: #5a4a4d; --color-texto-claro: #8e8083; --color-blanco: #ffffff; --color-error: #d9534f; --bg-error: #f9e3e3; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', Arial, sans-serif; background: linear-gradient(135deg, #fdf6f7 0%, #f5dde1 100%); color: var(--color-texto); min-height: 100vh; display: flex; flex-direction: column; }
        
        @keyframes aparecerFlotando { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes aparecerSuave { 0% { opacity: 0; } 100% { opacity: 1; } }

        header { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(12px); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 4px 20px rgba(227, 168, 180, 0.05); position: sticky; top: 0; z-index: 10; }
        header h1 { margin: 0; font-size: 22px; font-weight: 600; color: var(--color-primario-hover); letter-spacing: 0.5px; }
        .usuario { font-size: 13px; display: flex; align-items: center; gap: 15px; animation: aparecerSuave 1s ease forwards; }
        .cerrar-sesion { display: inline-block; background: rgba(224, 174, 184, 0.8); color: white; padding: 8px 16px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 12px; transition: all 0.3s; }
        .cerrar-sesion:hover { background: #c78b97; transform: scale(0.95); }

        main { max-width: 650px; margin: 40px auto; padding: 0 20px; flex-grow: 1; width: 100%; }

        .barra-superior { display: flex; justify-content: flex-start; margin-bottom: 25px; animation: aparecerSuave 0.6s ease forwards; }
        .boton-volver { display: inline-block; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(5px); color: var(--color-texto); padding: 10px 18px; text-decoration: none; border-radius: 10px; font-size: 13px; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.6); transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(227, 168, 180, 0.1); }
        .boton-volver:hover { background: var(--color-primario); color: var(--color-blanco); transform: translateX(-3px); border-color: var(--color-primario); }

        .contenedor-busqueda { background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(12px); padding: 35px; border-radius: 16px; box-shadow: 0 10px 30px rgba(227, 168, 180, 0.1); border: 1px solid rgba(255, 255, 255, 0.4); opacity: 0; animation: aparecerFlotando 0.6s ease forwards; animation-delay: 0.1s; }
        .contenedor-busqueda h2 { margin-top: 0; color: var(--color-primario-hover); font-size: 22px; margin-bottom: 20px; font-weight: 600; text-align: center; }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 14px; color: var(--color-texto); }
        input[type="text"] { width: 100%; padding: 12px 15px; font-family: 'Poppins', sans-serif; font-size: 14px; color: var(--color-texto); background: rgba(255, 255, 255, 0.5); border: 1px solid rgba(227, 168, 180, 0.5); border-radius: 10px; outline: none; transition: all 0.3s ease; }
        input[type="text"]:focus { background: rgba(255, 255, 255, 0.8); border-color: var(--color-primario-hover); box-shadow: 0 0 0 3px rgba(227, 168, 180, 0.2); }

        button[type="submit"] { width: 100%; padding: 12px 20px; background-color: var(--color-primario); color: white; border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; font-family: 'Poppins', sans-serif; box-shadow: 0 4px 12px rgba(227, 168, 180, 0.25); }
        button[type="submit"]:hover { background-color: var(--color-primario-hover); box-shadow: 0 6px 15px rgba(209, 149, 161, 0.35); transform: translateY(-2px); }

        .alert { padding: 15px; background-color: var(--bg-error); color: var(--color-error); border-radius: 10px; margin-top: 20px; font-size: 13px; text-align: center; border: 1px solid rgba(217, 83, 79, 0.2); animation: aparecerSuave 0.5s ease forwards; }

        .result-card { margin-top: 30px; padding-top: 25px; border-top: 1px solid rgba(255, 255, 255, 0.5); animation: aparecerFlotando 0.5s ease forwards; }
        .result-card h3 { color: var(--color-primario-hover); font-size: 18px; margin-bottom: 15px; text-align: center; }

        .table-info { width: 100%; border-collapse: collapse; font-size: 13px; }
        .table-info th, .table-info td { padding: 12px 15px; border-bottom: 1px solid rgba(227, 168, 180, 0.15); text-align: left; }
        .table-info th { font-weight: 600; color: var(--color-texto); width: 40%; background: rgba(255, 255, 255, 0.3); border-radius: 8px 0 0 8px; }
        .table-info td { color: var(--color-texto); background: rgba(255, 255, 255, 0.1); border-radius: 0 8px 8px 0; }

        footer { text-align: center; padding: 20px; color: var(--color-texto-claro); font-size: 11px; margin-top: auto; animation: aparecerSuave 1s ease forwards; }

        @media (max-width: 600px) { header { flex-direction: column; text-align: center; } .contenedor-busqueda { padding: 20px; } }
    </style>
</head>
<body>
    <header>
        <h1>NailData</h1>
        <div class="usuario">
            <span>Bienvenida(o), <strong><?= htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") ?></strong></span>
            <a href="cerrar_sesion.php" class="cerrar-sesion">Cerrar sesión</a>
        </div>
    </header>

    <main>
        <div class="barra-superior">
            <a href="inicio.php" class="boton-volver">← Volver al panel principal</a>
        </div>

        <div class="contenedor-busqueda">
            <h2>Consulta Individual</h2>
            
            <form method="GET" action="clienta.php">
                <div class="form-group">
                    <label for="id">ID de la Clienta (Ej: 2, 3...):</label>
                    <input type="text" id="id" name="id" value="<?= htmlspecialchars($id_buscado) ?>" placeholder="Ingrese el ID..." required>
                </div>
                <button type="submit">Buscar Clienta</button>
            </form>

            <?php if ($error): ?>
                <div class="alert"><?= $error ?></div>
            <?php endif; ?>

            <?php if ($clienta): ?>
                <div class="result-card">
                    <h3>Información Detallada</h3>
                    <table class="table-info">
                        <tr>
                            <th>ID</th>
                            <td><?= htmlspecialchars($clienta['id_clienta']) ?></td>
                        </tr>
                        <tr>
                            <th>Nombre Completo</th>
                            <td><?= htmlspecialchars($clienta['nombre']) ?></td>
                        </tr>
                        <tr>
                            <th>Teléfono</th>
                            <td><?= htmlspecialchars($clienta['telefono']) ?></td>
                        </tr>
                        <tr>
                            <th>Preferencias</th>
                            <td><?= htmlspecialchars($clienta['preferencias']) ?></td>
                        </tr>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer>Sistema de gestión para estudios de uñas | NailData &copy; 2026</footer>
</body>
</html>