{{--
    Daftar petak tambak interaktif: kartu ringkasan (opsional), pencarian instan, tab filter, tampilan kartu/tabel,
    progress kesiapan panen, dan panel detail. Perlu: $ponds (koleksi Pond dengan open_issues_count).
    Opsional: $showStats. Hanya membaca data yang sudah dimuat controller; tidak ada query baru.
--}}
@php
    use App\Support\Label;
    $showStats = $showStats ?? false;
    $stages = ['persiapan' => 1, 'pengisian_air' => 2, 'penguapan' => 3, 'kristalisasi_panen' => 4];
    $rows = $ponds->map(function ($p) use ($stages) {
        $inactive = in_array($p->operational_status, Label::INACTIVE_PONDS, true);
        $issues = (int) $p->open_issues_count;
        if ($inactive) {
            $tags = ['nonaktif', $p->operational_status];
            $tone = $p->operational_status === 'jeda' ? 'warning' : 'secondary';
        } elseif ($issues > 0) {
            $tags = ['kendala'];
            $tone = 'danger';
        } else {
            $tags = ['aktif'];
            $tone = 'success';
        }
        [$label, $color] = Label::pond($p->operational_status);
        $step = $stages[$p->operational_status] ?? 0;

        return (object) [
            'pond' => $p, 'tags' => $tags, 'tone' => $tone, 'issues' => $issues,
            'label' => $label, 'color' => $color, 'step' => $step, 'pct' => $step * 25,
            'stage' => $step ? 'Tahap '.$step.' dari 4: '.$label : $label.' (tidak ada kegiatan produksi)',
            'name' => $p->pond_name ?: $p->pond_code,
        ];
    });
    $count = fn ($tag) => $rows->filter(fn ($r) => in_array($tag, $r->tags, true))->count();
    $nAktif = $count('aktif'); $nKendala = $count('kendala'); $nNon = $count('nonaktif');
    $nJeda = $count('jeda'); $nTidak = $count('tidak_aktif');
@endphp

@if ($showStats)
    <div class="stat-grid mb-4" role="group" aria-label="Status Tambak">
        <button type="button" class="stat-btn stat-semua" data-filter="semua" aria-pressed="true" aria-label="{{ $rows->count() }} Semua Tambak">
            <span class="lbl"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>Semua</span>
            <span class="num">{{ $rows->count() }}</span>
            <span class="hint">petak tambak</span>
        </button>
        <button type="button" class="stat-btn stat-aktif" data-filter="aktif" aria-pressed="false" aria-label="{{ $nAktif }} Aktif">
            <span class="lbl"><i class="bi bi-check-circle" aria-hidden="true"></i>Aktif</span>
            <span class="num">{{ $nAktif }}</span>
            <span class="hint">berjalan lancar</span>
        </button>
        <button type="button" class="stat-btn stat-kendala" data-filter="kendala" aria-pressed="false" aria-label="{{ $nKendala }} Perlu Perhatian, Kendala">
            <span class="lbl"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i>Kendala</span>
            <span class="num">{{ $nKendala }}</span>
            <span class="hint">perlu perhatian</span>
        </button>
        <button type="button" class="stat-btn stat-nonaktif" data-filter="nonaktif" aria-pressed="false" aria-label="{{ $nNon }} Jeda atau Tidak Aktif">
            <span class="lbl"><i class="bi bi-pause-circle" aria-hidden="true"></i>Jeda</span>
            <span class="num">{{ $nNon }}</span>
            <span class="hint">jeda / tidak aktif</span>
        </button>
    </div>
@endif

<section data-pond-board aria-labelledby="judul-daftar-tambak">
    <div class="card-title-row">
        <h2 id="judul-daftar-tambak" class="h4 section-title"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i>Daftar Petak Tambak</h2>
        @unless ($rows->isEmpty())
            <div class="view-toggle" role="group" aria-label="Pilih tampilan daftar">
                <button type="button" data-view="kartu" aria-pressed="true"><i class="bi bi-grid" aria-hidden="true"></i>Kartu</button>
                <button type="button" data-view="tabel" aria-pressed="false"><i class="bi bi-list-ul" aria-hidden="true"></i>Tabel</button>
            </div>
        @endunless
    </div>

    @if ($rows->isEmpty())
        <div class="card empty-state">
            <i class="bi bi-inbox" aria-hidden="true"></i>
            <p class="fw-semibold">Anda belum memiliki tambak. Silakan hubungi koordinator untuk menambahkan tambak Anda.</p>
        </div>
    @else
        <div class="filter-bar mb-3">
            <div class="search-box">
                <label for="cari-tambak" class="visually-hidden">Cari nama atau kode tambak</label>
                <i class="bi bi-search" aria-hidden="true"></i>
                <input id="cari-tambak" type="search" class="form-control" placeholder="Cari nama atau kode tambak" autocomplete="off">
                <button type="button" id="cari-hapus" class="clear" hidden aria-label="Hapus pencarian"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </div>
            <div class="chip-row" role="group" aria-label="Saring tambak">
                <button type="button" class="chip" data-filter="semua" aria-pressed="true">Semua <span class="count">{{ $rows->count() }}</span></button>
                <button type="button" class="chip" data-filter="aktif" aria-pressed="false"><i class="bi bi-check-circle" aria-hidden="true"></i>Aktif <span class="count">{{ $nAktif }}</span></button>
                <button type="button" class="chip" data-filter="jeda" aria-pressed="false"><i class="bi bi-pause-circle" aria-hidden="true"></i>Jeda <span class="count">{{ $nJeda }}</span></button>
                <button type="button" class="chip" data-filter="kendala" aria-pressed="false"><i class="bi bi-exclamation-octagon" aria-hidden="true"></i>Kendala <span class="count">{{ $nKendala }}</span></button>
                <button type="button" class="chip" data-filter="tidak_aktif" aria-pressed="false"><i class="bi bi-slash-circle" aria-hidden="true"></i>Tidak Aktif <span class="count">{{ $nTidak }}</span></button>
            </div>
            <div id="hasil-cari" class="result-note" aria-live="polite"></div>
        </div>

        {{-- Tampilan kartu --}}
        <div class="pond-list" data-view-pane="kartu">
            @foreach ($rows as $r)
                @php $p = $r->pond; @endphp
                <article class="card pond-item tone-{{ $r->tone }}" data-tags="{{ implode(' ', $r->tags) }}"
                         data-search="{{ $r->name }} {{ $p->pond_code }} {{ $r->label }}"
                         data-id="{{ $p->pond_id }}" data-name="{{ $r->name }}" data-code="{{ $p->pond_code }}"
                         data-area="{{ number_format($p->area_m2, 0, ',', '.') }}" data-stage="{{ $r->stage }}"
                         data-issues="{{ $r->issues }}" data-show-url="{{ route('ponds.show', $p) }}">
                    <div class="pond-head">
                        <span class="pond-avatar"><i class="bi bi-water" aria-hidden="true"></i></span>
                        <button type="button" class="pond-open" data-open="detail" aria-label="Lihat ringkasan {{ $r->name }}">
                            <span class="pond-name d-block">{{ $r->name }}</span>
                            <span class="pond-meta d-block">Kode {{ $p->pond_code }} &middot; {{ number_format($p->area_m2, 0, ',', '.') }} m&sup2;</span>
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2" data-badge>
                        @include('partials.status', ['label' => $r->label, 'color' => $r->color])
                        @if ($r->issues > 0)
                            @include('partials.status', ['label' => 'Ada Kendala', 'color' => 'danger'])
                        @endif
                    </div>
                    <div>
                        <div class="readiness-top">
                            <span class="t">Kesiapan panen</span>
                            <span class="p">{{ $r->pct }}%</span>
                        </div>
                        <div class="meter {{ $r->step === 4 ? 'ok' : ($r->step === 0 ? 'off' : '') }}" role="progressbar" aria-label="Kesiapan panen {{ $r->name }}"
                             aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $r->pct }}" aria-valuetext="{{ $r->stage }}">
                            <span style="width: {{ $r->pct }}%"></span>
                        </div>
                        <div class="steps-text">{{ $r->stage }}</div>
                    </div>
                    <div class="pond-actions">
                        <button type="button" class="btn btn-primary" data-open="panen"><i class="bi bi-basket2" aria-hidden="true"></i>Catat Panen</button>
                        <button type="button" class="btn btn-danger-soft" data-open="kendala"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Lapor Kendala</button>
                        <a href="{{ route('ponds.show', $p) }}" class="btn btn-outline-secondary btn-detail"><i class="bi bi-card-list" aria-hidden="true"></i>Lihat Detail Tambak</a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Tampilan tabel --}}
        <div class="card table-card pond-table-wrap" data-view-pane="tabel" hidden>
            <div class="table-responsive">
                <table class="table">
                    <caption class="visually-hidden">Daftar petak tambak</caption>
                    <thead>
                        <tr><th scope="col">Tambak</th><th scope="col">Status</th><th scope="col">Kesiapan panen</th><th scope="col" class="table-num">Luas (m&sup2;)</th><th scope="col" class="table-num">Kendala</th><th scope="col">Aksi</th></tr>
                    </thead>
                    <tbody>
                    @foreach ($rows as $r)
                        @php $p = $r->pond; @endphp
                        <tr class="pond-row" data-tags="{{ implode(' ', $r->tags) }}" data-search="{{ $r->name }} {{ $p->pond_code }} {{ $r->label }}">
                            <td><span class="cell-title">{{ $r->name }}</span><span class="cell-sub">Kode {{ $p->pond_code }}</span></td>
                            <td>@include('partials.status', ['label' => $r->label, 'color' => $r->color])</td>
                            <td style="min-width: 160px;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="meter flex-grow-1 {{ $r->step === 4 ? 'ok' : ($r->step === 0 ? 'off' : '') }}" aria-hidden="true"><span style="width: {{ $r->pct }}%"></span></div>
                                    <span class="tabular fw-bold">{{ $r->pct }}%</span>
                                </div>
                            </td>
                            <td class="table-num">{{ number_format($p->area_m2, 0, ',', '.') }}</td>
                            <td class="table-num">{{ $r->issues > 0 ? $r->issues.' belum selesai' : 'Tidak ada' }}</td>
                            <td><a href="{{ route('ponds.show', $p) }}" class="btn btn-outline-primary text-nowrap">Detail</a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="kosong-cari" class="card empty-state mt-3" hidden>
            <i class="bi bi-search" aria-hidden="true"></i>
            <p class="fw-semibold">Tidak ada tambak yang cocok. Coba pilih &quot;Semua&quot; atau ubah kata pencarian.</p>
        </div>
    @endif
</section>

@if (! $rows->isEmpty())
<dialog id="panel-tambak" class="sheet" aria-labelledby="panel-judul">
    <div class="sheet-head">
        <div>
            <h2 id="panel-judul" class="h4 mb-0" data-f="nama"></h2>
            <div class="pond-meta" data-f="kode"></div>
            <div class="mt-2 d-flex flex-wrap gap-2" data-f="status"></div>
        </div>
        <button type="button" class="sheet-close" data-close aria-label="Tutup"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </div>
    <div class="sheet-body">
        <dl class="sheet-facts">
            <div><dt>Luas</dt><dd data-f="luas"></dd></div>
            <div><dt>Kendala</dt><dd data-f="kendala"></dd></div>
        </dl>
        <p class="sheet-note" data-f="tahap"></p>
        <p class="sheet-note">Pilih yang ingin Anda catat. Tambak ini sudah terpilih di formulir.</p>
        <div class="sheet-actions">
            <a class="btn btn-outline-primary" data-act="panen" data-base="{{ route('catat.panen') }}"><i class="bi bi-basket2" aria-hidden="true"></i>Catat Panen</a>
            <a class="btn btn-outline-primary" data-act="air" data-base="{{ route('catat.air') }}"><i class="bi bi-droplet-half" aria-hidden="true"></i>Catat Air</a>
            <a class="btn btn-outline-primary" data-act="kendala" data-base="{{ route('catat.kendala') }}"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i>Lapor Kendala</a>
            <a class="btn btn-outline-primary" data-act="detail"><i class="bi bi-card-list" aria-hidden="true"></i>Lihat Semua Data</a>
        </div>
    </div>
</dialog>
@endif
