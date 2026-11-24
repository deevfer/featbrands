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
    /* reCAPTCHA v3 token for the "contact" action, when the CMS enables it. */
    const recaptchaToken = async (current, action = 'contact') => {
        const siteKey = current.dataset.recaptchaSiteKey;
        if (!siteKey) return null;
        for (let attempt = 0; attempt < 50 && !window.grecaptcha; attempt += 1) {
            await new Promise((resolve) => setTimeout(resolve, 100));
        }
        if (!window.grecaptcha) throw new Error('recaptcha_unavailable');
        return new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('recaptcha_timeout')), 8000);
            window.grecaptcha.ready(() => {
                window.grecaptcha.execute(siteKey, { action }).then((token) => {
                    clearTimeout(timeout);
                    token ? resolve(token) : reject(new Error('recaptcha_empty'));
                }, reject);
            });
        });
    };
    /* Saves the form while the visitor types, so the team can follow up if it is never sent. */
    const intentStorage = 'feat_contact_intent_v1';
    const newKey = () => window.crypto?.randomUUID?.() || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
        const random = Math.random() * 16 | 0;
        return (c === 'x' ? random : (random & 0x3 | 0x8)).toString(16);
    });
    let intentKey = null;
    try { intentKey = sessionStorage.getItem(intentStorage); } catch {}
    if (!/^[0-9a-f-]{36}$/i.test(intentKey || '')) intentKey = newKey();
    try { sessionStorage.setItem(intentStorage, intentKey); } catch {}
    let intentFingerprint = '';
    let intentPending = false;
    let intentRetryAfter = 0;
    let intentTimer = null;
    const intentFields = ['first_name', 'last_name', 'email', 'country_code', 'phone', 'company', 'job_title', 'budget', 'referral_source', 'message', 'locale', 'market', 'website'];
    const trackingCookies = { fbp: '_fbp', fbc: '_fbc', ga: '_ga', gcl_aw: '_gcl_aw', li_fat_id: 'li_fat_id' };
    const intentData = (current) => {
        if (!current || current.dataset.state === 'success') return null;
        const data = {};
        intentFields.forEach((name) => { data[name] = String(current.elements.namedItem(name)?.value || '').trim(); });
        if (!data.first_name || data.first_name.length > 100 || data.email.length > 254 || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) return null;
        return data;
    };
    const tracking = () => {
        if (window.FeatConsent?.allows('marketing') !== true) return {};
        const cookies = Object.fromEntries(document.cookie.split('; ').map((item) => {
            const index = item.indexOf('=');
            return [item.slice(0, index), decodeURIComponent(item.slice(index + 1))];
        }));
        return Object.fromEntries(Object.entries(trackingCookies)
            .filter(([, cookie]) => /^[A-Za-z0-9._-]{1,255}$/.test(cookies[cookie] || ''))
            .map(([key, cookie]) => [key, cookies[cookie]]));
    };
    const saveIntent = async () => {
        const current = form();
        updateCountry();
        const data = intentData(current);
        if (!data || inFlight || intentPending || Date.now() < intentRetryAfter) return;
        const marketing = window.FeatConsent?.allows('marketing') === true;
        const payload = { ...data, intent_key: intentKey, marketing_consent: marketing, tracking: tracking() };
        const fingerprint = JSON.stringify(payload);
        if (fingerprint === intentFingerprint) return;
        intentPending = true;
        try {
            const token = await recaptchaToken(current, 'contact_intent');
            const response = await fetch(current.dataset.intentUrl, {
                method: 'POST', credentials: 'same-origin', keepalive: true,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': current.elements.namedItem('_token')?.value || '' },
                body: JSON.stringify(token ? { ...payload, 'g-recaptcha-response': token } : payload),
            });
            if (!response.ok) throw new Error('intent_failed');
            intentFingerprint = fingerprint;
        } catch {
            intentRetryAfter = Date.now() + 30000;
        } finally {
            intentPending = false;
        }
    };
    const scheduleIntent = () => {
        clearTimeout(intentTimer);
        intentTimer = setTimeout(saveIntent, 1200);
    };
    const resetIntent = () => {
        intentKey = newKey();
        intentFingerprint = '';
        try { sessionStorage.setItem(intentStorage, intentKey); } catch {}
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
        body.set('intent_key', intentKey);
        clearTimeout(intentTimer);
        clearErrors();
        inFlight = true;
        submitted.dataset.state = 'submitting';
        setBusy(true);
        try {
            try {
                const token = await recaptchaToken(submitted);
                if (token) body.set('g-recaptcha-response', token);
            } catch {
                showStatus('captcha');
                submitted.querySelector('[data-contact-status]').focus();
                return;
            }
            const response = await fetch(submitted.action, {
                method: 'POST', body, credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = response.ok || response.status === 422 ? await response.json() : null;
            const current = form();
            if (!current) return;
            if (response.status === 422) {
                showStatus(result.errors?.['g-recaptcha-response'] ? 'captcha' : 'validation');
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
                document.dispatchEvent(new CustomEvent('feat:lead-saved', { detail: { source: 'contact', id: result.id } }));
                resetIntent();
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
        if (event.target.closest('[data-contact-form]')) scheduleIntent();
    });
    document.addEventListener('input', (event) => {
        if (event.target.closest('[data-contact-form]')) scheduleIntent();
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden' && form()) saveIntent();
    });
    /* Browsers can autofill the form without input events. */
    setInterval(() => {
        if (!document.hidden && form()) saveIntent();
    }, 5000);
    initialize();
    document.addEventListener('site:content-updated', initialize);
    document.addEventListener('site:market-changed', () => {
        if (form()) form().elements.namedItem('market').value = document.body.dataset.market;
    });
})();
