(() => {
    let bindings;
    const initialize = () => {
        bindings?.abort();
        bindings = new AbortController();
        const archive = document.querySelector('[data-case-archive]');
        if (!archive) return;
        const buttons = [...archive.querySelectorAll('[data-case-filter]')];
        const cards = [...archive.querySelectorAll('[data-case-card]')];
        const counter = archive.querySelector('[data-case-counter]');
        const applyFilter = (filter) => {
            archive.dataset.filter = filter;
            buttons.forEach((button) => {
                const active = button.dataset.caseFilter === filter;
                button.classList.toggle('is-active', active);
                button.setAttribute('aria-pressed', String(active));
            });
            let count = 0;
            cards.forEach((card) => {
                card.hidden = filter !== 'all' && card.dataset.category !== filter;
                if (!card.hidden) count++;
            });
            counter.textContent = `${count} ${count === 1 ? counter.dataset.singular : counter.dataset.plural}`;
            archive.querySelector('[data-case-empty]').hidden = count !== 0;
        };
        buttons.forEach((button) => button.addEventListener('click', () => applyFilter(button.dataset.caseFilter), { signal: bindings.signal }));
        applyFilter(buttons.some((button) => button.dataset.caseFilter === archive.dataset.filter) ? archive.dataset.filter : 'all');
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
    else initialize();
    document.addEventListener('site:content-updated', initialize);
})();
