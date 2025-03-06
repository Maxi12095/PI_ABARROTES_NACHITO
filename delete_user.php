<?php
session_start();
include 'config.php';

// Verificar si el usuario tiene rol de administrador
if (!isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del usuario a eliminar
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    header("Location: admin_dashboard.php");
    exit;
}

// Llamar al procedimiento almacenado para eliminar el usuario
$stmt = $conn->prepare("CALL DeleteUser(?)");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // Redirigir al dashboard con un mensaje de éxito
    $_SESSION['message'] = "Usuario eliminado exitosamente.";
    $_SESSION['message_type'] = "success";
} else {
    // Redirigir al dashboard con un mensaje de error
    $_SESSION['message'] = "Error al eliminar el usuario: " . $stmt->error;
    $_SESSION['message_type'] = "danger";
}

$stmt->close();
$conn->close();

header("Location: admin_dashboard.php");
exit;
?>
