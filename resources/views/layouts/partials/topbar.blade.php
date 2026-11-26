{{-- Bilah atas: merek (HP), judul halaman (desktop), penanda sambungan internet, dan inisial pengguna. --}}
@php $user = auth()->user(); @endphp
<header class="topbar">
    <div class="page topbar-inner py-0">
        <a class="brand" href="{{ route($home) }}">
            <span class="brand-mark"><i class="bi bi-water" aria-hidden="true"></i></span>
            <span class="brand-text"><strong>SIMTAG</strong><span>{{ $area }}</span></span>
        </a>
        <div class="topbar-title">{{ $area }} &middot; Sistem Informasi Tambak Garam</div>
        <div class="topbar-actions">
            <span class="sys-pill" data-online role="status"><span class="txt">Tersambung</span></span>
            <span class="avatar d-lg-none" title="{{ $user->name }}" aria-hidden="true">{{ mb_strtoupper(mb_substr(trim($user->name), 0, 1)) }}</span>
        </div>
    </div>
</header>
