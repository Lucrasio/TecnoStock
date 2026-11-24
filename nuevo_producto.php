<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Obtenemos las categorías de la base de datos para mostrarlas en la lista desplegable
$stmt = $conexion->query("SELECT * FROM categoria WHERE estado = 1");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mensaje = "";

// Si el usuario envió el formulario...
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $id_categoria = $_POST['id_categoria'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock_actual = $_POST['stock_actual'];
    $stock_minimo = $_POST['stock_minimo'];

    // Validación en PHP: Comprobar que no envíen números negativos burlando el HTML
    if ($precio < 0 || $stock_actual < 0 || $stock_minimo < 0) {
        $mensaje = "<div class='alert alert-danger'>El precio y el stock no pueden ser negativos.</div>";
    } else {
        try {
            // Inserción segura con PDO
            $sql = "INSERT INTO producto (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, id_categoria) 
                    VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, :id_categoria)";
            $stmt = $conexion->prepare($sql);
            $stmt->execute([
                ':codigo' => $codigo,
                ':nombre' => $nombre,
                ':descripcion' => $descripcion,
                ':precio' => $precio,
                ':stock_actual' => $stock_actual,
                ':stock_minimo' => $stock_minimo,
                ':id_categoria' => $id_categoria
            ]);
            $mensaje = "<div class='alert alert-success'>Producto guardado. <a href='dashboard.php' class='alert-link'>Ver catálogo</a></div>";
        } catch (PDOException $e) {
            // El error 23000 de SQL significa que intentaron guardar un código que ya existe
            if ($e->getCode() == 23000) {
                $mensaje = "<div class='alert alert-danger'>Error: El código ingresado ya pertenece a otro producto.</div>";
            } else {
                $mensaje = "<div class='alert alert-danger'>Error al guardar: " . $e->getMessage() . "</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-4">
    <div class="container bg-white p-4 rounded shadow" style="max-width: 600px;">
        <h2 class="text-primary mb-4">➕ Registrar Producto</h2>
        
        <?php echo $mensaje; ?>

        <form action="nuevo_producto.php" method="POST" id="formProducto">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Código Único</label>
                    <input type="text" class="form-control" name="codigo" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Categoría</label>
                    <select class="form-select" name="id_categoria" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>"><?php echo $cat['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" name="nombre" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea class="form-control" name="descripcion" rows="2"></textarea>
            </div>
            <div class="row mb-4">
                <div class="col-md-4">
                    <label class="form-label">Precio ($)</label>
                    <input type="number" class="form-control" name="precio" id="precio" required min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock Inicial</label>
                    <input type="number" class="form-control" name="stock_actual" id="stock_actual" required min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Stock Mínimo</label>
                    <input type="number" class="form-control" name="stock_minimo" id="stock_minimo" required min="0">
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar Producto</button>
            </div>
        </form>
    </div>

    <script>
        // Validación en JavaScript: Protege la interfaz antes de enviar a PHP
        document.getElementById('formProducto').addEventListener('submit', function(e) {
            let precio = document.getElementById('precio').value;
            let stock_actual = document.getElementById('stock_actual').value;
            let stock_minimo = document.getElementById('stock_minimo').value;

            if (precio < 0 || stock_actual < 0 || stock_minimo < 0) {
                e.preventDefault(); // Detiene el envío
                alert('Atención: El precio y las cantidades de stock no pueden ser negativos.');
            }
        });
    </script>
</body>
</html>