<?php

/**
 * ============================================================
 * FUNCIONES GENERALES - elicell
 * ============================================================
 */


/**
 * Escapar texto para mostrarlo de forma segura en HTML.
 */
function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/**
 * Formatear dinero colombiano.
 */
function money($value)
{
    return '$' . number_format(
        (float) $value,
        0,
        ',',
        '.'
    );
}


/**
 * Redireccionar a una página.
 */
function redirect($url)
{
    header("Location: " . $url);
    exit;
}


/**
 * Iniciar sesión si todavía no existe.
 */
function start_session()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}


/**
 * Comprobar si existe una sesión administrativa.
 */
function is_admin_logged()
{
    start_session();

    return isset($_SESSION['admin_id']);
}


/**
 * Proteger páginas administrativas.
 */
function require_admin()
{
    if (!is_admin_logged()) {
        redirect("login.php");
    }
}


/**
 * Obtener el nombre del estado.
 */
function estado_class($estado)
{
    $estado = strtolower(trim($estado));

    switch ($estado) {

        case 'recibido':
            return 'estado-recibido';

        case 'en revisión':
        case 'en revision':
            return 'estado-revision';

        case 'en reparación':
        case 'en reparacion':
            return 'estado-reparacion';

        case 'reparado':
            return 'estado-reparado';

        case 'entregado':
            return 'estado-entregado';

        case 'no reparado':
        case 'no reparado':
            return 'estado-no-reparado';

        case 'cancelado':
            return 'estado-cancelado';

        default:
            return 'estado-default';
    }
}


/**
 * Calcular la fecha de vencimiento de la garantía.
 *
 * $cantidad = número de días, meses o años
 * $unidad   = dias | meses | anos
 */
function calcular_vencimiento_garantia(
    $fechaInicio,
    $cantidad,
    $unidad
) {

    $fecha = new DateTime($fechaInicio);

    $cantidad = (int) $cantidad;

    switch ($unidad) {

        case 'dias':

            $fecha->modify("+{$cantidad} days");

            break;

        case 'meses':

            $fecha->modify("+{$cantidad} months");

            break;

        case 'anos':

            $fecha->modify("+{$cantidad} years");

            break;
    }

    return $fecha->format('Y-m-d');
}


/**
 * Determinar si una garantía está vigente.
 */
function garantia_vigente($fechaVencimiento)
{
    if (empty($fechaVencimiento)) {
        return false;
    }

    $hoy = new DateTime();
    $vencimiento = new DateTime($fechaVencimiento);

    return $hoy <= $vencimiento;
}


/**
 * Formatear una fecha para mostrarla.
 */
function fecha_es($fecha)
{
    if (empty($fecha)) {
        return '-';
    }

    $date = new DateTime($fecha);

    return $date->format('d/m/Y');
}


/**
 * Formatear hora.
 */
function hora_es($hora)
{
    if (empty($hora)) {
        return '-';
    }

    return date('h:i A', strtotime($hora));
}


/**
 * Generar número de garantía.
 *
 * Ejemplo:
 * GAR-000001
 * GAR-000002
 * GAR-000003
 */
function generar_numero_garantia($pdo)
{
    $stmt = $pdo->query(
        "SELECT id FROM garantias ORDER BY id DESC LIMIT 1"
    );

    $ultimo = $stmt->fetch();

    if (!$ultimo) {
        $numero = 1;
    } else {
        $numero = ((int) $ultimo['id']) + 1;
    }

    return 'GAR-' . str_pad(
        $numero,
        6,
        '0',
        STR_PAD_LEFT
    );
}