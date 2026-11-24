<?php
// Datos de configuración de la base de datos
$host = "localhost";
$dbname = "tecnostock";
$username = "root"; // Usuario por defecto en XAMPP
$password = "";     // Contraseña por defecto en XAMPP (vacía)

try {
    // Intentamos conectar a la base de datos usando PDO
    $conexion = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Configuramos PDO para que nos muestre los errores si algo falla
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Mensaje temporal para comprobar que funciona (luego lo borraremos)
    
    
} catch(PDOException $e) {
    // Si hay un error, lo atrapamos y mostramos el mensaje
    die("Error de conexión: " . $e->getMessage());
}
?>