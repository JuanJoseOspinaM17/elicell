<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/config/helpers.php";

$estados = [];

try {

    $stmt = $pdo->query("
        SELECT id, nombre
        FROM estados
        WHERE activo = 1
        ORDER BY id ASC
    ");

    $estados = $stmt->fetchAll();

} catch (PDOException $e) {

    $estados = [];
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
        content="Sistema de registro de reparaciones y garantías de elicell"
    >

    <title>elicell | Registro de garantía</title>

    <link
        rel="stylesheet"
        href="assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/formulario.css"
    >

</head>

<body>

    <!-- =====================================================
         ENCABEZADO
    ====================================================== -->

    <header class="topbar">

        <div class="topbar-container">

            <div class="brand">

                <?php if (file_exists(__DIR__ . "/assets/img/logo.png")): ?>

                    <img
                        src="assets/img/logo.png"
                        alt="elicell"
                        class="brand-logo"
                    >

                <?php else: ?>

                    <span class="brand-text">
                        elicell
                    </span>

                <?php endif; ?>

            </div>

            <a
                href="admin/login.php"
                class="admin-link"
            >
                Panel administrativo
            </a>

        </div>

    </header>


    <!-- =====================================================
         CONTENIDO PRINCIPAL
    ====================================================== -->

    <main class="main-container">

        <section class="form-header">

            <span class="form-badge">
                REGISTRO DE SERVICIO
            </span>

            <h1>
                Registro de reparación y garantía
            </h1>

            <p>
                Registra la información del cliente, equipo,
                reparación y garantía.
            </p>

        </section>


        <!-- =================================================
             FORMULARIO
        ================================================== -->

        <form
            action="php/guardar_garantia.php"
            method="POST"
            id="garantiaForm"
            class="garantia-form"
        >

            <!-- =============================================
                 DATOS DEL CLIENTE
            ============================================== -->

            <section class="form-section">

                <div class="section-title">

                    <span class="section-number">
                        01
                    </span>

                    <div>

                        <h2>
                            Datos del cliente
                        </h2>

                        <p>
                            Información de la persona que entrega el equipo.
                        </p>

                    </div>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label for="nombre_cliente">
                            Nombre completo
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="nombre_cliente"
                            name="nombre_cliente"
                            placeholder="Ej: Juan Pérez"
                            maxlength="150"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="telefono_cliente">
                            Número de teléfono
                            <span>*</span>
                        </label>

                        <input
                            type="tel"
                            id="telefono_cliente"
                            name="telefono_cliente"
                            placeholder="Ej: 3001234567"
                            maxlength="30"
                            required
                        >

                    </div>

                </div>

            </section>


            <!-- =============================================
                 DATOS DEL EQUIPO
            ============================================== -->

            <section class="form-section">

                <div class="section-title">

                    <span class="section-number">
                        02
                    </span>

                    <div>

                        <h2>
                            Datos del equipo
                        </h2>

                        <p>
                            Información del teléfono o dispositivo recibido.
                        </p>

                    </div>

                </div>


                <div class="form-grid form-grid-3">

                    <div class="form-group">

                        <label for="tipo_dispositivo">
                            Tipo de dispositivo
                            <span>*</span>
                        </label>

                        <select
                            id="tipo_dispositivo"
                            name="tipo_dispositivo"
                            required
                        >

                            <option value="">
                                Seleccionar
                            </option>

                            <option value="Celular">
                                Celular
                            </option>

                            <option value="Tablet">
                                Tablet
                            </option>

                            <option value="Smartwatch">
                                Smartwatch
                            </option>

                            <option value="Otro">
                                Otro
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <label for="marca">
                            Marca
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="marca"
                            name="marca"
                            placeholder="Ej: Apple"
                            maxlength="100"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="modelo">
                            Modelo
                            <span>*</span>
                        </label>

                        <input
                            type="text"
                            id="modelo"
                            name="modelo"
                            placeholder="Ej: iPhone 13"
                            maxlength="100"
                            required
                        >

                    </div>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label for="imei">
                            IMEI
                            <small>
                                Opcional
                            </small>
                        </label>

                        <input
                            type="text"
                            id="imei"
                            name="imei"
                            placeholder="Número IMEI"
                            maxlength="50"
                        >

                    </div>

                </div>

            </section>


            <!-- =============================================
                 INFORMACIÓN DE INGRESO
            ============================================== -->

            <section class="form-section">

                <div class="section-title">

                    <span class="section-number">
                        03
                    </span>

                    <div>

                        <h2>
                            Información de ingreso
                        </h2>

                        <p>
                            Registra cómo llega el equipo al establecimiento.
                        </p>

                    </div>

                </div>


                <div class="form-group">

                    <label for="falla">
                        Falla que presenta
                        <span>*</span>
                    </label>

                    <textarea
                        id="falla"
                        name="falla"
                        rows="4"
                        placeholder="Describe detalladamente la falla que presenta el equipo..."
                        required
                    ></textarea>

                </div>


                <div class="form-group">

                    <label for="estado_fisico_entrada">
                        Estado físico al ingresar
                    </label>

                    <textarea
                        id="estado_fisico_entrada"
                        name="estado_fisico_entrada"
                        rows="3"
                        placeholder="Ej: Pantalla con rayones, tapa posterior en buen estado..."
                    ></textarea>

                </div>


                <div class="form-group">

                    <label for="observaciones_entrada">
                        Observaciones de entrada
                    </label>

                    <textarea
                        id="observaciones_entrada"
                        name="observaciones_entrada"
                        rows="3"
                        placeholder="Información adicional sobre el equipo al momento de recibirlo..."
                    ></textarea>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label for="fecha_entrada">
                            Fecha de entrada
                            <span>*</span>
                        </label>

                        <input
                            type="date"
                            id="fecha_entrada"
                            name="fecha_entrada"
                            value="<?= date('Y-m-d') ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="hora_entrada">
                            Hora de entrada
                            <span>*</span>
                        </label>

                        <input
                            type="time"
                            id="hora_entrada"
                            name="hora_entrada"
                            value="<?= date('H:i') ?>"
                            required
                        >

                    </div>

                </div>

            </section>


            <!-- =============================================
                 INFORMACIÓN DE LA REPARACIÓN
            ============================================== -->

            <section class="form-section">

                <div class="section-title">

                    <span class="section-number">
                        04
                    </span>

                    <div>

                        <h2>
                            Información de la reparación
                        </h2>

                        <p>
                            Registra el trabajo realizado y su costo.
                        </p>

                    </div>

                </div>


                <div class="form-group">

                    <label for="trabajo_realizado">
                        ¿Qué se le hizo al equipo?
                    </label>

                    <textarea
                        id="trabajo_realizado"
                        name="trabajo_realizado"
                        rows="4"
                        placeholder="Describe el trabajo realizado..."
                    ></textarea>

                </div>


                <div class="form-group">

                    <label for="repuestos">
                        Repuestos utilizados
                    </label>

                    <textarea
                        id="repuestos"
                        name="repuestos"
                        rows="3"
                        placeholder="Ej: Puerto de carga, pantalla, batería..."
                    ></textarea>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label for="costo">
                            Costo de reparación
                            <span>*</span>
                        </label>

                        <div class="input-money">

                            <span>
                                $
                            </span>

                            <input
                                type="number"
                                id="costo"
                                name="costo"
                                min="0"
                                step="100"
                                placeholder="0"
                                required
                            >

                        </div>

                    </div>

                </div>

            </section>


            <!-- =============================================
                 GARANTÍA
            ============================================== -->

            <section class="form-section">

                <div class="section-title">

                    <span class="section-number">
                        05
                    </span>

                    <div>

                        <h2>
                            Garantía
                        </h2>

                        <p>
                            Define el tiempo de garantía de la reparación.
                        </p>

                    </div>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label for="tiempo_garantia">
                            Tiempo de garantía
                            <span>*</span>
                        </label>

                        <input
                            type="number"
                            id="tiempo_garantia"
                            name="tiempo_garantia"
                            min="0"
                            value="0"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="unidad_garantia">
                            Unidad
                            <span>*</span>
                        </label>

                        <select
                            id="unidad_garantia"
                            name="unidad_garantia"
                            required
                        >

                            <option value="dias">
                                Días
                            </option>

                            <option value="meses">
                                Meses
                            </option>

                            <option value="anos">
                                Años
                            </option>

                        </select>

                    </div>

                </div>

                <div class="info-box">

                    <strong>
                        Importante
                    </strong>

                    <p>
                        La fecha de vencimiento de la garantía
                        será calculada automáticamente por el sistema.
                    </p>

                </div>

            </section>


            <!-- =============================================
                 ENTREGA
            ============================================== -->

            <section class="form-section">

                <div class="section-title">

                    <span class="section-number">
                        06
                    </span>

                    <div>

                        <h2>
                            Entrega y estado
                        </h2>

                        <p>
                            Información relacionada con la salida del equipo.
                        </p>

                    </div>

                </div>


                <div class="form-grid">

                    <div class="form-group">

                        <label for="fecha_salida">
                            Fecha de salida
                        </label>

                        <input
                            type="date"
                            id="fecha_salida"
                            name="fecha_salida"
                        >

                    </div>


                    <div class="form-group">

                        <label for="hora_salida">
                            Hora de salida
                        </label>

                        <input
                            type="time"
                            id="hora_salida"
                            name="hora_salida"
                        >

                    </div>

                </div>


                <div class="form-group">

                    <label for="estado_id">
                        Estado del servicio
                        <span>*</span>
                    </label>

                    <select
                        id="estado_id"
                        name="estado_id"
                        required
                    >

                        <?php foreach ($estados as $estado): ?>

                            <option
                                value="<?= e($estado['id']) ?>"
                                <?= strtolower($estado['nombre']) === 'recibido' ? 'selected' : '' ?>
                            >

                                <?= e($estado['nombre']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label for="observaciones_salida">
                        Observaciones de salida
                    </label>

                    <textarea
                        id="observaciones_salida"
                        name="observaciones_salida"
                        rows="3"
                        placeholder="Observaciones al momento de entregar el equipo..."
                    ></textarea>

                </div>

            </section>


            <!-- =============================================
                 BOTONES
            ============================================== -->

            <section class="form-actions">

                <div class="required-message">

                    <span>*</span>

                    Campos obligatorios

                </div>


                <div class="action-buttons">

                    <button
                        type="reset"
                        class="btn btn-secondary"
                    >
                        Limpiar formulario
                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Guardar garantía
                    </button>

                </div>

            </section>

        </form>

    </main>


    <!-- =====================================================
         FOOTER
    ====================================================== -->

    <footer class="footer">

        <p>
            © <?= date('Y') ?> elicell —
            Sistema de gestión de reparaciones y garantías.
        </p>

    </footer>


    <script src="assets/js/script.js"></script>

</body>

</html>