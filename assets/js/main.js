// NikahConnect — Main JavaScript

document.addEventListener('DOMContentLoaded', function() {

    // Animate elements on scroll
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-in').forEach(function(el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(el);
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        document.querySelectorAll('.alert').forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        });
    }, 5000);

    // Profile card hover effect
    document.querySelectorAll('.profile-card').forEach(function(card) {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });

    // Form validation feedback
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.dataset.original) {
                btn.dataset.original = btn.innerHTML;
                btn.innerHTML = '<span class="spinner"></span> Processing...';
                btn.style.opacity = '0.8';
                btn.disabled = true;
                // Re-enable after 5s in case of error
                setTimeout(function() {
                    btn.innerHTML = btn.dataset.original;
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    delete btn.dataset.original;
                }, 5000);
            }
        });
    });

    // Hamburger menu
    const hamburger = document.querySelector('.hamburger');
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            const navLinks = document.querySelector('.nav-links');
            if (navLinks) {
                navLinks.style.display = navLinks.style.display === 'flex' ? 'none' : 'flex';
                navLinks.style.flexDirection = 'column';
                navLinks.style.position = 'absolute';
                navLinks.style.top = '70px';
                navLinks.style.left = '0';
                navLinks.style.right = '0';
                navLinks.style.background = 'white';
                navLinks.style.padding = '10px 20px 20px';
                navLinks.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
                navLinks.style.zIndex = '1000';
            }
        });
    }

    // Char counter for textareas
    document.querySelectorAll('textarea[maxlength]').forEach(function(ta) {
        const max = parseInt(ta.getAttribute('maxlength'));
        const counter = document.createElement('div');
        counter.style.cssText = 'font-size:11px;color:var(--text-light);text-align:right;margin-top:3px';
        counter.textContent = '0 / ' + max;
        ta.parentNode.insertBefore(counter, ta.nextSibling);
        ta.addEventListener('input', function() {
            const len = this.value.length;
            counter.textContent = len + ' / ' + max;
            counter.style.color = len > max * 0.9 ? '#e74c3c' : 'var(--text-light)';
        });
    });

    // Confirm dialogs already on links with onclick
    // Profile completion progress animation
    const fills = document.querySelectorAll('.progress-fill');
    fills.forEach(function(fill) {
        const width = fill.style.width;
        fill.style.width = '0%';
        setTimeout(function() {
            fill.style.transition = 'width 1s ease';
            fill.style.width = width;
        }, 300);
    });

    // Tooltip on hover
    document.querySelectorAll('[title]').forEach(function(el) {
        if (el.tagName === 'A' || el.tagName === 'BUTTON') {
            el.addEventListener('mouseenter', function() {
                const title = this.getAttribute('title');
                if (!title) return;
                const tooltip = document.createElement('div');
                tooltip.className = 'custom-tooltip';
                tooltip.textContent = title;
                tooltip.style.cssText = 'position:fixed;background:#1a1a2e;color:white;padding:4px 10px;border-radius:4px;font-size:11px;z-index:9999;pointer-events:none;white-space:nowrap';
                document.body.appendChild(tooltip);
                const rect = this.getBoundingClientRect();
                tooltip.style.top = (rect.bottom + 6) + 'px';
                tooltip.style.left = rect.left + 'px';
                this._tooltip = tooltip;
            });
            el.addEventListener('mouseleave', function() {
                if (this._tooltip) { this._tooltip.remove(); delete this._tooltip; }
            });
        }
    });
});

// Sidebar toggle for mobile
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    if (sidebar) sidebar.classList.toggle('open');
}
