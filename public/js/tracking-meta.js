/* Meta Pixel with PageView and Lead; loaded only with marketing consent. */
(() => {
    const id = window.FEAT_META?.pixelId;
    if (!/^[0-9]+$/.test(id || '') || window.FeatMeta || !window.FeatConsent?.allows('marketing')) return;
    if (!window.fbq) {
        const fbq = window.fbq = function (...args) { fbq.callMethod ? fbq.callMethod(...args) : fbq.queue.push(args); };
        fbq.queue = [];
        fbq.loaded = true;
        fbq.version = '2.0';
        window._fbq = fbq;
    }
    // Without autoConfig, typing in the form is never inferred as a Lead.
    window.fbq('consent', 'grant');
    window.fbq('set', 'autoConfig', false, id);
    window.fbq('init', id);
    window.fbq('track', 'PageView');
    if (!document.querySelector('script[src*="connect.facebook.net/en_US/fbevents.js"]')) {
        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://connect.facebook.net/en_US/fbevents.js';
        document.head.appendChild(script);
    }
    const seen = new Set();
    const track = (name, reference) => {
        if (!window.FeatConsent?.allows('marketing') || !/^[1-9][0-9]*$/.test(String(reference || '')) || seen.has(`${name}:${reference}`)) return;
        try {
            window.fbq('track', name, { form_name: 'contact' });
            seen.add(`${name}:${reference}`);
        } catch (error) {}
    };
    window.FeatMeta = { track };
    document.addEventListener('feat:lead-saved', (event) => track('Lead', event.detail?.id));
})();
