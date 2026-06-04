<style>
    #details-modal { display: none !important; }
    #details-modal.show { display: flex !important; }
</style>
<?php
require 'config/db.php';
session_start();
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    header("HTTP/1.0 404 Not Found");
    include '404.php';
    exit;
}
$is_favorite = false;
if (isset($_SESSION['user'])) {
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['user']['id'], $product_id]);
    $is_favorite = $stmt->fetch() ? true : false;
}
$stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY sort_order");
$stmt->execute([$product_id]);
$images = $stmt->fetchAll();
if (empty($images)) {
    $images = [['image_url' => $product['image_url']]];
}
$sizes = [];
if (!empty($product['sizes'])) {
    $sizes = json_decode($product['sizes'], true);
    if (!is_array($sizes)) {
        $sizes = [];
    }
}
function getBreadcrumbs($product) {
    $crumbs = [];
    $crumbs[] = '<a href="index.php">ГЛАВНАЯ</a>';
    if ($product['category'] == 'men') {
        $crumbs[] = '<a href="catalog.php?category=men">МУЖЧИНАМ</a>';
    } elseif ($product['category'] == 'women') {
        $crumbs[] = '<a href="catalog.php?category=women">ЖЕНЩИНАМ</a>';
    } else {
        $crumbs[] = '<a href="catalog.php">КАТАЛОГ</a>';
    }
    if ($product['brand']) {
        $crumbs[] = '<a href="catalog.php?brand=' . urlencode($product['brand']) . '">' . htmlspecialchars($product['brand']) . '</a>';
    }
    $crumbs[] = '<span>' . htmlspecialchars($product['name']) . '</span>';
    return implode(' / ', $crumbs);
}
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/product.css">
<div class="breadcrumbs">
    <?= getBreadcrumbs($product) ?>
</div>

<div class="product-page">
    <div class="product-container">
        <div class="product-gallery">
            <?php foreach ($images as $img): ?>
                <img src="./assets/products/<?= htmlspecialchars($img['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <?php endforeach; ?>
        </div>
        <div class="product-info-right">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</p>
            <p class="product-color">Цвет: <?= htmlspecialchars($product['color']) ?></p>
            <form action="checkout.php" method="POST" class="order-form">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                
                <?php if (!empty($sizes)): ?>
                <div class="size-selector">
                    <label for="size">Выберите размер:</label>
                    <select name="size" id="size" required>
                        <option value="">--</option>
                        <?php foreach ($sizes as $size): ?>
                        <option value="<?= htmlspecialchars($size) ?>"><?= htmlspecialchars($size) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <button type="submit" class="btn preorder-btn">Оформить предзаказ</button>
            </form>

            <?php if (isset($_SESSION['user'])): ?>
                <form method="POST" action="toggle_favorite.php" class="favorite-form">
                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                    <button type="submit" name="toggle_favorite" class="favorite-btn <?= $is_favorite ? 'active' : '' ?>">♡</button>
                </form>
            <?php else: ?>
                <a href="login.php" class="favorite-btn">♡</a>
            <?php endif; ?>
            
            <div class="product-details-link">
                <a href="#" id="details-link">Детали продукта</a>
            </div>
        </div>
    </div>
</div>
<div id="details-modal" class="modal">
    <div class="modal-content">
        <span class="close-modal">&times;</span>
        <h2>Подробная информация о продукте</h2>
        <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
        <ul class="product-specs">
            <?php if (!empty($product['code'])): ?>
                <li><strong>Код товара:</strong> <?= htmlspecialchars($product['code']) ?></li>
            <?php endif; ?>
            <?php if (!empty($product['material'])): ?>
                <li><strong>Материал:</strong> <?= htmlspecialchars($product['material']) ?></li>
            <?php endif; ?>
            <?php if (!empty($product['heel'])): ?>
                <li><strong>Высота каблука:</strong> <?= htmlspecialchars($product['heel']) ?></li>
            <?php endif; ?>
            <?php if (!empty($product['sole'])): ?>
                <li><strong>Подошва:</strong> <?= htmlspecialchars($product['sole']) ?></li>
            <?php endif; ?>
            <?php if (!empty($product['features'])): ?>
                <li><strong>Особенности:</strong> <?= nl2br(htmlspecialchars($product['features'])) ?></li>
            <?php endif; ?>
        </ul>
        <p><a href="#" class="close-modal-link">Закрыть окно</a></p>
    </div>
</div>
<script src="js/product.js"></script>
<?php require 'includes/footer.php'; ?>