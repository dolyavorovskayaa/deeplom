<?php
require 'config/db.php';
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    if ($username === '' || $password === '') {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: index.php");
            exit;
        } else {
            $error = "Неверный логин или пароль";
        }
    }
}
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/auth.css">
<div class="auth-page">
    <div class="auth-container">
        <h1>Вход</h1>
        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <div class="form-group">
                <input type="text" name="username" placeholder="Логин" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Пароль" required>
            </div>
            <button type="submit" name="login" class="auth-btn">Войти</button>
        </form>
        <p class="auth-link">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
    </div>
</div>
<?php require 'includes/footer.php'; ?>