/*===== MENU SHOW =====*/
const showMenu = (toggleId, navId) => {
    const toggle = document.getElementById(toggleId),
        nav = document.getElementById(navId)

    if (toggle && nav) {
        toggle.addEventListener('click', () => {
            nav.classList.toggle('show')
        })
    }
}
showMenu('nav-toggle', 'nav-menu')

/*===== ACTIVE AND REMOVE MENU =====*/
const navLink = document.querySelectorAll('.nav__link');

function linkAction() {
    /*Active link*/
    navLink.forEach(n => n.classList.remove('active'));
    this.classList.add('active');

    /*Remove menu mobile*/
    const navMenu = document.getElementById('nav-menu')
    navMenu.classList.remove('show')
}
navLink.forEach(n => n.addEventListener('click', linkAction));

/*===== SCROLL REVEAL ANIMATION =====*/
const sr = ScrollReveal({
    origin: 'top',
    distance: '80px',
    duration: 2000,
    reset: true
});

/*SCROLL HOME*/
sr.reveal('.home__title', {});
sr.reveal('.button', { delay: 200 });
sr.reveal('.home__img', { delay: 400 });
sr.reveal('.home__social-icon', { interval: 200 });

/*SCROLL ABOUT*/
sr.reveal('.about__img', {});
sr.reveal('.about__subtitle', { delay: 400 });
sr.reveal('.about__text', { delay: 400 });

/*SCROLL SKILLS*/
sr.reveal('.skills__subtitle', {});
sr.reveal('.skills__text', {});
sr.reveal('.skills__data', { interval: 200 });
sr.reveal('.skills__img', { delay: 600 });

/*SCROLL WORK*/
sr.reveal('.work__img', { interval: 200 });

/*SCROLL CONTACT*/
sr.reveal('.contact__input', { interval: 200 });

/*===== FEEDBACK FORM SUBMISSION =====*/
document.getElementById('feedbackForm').addEventListener('submit', function (e) {
    e.preventDefault(); // Отменяем стандартное поведение формы

    let formData = new FormData(this);
    let xhr = new XMLHttpRequest();

    xhr.open('POST', '/assets/php/submit-feedback.php', true);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            let response = JSON.parse(xhr.responseText); // Разбираем JSON-ответ

            console.log(response);
            // Проверяем успешность отправки
            let notification = document.getElementById('notification');
            let notificationMessage = document.getElementById('notificationMessage');

            notificationMessage.textContent = decodeURIComponent(response.message); // Декодируем текст для корректного отображения
            notification.className = 'notification'; // сбрасываем предыдущие классы

            // В зависимости от статуса ответа, изменяем стиль уведомления
            if (response.status === 'error') {
                notification.classList.add('error');
            }

            notification.style.display = 'flex'; // Показываем уведомление с использованием flexbox

            // Скрытие уведомления через 3 секунды
            setTimeout(function () {
                notification.style.animation = 'fadeOut 1s forwards'; // Применяем анимацию исчезновения
                setTimeout(function () {
                    notification.style.display = 'none'; // Прячем уведомление по завершению анимации
                }, 1000); // Время, совпадающее с продолжительностью анимации
            }, 2500);
        }
    };

    xhr.send(formData);

});
