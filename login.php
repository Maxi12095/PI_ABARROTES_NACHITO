<?php
session_start();
include 'config.php';  // Incluye la configuración de la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Llamar al procedimiento almacenado UserLogin
    $stmt = $conn->prepare("CALL UserLogin(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Verificar la contraseña hasheada
        if (password_verify($password, $row['Password'])) {
            // Credenciales correctas
            $_SESSION['user_id'] = $row['ID_Users'];
            $_SESSION['username'] = $row['Username'];
            $_SESSION['role'] = $row['FK_Role'];  // Rol del usuario

            // Redirección basada en el rol
            if ($_SESSION['role'] == 1) {  // Administrador
                header("Location: admin_dashboard.php");
            } else if ($_SESSION['role'] == 2) {  // Empleado
                header("Location: inventory.php");
            }
            exit;
        } else {
            $error_message = "Contraseña incorrecta.";
        }
    } else {
        $error_message = "Usuario no encontrado.";
    }

    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styleback.css"> <!-- Estilos personalizados -->
</head>
<body>
    <div class="container-fluid bg-primary text-white py-3 text-center">
        <h1>Bienvenido</h1>
        <p>Inicia sesión para continuar</p>
    </div>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <h4>Iniciar Sesión</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de error -->
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger text-center">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario de inicio de sesión -->
                        <form action="login.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Nombre de Usuario:</label>
                                <input type="text" id="username" name="username" class="form-control" placeholder="Escribe tu usuario" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Contraseña:</label>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Escribe tu contraseña" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">Iniciar Sesión</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p class="mb-0">¿Olvidaste tu contraseña? <a href="recover_password_user.php" class="text-primary">Recupérala aquí</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
