<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION["usuario_id"])) {
    header("Location: index.html");
    exit;
}

$nombre_mostrar = $_SESSION["usuario"]; // Por defecto (ej. admin)

// Si es clienta, buscar su nombre real
if ($_SESSION['rol'] === 'clienta') {
    try {
        $sql = "SELECT nombre FROM clientas WHERE id_usuario = :id_usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->execute(['id_usuario' => $_SESSION['usuario_id']]);
        $datos_clienta = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($datos_clienta && !empty($datos_clienta['nombre'])) {
            $nombre_mostrar = $datos_clienta['nombre'];
        }
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Principal - NailData</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --color-primario: #e3a8b4; 
            --color-primario-hover: #d195a1; 
            --color-texto: #5a4a4d; 
            --color-texto-claro: #8e8083;
            --color-blanco: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #fdf6f7 0%, #f5dde1 100%);
            color: var(--color-texto);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes aparecerFlotando {
            0% { opacity: 0; transform: translateY(25px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes aparecerSuave {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        header {
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(10px);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            box-shadow: 0 4px 20px rgba(227, 168, 180, 0.15); 
            position: sticky;
            top: 0;
            z-index: 10;
        }

        header h1 { margin: 0; font-size: 22px; font-weight: 600; color: var(--color-primario-hover); letter-spacing: 0.5px; }

        .usuario { font-size: 13px; display: flex; align-items: center; gap: 15px; animation: aparecerSuave 1s ease forwards; }

        .cerrar-sesion {
            display: inline-block;
            background: #e0aeb8; 
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s;
        }
        .cerrar-sesion:hover { background: #c78b97; transform: scale(0.95); }

        main { max-width: 750px; margin: 40px auto; padding: 0 20px; flex-grow: 1; width: 100%; }

        .bienvenida { text-align: center; margin-bottom: 40px; animation: aparecerSuave 0.8s ease forwards; }
        .bienvenida h2 { margin-top: 0; margin-bottom: 8px; color: var(--color-primario-hover); font-weight: 600; font-size: 22px; }
        .bienvenida p { color: var(--color-texto-claro); line-height: 1.5; font-size: 13px; max-width: 500px; margin: 0 auto; }

        .modulos { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }

        .tarjeta {
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(10px);
            padding: 30px 25px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(227, 168, 180, 0.15);
            border: 1px solid rgba(255, 255, 255, 1); 
            display: flex; flex-direction: column; align-items: center; text-align: center;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            opacity: 0; animation: aparecerFlotando 0.6s ease forwards;
        }
        .tarjeta:nth-child(2) { animation-delay: 0.15s; }
        .tarjeta:hover { transform: translateY(-8px) scale(1.02); box-shadow: 0 15px 35px rgba(209, 149, 161, 0.25); background: rgba(255, 255, 255, 0.9); }

        .tarjeta h3 { color: var(--color-texto); margin-top: 0; margin-bottom: 10px; font-size: 17px; font-weight: 600; }
        .tarjeta p { color: var(--color-texto-claro); line-height: 1.5; font-size: 13px; margin-bottom: 25px; flex-grow: 1; }

        .boton {
            display: block; width: 100%; text-align: center; background: var(--color-primario);
            color: var(--color-blanco); padding: 12px 15px; text-decoration: none; border-radius: 10px;
            font-weight: 600; font-size: 13px; transition: all 0.3s ease; box-shadow: 0 4px 12px rgba(227, 168, 180, 0.3);
        }
        .boton:hover { background: var(--color-primario-hover); box-shadow: 0 6px 15px rgba(209, 149, 161, 0.4); }

        footer { text-align: center; padding: 20px; color: var(--color-texto-claro); font-size: 11px; margin-top: auto; animation: aparecerSuave 1s ease forwards; }

        @media (max-width: 600px) {
            header { flex-direction: column; padding: 15px; text-align: center; }
            .usuario { flex-direction: column; gap: 8px; }
            main { margin: 25px auto; }
        }
    </style>
</head>
<body>

    <header>
        <h1>NailData</h1>
        <div class="usuario">
            <span>Bienvenida(o), <strong><?= htmlspecialchars($nombre_mostrar, ENT_QUOTES, "UTF-8") ?></strong></span>
            <a href="cerrar_sesion.php" class="cerrar-sesion">Cerrar sesión</a>
        </div>
    </header>

    <main>
        <section class="bienvenida">
            <h2>Panel Principal</h2>
            <p>Administra la información de tu estudio, gestiona a tus clientas y mantén el control de tus citas de manera sencilla.</p>
        </section>

        <?php if ($_SESSION['rol'] === 'clienta'): ?>
            <section class="modulos" style="display: flex; justify-content: center;">
                <article class="tarjeta" style="width: 100%; max-width: 400px;">
                    <h3 style="color: #c2185b;">Atención</h3>
                    <p>Aún no tienes citas registradas en el sistema.</p>
                </article>
            </section>
        <?php else: ?>
            <section class="modulos">
                <article class="tarjeta">
                    <h3>Todas las clientas</h3>
                    <p>Consulta el listado general de clientas registradas y revisa su información básica.</p>
                    <a href="clientas.php" class="boton">Ver listado</a>
                </article>

                <article class="tarjeta">
                    <h3>Buscar por ID</h3>
                    <p>Encuentra información detallada y el historial de citas ingresando la identificación.</p>
                    <a href="clienta.php" class="boton">Buscar clienta</a>
                </article>

                <article class="tarjeta">
                    <h3>Registrar cita</h3>
                    <p>Asigna una nueva cita a las clientas registradas, verificando que el horario esté disponible.</p>
                    <a href="agendar.php" class="boton">Agendar cita</a>
                </article>
            </section>
        <?php endif; ?>
    </main>

    <footer>Sistema de gestión para estudios de uñas | NailData &copy; 2026</footer>

</body>
</html>