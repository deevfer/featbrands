(() => {
    let pendingRequest = null;
    let pendingUrl = null;
    let renderedUrl = new URL(window.location.href);
    const localeOf = (url) => url.searchParams.get('lang') === 'en' ? 'en' : 'es';
    const status = document.querySelector('[data-language-status]');
    const setBusy = (busy) => {
        const switcher = document.querySelector('[data-language-switcher]');
        if (busy) switcher?.setAttribute('aria-busy', 'true');
        else switcher?.removeAttribute('aria-busy');
    };

    const captureState = () => ({
        scroll: { left: window.scrollX, top: window.scrollY },
        deck: document.querySelector('[data-deck]')?.dataset.activeIndex,
        caseFilter: document.querySelector('[data-case-archive]')?.dataset.filter,
        contact: document.querySelector('[data-contact-form]') ? {
            fields: [...document.querySelectorAll('[data-contact-field]')].map((field) => ({
                name: field.name, value: field.value, invalid: field.getAttribute('aria-invalid') === 'true',
            })),
            state: document.querySelector('[data-contact-form]').dataset.state,
            errorKind: document.querySelector('[data-contact-form]').dataset.errorKind,
        } : null,
        panels: [...document.querySelectorAll('[data-panel-group]')].map((group) => ({
            name: group.dataset.panelGroup,
            index: group.dataset.activePanel || '0',
        })),
        details: [...document.querySelectorAll('main details')].map((detail) => detail.open),
        faq: [...document.querySelectorAll('[data-faq-toggle]')].findIndex((button) => button.getAttribute('aria-expanded') === 'true'),
    });

    const restoreState = (state) => {
        const deck = document.querySelector('[data-deck]');
        if (deck && state.deck !== undefined) deck.dataset.activeIndex = state.deck;
        const archive = document.querySelector('[data-case-archive]');
        if (archive && state.caseFilter !== undefined) archive.dataset.filter = state.caseFilter;
        const contact = document.querySelector('[data-contact-form]');
        if (contact && state.contact) {
            contact.dataset.state = state.contact.state;
            if (state.contact.errorKind) contact.dataset.errorKind = state.contact.errorKind;
            state.contact.fields.forEach((saved) => {
                const field = contact.elements.namedItem(saved.name);
                if (!field) return;
                field.value = saved.value;
                if (saved.invalid) {
                    field.setAttribute('aria-invalid', 'true');
                    const error = [...contact.querySelectorAll('[data-error-for]')].find((item) => item.dataset.errorFor === saved.name);
                    if (error) { error.textContent = error.dataset.errorMessage; error.hidden = false; }
                }
            });
        }
        document.querySelectorAll('[data-panel-group]').forEach((group) => {
            const saved = state.panels.find((panel) => panel.name === group.dataset.panelGroup);
            if (saved) group.dataset.activePanel = saved.index;
        });
        document.querySelectorAll('main details').forEach((detail, index) => {
            if (state.details[index] !== undefined) detail.open = state.details[index];
        });
        document.querySelectorAll('[data-faq-toggle]').forEach((button, index) => {
            const open = index === state.faq;
            button.setAttribute('aria-expanded', String(open));
            button.querySelector('[data-faq-sign]').textContent = open ? '–' : '+';
            document.getElementById(button.getAttribute('aria-controls')).hidden = !open;
        });
    };

    const switchLanguage = async (target, { history = 'push', focusLanguage = null } = {}) => {
        pendingRequest?.abort();
        const request = new AbortController();
        pendingRequest = request;
        pendingUrl = new URL(target);
        status.hidden = true;

        if (localeOf(target) === document.documentElement.lang && target.searchParams.get('market') === renderedUrl.searchParams.get('market')) {
            if (history === 'push' && target.href !== window.location.href) {
                window.history.pushState(null, '', target);
            }
            renderedUrl = new URL(target);
            pendingRequest = null;
            pendingUrl = null;
            setBusy(false);
            return;
        }

        setBusy(true);
        try {
            const response = await fetch(target, {
                headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
                signal: request.signal,
                credentials: 'same-origin',
            });
            if (!response.ok) throw new Error('Language request failed');
            const html = await response.text();
            if (request.signal.aborted) return;
            const translated = new DOMParser().parseFromString(html, 'text/html');
            const nextShell = translated.querySelector('.site-shell');
            if (!nextShell || translated.body.dataset.page !== document.body.dataset.page || translated.documentElement.lang !== localeOf(target)) {
                throw new Error('Unexpected language response');
            }

            const state = captureState();
            const imported = document.importNode(nextShell, true);
            document.querySelector('.site-shell').replaceWith(imported);
            document.documentElement.lang = translated.documentElement.lang;
            document.title = translated.title;
            document.querySelector('meta[name="description"]').content = translated.querySelector('meta[name="description"]').content;
            for (const [key, value] of Object.entries(translated.body.dataset)) document.body.dataset[key] = value;
            const skip = translated.querySelector('.skip-link');
            if (skip) document.querySelector('.skip-link').textContent = skip.textContent;
            restoreState(state);
            document.dispatchEvent(new CustomEvent('site:content-updated'));

            if (history === 'push') window.history.pushState(null, '', target);
            renderedUrl = new URL(target);
            window.scrollTo({ ...state.scroll, behavior: 'instant' });
            if (focusLanguage) document.querySelector(`[data-language="${focusLanguage}"]`)?.focus({ preventScroll: true });
            document.dispatchEvent(new CustomEvent('site:language-changed', {
                detail: { language: translated.documentElement.lang, url: target.href },
            }));
        } catch (error) {
            if (request.signal.aborted) return;
            if (history === 'none') window.history.replaceState(null, '', renderedUrl);
            status.textContent = document.documentElement.lang === 'en'
                ? 'The language could not be changed. Please try again.'
                : 'No se pudo cambiar el idioma. Inténtalo de nuevo.';
            status.hidden = false;
            document.dispatchEvent(new CustomEvent('site:language-error'));
        } finally {
            if (pendingRequest === request) {
                pendingRequest = null;
                pendingUrl = null;
                setBusy(false);
            }
        }
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-language]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target === '_blank') return;
        const destination = new URL(link.href);
        if (destination.origin !== window.location.origin || destination.pathname !== window.location.pathname) return;
        event.preventDefault();
        const target = new URL(window.location.href);
        target.searchParams.set('lang', link.dataset.language);
        switchLanguage(target, { focusLanguage: link.dataset.language });
    });

    window.addEventListener('popstate', () => {
        const target = new URL(window.location.href);
        if (target.pathname === renderedUrl.pathname) switchLanguage(target, { history: 'none' });
    });

    document.addEventListener('site:market-changed', () => {
        renderedUrl = new URL(window.location.href);
        if (pendingUrl) {
            const target = new URL(window.location.href);
            target.searchParams.set('lang', localeOf(pendingUrl));
            switchLanguage(target);
        }
    });
})();
