@php($measurement = app(\App\Services\MeasurementScripts::class))
@php($cookies = $t['cookies'])
<button id="cookie-trigger" class="cookie-trigger" type="button" data-site-swap data-cookie-open aria-controls="cookie-panel">{{ $cookies['trigger'] }}</button>
<section id="cookie-panel" class="cookie-panel" data-site-swap role="dialog" aria-labelledby="cookie-title" aria-describedby="cookie-description" hidden>
    <div class="cookie-heading"><span>{{ $cookies['kicker'] }}</span><button type="button" data-cookie-close aria-label="{{ $cookies['close'] }}">×</button></div>
    <h2 id="cookie-title">{{ $cookies['title'] }}</h2>
    <p id="cookie-description">{{ $cookies['body'] }}</p>
    <div class="cookie-options" data-cookie-options hidden>
        <label><span><strong>{{ $cookies['essentialTitle'] }}</strong><small>{{ $cookies['essentialBody'] }}</small></span><input type="checkbox" checked disabled></label>
        <label><span><strong>{{ $cookies['analyticsTitle'] }}</strong><small>{{ $cookies['analyticsBody'] }}</small></span><input type="checkbox" name="cookie-analytics"></label>
        <label><span><strong>{{ $cookies['marketingTitle'] }}</strong><small>{{ $cookies['marketingBody'] }}</small></span><input type="checkbox" name="cookie-marketing"></label>
    </div>
    <div class="cookie-actions">
        <button class="cookie-accept" type="button" data-cookie-accept>{{ $cookies['accept'] }}</button>
        <button type="button" data-cookie-reject>{{ $cookies['reject'] }}</button>
        <button type="button" data-cookie-configure>{{ $cookies['configure'] }}</button>
        <button class="cookie-accept" type="button" data-cookie-save hidden>{{ $cookies['save'] }}</button>
    </div>
    <p class="cookie-note">{{ $cookies['note'] }}</p>
</section>
{!! $measurement->withConsent($measurement->withoutDuplicates($siteSettings->custom_body_scripts, $siteSettings)) !!}
@if ($siteSettings->linkedInConfiguration()['enabled'])
    <script>window.FEAT_LINKEDIN = @js($siteSettings->linkedInConfiguration());</script>
    <script type="text/plain" data-feat-consent="marketing" defer data-src="{{ asset('js/tracking-linkedin.js').'?v='.filemtime(public_path('js/tracking-linkedin.js')) }}"></script>
@endif
