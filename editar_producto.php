<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: dashboard.php");
    exit();
}

$categorias = $conexion->query("SELECT * FROM categoria WHERE estado = 1")->fetchAll(PDO::FETCH_ASSOC);
$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $id_categoria = $_POST['id_categoria'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock_minimo = $_POST['stock_minimo'];

    if ($precio < 0 || $stock_minimo < 0) {
        $mensaje = "<div class='alert alert-danger'>El precio y el stock mínimo no pueden ser negativos.</div>";
    } else {
        try {
            $sql = "UPDATE producto SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion, 
                    precio = :precio, stock_minimo = :stock_minimo, id_categoria = :id_categoria 
                    WHERE id_producto = :id";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':precio' => $precio,
                ':stock_minimo' => $stock_minimo,
                ':id_categoria' => $id_categoria,
                ':id' => $id
            ]);
            $mensaje = "<div class='alert alert-success'>Producto actualizado. <a href='dashboard.php' class='alert-link'>Volver al catálogo</a></div>";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $mensaje = "<div class='alert alert-danger'>Error: Ese código ya pertenece a otro producto.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al actualizar: " . $e->getMessage() . "</div>";
            }
        }
    }
}

// Cargar datos actuales del producto
$stmtProd = $conexion->prepare("SELECT * FROM producto WHERE id_producto = :id");
$stmtProd->execute([':id' => $id]);
$prod = $stmtProd->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow" style="max-width: 600px;">
        <h2 class="text-warning mb-4">✏️ Editar Producto</h2>
        <?php echo $mensaje; ?>

        <form method="POST">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Código Único</label>
                    <input type="text" class="form-control" name="codigo" value="<?php echo $prod['codigo']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" name="id_categoria" required>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" <?php if($cat['id_categoria'] == $prod['id_categoria']) echo 'selected'; ?>>
                                <?php echo $cat['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" name="nombre" value="<?php echo $prod['nombre']; ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" name="descripcion" rows="2"><?php echo $prod['descripcion']; ?></textarea>
            </div>
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label">Precio ($)</label>
                    <input type="number" class="form-control" name="precio" value="<?php echo $prod['precio']; ?>" required min="0">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Stock Mínimo</label>
                    <input type="number" class="form-control" name="stock_minimo" value="<?php echo $prod['stock_minimo']; ?>" required min="0">
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="dashboard.php" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-warning">Guardar Cambios</button>
            </div>
        </form>
    </div>
</body>
</html>