<?php
require_once 'conexion.php';
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION["usuario_id"])) {
    header("Location: index.html");
    exit;
}

$nombre = $_SESSION["nombre"] ?? $_SESSION["usuario"];

$mensaje = "";
$tipo_mensaje = "";

try {
    // Obtener las clientas
    $sql_clientas = "SELECT id_usuario, nombre FROM clientas ORDER BY nombre ASC";
    $stmt_clientas = $conexion->prepare($sql_clientas);
    $stmt_clientas->execute();
    $clientas = $stmt_clientas->fetchAll(PDO::FETCH_ASSOC);

    // Obtener los servicios
    $sql_servicios = "SELECT id_servicio, nombre, duracion_min FROM servicio ORDER BY nombre ASC";
    $stmt_servicios = $conexion->prepare($sql_servicios);
    $stmt_servicios->execute();
    $servicios = $stmt_servicios->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $mensaje = "Error al cargar los datos base: " . $e->getMessage();
    $tipo_mensaje = "error";
}

// ---------------------------------------------------------
// PROCESAR EL FORMULARIO (Botón "Agendar y Guardar")
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $id_servicio = $_POST['id_servicio'];
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    try {
        // Verificar cruces de horarios
        $sql_cruce = "SELECT id_cita FROM citas 
                      WHERE fecha = :fecha 
                      AND hora_inicio < :hora_fin 
                      AND hora_fin > :hora_inicio
                      AND estado != 'cancelada'";
        
        $stmt_cruce = $conexion->prepare($sql_cruce);
        $stmt_cruce->bindParam(':fecha', $fecha);
        $stmt_cruce->bindParam(':hora_inicio', $hora_inicio);
        $stmt_cruce->bindParam(':hora_fin', $hora_fin);
        $stmt_cruce->execute();

        if ($stmt_cruce->rowCount() > 0) {
            $mensaje = "¡Ojo! El horario seleccionado se cruza con una cita que ya está programada.";
            $tipo_mensaje = "error";
        } else {
            // Guardar la cita
            $sql_insert = "INSERT INTO citas (id_usuario, id_servicio, fecha, hora_inicio, hora_fin, estado) 
                           VALUES (:id_usuario, :id_servicio, :fecha, :hora_inicio, :hora_fin, 'pendiente')";
            
            $stmt_insert = $conexion->prepare($sql_insert);
            $stmt_insert->bindParam(':id_usuario', $id_usuario);
            $stmt_insert->bindParam(':id_servicio', $id_servicio);
            $stmt_insert->bindParam(':fecha', $fecha);
            $stmt_insert->bindParam(':hora_inicio', $hora_inicio);
            $stmt_insert->bindParam(':hora_fin', $hora_fin);

            if ($stmt_insert->execute()) {
                $mensaje = "¡Cita agendada con éxito!";
                $tipo_mensaje = "exito";
            }
        }
    } catch (PDOException $e) {
        $mensaje = "Excepción al guardar la cita: " . $e->getMessage();
        $tipo_mensaje = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Cita - NailData</title>
    
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        @keyframes aparecerFlotando {
            0% { opacity: 0; transform: translateY(20px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes aparecerSuave {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        /* --- CABECERA --- */
        header {
            background: rgba(255, 255, 255, 0.4); 
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        header h1 { font-size: 22px; font-weight: 600; color: var(--color-primario-hover); }

        .cerrar-sesion {
            background: rgba(224, 174, 184, 0.8); 
            color: white;
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600; font-size: 12px;
            transition: all 0.3s;
        }

        .cerrar-sesion:hover { background: #c78b97; transform: scale(0.95); }

        /* --- CONTENIDO PRINCIPAL --- */
        main { max-width: 600px; margin: 40px auto; padding: 0 20px; flex-grow: 1; width: 100%; }

        .boton-volver {
            display: inline-block;
            background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(5px);
            color: var(--color-texto); padding: 10px 18px; text-decoration: none;
            border-radius: 10px; font-size: 13px; font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.6); margin-bottom: 25px; transition: all 0.3s ease;
        }

        .boton-volver:hover { background: var(--color-primario); color: var(--color-blanco); }

        /* --- FORMULARIO (Cristal) --- */
        .tarjeta-formulario {
            background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(12px);
            padding: 35px; border-radius: 16px; box-shadow: 0 10px 30px rgba(227, 168, 180, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.4); animation: aparecerFlotando 0.6s ease forwards;
        }

        .tarjeta-formulario h2 { margin-top: 0; color: var(--color-primario-hover); font-size: 22px; margin-bottom: 25px; text-align: center; }

        .form-group { margin-bottom: 20px; }
        .form-row { display: flex; gap: 15px; }
        .form-row .form-group { flex: 1; }

        label { display: block; margin-bottom: 8px; font-weight: 500; font-size: 13px; }

        /* Contenedor del checkbox */
        .opcion-auto {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: var(--color-texto-claro);
            margin-bottom: 10px;
        }

        select, input[type="date"], input[type="time"] {
            width: 100%; padding: 12px 15px; font-family: 'Poppins', sans-serif; font-size: 13px;
            color: var(--color-texto); background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(227, 168, 180, 0.4); border-radius: 10px; outline: none; transition: all 0.3s ease;
        }

        select:focus, input:focus { background: rgba(255, 255, 255, 0.9); border-color: var(--color-primario-hover); }
        
        /* Input de solo lectura (cuando está automático) */
        input[readonly] {
            background: rgba(230, 230, 230, 0.4);
            cursor: not-allowed;
            color: var(--color-texto-claro);
        }

        button[type="submit"] {
            width: 100%; padding: 14px; background-color: var(--color-primario); color: white;
            border: none; border-radius: 10px; font-size: 14px; font-weight: 600; cursor: pointer;
            transition: all 0.3s ease; font-family: 'Poppins', sans-serif; margin-top: 10px;
        }

        button[type="submit"]:hover { background-color: var(--color-primario-hover); transform: translateY(-2px); }

        /* --- ALERTAS --- */
        .alerta { padding: 15px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; text-align: center; animation: aparecerSuave 0.5s ease forwards; }
        .alerta-error { background-color: var(--bg-error); color: var(--color-error); border: 1px solid rgba(217, 83, 79, 0.2); }
        .alerta-exito { background-color: var(--bg-exito); color: var(--color-exito); border: 1px solid rgba(46, 125, 50, 0.2); }

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

        <div class="tarjeta-formulario">
            <h2>Agendar Cita</h2>

            <?php if ($mensaje): ?>
                <div class="alerta <?= $tipo_mensaje == 'error' ? 'alerta-error' : 'alerta-exito' ?>">
                    <?= $mensaje ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="agendar.php">
                <div class="form-group">
                    <label for="id_usuario">Seleccionar Clienta:</label>
                    <select id="id_usuario" name="id_usuario" required>
                        <option value="">-- Elige una clienta --</option>
                        <?php foreach ($clientas as $c): ?>
                            <option value="<?= $c['id_usuario'] ?>"><?= htmlspecialchars($c['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_servicio">Servicio a realizar:</label>
                    <select id="id_servicio" name="id_servicio" required>
                        <option value="">-- Elige un servicio --</option>
                        <?php foreach ($servicios as $s): ?>
                            <!-- ¡NUEVO!: Guardamos la duración oculta en data-duracion -->
                            <option value="<?= $s['id_servicio'] ?>" data-duracion="<?= $s['duracion_min'] ?>">
                                <?= htmlspecialchars($s['nombre']) ?> (<?= $s['duracion_min'] ?> min)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha de la cita:</label>
                    <input type="date" id="fecha" name="fecha" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="hora_inicio">Hora de inicio:</label>
                        <input type="time" id="hora_inicio" name="hora_inicio" required>
                    </div>
                    <div class="form-group">
                        <label for="hora_fin">Hora de fin:</label>
                        <!-- ¡NUEVO!: Checkbox para controlar si es manual o automático -->
                        <div class="opcion-auto">
                            <input type="checkbox" id="calculo_auto" checked>
                            <label for="calculo_auto" style="margin:0; cursor:pointer;">Calcular automático</label>
                        </div>
                        <input type="time" id="hora_fin" name="hora_fin" readonly required>
                    </div>
                </div>

                <button type="submit">Agendar y Guardar</button>
            </form>
        </div>
    </main>

    <footer>Sistema de gestión para estudios de uñas | NailData &copy; 2026</footer>

    <!-- ¡NUEVO!: Script para calcular la hora automáticamente -->
    <script>
        // Elementos del formulario
        const selectServicio = document.getElementById('id_servicio');
        const inputInicio = document.getElementById('hora_inicio');
        const inputFin = document.getElementById('hora_fin');
        const checkAuto = document.getElementById('calculo_auto');

        // Función que hace la matemática del tiempo
        function calcularHoraFin() {
            // Si la casilla no está marcada (es decir, el usuario quiere hacerlo manual), no hacemos nada
            if (!checkAuto.checked) return;

            // Revisamos que haya un servicio seleccionado y una hora de inicio ingresada
            if (selectServicio.value !== "" && inputInicio.value !== "") {
                // Obtener los minutos del servicio desde el atributo "data-duracion" que pusimos en el HTML
                const opcionSeleccionada = selectServicio.options[selectServicio.selectedIndex];
                const duracionMinutos = parseInt(opcionSeleccionada.getAttribute('data-duracion'));

                // Separar la hora y los minutos que escribió el usuario (Ej: "14:30" -> 14 y 30)
                const [horas, minutos] = inputInicio.value.split(':').map(Number);
                
                // Usamos la herramienta de fecha de JavaScript para sumar el tiempo fácil
                let fechaCalculo = new Date();
                fechaCalculo.setHours(horas);
                fechaCalculo.setMinutes(minutos + duracionMinutos); // Le sumamos la duración

                // Convertir el resultado de vuelta a texto con formato HH:MM (Ej: "16:00")
                const horasFin = String(fechaCalculo.getHours()).padStart(2, '0');
                const minutosFin = String(fechaCalculo.getMinutes()).padStart(2, '0');
                
                // Poner el resultado en el campo de "Hora de fin"
                inputFin.value = `${horasFin}:${minutosFin}`;
            } else {
                inputFin.value = ""; // Limpiar si borran la hora de inicio
            }
        }

        // ¿Qué pasa si el usuario hace clic en el checkbox?
        checkAuto.addEventListener('change', function() {
            if (this.checked) {
                inputFin.setAttribute('readonly', true); // Bloquea escritura manual
                calcularHoraFin(); // Calcula de inmediato
            } else {
                inputFin.removeAttribute('readonly'); // Permite escritura manual
            }
        });

        // Escuchar cuando el usuario cambia el servicio o la hora de inicio
        selectServicio.addEventListener('change', calcularHoraFin);
        inputInicio.addEventListener('change', calcularHoraFin);
    </script>
</body>
</html>