document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const currentSection = params.get('do') || 'Manage';

    document.querySelectorAll('#sidebar-nav a[href*="do="]').forEach(function (link) {
        const target = new URL(link.href, window.location.href).searchParams.get('do');
        if (target === currentSection) {
            link.classList.add('active');
        }
    });

    const revealItems = document.querySelectorAll('.page-header, .card, .stat-card');
    revealItems.forEach(function (element, index) {
        element.classList.add('dashboard-reveal');
        element.style.transitionDelay = Math.min(index * 35, 280) + 'ms';
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries, currentObserver) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('.dashboard-reveal').forEach(function (element) {
            observer.observe(element);
        });
    } else {
        document.querySelectorAll('.dashboard-reveal').forEach(function (element) {
            element.classList.add('is-visible');
        });
    }

    document.querySelectorAll('.stat-card h3').forEach(function (counter) {
        const target = Number(counter.textContent.replace(/[^0-9.]/g, ''));
        if (!Number.isFinite(target) || target === 0 || target > 1000000) return;
        const decimals = counter.textContent.includes('.') ? 1 : 0;
        const start = performance.now();
        const duration = 700;
        const animate = function (now) {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            counter.textContent = (target * eased).toFixed(decimals);
            if (progress < 1) window.requestAnimationFrame(animate);
        };
        window.requestAnimationFrame(animate);
    });

    document.querySelectorAll('#sidebar-nav a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth < 769) {
                const sidebar = document.getElementById('sidebar');
                if (sidebar && window.bootstrap && bootstrap.Collapse) {
                    bootstrap.Collapse.getOrCreateInstance(sidebar).hide();
                }
            }
        });
    });

    const csvInput = document.getElementById('productsCsv');
    const uploadDropzone = document.querySelector('.upload-dropzone p');
    if (csvInput && uploadDropzone) {
        csvInput.addEventListener('change', function () {
            uploadDropzone.textContent = csvInput.files.length ? csvInput.files[0].name : 'Required columns: product_name, price, stock_quantity.';
        });
    }
});