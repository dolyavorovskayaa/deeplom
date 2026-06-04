<?php
require 'config/db.php';
session_start();
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
if (!$product_id) {
    $product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
}
if (!$product_id) {
    header("Location: index.php");
    exit;
}
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();
if (!$product) {
    header("Location: index.php");
    exit;
}
$sizes = [];
if (!empty($product['sizes'])) {
    $sizes = json_decode($product['sizes'], true);
    if (!is_array($sizes)) {
        $sizes = [];
    }
}
$first_name = $last_name = $patronymic = $phone = $email = '';
if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $first_name = $user['first_name'] ?? '';
    $last_name = $user['last_name'] ?? '';
    $patronymic = $user['patronymic'] ?? '';
    $phone = $user['phone'] ?? '';
    $email = $user['email'] ?? '';
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_order'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $patronymic = trim($_POST['patronymic']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $delivery_region = trim($_POST['delivery_region']);
    $delivery_method = trim($_POST['delivery_method']);
    $payment_method = trim($_POST['payment_method']);
    $comment = trim($_POST['comment']);
    $size = isset($_POST['size']) ? trim($_POST['size']) : ''; 
    if (empty($first_name) || empty($last_name) || empty($phone) || empty($email) || empty($delivery_region) || empty($delivery_method) || empty($payment_method) || empty($size)) {
        $error = "Заполните все обязательные поля, включая размер";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Некорректный email";
    } else {
        $user_id = isset($_SESSION['user']) ? $_SESSION['user']['id'] : null;
        $total_price = $product['price'];

        $stmt = $pdo->prepare("INSERT INTO orders (user_id, product_id, size, first_name, last_name, patronymic, phone, email, delivery_region, delivery_method, payment_method, comment, total_price, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        if ($stmt->execute([$user_id, $product_id, $size, $first_name, $last_name, $patronymic, $phone, $email, $delivery_region, $delivery_method, $payment_method, $comment, $total_price])) {
            $_SESSION['order_success'] = "Заказ №" . $pdo->lastInsertId() . " оформлен! Мы свяжемся с вами.";
            header("Location: index.php");
            exit;
        } else {
            $error = "Ошибка при оформлении заказа";
        }
    }
}
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/checkout.css">
<div class="breadcrumbs">
    <a href="index.php">ГЛАВНАЯ</a> / 
    <?php if ($product['category'] == 'men'): ?>
        <a href="catalog.php?category=men">МУЖЧИНАМ</a> / 
    <?php elseif ($product['category'] == 'women'): ?>
        <a href="catalog.php?category=women">ЖЕНЩИНАМ</a> / 
    <?php else: ?>
        <a href="catalog.php">КАТАЛОГ</a> / 
    <?php endif; ?>
    <?php if ($product['brand']): ?>
        <a href="catalog.php?brand=<?= urlencode($product['brand']) ?>"><?= htmlspecialchars($product['brand']) ?></a> / 
    <?php endif; ?>
    <a href="product.php?id=<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></a> / 
    <span>ОФОРМЛЕНИЕ</span>
</div>
<div class="checkout-page">
    <h1>Оформление заказа</h1>
    <div class="checkout-container">
        <div class="checkout-product">
            <img src="./assets/products/<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            <div>
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <p>Цвет: <?= htmlspecialchars($product['color']) ?></p>
                <div class="size-selector">
                    <label for="size">Выберите размер:</label>
                    <select name="size" id="size" required form="order-form">
                        <option value="">--</option>
                        <?php foreach ($sizes as $sizeOption): ?>
                        <option value="<?= htmlspecialchars($sizeOption) ?>" <?= (isset($_POST['size']) && $_POST['size']==$sizeOption) ? 'selected' : '' ?>><?= htmlspecialchars($sizeOption) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <p class="price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</p>
            </div>
        </div>
        <form method="POST" class="checkout-form" id="order-form">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <h2>Получатель</h2>
            <div class="form-row">
                <input type="text" name="last_name" placeholder="Фамилия *" value="<?= htmlspecialchars($last_name) ?>" required>
                <input type="text" name="first_name" placeholder="Имя *" value="<?= htmlspecialchars($first_name) ?>" required>
                <input type="text" name="patronymic" placeholder="Отчество" value="<?= htmlspecialchars($patronymic) ?>">
            </div>
            <h2>Контакты</h2>
            <div class="form-row">
                <input type="tel" name="phone" placeholder="Телефон *" value="<?= htmlspecialchars($phone) ?>" required>
                <input type="email" name="email" placeholder="Email *" value="<?= htmlspecialchars($email) ?>" required>
            </div>
            <h2>Доставка</h2>
            <div class="form-row">
                <input type="text" name="delivery_region" placeholder="Регион доставки *" required>
            </div>
            <div class="form-row">
                <select name="delivery_method" required>
                    <option value="">Способ доставки *</option>
                    <option value="Курьером">Курьером</option>
                    <option value="Самовывоз">Самовывоз</option>
                    <option value="Почта России">Почта России</option>
                </select>
            </div>
            <h2>Оплата</h2>
            <div class="form-row">
                <select name="payment_method" required>
                    <option value="">Способ оплаты *</option>
                    <option value="Картой онлайн">Картой онлайн</option>
                    <option value="При получении">При получении</option>
                </select>
            </div>
            <div class="form-row">
                <textarea name="comment" placeholder="Комментарий к заказу"></textarea>
            </div>
            <p class="notice">Из-за проблем с доставкой, оплата товара может быть затруднена. Оформите заявку без выбора оплаты, мы вам перезвоним и поможем с оплатой.</p>
            <button type="submit" name="submit_order" class="btn submit-btn">Оформить предзаказ</button>
        </form>
    </div>
</div>
<?php require 'includes/footer.php'; ?>