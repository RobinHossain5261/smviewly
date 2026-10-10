import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

// Contact confirmation is shown after the server has saved the message.
const confirmation = document.querySelector('[data-toast-message]');
if (confirmation?.dataset.toastMessage) {
    const content = document.createElement('div');
    content.className = 'sm-toast-content';
    const icon = document.createElement('i');
    icon.className = 'ph ph-check-circle';
    icon.setAttribute('aria-hidden', 'true');
    const copy = document.createElement('div');
    const title = document.createElement('strong');
    title.textContent = 'Message sent';
    const message = document.createElement('span');
    message.textContent = confirmation.dataset.toastMessage;
    copy.append(title, message);
    content.append(icon, copy);
    const toast = Toastify({
        node: content,
        duration: 6000,
        close: true,
        gravity: 'top',
        position: window.matchMedia('(max-width: 640px)').matches ? 'center' : 'right',
        offset: { x: 0, y: window.matchMedia('(max-width: 640px)').matches ? 65 : 75 },
        stopOnFocus: true,
        ariaLive: 'polite',
        className: 'sm-toast-success',
        style: { background: '#f2faf5' },
    });
    toast.showToast();
    const closeButton = toast.toastElement.querySelector('.toast-close');
    closeButton?.setAttribute('aria-label', 'Dismiss notification');
    closeButton?.setAttribute('type', 'button');
}

// Navigation shared by every public page.
(() => {
    const toolsMenu = document.querySelector('.sm-nav-tools');
    const hoverMenu = window.matchMedia('(hover: hover) and (pointer: fine)');
    let menuCloseTimer;
    if (toolsMenu) {
        toolsMenu.addEventListener('pointerenter', () => { if (hoverMenu.matches) { clearTimeout(menuCloseTimer); toolsMenu.open = true; } });
        toolsMenu.addEventListener('pointerleave', () => { if (hoverMenu.matches) menuCloseTimer = setTimeout(() => { if (!toolsMenu.contains(document.activeElement)) toolsMenu.open = false; }, 160); });
        toolsMenu.addEventListener('focusin', () => clearTimeout(menuCloseTimer));
        toolsMenu.addEventListener('focusout', event => { if (!toolsMenu.contains(event.relatedTarget)) toolsMenu.open = false; });
    }
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const backToTop = document.querySelector('.sm-back-to-top');
    const updateTopButton = () => {
        if (!backToTop) return;
        const visible = window.scrollY > 420;
        backToTop.classList.toggle('is-visible', visible);
        backToTop.setAttribute('aria-hidden', String(!visible));
        backToTop.tabIndex = visible ? 0 : -1;
    };
    window.addEventListener('scroll', updateTopButton, { passive: true });
    updateTopButton();
    backToTop?.addEventListener('click', event => {
        event.preventDefault();
        window.scrollTo({ top: 0, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
        const homeLink = document.querySelector('.sm-header .sm-brand');
        homeLink?.focus({ preventScroll: true });
    });
    document.addEventListener('click', event => { if (toolsMenu && !toolsMenu.contains(event.target)) toolsMenu.open = false; });
    document.addEventListener('keydown', event => { if (event.key === 'Escape' && toolsMenu?.open) { toolsMenu.open = false; toolsMenu.querySelector('summary').focus(); } });
    const menuButton = document.querySelector('.sm-menu');
    const mobileNav = document.getElementById('sm-mobile-nav');
    const drawer = document.getElementById('sm-mobile-drawer');
    let closeTimer;
    function closeMenu() {
        if (!drawer?.open) return;
        drawer.classList.remove('is-open');
        menuButton.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('sm-drawer-lock');
        clearTimeout(closeTimer);
        closeTimer = setTimeout(() => { drawer.close(); menuButton.focus({ preventScroll: true }); }, reducedMotion.matches ? 0 : 260);
    }
    if (menuButton && drawer) {
        menuButton.addEventListener('click', () => {
            if (drawer.open) { closeMenu(); return; }
            clearTimeout(closeTimer);
            drawer.showModal();
            document.body.classList.add('sm-drawer-lock');
            menuButton.setAttribute('aria-expanded', 'true');
            requestAnimationFrame(() => requestAnimationFrame(() => drawer.classList.add('is-open')));
        });
        drawer.querySelectorAll('.sm-drawer-close, .sm-drawer-overlay').forEach(button => button.addEventListener('click', closeMenu));
        mobileNav.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMenu));
        drawer.addEventListener('cancel', event => { event.preventDefault(); closeMenu(); });
        window.matchMedia('(min-width: 1101px)').addEventListener('change', event => { if (event.matches) closeMenu(); });
        const toolsToggle = drawer.querySelector('.sm-mobile-tools-toggle');
        const toolLinks = document.getElementById('sm-mobile-tool-links');
        toolsToggle.addEventListener('click', () => {
            const expanded = toolsToggle.getAttribute('aria-expanded') !== 'true';
            toolsToggle.setAttribute('aria-expanded', String(expanded));
            toolLinks.classList.toggle('is-expanded', expanded);
            toolLinks.inert = !expanded;
        });
    }


    // Interactive Smooth Exclusive Accordion for FAQs
    const faqItems = document.querySelectorAll('.sm-faq-item');
    faqItems.forEach(item => {
        const btn = item.querySelector('.sm-faq-question');
        btn.addEventListener('click', () => {
            const isOpen = item.classList.contains('is-open');

            // Smoothly close all other open items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('is-open')) {
                    otherItem.classList.remove('is-open');
                    otherItem.querySelector('.sm-faq-question')?.setAttribute('aria-expanded', 'false');
                }
            });

            // Toggle clicked item
            if (isOpen) {
                item.classList.remove('is-open');
                btn.setAttribute('aria-expanded', 'false');
            } else {
                item.classList.add('is-open');
                btn.setAttribute('aria-expanded', 'true');
            }
        });
    });


})();
// Keep the article navigation compact on phones and tablets.
const articleToc = document.querySelector('.sm-article-toc');
if (articleToc) {
    const sidebarLayout = window.matchMedia('(min-width: 861px)');
    articleToc.open = sidebarLayout.matches;
    sidebarLayout.addEventListener('change', event => { articleToc.open = event.matches; });
}

// Bulk contact selection applies only to the messages visible on this page.
const bulkDelete = document.getElementById('contact-bulk-delete');
if (bulkDelete) {
    const selections = [...document.querySelectorAll('.contact-selection')];
    const selectAll = document.getElementById('contact-select-all');
    const deleteButton = document.getElementById('contact-delete-selected');
    const countLabel = document.getElementById('contact-selection-count');
    const updateSelection = () => {
        const count = selections.filter(input => input.checked).length;
        countLabel.textContent = count + ' selected';
        deleteButton.disabled = count === 0;
        selectAll.checked = count === selections.length && count > 0;
        selectAll.indeterminate = count > 0 && count < selections.length;
    };
    selectAll.addEventListener('change', () => {
        selections.forEach(input => { input.checked = selectAll.checked; });
        updateSelection();
    });
    selections.forEach(input => input.addEventListener('change', updateSelection));
    bulkDelete.addEventListener('submit', event => {
        const count = selections.filter(input => input.checked).length;
        if (!count || !window.confirm('Permanently delete ' + count + ' selected message' + (count === 1 ? '?' : 's?'))) event.preventDefault();
    });
    window.addEventListener('pageshow', updateSelection);
    updateSelection();
}
