(() => {
    const initialize = () => {
        document.querySelectorAll('[data-panel-group]').forEach((group) => {
            const tabs = [...group.querySelectorAll('[data-panel-tab]')];
            const panels = [...group.querySelectorAll('[data-panel]')];
            const next = group.querySelector('[data-panel-next]');
            let active = 0;
            const select = (index, focus = false) => {
                active = (index + tabs.length) % tabs.length;
                group.dataset.activePanel = active;
                tabs.forEach((tab, tabIndex) => {
                    const selected = tabIndex === active;
                    tab.classList.toggle('is-active', selected);
                    tab.setAttribute('aria-selected', String(selected));
                    tab.tabIndex = selected ? 0 : -1;
                });
                panels.forEach((panel, panelIndex) => panel.hidden = panelIndex !== active);
                if (focus) tabs[active].focus();
                if (next) {
                    next.textContent = `${active === tabs.length - 1 ? next.dataset.restartLabel : next.dataset.nextLabel} →`;
                }
            };
            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => select(index));
                tab.addEventListener('keydown', (event) => {
                    const vertical = tab.closest('[role="tablist"]').getAttribute('aria-orientation') === 'vertical';
                    const forward = vertical ? ['ArrowDown', 'ArrowRight'] : ['ArrowRight'];
                    const backward = vertical ? ['ArrowUp', 'ArrowLeft'] : ['ArrowLeft'];
                    if ([...forward, ...backward, 'Home', 'End'].includes(event.key)) {
                        event.preventDefault();
                        if (event.key === 'Home') select(0, true);
                        else if (event.key === 'End') select(tabs.length - 1, true);
                        else select(index + (forward.includes(event.key) ? 1 : -1), true);
                    }
                });
            });
            next?.addEventListener('click', () => select(active + 1));
            group.querySelectorAll('[data-panel-advance]').forEach((button) => {
                button.addEventListener('click', () => select(active + 1));
            });
            select(Number(group.dataset.activePanel || 0));
        });
    };
    initialize();
    document.addEventListener('site:content-updated', initialize);
})();
