<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $user_id = $_SESSION['user_id'];

    // Verificar la contraseña actual
    $stmt = $conn->prepare("CALL VerifyCurrentPassword(?)"); //proceso
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($current_password, $user['Password'])) {
        $error_message = "La contraseña actual es incorrecta.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "La nueva contraseña y la confirmación no coinciden.";
    } elseif (password_verify($new_password, $user['Password'])) {
        $error_message = "La nueva contraseña no puede ser igual a la contraseña actual.";
    } else {
        // Hashear la nueva contraseña
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        // Actualizar la contraseña
        $stmt = $conn->prepare("CALL UpdateUserPassword(?, ?)"); //proceso
        $stmt->bind_param("is", $user_id, $new_password_hash);

        if ($stmt->execute()) {
            $success_message = "Contraseña actualizada exitosamente.";
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
    <title>Cambiar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white text-center">
                        <h4>Cambiar Contraseña</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de éxito o error -->
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success text-center">
                                <?php echo $success_message; ?>
                            </div>
                        <?php elseif (isset($error_message)): ?>
                            <div class="alert alert-danger text-center">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario para cambiar contraseña -->
                        <form action="change_password.php" method="post">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Contraseña Actual:</label>
                                <input 
                                    type="password" 
                                    id="current_password" 
                                    name="current_password" 
                                    class="form-control" 
                                    placeholder="Ingresa tu contraseña actual" 
                                    required
                                >
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nueva Contraseña:</label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="new_password" 
                                    class="form-control" 
                                    placeholder="Ingresa tu nueva contraseña" 
                                    required
                                >
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Nueva Contraseña:</label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="confirm_password" 
                                    class="form-control" 
                                    placeholder="Confirma tu nueva contraseña" 
                                    required
                                >
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-warning">Cambiar Contraseña</button>
                                <a href="inventory.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
