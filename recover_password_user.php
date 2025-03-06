<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener el nombre de usuario
    $username = $_POST['username'];

    // Llamar al procedimiento almacenado GetUserSecurityQuestions
    $stmt = $conn->prepare("CALL GetUserSecurityQuestions(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Guardar el nombre de usuario y las preguntas en la sesión
        $_SESSION['username'] = $username;
        $_SESSION['questions'] = $result->fetch_all(MYSQLI_ASSOC);
        header("Location: recover_password_questions.php"); // Redirigir a la página de preguntas
        exit;
    } else {
        $error_message = "No se encontraron preguntas de seguridad para este usuario.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Recuperar Contraseña</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de error -->
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger text-center">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario para ingresar nombre de usuario -->
                        <form action="recover_password_user.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nombre de Usuario:</label>
                                <input type="text" id="username" name="username" class="form-control" placeholder="Ingresa tu usuario" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Siguiente</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="login.php" class="text-secondary">Volver al Inicio de Sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
