<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? null;

if ($id) {
    // Eliminación lógica: cambia el estado a 0 para ocultarlo sin romper el historial
    $stmt = $conexion->prepare("UPDATE producto SET estado = 0 WHERE id_producto = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: dashboard.php");
exit();
?>