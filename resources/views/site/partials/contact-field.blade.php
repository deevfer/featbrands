<div class="contact-field">
    <label for="contact-{{ $field }}">{{ $copy['fields'][$field] }} @if ($required)<span aria-hidden="true">*</span>@else<span class="contact-optional">({{ $copy['optional'] }})</span>@endif</label>
    <input data-contact-field id="contact-{{ $field }}" type="{{ $type }}" name="{{ $field }}" autocomplete="{{ $autocomplete }}" placeholder="{{ $copy['placeholders'][$field] }}" value="{{ old($field) }}" maxlength="{{ $maxLength }}" @if ($required) required @endif aria-describedby="contact-{{ $field }}-error" @error($field) aria-invalid="true" @enderror>
    <p id="contact-{{ $field }}-error" class="contact-field-error" data-error-for="{{ $field }}" data-error-message="{{ $copy['invalidField'] }}" @if (!$errors->has($field)) hidden @endif>{{ $errors->first($field) }}</p>
</div>
