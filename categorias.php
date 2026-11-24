<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

$mensaje = "";
$cat_editar = null;

// Si se hace clic en "Editar" en alguna categoría
if (isset($_GET['editar'])) {
    $stmt = $conexion->prepare("SELECT * FROM categoria WHERE id_categoria = :id");
    $stmt->execute([':id' => $_GET['editar']]);
    $cat_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Guardar nueva categoría o actualizar existente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $id_categoria = $_POST['id_categoria'] ?? '';

    if (!empty($id_categoria)) {
        $stmt = $conexion->prepare("UPDATE categoria SET nombre = :nombre, descripcion = :descripcion WHERE id_categoria = :id");
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion, ':id' => $id_categoria]);
        $mensaje = "<div class='alert alert-success'>Categoría actualizada con éxito.</div>";
        $cat_editar = null;
    } else {
        $stmt = $conexion->prepare("INSERT INTO categoria (nombre, descripcion, estado) VALUES (:nombre, :descripcion, 1)");
        $stmt->execute([':nombre' => $nombre, ':descripcion' => $descripcion]);
        $mensaje = "<div class='alert alert-success'>Categoría registrada con éxito.</div>";
    }
}

$categorias = $conexion->query("SELECT * FROM categoria WHERE estado = 1")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-secondary">🏷️ Gestión de Categorías</h2>
            <a href="dashboard.php" class="btn btn-secondary">Volver al Catálogo</a>
        </div>

        <?php echo $mensaje; ?>

        <!-- Formulario para Crear o Editar -->
        <form method="POST" action="categorias.php" class="row g-3 mb-4 bg-light p-3 rounded border">
            <input type="hidden" name="id_categoria" value="<?php echo $cat_editar['id_categoria'] ?? ''; ?>">
            <div class="col-md-4">
                <label class="form-label">Nombre de la Categoría</label>
                <input type="text" name="nombre" class="form-control" required value="<?php echo $cat_editar['nombre'] ?? ''; ?>">
            </div>
            <div class="col-md-5">
                <label class="form-label">Descripción</label>
                <input type="text" name="descripcion" class="form-control" value="<?php echo $cat_editar['descripcion'] ?? ''; ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-success w-100">
                    <?php echo $cat_editar ? 'Actualizar' : '+ Agregar'; ?>
                </button>
                <?php if ($cat_editar): ?>
                    <a href="categorias.php" class="btn btn-outline-secondary">Cancelar</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Listado -->
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categorias as $cat): ?>
                    <tr>
                        <td><?php echo $cat['id_categoria']; ?></td>
                        <td><b><?php echo $cat['nombre']; ?></b></td>
                        <td><?php echo $cat['descripcion']; ?></td>
                        <td>
                            <a href="categorias.php?editar=<?php echo $cat['id_categoria']; ?>" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>