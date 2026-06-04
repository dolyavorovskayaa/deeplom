<?php http_response_code(404); ?>
<?php require 'includes/header.php'; ?>
<style>
    .error-page {
        max-width: 600px;
        margin: 100px auto;
        text-align: center;
        font-family: 'Montserrat', sans-serif;
        padding: 0 20px;
    }
    .error-page h1 {
        font-size: 120px;
        font-weight: 300;
        margin: 0;
        line-height: 1;
        color: #000;
    }
    .error-page p {
        font-size: 18px;
        color: #555;
        margin: 20px 0 40px;
    }
    .error-actions {
        display: flex;
        gap: 20px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .error-actions a {
        display: inline-block;
        padding: 12px 30px;
        border: 1px solid #000;
        background: none;
        color: #000;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s;
    }
    .error-actions a:hover {
        background: #000;
        color: #fff;
    }
    @media (max-width: 480px) {
        .error-page h1 {
            font-size: 80px;
        }
        .error-page p {
            font-size: 16px;
        }
    }
</style>
<div class="error-page">
    <h1>404</h1>
    <p>Страница не найдена</p>
    <div class="error-actions">
        <a href="index.php">На главную</a>
        <a href="catalog.php">В каталог</a>
    </div>
</div>
<?php require 'includes/footer.php'; ?>