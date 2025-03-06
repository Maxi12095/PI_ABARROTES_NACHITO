<?php
session_start();
include 'config.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener todas las preguntas de seguridad a través del proceso almacenado
$stmt = $conn->prepare("CALL GetSecurityQuestions()");
$stmt->execute();
$result = $stmt->get_result();
$questions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Dividir las preguntas en subconjuntos de 3
$question_groups = array_chunk($questions, 3);

// Procesar el formulario al enviarlo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $question1_id = $_POST['question1'];
    $answer1 = strtolower(trim($_POST['answer1'])); // Convertir a minúsculas

    $question2_id = $_POST['question2'];
    $answer2 = strtolower(trim($_POST['answer2'])); // Convertir a minúsculas

    $question3_id = $_POST['question3'];
    $answer3 = strtolower(trim($_POST['answer3'])); // Convertir a minúsculas

    // Validar que las preguntas sean diferentes
    if ($question1_id === $question2_id || $question1_id === $question3_id || $question2_id === $question3_id) {
        $error_message = "Por favor, selecciona preguntas diferentes.";
    } else {
        $user_id = $_SESSION['user_id'];

        // Insertar respuestas en la base de datos usando el proceso almacenado
        $stmt = $conn->prepare("CALL InsertUserSecurityAnswer(?, ?, ?)");

        // Encriptar las respuestas y ejecutarlas
        $answer1_hash = password_hash($answer1, PASSWORD_DEFAULT);
        $stmt->bind_param("iis", $user_id, $question1_id, $answer1_hash);
        $stmt->execute();

        $answer2_hash = password_hash($answer2, PASSWORD_DEFAULT);
        $stmt->bind_param("iis", $user_id, $question2_id, $answer2_hash);
        $stmt->execute();

        $answer3_hash = password_hash($answer3, PASSWORD_DEFAULT);
        $stmt->bind_param("iis", $user_id, $question3_id, $answer3_hash);
        $stmt->execute();

        $stmt->close();

        $success_message = "Preguntas configuradas correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Preguntas de Seguridad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Validar que las preguntas sean diferentes
        function validateQuestions() {
            const question1 = document.getElementById('question1').value;
            const question2 = document.getElementById('question2').value;
            const question3 = document.getElementById('question3').value;

            if (question1 === question2 || question1 === question3 || question2 === question3) {
                alert("Por favor, selecciona preguntas diferentes.");
                return false;
            }
            return true;
        }

        // Convertir las respuestas a minúsculas automáticamente
        function toLowerCaseInput(inputId) {
            const input = document.getElementById(inputId);
            input.value = input.value.toLowerCase();
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Configurar Preguntas de Seguridad</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger text-center">
                                <?php echo $error_message; ?>
                            </div>
                        <?php elseif (isset($success_message)): ?>
                            <div class="alert alert-success text-center">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="configure_security_questions.php" method="post" onsubmit="return validateQuestions();">
                            <div class="mb-3">
                                <label for="question1" class="form-label">Pregunta 1:</label>
                                <select id="question1" name="question1" class="form-select" required>
                                    <option value="">--Selecciona una pregunta--</option>
                                    <?php foreach ($question_groups[0] as $question): ?>
                                        <option value="<?php echo $question['ID_Question']; ?>">
                                            <?php echo htmlspecialchars($question['Question_Text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" id="answer1" name="answer1" class="form-control mt-2" placeholder="Respuesta" required oninput="toLowerCaseInput('answer1');">
                            </div>

                            <div class="mb-3">
                                <label for="question2" class="form-label">Pregunta 2:</label>
                                <select id="question2" name="question2" class="form-select" required>
                                    <option value="">--Selecciona una pregunta--</option>
                                    <?php foreach ($question_groups[1] as $question): ?>
                                        <option value="<?php echo $question['ID_Question']; ?>">
                                            <?php echo htmlspecialchars($question['Question_Text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" id="answer2" name="answer2" class="form-control mt-2" placeholder="Respuesta" required oninput="toLowerCaseInput('answer2');">
                            </div>

                            <div class="mb-3">
                                <label for="question3" class="form-label">Pregunta 3:</label>
                                <select id="question3" name="question3" class="form-select" required>
                                    <option value="">--Selecciona una pregunta--</option>
                                    <?php foreach ($question_groups[2] as $question): ?>
                                        <option value="<?php echo $question['ID_Question']; ?>">
                                            <?php echo htmlspecialchars($question['Question_Text']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" id="answer3" name="answer3" class="form-control mt-2" placeholder="Respuesta" required oninput="toLowerCaseInput('answer3');">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Guardar Preguntas</button>
                                <a href="javascript:history.back()" class="btn btn-secondary">Volver</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
