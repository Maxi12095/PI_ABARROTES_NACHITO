<?php
session_start();
include 'config.php';

// Verificar si el usuario tiene rol de administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    // Hasheando la contraseña
    $role = $_POST['role'];

    // Llamar al procedimiento almacenado AddUser
    $stmt = $conn->prepare("CALL AddUser(?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $firstname, $lastname, $username, $password, $role);

    if ($stmt->execute()) {
        $success_message = "Usuario creado exitosamente.";
    } else {
        $error_message = "Error al crear el usuario: " . $stmt->error;
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
    <title>Agregar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Agregar Nuevo Usuario</h4>
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

                        <!-- Formulario -->
                        <form action="add_user.php" method="post">
                            <div class="row g-3">
                                <!-- Nombre -->
                                <div class="col-md-6">
                                    <label for="firstname" class="form-label">Nombre:</label>
                                    <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Escribe el nombre" required>
                                </div>

                                <!-- Apellido -->
                                <div class="col-md-6">
                                    <label for="lastname" class="form-label">Apellido:</label>
                                    <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Escribe el apellido" required>
                                </div>

                                <!-- Nombre de Usuario -->
                                <div class="col-md-12">
                                    <label for="username" class="form-label">Nombre de Usuario:</label>
                                    <input type="text" id="username" name="username" class="form-control" placeholder="Escribe el nombre de usuario" required>
                                </div>

                                <!-- Contraseña -->
                                <div class="col-md-12">
                                    <label for="password" class="form-label">Contraseña:</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Escribe una contraseña" required>
                                </div>

                                <!-- Selección de Rol -->
                                <div class="col-md-12">
                                    <label for="role" class="form-label">Rol:</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="" disabled selected>Selecciona un rol</option>
                                        <option value="1">Administrador</option>
                                        <option value="2">Empleado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="mt-4 d-flex justify-content-between">
                                <button type="submit" class="btn btn-success">Crear Usuario</button>
                                <a href="admin_dashboard.php" class="btn btn-secondary">Volver</a>
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
