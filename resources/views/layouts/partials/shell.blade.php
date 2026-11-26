{{--
    Kerangka aplikasi bersama: sidebar (desktop), topbar, dan bottom navigation (HP).
    Perlu: $menu (array [route, label, icon, active]), $bottom (array yang sama, maks 5 item),
    $home (nama route beranda), $area (teks peran), $drawer (bool: tampilkan tombol "Menu" berisi seluruh menu).
--}}
@php
    $user = auth()->user();
    $initial = mb_strtoupper(mb_substr(trim($user->name), 0, 1));
    $drawer = $drawer ?? false;
@endphp

<aside class="sidebar" aria-label="Menu samping">
    <a class="brand" href="{{ route($home) }}">
        <span class="brand-mark"><i class="bi bi-water" aria-hidden="true"></i></span>
        <span class="brand-text"><strong>SIMTAG</strong><span>{{ $area }}</span></span>
    </a>

    <nav class="{{ $navClass ?? 'side-menu' }}" aria-label="Menu utama">
        <p class="side-label">Menu</p>
        <ul class="side-nav">
            @foreach ($menu as [$route, $label, $icon, $active])
                <li>
                    <a href="{{ route($route) }}" class="{{ $active ? 'active' : '' }}" @if ($active) aria-current="page" @endif>
                        <i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="side-foot">
        <div class="user-chip">
            <span class="avatar" aria-hidden="true">{{ $initial }}</span>
            <span class="who"><strong>{{ $user->name }}</strong><span>{{ $area }}</span></span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar</button>
        </form>
    </div>
</aside>

@push('after-shell')
<nav class="bottom-nav" aria-label="Menu bawah">
    @foreach ($bottom as $item)
        @php [$route, $label, $icon, $active] = $item; $fab = $item[4] ?? false; @endphp
        <a href="{{ route($route) }}" class="{{ $active ? 'active' : '' }} {{ $fab ? 'fab' : '' }}" @if ($active) aria-current="page" @endif>
            <i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
        </a>
    @endforeach
    @if ($drawer)
        <button type="button" data-drawer-open="menu-lengkap" aria-haspopup="dialog" aria-controls="menu-lengkap">
            <i class="bi bi-grid" aria-hidden="true"></i><span>Menu</span>
        </button>
    @endif
</nav>

@if ($drawer)
<div class="drawer" id="menu-lengkap" role="dialog" aria-modal="true" aria-labelledby="menu-lengkap-judul">
    <div class="drawer-backdrop" data-drawer-close></div>
    <div class="drawer-panel">
        <div class="drawer-grip" aria-hidden="true"></div>
        <div class="drawer-head">
            <h2 id="menu-lengkap-judul" class="h4 mb-0">Semua Menu</h2>
            <button type="button" class="icon-btn" data-drawer-close aria-label="Tutup menu"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
        <ul class="side-nav mb-3">
            @foreach ($menu as [$route, $label, $icon, $active])
                <li>
                    <a href="{{ route($route) }}" class="{{ $active ? 'active' : '' }}" @if ($active) aria-current="page" @endif>
                        <i class="bi {{ $icon }}" aria-hidden="true"></i><span>{{ $label }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="user-chip mb-3">
            <span class="avatar" aria-hidden="true">{{ $initial }}</span>
            <span class="who"><strong>{{ $user->name }}</strong><span>{{ $area }}</span></span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100"><i class="bi bi-box-arrow-right" aria-hidden="true"></i> Keluar</button>
        </form>
    </div>
</div>
@endif
@endpush
