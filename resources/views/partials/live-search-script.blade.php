<script>
    function showLoadingSpinner() {
        const loading = document.getElementById('search-loading') || document.getElementById('loader');
        const btn = document.getElementById('search-submit-btn');
        if (loading) loading.classList.remove('hidden');
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-55');
        }
    }

    function hideLoadingSpinner() {
        const loading = document.getElementById('search-loading') || document.getElementById('loader');
        const btn = document.getElementById('search-submit-btn');
        if (loading) loading.classList.add('hidden');
        if (btn) {
            btn.disabled = false;
            btn.classList.remove('opacity-55');
        }
    }

    function doReloadCaptcha() {
        fetch('/reload-captcha')
            .then(response => response.json())
            .then(data => {
                document.querySelectorAll('.captcha-img').forEach(container => {
                    if (data.captcha) container.innerHTML = data.captcha;
                });
                const captchaInput = document.getElementById('captcha-input');
                if (captchaInput) captchaInput.value = '';
            })
            .catch(() => {});
    }

    document.addEventListener('DOMContentLoaded', () => {
        const reloadBtn = document.getElementById('reload-captcha');
        if (reloadBtn) reloadBtn.addEventListener('click', doReloadCaptcha);

        const searchForm = document.getElementById('hero-search-form');
        const errorContainer = document.getElementById('captcha-error-container');
        const errorText = document.getElementById('captcha-error-text');
        const resultsMount = document.getElementById('live-results');
        const mockup = document.getElementById('profile-preview-mockup');

        if (!searchForm) return;

        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            showLoadingSpinner();
            if (errorContainer) errorContainer.classList.add('hidden');
            if (mockup) mockup.classList.add('hidden');
            if (resultsMount) resultsMount.classList.add('hidden');

            const formData = new FormData(searchForm);

            fetch(searchForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(async response => {
                hideLoadingSpinner();
                let data = {};
                try { data = await response.json(); } catch (err) { data = {}; }

                if (response.ok && data.success && data.html) {
                    if (resultsMount) {
                        resultsMount.innerHTML = data.html;
                        resultsMount.classList.remove('hidden');
                        resultsMount.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    doReloadCaptcha();
                    return;
                }

                let errorMsg = response.status === 419
                    ? 'Your session expired. Refresh this page and try again.'
                    : response.status === 429
                        ? 'Too many searches. Please wait a minute and try again.'
                        : 'Could not complete this lookup. Please try again.';
                if (data.errors && data.errors.captcha) {
                    errorMsg = data.errors.captcha[0];
                } else if (data.errors && data.errors.username) {
                    errorMsg = data.errors.username[0];
                } else if (data.message && response.status !== 419 && response.status !== 429) {
                    errorMsg = data.message;
                }

                if (errorText) errorText.textContent = errorMsg;
                if (errorContainer) errorContainer.classList.remove('hidden');
                doReloadCaptcha();
            })
            .catch(() => {
                hideLoadingSpinner();
                if (errorText) errorText.textContent = 'Network error. Please try again.';
                if (errorContainer) errorContainer.classList.remove('hidden');
                doReloadCaptcha();
            });
        });
    });

    function switchResultTab(btn, tab) {
        const root = btn.closest('[data-result-root]') || btn.closest('.rounded-3xl') || document;
        root.querySelectorAll('.result-tab').forEach((el) => {
            el.classList.remove('border-slate-950', 'text-slate-950', 'border-brand-primary', 'text-brand-primary', 'border-[#1877F2]', 'text-[#1877F2]');
            el.classList.add('border-transparent', 'text-slate-400');
            el.setAttribute('aria-selected', 'false');
        });
        btn.classList.remove('border-transparent', 'text-slate-400', 'text-slate-500');
        btn.setAttribute('aria-selected', 'true');
        const isFbRoot = !!btn.closest('[data-fb-root]');
        if (isFbRoot) btn.classList.add('border-[#1877F2]', 'text-[#1877F2]');
        else btn.classList.add('border-brand-primary', 'text-slate-950');
        root.querySelectorAll('[data-tab-panel]').forEach((panel) => {
            panel.classList.toggle('hidden', panel.getAttribute('data-tab-panel') !== tab);
        });
    }

    function shareLookupResult(title, url) {
        if (navigator.share) {
            navigator.share({ title: title, url: url }).catch(() => {});
            return;
        }
        if (navigator.clipboard && url) {
            navigator.clipboard.writeText(url).then(() => alert('Link copied')).catch(() => {});
        }
    }
</script>
