<?php
session_start(); // Inicia la sesión para recordar que el usuario está conectado
require_once 'conexion.php'; // Trae tu puente de conexión

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];

    // Consulta preparada por seguridad (exigido en tu rúbrica)
    $sql = "SELECT id_usuario, nombre FROM usuario WHERE correo = :correo AND clave = :clave AND estado = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':clave', $clave);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Si coincide, guardamos sus datos y lo mandamos al panel principal
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        header("Location: dashboard.php");
        exit();
    } else {
        // Si se equivoca, mostramos una alerta y lo devolvemos
        echo "<script>alert('Correo o contraseña incorrectos'); window.location.href='index.php';</script>";
    }
}
?>