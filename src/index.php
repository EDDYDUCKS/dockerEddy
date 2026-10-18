<?php
require_once __DIR__ . '/db.php';

$mensaje = '';
$error = '';

// Modo edición (Reto A)
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$edit_producto = null;

// Parámetro de búsqueda (Reto B)
$buscar = isset($_GET['q']) ? trim($_GET['q']) : '';

// 1. Manejo de formularios POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Insertar nuevo producto
    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = trim($_POST['precio'] ?? '');

        if ($nombre === '' || $precio === '' || !is_numeric($precio) || (float)$precio < 0) {
            $error = 'Por favor ingresa un nombre válido y un precio numérico positivo.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO productos (nombre, precio) VALUES (:nombre, :precio)');
            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => (float)$precio,
            ]);
            header('Location: index.php');
            exit;
        }
    }

    // Actualizar producto (Reto A)
    if ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = trim($_POST['precio'] ?? '');

        if ($id <= 0 || $nombre === '' || $precio === '' || !is_numeric($precio) || (float)$precio < 0) {
            $error = 'Datos inválidos para actualizar el producto.';
        } else {
            $stmt = $pdo->prepare('UPDATE productos SET nombre = :nombre, precio = :precio WHERE id = :id');
            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => (float)$precio,
                ':id'     => $id,
            ]);
            header('Location: index.php');
            exit;
        }
    }

    // Eliminar producto
    if ($accion === 'eliminar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM productos WHERE id = :id');
            $stmt->execute([':id' => $id]);
        }
        header('Location: index.php');
        exit;
    }
}

// Cargar producto para editar si se solicitó
if ($edit_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = :id');
    $stmt->execute([':id' => $edit_id]);
    $edit_producto = $stmt->fetch();
}

// 2. Consulta de productos (con soporte para búsqueda LIKE - Reto B)
if ($buscar !== '') {
    $stmt = $pdo->prepare('SELECT * FROM productos WHERE nombre LIKE :termino ORDER BY id DESC');
    $stmt->execute([':termino' => "%{$buscar}%"]);
    $productos = $stmt->fetchAll();
} else {
    $stmt = $pdo->query('SELECT * FROM productos ORDER BY id DESC');
    $productos = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de productos</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background-color: #f7f9fc;
            color: #333;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            padding: 35px 40px;
        }
        h1 {
            margin-top: 0;
            font-size: 26px;
            color: #2c3e50;
            border-bottom: 2px solid #edf2f7;
            padding-bottom: 15px;
        }
        h2 {
            font-size: 19px;
            color: #34495e;
            margin-top: 25px;
            margin-bottom: 12px;
        }
        .form-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        input[type="text"], input[type="number"] {
            padding: 10px 14px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 14px;
            flex: 1;
            min-width: 180px;
            outline: none;
            transition: border-color 0.2s;
        }
        input[type="text"]:focus, input[type="number"]:focus {
            border-color: #3182ce;
        }
        .btn {
            background-color: #edf2f7;
            color: #2d3748;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background-color: #e2e8f0;
        }
        .btn-primary {
            background-color: #3182ce;
            color: #ffffff;
            border-color: #2b6cb0;
        }
        .btn-primary:hover {
            background-color: #2b6cb0;
        }
        .btn-danger {
            background-color: #fff5f5;
            color: #e53e3e;
            border-color: #feb2b2;
            padding: 6px 12px;
            font-size: 13px;
        }
        .btn-danger:hover {
            background-color: #e53e3e;
            color: #fff;
        }
        .btn-secondary {
            background-color: #ebf8ff;
            color: #3182ce;
            border-color: #bee3f8;
            padding: 6px 12px;
            font-size: 13px;
            margin-right: 6px;
        }
        .btn-secondary:hover {
            background-color: #3182ce;
            color: #fff;
        }
        .search-box {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            background: #f8fafc;
            padding: 12px;
            border-radius: 6px;
            align-items: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            text-align: left;
            padding: 12px 14px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
        }
        th {
            background-color: #f7fafc;
            color: #4a5568;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f8fafc;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background-color: #fff5f5;
            color: #c53030;
            border: 1px solid #feb2b2;
        }
        .actions-cell {
            white-space: nowrap;
        }
        .empty-row {
            text-align: center;
            color: #718096;
            padding: 24px;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Tienda de productos</h1>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Formulario de Registro / Edición -->
    <h2><?= $edit_producto ? 'Editar producto #' . htmlspecialchars($edit_producto['id']) : 'Registrar producto' ?></h2>
    <form method="POST" action="index.php" class="form-row">
        <?php if ($edit_producto): ?>
            <input type="hidden" name="accion" value="actualizar">
            <input type="hidden" name="id" value="<?= htmlspecialchars($edit_producto['id']) ?>">
            <input type="text" name="nombre" value="<?= htmlspecialchars($edit_producto['nombre']) ?>" required placeholder="Nombre del producto">
            <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($edit_producto['precio']) ?>" required placeholder="Precio">
            <button type="submit" class="btn btn-primary">Actualizar</button>
            <a href="index.php" class="btn">Cancelar</a>
        <?php else: ?>
            <input type="hidden" name="accion" value="crear">
            <input type="text" name="nombre" required placeholder="Nombre del producto">
            <input type="number" step="0.01" name="precio" required placeholder="Precio">
            <button type="submit" class="btn">Guardar</button>
        <?php endif; ?>
    </form>

    <!-- Búsqueda (Reto B) -->
    <h2>Productos registrados</h2>
    <form method="GET" action="index.php" class="search-box">
        <input type="text" name="q" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar por nombre...">
        <button type="submit" class="btn">Buscar</button>
        <?php if ($buscar !== ''): ?>
            <a href="index.php" class="btn">Limpiar</a>
        <?php endif; ?>
    </form>

    <!-- Tabla de Productos -->
    <table>
        <thead>
            <tr>
                <th style="width: 60px;">ID</th>
                <th>Nombre</th>
                <th style="width: 110px;">Precio</th>
                <th style="width: 170px;">Creado</th>
                <th style="width: 150px;">Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="5" class="empty-row">No se encontraron productos registrados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['nombre']) ?></td>
                        <td>$<?= number_format((float)$p['precio'], 2) ?></td>
                        <td><?= htmlspecialchars($p['creado_en']) ?></td>
                        <td class="actions-cell">
                            <a href="index.php?edit=<?= htmlspecialchars($p['id']) ?>" class="btn btn-secondary">Editar</a>
                            <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
