const burgerBtn = document.querySelector('.burger-btn');
const mobileMenu = document.querySelector('.mobile-menu');

burgerBtn.addEventListener('click', () => {
    mobileMenu.classList.toggle('active');
    burgerBtn.classList.toggle('active');
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.burger-btn') && !e.target.closest('.mobile-menu')) {
        mobileMenu.classList.remove('active');
        burgerBtn.classList.remove('active');
    }
});