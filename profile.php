<?php
require 'config/db.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user']['id'];
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'orders';
$orders = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.image_url, p.price
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.id DESC
");
$orders->execute([$user_id]);
$orders = $orders->fetchAll();
$favorites = $pdo->prepare("
    SELECT p.*, f.id as fav_id
    FROM favorites f
    JOIN products p ON f.product_id = p.id
    WHERE f.user_id = ?
");
$favorites->execute([$user_id]);
$favorites = $favorites->fetchAll();
$update_success = '';
$update_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $patronymic = trim($_POST['patronymic']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $update_error = "Пользователь с таким email уже существует";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, patronymic = ?, phone = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$first_name, $last_name, $patronymic, $phone, $email, $user_id])) {
            $_SESSION['user']['first_name'] = $first_name;
            $_SESSION['user']['last_name'] = $last_name;
            $_SESSION['user']['patronymic'] = $patronymic;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['email'] = $email;
            $update_success = "Данные успешно обновлены";
        } else {
            $update_error = "Ошибка при обновлении";
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!password_verify($current, $user['password'])) {
        $update_error = "Неверный текущий пароль";
    } elseif ($new !== $confirm) {
        $update_error = "Новые пароли не совпадают";
    } elseif (strlen($new) < 6) {
        $update_error = "Новый пароль должен быть не менее 6 символов";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($stmt->execute([$hashed, $user_id])) {
            $update_success = "Пароль успешно изменен";
        } else {
            $update_error = "Ошибка при смене пароля";
        }
    }
}
if (isset($_GET['remove_fav'])) {
    $fav_id = (int)$_GET['remove_fav'];
    $stmt = $pdo->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
    $stmt->execute([$fav_id, $user_id]);
    header("Location: profile.php?tab=favorites");
    exit;
}
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/profile.css">
<div class="profile-page">
    <h1>Личный кабинет</h1>
    <div class="profile-tabs">
        <a href="?tab=orders" class="<?= $tab == 'orders' ? 'active' : '' ?>">Мои заказы</a>
        <a href="?tab=favorites" class="<?= $tab == 'favorites' ? 'active' : '' ?>">Избранное</a>
        <a href="?tab=edit" class="<?= $tab == 'edit' ? 'active' : '' ?>">Редактировать профиль</a>
    </div>
    <div class="profile-content">
        <?php if ($tab == 'orders'): ?>
            <h2>История заказов</h2>
            <?php if (empty($orders)): ?>
                <p>У вас пока нет заказов. <a href="index.php">Перейти в каталог</a></p>
            <?php else: ?>
                <div class="orders-list">
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-img">
                                <img src="./assets/products/<?= htmlspecialchars($order['image_url']) ?>" alt="">
                            </div>
                            <div class="order-info">
                                <h3><?= htmlspecialchars($order['product_name']) ?></h3>
                                <p>Цена: <?= number_format($order['price'], 0, '', ' ') ?> ₽</p>
                                <p>Размер: <?= htmlspecialchars($order['size'] ?? '-') ?></p>
                                <p>Статус: <span class="status-<?= $order['status'] ?>">
                                    <?php 
                                        if ($order['status'] == 'confirmed') echo 'Подтвержден';
                                        elseif ($order['status'] == 'rejected') echo 'Отклонен';
                                        else echo 'В обработке';
                                    ?>
                                </span></p>
                                <p>Дата заказа: <?= date('d.m.Y', strtotime($order['created_at'] ?? 'now')) ?></p>
                                <?php if (!empty($order['admin_comment'])): ?>
                                    <div class="admin-comment">
                                        <strong>Комментарий администратора:</strong>
                                        <p><?= nl2br(htmlspecialchars($order['admin_comment'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php elseif ($tab == 'favorites'): ?>
            <h2>Избранные товары</h2>
            <?php if (empty($favorites)): ?>
                <p>В избранном пока пусто. <a href="index.php">Добавить товары</a></p>
            <?php else: ?>
                <div class="favorites-grid">
                    <?php foreach ($favorites as $fav): ?>
                        <div class="favorite-card">
                            <a href="product.php?id=<?= $fav['id'] ?>">
                                <img src="./assets/products/<?= htmlspecialchars($fav['image_url']) ?>" alt="">
                                <h4><?= htmlspecialchars($fav['name']) ?></h4>
                                <p class="price"><?= number_format($fav['price'], 0, '', ' ') ?> ₽</p>
                            </a>
                            <a href="?remove_fav=<?= $fav['fav_id'] ?>" class="remove-fav" onclick="return confirm('Убрать из избранного?')">×</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>  
        <?php elseif ($tab == 'edit'): ?>
            <h2>Редактирование профиля</h2>
            <?php if ($update_success): ?>
                <div class="success"><?= htmlspecialchars($update_success) ?></div>
            <?php endif; ?>
            <?php if ($update_error): ?>
                <div class="error"><?= htmlspecialchars($update_error) ?></div>
            <?php endif; ?>
            <form method="POST" class="profile-form">
                <div class="form-row">
                    <input type="text" name="last_name" placeholder="Фамилия" value="<?= htmlspecialchars($_SESSION['user']['last_name'] ?? '') ?>" required>
                    <input type="text" name="first_name" placeholder="Имя" value="<?= htmlspecialchars($_SESSION['user']['first_name'] ?? '') ?>" required>
                    <input type="text" name="patronymic" placeholder="Отчество" value="<?= htmlspecialchars($_SESSION['user']['patronymic'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <input type="tel" name="phone" placeholder="Телефон" value="<?= htmlspecialchars($_SESSION['user']['phone'] ?? '') ?>" required>
                    <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" required>
                </div>
                <button type="submit" name="update_profile" class="btn">Сохранить изменения</button>
            </form>
            <h3>Смена пароля</h3>
            <form method="POST" class="profile-form">
                <div class="form-row">
                    <input type="password" name="current_password" placeholder="Текущий пароль" required>
                </div>
                <div class="form-row">
                    <input type="password" name="new_password" placeholder="Новый пароль" required>
                    <input type="password" name="confirm_password" placeholder="Подтвердите новый пароль" required>
                </div>
                <button type="submit" name="change_password" class="btn">Изменить пароль</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php require 'includes/footer.php'; ?>