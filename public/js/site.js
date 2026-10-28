(() => {
    let lifecycle;
    let deckTimer;
    const initialize = () => {
        lifecycle?.abort();
        clearInterval(deckTimer);
        lifecycle = new AbortController();
        const listen = (element, type, callback, options = {}) => {
            element.addEventListener(type, callback, { ...options, signal: lifecycle.signal });
        };
        const body = document.body;
        const updateNavigation = () => {
            document.querySelectorAll('[data-nav-page]').forEach((link) => {
                const section = link.dataset.navSection;
                const active = section
                    ? body.dataset.page === 'home' && window.location.hash === `#${section}`
                    : link.dataset.navPage === body.dataset.page;
                link.classList.toggle('is-active', active);
                if (active) link.setAttribute('aria-current', section ? 'location' : 'page');
                else link.removeAttribute('aria-current');
            });
        };
        updateNavigation();
        listen(window, 'hashchange', updateNavigation);
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const activateOnKeyboard = (element, action) => {
            listen(element, 'keydown', (event) => {
                if (event.target === element && (event.key === 'Enter' || event.key === ' ')) {
                    event.preventDefault();
                    action();
                }
            });
        };

        document.querySelectorAll('[data-link]').forEach((card) => {
            const navigate = () => window.location.assign(card.dataset.link);
            listen(card, 'click', (event) => {
                if (!event.target.closest('a,button')) navigate();
            });
            activateOnKeyboard(card, navigate);
        });

        const menu = document.querySelector('[data-services-menu]');
        if (menu) {
            const toggle = menu.querySelector('[data-services-toggle]');
            const dropdown = menu.querySelector('[data-services-dropdown]');
            const open = (value) => {
                dropdown.hidden = !value;
                toggle.setAttribute('aria-expanded', String(value));
            };
            listen(toggle, 'click', () => open(dropdown.hidden));
            listen(menu, 'mouseenter', () => {
                if (window.matchMedia('(hover: hover)').matches) open(true);
            });
            listen(menu, 'mouseleave', () => {
                if (!menu.contains(document.activeElement)) open(false);
            });
            listen(menu, 'focusout', (event) => {
                if (!menu.contains(event.relatedTarget)) open(false);
            });
            listen(document, 'click', (event) => {
                if (!menu.contains(event.target)) open(false);
            });
            listen(menu, 'keydown', (event) => {
                if (event.key === 'Escape') {
                    open(false);
                    toggle.focus();
                }
            });
        }

        const selectMarket = (market) => {
            body.dataset.market = market;
            document.querySelectorAll('[data-market-choice]').forEach((chip) => {
                const active = chip.dataset.marketChoice === market;
                chip.classList.toggle('is-active', active);
                chip.setAttribute('aria-pressed', String(active));
            });
            document.querySelectorAll('[data-office]').forEach((office) => {
                const active = office.dataset.office === market;
                office.classList.toggle('is-active', active);
                office.setAttribute('aria-expanded', String(active));
                office.querySelector('[data-office-details]').hidden = !active;
                office.querySelector('[data-office-sign]').textContent = active ? '– ' : '+ ';
            });
            const current = new URL(window.location.href);
            if (market) current.searchParams.set('market', market);
            else current.searchParams.delete('market');
            window.history.replaceState(null, '', current);
            document.querySelectorAll('a[href], [data-link], [data-service-url]').forEach((element) => {
                const attribute = element.hasAttribute('href') ? 'href' : element.hasAttribute('data-service-url') ? 'data-service-url' : 'data-link';
                const value = element.getAttribute(attribute);
                if (value.startsWith('#')) return;
                const target = new URL(value, window.location.href);
                if (target.origin !== window.location.origin) return;
                if (market) target.searchParams.set('market', market);
                else target.searchParams.delete('market');
                element.setAttribute(attribute, target.href);
            });
            const performance = new URL(body.dataset.performanceUrl);
            if (market) performance.searchParams.set('market', market);
            else performance.searchParams.delete('market');
            body.dataset.performanceUrl = performance.href;
            document.dispatchEvent(new CustomEvent('site:market-changed'));
        };
        document.querySelectorAll('[data-market-choice]').forEach((chip) => {
            listen(chip, 'click', () => selectMarket(chip.dataset.marketChoice));
        });
        document.querySelectorAll('[data-office]').forEach((office) => {
            const select = () => selectMarket(office.dataset.office);
            listen(office, 'click', select);
            activateOnKeyboard(office, select);
        });

        const faqButtons = [...document.querySelectorAll('[data-faq-toggle]')];
        faqButtons.forEach((button) => {
            listen(button, 'click', () => {
                const shouldOpen = button.getAttribute('aria-expanded') !== 'true';
                faqButtons.forEach((other) => {
                    const open = other === button && shouldOpen;
                    other.setAttribute('aria-expanded', String(open));
                    other.querySelector('[data-faq-sign]').textContent = open ? '–' : '+';
                    document.getElementById(other.getAttribute('aria-controls')).hidden = !open;
                });
            });
        });

        const deck = document.querySelector('[data-deck]');
        if (!deck) return;
        const cards = [...deck.querySelectorAll('[data-deck-card]')];
        const dots = [...deck.querySelectorAll('[data-deck-dot]')];
        const poses = [
            ['translate(0,0) rotate(0deg) scale(1)', 50, 1],
            ['translate(0,32px) rotate(2.5deg) scale(.94)', 40, 1],
            ['translate(0,62px) rotate(-3deg) scale(.885)', 30, 1],
            ['translate(0,88px) rotate(1.5deg) scale(.835)', 20, .85],
            ['translate(-60px,130px) rotate(-10deg) scale(.8)', 10, 0],
        ];
        let active = Number(deck.dataset.activeIndex || 0);
        let hold = false;
        const show = (index) => {
            active = (index + cards.length) % cards.length;
            deck.dataset.activeIndex = active;
            cards.forEach((card, cardIndex) => {
                const position = (cardIndex - active + cards.length) % cards.length;
                const [transform, zIndex, opacity] = poses[position];
                card.style.transform = transform;
                card.style.zIndex = zIndex;
                card.style.opacity = opacity;
                card.style.visibility = opacity === 0 ? 'hidden' : 'visible';
                card.tabIndex = position === 0 ? 0 : -1;
                card.querySelectorAll('button').forEach((button) => button.tabIndex = position === 0 ? 0 : -1);
            });
            dots.forEach((dot, dotIndex) => {
                dot.classList.toggle('is-active', dotIndex === active);
                dot.setAttribute('aria-pressed', String(dotIndex === active));
            });
            deck.querySelector('[data-deck-counter]').textContent = `${String(active + 1).padStart(2, '0')} — 05`;
        };
        const open = (index) => {
            if (index === active) window.location.assign(cards[index].dataset.serviceUrl);
            else show(active + 1);
        };
        deck.querySelectorAll('[data-deck-open]').forEach((element) => {
            listen(element, 'click', (event) => {
                event.stopPropagation();
                open(Number(element.dataset.deckOpen));
            });
            if (element.hasAttribute('data-deck-card')) activateOnKeyboard(element, () => open(Number(element.dataset.deckOpen)));
        });
        dots.forEach((dot) => listen(dot, 'click', () => show(Number(dot.dataset.deckDot))));
        listen(deck.querySelector('[data-deck-next]'), 'click', () => show(active + 1));
        listen(deck.querySelector('[data-deck-prev]'), 'click', () => show(active - 1));
        listen(deck, 'mouseenter', () => hold = true);
        listen(deck, 'mouseleave', () => hold = false);
        listen(deck, 'focusin', () => hold = true);
        listen(deck, 'focusout', (event) => {
            if (!deck.contains(event.relatedTarget)) hold = false;
        });
        deckTimer = reducedMotion ? null : setInterval(() => {
            if (!hold && !document.hidden) show(active + 1);
        }, 4500);
        listen(window, 'pagehide', () => clearInterval(deckTimer), { once: true });
        show(active);
    };
    initialize();
    document.addEventListener('site:content-updated', initialize);
})();
