<a href="{{ route('catat') }}" class="back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Kembali ke menu Catat</a>
<div class="page-head">
    <div>
        <h1>{{ $heading }}</h1>
        @isset($lead)<p class="lead-text">{{ $lead }}</p>@endisset
    </div>
</div>
