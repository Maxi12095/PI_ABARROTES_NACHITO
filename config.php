<?php
$servername = "localhost";
$username = "root"; // Usuario por defecto de XAMPP para MySQL
$password = ""; // Por defecto, XAMPP no tiene contraseña para MySQL
$dbname = "abana"; // Nombre de la base de datos actualizada

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Confirmar conexión exitosa
//echo "SE CONECTO LA BASE PENDEJO";
?>