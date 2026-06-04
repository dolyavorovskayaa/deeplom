document.addEventListener('DOMContentLoaded', function() {
    console.log('product.js loaded');
    const modal = document.getElementById('details-modal');
    const link = document.getElementById('details-link');
    const closeBtn = document.querySelector('.close-modal');
    const closeLink = document.querySelector('.close-modal-link');

    if (!modal || !link) {
        console.error('Модальное окно или ссылка не найдены');
        return;
    }

    link.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('open modal');
        modal.classList.add('show');
    });

    function closeModal() {
        console.log('close modal');
        modal.classList.remove('show');
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (closeLink) closeLink.addEventListener('click', function(e) {
        e.preventDefault();
        closeModal();
    });

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
});