<?php
include 'config.php'; // Incluye la configuración de conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recoger datos del formulario
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Verificar si el nombre de usuario ya existe
    $checkUserStmt = $conn->prepare("SELECT COUNT(*) AS user_count FROM users WHERE Username = ?");
    $checkUserStmt->bind_param("s", $username);
    $checkUserStmt->execute();
    $result = $checkUserStmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['user_count'] > 0) {
        echo "El nombre de usuario ya está registrado. Por favor, elige otro.";
    } else {
        // Hashear la contraseña antes de guardarla en la base de datos
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Preparar la sentencia SQL para insertar el nuevo usuario
        $stmt = $conn->prepare("INSERT INTO users (FirstName, LastName, Username, Password, FK_Role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $firstname, $lastname, $username, $hashed_password, $role);

        if ($stmt->execute()) {
            echo "Usuario registrado exitosamente!";
        } else {
            echo "Error al registrar el usuario: " . $stmt->error;
        }

        $stmt->close();
    }

    $checkUserStmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/styleback.css"> <!-- Asegúrate de que este archivo CSS esté presente -->
</head>
<body>
    <div class="container-centered">
        <h2>Registrar Usuario</h2>
        <form action="register.php" method="post">
            <div class="form-group">
                <label for="firstname">Nombre:</label>
                <input type="text" id="firstname" name="firstname" required>
            </div>
            <div class="form-group">
                <label for="lastname">Apellido:</label>
                <input type="text" id="lastname" name="lastname" required>
            </div>
            <div class="form-group">
                <label for="username">Nombre de Usuario:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="role">Rol:</label>
                <select id="role" name="role" required>
                    <option value="1">Administrador</option>
                    <option value="2">Empleado</option>
                </select>
            </div>
            <button type="submit" class="btn-submit">Registrar</button>
        </form>
    </div>
</body>
</html>
