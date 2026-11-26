{{-- Kartu pilihan status tambak. Perlu: $selected. Opsional: $legend. --}}
@php
    $meta = [
        'persiapan' => ['bi-tools', 'ok', 'Tahap 1: menyiapkan lahan'],
        'pengisian_air' => ['bi-water', 'ok', 'Tahap 2: memasukkan air laut'],
        'penguapan' => ['bi-sun', 'ok', 'Tahap 3: air menguap'],
        'kristalisasi_panen' => ['bi-gem', 'ok', 'Tahap 4: garam terbentuk / dipanen'],
        'jeda' => ['bi-pause-circle', 'warn', 'Berhenti sementara'],
        'tidak_aktif' => ['bi-slash-circle', 'mute', 'Tidak dipakai'],
    ];
    $options = collect(\App\Support\Label::pondNames())->map(fn ($v, $k) => [
        'label' => $v, 'icon' => $meta[$k][0] ?? 'bi-circle', 'tone' => $meta[$k][1] ?? 'mute', 'desc' => $meta[$k][2] ?? null,
    ])->all();
@endphp
@include('partials.choice', ['name' => 'operational_status', 'legend' => $legend ?? 'Status tambak', 'options' => $options, 'selected' => $selected, 'cols' => 'cols-2', 'idBase' => 'st'])
