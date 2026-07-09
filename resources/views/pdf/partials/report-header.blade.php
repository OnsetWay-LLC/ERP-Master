<div class="report-header">

    @if($company->logo)
        <div class="report-logo">
            <img
                src="{{ public_path('storage/' . $company->logo) }}"
                alt="Company Logo"
            >
        </div>
    @endif

    @if($company->report_header)
        <div class="custom-report-header">
            {!! nl2br(e($company->report_header)) !!}
        </div>
    @endif

</div>