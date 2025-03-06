<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['username']) || $_SESSION['role'] != 1) { // Solo el rol de administrador (1) puede acceder
    header("Location: login.php");
    exit;
}

// Manejar las acciones: agregar, editar, eliminar
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_question'])) {
        $question_text = $_POST['question_text'];

        // Llamar al procedimiento almacenado para agregar una pregunta
        $query = "CALL AddSecurityQuestion(?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $question_text);
        $stmt->execute();
        $stmt->close();
        $success_message = "Pregunta agregada exitosamente.";
    }

    if (isset($_POST['edit_question'])) {
        $question_id = $_POST['question_id'];
        $question_text = $_POST['question_text'];

        // Llamar al procedimiento almacenado para actualizar la pregunta
        $query = "CALL UpdateSecurityQuestion(?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $question_id, $question_text);
        $stmt->execute();
        $stmt->close();
        $success_message = "Pregunta actualizada exitosamente.";
    }

    if (isset($_POST['delete_question'])) {
        $question_id = $_POST['question_id'];

        // Llamar al procedimiento almacenado para eliminar la pregunta
        $query = "CALL DeleteSecurityQuestion(?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();
        $success_message = "Pregunta eliminada exitosamente.";
    }
}

// Obtener todas las preguntas
$query = "SELECT * FROM security_questions";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Preguntas de Seguridad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="card shadow-lg">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Gestionar Preguntas de Seguridad</h4>
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

                        <!-- Formulario para agregar nueva pregunta -->
                        <form action="manage_questions.php" method="post">
                            <div class="mb-3">
                                <label for="question_text" class="form-label">Nueva Pregunta:</label>
                                <input type="text" id="question_text" name="question_text" class="form-control" placeholder="Escribe una nueva pregunta" required>
                            </div>
                            <button type="submit" name="add_question" class="btn btn-success">Agregar Pregunta</button>
                        </form>

                        <hr>

                        <h5 class="mt-4">Lista de Preguntas de Seguridad</h5>
                        <table class="table table-striped table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Pregunta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ID_Question']); ?></td>
                                    <td><?php echo htmlspecialchars($row['Question_Text']); ?></td>
                                    <td>
                                        <!-- Formulario para editar una pregunta -->
                                        <form action="manage_questions.php" method="post" class="d-inline-block">
                                            <input type="hidden" name="question_id" value="<?php echo $row['ID_Question']; ?>">
                                            <input type="text" name="question_text" value="<?php echo htmlspecialchars($row['Question_Text']); ?>" class="form-control form-control-sm" required>
                                            <button type="submit" name="edit_question" class="btn btn-warning btn-sm mt-2">Editar</button>
                                        </form>

                                        <!-- Formulario para eliminar una pregunta -->
                                        <form action="manage_questions.php" method="post" class="d-inline-block mt-2">
                                            <input type="hidden" name="question_id" value="<?php echo $row['ID_Question']; ?>">
                                            <button type="submit" name="delete_question" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta pregunta?');">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="admin_dashboard.php" class="btn btn-secondary">Volver al Panel de Administración</a>
                        </div>
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
