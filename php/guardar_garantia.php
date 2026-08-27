<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/helpers.php";


// ============================================================
// SOLO PERMITIR PETICIONES POST
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    redirect("../index.php");

}


// ============================================================
// FUNCIÓN PARA OBTENER DATOS
// ============================================================

function post_value($name)
{

    return isset($_POST[$name])
        ? trim($_POST[$name])
        : '';

}


// ============================================================
// RECIBIR DATOS
// ============================================================

$nombre_cliente =
    post_value('nombre_cliente');

$telefono_cliente =
    post_value('telefono_cliente');

$nombre_garante =
    post_value('nombre_garante');

$tipo_dispositivo =
    post_value('tipo_dispositivo');

$marca =
    post_value('marca');

$modelo =
    post_value('modelo');

$imei =
    post_value('imei');

$falla =
    post_value('falla');

$estado_fisico_entrada =
    post_value('estado_fisico_entrada');

$observaciones_entrada =
    post_value('observaciones_entrada');

$trabajo_realizado =
    post_value('trabajo_realizado');

$repuestos =
    post_value('repuestos');

$costo =
    post_value('costo');

$tiempo_garantia =
    post_value('tiempo_garantia');

$unidad_garantia =
    post_value('unidad_garantia');

$fecha_entrada =
    post_value('fecha_entrada');

$hora_entrada =
    post_value('hora_entrada');

$fecha_salida =
    post_value('fecha_salida');

$hora_salida =
    post_value('hora_salida');

$estado_id =
    post_value('estado_id');

$observaciones_salida =
    post_value('observaciones_salida');


// ============================================================
// VALIDACIONES
// ============================================================

$errores = [];


// ------------------------------------------------------------
// CLIENTE
// ------------------------------------------------------------

if ($nombre_cliente === '') {

    $errores[] =
        "El nombre del cliente es obligatorio.";

}


if ($telefono_cliente === '') {

    $errores[] =
        "El teléfono del cliente es obligatorio.";

}


// ------------------------------------------------------------
// GARANTE
// ------------------------------------------------------------

if ($nombre_garante === '') {

    $errores[] =
        "El nombre de la persona responsable de la garantía es obligatorio.";

}


// ------------------------------------------------------------
// EQUIPO
// ------------------------------------------------------------

if ($tipo_dispositivo === '') {

    $errores[] =
        "Debes seleccionar el tipo de dispositivo.";

}


if ($marca === '') {

    $errores[] =
        "La marca del equipo es obligatoria.";

}


if ($modelo === '') {

    $errores[] =
        "El modelo del equipo es obligatorio.";

}


// ------------------------------------------------------------
// FALLA
// ------------------------------------------------------------

if ($falla === '') {

    $errores[] =
        "Debes indicar la falla del equipo.";

}


// ------------------------------------------------------------
// COSTO
// ------------------------------------------------------------

if (
    $costo === ''
    ||
    !is_numeric($costo)
) {

    $errores[] =
        "El costo de la reparación no es válido.";

} elseif ((float)$costo < 0) {

    $errores[] =
        "El costo no puede ser negativo.";

}


// ------------------------------------------------------------
// GARANTÍA
// ------------------------------------------------------------

if (
    $tiempo_garantia === ''
    ||
    !is_numeric($tiempo_garantia)
) {

    $errores[] =
        "El tiempo de garantía no es válido.";

} elseif ((int)$tiempo_garantia < 0) {

    $errores[] =
        "El tiempo de garantía no puede ser negativo.";

}


$unidades_validas = [

    'dias',
    'meses',
    'anos'

];


if (
    !in_array(
        $unidad_garantia,
        $unidades_validas,
        true
    )
) {

    $errores[] =
        "La unidad de garantía seleccionada no es válida.";

}


// ------------------------------------------------------------
// FECHA
// ------------------------------------------------------------

if ($fecha_entrada === '') {

    $errores[] =
        "La fecha de entrada es obligatoria.";

}


// ------------------------------------------------------------
// HORA
// ------------------------------------------------------------

if ($hora_entrada === '') {

    $errores[] =
        "La hora de entrada es obligatoria.";

}


// ------------------------------------------------------------
// ESTADO
// ------------------------------------------------------------

if (
    $estado_id === ''
    ||
    !ctype_digit((string)$estado_id)
) {

    $errores[] =
        "El estado seleccionado no es válido.";

}


// ============================================================
// MOSTRAR ERRORES
// ============================================================

if (!empty($errores)) {

    $mensaje =
        implode("\n", $errores);

    echo "<script>";

    echo "alert("
        . json_encode($mensaje)
        . ");";

    echo "window.history.back();";

    echo "</script>";

    exit;

}


// ============================================================
// VALIDAR ESTADO
// ============================================================

$stmtEstado = $pdo->prepare("

    SELECT id

    FROM estados

    WHERE id = ?

    AND activo = 1

    LIMIT 1

");


$stmtEstado->execute([

    $estado_id

]);


$estadoExiste =
    $stmtEstado->fetch();


if (!$estadoExiste) {

    die(
        "El estado seleccionado no existe."
    );

}


// ============================================================
// VALIDAR FECHA
// ============================================================

$fechaObjeto =
    DateTime::createFromFormat(
        'Y-m-d',
        $fecha_entrada
    );


if (
    !$fechaObjeto
    ||
    $fechaObjeto->format('Y-m-d')
        !==
    $fecha_entrada
) {

    die(
        "La fecha de entrada no es válida."
    );

}


// ============================================================
// CALCULAR VENCIMIENTO
// ============================================================

$fecha_vencimiento_garantia =
    calcular_vencimiento_garantia(
        $fecha_entrada,
        $tiempo_garantia,
        $unidad_garantia
    );


// ============================================================
// LIMPIAR COSTO
// ============================================================

$costo =
    number_format(
        (float)$costo,
        2,
        '.',
        ''
    );


// ============================================================
// GENERAR NÚMERO DE GARANTÍA
// ============================================================

$numero_garantia =
    generar_numero_garantia($pdo);


// ============================================================
// ADMINISTRADOR
// ============================================================

start_session();

$creado_por = null;


if (
    isset($_SESSION['admin_id'])
) {

    $creado_por =
        (int)$_SESSION['admin_id'];

}


// ============================================================
// GUARDAR
// ============================================================

try {

    $sql = "

        INSERT INTO garantias (

            numero_garantia,

            nombre_cliente,
            telefono_cliente,
            nombre_garante,

            tipo_dispositivo,
            marca,
            modelo,
            imei,

            falla,
            estado_fisico_entrada,
            observaciones_entrada,

            trabajo_realizado,
            repuestos,
            costo,

            tiempo_garantia,
            unidad_garantia,
            fecha_vencimiento_garantia,

            fecha_entrada,
            hora_entrada,

            fecha_salida,
            hora_salida,

            estado_id,

            observaciones_salida,

            creado_por

        )

        VALUES (

            :numero_garantia,

            :nombre_cliente,
            :telefono_cliente,
            :nombre_garante,

            :tipo_dispositivo,
            :marca,
            :modelo,
            :imei,

            :falla,
            :estado_fisico_entrada,
            :observaciones_entrada,

            :trabajo_realizado,
            :repuestos,
            :costo,

            :tiempo_garantia,
            :unidad_garantia,
            :fecha_vencimiento_garantia,

            :fecha_entrada,
            :hora_entrada,

            :fecha_salida,
            :hora_salida,

            :estado_id,

            :observaciones_salida,

            :creado_por

        )

    ";


    $stmt =
        $pdo->prepare($sql);


    $stmt->execute([

        ':numero_garantia' =>
            $numero_garantia,

        ':nombre_cliente' =>
            $nombre_cliente,

        ':telefono_cliente' =>
            $telefono_cliente,

        ':nombre_garante' =>
            $nombre_garante,

        ':tipo_dispositivo' =>
            $tipo_dispositivo,

        ':marca' =>
            $marca,

        ':modelo' =>
            $modelo,

        ':imei' =>
            $imei !== ''
                ? $imei
                : null,

        ':falla' =>
            $falla,

        ':estado_fisico_entrada' =>
            $estado_fisico_entrada !== ''
                ? $estado_fisico_entrada
                : null,

        ':observaciones_entrada' =>
            $observaciones_entrada !== ''
                ? $observaciones_entrada
                : null,

        ':trabajo_realizado' =>
            $trabajo_realizado !== ''
                ? $trabajo_realizado
                : null,

        ':repuestos' =>
            $repuestos !== ''
                ? $repuestos
                : null,

        ':costo' =>
            $costo,

        ':tiempo_garantia' =>
            (int)$tiempo_garantia,

        ':unidad_garantia' =>
            $unidad_garantia,

        ':fecha_vencimiento_garantia' =>
            $fecha_vencimiento_garantia,

        ':fecha_entrada' =>
            $fecha_entrada,

        ':hora_entrada' =>
            $hora_entrada,

        ':fecha_salida' =>
            $fecha_salida !== ''
                ? $fecha_salida
                : null,

        ':hora_salida' =>
            $hora_salida !== ''
                ? $hora_salida
                : null,

        ':estado_id' =>
            (int)$estado_id,

        ':observaciones_salida' =>
            $observaciones_salida !== ''
                ? $observaciones_salida
                : null,

        ':creado_por' =>
            $creado_por

    ]);


    // ========================================================
    // ÉXITO
    // ========================================================

    $id =
        $pdo->lastInsertId();

    ?>

    <!DOCTYPE html>

    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            Registro guardado | elicell
        </title>

        <link
            rel="stylesheet"
            href="../assets/css/style.css"
        >

        <style>

            .success-container {

                width: min(600px, 92%);

                margin: 100px auto;

                background: #ffffff;

                border-radius: 18px;

                padding: 45px 35px;

                text-align: center;

                box-shadow:
                    0 10px 40px
                    rgba(0, 0, 0, .08);

                border:
                    1px solid #dddddd;

            }

            .success-icon {

                width: 70px;

                height: 70px;

                margin:
                    0 auto 20px;

                border-radius: 50%;

                background: #198754;

                color: #ffffff;

                display: flex;

                align-items: center;

                justify-content: center;

                font-size: 34px;

                font-weight: bold;

            }

            .success-container h1 {

                margin-bottom: 10px;

                font-size: 28px;

            }

            .success-container p {

                color: #777777;

                margin-bottom: 25px;

            }

            .garantia-numero {

                display: inline-block;

                background: #eaf3ff;

                color: #0b5ed7;

                padding: 14px 22px;

                border-radius: 10px;

                font-size: 22px;

                font-weight: 800;

                margin-bottom: 30px;

            }

            .success-buttons {

                display: flex;

                justify-content: center;

                gap: 12px;

                flex-wrap: wrap;

            }

            .success-buttons a {

                text-decoration: none;

                padding: 13px 20px;

                border-radius: 9px;

                font-weight: 700;

                font-size: 14px;

            }

            .btn-primary {

                background: #0b5ed7;

                color: #ffffff;

            }

            .btn-secondary {

                background: #eeeeee;

                color: #222222;

            }

        </style>

    </head>

    <body>

        <main>

            <div class="success-container">

                <div class="success-icon">
                    ✓
                </div>

                <h1>
                    Registro guardado
                </h1>

                <p>
                    La reparación y garantía fueron
                    registradas correctamente.
                </p>

                <div class="garantia-numero">

                    <?= e($numero_garantia) ?>

                </div>

                <div class="success-buttons">

                    <a
                        href="../index.php"
                        class="btn-primary"
                    >
                        Nuevo registro
                    </a>

                    <a
                        href="../admin/registros.php"
                        class="btn-secondary"
                    >
                        Ver registros
                    </a>

                </div>

            </div>

        </main>

    </body>

    </html>

    <?php


} catch (PDOException $e) {

    error_log(
        "elicell - Error al guardar garantía: "
        . $e->getMessage()
    );

    ?>

    <!DOCTYPE html>

    <html lang="es">

    <head>

        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1.0"
        >

        <title>
            Error | elicell
        </title>

        <link
            rel="stylesheet"
            href="../assets/css/style.css"
        >

    </head>

    <body>

        <main>

            <div class="error-container">

                <h1>
                    No se pudo guardar
                </h1>

                <p>
                    Ocurrió un problema al guardar el registro.
                    Verifica la conexión con la base de datos
                    e inténtalo nuevamente.
                </p>

                <a
                    href="../index.php"
                    class="error-button"
                >
                    Volver al formulario
                </a>

            </div>

        </main>

    </body>

    </html>

    <?php

}