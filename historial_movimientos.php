<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT m.*, p.nombre AS producto_nombre, p.codigo AS producto_codigo, u.nombre AS usuario_nombre
        FROM movimiento m
        INNER JOIN producto p ON m.id_producto = p.id_producto
        INNER JOIN usuario u ON m.id_usuario = u.id_usuario
        ORDER BY m.id_movimiento DESC";
$movimientos = $conexion->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Movimientos - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-info">📋 Historial de Movimientos</h2>
            <a href="dashboard.php" class="btn btn-secondary">Volver al Catálogo</a>
        </div>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Observación</th>
                    <th>Responsable</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($movimientos) > 0): ?>
                    <?php foreach ($movimientos as $mov): ?>
                        <tr>
                            <td>#<?php echo $mov['id_movimiento']; ?></td>
                            <td><?php echo $mov['producto_codigo'] . " - " . $mov['producto_nombre']; ?></td>
                            <td>
                                <?php if ($mov['tipo'] == 'entrada'): ?>
                                    <span class="badge bg-success">📥 Entrada</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">📤 Salida</span>
                                <?php endif; ?>
                            </td>
                            <td><b><?php echo $mov['cantidad']; ?></b></td>
                            <td><?php echo $mov['observacion']; ?></td>
                            <td><?php echo $mov['usuario_nombre']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">Aún no hay movimientos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>