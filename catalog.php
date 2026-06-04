<?php
require 'config/db.php';
session_start();
$category = isset($_GET['category']) ? $_GET['category'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];
if ($category && in_array($category, ['men', 'women'])) {
    $sql .= " AND (category = ? OR category = 'unisex')";
    $params[] = $category;
}
if ($brand) {
    $sql .= " AND brand = ?";
    $params[] = $brand;
}
if ($search) {
    $sql .= " AND (name LIKE ? OR description LIKE ? OR brand LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}
$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$brands = $pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand != '' ORDER BY brand")->fetchAll(PDO::FETCH_COLUMN);
$user_favorites = [];
if (isset($_SESSION['user'])) {
    $stmt = $pdo->prepare("SELECT product_id FROM favorites WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user_favorites = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/catalog.css">

<div class="breadcrumbs">
    <a href="index.php">ГЛАВНАЯ</a>
    <?php if ($category): ?>
        / <span><?= $category == 'men' ? 'МУЖЧИНАМ' : 'ЖЕНЩИНАМ' ?></span>
    <?php elseif ($brand): ?>
        / <span><?= htmlspecialchars($brand) ?></span>
    <?php elseif ($search): ?>
        / <span>ПОИСК: <?= htmlspecialchars($search) ?></span>
    <?php else: ?>
        / <span>КАТАЛОГ</span>
    <?php endif; ?>
</div>

<div class="catalog-page">
    <div class="catalog-header">
        <h1>
            <?php if ($category): ?>
                <?= $category == 'men' ? 'МУЖЧИНАМ' : 'ЖЕНЩИНАМ' ?>
            <?php elseif ($brand): ?>
                <?= htmlspecialchars($brand) ?>
            <?php elseif ($search): ?>
                РЕЗУЛЬТАТЫ ПОИСКА: "<?= htmlspecialchars($search) ?>"
            <?php else: ?>
                ВСЕ ТОВАРЫ
            <?php endif; ?>
        </h1>
        <p class="results-count"><?= count($products) ?> РЕЗУЛЬТАТОВ</p>
    </div>

    <div class="catalog-container">
        <div class="filters">
            <h3>ФИЛЬТРЫ</h3>
            <?php if (empty($brands)): ?>
                <p>Нет брендов</p>
            <?php else: ?>
                <div class="filter-group">
                    <h4>Бренд</h4>
                    <?php foreach ($brands as $b): ?>
                        <label>
                            <input type="radio" name="brand" value="<?= htmlspecialchars($b) ?>" <?= $brand == $b ? 'checked' : '' ?> onchange="window.location.href='?<?= http_build_query(array_merge($_GET, ['brand' => $b, 'page' => null])) ?>'">
                            <?= htmlspecialchars($b) ?>
                        </label>
                    <?php endforeach; ?>
                    <?php if ($brand): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['brand' => null, 'page' => null])) ?>" class="clear-filter">Сбросить</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="product.php?id=<?= $product['id'] ?>" class="product-link">
                        <img src="./assets/products/<?= htmlspecialchars($product['image_url']) ?>" 
                            alt="<?= htmlspecialchars($product['name']) ?>" 
                            class="product-image">
                        <div class="product-overlay">
                            <span class="product-name"><?= htmlspecialchars($product['name']) ?></span>
                        </div>
                    </a>

                    <?php if (isset($_SESSION['user'])): ?>
                        <form method="POST" action="toggle_favorite.php" class="favorite-form">
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                            <button type="submit" name="toggle_favorite" class="favorite-btn <?= in_array($product['id'], $user_favorites) ? 'active' : '' ?>">
                                ♡
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="login.php" class="favorite-form">
                            <button class="favorite-btn">♡</button>
                        </a>
                    <?php endif; ?>

                    <div class="product-price">
                        <?= number_format($product['price'], 0, '', ' ') ?> ₽
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require 'includes/footer.php'; ?>