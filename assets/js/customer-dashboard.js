document.addEventListener('DOMContentLoaded', function () {
    const dashboard = document.querySelector('.customer-dashboard');
    if (!dashboard) return;

    const sections = Array.from(dashboard.querySelectorAll('main section[id]'));
    const navigationLinks = Array.from(dashboard.querySelectorAll('.dashboard-sidebar .nav-link[href^="#"]'));
    const showSection = function (sectionId, updateHash) {
        const target = document.getElementById(sectionId) || document.getElementById('overview');
        sections.forEach(function (section) { section.classList.toggle('dashboard-section-active', section === target); });
        navigationLinks.forEach(function (link) { link.classList.toggle('active', link.getAttribute('href') === '#' + target.id); });
        if (updateHash && window.location.hash !== '#' + target.id) history.pushState(null, '', '#' + target.id);
        target.querySelectorAll('.dashboard-reveal').forEach(function (element) { element.classList.remove('is-visible'); });
        window.setTimeout(function () { target.querySelectorAll('.dashboard-reveal').forEach(function (element) { element.classList.add('is-visible'); }); }, 20);
    };
    showSection(window.location.hash.substring(1) || 'overview', false);
    navigationLinks.forEach(function (link) { link.addEventListener('click', function (event) { event.preventDefault(); showSection(link.getAttribute('href').substring(1), true); }); });
    dashboard.querySelectorAll('a[href^="#"]').forEach(function (link) { link.addEventListener('click', function (event) { const targetId = link.getAttribute('href').substring(1); if (document.getElementById(targetId)) { event.preventDefault(); showSection(targetId, true); } }); });
    window.addEventListener('popstate', function () { showSection(window.location.hash.substring(1) || 'overview', false); });
    window.addEventListener('hashchange', function () { showSection(window.location.hash.substring(1) || 'overview', false); });

    dashboard.querySelectorAll('.dashboard-card, .product-card, .page-header').forEach(function (element, index) {
        element.classList.add('dashboard-reveal');
        element.style.transitionDelay = Math.min(index * 35, 280) + 'ms';
    });
    const revealItems = dashboard.querySelectorAll('.dashboard-reveal');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries, currentObserver) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08 });
        revealItems.forEach(function (element) { observer.observe(element); });
    } else {
        revealItems.forEach(function (element) { element.classList.add('is-visible'); });
    }

    dashboard.querySelectorAll('.metric h3').forEach(function (counter) {
        const target = Number(counter.textContent.replace(/[^0-9.]/g, ''));
        if (!Number.isFinite(target) || target === 0 || target > 1000000) return;
        const start = performance.now();
        const animate = function (now) {
            const progress = Math.min((now - start) / 700, 1);
            counter.textContent = Math.floor(target * (1 - Math.pow(1 - progress, 3))).toLocaleString();
            if (progress < 1) window.requestAnimationFrame(animate);
        };
        window.requestAnimationFrame(animate);
    });

    const orderForm = document.getElementById('dashboardOrderForm');
    const quantityInput = document.getElementById('dashboardOrderQuantity');
    const subtotalOutput = document.getElementById('dashboardOrderSubtotal');
    const taxOutput = document.getElementById('dashboardOrderTax');
    const totalOutput = document.getElementById('dashboardOrderTotal');
    if (orderForm && quantityInput) {
        const price = Number(orderForm.dataset.price || 0);
        const taxRules = JSON.parse(orderForm.dataset.taxRules || '[]');
        const formatMoney = value => value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const updateOrderTotals = function () {
            const quantity = Math.max(1, Math.min(Number(quantityInput.value) || 1, Number(quantityInput.max) || Number.MAX_SAFE_INTEGER));
            quantityInput.value = quantity;
            const subtotal = price * quantity;
            const matchingRule = taxRules.find(rule => Number(rule.min) <= quantity && (rule.max === null || Number(rule.max) >= quantity));
            const taxRate = matchingRule ? Number(matchingRule.rate) : 0;
            const tax = subtotal * taxRate / 100;
            subtotalOutput.textContent = formatMoney(subtotal);
            taxOutput.textContent = formatMoney(tax);
            totalOutput.textContent = formatMoney(subtotal + tax);
        };
        quantityInput.addEventListener('input', updateOrderTotals);
        updateOrderTotals();
        if (window.location.hash === '#place-order') document.getElementById('place-order')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});
