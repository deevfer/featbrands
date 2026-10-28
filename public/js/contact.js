(() => {
    let inFlight = false;
    const form = () => document.querySelector('[data-contact-form]');
    const updateCountry = () => {
        const current = form();
        const select = current?.querySelector('[data-phone-country]');
        const option = select?.selectedOptions[0];
        if (!option) return;
        const picker = select.closest('[data-country-picker]');
        current.elements.namedItem('country_code').value = option.dataset.dialCode;
        picker.querySelector('[data-country-flag]').textContent = option.dataset.flag;
        picker.querySelector('[data-country-prefix]').textContent = option.dataset.dialCode;
        picker.dataset.countryReady = 'true';
    };
    const setBusy = (busy) => {
        const current = form();
        if (!current) return;
        const button = current.querySelector('[data-contact-submit]');
        button.disabled = busy;
        button.querySelector('span').textContent = busy ? button.dataset.sending : button.dataset.label;
        if (busy) current.setAttribute('aria-busy', 'true');
        else current.removeAttribute('aria-busy');
    };
    const showStatus = (kind) => {
        const current = form();
        const status = current.querySelector('[data-contact-status]');
        current.dataset.errorKind = kind;
        status.textContent = status.dataset[kind] || status.dataset.network;
        status.hidden = false;
    };
    const clearErrors = () => {
        const current = form();
        current.querySelectorAll('[data-contact-field]').forEach((field) => field.removeAttribute('aria-invalid'));
        current.querySelectorAll('[data-error-for]').forEach((error) => { error.hidden = true; error.textContent = ''; });
        current.querySelector('[data-contact-status]').hidden = true;
        delete current.dataset.errorKind;
    };
    const initialize = () => {
        const current = form();
        if (!current) return;
        updateCountry();
        const success = current.dataset.state === 'success';
        current.hidden = success;
        document.querySelector('[data-contact-success]').hidden = !success;
        setBusy(inFlight);
        if (current.dataset.errorKind) showStatus(current.dataset.errorKind);
    };
    document.addEventListener('submit', async (event) => {
        if (!event.target.matches('[data-contact-form]')) return;
        event.preventDefault();
        if (inFlight) return;
        const submitted = event.target;
        if (!submitted.reportValidity()) return;
        updateCountry();
        const body = new FormData(submitted);
        clearErrors();
        inFlight = true;
        submitted.dataset.state = 'submitting';
        setBusy(true);
        try {
            const response = await fetch(submitted.action, {
                method: 'POST', body, credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = response.ok || response.status === 422 ? await response.json() : null;
            const current = form();
            if (!current) return;
            if (response.status === 422) {
                showStatus('validation');
                let firstInvalid;
                for (const [name, messages] of Object.entries(result.errors || {})) {
                    const field = current.elements.namedItem(name);
                    const error = [...current.querySelectorAll('[data-error-for]')].find((item) => item.dataset.errorFor === name);
                    if (!field || !error) continue;
                    field.setAttribute('aria-invalid', 'true');
                    error.textContent = body.get('locale') === document.documentElement.lang ? messages[0] : error.dataset.errorMessage;
                    error.hidden = false;
                    firstInvalid ||= field;
                }
                (firstInvalid || current.querySelector('[data-contact-status]')).focus();
            } else if (!response.ok) {
                showStatus(response.status === 429 ? 'rateLimit' : response.status === 419 ? 'expired' : 'network');
                current.querySelector('[data-contact-status]').focus();
            } else {
                if (response.status !== 201 || typeof result.message !== 'string') throw new Error('Unexpected contact response');
                current.reset();
                current.dataset.state = 'success';
                current.hidden = true;
                const success = document.querySelector('[data-contact-success]');
                success.hidden = false;
                success.focus();
            }
        } catch {
            if (form()) {
                showStatus('network');
                form().querySelector('[data-contact-status]').focus();
            }
        } finally {
            inFlight = false;
            const current = form();
            if (current && current.dataset.state !== 'success') current.dataset.state = 'idle';
            setBusy(false);
        }
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-contact-again]') || inFlight) return;
        form().dataset.state = 'idle';
        clearErrors();
        initialize();
        form().elements.namedItem('first_name').focus();
    });
    document.addEventListener('change', (event) => {
        if (event.target.matches('[data-phone-country]')) updateCountry();
    });
    initialize();
    document.addEventListener('site:content-updated', initialize);
    document.addEventListener('site:market-changed', () => {
        if (form()) form().elements.namedItem('market').value = document.body.dataset.market;
    });
})();
