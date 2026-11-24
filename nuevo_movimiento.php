<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Traemos los productos activos para mostrarlos en la lista
$stmt = $conexion->query("SELECT id_producto, nombre, stock_actual FROM producto WHERE estado = 1");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = $_POST['id_producto'];
    $tipo = $_POST['tipo'];
    $cantidad = (int)$_POST['cantidad'];
    $observacion = $_POST['observacion'];
    $id_usuario = $_SESSION['id_usuario'];

    if ($cantidad <= 0) {
        $mensaje = "<div class='alert alert-danger'>La cantidad debe ser mayor a cero.</div>";
    } else {
        try {
            // Iniciamos una "Transacción": O se guarda todo, o no se guarda nada
            $conexion->beginTransaction();

            // 1. Consultamos el stock actual del producto seleccionado
            $stmtStock = $conexion->prepare("SELECT stock_actual FROM producto WHERE id_producto = :id");
            $stmtStock->execute([':id' => $id_producto]);
            $productoData = $stmtStock->fetch(PDO::FETCH_ASSOC);
            $stock_actual = $productoData['stock_actual'];

            // 2. Calculamos el nuevo stock según la regla de negocio
            if ($tipo == 'entrada') {
                $nuevo_stock = $stock_actual + $cantidad;
            } else { // es salida
                $nuevo_stock = $stock_actual - $cantidad;
                if ($nuevo_stock < 0) {
                    throw new Exception("No hay stock suficiente. El stock actual es de $stock_actual unidades.");
                }
            }

            // 3. Registramos el movimiento en el historial
            $sqlMov = "INSERT INTO movimiento (tipo, cantidad, observacion, id_producto, id_usuario) 
                       VALUES (:tipo, :cantidad, :observacion, :id_producto, :id_usuario)";
            $stmtMov = $conexion->prepare($sqlMov);
            $stmtMov->execute([
                ':tipo' => $tipo,
                ':cantidad' => $cantidad,
                ':observacion' => $observacion,
                ':id_producto' => $id_producto,
                ':id_usuario' => $id_usuario
            ]);

            // 4. Actualizamos el stock automáticamente en la tabla producto
            $sqlUpdate = "UPDATE producto SET stock_actual = :nuevo_stock WHERE id_producto = :id_producto";
            $stmtUpdate = $conexion->prepare($sqlUpdate);
            $stmtUpdate->execute([
                ':nuevo_stock' => $nuevo_stock,
                ':id_producto' => $id_producto
            ]);

            // Si todo salió bien, confirmamos los cambios
            $conexion->commit();
            $mensaje = "<div class='alert alert-success'>Movimiento registrado y stock actualizado con éxito.</div>";

        } catch (Exception $e) {
            // Si hubo un error (ej. stock negativo), deshacemos todo para proteger la base de datos
            $conexion->rollBack();
            $mensaje = "<div class='alert alert-danger'>" . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Movimiento - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow" style="max-width: 600px;">
        <h2 class="text-primary mb-4">🔄 Registrar Entrada o Salida</h2>
        
        <?php echo $mensaje; ?>

        <form action="nuevo_movimiento.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Producto</label>
                <select class="form-select" name="id_producto" required>
                    <option value="">Seleccione un producto...</option>
                    <?php foreach ($productos as $prod): ?>
                        <!-- Mostramos el nombre y cuánto stock tiene actualmente para guiar al usuario -->
                        <option value="<?php echo $prod['id_producto']; ?>">
                            <?php echo $prod['nombre']; ?> (Stock actual: <?php echo $prod['stock_actual']; ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Tipo de Movimiento</label>
                    <select class="form-select" name="tipo" required>
                        <option value="entrada">📥 Entrada (Aumenta stock)</option>
                        <option value="salida">📤 Salida (Disminuye stock)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Cantidad</label>
                    <input type="number" class="form-control" name="cantidad" required min="1">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Observación (Opcional)</label>
                <textarea class="form-control" name="observacion" rows="2" placeholder="Ej: Compra a proveedor, venta local, producto dañado..."></textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="dashboard.php" class="btn btn-secondary">Volver al Catálogo</a>
                <button type="submit" class="btn btn-primary">Registrar Movimiento</button>
            </div>
        </form>
    </div>
</body>
</html>