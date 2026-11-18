/* GA4 lead event for the contact form; loaded only with analytics consent. */
(() => {
    const measurementId = window.FEAT_ANALYTICS?.measurementId;
    if (!/^G-[A-Z0-9]+$/.test(measurementId || '') || window.FeatAnalytics) return;
    const savedLeads = new Set();
    const trackLead = ({ id } = {}) => {
        if (!/^[1-9][0-9]*$/.test(String(id || '')) || savedLeads.has(id) || typeof window.gtag !== 'function') return;
        try {
            // The ID only deduplicates locally; form values and IDs are never sent to GA4.
            window.gtag('event', 'generate_lead', { send_to: measurementId, form_name: 'contact', lead_type: 'contact_submitted' });
            savedLeads.add(id);
        } catch (error) {}
    };
    window.FeatAnalytics = { trackLead };
    document.addEventListener('feat:lead-saved', (event) => trackLead(event.detail));
})();
