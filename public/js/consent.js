/* Cookie consent: optional tags stay as inert templates until the visitor allows their category. */
(() => {
    if (window.FeatConsent) return;
    const cookie = 'feat_consent_v1';
    const lifetime = 180 * 86400;
    let preferences = null;
    let googleLoaded = false;
    let lastFocus = null;
    try {
        const value = document.cookie.split('; ').find((item) => item.startsWith(`${cookie}=`));
        const saved = value ? JSON.parse(decodeURIComponent(value.slice(cookie.length + 1))) : null;
        if (saved?.v === 1 && typeof saved.analytics === 'boolean' && typeof saved.marketing === 'boolean' && Date.now() - saved.at < lifetime * 1000) preferences = saved;
    } catch (error) {}
    const allows = (category) => category === 'analytics' ? preferences?.analytics === true
        : category === 'marketing' ? preferences?.marketing === true
        : category === 'all' ? preferences?.analytics === true && preferences?.marketing === true : false;
    const updateGoogle = () => {
        window.gtag?.('consent', 'update', {
            analytics_storage: allows('analytics') ? 'granted' : 'denied',
            ad_storage: allows('marketing') ? 'granted' : 'denied',
            ad_user_data: allows('marketing') ? 'granted' : 'denied',
            ad_personalization: allows('marketing') ? 'granted' : 'denied',
        });
        if (window.FEAT_ANALYTICS?.measurementId) window[`ga-disable-${window.FEAT_ANALYTICS.measurementId}`] = !allows('analytics');
    };
    const activate = () => {
        updateGoogle();
        document.querySelectorAll('script[type="text/plain"][data-feat-consent]').forEach((template) => {
            if (!allows(template.dataset.featConsent)) return;
            if (template.dataset.featGoogleLoader && googleLoaded) {
                template.remove();
                return;
            }
            const script = document.createElement('script');
            for (const attribute of template.attributes) {
                if (!['type', 'data-src', 'data-feat-consent', 'data-feat-google-loader'].includes(attribute.name)) script.setAttribute(attribute.name, attribute.value);
            }
            if (template.dataset.src) {
                script.async = template.hasAttribute('async');
                script.src = template.dataset.src;
            } else {
                script.textContent = template.textContent;
            }
            if (template.dataset.featGoogleLoader) googleLoaded = true;
            template.replaceWith(script);
        });
    };
    const clearOptionalCookies = () => {
        const names = document.cookie.split(';').map((item) => item.split('=')[0].trim()).filter((name) => /^(_ga|_gid|_gat|_gcl|_fbp|_fbc|li_gc|li_sugr|bcookie|bscookie)/.test(name));
        for (const name of names) {
            for (const domain of ['', location.hostname, `.${location.hostname.replace(/^www\./, '')}`]) {
                document.cookie = `${name}=; Max-Age=0; Path=/; SameSite=Lax${domain ? `; Domain=${domain}` : ''}; Secure`;
            }
        }
    };
    const panel = () => document.getElementById('cookie-panel');
    const close = () => {
        if (panel()) panel().hidden = true;
        lastFocus?.focus?.();
    };
    const open = (configure = true) => {
        const current = panel();
        if (!current) return;
        lastFocus = document.activeElement;
        current.hidden = false;
        current.querySelector('[data-cookie-options]').hidden = !configure;
        current.querySelector('[data-cookie-save]').hidden = !configure;
        current.querySelector('[data-cookie-configure]').hidden = configure;
        current.querySelector('[name="cookie-analytics"]').checked = allows('analytics');
        current.querySelector('[name="cookie-marketing"]').checked = allows('marketing');
        current.querySelector('button')?.focus();
    };
    const save = (analytics, marketing) => {
        const revoked = (allows('analytics') && !analytics) || (allows('marketing') && !marketing);
        preferences = { v: 1, analytics: analytics === true, marketing: marketing === true, at: Date.now() };
        document.cookie = `${cookie}=${encodeURIComponent(JSON.stringify(preferences))}; Path=/; Max-Age=${lifetime}; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
        updateGoogle();
        if (!marketing) window.fbq?.('consent', 'revoke');
        document.dispatchEvent(new CustomEvent('feat:consent-changed', { detail: { analytics: allows('analytics'), marketing: allows('marketing') } }));
        close();
        if (revoked) {
            clearOptionalCookies();
            location.reload();
            return;
        }
        activate();
    };
    window.FeatConsent = { allows, open, save, preferences: () => preferences ? { ...preferences } : null };
    const init = () => {
        document.addEventListener('click', (event) => {
            if (event.target.closest('[data-cookie-open], [data-cookie-configure]')) open();
            if (event.target.closest('[data-cookie-close]')) close();
            if (event.target.closest('[data-cookie-accept]')) save(true, true);
            if (event.target.closest('[data-cookie-reject]')) save(false, false);
            if (event.target.closest('[data-cookie-save]')) save(panel().querySelector('[name="cookie-analytics"]').checked, panel().querySelector('[name="cookie-marketing"]').checked);
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && panel() && !panel().hidden) close();
        });
        activate();
        if (!preferences) open(false);
    };
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
    else init();
})();
