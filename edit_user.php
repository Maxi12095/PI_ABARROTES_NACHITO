<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Verificar si el usuario tiene el rol de administrador
if ($_SESSION['role'] != 1) { // Suponiendo que el rol de administrador es 1
    header("Location: inventory.php");
    exit;
}

// Obtener el ID del usuario desde el parámetro GET
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: admin_dashboard.php");
    exit;
}

// Manejar la actualización del usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashear la contraseña
    $role_id = $_POST['role'];

    // Llamar al procedimiento almacenado para editar al usuario
    $query = "CALL EditUser(?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssi", $user_id, $first_name, $last_name, $username, $password, $role_id);

    if ($stmt->execute()) {
        $success_message = "Usuario actualizado exitosamente.";
    } else {
        $error_message = "Error al actualizar el usuario: " . $stmt->error;
    }

    $stmt->close();
}

// Obtener los datos del usuario
$query = "SELECT * FROM users WHERE ID_Users = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Editar Usuario</h4>
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
                        <form action="edit_user.php?id=<?php echo $user_id; ?>" method="post">
                            <div class="row g-3">
                                <!-- Nombre -->
                                <div class="col-md-6">
                                    <label for="first_name" class="form-label">Nombre:</label>
                                    <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['FirstName']); ?>" class="form-control" required>
                                </div>

                                <!-- Apellido -->
                                <div class="col-md-6">
                                    <label for="last_name" class="form-label">Apellido:</label>
                                    <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['LastName']); ?>" class="form-control" required>
                                </div>

                                <!-- Nombre de Usuario -->
                                <div class="col-md-12">
                                    <label for="username" class="form-label">Nombre de Usuario:</label>
                                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" class="form-control" required>
                                </div>

                                <!-- Contraseña -->
                                <div class="col-md-12">
                                    <label for="password" class="form-label">Contraseña:</label>
                                    <input type="password" id="password" name="password" class="form-control" required>
                                </div>

                                <!-- Selección de Rol -->
                                <div class="col-md-12">
                                    <label for="role" class="form-label">Rol:</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="1" <?php echo $user['FK_Role'] == 1 ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="2" <?php echo $user['FK_Role'] == 2 ? 'selected' : ''; ?>>Empleado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="mt-4 d-flex justify-content-between">
                                <button type="submit" class="btn btn-success">Guardar Cambios</button>
                                <a href="admin_dashboard.php" class="btn btn-secondary">Volver al Dashboard</a>
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
