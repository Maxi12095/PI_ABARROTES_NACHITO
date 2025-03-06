<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del producto desde el parámetro GET
$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header("Location: inventory.php");
    exit;
}

// Consulta para eliminar el producto
$query = "DELETE FROM products WHERE ID_Product = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    $success_message = "Producto eliminado exitosamente.";
} else {
    $error_message = "Error al eliminar el producto: " . $stmt->error;
}

$stmt->close();
$conn->close();

// Redirigir al inventario con un mensaje de confirmación
header("Location: inventory.php?message=" . urlencode($success_message ?? $error_message));
exit;
