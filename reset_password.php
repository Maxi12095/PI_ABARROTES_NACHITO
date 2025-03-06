<?php
session_start();
include 'config.php';

// Verificar que el usuario esté en sesión
if (!isset($_SESSION['reset_user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error_message = "Las contraseñas no coinciden.";
    } else {
        // Encriptar la nueva contraseña
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Actualizar la contraseña en la base de datos usando el procedimiento almacenado
        $username = $_SESSION['reset_user'];
        $stmt = $conn->prepare("CALL UpdatePasswordByUsername(?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {
            $success_message = "Tu contraseña ha sido restablecida correctamente. Ahora puedes iniciar sesión.";
            unset($_SESSION['reset_user']); // Limpiar la sesión
        } else {
            $error_message = "Ocurrió un error al actualizar la contraseña.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Restablecer Contraseña</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de éxito o error -->
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success text-center">
                                <?php echo $success_message; ?>
                            </div>
                            <div class="d-grid">
                                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
                            </div>
                        <?php elseif (isset($error_message)): ?>
                            <div class="alert alert-danger text-center">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario para restablecer contraseña -->
                        <?php if (!isset($success_message)): ?>
                            <form action="reset_password.php" method="post">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nueva Contraseña:</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" placeholder="Ingresa tu nueva contraseña" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmar Contraseña:</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirma tu nueva contraseña" required>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">Restablecer Contraseña</button>
                                    <a href="login.php" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
