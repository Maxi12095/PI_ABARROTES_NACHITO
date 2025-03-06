<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product = $_POST['product'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];
    $fk_category = $_POST['fk_category'];
    $sku = $_POST['sku'];  // SKU personalizado

    // Llamar al procedimiento almacenado AddProduct
    $query = "CALL AddProduct(?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssdiss", $product, $description, $price, $qty, $fk_category, $sku);

    if ($stmt->execute()) {
        $success_message = "Producto agregado exitosamente.";
    } else {
        $error_message = "Error al agregar el producto: " . $stmt->error;
    }

    $stmt->close();
}

// Obtener las categorías para el dropdown
$category_query = "SELECT ID_Category, Category_Name FROM categories";
$categories = $conn->query($category_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Añadir Producto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Añadir Nuevo Producto</h3>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de éxito o error -->
                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                            </div>
                        <?php elseif (isset($error_message)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario para añadir producto -->
                        <form action="add_product.php" method="post">
                            <div class="mb-3">
                                <label for="product" class="form-label">Nombre del Producto:</label>
                                <input type="text" id="product" name="product" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Descripción:</label>
                                <input type="text" id="description" name="description" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="price" class="form-label">Precio:</label>
                                <input type="number" step="0.01" id="price" name="price" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="qty" class="form-label">Cantidad:</label>
                                <input type="number" id="qty" name="qty" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="fk_category" class="form-label">Categoría:</label>
                                <select id="fk_category" name="fk_category" class="form-select" required>
                                    <option value="">Seleccione una categoría</option>
                                    <?php while ($row = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $row['ID_Category']; ?>">
                                            <?php echo htmlspecialchars($row['Category_Name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="sku" class="form-label">SKU (Código de barras):</label>
                                <input type="number" id="sku" name="sku" class="form-control" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">Añadir Producto</button>
                                <a href="inventory.php" class="btn btn-secondary">Volver al Inventario</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
