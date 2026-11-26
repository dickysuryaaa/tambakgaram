<?php

namespace App\Support;

use App\Models\Harvest;
use App\Models\Issue;
use App\Models\Member;
use App\Models\Pond;
use App\Models\ProductionActivity;
use App\Models\WaterObservation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Gabungan catatan (panen, air, kendala, kegiatan), dibatasi ke tambak yang boleh dilihat
 * anggota (petambak: miliknya; koordinator_admin: semua).
 */
class RecordFeed
{
    public const TYPES = [
        'panen' => 'Panen',
        'air' => 'Pengamatan Air',
        'kendala' => 'Kendala',
        'kegiatan' => 'Kegiatan',
    ];

    /** @return Collection<int, array<string,mixed>> */
    public static function latest(Member $member, int $limit = 8, ?string $type = null, ?int $pondId = null, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $pondIds = Pond::visibleTo($member)->when($pondId, fn ($q) => $q->where('pond_id', $pondId))->pluck('pond_id');
        $range = fn ($q, string $col) => $q
            ->when($from, fn ($q) => $q->where($col, '>=', $from->copy()->startOfDay()))
            ->when($to, fn ($q) => $q->where($col, '<=', $to->copy()->endOfDay()));
        $want = fn (string $t) => $type === null || $type === $t;

        $items = collect();

        if ($want('panen')) {
            $range(Harvest::with('pond')->whereIn('pond_id', $pondIds), 'harvest_date')
                ->latest('harvest_date')->latest('harvest_id')->limit($limit)->get()
                ->each(fn (Harvest $h) => $items->push([
                    'type' => 'panen', 'icon' => 'bi-basket2', 'title' => 'Panen',
                    'pond' => $h->pond, 'when' => $h->harvest_date->copy()->startOfDay(), 'date_only' => true,
                    'summary' => self::num($h->quantity).' '.$h->unit
                        .($h->unit === 'ton' ? ' ('.number_format($h->quantity_kg, 0, ',', '.').' kg)' : ''),
                    'badge' => null, 'note' => $h->notes,
                    'sort' => $h->harvest_date->copy()->setTime(12, 0)->timestamp + $h->harvest_id / 1e6,
                ]));
        }

        if ($want('air')) {
            $range(WaterObservation::with('pond')->whereIn('pond_id', $pondIds), 'observed_at')
                ->latest('observed_at')->latest('observation_id')->limit($limit)->get()
                ->each(function (WaterObservation $o) use ($items) {
                    $parts = [];
                    if ($o->salinity_value !== null) {
                        $parts[] = 'Salinitas '.self::num($o->salinity_value).' °Bé';
                    }
                    if ($o->temperature_c !== null) {
                        $parts[] = 'Suhu '.self::num($o->temperature_c).' °C';
                    }
                    if ($o->ph_value !== null) {
                        $parts[] = 'pH '.self::num($o->ph_value);
                    }
                    $items->push([
                        'type' => 'air', 'icon' => 'bi-droplet-half', 'title' => 'Pengamatan Air',
                        'pond' => $o->pond, 'when' => $o->observed_at, 'date_only' => false,
                        'summary' => $parts ? implode(', ', $parts) : 'Tidak ada angka yang diisi',
                        'badge' => null, 'note' => $o->notes,
                        'sort' => $o->observed_at->timestamp + $o->observation_id / 1e6,
                    ]);
                });
        }

        if ($want('kendala')) {
            $range(Issue::with('pond')->whereIn('pond_id', $pondIds), 'reported_at')
                ->latest('reported_at')->latest('issue_id')->limit($limit)->get()
                ->each(fn (Issue $i) => $items->push([
                    'type' => 'kendala', 'icon' => 'bi-exclamation-triangle', 'title' => 'Kendala: '.(Label::CATEGORIES[$i->category] ?? $i->category),
                    'pond' => $i->pond, 'when' => $i->reported_at, 'date_only' => false,
                    'summary' => $i->description,
                    'badge' => Label::issue($i->status), 'note' => $i->follow_up_notes ? 'Tindak lanjut: '.$i->follow_up_notes : null,
                    'sort' => $i->reported_at->timestamp + $i->issue_id / 1e6,
                ]));
        }

        if ($want('kegiatan')) {
            $range(ProductionActivity::with('cycle.pond')
                ->whereHas('cycle', fn ($q) => $q->whereIn('pond_id', $pondIds)), 'activity_date')
                ->latest('activity_date')->latest('activity_id')->limit($limit)->get()
                ->each(fn (ProductionActivity $a) => $items->push([
                    'type' => 'kegiatan', 'icon' => 'bi-tools', 'title' => Label::activity($a->activity_type),
                    'pond' => $a->cycle->pond, 'when' => $a->activity_date->copy()->startOfDay(), 'date_only' => true,
                    'summary' => $a->description ?: '-',
                    'badge' => null, 'note' => null,
                    'sort' => $a->activity_date->copy()->setTime(12, 0)->timestamp + $a->activity_id / 1e6,
                ]));
        }

        return $items->sortByDesc('sort')->take($limit)->values();
    }

    public static function num($v): string
    {
        $s = number_format((float) $v, 2, ',', '.');

        return str_contains($s, ',') ? rtrim(rtrim($s, '0'), ',') : $s;
    }
}
