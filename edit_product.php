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

// Manejar la actualización del producto
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product = $_POST['product'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $qty = $_POST['qty'];
    $fk_category = $_POST['fk_category'];
    $sku = $_POST['sku']; // SKU recibido desde el formulario

    // Validación de datos (Asegurarse de que el precio y cantidad sean números válidos)
    if (!is_numeric($price)) {
        $error_message = "El precio debe ser un número válido.";
    } elseif (!is_numeric($qty)) {
        $error_message = "La cantidad debe ser un número válido.";
    } else {
        // Llamar al procedimiento almacenado para actualizar el producto
        $query = "CALL UpdateProduct(?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);

        // Bind parameters con los tipos correctos
        $stmt->bind_param("ssdiiis", $product, $description, $price, $qty, $fk_category, $sku, $product_id);

        if ($stmt->execute()) {
            $success_message = "Producto actualizado exitosamente.";
        } else {
            $error_message = "Error al actualizar el producto: " . $stmt->error;
        }

        $stmt->close();
    }
}

// Llamar al procedimiento almacenado para obtener los datos del producto
$query = "CALL GetProductById(?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

// Obtener las categorías para el dropdown
$category_query = "SELECT ID_Category, Category_Name FROM categories";
$categories = $conn->query($category_query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Editar Producto</h4>
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

                        <!-- Formulario para editar producto -->
                        <form action="edit_product.php?id=<?php echo $product_id; ?>" method="post">
                            <div class="row g-3">
                                <!-- Nombre del Producto -->
                                <div class="col-md-12">
                                    <label for="product" class="form-label">Nombre del Producto:</label>
                                    <input type="text" id="product" name="product" value="<?php echo htmlspecialchars($product['Product']); ?>" class="form-control" required>
                                </div>

                                <!-- Descripción -->
                                <div class="col-md-12">
                                    <label for="description" class="form-label">Descripción:</label>
                                    <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($product['Description']); ?>" class="form-control" required>
                                </div>

                                <!-- Precio -->
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Precio:</label>
                                    <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['Price']); ?>" class="form-control" required>
                                </div>

                                <!-- Cantidad -->
                                <div class="col-md-6">
                                    <label for="qty" class="form-label">Cantidad:</label>
                                    <input type="number" id="qty" name="qty" value="<?php echo htmlspecialchars($product['Qty']); ?>" class="form-control" required>
                                </div>

                                <!-- SKU -->
                                <div class="col-md-12">
                                    <label for="sku" class="form-label">SKU:</label>
                                    <input type="text" id="sku" name="sku" value="<?php echo htmlspecialchars($product['SKU']); ?>" class="form-control" required>
                                </div>

                                <!-- Categoría -->
                                <div class="col-md-12">
                                    <label for="fk_category" class="form-label">Categoría:</label>
                                    <select id="fk_category" name="fk_category" class="form-select" required>
                                        <?php while ($row = $categories->fetch_assoc()): ?>
                                            <option value="<?php echo $row['ID_Category']; ?>" <?php echo $row['ID_Category'] == $product['fk_category'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($row['Category_Name']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="mt-4 d-flex justify-content-between">
                                <button type="submit" class="btn btn-success">Guardar Cambios</button>
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

<?php
$conn->close();
?>
