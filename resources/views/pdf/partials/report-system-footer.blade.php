@php
    $locale = app()->getLocale();

    $systemFooterImage = $locale === 'ar'
        ? public_path('images/reports/system-footer-ar.png')
        : public_path('images/reports/system-footer-en.png');
@endphp

<div class="system-report-footer">
    <img
        src="{{ $systemFooterImage }}"
        alt="System Footer"
    >
</div>