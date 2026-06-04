<?php
require 'config/db.php';
session_start();
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $patronymic = trim($_POST['patronymic']);
    $phone = trim($_POST['phone']);
    if (empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name) || empty($phone)) {
        $error = "Все поля, кроме отчества, обязательны для заполнения";
    } elseif ($password !== $password_confirm) {
        $error = "Пароли не совпадают";
    } elseif (strlen($password) < 6) {
        $error = "Пароль должен содержать минимум 6 символов";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $error = "Пользователь с таким именем или email уже существует";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, patronymic, phone) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password, $first_name, $last_name, $patronymic, $phone])) {
                $success = "Регистрация успешна! Теперь вы можете войти.";
            } else {
                $error = "Ошибка при регистрации, попробуйте позже";
            }
        }
    }
}
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/auth.css">
<div class="auth-page">
    <div class="auth-container">
        <h1>Регистрация</h1>
        <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="auth-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <div class="form-group">
                <input type="text" name="username" placeholder="Логин *" required>
            </div>
            <div class="form-group">
                <input type="email" name="email" placeholder="Email *" required>
            </div>
            <div class="form-row">
                <input type="text" name="last_name" placeholder="Фамилия *" required>
                <input type="text" name="first_name" placeholder="Имя *" required>
                <input type="text" name="patronymic" placeholder="Отчество">
            </div>
            <div class="form-group">
                <input type="tel" name="phone" placeholder="Телефон *" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Пароль *" required>
            </div>
            <div class="form-group">
                <input type="password" name="password_confirm" placeholder="Повторите пароль *" required>
            </div>
            <button type="submit" name="register" class="auth-btn">Зарегистрироваться</button>
        </form>
        <p class="auth-link">Уже есть аккаунт? <a href="login.php">Войти</a></p>
    </div>
</div>
<?php require 'includes/footer.php'; ?>