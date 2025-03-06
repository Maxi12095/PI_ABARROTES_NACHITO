<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Obtener el ID de la categoría
$category_id = $_GET['id'] ?? null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST['category_name'];

    // Llamar al procedimiento almacenado UpdateCategory
    $query = "CALL UpdateCategory(?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $category_id, $category_name);

    if ($stmt->execute()) {
        $success_message = "Categoría actualizada exitosamente.";
    } else {
        $error_message = "Error al actualizar la categoría: " . $stmt->error;
    }

    $stmt->close();
}

// Obtener los datos de la categoría
$query = "SELECT * FROM categories WHERE ID_Category = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
$category = $result->fetch_assoc();

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Editar Categoría</h4>
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

                        <!-- Formulario para editar categoría -->
                        <form action="edit_category.php?id=<?php echo $category_id; ?>" method="post">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Nombre de la Categoría:</label>
                                <input type="text" id="category_name" name="category_name" class="form-control" value="<?php echo htmlspecialchars($category['Category_Name']); ?>" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                <a href="inventory.php" class="btn btn-secondary">Volver al Inventario</a>
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
