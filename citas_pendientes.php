<?php
require_once 'conexion.php';
session_start();

if (!isset($_SESSION["usuario_id"])) {
    header("Location: index.html");
    exit;
}

$mensaje = "";
$tipo_mensaje = "";

// ---------------------------------------------------------
// PROCESAR LA CANCELACIÓN (Si el usuario presionó el botón)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cita_cancelar'])) {
    //Aca obtiene el id de la cita
    $id_cita = $_POST['id_cita_cancelar'];
    
    try {
        // Hacemos un UPDATE en lugar de DELETE para mantener el historial
        $sql_cancelar = "UPDATE citas SET estado = 'cancelada' WHERE id_cita = :id_cita";
        $stmt_cancelar = $conexion->prepare($sql_cancelar);
        $stmt_cancelar->bindParam(':id_cita', $id_cita);
        
        if ($stmt_cancelar->execute()) {
            $mensaje = "Cita cancelada correctamente. El espacio vuelve a estar disponible.";
            $tipo_mensaje = "exito";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al cancelar la cita: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// ---------------------------------------------------------
// PROCESAR CITA REALIZADA (Agregado nuevo)
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_cita_realizada'])) {
    $id_cita = $_POST['id_cita_realizada'];
    
    try {
        $sql_realizada = "UPDATE citas SET estado = 'realizada' WHERE id_cita = :id_cita";
        $stmt_realizada = $conexion->prepare($sql_realizada);
        $stmt_realizada->bindParam(':id_cita', $id_cita);
        
        if ($stmt_realizada->execute()) {
            $mensaje = "Cita marcada como realizada correctamente.";
            $tipo_mensaje = "exito";
        }
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar la cita: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}

// ---------------------------------------------------------
// CONSULTAR LAS CITAS DEL DÍA
// ---------------------------------------------------------
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');
$citas = [];

try {
    // ¡NUEVO!: Agregamos c.id_cita en el SELECT para saber cuál cancelar
    $sql = "SELECT c.id_cita, c.hora_inicio, c.hora_fin, c.estado, 
                   cl.nombre AS nombre_clienta, 
                   s.nombre AS nombre_servicio 
            FROM citas c
            JOIN clientas cl ON c.id_usuario = cl.id_usuario
            JOIN servicio s ON c.id_servicio = s.id_servicio
            WHERE c.fecha = :fecha
            ORDER BY c.hora_inicio ASC";
            
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':fecha', $fecha_filtro);
    $stmt->execute();
    
    $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensaje = "Error al consultar la agenda: " . $e->getMessage();
    $tipo_mensaje = "error";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda de Citas - NailData</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-primario: #e3a8b4; 
            --color-primario-hover: #d195a1; 
            --color-texto: #5a4a4d; 
            --color-texto-claro: #8e8083;
            --color-blanco: #ffffff;
            --color-error: #d9534f;
            --bg-error: #f9e3e3;
            --color-exito: #2e7d32;
            --bg-exito: #e8f5e9;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #fdf6f7 0%, #f5dde1 100%);
            color: var(--color-texto);
            min-height: 100vh; display: flex; flex-direction: column;
        }

        @keyframes aparecerFlotando {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        @keyframes aparecerSuave {
            0% { opacity: 0; } 100% { opacity: 1; }
        }

        /* --- CABECERA Y BOTONES --- */
        header {
            background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(12px);
            padding: 15px 40px; display: flex; justify-content: space-between;
            align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: sticky; top: 0; z-index: 10;
        }
        header h1 { font-size: 22px; font-weight: 600; color: var(--color-primario-hover); }
        .cerrar-sesion {
            background: rgba(224, 174, 184, 0.8); color: white; padding: 8px 16px;
            text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 12px; transition: 0.3s;
        }
        .cerrar-sesion:hover { background: #c78b97; transform: scale(0.95); }

        main { max-width: 800px; margin: 40px auto; padding: 0 20px; flex-grow: 1; width: 100%; }

        .boton-volver {
            display: inline-block; background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(5px);
            color: var(--color-texto); padding: 10px 18px; text-decoration: none;
            border-radius: 10px; font-size: 13px; font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.6); margin-bottom: 25px; transition: 0.3s;
        }
        .boton-volver:hover { background: var(--color-primario); color: var(--color-blanco); }

        /* --- FILTRO DE FECHA --- */
        .tarjeta-filtro {
            background: rgba(255, 255, 255, 0.3); backdrop-filter: blur(12px);
            padding: 20px 30px; border-radius: 16px; box-shadow: 0 5px 20px rgba(227, 168, 180, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5); margin-bottom: 30px;
            display: flex; justify-content: space-between; align-items: center;
            animation: aparecerFlotando 0.4s ease forwards;
        }
        .tarjeta-filtro h2 { font-size: 18px; color: var(--color-primario-hover); }
        .formulario-filtro { display: flex; gap: 10px; align-items: center; }
        .formulario-filtro input {
            padding: 10px 15px; font-family: 'Poppins', sans-serif; font-size: 13px;
            background: rgba(255, 255, 255, 0.7); border: 1px solid rgba(227, 168, 180, 0.4);
            border-radius: 8px; outline: none; color: var(--color-texto);
        }
        .formulario-filtro button {
            padding: 10px 20px; background-color: var(--color-primario); color: white;
            border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: 0.3s;
        }
        .formulario-filtro button:hover { background-color: var(--color-primario-hover); }

        /* --- ALERTAS --- */
        .alerta { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; text-align: center; animation: aparecerSuave 0.5s ease forwards; }
        .alerta-error { background-color: var(--bg-error); color: var(--color-error); border: 1px solid rgba(217, 83, 79, 0.2); }
        .alerta-exito { background-color: var(--bg-exito); color: var(--color-exito); border: 1px solid rgba(46, 125, 50, 0.2); }

        /* --- TARJETAS DE CITAS --- */
        .lista-citas { display: flex; flex-direction: column; gap: 15px; }
        
        .cita-card {
            background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(10px);
            border-radius: 12px; padding: 20px; border: 1px solid rgba(255, 255, 255, 0.6);
            display: flex; align-items: center; justify-content: space-between;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: aparecerFlotando 0.6s ease forwards;
        }
        .cita-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(227, 168, 180, 0.2); background: rgba(255, 255, 255, 0.6); }

        /* Cita cancelada se ve más opaca */
        .cita-cancelada { opacity: 0.6; filter: grayscale(100%); }
        .cita-cancelada:hover { transform: none; box-shadow: none; cursor: not-allowed; }
        
        /* Cita realizada (Agregado nuevo) */
        .cita-realizada { border-left: 4px solid var(--color-exito); opacity: 0.85; }

        .cita-hora {
            font-size: 18px; font-weight: 600; color: var(--color-primario-hover);
            min-width: 150px; border-right: 2px solid rgba(227, 168, 180, 0.3); padding-right: 15px;
        }
        .cita-hora span { display: block; font-size: 11px; color: var(--color-texto-claro); font-weight: 400; margin-top: 2px;}
        
        .cita-info { flex-grow: 1; padding-left: 20px; }
        .cita-info h3 { font-size: 16px; margin-bottom: 4px; color: var(--color-texto); }
        .cita-info p { font-size: 13px; color: var(--color-texto-claro); }

        .acciones-cita { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; }

        .cita-estado {
            font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; text-transform: uppercase;
        }
        .estado-pendiente { background: rgba(227, 168, 180, 0.2); color: var(--color-primario-hover); }
        .estado-cancelada { background: var(--bg-error); color: var(--color-error); }
        .estado-realizada { background: var(--bg-exito); color: var(--color-exito); } /* Agregado nuevo */

        /* Contenedor de botones (Agregado nuevo) */
        .grupo-botones { display: flex; gap: 8px; }

        .btn-cancelar, .btn-realizada {
            background: none; border: 1px solid var(--color-error); color: var(--color-error);
            padding: 5px 12px; border-radius: 6px; font-size: 11px; font-family: 'Poppins', sans-serif;
            cursor: pointer; transition: 0.3s; font-weight: 500;
        }
        .btn-cancelar:hover { background: var(--color-error); color: white; }

        /* Botón de realizada (Agregado nuevo) */
        .btn-realizada { border: 1px solid var(--color-exito); color: var(--color-exito); }
        .btn-realizada:hover { background: var(--color-exito); color: white; }

        .mensaje-vacio { text-align: center; padding: 40px; color: var(--color-texto-claro); background: rgba(255, 255, 255, 0.2); border-radius: 12px; font-size: 14px; }
        footer { text-align: center; padding: 20px; font-size: 11px; margin-top: auto; }
    </style>
</head>
<body>

    <header>
        <h1>NailData</h1>
        <a href="cerrar_sesion.php" class="cerrar-sesion">Cerrar sesión</a>
    </header>

    <main>
        <a href="inicio.php" class="boton-volver">← Volver al panel principal</a>

        <div class="tarjeta-filtro">
            <h2>Agenda del día</h2>
            <form method="GET" action="citas_pendientes.php" class="formulario-filtro">
                <input type="date" name="fecha" value="<?= htmlspecialchars($fecha_filtro) ?>" required>
                <button type="submit">Buscar</button>
            </form>
        </div>

        <?php if ($mensaje): ?>
            <div class="alerta <?= $tipo_mensaje == 'error' ? 'alerta-error' : 'alerta-exito' ?>">
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <div class="lista-citas">
            <?php if (empty($citas)): ?>
                <div class="mensaje-vacio">No tienes citas programadas para esta fecha</div>
            <?php else: ?>
                <?php foreach ($citas as $cita): ?>
                    
                    <?php 
                        $hora_in_formato = date("h:i A", strtotime($cita['hora_inicio']));
                        $hora_fin_formato = date("h:i A", strtotime($cita['hora_fin']));
                        
                        // Lógica agregada para manejar las clases
                        $es_cancelada = ($cita['estado'] === 'cancelada');
                        $es_realizada = ($cita['estado'] === 'realizada');
                        $es_pendiente = ($cita['estado'] === 'pendiente');

                        $clase_tarjeta = $es_cancelada ? 'cita-cancelada' : ($es_realizada ? 'cita-realizada' : '');
                        $clase_etiqueta = $es_cancelada ? 'estado-cancelada' : ($es_realizada ? 'estado-realizada' : 'estado-pendiente');
                    ?>

                    <!-- Si está cancelada, le agregamos una clase CSS extra para ponerla opaca y gris -->
                    <div class="cita-card <?= $clase_tarjeta ?>">
                        <div class="cita-hora">
                            <?= $hora_in_formato ?>
                            <span>hasta las <?= $hora_fin_formato ?></span>
                        </div>
                        <div class="cita-info">
                            <h3><?= htmlspecialchars($cita['nombre_clienta']) ?></h3>
                            <p>Servicio: <?= htmlspecialchars($cita['nombre_servicio']) ?></p>
                        </div>
                        
                        <div class="acciones-cita">
                            <span class="cita-estado <?= $clase_etiqueta ?>">
                                <?= htmlspecialchars($cita['estado']) ?>
                            </span>

                            <!-- ¡NUEVO!: Botón de cancelar. Solo se muestra si NO está cancelada ya -->
                            <?php if ($es_pendiente): ?>
                                <div class="grupo-botones">
                                    <form method="POST" action="citas_pendientes.php?fecha=<?= $fecha_filtro ?>" onsubmit="return confirm('¿Marcar esta cita como terminada?');">
                                        <input type="hidden" name="id_cita_realizada" value="<?= $cita['id_cita'] ?>">
                                        <button type="submit" class="btn-realizada">Completar cita</button>
                                    </form>

                                    <form method="POST" action="citas_pendientes.php?fecha=<?= $fecha_filtro ?>" onsubmit="return confirm('¿Estás segura de que deseas cancelar esta cita?');">
                                        <input type="hidden" name="id_cita_cancelar" value="<?= $cita['id_cita'] ?>">
                                        <button type="submit" class="btn-cancelar">Cancelar cita</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

    <footer>Sistema de gestión para estudios de uñas | NailData &copy; 2026</footer>

</body>
</html>