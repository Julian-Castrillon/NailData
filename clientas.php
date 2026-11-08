<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION["usuario_id"]) || $_SESSION['rol'] !== 'administradora') {
    header("Location: inicio.php");
    exit;
}

$nombre = $_SESSION["usuario"];

try {
    $consulta = "SELECT id_clienta, nombre, telefono, preferencias FROM clientas";
    $sentencia = $conexion->prepare($consulta);
    $sentencia->execute();
    $clientas = $sentencia->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $error) {
    echo "Error en la conexión o consulta: " . $error->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Clientas - NailData</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --color-primario: #e3a8b4; --color-primario-hover: #d195a1; --color-texto: #5a4a4d; --color-texto-claro: #8e8083; --color-blanco: #ffffff; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', Arial, sans-serif; background: linear-gradient(135deg, #fdf6f7 0%, #f5dde1 100%); color: var(--color-texto); min-height: 100vh; display: flex; flex-direction: column; }
        
        @keyframes aparecerFlotando { 0% { opacity: 0; transform: translateY(20px); } 100% { opacity: 1; transform: translateY(0); } }
        @keyframes aparecerSuave { 0% { opacity: 0; } 100% { opacity: 1; } }

        header { background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(12px); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.3); box-shadow: 0 4px 20px rgba(227, 168, 180, 0.05); position: sticky; top: 0; z-index: 10; }
        header h1 { margin: 0; font-size: 22px; font-weight: 600; color: var(--color-primario-hover); letter-spacing: 0.5px; }
        .usuario { font-size: 13px; display: flex; align-items: center; gap: 15px; animation: aparecerSuave 1s ease forwards; }
        
        .cerrar-sesion { display: inline-block; background: rgba(224, 174, 184, 0.8); color: white; padding: 8px 16px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 12px; transition: all 0.3s; }
        .cerrar-sesion:hover { background: #c78b97; transform: scale(0.95); }

        main { max-width: 1100px; margin: 40px auto; padding: 0 20px; flex-grow: 1; width: 100%; }

        .barra-superior { display: flex; justify-content: flex-start; margin-bottom: 25px; animation: aparecerSuave 0.6s ease forwards; }
        .boton-volver { display: inline-block; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(5px); color: var(--color-texto); padding: 10px 18px; text-decoration: none; border-radius: 10px; font-size: 13px; font-weight: 500; border: 1px solid rgba(255, 255, 255, 0.6); transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(227, 168, 180, 0.1); }
        .boton-volver:hover { background: var(--color-primario); color: var(--color-blanco); transform: translateX(-3px); border-color: var(--color-primario); }

        .tarjeta-tabla { background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(12px); padding: 30px; border-radius: 16px; box-shadow: 0 10px 30px rgba(227, 168, 180, 0.1); border: 1px solid rgba(255, 255, 255, 0.4); opacity: 0; animation: aparecerFlotando 0.6s ease forwards; animation-delay: 0.1s; }
        .tarjeta-tabla h2 { margin-top: 0; color: var(--color-primario-hover); font-size: 22px; margin-bottom: 5px; font-weight: 600; }
        .tarjeta-tabla p { color: var(--color-texto-claro); margin-bottom: 25px; font-size: 13px; }

        .tabla-contenedor { overflow-x: auto; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; }
        th { background-color: rgba(227, 168, 180, 0.3); color: var(--color-texto); font-weight: 600; padding: 14px 15px; border-bottom: 2px solid rgba(255, 255, 255, 0.5); }
        td { padding: 14px 15px; border-bottom: 1px solid rgba(227, 168, 180, 0.15); color: var(--color-texto); transition: background-color 0.2s ease; }
        tbody tr { transition: all 0.3s ease; }
        tbody tr:hover { background-color: rgba(255, 255, 255, 0.6); transform: scale(1.005); }
        .sin-datos { text-align: center; color: var(--color-texto-claro); padding: 30px; font-style: italic; }

        footer { text-align: center; padding: 20px; color: var(--color-texto-claro); font-size: 11px; margin-top: auto; animation: aparecerSuave 1s ease forwards; }

        @media (max-width: 768px) { header { flex-direction: column; padding: 15px; text-align: center; } .tarjeta-tabla { padding: 20px 15px; } }
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

        <section class="tarjeta-tabla">
            <h2>Listado de Clientas</h2>
            <p>Registros actuales consultados desde la base de datos del estudio.</p>

            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Preferencias</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($clientas)): ?>
                            <?php foreach ($clientas as $fila): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fila['id_clienta']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['telefono']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['preferencias']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="sin-datos">No hay registros de clientas encontrados.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <footer>Sistema de gestión para estudios de uñas | NailData &copy; 2026</footer>
</body>
</html>