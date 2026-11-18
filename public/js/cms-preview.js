/* CMS live preview: renders the public page with the form's unsaved changes and links texts to their fields. */
window.cmsPreview = (config) => ({
    config,
    device: 'desktop',
    locale: 'es',
    collapsed: false,
    overlayOpen: false,
    status: 'loading',
    notice: '',
    blocking: false,
    dirty: false,
    path: '',
    hint: null,
    fields: [],
    timer: null,
    busy: false,
    queued: false,
    scrollY: 0,
    widths: { desktop: 1280, tablet: 820, mobile: 390 },
    messages: {
        404: 'Esta página no está disponible públicamente todavía.',
        422: 'Hay un campo con un valor no válido. Corrígelo para actualizar la vista previa.',
        500: 'No se pudo generar la vista previa. Revisa los cambios o vuelve a intentarlo.',
    },

    init() {
        try {
            this.device = localStorage.getItem('cmsPreviewDevice') || 'desktop';
            this.collapsed = localStorage.getItem('cmsPreviewCollapsed') === '1';
        } catch (error) {}

        const form = this.$refs.form;
        form.addEventListener('input', (event) => this.onEdit(event.target));
        form.addEventListener('change', (event) => this.onEdit(event.target));
        form.addEventListener('focusin', (event) => this.onFocus(event.target));
        form.addEventListener('click', (event) => {
            if (event.target.closest('[role="tab"]')) {
                setTimeout(() => this.onTabChange(), 80);
            }
        });

        Livewire.hook('commit', ({ component, commit, succeed }) => {
            if (component.id !== this.$wire.$id) return;
            const methods = commit.calls.map((call) => call.method);
            if (methods.includes('renderPreview')) return;
            succeed(() => {
                if (methods.includes('save')) this.dirty = false;
                this.schedule(250);
            });
        });

        this.$refs.frame.addEventListener('load', () => this.onFrameLoad());
        new ResizeObserver(() => this.fit()).observe(this.$refs.stage);
        this.fit();
        this.refresh();
    },

    setDevice(device) {
        this.device = device;
        try { localStorage.setItem('cmsPreviewDevice', device); } catch (error) {}
        this.fit();
    },

    setLocale(locale) {
        if (this.locale === locale) return;
        this.locale = locale;
        this.refresh();
    },

    toggleCollapsed() {
        this.collapsed = !this.collapsed;
        try { localStorage.setItem('cmsPreviewCollapsed', this.collapsed ? '1' : '0'); } catch (error) {}
        this.$nextTick(() => this.fit());
    },

    openOverlay() {
        this.overlayOpen = true;
        this.$nextTick(() => this.fit());
    },

    /* Scales the page to the panel, keeping the real width of each device. */
    fit() {
        const stage = this.$refs.stage;
        const frame = this.$refs.frame;
        if (!stage.clientWidth) return;
        const width = this.widths[this.device];
        const scale = Math.min(1, (stage.clientWidth - 24) / width);
        frame.style.width = `${width}px`;
        frame.style.height = `${(stage.clientHeight - 24) / scale}px`;
        frame.style.transform = `translateX(-50%) scale(${scale})`;
        stage.dataset.device = this.device;
    },

    schedule(delay) {
        clearTimeout(this.timer);
        this.timer = setTimeout(() => this.refresh(), delay);
    },

    async refresh() {
        if (this.busy) {
            this.queued = true;
            return;
        }
        this.busy = true;
        this.status = 'loading';

        try {
            const result = await this.$wire.renderPreview(this.locale, this.hint);
            this.path = result.path;
            if (result.html) {
                this.notice = '';
                this.show(result.html);
                this.status = 'ready';
            } else {
                this.status = 'error';
                this.notice = this.messages[result.status] || this.messages[500];
                this.blocking = result.status !== 422 || !this.$refs.frame.srcdoc;
            }
        } catch (error) {
            this.status = 'error';
            this.notice = this.messages[500];
            this.blocking = !this.$refs.frame.srcdoc;
        } finally {
            this.busy = false;
            if (this.queued) {
                this.queued = false;
                this.refresh();
            }
        }
    },

    show(html) {
        const frame = this.$refs.frame;
        try { this.scrollY = frame.contentWindow?.scrollY || 0; } catch (error) {}
        const style = '<style>[data-cms-field]{cursor:pointer;transition:outline-color .15s}[data-cms-field]:hover{outline:2px dashed #f59e0b;outline-offset:3px}.cms-focus{outline:3px solid #f59e0b!important;outline-offset:4px;background-color:rgba(253,219,2,.2)!important;border-radius:2px}</style>';
        frame.srcdoc = html.includes('</head>') ? html.replace('</head>', `${style}</head>`) : style + html;
    },

    onFrameLoad() {
        const frame = this.$refs.frame;
        const doc = frame.contentDocument;
        if (!frame.srcdoc || !doc?.body) return;
        frame.contentWindow.scrollTo(0, this.scrollY);

        doc.addEventListener('click', (event) => {
            if (event.target.closest('a')) event.preventDefault();
            const target = event.target.closest('[data-cms-field]');
            if (target) this.openField(target);
        }, true);
        doc.addEventListener('submit', (event) => event.preventDefault(), true);

        this.indexFields(doc);
        const active = document.activeElement;
        if (this.isField(active) && this.$refs.form.contains(active)) this.highlight(active, false);
    },

    isField(element) {
        return element instanceof HTMLTextAreaElement
            || (element instanceof HTMLInputElement && ['text', 'url', 'email', 'search', ''].includes(element.type));
    },

    statePath(element) {
        for (const attribute of element.attributes) {
            if (attribute.name.startsWith('wire:model')) return attribute.value;
        }
        return element.id || null;
    },

    languageOf(path) {
        return path?.match(/(?:^|[._])(es|en)(?:[._]|$)/)?.[1] || null;
    },

    track(element) {
        if (!this.isField(element)) return;
        const path = this.statePath(element);
        if (!path) return;
        const changedPage = this.pageOf(path) !== this.pageOf(this.hint);
        this.hint = path;
        const language = this.languageOf(path);
        if (this.config.localized && language && language !== this.locale) {
            this.locale = language;
            return true;
        }
        return changedPage;
    },

    pageOf(path) {
        return path?.match(/(?:^|\.)c(\d+)\./)?.[1] || null;
    },

    onEdit(element) {
        if (!this.isField(element) && !(element instanceof HTMLSelectElement) && element.type !== 'checkbox') return;
        this.dirty = true;
        this.track(element);
        this.schedule(600);
    },

    onFocus(element) {
        if (!this.isField(element)) return;
        if (this.track(element)) {
            this.schedule(0);
            return;
        }
        this.highlight(element, true);
    },

    onTabChange() {
        const visible = [...this.$refs.form.querySelectorAll('input, textarea')].find((element) => this.isField(element) && element.offsetParent);
        if (visible && this.track(visible)) this.schedule(0);
    },

    normalize(text) {
        return (text || '').replace(/\s+/g, ' ').trim();
    },

    /* Marks the preview elements that show each field's value. */
    indexFields(doc) {
        const form = this.$refs.form;
        this.fields = [...form.querySelectorAll('input, textarea')].filter((element) => this.isField(element));

        const texts = [];
        const walker = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT, {
            acceptNode: (node) => node.parentElement?.closest('script, style, noscript') ? NodeFilter.FILTER_REJECT : NodeFilter.FILTER_ACCEPT,
        });
        while (walker.nextNode()) {
            const text = this.normalize(walker.currentNode.nodeValue);
            if (text) texts.push({ element: walker.currentNode.parentElement, text });
        }
        const images = [...doc.querySelectorAll('img[alt]')];
        const links = [...doc.querySelectorAll('a[href]')];

        const ordered = this.fields
            .map((field, index) => ({ field, index, language: this.languageOf(this.statePath(field)) }))
            .sort((a, b) => (b.language === this.locale) - (a.language === this.locale));

        for (const { field, index } of ordered) {
            const value = this.normalize(field.value);
            if (value.length < 2) continue;
            const path = this.statePath(field) || '';
            const isLink = /(?:href|Href|Url|url)(?:\.|$)|hrefs\./.test(path) || /^(https?:|mailto:|tel:|\/|#)/.test(value);
            const element = isLink ? this.findLink(links, value) : this.findText(texts, images, value);
            if (element && !element.hasAttribute('data-cms-field')) element.setAttribute('data-cms-field', index);
        }
    },

    findText(texts, images, value) {
        const exact = texts.find((entry) => entry.text === value);
        if (exact) return exact.element;
        const whole = texts.find((entry) => this.normalize(entry.element.textContent) === value);
        if (whole) return whole.element;
        const image = images.find((img) => this.normalize(img.alt) === value);
        if (image) return image;
        if (value.length >= 8) return texts.find((entry) => entry.text.includes(value))?.element || null;
        return null;
    },

    findLink(links, value) {
        return links.find((link) => {
            const href = link.getAttribute('href');
            if (/^(mailto:|tel:|#)/.test(value)) return href === value;
            try {
                const url = new URL(href, 'http://preview.local');
                if (/^https?:/.test(value)) return href.startsWith(value) || decodeURIComponent(url.href).startsWith(value);
                return decodeURIComponent(url.pathname) === decodeURIComponent(value.split(/[?#]/)[0]);
            } catch (error) {
                return false;
            }
        }) || null;
    },

    highlight(field, scroll) {
        const doc = this.$refs.frame.contentDocument;
        if (!doc?.body) return;
        doc.querySelectorAll('.cms-focus').forEach((element) => element.classList.remove('cms-focus'));
        const index = this.fields.indexOf(field);
        const target = index >= 0 ? doc.querySelector(`[data-cms-field="${index}"]`) : null;
        if (!target) return;
        target.classList.add('cms-focus');
        if (scroll) target.scrollIntoView({ block: 'center', behavior: 'smooth' });
    },

    /* Opens the tabs, sections and repeater items that hide a field, then focuses it. */
    openField(element) {
        const field = this.fields[Number(element.dataset.cmsField)];
        if (!field?.isConnected) return;
        this.overlayOpen = false;

        const ancestors = [];
        for (let node = field.parentElement; node && node !== this.$refs.form; node = node.parentElement) ancestors.unshift(node);
        for (const node of ancestors) {
            if (node.getAttribute('role') === 'tabpanel' && node.id) {
                this.$refs.form.querySelector(`[aria-controls="${CSS.escape(node.id)}"]`)?.click();
            }
            node.dispatchEvent(new CustomEvent('expand'));
        }

        setTimeout(() => {
            field.scrollIntoView({ block: 'center', behavior: 'smooth' });
            field.focus({ preventScroll: true });
            const wrapper = field.closest('.fi-fo-field') || field.parentElement;
            wrapper.classList.add('cms-live-flash');
            setTimeout(() => wrapper.classList.remove('cms-live-flash'), 1600);
        }, 120);
    },
});
