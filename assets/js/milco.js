/* ── MILCO JS ───────────────────────────────────── */
(function ($) {
    'use strict';

    /* Smooth scroll */
    document.addEventListener('click', function (e) {
        const a = e.target.closest('a[href^="#"]');
        if (!a) return;
        const id = a.getAttribute('href');
        if (id.length < 2) return;
        const el = document.querySelector(id);
        if (el) { e.preventDefault(); el.scrollIntoView({ behavior: 'smooth' }); }
    });

    /* Cart icon bounce */
    $(document.body).on('added_to_cart', function () {
        const cart = document.querySelector('.site-header-cart');
        if (!cart) return;
        cart.style.transition = 'transform .25s';
        cart.style.transform  = 'scale(1.08)';
        setTimeout(() => { cart.style.transform = 'scale(1)'; }, 250);
    });

})(jQuery);

/* ── Everything DOM-dependent in ONE listener ───── */
document.addEventListener('DOMContentLoaded', function () {

    /* ---- Notifications Bell ---- */
    const bell = document.querySelector('.milco-bell');
    if (bell && typeof milcoData !== 'undefined' && milcoData.isAdmin === 'yes') {

        const dropdown = document.createElement('div');
        dropdown.className = 'milco-notif-dropdown';
        dropdown.innerHTML = '<p class="milco-notif-loading">Loading…</p>';
        bell.parentNode.style.position = 'relative';
        bell.parentNode.appendChild(dropdown);

        let loaded = false;

        bell.addEventListener('click', function (e) {
            e.stopPropagation();
            dropdown.classList.toggle('milco-notif-open');

            if (!loaded) {
                loaded = true;
                fetch(milcoData.ajaxUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=milco_get_notifications&nonce=' + milcoData.nonce,
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success || data.data.notes.length === 0) {
                        dropdown.innerHTML = '<p class="milco-notif-empty">All clear ✅</p>';
                        return;
                    }
                    const dot = bell.querySelector('.milco-notif-dot');
                    if (dot) dot.textContent = data.data.count;

                    dropdown.innerHTML = data.data.notes.map(n => `
                        <a href="${n.link}" class="milco-notif-item milco-notif-${n.type}">
                            <span class="milco-notif-msg">${n.message}</span>
                            <span class="milco-notif-time">${n.time}</span>
                        </a>
                    `).join('');
                })
                .catch(() => {
                    dropdown.innerHTML = '<p class="milco-notif-empty">Could not load.</p>';
                });
            }
        });
    }

    /* ---- Mobile Hamburger Menu ---- */
    const headerInner = document.querySelector('.milco-header__inner');
    const nav         = document.querySelector('.milco-nav');

    if (!headerInner || !nav) {
        console.warn('MILCO: .milco-header__inner or .milco-nav not found — hamburger skipped.');
        return;
    }

    const toggle = document.createElement('button');
    toggle.className = 'milco-menu-toggle';
    toggle.setAttribute('aria-label', 'Toggle menu');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.innerHTML = `
        <svg class="icon-menu" xmlns="http://www.w3.org/2000/svg"
             width="24" height="24" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round">
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
        <svg class="icon-close" xmlns="http://www.w3.org/2000/svg"
             width="24" height="24" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round"
             style="display:none">
            <line x1="18" y1="6" x2="6"  y2="18"/>
            <line x1="6"  y1="6" x2="18" y2="18"/>
        </svg>`;

    const actions = headerInner.querySelector('.milco-header__actions');
    if (actions) {
        headerInner.insertBefore(toggle, actions);
    } else {
        headerInner.appendChild(toggle);
    }

    function closeMenu() {
        nav.classList.remove('milco-nav--open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.querySelector('.icon-menu').style.display  = '';
        toggle.querySelector('.icon-close').style.display = 'none';
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = nav.classList.toggle('milco-nav--open');
        toggle.setAttribute('aria-expanded', String(isOpen));
        toggle.querySelector('.icon-menu').style.display  = isOpen ? 'none' : '';
        toggle.querySelector('.icon-close').style.display = isOpen ? ''     : 'none';
    });

    nav.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('click', function (e) {
        if (!headerInner.contains(e.target)) closeMenu();
    });

    /* ---- Console confirmation ---- */
    console.log('MILCO JS loaded ✓');
});