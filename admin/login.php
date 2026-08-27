<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../config/helpers.php";

start_session();


// ============================================================
// SI YA ESTÁ LOGUEADO
// ============================================================

if (is_admin_logged()) {
    redirect("dashboard.php");
}


// ============================================================
// VARIABLES
// ============================================================

$error = "";


// ============================================================
// PROCESAR LOGIN
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $usuario = trim($_POST['usuario'] ?? '');

    $password = $_POST['password'] ?? '';


    // --------------------------------------------------------
    // VALIDAR CAMPOS
    // --------------------------------------------------------

    if ($usuario === '' || $password === '') {

        $error =
            "Debes ingresar usuario y contraseña.";

    } else {

        try {

            $stmt = $pdo->prepare("
                SELECT
                    id,
                    nombre,
                    usuario,
                    password,
                    activo
                FROM admins
                WHERE usuario = ?
                LIMIT 1
            ");

            $stmt->execute([
                $usuario
            ]);

            $admin = $stmt->fetch();


            // ------------------------------------------------
            // COMPROBAR USUARIO
            // ------------------------------------------------

            if (!$admin) {

                $error =
                    "El usuario o la contraseña son incorrectos.";

            }


            // ------------------------------------------------
            // COMPROBAR SI ESTÁ ACTIVO
            // ------------------------------------------------

            elseif ((int)$admin['activo'] !== 1) {

                $error =
                    "Este usuario se encuentra desactivado.";

            }


            // ------------------------------------------------
            // COMPROBAR CONTRASEÑA
            // ------------------------------------------------

            elseif (
                !password_verify(
                    $password,
                    $admin['password']
                )
            ) {

                $error =
                    "El usuario o la contraseña son incorrectos.";

            }


            // ------------------------------------------------
            // LOGIN CORRECTO
            // ------------------------------------------------

            else {

                session_regenerate_id(true);

                $_SESSION['admin_id'] =
                    (int)$admin['id'];

                $_SESSION['admin_nombre'] =
                    $admin['nombre'];

                $_SESSION['admin_usuario'] =
                    $admin['usuario'];

                redirect("dashboard.php");
            }


        } catch (PDOException $e) {

            error_log(
                "elicell login: " .
                $e->getMessage()
            );

            $error =
                "No fue posible procesar el inicio de sesión.";
        }
    }
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
        elicell | Iniciar sesión
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }


        body {
            min-height: 100vh;

            margin: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background: #f4f6f9;

            font-family:
                Arial,
                Helvetica,
                sans-serif;
        }


        .login-wrapper {

            width: min(430px, 92%);

        }


        .login-card {

            background: #ffffff;

            border:
                1px solid #dddddd;

            border-radius: 18px;

            padding: 38px;

            box-shadow:
                0 15px 45px
                rgba(0, 0, 0, .08);

        }


        .brand {

            text-align: center;

            margin-bottom: 30px;

        }


        .brand-name {

            font-size: 34px;

            font-weight: 800;

            color: #0b5ed7;

            letter-spacing: -1px;

        }


        .brand-subtitle {

            margin-top: 5px;

            color: #777777;

            font-size: 14px;

        }


        .login-title {

            text-align: center;

            margin-bottom: 25px;

        }


        .login-title h1 {

            font-size: 22px;

            margin: 0 0 6px;

        }


        .login-title p {

            margin: 0;

            color: #777777;

            font-size: 13px;

        }


        .form-group {

            margin-bottom: 18px;

        }


        .form-group label {

            display: block;

            margin-bottom: 7px;

            font-size: 14px;

            font-weight: 700;

        }


        .form-group input {

            width: 100%;

            padding: 13px 14px;

            border:
                1px solid #d7d7d7;

            border-radius: 9px;

            font-size: 14px;

            outline: none;

        }


        .form-group input:focus {

            border-color: #0b5ed7;

            box-shadow:
                0 0 0 3px
                rgba(11, 94, 215, .10);

        }


        .error {

            background: #fff0f1;

            color: #b42318;

            border:
                1px solid #f2c2c5;

            border-radius: 9px;

            padding: 12px 14px;

            margin-bottom: 18px;

            font-size: 13px;

        }


        .btn-login {

            width: 100%;

            border: none;

            border-radius: 9px;

            padding: 14px;

            background: #0b5ed7;

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            cursor: pointer;

            transition: .2s;

        }


        .btn-login:hover {

            background: #084298;

        }


        .back-link {

            display: block;

            text-align: center;

            margin-top: 20px;

            color: #777777;

            text-decoration: none;

            font-size: 13px;

        }


        .back-link:hover {

            color: #0b5ed7;

        }


        .login-footer {

            text-align: center;

            margin-top: 18px;

            color: #999999;

            font-size: 12px;

        }


        @media (max-width: 500px) {

            .login-card {

                padding: 28px 22px;

            }

        }

    </style>

</head>

<body>

    <main class="login-wrapper">

        <div class="login-card">

            <div class="brand">

                <div class="brand-name">
                    elicell
                </div>

                <div class="brand-subtitle">
                    Sistema de reparaciones y garantías
                </div>

            </div>


            <div class="login-title">

                <h1>
                    Panel administrativo
                </h1>

                <p>
                    Inicia sesión para continuar
                </p>

            </div>


            <?php if ($error !== ''): ?>

                <div class="error">
                    <?= e($error) ?>
                </div>

            <?php endif; ?>


            <form
                method="POST"
                action=""
                autocomplete="off"
            >

                <div class="form-group">

                    <label for="usuario">
                        Usuario
                    </label>

                    <input
                        type="text"
                        id="usuario"
                        name="usuario"
                        placeholder="Ingresa tu usuario"
                        autocomplete="username"
                        required
                        value="<?= e($_POST['usuario'] ?? '') ?>"
                    >

                </div>


                <div class="form-group">

                    <label for="password">
                        Contraseña
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Ingresa tu contraseña"
                        autocomplete="current-password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="btn-login"
                >
                    Iniciar sesión
                </button>

            </form>


            <a
                href="../index.php"
                class="back-link"
            >
                ← Volver al registro
            </a>

        </div>


        <div class="login-footer">

            © <?= date('Y') ?> elicell

        </div>

    </main>

</body>

</html>