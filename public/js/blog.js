(() => {
    let lifecycle;
    const initialize = () => {
        lifecycle?.abort();
        lifecycle = new AbortController();
        const signal = lifecycle.signal;
        document.querySelector('[data-blog-search]')?.addEventListener('submit', (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const term = form.elements.namedItem('q').value.trim();
            const base = form.dataset.searchBase;
            const destination = new URL(term ? `${base}/${encodeURIComponent(term)}` : base.replace(/\/search$/, ''), location.origin);
            destination.searchParams.set('market', document.body.dataset.market);
            destination.hash = 'archivo';
            location.assign(destination);
        }, { signal });
        document.querySelector('[data-blog-copy]')?.addEventListener('click', async (event) => {
            const button = event.currentTarget;
            try {
                await navigator.clipboard.writeText(button.dataset.url);
                button.textContent = button.dataset.copied;
                document.querySelector('[data-copy-status]').textContent = button.dataset.copied;
            } catch {
                const field = document.createElement('textarea');
                field.value = button.dataset.url;
                field.style.position = 'fixed'; field.style.opacity = '0';
                document.body.append(field); field.select();
                const copied = document.execCommand('copy'); field.remove();
                if (copied) button.textContent = button.dataset.copied;
            }
        }, { signal });
        document.querySelector('[data-blog-print]')?.addEventListener('click', () => window.print(), { signal });
        const progress = document.querySelector('[data-reading-progress]');
        const content = document.querySelector('[data-blog-content]');
        if (progress && content) {
            const update = () => {
                const rect = content.getBoundingClientRect();
                const total = Math.max(1, rect.height - innerHeight * .35);
                progress.style.width = `${Math.min(100, Math.max(0, -rect.top / total * 100))}%`;
            };
            window.addEventListener('scroll', update, { passive: true, signal });
            window.addEventListener('resize', update, { passive: true, signal });
            update();
        }
    };
    initialize();
    document.addEventListener('site:content-updated', initialize);
})();
