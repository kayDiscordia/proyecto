<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$dbname = 'proyecto';
$user = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener la cédula enviada por AJAX
    $cedula = $_GET['cedula'];

    // Consulta para verificar si la cédula ya existe
    $query = "SELECT COUNT(*) AS count FROM empleados WHERE cedula = :cedula";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':cedula', $cedula);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver una respuesta JSON
    if ($result['count'] > 0) {
        echo json_encode(['existe' => true]);
    } else {
        echo json_encode(['existe' => false]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
}
?>