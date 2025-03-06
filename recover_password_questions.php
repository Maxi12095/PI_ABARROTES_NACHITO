<?php
session_start();
include 'config.php';

// Verificar que el usuario esté en sesión
if (!isset($_SESSION['username'])) {
    header("Location: recover_password_user.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION['username'];
    $answer1 = strtolower(trim($_POST['answer1'])); // Convertir a minúsculas
    $answer2 = strtolower(trim($_POST['answer2'])); // Convertir a minúsculas
    $answer3 = strtolower(trim($_POST['answer3'])); // Convertir a minúsculas

    // Consultar los hashes de las respuestas con un procedimiento almacenado
    $stmt = $conn->prepare("CALL GetAnswerHashesByUser(?)");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $hashes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Validar las respuestas
    if (
        password_verify($answer1, $hashes[0]['Answer_Hash']) &&
        password_verify($answer2, $hashes[1]['Answer_Hash']) &&
        password_verify($answer3, $hashes[2]['Answer_Hash'])
    ) {
        $_SESSION['reset_user'] = $username; // Usuario autenticado para resetear contraseña
        header("Location: reset_password.php");
        exit;
    } else {
        $error_message = "Una o más respuestas son incorrectas. Por favor, inténtalo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responde las Preguntas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Preguntas de Seguridad</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de error -->
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger text-center">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Formulario para responder preguntas -->
                        <form action="recover_password_questions.php" method="post">
                            <?php foreach ($_SESSION['questions'] as $index => $question): ?>
                                <div class="mb-3">
                                    <label for="answer<?php echo $index + 1; ?>" class="form-label">
                                        <?php echo htmlspecialchars($question['Question_Text']); ?>
                                    </label>
                                    <input type="text" id="answer<?php echo $index + 1; ?>" name="answer<?php echo $index + 1; ?>" class="form-control" placeholder="Ingresa tu respuesta" required>
                                </div>
                            <?php endforeach; ?>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Responder</button>
                                <a href="recover_password_user.php" class="btn btn-secondary">Volver</a>
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
