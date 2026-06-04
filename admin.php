<?php
require 'config/db.php';
session_start();
$error = $_SESSION['admin_error'] ?? '';
$success = $_SESSION['admin_success'] ?? '';
unset($_SESSION['admin_error'], $_SESSION['admin_success']);
if (!isset($_SESSION['user']) || !$_SESSION['user']['is_admin']) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';
$edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_action'])) {
    $order_id = (int)$_POST['order_id'];
    $action = $_POST['action'];
    $comment = trim($_POST['admin_comment'] ?? '');

    if ($action === 'confirm') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed', admin_comment = ? WHERE id = ?");
        $stmt->execute([$comment, $order_id]);
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'rejected', admin_comment = ? WHERE id = ?");
        $stmt->execute([$comment, $order_id]);
    }
    header("Location: admin.php");
    exit;
}

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE product_id = ?");
    $stmt->execute([$delete_id]);
    $orders_count = $stmt->fetchColumn();
    
    if ($orders_count > 0) {
        $_SESSION['admin_error'] = "Невозможно удалить товар, так как на него есть заказы.";
        header("Location: admin.php?tab=products");
        exit;
    }
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmt->execute([$delete_id]);
    $images = $stmt->fetchAll();
    foreach ($images as $img) {
        $file = __DIR__ . '/assets/products/' . $img['image_url'];
        if (file_exists($file)) unlink($file);
    }
    $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$delete_id]);
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$delete_id]);
    
    $_SESSION['admin_success'] = "Товар удалён.";
    header("Location: admin.php?tab=products");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['add_product']) || isset($_POST['edit_product']))) {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $category = $_POST['category'];
    $price = (float)$_POST['price'];
    $color = trim($_POST['color']);
    $code = trim($_POST['code']);
    $material = trim($_POST['material']);
    $heel = trim($_POST['heel']);
    $sole = trim($_POST['sole']);
    $features = trim($_POST['features']);
    $description = trim($_POST['description']);
    $sizes = $_POST['sizes'] ?? [];
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if (empty($name) || empty($price) || empty($category) || empty($sizes)) {
        $error = "Заполните все обязательные поля (название, цена, категория, хотя бы один размер)";
    } else {
        $sizes_json = json_encode($sizes);
        if ($product_id) {
            $stmt = $pdo->prepare("UPDATE products SET name=?, brand=?, category=?, price=?, sizes=?, color=?, code=?, material=?, heel=?, sole=?, features=?, description=? WHERE id=?");
            $stmt->execute([$name, $brand, $category, $price, $sizes_json, $color, $code, $material, $heel, $sole, $features, $description, $product_id]);
            $success = "Товар обновлён!";
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (name, brand, category, price, sizes, color, code, material, heel, sole, features, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $brand, $category, $price, $sizes_json, $color, $code, $material, $heel, $sole, $features, $description]);
            $product_id = $pdo->lastInsertId();
            $success = "Товар добавлен!";
        }

        if (!empty($_FILES['images']['name'][0])) {
            $target_dir = "assets/products/";
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);
                    $target_file = $target_dir . $file_name;
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $stmt_img = $pdo->prepare("INSERT INTO product_images (product_id, image_url) VALUES (?, ?)");
                        $stmt_img->execute([$product_id, $file_name]);
                    }
                }
            }
            $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY id LIMIT 1");
            $stmt->execute([$product_id]);
            $first = $stmt->fetch();
            if ($first) {
                $pdo->prepare("UPDATE products SET image_url = ? WHERE id = ?")->execute([$first['image_url'], $product_id]);
            }
        }
        if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $img_id) {
                $img_id = (int)$img_id;
                $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE id = ?");
                $stmt->execute([$img_id]);
                $img = $stmt->fetch();
                if ($img) {
                    $file = __DIR__ . '/assets/products/' . $img['image_url'];
                    if (file_exists($file)) unlink($file);
                    $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$img_id]);
                }
            }
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM product_images WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $count = $stmt->fetchColumn();
        if ($count == 0) {
            $pdo->prepare("UPDATE products SET image_url = NULL WHERE id = ?")->execute([$product_id]);
        }

        header("Location: admin.php?tab=products&success=1");
        exit;
    }
}

$edit_product = null;
$edit_images = [];
if ($edit_id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_product = $stmt->fetch();
    if ($edit_product) {
        $stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY id");
        $stmt->execute([$edit_id]);
        $edit_images = $stmt->fetchAll();
    } else {
        $edit_id = 0;
    }
}

$orders = $pdo->query("
    SELECT o.*, u.username, p.name as product_name 
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN products p ON o.product_id = p.id
    ORDER BY o.id DESC
")->fetchAll();

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/admin.css">

<div class="breadcrumbs">
    <a href="index.php">ГЛАВНАЯ</a> / <span>АДМИНИСТРИРОВАНИЕ</span>
</div>

<div class="admin-page">
    <h1>Административная панель</h1>
    <div class="admin-tabs">
        <button class="tab-btn <?= (!isset($_GET['tab']) || $_GET['tab'] == 'orders') ? 'active' : '' ?>" onclick="location.href='?tab=orders'">Управление заказами</button>
        <button class="tab-btn <?= isset($_GET['tab']) && $_GET['tab'] == 'add' ? 'active' : '' ?>" onclick="location.href='?tab=add'">Добавить товар</button>
        <button class="tab-btn <?= isset($_GET['tab']) && $_GET['tab'] == 'products' ? 'active' : '' ?>" onclick="location.href='?tab=products'">Все товары</button>
    </div>
    <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'orders'): ?>
    <div class="tab-content active">
        <h2>Заказы</h2>
        <?php if (empty($orders)): ?>
            <p>Нет заказов.</p>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Товар</th>
                        <th>Размер</th>
                        <th>Email</th>
                        <th>Статус</th>
                        <th>Комментарий админа</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= $order['id'] ?></td>
                        <td><?= htmlspecialchars($order['username']) ?></td>
                        <td><?= htmlspecialchars($order['product_name']) ?></td>
                        <td><?= htmlspecialchars($order['size'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($order['email']) ?></td>
                        <td class="status-<?= $order['status'] ?>">
                            <?php 
                                if ($order['status'] == 'confirmed') echo 'Подтверждён';
                                elseif ($order['status'] == 'rejected') echo 'Отклонён';
                                else echo 'Ожидает';
                            ?>
                        </td>
                        <td><?= nl2br(htmlspecialchars($order['admin_comment'] ?? '')) ?></td>
                        <td>
                            <?php if ($order['status'] == 'pending'): ?>
                                <button class="btn small" onclick="openActionModal(<?= $order['id'] ?>, 'confirm')">Подтвердить</button>
                                <button class="btn small" onclick="openActionModal(<?= $order['id'] ?>, 'reject')">Отклонить</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ((isset($_GET['tab']) && $_GET['tab'] == 'add') || $edit_id): ?>
    <div class="tab-content active">
        <h2><?= $edit_id ? 'Редактировать товар' : 'Добавить новый товар' ?></h2>
        <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="product-form">
            <?php if ($edit_id): ?>
                <input type="hidden" name="product_id" value="<?= $edit_id ?>">
            <?php endif; ?>
            <div class="form-row">
                <input type="text" name="name" placeholder="Название товара *" value="<?= htmlspecialchars($edit_product['name'] ?? '') ?>" required>
                <input type="text" name="brand" placeholder="Бренд" value="<?= htmlspecialchars($edit_product['brand'] ?? '') ?>">
            </div>
            <div class="form-row">
                <select name="category" required>
                    <option value="">Категория *</option>
                    <option value="men" <?= (isset($edit_product) && $edit_product['category']=='men') ? 'selected' : '' ?>>Мужчинам</option>
                    <option value="women" <?= (isset($edit_product) && $edit_product['category']=='women') ? 'selected' : '' ?>>Женщинам</option>
                    <option value="unisex" <?= (isset($edit_product) && $edit_product['category']=='unisex') ? 'selected' : '' ?>>Унисекс</option>
                </select>
                <input type="number" step="0.01" name="price" placeholder="Цена *" value="<?= htmlspecialchars($edit_product['price'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <input type="text" name="color" placeholder="Цвет" value="<?= htmlspecialchars($edit_product['color'] ?? '') ?>">
                <input type="text" name="code" placeholder="Артикул (код)" value="<?= htmlspecialchars($edit_product['code'] ?? '') ?>">
            </div>
            <div class="form-row">
                <input type="text" name="material" placeholder="Материал" value="<?= htmlspecialchars($edit_product['material'] ?? '') ?>">
                <input type="text" name="heel" placeholder="Высота каблука" value="<?= htmlspecialchars($edit_product['heel'] ?? '') ?>">
            </div>
            <div class="form-row">
                <input type="text" name="sole" placeholder="Подошва" value="<?= htmlspecialchars($edit_product['sole'] ?? '') ?>">
            </div>
            <div class="form-row">
                <textarea name="features" placeholder="Особенности (каждая с новой строки)"><?= htmlspecialchars($edit_product['features'] ?? '') ?></textarea>
            </div>
            <div class="form-row">
                <textarea name="description" placeholder="Описание" rows="5"><?= htmlspecialchars($edit_product['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Размеры (выберите доступные):</label>
                <div class="sizes-grid">
                    <?php 
                    $all_sizes = ['34','34.5','35','35.5','36','36.5','37','37.5','38','38.5','39','39.5','40','40.5','41','41.5','42','42.5','43','43.5','44','44.5','45','45.5','46'];
                    $current_sizes = $edit_product ? json_decode($edit_product['sizes'], true) : [];
                    foreach ($all_sizes as $s): 
                    ?>
                    <label>
                        <input type="checkbox" name="sizes[]" value="<?= $s ?>" <?= in_array($s, $current_sizes) ? 'checked' : '' ?>> <?= $s ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php if ($edit_id && !empty($edit_images)): ?>
            <div class="form-group">
                <label>Текущие изображения (отметьте для удаления):</label>
                <div class="current-images">
                    <?php foreach ($edit_images as $img): ?>
                    <div class="image-check">
                        <label>
                            <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>"> Удалить
                        </label>
                        <img src="./assets/products/<?= htmlspecialchars($img['image_url']) ?>" alt="" style="max-width: 100px; max-height: 100px;">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Загрузить новые изображения (можно несколько):</label>
                <input type="file" name="images[]" multiple accept="image/*">
            </div>

            <button type="submit" name="<?= $edit_id ? 'edit_product' : 'add_product' ?>" class="btn"><?= $edit_id ? 'Сохранить изменения' : 'Добавить товар' ?></button>
            <?php if ($edit_id): ?>
                <a href="?tab=products" class="btn">Отмена</a>
            <?php endif; ?>
        </form>
    </div>
    <?php endif; ?>
    <?php if (isset($_GET['tab']) && $_GET['tab'] == 'products'): ?>
    <div class="tab-content active">
        <h2>Управление товарами</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if (empty($products)): ?>
            <p>Товаров нет. <a href="?tab=add">Добавить первый товар</a></p>
        <?php else: ?>
            <table class="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Изображение</th>
                        <th>Название</th>
                        <th>Бренд</th>
                        <th>Категория</th>
                        <th>Цена</th>
                        <th>Размеры</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td>
                            <?php if ($p['image_url']): ?>
                                <img src="./assets/products/<?= htmlspecialchars($p['image_url']) ?>" alt="" style="max-width: 50px;">
                            <?php else: ?>
                                Нет фото
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['brand']) ?></td>
                        <td><?= $p['category'] ?></td>
                        <td><?= number_format($p['price'], 0, '', ' ') ?> ₽</td>
                        <td><?= implode(', ', json_decode($p['sizes'], true) ?? []) ?></td>
                        <td>
                            <a href="?edit_id=<?= $p['id'] ?>" class="btn small">Редактировать</a>
                            <a href="?delete_id=<?= $p['id'] ?>" class="btn small" onclick="return confirm('Удалить товар? Это действие нельзя отменить.')">Удалить</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<div id="actionModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Подтверждение заказа</h2>
        <form method="POST" id="actionForm">
            <input type="hidden" name="order_id" id="orderId">
            <input type="hidden" name="action" id="orderAction">
            <div class="form-group">
                <label for="admin_comment">Комментарий (необязательно):</label>
                <textarea name="admin_comment" id="admin_comment" rows="4" placeholder="Причина отклонения или примечание..."></textarea>
            </div>
            <button type="submit" name="order_action" class="btn">Отправить</button>
        </form>
    </div>
</div>

<script>
function openActionModal(orderId, action) {
    document.getElementById('orderId').value = orderId;
    document.getElementById('orderAction').value = action;
    document.getElementById('modalTitle').innerText = action === 'confirm' ? 'Подтверждение заказа' : 'Отклонение заказа';
    document.getElementById('actionModal').style.display = 'block';
}
document.querySelector('.close').onclick = function() {
    document.getElementById('actionModal').style.display = 'none';
}
window.onclick = function(event) {
    if (event.target == document.getElementById('actionModal')) {
        document.getElementById('actionModal').style.display = 'none';
    }
}
</script>

<?php require 'includes/footer.php'; ?>