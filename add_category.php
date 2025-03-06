<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Manejar el formulario de añadir categoría
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category_name = $_POST['category_name'];

    // Llamar al procedimiento almacenado AddCategory
    $query = "CALL AddCategory(?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $category_name);

    if ($stmt->execute()) {
        $success_message = "Categoría agregada exitosamente.";
    } else {
        $error_message = "Error al agregar la categoría: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Categoría</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Añadir Nueva Categoría</h4>
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

                        <!-- Formulario para añadir categoría -->
                        <form action="add_category.php" method="post">
                            <div class="mb-3">
                                <label for="category_name" class="form-label">Nombre de la Categoría:</label>
                                <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Escribe el nombre de la categoría" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Añadir Categoría</button>
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
