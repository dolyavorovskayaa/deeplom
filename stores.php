<?php
require 'config/db.php';
session_start();
$stores = $pdo->query("SELECT * FROM stores ORDER BY name")->fetchAll();
?>
<?php require 'includes/header.php'; ?>
<link rel="stylesheet" href="./css/stores.css">

<div class="breadcrumbs">
    <a href="index.php">ГЛАВНАЯ</a> / <span>РАСПОЛОЖЕНИЕ МАГАЗИНОВ</span>
</div>

<div class="stores-page">
    <h1>РАСПОЛОЖЕНИЕ МАГАЗИНОВ</h1>
    
    <div class="stores-container">
        <div class="stores-list">
            <?php foreach ($stores as $index => $store): ?>
                <div class="store-card" data-index="<?= $index ?>" data-lat="<?= $store['lat'] ?>" data-lng="<?= $store['lng'] ?>" data-name="<?= htmlspecialchars($store['name']) ?>">
                    <h3><?= htmlspecialchars($store['name']) ?></h3>
                    <p class="address"><?= htmlspecialchars($store['address']) ?></p>
                    <p class="phone"><?= htmlspecialchars($store['phone']) ?></p>
                    <button class="btn show-on-map">Посетить магазин</button>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="stores-map">
            <div id="map" style="width: 100%; height: 100%;"></div>
        </div>
    </div>
</div>
<script src="https://api-maps.yandex.ru/2.1/?apikey=dc874622-55d1-4156-bc6c-54ecf36e20c0&lang=ru_RU" type="text/javascript"></script>
<script>
ymaps.ready(init);

function init() {
    var myMap = new ymaps.Map("map", {
        center: [55.751244, 37.618423],
        zoom: 10,
        controls: ['zoomControl', 'fullscreenControl']
    });
    var placemarks = [];
    document.querySelectorAll('.store-card').forEach(function(card, index) {
        var lat = parseFloat(card.dataset.lat);
        var lng = parseFloat(card.dataset.lng);
        var name = card.dataset.name;

        var placemark = new ymaps.Placemark([lat, lng], {
            balloonContent: name,
            hintContent: name
        }, {
            preset: 'islands#darkGreenDotIcon'
        });

        myMap.geoObjects.add(placemark);
        placemarks.push(placemark);
    });
    document.querySelectorAll('.show-on-map').forEach(function(btn, index) {
        btn.addEventListener('click', function() {
            var coords = placemarks[index].geometry.getCoordinates();
            myMap.setCenter(coords, 15, {
                duration: 300
            });
            placemarks[index].balloon.open();
        });
    });
}
</script>

<?php require 'includes/footer.php'; ?>