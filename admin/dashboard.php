<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/helpers.php";

require_admin();

start_session();

$adminNombre = $_SESSION['admin_nombre'] ?? 'Administrador';


// ============================================================
// ESTADÍSTICAS GENERALES
// ============================================================

try {

    // Total de registros
    $stmt = $pdo->query("
        SELECT COUNT(*) AS total
        FROM garantias
    ");

    $totalRegistros = (int) $stmt->fetch()['total'];


    // Registros pendientes / recibidos
    $stmt = $pdo->query("
        SELECT COUNT(*) AS total
        FROM garantias g
        INNER JOIN estados e
            ON e.id = g.estado_id
        WHERE e.nombre IN ('Recibido', 'En revisión')
    ");

    $pendientes = (int) $stmt->fetch()['total'];


    // En reparación
    $stmt = $pdo->query("
        SELECT COUNT(*) AS total
        FROM garantias g
        INNER JOIN estados e
            ON e.id = g.estado_id
        WHERE e.nombre = 'En reparación'
    ");

    $enReparacion = (int) $stmt->fetch()['total'];


    // Entregados
    $stmt = $pdo->query("
        SELECT COUNT(*) AS total
        FROM garantias g
        INNER JOIN estados e
            ON e.id = g.estado_id
        WHERE e.nombre = 'Entregado'
    ");

    $entregados = (int) $stmt->fetch()['total'];


    // Reparados
    $stmt = $pdo->query("
        SELECT COUNT(*) AS total
        FROM garantias g
        INNER JOIN estados e
            ON e.id = g.estado_id
        WHERE e.nombre = 'Reparado'
    ");

    $reparados = (int) $stmt->fetch()['total'];


    // Total facturado
    $stmt = $pdo->query("
        SELECT COALESCE(SUM(costo), 0) AS total
        FROM garantias
        WHERE estado_id != (
            SELECT id
            FROM estados
            WHERE nombre = 'Cancelado'
            LIMIT 1
        )
    ");

    $totalIngresos = (float) $stmt->fetch()['total'];


    // Garantías vigentes
    $stmt = $pdo->query("
        SELECT COUNT(*) AS total
        FROM garantias
        WHERE fecha_vencimiento_garantia IS NOT NULL
        AND fecha_vencimiento_garantia >= CURDATE()
    ");

    $garantiasVigentes = (int) $stmt->fetch()['total'];


    // ========================================================
    // ÚLTIMOS REGISTROS
    // ========================================================

    $stmt = $pdo->query("
        SELECT
            g.id,
            g.numero_garantia,
            g.nombre_cliente,
            g.telefono_cliente,
            g.marca,
            g.modelo,
            g.costo,
            g.fecha_entrada,
            g.fecha_vencimiento_garantia,
            e.nombre AS estado

        FROM garantias g

        INNER JOIN estados e
            ON e.id = g.estado_id

        ORDER BY g.id DESC

        LIMIT 8
    ");

    $ultimosRegistros = $stmt->fetchAll();


} catch (PDOException $e) {

    error_log(
        "elicell dashboard: " .
        $e->getMessage()
    );

    $totalRegistros = 0;
    $pendientes = 0;
    $enReparacion = 0;
    $entregados = 0;
    $reparados = 0;
    $totalIngresos = 0;
    $garantiasVigentes = 0;
    $ultimosRegistros = [];

}

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
        content="Panel administrativo de elicell"
    >

    <title>
        elicell | Dashboard
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="../assets/css/admin.css"
    >

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
            class="nav-item active"
        >
            <span class="nav-icon">▦</span>
            Dashboard
        </a>


        <a
            href="registros.php"
            class="nav-item"
        >
            <span class="nav-icon">▤</span>
            Todos los registros
        </a>


        <a
            href="../index.php"
            class="nav-item"
        >
            <span class="nav-icon">＋</span>
            Nuevo registro
        </a>

    </nav>


    <div class="sidebar-bottom">

        <div class="admin-user">

            <div class="admin-avatar">
                <?= e(
                    strtoupper(
                        substr($adminNombre, 0, 1)
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
     CONTENIDO PRINCIPAL
============================================================ -->

<main class="admin-main">


    <!-- HEADER -->

    <header class="admin-header">

        <div>

            <div class="admin-breadcrumb">
                elicell / Dashboard
            </div>

            <h1>
                Panel de control
            </h1>

            <p>
                Resumen general de reparaciones y garantías.
            </p>

        </div>


        <a
            href="../index.php"
            class="new-record-btn"
        >
            + Nuevo registro
        </a>

    </header>


    <!-- ========================================================
         TARJETAS PRINCIPALES
    ========================================================= -->

    <section class="stats-grid">


        <!-- TOTAL -->

        <div class="stat-card">

            <div class="stat-icon blue">
                ▤
            </div>

            <div class="stat-content">

                <span>
                    Total de registros
                </span>

                <strong>
                    <?= number_format($totalRegistros) ?>
                </strong>

            </div>

        </div>


        <!-- PENDIENTES -->

        <div class="stat-card">

            <div class="stat-icon orange">
                ◷
            </div>

            <div class="stat-content">

                <span>
                    Pendientes
                </span>

                <strong>
                    <?= number_format($pendientes) ?>
                </strong>

            </div>

        </div>


        <!-- REPARACIÓN -->

        <div class="stat-card">

            <div class="stat-icon purple">
                ⚙
            </div>

            <div class="stat-content">

                <span>
                    En reparación
                </span>

                <strong>
                    <?= number_format($enReparacion) ?>
                </strong>

            </div>

        </div>


        <!-- REPARADOS -->

        <div class="stat-card">

            <div class="stat-icon green">
                ✓
            </div>

            <div class="stat-content">

                <span>
                    Reparados
                </span>

                <strong>
                    <?= number_format($reparados) ?>
                </strong>

            </div>

        </div>


        <!-- ENTREGADOS -->

        <div class="stat-card">

            <div class="stat-icon dark">
                ▣
            </div>

            <div class="stat-content">

                <span>
                    Entregados
                </span>

                <strong>
                    <?= number_format($entregados) ?>
                </strong>

            </div>

        </div>


        <!-- GARANTÍAS -->

        <div class="stat-card">

            <div class="stat-icon cyan">
                ♢
            </div>

            <div class="stat-content">

                <span>
                    Garantías vigentes
                </span>

                <strong>
                    <?= number_format($garantiasVigentes) ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- ========================================================
         INGRESOS
    ========================================================= -->

    <section class="income-card">

        <div>

            <span>
                Valor total registrado
            </span>

            <strong>
                <?= money($totalIngresos) ?>
            </strong>

            <p>
                Valor acumulado de las reparaciones registradas.
            </p>

        </div>

        <div class="income-icon">
            $
        </div>

    </section>


    <!-- ========================================================
         ÚLTIMOS REGISTROS
    ========================================================= -->

    <section class="table-card">

        <div class="table-card-header">

            <div>

                <h2>
                    Últimos registros
                </h2>

                <p>
                    Los servicios registrados recientemente.
                </p>

            </div>


            <a
                href="registros.php"
                class="view-all"
            >
                Ver todos →
            </a>

        </div>


        <?php if (!empty($ultimosRegistros)): ?>


            <div class="table-container">

                <table>

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
                                Fecha
                            </th>

                            <th>
                                Costo
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
                            $ultimosRegistros
                            as $registro
                        ): ?>

                            <tr>

                                <td>

                                    <strong class="garantia-code">
                                        <?= e(
                                            $registro[
                                                'numero_garantia'
                                            ]
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <div class="client-cell">

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


                                <td>

                                    <div class="device-cell">

                                        <strong>
                                            <?= e(
                                                $registro['marca']
                                            ) ?>
                                        </strong>

                                        <span>
                                            <?= e(
                                                $registro['modelo']
                                            ) ?>
                                        </span>

                                    </div>

                                </td>


                                <td>

                                    <?= fecha_es(
                                        $registro[
                                            'fecha_entrada'
                                        ]
                                    ) ?>

                                </td>


                                <td>

                                    <strong>
                                        <?= money(
                                            $registro['costo']
                                        ) ?>
                                    </strong>

                                </td>


                                <td>

                                    <span
                                        class="status-badge
                                        <?= e(
                                            estado_class(
                                                $registro['estado']
                                            )
                                        ) ?>"
                                    >

                                        <?= e(
                                            $registro['estado']
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <a
                                        href="ver_registro.php?id=<?= (int)$registro['id'] ?>"
                                        class="action-view"
                                    >
                                        Ver
                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="empty-state">

                <div class="empty-icon">
                    ▤
                </div>

                <h3>
                    No hay registros todavía
                </h3>

                <p>
                    Cuando registres una reparación,
                    aparecerá aquí.
                </p>

                <a
                    href="../index.php"
                    class="new-record-btn"
                >
                    Crear primer registro
                </a>

            </div>


        <?php endif; ?>

    </section>


</main>


</body>
</html>