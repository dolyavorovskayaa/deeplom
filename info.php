<?php
require 'config/db.php';
session_start();

$section = isset($_GET['section']) ? $_GET['section'] : 'about';
$contact_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_contact'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    $_SESSION['contact_success'] = 'Спасибо за обращение! Мы ответим вам в ближайшее время.';
    header("Location: info.php?section=contacts");
    exit;
}
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/info.css">

<div class="breadcrumbs">
    <a href="index.php">ГЛАВНАЯ</a> / <span><?php
        $titles = [
            'about' => 'О НАС',
            'history' => 'ИСТОРИЯ',
            'sustainability' => 'УСТОЙЧИВОЕ РАЗВИТИЕ',
            'contacts' => 'КОНТАКТЫ'
        ];
        echo $titles[$section] ?? 'ИНФОРМАЦИЯ';
    ?></span>
</div>

<div class="info-page">
    <?php if ($section == 'about'): ?>
        <h1>О НАС</h1>
        <div class="about-grid">
            <div class="about-block">
                <h2>МИССИЯ</h2>
                <p>Сделать уникальную обувь доступной каждому ценителю, создавая безопасное и доверительное пространство для ресӗлла люксовых брендов.</p>
            </div>
            <div class="about-block">
                <h2>ЦЕННОСТИ</h2>
                <ul>
                    <li>Аутентичность — каждая пара проверена экспертами</li>
                    <li>Осознанность — продлеваем жизнь вещам</li>
                    <li>Сообщество — объединяем продавцов и покупателей</li>
                </ul>
            </div>
            <div class="about-block">
                <h2>КОМАНДА</h2>
                <p>Мы — небольшая команда энтузиастов, коллекционеров и экспертов по люксовой моде. Нам важно, чтобы каждая сделка приносила радость и уверенность.</p>
            </div>
        </div>

    <?php elseif ($section == 'history'): ?>
        <h1>ИСТОРИЯ ПЛАТФОРМЫ</h1>
        <div class="history-content">
            <p>Всё началось с любви к качественной обуви и желания сделать редкие модели доступнее. Основатели проекта, коллекционеры и ценители, заметили, что многие люксовые пары пылятся в шкафах, в то время как другие мечтают их найти. Так в 2020 году родилась идея создать пространство, где можно безопасно покупать и продавать премиальную обувь.</p>
            <p>За первые два года мы объединили сотни продавцов и тысячи покупателей. Сегодня наша платформа — это не просто доска объявлений, а курируемое сообщество, где каждая пара проходит аутентификацию, а продавцы получают поддержку на всех этапах.</p>
            <p>Мы гордимся тем, что помогаем продлевать жизнь качественным вещам и делаем мир моды чуть более осознанным.</p>
        </div>

    <?php elseif ($section == 'sustainability'): ?>
        <h1>УСТОЙЧИВОЕ РАЗВИТИЕ</h1>
        <div class="faq-list">
            <div class="faq-item">
                <div class="faq-question">
                    <span>ЧЕМ ОТЛИЧАЕТСЯ ВАША ПЛАТФОРМА ОТ ОБЫЧНЫХ МАГАЗИНОВ?</span>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Мы не производим обувь — мы помогаем находить редкие, лимитированные и винтажные модели от ведущих люксовых брендов. Наша цель — дать вторую жизнь качественной обуви и сделать уникальные вещи доступными для ценителей.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>КАК ВЫ ГАРАНТИРУЕТЕ ПОДЛИННОСТЬ ТОВАРОВ?</span>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Каждая пара перед публикацией проходит многоступенчатую проверку экспертами. Мы сотрудничаем с независимыми аутентификаторами, которые подтверждают оригинальность моделей. Вы получаете товар, прошедший строгий контроль.</p>
                </div>
            </div>
            <div class="faq-item">
                <div class="faq-question">
                    <span>КАК СТАТЬ ПРОДАВЦОМ НА ПЛАТФОРМЕ?</span>
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <p>Если у вас есть оригинальная люксовая обувь в отличном состоянии, вы можете предложить её нашим покупателям. Заполните форму в разделе «Стать продавцом», наши менеджеры свяжутся с вами для оценки и помогут разместить товар.</p>
                </div>
            </div>
        </div>

    <?php elseif ($section == 'contacts'): ?>
        <h1>КОНТАКТЫ</h1>
        <div class="contacts-container">
            <div class="contacts-info">
                <h2>СВЯЖИТЕСЬ С НАМИ</h2>
                <div class="contact-block">
                    <h3>Email</h3>
                    <p><a href="mailto:support@yourplatform.ru">support@yourplatform.ru</a></p>
                </div>
                <div class="contact-block">
                    <h3>Телефон</h3>
                    <p>+7 (495) 123-45-67</p>
                </div>
                <div class="contact-block">
                    <h3>Социальные сети</h3>
                    <div class="social-links">
                        <a href="#" target="_blank">Instagram</a>
                        <a href="#" target="_blank">Telegram</a>
                        <a href="#" target="_blank">VK</a>
                    </div>
                </div>
                <div class="contact-block">
                    <h3>Адрес офиса</h3>
                    <p>Москва, ул. Тверская, 15 (только по предварительной договорённости)</p>
                </div>
            </div>
            
            <div class="contacts-form">
                <h2>НАПИШИТЕ НАМ</h2>
                <?php if (isset($_SESSION['contact_success'])): ?>
                    <div class="success"><?= $_SESSION['contact_success'] ?></div>
                    <?php unset($_SESSION['contact_success']); ?>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="name" placeholder="Ваше имя" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <textarea name="message" placeholder="Сообщение" rows="5" required></textarea>
                    <button type="submit" name="send_contact" class="btn">Отправить</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($section == 'sustainability'): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });
});
</script>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>