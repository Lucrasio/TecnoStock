<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php");
    exit();
}

// Traer categorías para el filtro
$categorias = $conexion->query("SELECT * FROM categoria WHERE estado = 1")->fetchAll(PDO::FETCH_ASSOC);

// Lógica del buscador y filtro
$busqueda = $_GET['buscar'] ?? '';
$cat_filtro = $_GET['categoria'] ?? '';

$sql = "SELECT p.*, c.nombre as categoria_nombre 
        FROM producto p 
        LEFT JOIN categoria c ON p.id_categoria = c.id_categoria 
        WHERE p.estado = 1";
$params = [];

if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE :buscar OR p.codigo LIKE :buscar)";
    $params[':buscar'] = "%$busqueda%";
}
if (!empty($cat_filtro)) {
    $sql .= " AND p.id_categoria = :cat";
    $params[':cat'] = $cat_filtro;
}

$stmt = $conexion->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), 
                        url('https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .container {
            background: rgba(255, 255, 255, 0.95) !important;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5) !important;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="p-4">
    <div class="container bg-white p-4 rounded shadow">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-primary">📦 Catálogo de Productos</h2>
            <div>
                <span class="me-3">Hola, <b><?php echo $_SESSION['nombre']; ?></b></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
            </div>
        </div>

        <!-- Botones de navegación -->
        <div class="mb-3 d-flex flex-wrap gap-2">
            <a href="nuevo_producto.php" class="btn btn-success">+ Registrar Producto</a>
            <a href="nuevo_movimiento.php" class="btn btn-primary">🔄 Registrar Movimiento</a>
            <a href="historial_movimientos.php" class="btn btn-info text-white">📋 Historial de Movimientos</a>
            <a href="categorias.php" class="btn btn-secondary">🏷️ Gestionar Categorías</a>
        </div>

        <!-- Buscador y Filtro -->
        <form method="GET" action="dashboard.php" class="row g-2 mb-4 bg-light p-3 rounded border">
            <div class="col-md-5">
                <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre o código..." value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
            <div class="col-md-4">
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id_categoria']; ?>" <?php if($cat_filtro == $cat['id_categoria']) echo 'selected'; ?>>
                            <?php echo $cat['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">🔍 Buscar</button>
                <a href="dashboard.php" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>

        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock Actual</th>
                    <th>Estado Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($productos) > 0): ?>
                    <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td><?php echo $prod['codigo']; ?></td>
                            <td><?php echo $prod['nombre']; ?></td>
                            <td><?php echo $prod['categoria_nombre']; ?></td>
                            <td>$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                            <td><?php echo $prod['stock_actual']; ?></td>
                            <td>
                                <?php if ($prod['stock_actual'] <= $prod['stock_minimo']): ?>
                                    <span class="badge bg-danger">Stock Bajo</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Normal</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="editar_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="eliminar_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron productos.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>