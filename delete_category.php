<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID de la categoría desde el parámetro GET
$category_id = $_GET['id'] ?? null;

if (!$category_id) {
    header("Location: inventory.php");
    exit;
}

// Consulta para eliminar la categoría
$query = "DELETE FROM categories WHERE ID_Category = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    $success_message = "Categoría eliminada exitosamente.";
} else {
    $error_message = "Error al eliminar la categoría: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirigir al inventario con un mensaje de confirmación
header("Location: inventory.php?message=" . urlencode($success_message ?? $error_message));
exit;
