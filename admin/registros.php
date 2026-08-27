<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/helpers.php";

require_admin();


// ============================================================
// FILTROS
// ============================================================

$buscar = trim($_GET['buscar'] ?? '');
$estado = trim($_GET['estado'] ?? '');


// ============================================================
// CONSULTA PRINCIPAL
// ============================================================

$sql = "
    SELECT
        g.id,
        g.numero_garantia,
        g.nombre_cliente,
        g.telefono_cliente,
        g.tipo_dispositivo,
        g.marca,
        g.modelo,
        g.imei,
        g.costo,
        g.fecha_entrada,
        g.hora_entrada,
        g.fecha_salida,
        g.hora_salida,
        g.fecha_vencimiento_garantia,
        g.estado_id,
        e.nombre AS estado

    FROM garantias g

    LEFT JOIN estados e
        ON e.id = g.estado_id

    WHERE 1 = 1
";

$params = [];


// ============================================================
// BUSCADOR
// ============================================================

if ($buscar !== '') {

    /*
     * IMPORTANTE:
     * Cada marcador PDO tiene un nombre diferente.
     * Esto evita problemas cuando PDO usa consultas preparadas
     * reales en lugar de consultas emuladas.
     */

    $sql .= "
        AND (
            g.numero_garantia COLLATE utf8mb4_unicode_ci LIKE :buscar_garantia
            OR g.nombre_cliente COLLATE utf8mb4_unicode_ci LIKE :buscar_nombre
            OR g.telefono_cliente COLLATE utf8mb4_unicode_ci LIKE :buscar_telefono
            OR g.imei COLLATE utf8mb4_unicode_ci LIKE :buscar_imei
            OR g.marca COLLATE utf8mb4_unicode_ci LIKE :buscar_marca
            OR g.modelo COLLATE utf8mb4_unicode_ci LIKE :buscar_modelo
        )
    ";

    $valorBuscar = '%' . $buscar . '%';

    $params[':buscar_garantia'] = $valorBuscar;
    $params[':buscar_nombre'] = $valorBuscar;
    $params[':buscar_telefono'] = $valorBuscar;
    $params[':buscar_imei'] = $valorBuscar;
    $params[':buscar_marca'] = $valorBuscar;
    $params[':buscar_modelo'] = $valorBuscar;
}


// ============================================================
// FILTRO POR ESTADO
// ============================================================

if ($estado !== '') {

    if (ctype_digit($estado)) {

        $sql .= "
            AND g.estado_id = :estado
        ";

        $params[':estado'] = (int)$estado;

    }

}


// ============================================================
// ORDEN
// ============================================================

$sql .= "
    ORDER BY g.id DESC
";


// ============================================================
// VARIABLES
// ============================================================

$registros = [];

$errorBaseDatos = null;


// ============================================================
// EJECUTAR CONSULTA
// ============================================================

try {

    $stmt = $pdo->prepare($sql);

    $stmt->execute($params);

    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    /*
     * Antes el sistema ocultaba completamente el error
     * y simplemente mostraba "No encontramos registros".
     *
     * Ahora lo guardamos y lo mostramos en pantalla
     * para poder detectar cualquier problema real.
     */

    error_log(
        "elicell registros.php: " .
        $e->getMessage()
    );

    $errorBaseDatos = $e->getMessage();

    $registros = [];

}


// ============================================================
// OBTENER ESTADOS
// ============================================================

$estados = [];

try {

    $stmtEstados = $pdo->query("
        SELECT
            id,
            nombre
        FROM estados
        WHERE activo = 1
        ORDER BY id ASC
    ");

    $estados = $stmtEstados->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    error_log(
        "elicell estados: " .
        $e->getMessage()
    );

    $estados = [];

}


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
        content="Todos los registros de reparaciones de elicell"
    >

    <title>
        elicell | Todos los registros
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

        .records-page {

            max-width: 1500px;
            margin: 0 auto;

        }


        .admin-header {

            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;

        }


        .admin-breadcrumb {

            color: #888;
            font-size: 11px;
            margin-bottom: 7px;

        }


        .admin-header h1 {

            margin: 0;
            font-size: 28px;
            color: #111;

        }


        .admin-header p {

            margin: 6px 0 0;
            color: #777;
            font-size: 13px;

        }


        .new-record-btn {

            display: inline-flex;
            align-items: center;
            justify-content: center;

            height: 42px;
            padding: 0 17px;

            border-radius: 8px;

            background: #111;
            color: white;

            text-decoration: none;

            font-size: 12px;
            font-weight: 700;

            white-space: nowrap;

        }


        .new-record-btn:hover {

            background: #333;

        }


        .records-toolbar {

            background: #fff;

            border: 1px solid #e3e3e3;

            border-radius: 13px;

            padding: 20px;

            margin-bottom: 18px;

        }


        .filters-form {

            display: grid;

            grid-template-columns:
                minmax(250px, 1fr)
                220px
                auto
                auto;

            gap: 12px;

            align-items: end;

        }


        .filter-group {

            display: flex;

            flex-direction: column;

            gap: 6px;

        }


        .filter-group label {

            color: #666;

            font-size: 10px;

            font-weight: 800;

            text-transform: uppercase;

            letter-spacing: .4px;

        }


        .filter-group input,
        .filter-group select {

            width: 100%;

            height: 42px;

            padding: 0 12px;

            border: 1px solid #d8d8d8;

            border-radius: 8px;

            background: #fff;

            color: #222;

            font-size: 13px;

            outline: none;

            box-sizing: border-box;

        }


        .filter-group input:focus,
        .filter-group select:focus {

            border-color: #111;

        }


        .btn-filter {

            height: 42px;

            padding: 0 18px;

            border: 0;

            border-radius: 8px;

            background: #111;

            color: #fff;

            font-size: 12px;

            font-weight: 800;

            cursor: pointer;

        }


        .btn-filter:hover {

            background: #333;

        }


        .btn-clear {

            height: 42px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            padding: 0 16px;

            border-radius: 8px;

            background: #eee;

            color: #333;

            text-decoration: none;

            font-size: 12px;

            font-weight: 700;

            box-sizing: border-box;

        }


        .btn-clear:hover {

            background: #ddd;

        }


        .records-summary {

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            margin-bottom: 12px;

        }


        .records-summary strong {

            color: #111;
            font-size: 15px;

        }


        .records-summary span {

            color: #888;
            font-size: 12px;

        }


        .records-table-card {

            background: #fff;

            border: 1px solid #e1e1e1;

            border-radius: 13px;

            overflow: hidden;

            box-shadow:
                0 3px 12px rgba(0,0,0,.03);

        }


        .records-table-container {

            width: 100%;
            overflow-x: auto;

        }


        .records-table {

            width: 100%;

            min-width: 1100px;

            border-collapse: collapse;

        }


        .records-table thead {

            background: #f7f7f7;

        }


        .records-table th {

            padding: 13px 15px;

            color: #777;

            font-size: 10px;

            text-transform: uppercase;

            letter-spacing: .4px;

            text-align: left;

            white-space: nowrap;

        }


        .records-table td {

            padding: 14px 15px;

            border-top: 1px solid #eee;

            font-size: 12px;

            vertical-align: middle;

        }


        .records-table tbody tr {

            transition: .15s;

        }


        .records-table tbody tr:hover {

            background: #fafafa;

        }


        .record-number {

            color: #111;

            font-weight: 800;

            font-size: 11px;

        }


        .record-client {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .record-client strong {

            color: #111;
            font-size: 12px;

        }


        .record-client span {

            color: #888;
            font-size: 10px;

        }


        .record-device {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .record-device strong {

            color: #111;
            font-size: 12px;

        }


        .record-device span {

            color: #888;
            font-size: 10px;

        }


        .record-date {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .record-date strong {

            font-size: 11px;

        }


        .record-date span {

            color: #888;
            font-size: 10px;

        }


        .record-cost {

            font-weight: 800;
            white-space: nowrap;

        }


        .record-warranty {

            display: flex;

            flex-direction: column;

            gap: 3px;

        }


        .record-warranty strong {

            font-size: 11px;

        }


        .record-warranty span {

            font-size: 10px;
            color: #777;

        }


        .warranty-valid {

            color: #198754;

        }


        .warranty-expired {

            color: #dc3545;

        }


        .record-action {

            display: inline-flex;

            align-items: center;
            justify-content: center;

            padding: 8px 12px;

            border-radius: 7px;

            background: #111;

            color: #fff;

            text-decoration: none;

            font-size: 11px;

            font-weight: 800;

            white-space: nowrap;

        }


        .record-action:hover {

            background: #333;

        }


        .records-empty {

            text-align: center;

            padding: 75px 25px;

        }


        .records-empty-icon {

            width: 65px;

            height: 65px;

            margin: 0 auto 16px;

            border-radius: 50%;

            background: #f1f1f1;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 25px;

        }


        .records-empty h3 {

            margin: 0 0 7px;

            font-size: 18px;

            color: #111;

        }


        .records-empty p {

            margin: 0 0 22px;

            color: #888;

            font-size: 13px;

        }


        .database-warning {

            margin-bottom: 15px;

            padding: 14px 16px;

            border-radius: 9px;

            background: #fff3cd;

            border: 1px solid #ffe69c;

            color: #664d03;

            font-size: 12px;

        }


        .database-warning strong {

            display: block;

            margin-bottom: 6px;

        }


        .database-warning code {

            display: block;

            margin-top: 8px;

            padding: 9px;

            background: rgba(0,0,0,.05);

            border-radius: 6px;

            overflow-x: auto;

            font-size: 11px;

        }


        @media (max-width: 900px) {

            .filters-form {

                grid-template-columns: 1fr 1fr;

            }

        }


        @media (max-width: 700px) {

            .admin-header {

                align-items: flex-start;
                flex-direction: column;

            }


            .new-record-btn {

                width: 100%;

            }


            .filters-form {

                grid-template-columns: 1fr;

            }


            .records-summary {

                align-items: flex-start;
                flex-direction: column;

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

    <div class="records-page">


        <!-- HEADER -->

        <header class="admin-header">

            <div>

                <div class="admin-breadcrumb">
                    elicell / Registros
                </div>

                <h1>
                    Todos los registros
                </h1>

                <p>
                    Consulta todas las reparaciones
                    registradas en el sistema.
                </p>

            </div>


            <a
                href="../index.php"
                class="new-record-btn"
            >
                + Nuevo registro
            </a>

        </header>


        <!-- ERROR DE BASE DE DATOS -->

        <?php if ($errorBaseDatos !== null): ?>

            <div class="database-warning">

                <strong>
                    ⚠️ Se produjo un error en la consulta.
                </strong>

                El sistema no pudo realizar la búsqueda
                correctamente.

                <code>
                    <?= e($errorBaseDatos) ?>
                </code>

            </div>

        <?php endif; ?>


        <!-- FILTROS -->

        <section class="records-toolbar">

            <form
                method="GET"
                action="registros.php"
                class="filters-form"
            >

                <div class="filter-group">

                    <label for="buscar">
                        Buscar registro
                    </label>

                    <input
                        type="search"
                        id="buscar"
                        name="buscar"
                        value="<?= e($buscar) ?>"
                        placeholder="Nombre, teléfono, garantía, IMEI, marca o modelo..."
                        autocomplete="off"
                    >

                </div>


                <div class="filter-group">

                    <label for="estado">
                        Estado
                    </label>

                    <select
                        id="estado"
                        name="estado"
                    >

                        <option value="">
                            Todos los estados
                        </option>


                        <?php foreach (
                            $estados as $estadoItem
                        ): ?>

                            <option
                                value="<?= (int)$estadoItem['id'] ?>"
                                <?= (
                                    $estado !== ''
                                    &&
                                    (int)$estado ===
                                    (int)$estadoItem['id']
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >

                                <?= e(
                                    $estadoItem['nombre']
                                ) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <button
                    type="submit"
                    class="btn-filter"
                >
                    Buscar
                </button>


                <a
                    href="registros.php"
                    class="btn-clear"
                >
                    Limpiar
                </a>

            </form>

        </section>


        <!-- RESUMEN -->

        <div class="records-summary">

            <div>

                <strong>

                    <?= number_format(
                        count($registros)
                    ) ?>

                    registro<?= count($registros) == 1 ? '' : 's' ?>

                </strong>


                <?php if ($buscar !== ''): ?>

                    <span>

                        — resultados para:

                        "<?= e($buscar) ?>"

                    </span>

                <?php endif; ?>

            </div>


            <?php if ($estado !== ''): ?>

                <span>
                    Filtro de estado activo
                </span>

            <?php endif; ?>

        </div>


        <!-- TABLA -->

        <section class="records-table-card">


            <?php if (!empty($registros)): ?>


                <div class="records-table-container">

                    <table class="records-table">

                        <thead>

                            <tr>

                                <th>
                                    Garantía
                                </th>

                                <th>
                                    Cliente
                                </th>

                                <th>
                                    Equipo
                                </th>

                                <th>
                                    Entrada
                                </th>

                                <th>
                                    Salida
                                </th>

                                <th>
                                    Costo
                                </th>

                                <th>
                                    Garantía vence
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Acción
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach (
                                $registros as $registro
                            ): ?>


                                <?php

                                $hoy = date('Y-m-d');

                                $vencimiento =
                                    $registro[
                                        'fecha_vencimiento_garantia'
                                    ]
                                    ?? null;

                                $garantiaVigente =
                                    !empty($vencimiento)
                                    &&
                                    $vencimiento >= $hoy;

                                ?>


                                <tr>


                                    <!-- GARANTÍA -->

                                    <td>

                                        <span
                                            class="record-number"
                                        >

                                            <?= e(
                                                $registro[
                                                    'numero_garantia'
                                                ]
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- CLIENTE -->

                                    <td>

                                        <div
                                            class="record-client"
                                        >

                                            <strong>

                                                <?= e(
                                                    $registro[
                                                        'nombre_cliente'
                                                    ]
                                                ) ?>

                                            </strong>


                                            <span>

                                                <?= e(
                                                    $registro[
                                                        'telefono_cliente'
                                                    ]
                                                ) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- EQUIPO -->

                                    <td>

                                        <div
                                            class="record-device"
                                        >

                                            <strong>

                                                <?= e(
                                                    $registro[
                                                        'marca'
                                                    ]
                                                ) ?>

                                            </strong>


                                            <span>

                                                <?= e(
                                                    $registro[
                                                        'modelo'
                                                    ]
                                                ) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- ENTRADA -->

                                    <td>

                                        <div
                                            class="record-date"
                                        >

                                            <strong>

                                                <?= !empty(
                                                    $registro[
                                                        'fecha_entrada'
                                                    ]
                                                )
                                                    ? fecha_es(
                                                        $registro[
                                                            'fecha_entrada'
                                                        ]
                                                    )
                                                    : '—'
                                                ?>

                                            </strong>


                                            <span>

                                                <?= e(
                                                    $registro[
                                                        'hora_entrada'
                                                    ]
                                                    ?? ''
                                                ) ?>

                                            </span>

                                        </div>

                                    </td>


                                    <!-- SALIDA -->

                                    <td>

                                        <?php if (
                                            !empty(
                                                $registro[
                                                    'fecha_salida'
                                                ]
                                            )
                                        ): ?>

                                            <div
                                                class="record-date"
                                            >

                                                <strong>

                                                    <?= fecha_es(
                                                        $registro[
                                                            'fecha_salida'
                                                        ]
                                                    ) ?>

                                                </strong>


                                                <span>

                                                    <?= e(
                                                        $registro[
                                                            'hora_salida'
                                                        ]
                                                        ?? ''
                                                    ) ?>

                                                </span>

                                            </div>

                                        <?php else: ?>

                                            <span
                                                style="
                                                    color:#c76b00;
                                                    font-weight:700;
                                                "
                                            >
                                                Pendiente
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- COSTO -->

                                    <td>

                                        <span
                                            class="record-cost"
                                        >

                                            <?= money(
                                                $registro[
                                                    'costo'
                                                ]
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- VENCIMIENTO -->

                                    <td>

                                        <?php if (
                                            !empty(
                                                $vencimiento
                                            )
                                        ): ?>

                                            <div
                                                class="record-warranty"
                                            >

                                                <strong
                                                    class="<?= $garantiaVigente
                                                        ? 'warranty-valid'
                                                        : 'warranty-expired'
                                                    ?>"
                                                >

                                                    <?= fecha_es(
                                                        $vencimiento
                                                    ) ?>

                                                </strong>


                                                <span>

                                                    <?= $garantiaVigente
                                                        ? 'Vigente'
                                                        : 'Vencida'
                                                    ?>

                                                </span>

                                            </div>

                                        <?php else: ?>

                                            <span
                                                style="color:#aaa;"
                                            >
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>


                                    <!-- ESTADO -->

                                    <td>

                                        <?php

                                        $nombreEstado =
                                            $registro[
                                                'estado'
                                            ]
                                            ??
                                            'Sin estado';

                                        ?>


                                        <span
                                            class="
                                                status-badge
                                                <?= e(
                                                    estado_class(
                                                        $nombreEstado
                                                    )
                                                )
                                                ?>
                                            "
                                        >

                                            <?= e(
                                                $nombreEstado
                                            ) ?>

                                        </span>

                                    </td>


                                    <!-- ACCIÓN -->

                                    <td>

                                        <a
                                            href="ver_registro.php?id=<?= (int)$registro['id'] ?>"
                                            class="record-action"
                                        >
                                            Ver registro
                                        </a>

                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="records-empty">

                    <div class="records-empty-icon">
                        ▤
                    </div>


                    <h3>
                        No encontramos registros
                    </h3>


                    <p>

                        <?php if (
                            $buscar !== ''
                            ||
                            $estado !== ''
                        ): ?>

                            No hay resultados que coincidan
                            con los filtros seleccionados.

                        <?php else: ?>

                            Todavía no hay registros
                            de reparaciones.

                        <?php endif; ?>

                    </p>


                    <?php if (
                        $buscar !== ''
                        ||
                        $estado !== ''
                    ): ?>

                        <a
                            href="registros.php"
                            class="btn-clear"
                        >
                            Limpiar filtros
                        </a>

                    <?php else: ?>

                        <a
                            href="../index.php"
                            class="new-record-btn"
                        >
                            Crear registro
                        </a>

                    <?php endif; ?>

                </div>


            <?php endif; ?>


        </section>


    </div>

</main>


</body>

</html>