<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./css/nav-style.css">
    <link rel="stylesheet" href="./css/footer-style.css">
    <link rel="stylesheet" href="./css/style.css">
    <title>Магазин обуви</title>
</head>
<body>
<header class="header">
    <a href="./index.php"><img src="./assets/logo.png" alt="logo" class="logo"></a>
    <nav class="nav">
        <a href="catalog.php?category=men">МУЖЧИНАМ</a>
        <a href="catalog.php?category=women">ЖЕНЩИНАМ</a>
        <a href="catalog.php?sort=new">НОВАЯ КОЛЛЕКЦИЯ</a>
        <?php if (isset($_SESSION['user'])): ?>
            <?php if ($_SESSION['user']['is_admin']): ?>
                <a href="admin.php">АДМИНКА</a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>
    <div class="icons">
    <div class="search-container">
    <form action="catalog.php" method="GET" class="search-form">
        <input type="text" name="search" placeholder="Поиск..." autocomplete="off" id="search-input">
        <button type="submit" class="search-btn" id="search-toggle">
            <img src="./assets/lupa.png" alt="Поиск">
        </button>
    </form>
        <div id="search-suggestions" class="search-suggestions"></div>
    </div>

    <?php if (isset($_SESSION['user'])): ?>
        <div class="profile-icon-container">
            <a href="profile.php" class="profile-link">
                <img src="./assets/user.png" alt="profile" class="profile-img default-img">
                <img src="./assets/check.png" alt="logged in" class="profile-img hover-img">
            </a>
            <div class="profile-tooltip">
                <p>Вы вошли как <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong></p>
                <a href="profile.php">Профиль</a> | <a href="logout.php">Выйти</a>
            </div>
        </div>
    <?php else: ?>
        <a href="login.php"><img src="./assets/user.png" alt="profile"></a>
    <?php endif; ?>
</div>
    <button class="burger-btn" aria-label="Меню">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="mobile-menu">
        <nav>
            <a href="catalog.php?category=men">МУЖЧИНАМ</a>
            <a href="catalog.php?category=women">ЖЕНЩИНАМ</a>
            <a href="catalog.php?brand=Prada">ТУФЛИ НА ЗАКАЗ</a>
            <a href="catalog.php?search=limited">ПОДАРКИ</a>
            <a href="catalog.php?sort=new">НОВАЯ КОЛЛЕКЦИЯ</a>
            <?php if (isset($_SESSION['user'])): ?>
                <a href="profile.php">ЛИЧНЫЙ КАБИНЕТ</a>
                <a href="logout.php">ВЫЙТИ</a>
            <?php else: ?>
                <a href="login.php">ВХОД</a>
                <a href="register.php">РЕГИСТРАЦИЯ</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<script src="js/burger.js"></script>
<script src="js/search.js"></script>