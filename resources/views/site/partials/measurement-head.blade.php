@php
    $measurement = app(\App\Services\MeasurementScripts::class);
    $tracking = $measurement->configuration($siteSettings);
    $version = fn (string $file): string => asset($file).'?v='.filemtime(public_path($file));
@endphp
<script>
    window.dataLayer = window.dataLayer || [];
    window.gtag = window.gtag || function () { dataLayer.push(arguments); };
    gtag('consent', 'default', { analytics_storage: 'denied', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
</script>
<script defer src="{{ $version('js/consent.js') }}"></script>
@if ($tracking['tagManagerId'])
    <script type="text/plain" data-feat-consent="all">(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer',@js($tracking['tagManagerId']));</script>
@endif
@if ($tracking['measurementId'])
    <script type="text/plain" data-feat-consent="analytics" data-feat-google-loader="true" async data-src="https://www.googletagmanager.com/gtag/js?id={{ $tracking['measurementId'] }}"></script>
    <script type="text/plain" data-feat-consent="analytics">gtag('js', new Date()); gtag('config', @js($tracking['measurementId']));</script>
    <script>window.FEAT_ANALYTICS = @js(['measurementId' => $tracking['measurementId']]);</script>
    <script type="text/plain" data-feat-consent="analytics" defer data-src="{{ $version('js/tracking-analytics.js') }}"></script>
@endif
@if ($tracking['adsId'])
    <script type="text/plain" data-feat-consent="marketing" data-feat-google-loader="true" async data-src="https://www.googletagmanager.com/gtag/js?id={{ $tracking['adsId'] }}"></script>
    <script type="text/plain" data-feat-consent="marketing">gtag('js', new Date()); gtag('config', @js($tracking['adsId']));</script>
@endif
@if ($tracking['pixelId'])
    <script>window.FEAT_META = @js(['pixelId' => $tracking['pixelId']]);</script>
    <script type="text/plain" data-feat-consent="marketing" defer data-src="{{ $version('js/tracking-meta.js') }}"></script>
@endif
{!! $measurement->withConsent($measurement->withoutDuplicates($siteSettings->custom_head_scripts, $siteSettings)) !!}
@if ($page === 'contact' && $siteSettings->recaptchaIsConfigured())
    <script async defer src="https://www.google.com/recaptcha/api.js?render={{ urlencode($siteSettings->recaptcha_site_key) }}&hl={{ $locale }}"></script>
@endif
