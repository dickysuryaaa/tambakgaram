<?php

namespace App\Http\Controllers\Petambak;

use App\Models\Harvest;
use App\Support\RecordFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** Riwayat catatan (F-10): per jenis, per tambak, dan per periode. */
class HistoryController extends PetambakController
{
    public function __invoke(Request $request)
    {
        $f = $request->validate([
            'jenis' => ['nullable', Rule::in(array_keys(RecordFeed::TYPES))],
            'tambak' => ['nullable', 'integer'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $ponds = $this->myPonds()->orderBy('pond_code')->get();
        $pondId = ! empty($f['tambak']) && $ponds->contains('pond_id', (int) $f['tambak']) ? (int) $f['tambak'] : null;
        $from = ! empty($f['dari']) ? Carbon::parse($f['dari']) : null;
        $to = ! empty($f['sampai']) ? Carbon::parse($f['sampai']) : null;
        $type = $f['jenis'] ?? null;

        $limit = 50;
        $items = RecordFeed::latest($this->me(), $limit, $type, $pondId, $from, $to);

        // Total panen (kg) pada filter yang dipilih; konversi ton ke kg di sini.
        $totalKg = null;
        if ($type === 'panen') {
            $totalKg = Harvest::whereIn('pond_id', $ponds->pluck('pond_id'))
                ->when($pondId, fn ($q) => $q->where('pond_id', $pondId))
                ->when($from, fn ($q) => $q->where('harvest_date', '>=', $from->toDateString()))
                ->when($to, fn ($q) => $q->where('harvest_date', '<=', $to->toDateString()))
                ->get()->sum->quantity_kg;
        }

        return view('petambak.riwayat.index', [
            'items' => $items, 'ponds' => $ponds, 'type' => $type, 'pondId' => $pondId,
            'dari' => $f['dari'] ?? '', 'sampai' => $f['sampai'] ?? '',
            'limit' => $limit, 'totalKg' => $totalKg,
        ]);
    }
}
