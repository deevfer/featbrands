@extends('site.layout')

@section('content')
<main id="main-content" class="specialization contact-page">
    <div class="contact-workspace service-width">
        <aside class="contact-intro">
            <p class="service-eyebrow">{{ $copy['name'] }} / {{ $copy['asideKicker'] }}</p>
            <h1>{{ $copy['title'] }} <em>{{ $copy['accent'] }}</em></h1>
            <p class="contact-intro-body">{{ $copy['intro'] }}</p>
            <p class="contact-intro-note">{{ $copy['note'] }}</p>
            <span class="contact-region">{{ $copy['sideFoot'] }}</span>
        </aside>
        <section id="solicitud" class="contact-form-panel">
            <p class="service-eyebrow">{{ $copy['kicker'] }}</p>
            <h2>{{ $copy['formTitle'] }}</h2>
            <p class="contact-form-intro">{{ $copy['formIntro'] }}</p>
            <p class="contact-required-note">{{ $copy['requiredNote'] }}</p>
            <form data-contact-form data-state="{{ session('contact_submitted') ? 'success' : 'idle' }}" action="{{ route('contact.store', ['lang' => $locale, 'market' => $market]) }}" method="POST" @if (session('contact_submitted')) hidden @endif>
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">
                <input type="hidden" name="market" value="{{ $market }}">
                <div class="contact-honeypot" aria-hidden="true"><label for="contact-website">Website</label><input id="contact-website" type="text" name="website" tabindex="-1" autocomplete="off"></div>
                <fieldset><legend><span>01</span>{{ $copy['personal'] }}</legend><div class="contact-fields">
                    @foreach (['first_name', 'last_name', 'email', 'phone', 'company', 'job_title'] as $field)
                        @if ($field === 'phone')
                            @php
                                $selectedCountry = old('phone_country');
                                if (!$selectedCountry) {
                                    $oldPrefix = old('country_code');
                                    $selectedCountry = $oldPrefix
                                        ? (collect($phoneCountries)->firstWhere('dial_code', $oldPrefix)['iso'] ?? strtoupper($market))
                                        : strtoupper($market);
                                }
                            @endphp
                            <div class="contact-field contact-phone-field">
                                <label for="contact-phone">{{ $copy['fields']['phone'] }} <span aria-hidden="true">*</span></label>
                                <div class="contact-phone-inputs">
                                    <div class="contact-country-picker" data-country-picker>
                                        <label class="contact-visually-hidden" for="contact-phone_country">{{ $copy['fields']['phone_country'] }}</label>
                                        <select data-contact-field data-phone-country id="contact-phone_country" name="phone_country" required aria-describedby="contact-phone-hint contact-country_code-error contact-phone_country-error" @if ($errors->hasAny(['phone_country', 'country_code'])) aria-invalid="true" @endif>
                                            @foreach ($phoneCountries as $country)
                                                <option value="{{ $country['iso'] }}" data-dial-code="{{ $country['dial_code'] }}" data-flag="{{ $country['flag'] }}" @selected($selectedCountry === $country['iso'])>{{ $country['name'][$locale] }} {{ $country['flag'] }} {{ $country['dial_code'] }}</option>
                                            @endforeach
                                        </select>
                                        <span class="contact-country-label" data-country-label aria-hidden="true"><span class="contact-country-flag" data-country-flag></span><span data-country-prefix></span><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m3 4.5 3 3 3-3"/></svg></span>
                                        <input data-contact-field type="hidden" id="contact-country_code" name="country_code" value="{{ old('country_code', ['gt' => '+502', 'cr' => '+506', 'us' => '+1'][$market]) }}">
                                    </div>
                                    <input data-contact-field id="contact-phone" type="tel" name="phone" autocomplete="tel-national" placeholder="{{ $copy['placeholders']['phone'] }}" value="{{ old('phone') }}" required pattern="[0-9][0-9 \(\)\-]{3,24}" maxlength="25" aria-describedby="contact-phone-hint contact-phone-error" @error('phone') aria-invalid="true" @enderror>
                                </div>
                                <p class="contact-field-hint" id="contact-phone-hint">{{ $copy['phoneHint'] }}</p>
                                @foreach (['country_code', 'phone_country', 'phone'] as $phoneField)
                                    <p id="contact-{{ $phoneField }}-error" class="contact-field-error" data-error-for="{{ $phoneField }}" data-error-message="{{ $copy['validation']['phone'] }}" @if (!$errors->has($phoneField)) hidden @endif>{{ $errors->first($phoneField) }}</p>
                                @endforeach
                            </div>
                        @else
                            @include('site.partials.contact-field', ['field' => $field, 'required' => $field !== 'job_title', 'type' => $field === 'email' ? 'email' : 'text', 'maxLength' => ['first_name' => 100, 'last_name' => 100, 'email' => 254, 'company' => 150, 'job_title' => 120][$field], 'autocomplete' => ['first_name' => 'given-name', 'last_name' => 'family-name', 'email' => 'email', 'company' => 'organization', 'job_title' => 'organization-title'][$field]])
                        @endif
                    @endforeach
                </div></fieldset>
                <fieldset><legend><span>02</span>{{ $copy['project'] }}</legend><div class="contact-fields">
                    @include('site.partials.contact-field', ['field' => 'budget', 'required' => false, 'type' => 'text', 'maxLength' => 120, 'autocomplete' => 'off'])
                    <div class="contact-field"><label for="contact-referral_source">{{ $copy['fields']['referral_source'] }} <span aria-hidden="true">*</span></label><select data-contact-field id="contact-referral_source" name="referral_source" required aria-describedby="contact-referral_source-error" @error('referral_source') aria-invalid="true" @enderror><option value="">{{ $copy['choose'] }}</option>@foreach ($copy['referrals'] as $value => $label)<option value="{{ $value }}" @selected(old('referral_source') === $value)>{{ $label }}</option>@endforeach</select><p id="contact-referral_source-error" class="contact-field-error" data-error-for="referral_source" data-error-message="{{ $copy['invalidField'] }}" @if (!$errors->has('referral_source')) hidden @endif>{{ $errors->first('referral_source') }}</p></div>
                    <div class="contact-field contact-field-wide"><label for="contact-message">{{ $copy['fields']['message'] }} <span aria-hidden="true">*</span></label><textarea data-contact-field id="contact-message" name="message" rows="6" placeholder="{{ $copy['placeholders']['message'] }}" required minlength="20" maxlength="5000" aria-describedby="contact-message-hint contact-message-error" @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea><p class="contact-field-hint" id="contact-message-hint">{{ $copy['messageHint'] }}</p><p id="contact-message-error" class="contact-field-error" data-error-for="message" data-error-message="{{ $copy['invalidField'] }}" @if (!$errors->has('message')) hidden @endif>{{ $errors->first('message') }}</p></div>
                </div></fieldset>
                <div class="contact-form-status" data-contact-status role="status" tabindex="-1" data-validation="{{ $copy['errorTitle'] }}" data-network="{{ $copy['networkError'] }}" data-rate-limit="{{ $copy['rateLimit'] }}" data-expired="{{ $copy['expired'] }}" @if ($errors->isEmpty()) hidden @endif>{{ $errors->isNotEmpty() ? $copy['errorTitle'] : '' }}</div>
                <button class="contact-submit" type="submit" data-contact-submit data-label="{{ $copy['send'] }}" data-sending="{{ $copy['sending'] }}"><span>{{ $copy['send'] }}</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M5 19 19 5M5 5h14v14"/></svg></button>
            </form>
            <div class="contact-success" data-contact-success role="status" tabindex="-1" @if (!session('contact_submitted')) hidden @endif><span aria-hidden="true">↗</span><h3>{{ $copy['successTitle'] }}</h3><p>{{ $copy['success'] }}</p><button type="button" data-contact-again>{{ $copy['again'] }} →</button></div>
        </section>
    </div>
</main>
@endsection
