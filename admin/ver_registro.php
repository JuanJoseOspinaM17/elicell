<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/helpers.php";

require_admin();


// ============================================================
// ID DEL REGISTRO
// ============================================================

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id || $id <= 0) {
    redirect("registros.php");
}


// ============================================================
// BUSCAR REGISTRO
// ============================================================

try {

    $stmt = $pdo->prepare("
        SELECT
            g.*,
            e.nombre AS estado_nombre
        FROM garantias g
        INNER JOIN estados e
            ON e.id = g.estado_id
        WHERE g.id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    $registro = $stmt->fetch();


} catch (PDOException $e) {

    error_log(
        "elicell ver registro: " .
        $e->getMessage()
    );

    $registro = false;
}


// ============================================================
// SI NO EXISTE
// ============================================================

if (!$registro) {
    redirect("registros.php");
}


// ============================================================
// DATOS
// ============================================================

$numeroGarantia =
    $registro['numero_garantia'] ?? '';


$estado =
    $registro['estado_nombre'] ?? '';


$hoy = date('Y-m-d');

$vencimiento =
    $registro['fecha_vencimiento_garantia']
    ?? null;


$garantiaVigente =
    $vencimiento !== null
    &&
    $vencimiento >= $hoy;


// ============================================================
// SESIÓN
// ============================================================

start_session();

$adminNombre =
    $_SESSION['admin_nombre']
    ?? 'Administrador';

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Detalle del registro de reparación y garantía"
    >

    <title>
        elicell | <?= e($numeroGarantia) ?>
    </title>


    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >


    <style>

        /* =====================================================
           DETALLE DEL REGISTRO
        ===================================================== */

        .detail-page {

            max-width: 1200px;

            margin: 0 auto;

        }


        /* =====================================================
           HEADER
        ===================================================== */

        .detail-top {

            display: flex;

            align-items: flex-start;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 25px;

        }


        .detail-back {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            color: #666666;

            text-decoration: none;

            font-size: 12px;

            margin-bottom: 12px;

        }


        .detail-back:hover {

            color: #0b5ed7;

        }


        .detail-title {

            margin: 0;

            font-size: 29px;

        }


        .detail-subtitle {

            margin: 6px 0 0;

            color: #777777;

            font-size: 13px;

        }


        .detail-actions {

            display: flex;

            gap: 9px;

            flex-wrap: wrap;

        }


        .btn-secondary {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 40px;

            padding: 0 14px;

            border-radius: 8px;

            background: #eeeeee;

            color: #333333;

            text-decoration: none;

            border: none;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

        }


        .btn-secondary:hover {

            background: #dddddd;

        }


        .btn-primary {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 40px;

            padding: 0 14px;

            border-radius: 8px;

            background: #0b5ed7;

            color: #ffffff;

            text-decoration: none;

            border: none;

            font-size: 12px;

            font-weight: 700;

            cursor: pointer;

        }


        .btn-primary:hover {

            background: #084298;

        }


        /* =====================================================
           ESTADO
        ===================================================== */

        .detail-status-card {

            background: #ffffff;

            border: 1px solid #e1e1e1;

            border-radius: 13px;

            padding: 20px 22px;

            margin-bottom: 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

        }


        .detail-status-info {

            display: flex;

            flex-direction: column;

            gap: 5px;

        }


        .detail-status-info span {

            color: #888888;

            font-size: 11px;

            text-transform: uppercase;

            letter-spacing: .4px;

            font-weight: 700;

        }


        .detail-status-info strong {

            font-size: 20px;

        }


        .detail-number {

            text-align: right;

        }


        .detail-number span {

            display: block;

            color: #888888;

            font-size: 10px;

            margin-bottom: 4px;

        }


        .detail-number strong {

            color: #0b5ed7;

            font-size: 14px;

        }


        /* =====================================================
           GRID
        ===================================================== */

        .detail-grid {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 18px;

        }


        .detail-card {

            background: #ffffff;

            border: 1px solid #e1e1e1;

            border-radius: 13px;

            overflow: hidden;

        }


        .detail-card.full {

            grid-column: 1 / -1;

        }


        .detail-card-header {

            padding: 18px 21px;

            border-bottom: 1px solid #eeeeee;

            background: #fafafa;

        }


        .detail-card-header h2 {

            margin: 0;

            font-size: 16px;

        }


        .detail-card-header p {

            margin: 4px 0 0;

            color: #888888;

            font-size: 11px;

        }


        .detail-card-body {

            padding: 21px;

        }


        /* =====================================================
           CAMPOS
        ===================================================== */

        .info-grid {

            display: grid;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 18px 25px;

        }


        .info-item {

            min-width: 0;

        }


        .info-item.full {

            grid-column: 1 / -1;

        }


        .info-label {

            display: block;

            color: #888888;

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: .35px;

            font-weight: 700;

            margin-bottom: 5px;

        }


        .info-value {

            color: #161616;

            font-size: 13px;

            line-height: 1.5;

            word-break: break-word;

        }


        .info-value.empty {

            color: #aaaaaa;

        }


        .description-box {

            background: #f8f9fa;

            border: 1px solid #eeeeee;

            border-radius: 9px;

            padding: 13px;

            color: #333333;

            font-size: 13px;

            line-height: 1.6;

            white-space: pre-wrap;

        }


        /* =====================================================
           GARANTÍA
        ===================================================== */

        .warranty-box {

            border-radius: 11px;

            padding: 18px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

        }


        .warranty-box.valid {

            background: #eaf7ef;

            border: 1px solid #c7ead5;

        }


        .warranty-box.expired {

            background: #fff0f1;

            border: 1px solid #f2c2c5;

        }


        .warranty-main {

            display: flex;

            flex-direction: column;

            gap: 5px;

        }


        .warranty-main span {

            font-size: 11px;

            color: #777777;

        }


        .warranty-main strong {

            font-size: 20px;

        }


        .warranty-box.valid .warranty-main strong {

            color: #198754;

        }


        .warranty-box.expired .warranty-main strong {

            color: #b42318;

        }


        .warranty-date {

            text-align: right;

        }


        .warranty-date span {

            display: block;

            color: #777777;

            font-size: 10px;

            margin-bottom: 4px;

        }


        .warranty-date strong {

            font-size: 14px;

        }


        /* =====================================================
           COSTO
        ===================================================== */

        .cost-box {

            display: flex;

            align-items: center;

            justify-content: space-between;

            background: #111111;

            color: #ffffff;

            padding: 18px;

            border-radius: 10px;

        }


        .cost-box span {

            color: #999999;

            font-size: 11px;

        }


        .cost-box strong {

            font-size: 22px;

        }


        /* =====================================================
           ENTRADA / SALIDA
        ===================================================== */

        .time-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 14px;

        }


        .time-box {

            background: #f8f9fa;

            border: 1px solid #eeeeee;

            border-radius: 9px;

            padding: 15px;

        }


        .time-box span {

            display: block;

            color: #888888;

            font-size: 10px;

            text-transform: uppercase;

            font-weight: 700;

            margin-bottom: 6px;

        }


        .time-box strong {

            font-size: 14px;

        }


        .time-box small {

            display: block;

            color: #888888;

            margin-top: 3px;

            font-size: 11px;

        }


        /* =====================================================
           PIE
        ===================================================== */

        .detail-footer {

            margin-top: 20px;

            padding: 18px 0;

            color: #999999;

            font-size: 11px;

            text-align: center;

        }


        /* =====================================================
           RESPONSIVE
        ===================================================== */

        @media (max-width: 850px) {

            .detail-grid {

                grid-template-columns: 1fr;

            }


            .detail-card.full {

                grid-column: auto;

            }


            .detail-top {

                flex-direction: column;

            }


            .detail-actions {

                width: 100%;

            }


            .detail-actions a {

                flex: 1;

            }

        }


        @media (max-width: 600px) {

            .detail-title {

                font-size: 24px;

            }


            .info-grid {

                grid-template-columns: 1fr;

            }


            .info-item.full {

                grid-column: auto;

            }


            .time-grid {

                grid-template-columns: 1fr;

            }


            .detail-status-card {

                align-items: flex-start;

                flex-direction: column;

            }


            .detail-number {

                text-align: left;

            }


            .warranty-box {

                align-items: flex-start;

                flex-direction: column;

            }


            .warranty-date {

                text-align: left;

            }

        }


        /* =====================================================
           IMPRESIÓN
        ===================================================== */

        @media print {

            .sidebar,
            .detail-actions,
            .detail-back {

                display: none !important;

            }


            .admin-main {

                margin-left: 0 !important;

                padding: 0 !important;

            }


            .admin-body {

                background: #ffffff;

            }


            .detail-card,
            .detail-status-card {

                break-inside: avoid;

                box-shadow: none !important;

            }


            .detail-footer {

                display: none;

            }

        }

    </style>

</head>


<body class="admin-body">


<!-- ============================================================
     SIDEBAR
============================================================ -->

<aside class="sidebar">

    <div class="sidebar-brand">

        <div class="sidebar-logo">
            elicell
        </div>

        <div class="sidebar-subtitle">
            Administración
        </div>

    </div>


    <nav class="sidebar-nav">

        <a
            href="dashboard.php"
            class="nav-item"
        >
            <span class="nav-icon">
                ▦
            </span>

            Dashboard
        </a>


        <a
            href="registros.php"
            class="nav-item active"
        >
            <span class="nav-icon">
                ▤
            </span>

            Todos los registros
        </a>


        <a
            href="../index.php"
            class="nav-item"
        >
            <span class="nav-icon">
                ＋
            </span>

            Nuevo registro
        </a>

    </nav>


    <div class="sidebar-bottom">

        <div class="admin-user">

            <div class="admin-avatar">

                <?= e(
                    strtoupper(
                        substr(
                            $adminNombre,
                            0,
                            1
                        )
                    )
                ) ?>

            </div>


            <div class="admin-user-info">

                <strong>
                    <?= e($adminNombre) ?>
                </strong>

                <span>
                    Administrador
                </span>

            </div>

        </div>


        <a
            href="logout.php"
            class="logout-link"
        >
            Cerrar sesión
        </a>

    </div>

</aside>


<!-- ============================================================
     CONTENIDO
============================================================ -->

<main class="admin-main">

    <div class="detail-page">


        <!-- ====================================================
             HEADER
        ===================================================== -->

        <div class="detail-top">

            <div>

                <a
                    href="registros.php"
                    class="detail-back"
                >
                    ← Volver a todos los registros
                </a>


                <h1 class="detail-title">

                    Registro
                    <?= e($numeroGarantia) ?>

                </h1>


                <p class="detail-subtitle">

                    Detalle completo del servicio de reparación.

                </p>

            </div>


            <div class="detail-actions">

                <button
                    type="button"
                    class="btn-secondary"
                    onclick="window.print()"
                >
                    🖨 Imprimir
                </button>


                <a
                    href="editar_registro.php?id=<?= (int)$registro['id'] ?>"
                    class="btn-primary"
                >
                    ✎ Editar registro
                </a>

            </div>

        </div>


        <!-- ====================================================
             ESTADO
        ===================================================== -->

        <section class="detail-status-card">

            <div class="detail-status-info">

                <span>
                    Estado actual
                </span>


                <strong>

                    <span
                        class="status-badge
                        <?= e(
                            estado_class($estado)
                        ) ?>"
                    >

                        <?= e($estado) ?>

                    </span>

                </strong>

            </div>


            <div class="detail-number">

                <span>
                    Número de garantía
                </span>

                <strong>
                    <?= e($numeroGarantia) ?>
                </strong>

            </div>

        </section>


        <!-- ====================================================
             GRID PRINCIPAL
        ===================================================== -->

        <div class="detail-grid">


            <!-- =================================================
                 CLIENTE
            ================================================== -->

            <section class="detail-card">

                <div class="detail-card-header">

                    <h2>
                        Datos del cliente
                    </h2>

                    <p>
                        Información de contacto.
                    </p>

                </div>


                <div class="detail-card-body">

                    <div class="info-grid">


                        <div class="info-item">

                            <span class="info-label">
                                Nombre completo
                            </span>

                            <div class="info-value">

                                <?= e(
                                    $registro[
                                        'nombre_cliente'
                                    ]
                                ) ?>

                            </div>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Teléfono
                            </span>

                            <div class="info-value">

                                <?= e(
                                    $registro[
                                        'telefono_cliente'
                                    ]
                                ) ?>

                            </div>

                        </div>


                    </div>

                </div>

            </section>


            <!-- =================================================
                 EQUIPO
            ================================================== -->

            <section class="detail-card">

                <div class="detail-card-header">

                    <h2>
                        Datos del equipo
                    </h2>

                    <p>
                        Dispositivo recibido.
                    </p>

                </div>


                <div class="detail-card-body">

                    <div class="info-grid">


                        <div class="info-item">

                            <span class="info-label">
                                Tipo de dispositivo
                            </span>

                            <div class="info-value">

                                <?= e(
                                    $registro[
                                        'tipo_dispositivo'
                                    ]
                                ) ?>

                            </div>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Marca
                            </span>

                            <div class="info-value">

                                <?= e(
                                    $registro[
                                        'marca'
                                    ]
                                ) ?>

                            </div>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Modelo
                            </span>

                            <div class="info-value">

                                <?= e(
                                    $registro[
                                        'modelo'
                                    ]
                                ) ?>

                            </div>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                IMEI
                            </span>

                            <div
                                class="
                                    info-value
                                    <?= empty(
                                        $registro['imei']
                                    )
                                        ? 'empty'
                                        : ''
                                    ?>
                                "
                            >

                                <?= !empty(
                                    $registro['imei']
                                )
                                    ? e(
                                        $registro['imei']
                                    )
                                    : 'No registrado'
                                ?>

                            </div>

                        </div>


                    </div>

                </div>

            </section>


            <!-- =================================================
                 INGRESO
            ================================================== -->

            <section class="detail-card">

                <div class="detail-card-header">

                    <h2>
                        Información de ingreso
                    </h2>

                    <p>
                        Condiciones en las que fue recibido.
                    </p>

                </div>


                <div class="detail-card-body">


                    <div class="time-grid">


                        <div class="time-box">

                            <span>
                                Fecha de entrada
                            </span>

                            <strong>

                                <?= fecha_es(
                                    $registro[
                                        'fecha_entrada'
                                    ]
                                ) ?>

                            </strong>


                            <small>

                                Hora:

                                <?= e(
                                    $registro[
                                        'hora_entrada'
                                    ]
                                ) ?>

                            </small>

                        </div>


                        <div class="time-box">

                            <span>
                                Fecha de salida
                            </span>

                            <?php if (
                                !empty(
                                    $registro[
                                        'fecha_salida'
                                    ]
                                )
                            ): ?>

                                <strong>

                                    <?= fecha_es(
                                        $registro[
                                            'fecha_salida'
                                        ]
                                    ) ?>

                                </strong>


                                <small>

                                    Hora:

                                    <?= e(
                                        $registro[
                                            'hora_salida'
                                        ]
                                    ) ?>

                                </small>

                            <?php else: ?>

                                <strong
                                    style="
                                        color:#c76b00;
                                    "
                                >
                                    Pendiente
                                </strong>

                            <?php endif; ?>

                        </div>


                    </div>


                    <div
                        style="
                            height:18px;
                        "
                    ></div>


                    <div class="info-item">

                        <span class="info-label">
                            Falla reportada
                        </span>


                        <div class="description-box">

                            <?= e(
                                $registro[
                                    'falla'
                                ]
                            ) ?>

                        </div>

                    </div>


                    <?php if (
                        !empty(
                            $registro[
                                'estado_fisico'
                            ]
                        )
                    ): ?>

                        <div
                            style="
                                height:18px;
                            "
                        ></div>


                        <div class="info-item">

                            <span class="info-label">
                                Estado físico al ingresar
                            </span>


                            <div class="description-box">

                                <?= e(
                                    $registro[
                                        'estado_fisico'
                                    ]
                                ) ?>

                            </div>

                        </div>

                    <?php endif; ?>


                </div>

            </section>


            <!-- =================================================
                 REPARACIÓN
            ================================================== -->

            <section class="detail-card">

                <div class="detail-card-header">

                    <h2>
                        Reparación
                    </h2>

                    <p>
                        Trabajo realizado al equipo.
                    </p>

                </div>


                <div class="detail-card-body">


                    <?php if (
                        !empty(
                            $registro[
                                'reparacion_realizada'
                            ]
                        )
                    ): ?>

                        <div class="info-item">

                            <span class="info-label">
                                Trabajo realizado
                            </span>


                            <div class="description-box">

                                <?= e(
                                    $registro[
                                        'reparacion_realizada'
                                    ]
                                ) ?>

                            </div>

                        </div>

                    <?php else: ?>

                        <div class="info-item">

                            <span class="info-label">
                                Trabajo realizado
                            </span>


                            <div class="info-value empty">
                                No registrado.
                            </div>

                        </div>

                    <?php endif; ?>


                    <div
                        style="
                            height:18px;
                        "
                    ></div>


                    <div class="cost-box">

                        <div>

                            <span>
                                Costo total
                            </span>

                            <strong>
                                <?= money(
                                    $registro['costo']
                                ) ?>
                            </strong>

                        </div>

                        <div>
                            $
                        </div>

                    </div>


                </div>

            </section>


            <!-- =================================================
                 GARANTÍA
            ================================================== -->

            <section class="detail-card full">

                <div class="detail-card-header">

                    <h2>
                        Garantía
                    </h2>

                    <p>
                        Información de cobertura de la reparación.
                    </p>

                </div>


                <div class="detail-card-body">


                    <div
                        class="
                            warranty-box
                            <?= $garantiaVigente
                                ? 'valid'
                                : 'expired'
                            ?>
                        "
                    >


                        <div class="warranty-main">

                            <span>
                                Estado de la garantía
                            </span>


                            <strong>

                                <?= $garantiaVigente
                                    ? 'Garantía vigente'
                                    : 'Garantía vencida'
                                ?>

                            </strong>

                        </div>


                        <div class="warranty-date">

                            <span>
                                Fecha de vencimiento
                            </span>


                            <strong>

                                <?php if (
                                    $vencimiento
                                ): ?>

                                    <?= fecha_es(
                                        $vencimiento
                                    ) ?>

                                <?php else: ?>

                                    No registrada

                                <?php endif; ?>

                            </strong>

                        </div>


                    </div>


                    <div
                        style="
                            height:18px;
                        "
                    ></div>


                    <div class="info-grid">


                        <div class="info-item">

                            <span class="info-label">
                                Tiempo de garantía
                            </span>

                            <div class="info-value">

                                <?php

                                if (
                                    isset(
                                        $registro[
                                            'dias_garantia'
                                        ]
                                    )
                                    &&
                                    $registro[
                                        'dias_garantia'
                                    ] !== null
                                ) {

                                    echo e(
                                        $registro[
                                            'dias_garantia'
                                        ]
                                    )
                                    . ' días';

                                } else {

                                    echo 'No registrado';

                                }

                                ?>

                            </div>

                        </div>


                        <div class="info-item">

                            <span class="info-label">
                                Fecha de inicio
                            </span>

                            <div class="info-value">

                                <?php if (
                                    !empty(
                                        $registro[
                                            'fecha_inicio_garantia'
                                        ]
                                    )
                                ): ?>

                                    <?= fecha_es(
                                        $registro[
                                            'fecha_inicio_garantia'
                                        ]
                                    ) ?>

                                <?php else: ?>

                                    <?= fecha_es(
                                        $registro[
                                            'fecha_salida'
                                        ]
                                        ??
                                        $registro[
                                            'fecha_entrada'
                                        ]
                                    ) ?>

                                <?php endif; ?>

                            </div>

                        </div>


                    </div>


                </div>

            </section>


            <!-- =================================================
                 OBSERVACIONES
            ================================================== -->

            <?php if (
                !empty(
                    $registro[
                        'observaciones'
                    ]
                )
            ): ?>

                <section class="detail-card full">

                    <div class="detail-card-header">

                        <h2>
                            Observaciones
                        </h2>

                        <p>
                            Información adicional del servicio.
                        </p>

                    </div>


                    <div class="detail-card-body">

                        <div class="description-box">

                            <?= e(
                                $registro[
                                    'observaciones'
                                ]
                            ) ?>

                        </div>

                    </div>

                </section>

            <?php endif; ?>


        </div>


        <div class="detail-footer">

            elicell — Registro
            <?= e($numeroGarantia) ?>

        </div>


    </div>

</main>


</body>

</html>