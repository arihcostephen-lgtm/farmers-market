document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.public-auth-card, .public-home-page .card, .public-home-page section > .container').forEach(function (element) {
        element.classList.add('public-reveal');
    });

    const revealObserver = new IntersectionObserver(function (entries, observer) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.public-reveal').forEach(function (element) {
        revealObserver.observe(element);
    });

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            button.innerHTML = showing ? '<i class="fa-solid fa-eye"></i>' : '<i class="fa-solid fa-eye-slash"></i>';
        });
    });

    const password = document.querySelector('[data-password="new"]');
    const confirmation = document.querySelector('[data-password="confirmation"]');
    if (password && confirmation) {
        const validatePasswords = function () {
            confirmation.setCustomValidity(password.value !== confirmation.value ? 'Passwords do not match.' : '');
        };
        password.addEventListener('input', validatePasswords);
        confirmation.addEventListener('input', validatePasswords);
    }
});
