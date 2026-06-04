<?php
require 'config/db.php';
session_start();

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 3");
$latest_products = $stmt->fetchAll();

$has_products = !empty($latest_products);
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/index.css">

<main class="main-home">
    <section class="hero">
        <h1>LUXURY RESALE<br>FOOTWEAR</h1>
        <p>Аутентичная обувь премиальных брендов</p>
    </section>
    <section class="brands-carousel">
        <a href="catalog.php" class="carousel-link">
            <div class="carousel-track">
                <img src="assets/brand.png" alt="Brands">
                <img src="assets/brand.png" alt="Brands">
            </div>
        </a>
    </section>
    <?php if ($has_products): ?>
    <section class="new-arrivals">
        <h2>НОВЫЕ ПОСТУПЛЕНИЯ</h2>
        <div class="products-grid">
            <?php foreach ($latest_products as $product): ?>
            <div class="product-card">
                <a href="product.php?id=<?= $product['id'] ?>" class="product-link">
                    <img src="./assets/products/<?= htmlspecialchars($product['image_url']) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>" 
                         class="product-image">
                    <div class="product-overlay">
                        <span class="product-name"><?= htmlspecialchars($product['name']) ?></span>
                    </div>
                </a>
                <div class="product-price">
                    <?= number_format($product['price'], 0, '', ' ') ?> ₽
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <section class="our-stores">
        <h2>НАШИ МАГАЗИНЫ</h2>
        <div class="stores-preview">
            <div class="store-card">
                <img src="assets/shop1.png" alt="Магазин в Москве">
                <h3>Москва</h3>
                <p>ул. Петровка, 2</p>
                <a href="stores.php" class="btn-store">Подробнее</a>
            </div>
            <div class="store-card">
                <img src="assets/shop2.png" alt="Магазин в Рязани">
                <h3>Рязань</h3>
                <p>Казанское шоссе, 24</p>
                <a href="stores.php" class="btn-store">Подробнее</a>
            </div>
        </div>
    </section>
</main>

<?php require 'includes/footer.php'; ?>