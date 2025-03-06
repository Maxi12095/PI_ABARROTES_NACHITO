<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Inicializar las variables de búsqueda
$search_term = isset($_POST['search']) ? $_POST['search'] : '';
$search_sku = isset($_POST['search_sku']) ? $_POST['search_sku'] : '';
$category_filter = isset($_POST['category_filter']) ? $_POST['category_filter'] : '';

// Inicializar el array de productos
$products = [];

// Llamar al procedimiento almacenado para buscar productos por nombre
$query_name = "CALL SearchProductByName(?)";
$stmt_name = $conn->prepare($query_name);

// Si se pasa un término de búsqueda por nombre, se aplica el comodín.
$search_param_name = "%" . $search_term . "%"; 

// Bind the parameter para búsqueda por nombre
$stmt_name->bind_param("s", $search_param_name);
$stmt_name->execute();
$result_name = $stmt_name->get_result();

// Almacenar los resultados del procedimiento por nombre
while ($row = $result_name->fetch_assoc()) {
    $products[] = $row;
}

$stmt_name->close();
$conn->next_result(); // Para preparar la conexión para la siguiente consulta

// Si se pasa un SKU para búsqueda, se busca por SKU
if ($search_sku != '') {
    $query_sku = "CALL SearchProductBySku(?)";
    $stmt_sku = $conn->prepare($query_sku);

    $stmt_sku->bind_param("s", $search_sku); // SKU es un string
    $stmt_sku->execute();
    $result_sku = $stmt_sku->get_result();
    $products = []; // Limpiar el array de productos

    // Almacenar los resultados de la búsqueda por SKU
    while ($row = $result_sku->fetch_assoc()) {
        $products[] = $row;
    }

    $stmt_sku->close();
}

// Obtener las categorías para el dropdown de categorías
$category_query = "SELECT * FROM categories";
$category_result = $conn->query($category_query);

// Inicializar el array de categorías
$categories = [];
while ($category = $category_result->fetch_assoc()) {
    $categories[] = $category;
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styleback.css">
    <script>
        // Función para alternar la visibilidad de la Card de búsqueda
        function toggleSearchCard() {
            var searchCard = document.getElementById('searchCard');
            if (searchCard.style.display === "none" || searchCard.style.display === "") {
                searchCard.style.display = "block";
            } else {
                searchCard.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <!-- Botón para alternar Sidebar -->
    <button class="btn btn-dark toggle-sidebar-btn" onclick="toggleSidebar()">☰</button>

    <!-- Contenedor principal -->
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-2 d-md-block sidebar">
            <div class="position-sticky pt-3">
                <h2 class="text-center">Menú</h2>
                <ul class="nav flex-column">
                    <?php if ($_SESSION['role'] == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin_dashboard.php">Panel de Administrador</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="inventory.php">Inventario</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="configure_security_questions.php">Configurar Preguntas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="change_password.php">Cambiar Contraseña</a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Contenido principal -->
        <div id="main-content" class="main-content">
            <!-- Header -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Inventario</a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <span class="nav-link">Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></span>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-danger btn-sm text-white" href="logout.php">Cerrar Sesión</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Contenido -->
            <main class="container-fluid mt-4">
                <!-- Card Box para búsquedas (oculta por defecto) -->
                <div class="card mb-4" id="searchCard" style="display: none;">
                    <div class="card-body">
                        <h1 class="h2">Búsquedas</h1>
                        <br>
                        <!-- Formulario de búsqueda por nombre -->
                        <form class="row g-3" method="post" action="inventory.php">
                            <div class="col-md-8">
                                <input 
                                    type="text" 
                                    name="search" 
                                    class="form-control" 
                                    placeholder="Buscar producto por nombre" 
                                    value="<?php echo htmlspecialchars($search_term); ?>">
                            </div>
                            <div class="col-md-4 d-flex">
                                <button type="submit" class="btn btn-primary me-2">Buscar por Nombre</button>
                                <a href="inventory.php" class="btn btn-secondary">Limpiar Búsqueda</a>
                            </div>
                        </form>
                        <br>

                        <!-- Formulario de búsqueda por SKU -->
                        <form class="row g-3" method="post" action="inventory.php">
                            <div class="col-md-8">
                                <input 
                                    type="text" 
                                    name="search_sku" 
                                    class="form-control" 
                                    placeholder="Buscar producto por SKU" 
                                    value="<?php echo htmlspecialchars($search_sku); ?>">
                            </div>
                            <div class="col-md-4 d-flex">
                                <button type="submit" class="btn btn-primary me-2">Buscar por SKU</button>
                                <a href="inventory.php" class="btn btn-secondary">Limpiar Búsqueda</a>
                            </div>
                        </form>
                        <br>
                    </div>
                </div>

                <!-- Botón para alternar visibilidad de las card boxes -->
                <button class="btn btn-info" onclick="toggleSearchCard()">Mostrar/Ocultar Búsquedas</button>

                <!-- Botones de acción -->
                <div class="mb-4">
                    <a href="add_product.php" class="btn btn-success me-2">Añadir Nuevo Producto</a>
                    <a href="add_category.php" class="btn btn-info">Añadir Nueva Categoría</a>
                </div>

                <!-- Tabla de productos -->
                <h2>Productos</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Descripción</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($products) > 0): ?>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($product['ID_Product']); ?></td>
                                        <td><?php echo htmlspecialchars($product['Product']); ?></td>
                                        <td><?php echo htmlspecialchars($product['Description']); ?></td>
                                        <td>
                                            <?php
                                            // Verificar si la categoría existe
                                            echo isset($product['Category']) ? htmlspecialchars($product['Category']) : 'Categoría no disponible';
                                            ?>
                                        </td>
                                        <td>$<?php echo number_format($product['Price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($product['Qty']); ?></td>
                                        <td>
                                            <a href="edit_product.php?id=<?php echo $product['ID_Product']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                            <a href="delete_product.php?id=<?php echo $product['ID_Product']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de querer eliminar este producto?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No se encontraron productos.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Tabla de categorías -->
                <h2>Categorías</h2>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre de Categoría</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($categories) > 0): ?>
                                <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($category['ID_Category']); ?></td>
                                        <td><?php echo htmlspecialchars($category['Category_Name']); ?></td>
                                        <td>
                                            <a href="edit_category.php?id=<?php echo $category['ID_Category']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                            <a href="delete_category.php?id=<?php echo $category['ID_Category']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de querer eliminar esta categoría?');">Eliminar</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center">No se encontraron categorías.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main> 
        </div> 
    </div>
    <script src="js/slide.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
