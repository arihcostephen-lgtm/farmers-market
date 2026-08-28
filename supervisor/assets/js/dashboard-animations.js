/**
 * Modern Dashboard Animations & Interactions
 * Matches Admin Dashboard Design System
 */

(function() {
    'use strict';

    // ============================================
    // Intersection Observer for Animations
    // ============================================
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = `slideInUp 0.6s ease-out forwards`;
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Observe all cards and stats elements
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.card, .metric, .stats-card, .action-tile').forEach(el => {
            observer.observe(el);
        });
    });

    // ============================================
    // Sidebar Toggle
    // ============================================

    const toggleSidebarBtn = document.querySelector('.navbar-toggler');
    const sidebar = document.querySelector('.sidebar');

    if (toggleSidebarBtn && sidebar) {
        toggleSidebarBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : 'auto';
        });

        // Close sidebar when a link is clicked
        sidebar.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.remove('show');
                document.body.style.overflow = 'auto';
            });
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.navbar') && !event.target.closest('.sidebar')) {
                sidebar.classList.remove('show');
                document.body.style.overflow = 'auto';
            }
        });
    }

    // ============================================
    // Active Navigation Link Highlighting
    // ============================================

    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || href.endsWith(currentPage)) {
            link.classList.add('active');
        }
    });

    // ============================================
    // Smooth Scroll for Anchor Links
    // ============================================

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // ============================================
    // Number Counter Animation
    // ============================================

    const animateCounter = (element, target, duration = 2000) => {
        let current = 0;
        const increment = target / (duration / 16);
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target.toLocaleString();
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, 16);
    };

    const startCounters = () => {
        document.querySelectorAll('.stats-card h3, .metric h3').forEach(element => {
            const text = element.textContent.replace(/[^\d]/g, '');
            if (text) {
                const target = parseInt(text);
                element.style.animation = 'none';
                observer.observe(element.parentElement);
                
                // Start animation when visible
                const counterObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && !entry.target.dataset.animated) {
                            animateCounter(element, target);
                            entry.target.dataset.animated = 'true';
                            counterObserver.unobserve(entry.target);
                        }
                    });
                });
                counterObserver.observe(element.parentElement);
            }
        });
    };

    document.addEventListener('DOMContentLoaded', startCounters);

    // ============================================
    // Hover Effects for Stat Cards
    // ============================================

    document.querySelectorAll('.stats-card, .metric').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px) scale(1.02)';
        });

        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // ============================================
    // Table Row Hover Effects
    // ============================================

    document.querySelectorAll('.table tbody tr').forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.boxShadow = 'inset 0 0 10px rgba(16, 184, 130, 0.1)';
        });

        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
            this.style.boxShadow = 'none';
        });
    });

    // ============================================
    // Form Focus Effects
    // ============================================

    document.querySelectorAll('.form-control, .form-select').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });

    // ============================================
    // Tooltip Initialization (if Bootstrap tooltips are needed)
    // ============================================

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ============================================
    // Popover Initialization
    // ============================================

    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // ============================================
    // Loading State Handler
    // ============================================

    window.showLoading = function(element) {
        if (element) {
            element.classList.add('loading');
            element.style.opacity = '0.6';
            element.style.pointerEvents = 'none';
        }
    };

    window.hideLoading = function(element) {
        if (element) {
            element.classList.remove('loading');
            element.style.opacity = '1';
            element.style.pointerEvents = 'auto';
        }
    };

    // ============================================
    // Form Submission Handler
    // ============================================

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
            }
        });
    });

    // ============================================
    // Notification System
    // ============================================

    window.showNotification = function(message, type = 'success', duration = 4000) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed`;
        notification.style.cssText = `
            top: 80px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        `;
        notification.textContent = message;
        notification.setAttribute('role', 'alert');

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'slideOutRight 0.3s ease-in';
            setTimeout(() => notification.remove(), 300);
        }, duration);
    };

    // Add slideOutRight animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideOutRight {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(400px);
            }
        }
    `;
    document.head.appendChild(style);

    // ============================================
    // Scroll-to-Top Button
    // ============================================

    const scrollButton = document.createElement('button');
    scrollButton.id = 'scrollToTop';
    scrollButton.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
    scrollButton.className = 'btn btn-success rounded-circle';
    scrollButton.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        padding: 0;
        border-radius: 50%;
        display: none;
        z-index: 1000;
        box-shadow: 0 6px 16px rgba(16, 184, 130, 0.3);
        animation: floatUp 0.6s ease-out infinite;
    `;
    document.body.appendChild(scrollButton);

    window.addEventListener('scroll', () => {
        if (window.pageYOffset > 300) {
            scrollButton.style.display = 'flex';
            scrollButton.style.justifyContent = 'center';
            scrollButton.style.alignItems = 'center';
        } else {
            scrollButton.style.display = 'none';
        }
    });

    scrollButton.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ============================================
    // Keyboard Shortcuts
    // ============================================

    document.addEventListener('keydown', (e) => {
        // Alt + Home to go to dashboard
        if (e.altKey && e.key === 'Home') {
            window.location.href = 'dashboard.php';
        }
        // Escape to close sidebar on mobile
        if (e.key === 'Escape' && sidebar) {
            sidebar.classList.remove('show');
            document.body.style.overflow = 'auto';
        }
    });

    // ============================================
    // Lazy Loading for Images
    // ============================================

    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    observer.unobserve(img);
                }
            });
        });

        document.querySelectorAll('img[data-src]').forEach(img => imageObserver.observe(img));
    }

    // ============================================
    // Theme Toggle (Optional)
    // ============================================

    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.documentElement.classList.toggle('dark-theme');
            localStorage.setItem('theme', document.documentElement.classList.contains('dark-theme') ? 'dark' : 'light');
        });

        // Load saved theme
        if (localStorage.getItem('theme') === 'light') {
            document.documentElement.classList.remove('dark-theme');
        }
    }

    // ============================================
    // Table Export Function
    // ============================================

    window.exportTableToCSV = function(filename = 'export.csv') {
        const tables = document.querySelectorAll('.table');
        let csv = [];
        
        tables.forEach(table => {
            table.querySelectorAll('tr').forEach(row => {
                const rowData = [];
                row.querySelectorAll('th, td').forEach(cell => {
                    rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
                });
                csv.push(rowData.join(','));
            });
        });

        const csvContent = 'data:text/csv;charset=utf-8,' + csv.join('\n');
        const link = document.createElement('a');
        link.setAttribute('href', encodeURI(csvContent));
        link.setAttribute('download', filename);
        link.click();
    };

    // ============================================
    // Print Functionality
    // ============================================

    window.printDashboard = function() {
        window.print();
    };

    // ============================================
    // Data Refresh Handler
    // ============================================

    const refreshButton = document.querySelector('[data-refresh]');
    if (refreshButton) {
        refreshButton.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.style.animation = 'spin 1s linear infinite';
            
            // Refresh the page after a short delay
            setTimeout(() => {
                location.reload();
            }, 500);
        });
    }

    // Add spin animation
    const spinStyle = document.createElement('style');
    spinStyle.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(spinStyle);

    // ============================================
    // Performance Monitoring (Optional)
    // ============================================

    if (window.performance && window.performance.timing) {
        window.addEventListener('load', function() {
            const pageLoadTime = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
            console.log('Page load time: ' + pageLoadTime + 'ms');
        });
    }

})();
