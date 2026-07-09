@if($company->report_footer)
    <div class="custom-report-footer">
        {!! nl2br(e($company->report_footer)) !!}
    </div>
@endif