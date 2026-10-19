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

        if ($nombre === '') {
            $error = 'El nombre del producto no puede estar vacío.';
        } elseif (mb_strlen($nombre) > 100) {
            $error = 'El nombre del producto no puede exceder los 100 caracteres.';
        } elseif ($precio === '' || !is_numeric($precio) || (float)$precio <= 0) {
            $error = 'El precio debe ser un número positivo mayor a 0 (ej. 15.50).';
        } else {
            $stmt = $pdo->prepare('INSERT INTO productos (nombre, precio) VALUES (:nombre, :precio)');
            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => (float)$precio,
            ]);
            header('Location: index.php?msg=creado');
            exit;
        }
    }

    // Actualizar producto (Reto A)
    if ($accion === 'actualizar') {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $precio = trim($_POST['precio'] ?? '');

        if ($id <= 0) {
            $error = 'Identificador de producto inválido.';
        } elseif ($nombre === '') {
            $error = 'El nombre del producto no puede estar vacío.';
        } elseif (mb_strlen($nombre) > 100) {
            $error = 'El nombre del producto no puede exceder los 100 caracteres.';
        } elseif ($precio === '' || !is_numeric($precio) || (float)$precio <= 0) {
            $error = 'El precio debe ser un número positivo mayor a 0.';
        } else {
            $stmt = $pdo->prepare('UPDATE productos SET nombre = :nombre, precio = :precio WHERE id = :id');
            $stmt->execute([
                ':nombre' => $nombre,
                ':precio' => (float)$precio,
                ':id'     => $id,
            ]);
            header('Location: index.php?msg=actualizado');
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
        header('Location: index.php?msg=eliminado');
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

$msg_param = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda de Productos | ULSA Docker</title>
    <style>
        :root {
            --primary: #9b51e0;
            --primary-dark: #7b2cbf;
            --primary-light: #bb86fc;
            --primary-soft: #f3e8ff;
            --accent: #ec4899;
            --bg-canvas: #a855f7;
            --card-bg: #ffffff;
            --text-main: #2d1847;
            --text-muted: #8c75a5;
            --border-soft: #f0e6fc;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #a855f7 0%, #9333ea 50%, #7e22ce 100%);
            min-height: 100vh;
            padding: 40px 15px;
            display: flex;
            justify-content: center;
        }
        .app-window {
            width: 100%;
            max-width: 1100px;
            background: #ffffff;
            border-radius: 36px;
            box-shadow: 0 25px 60px -15px rgba(50, 0, 90, 0.4);
            overflow: hidden;
            position: relative;
        }

        /* Hero Purple Curve Header */
        .hero-banner {
            background: linear-gradient(180deg, #8b5cf6 0%, #a855f7 70%, #c084fc 100%);
            padding: 30px 40px 70px 40px;
            border-bottom-left-radius: 50% 30px;
            border-bottom-right-radius: 50% 30px;
            color: #ffffff;
            text-align: center;
            position: relative;
        }
        .nav-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
        }
        .logo-box {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .logo-box span {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 13px;
        }
        .nav-menu {
            display: flex;
            gap: 22px;
            align-items: center;
        }
        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.2s;
        }
        .nav-link:hover {
            color: #ffffff;
        }
        .btn-nav-login {
            border: 1.5px solid rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            padding: 6px 18px;
            font-size: 13px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-nav-login:hover {
            background: #ffffff;
            color: var(--primary-dark);
        }

        .hero-title {
            font-size: 34px;
            font-weight: 700;
            margin-bottom: 24px;
            letter-spacing: -0.5px;
        }

        /* Search Bar */
        .search-form {
            max-width: 520px;
            margin: 0 auto 16px auto;
            position: relative;
            display: flex;
        }
        .search-input {
            width: 100%;
            padding: 14px 50px 14px 24px;
            border-radius: 50px;
            border: none;
            outline: none;
            font-size: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            color: #333;
        }
        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: #9333ea;
            border: none;
            color: #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .search-btn:hover {
            background: #7e22ce;
        }

        /* Quick Pills */
        .pills-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            flex-wrap: wrap;
        }
        .pill-badge {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(5px);
            color: #ffffff;
            padding: 7px 18px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .pill-badge:hover, .pill-badge.active {
            background: #ffffff;
            color: #9333ea;
        }

        /* Main Content Container */
        .content-body {
            padding: 35px 45px 50px 45px;
        }

        /* Toast Notifications */
        .alert {
            padding: 14px 20px;
            border-radius: 14px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: fadeIn 0.3s ease;
        }
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background-color: #fdf2f8;
            color: #9d174d;
            border: 1px solid #fbcfe8;
        }

        /* Promo Banner Widget */
        .promo-card {
            background: linear-gradient(100deg, #8b5cf6 0%, #ec4899 100%);
            border-radius: 24px;
            padding: 24px 35px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            box-shadow: 0 10px 25px rgba(168, 85, 247, 0.25);
        }
        .promo-text h3 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        .promo-text p {
            font-size: 20px;
            font-weight: 700;
        }
        .promo-badge {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(8px);
            padding: 12px 24px;
            border-radius: 20px;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        /* Form Card */
        .form-section {
            background: #faf5ff;
            border: 1.5px solid #f3e8ff;
            border-radius: 24px;
            padding: 28px 32px;
            margin-bottom: 40px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }
        .section-header h2 {
            font-size: 20px;
            color: var(--text-main);
            font-weight: 700;
        }
        .form-inputs {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .input-group {
            flex: 1;
            min-width: 220px;
        }
        .form-control {
            width: 100%;
            padding: 12px 18px;
            border-radius: 14px;
            border: 1.5px solid #e9d5ff;
            font-size: 14px;
            outline: none;
            background: #ffffff;
            color: #333;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: #9333ea;
            box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.15);
        }
        .btn-action {
            background: linear-gradient(135deg, #9333ea 0%, #c084fc 100%);
            color: #ffffff;
            border: none;
            padding: 12px 28px;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 6px 15px rgba(147, 51, 234, 0.25);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(147, 51, 234, 0.35);
        }
        .btn-cancel {
            background: #ede9fe;
            color: #6b21a8;
            box-shadow: none;
        }
        .btn-cancel:hover {
            background: #ddd6fe;
        }

        /* View Toggle Tabs */
        .tabs-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .collection-title {
            font-size: 22px;
            color: #7e22ce;
            font-weight: 800;
        }
        .view-switcher {
            display: flex;
            background: #f3e8ff;
            padding: 4px;
            border-radius: 14px;
            gap: 4px;
        }
        .btn-tab {
            border: none;
            background: transparent;
            padding: 6px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            color: #7e22ce;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-tab.active {
            background: #ffffff;
            color: #9333ea;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        /* Product Cards Grid (Matching mockup) */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 22px;
            margin-bottom: 40px;
        }
        .product-card {
            background: #ffffff;
            border: 1.5px solid #f3e8ff;
            border-radius: 20px;
            padding: 16px;
            transition: all 0.25s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(168, 85, 247, 0.15);
            border-color: #d8b4fe;
        }
        .card-visual {
            height: 140px;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 14px;
            position: relative;
            overflow: hidden;
        }
        .visual-icon {
            width: 50px;
            height: 50px;
            opacity: 0.55;
            color: #9333ea;
        }
        .card-id-tag {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(255, 255, 255, 0.85);
            font-size: 11px;
            font-weight: 700;
            color: #7e22ce;
            padding: 2px 8px;
            border-radius: 8px;
        }
        .card-title {
            font-size: 15px;
            font-weight: 700;
            color: #2e1065;
            margin-bottom: 6px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .card-price {
            font-size: 17px;
            font-weight: 800;
            color: #9333ea;
            margin-bottom: 8px;
        }
        .card-date {
            font-size: 11px;
            color: #a855f7;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .card-actions {
            margin-top: auto;
            display: flex;
            gap: 8px;
        }
        .btn-card-edit {
            flex: 1;
            padding: 7px 0;
            text-align: center;
            background: #f3e8ff;
            color: #7e22ce;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        .btn-card-edit:hover {
            background: #9333ea;
            color: #ffffff;
        }
        .btn-card-delete {
            flex: 1;
            padding: 7px 0;
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-card-delete:hover {
            background: #ef4444;
            color: #ffffff;
        }

        /* University Requirement Table View */
        .table-view-box {
            display: none;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #f3e8ff;
            overflow: hidden;
            margin-bottom: 40px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            text-align: left;
            padding: 14px 18px;
            border-bottom: 1px solid #f3e8ff;
            font-size: 14px;
        }
        th {
            background-color: #faf5ff;
            color: #6b21a8;
            font-weight: 700;
        }
        tr:hover {
            background-color: #faf5ff;
        }

        /* Footer Widget */
        .footer-newsletter {
            background: #f3e8ff;
            border-radius: 24px;
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .newsletter-info h4 {
            color: #581c87;
            font-size: 17px;
            margin-bottom: 4px;
        }
        .newsletter-info p {
            color: #7e22ce;
            font-size: 13px;
        }
        .newsletter-form {
            display: flex;
            gap: 10px;
        }
        .newsletter-input {
            padding: 10px 18px;
            border-radius: 25px;
            border: 1px solid #d8b4fe;
            background: #ffffff;
            font-size: 13px;
            outline: none;
        }
        .btn-subscribe {
            background: #ec4899;
            color: #ffffff;
            border: none;
            padding: 10px 22px;
            border-radius: 25px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-subscribe:hover {
            background: #db2777;
        }
        .empty-state {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            color: #9333ea;
            background: #faf5ff;
            border-radius: 20px;
            border: 2px dashed #e9d5ff;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-8px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="app-window">
    <!-- Hero Section con curva morada -->
    <div class="hero-banner">
        <header class="nav-header">
            <div class="logo-box">
                LOGO <span>ULSA SHOP</span>
            </div>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link">Inicio</a>
                <a href="#catalogo" class="nav-link">Productos</a>
                <a href="#formulario" class="nav-link">Registrar</a>
                <a href="index.php" class="btn-nav-login">Docker PHP</a>
            </nav>
        </header>

        <h1 class="hero-title">Encuentra todo lo que buscas aquí</h1>

        <!-- Buscador estilizado (Reto B) -->
        <form method="GET" action="index.php" class="search-form">
            <input type="text" name="q" value="<?= htmlspecialchars($buscar) ?>" placeholder="Buscar por nombre..." class="search-input">
            <button type="submit" class="search-btn" title="Buscar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </button>
        </form>

        <!-- Pills de filtro rápido -->
        <div class="pills-group">
            <a href="index.php" class="pill-badge <?= $buscar === '' ? 'active' : '' ?>">Todos</a>
            <a href="index.php?q=Monitor" class="pill-badge <?= $buscar === 'Monitor' ? 'active' : '' ?>">Monitores</a>
            <a href="index.php?q=Pantalla" class="pill-badge <?= $buscar === 'Pantalla' ? 'active' : '' ?>">Pantallas</a>
            <a href="index.php?q=Gaming" class="pill-badge <?= $buscar === 'Gaming' ? 'active' : '' ?>">Gaming</a>
        </div>
    </div>

    <!-- Cuerpo principal -->
    <main class="content-body">

        <?php if ($error): ?>
            <div class="alert alert-error">
                <span>⚠️ <?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($msg_param === 'creado'): ?>
            <div class="alert alert-success">
                <span>✨ ¡Producto registrado exitosamente en MySQL!</span>
            </div>
        <?php elseif ($msg_param === 'actualizado'): ?>
            <div class="alert alert-success">
                <span>✏️ ¡Producto actualizado con éxito!</span>
            </div>
        <?php elseif ($msg_param === 'eliminado'): ?>
            <div class="alert alert-success">
                <span>🗑️ ¡Producto eliminado correctamente!</span>
            </div>
        <?php endif; ?>

        <!-- Banner de Promoción 20% (Como el diseño) -->
        <div class="promo-card">
            <div class="promo-text">
                <h3>OFERTA ESPECIAL ULSA</h3>
                <p>Descuentos en productos de tecnología seleccionados</p>
            </div>
            <div class="promo-badge">20%</div>
        </div>

        <!-- Formulario de Registro / Edición -->
        <section id="formulario" class="form-section">
            <div class="section-header">
                <h2><?= $edit_producto ? 'Editar producto #' . htmlspecialchars($edit_producto['id']) : 'Registrar producto' ?></h2>
                <?php if ($edit_producto): ?>
                    <span style="font-size: 13px; color: #9333ea; font-weight: 600;">Modificando registro existente</span>
                <?php endif; ?>
            </div>

            <form method="POST" action="index.php" class="form-inputs">
                <?php if ($edit_producto): ?>
                    <input type="hidden" name="accion" value="actualizar">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($edit_producto['id']) ?>">
                    <div class="input-group">
                        <input type="text" name="nombre" value="<?= htmlspecialchars($edit_producto['nombre']) ?>" required maxlength="100" placeholder="Nombre del producto" class="form-control">
                    </div>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0.01" name="precio" value="<?= htmlspecialchars($edit_producto['precio']) ?>" required placeholder="Precio" class="form-control">
                    </div>
                    <button type="submit" class="btn-action">Actualizar</button>
                    <a href="index.php" class="btn-action btn-cancel">Cancelar</a>
                <?php else: ?>
                    <input type="hidden" name="accion" value="crear">
                    <div class="input-group">
                        <input type="text" name="nombre" required maxlength="100" placeholder="Nombre del producto" class="form-control">
                    </div>
                    <div class="input-group">
                        <input type="number" step="0.01" min="0.01" name="precio" required placeholder="Precio" class="form-control">
                    </div>
                    <button type="submit" class="btn-action">Guardar</button>
                <?php endif; ?>
            </form>
        </section>

        <!-- Barra de títulos y Selector de Vistas -->
        <div class="tabs-bar" id="catalogo">
            <h2 class="collection-title">Nuestra Colección</h2>
            
            <div class="view-switcher">
                <button type="button" class="btn-tab active" id="btn-grid" onclick="switchView('grid')">
                    Tarjetas Dinámicas
                </button>
                <button type="button" class="btn-tab" id="btn-table" onclick="switchView('table')">
                    Tabla Oficial
                </button>
            </div>
        </div>

        <!-- 1. VISTA DE TARJETAS (Idéntica al mockup morado) -->
        <div class="products-grid" id="grid-view">
            <?php if (empty($productos)): ?>
                <div class="empty-state">
                    <h3>No se encontraron productos registrados</h3>
                    <p style="margin-top: 6px; font-size: 14px;">Intenta con otro término de búsqueda o agrega un nuevo producto en el formulario superior.</p>
                </div>
            <?php else: ?>
                <?php foreach ($productos as $p): ?>
                    <div class="product-card">
                        <div class="card-visual">
                            <span class="card-id-tag">#<?= htmlspecialchars($p['id']) ?></span>
                            <!-- Icono decorativo estilo mockup -->
                            <svg class="visual-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="8" y1="21" x2="16" y2="21"></line>
                                <line x1="12" y1="17" x2="12" y2="21"></line>
                            </svg>
                        </div>
                        <h3 class="card-title" title="<?= htmlspecialchars($p['nombre']) ?>"><?= htmlspecialchars($p['nombre']) ?></h3>
                        <div class="card-price">$<?= number_format((float)$p['precio'], 2) ?></div>
                        <div class="card-date">
                            <span>📅</span> <?= htmlspecialchars($p['creado_en']) ?>
                        </div>
                        <div class="card-actions">
                            <a href="index.php?edit=<?= htmlspecialchars($p['id']) ?>#formulario" class="btn-card-edit">Editar</a>
                            <form method="POST" action="index.php" style="flex:1;" onsubmit="return confirm('¿Seguro que deseas eliminar este producto?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                                <button type="submit" class="btn-card-delete" style="width:100%;">Eliminar</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- 2. VISTA DE TABLA OFICIAL (Requisito estricto de la práctica) -->
        <div class="table-view-box" id="table-view">
            <table>
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Nombre</th>
                        <th style="width: 130px;">Precio</th>
                        <th style="width: 180px;">Creado</th>
                        <th style="width: 180px;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #888; padding: 25px;">No hay productos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $p): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($p['id']) ?></strong></td>
                                <td><?= htmlspecialchars($p['nombre']) ?></td>
                                <td style="color: #9333ea; font-weight: 700;">$<?= number_format((float)$p['precio'], 2) ?></td>
                                <td><?= htmlspecialchars($p['creado_en']) ?></td>
                                <td>
                                    <a href="index.php?edit=<?= htmlspecialchars($p['id']) ?>#formulario" class="btn-action" style="padding: 6px 14px; font-size: 12px; margin-right: 5px;">Editar</a>
                                    <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('¿Deseas eliminar este registro?');">
                                        <input type="hidden" name="accion" value="eliminar">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                                        <button type="submit" class="btn-action btn-cancel" style="padding: 6px 14px; font-size: 12px; color: #dc2626; background: #fee2e2;">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer estilo Newsletter del mockup -->
        <div class="footer-newsletter">
            <div class="newsletter-info">
                <h4>Suscríbete a nuestras novedades</h4>
                <p>Infraestructura tecnológica y prácticas con Docker en ULSA.</p>
            </div>
            <div class="newsletter-form">
                <input type="email" placeholder="Tu correo electrónico" class="newsletter-input" readonly value="estudiante@ulsa.edu.ni">
                <button type="button" class="btn-subscribe">Suscribirme</button>
            </div>
        </div>

    </main>
</div>

<script>
function switchView(view) {
    const grid = document.getElementById('grid-view');
    const table = document.getElementById('table-view');
    const btnGrid = document.getElementById('btn-grid');
    const btnTable = document.getElementById('btn-table');

    if (view === 'grid') {
        grid.style.display = 'grid';
        table.style.display = 'none';
        btnGrid.classList.add('active');
        btnTable.classList.remove('active');
    } else {
        grid.style.display = 'none';
        table.style.display = 'block';
        btnTable.classList.add('active');
        btnGrid.classList.remove('active');
    }
}
</script>

</body>
</html>
