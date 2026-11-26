<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Member;
use App\Models\Pond;
use App\Models\ProductionCycle;
use App\Models\WaterObservation;
use App\Support\Label;
use App\Support\Period;
use App\Support\Stats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Daftar baca-saja untuk seluruh tambak: siklus dan kegiatan, panen, pengamatan air (F-07 s.d. F-11, F-13). */
class RecordListController extends Controller
{
    public function cycles(Request $request)
    {
        $period = Period::optional($request);
        $f = [
            'tambak' => $request->query('tambak'),
            'anggota' => $request->query('anggota'),
            'status' => $request->query('status'),
        ];

        $cycles = ProductionCycle::with(['pond.member', 'activities' => fn ($q) => $q->with('recorder')->orderByDesc('activity_date')])
            ->when(ctype_digit((string) $f['tambak']), fn ($q) => $q->where('pond_id', $f['tambak']))
            ->when(ctype_digit((string) $f['anggota']), fn ($q) => $q->whereHas('pond', fn ($p) => $p->where('member_id', $f['anggota'])))
            ->when(array_key_exists((string) $f['status'], Label::CYCLES), fn ($q) => $q->where('cycle_status', $f['status']));
        // Siklus ikut tampil bila masa berjalannya beririsan dengan periode.
        $cycles = $cycles
            ->when($period->from, fn ($q) => $q->where(fn ($w) => $w->whereNull('end_date')->orWhere('end_date', '>=', $period->from->toDateString())))
            ->when($period->to, fn ($q) => $q->where('start_date', '<=', $period->to->toDateString()))
            ->orderByDesc('start_date')->paginate(10)->withQueryString();

        return view('koordinator.catatan.siklus', ['cycles' => $cycles, 'period' => $period, 'f' => $f] + $this->options());
    }

    public function harvests(Request $request)
    {
        $period = Period::optional($request);
        $f = ['tambak' => $request->query('tambak'), 'anggota' => $request->query('anggota')];

        $query = Harvest::with(['pond.member', 'recorder'])
            ->when(ctype_digit((string) $f['tambak']), fn ($q) => $q->where('pond_id', $f['tambak']))
            ->when(ctype_digit((string) $f['anggota']), fn ($q) => $q->whereHas('pond', fn ($p) => $p->where('member_id', $f['anggota'])));
        $query = $period->whereDate($query, 'harvest_date');

        $totalKg = (float) (clone $query)->sum(DB::raw(Stats::HARVEST_KG));
        $harvests = $query->orderByDesc('harvest_date')->orderByDesc('harvest_id')->paginate(15)->withQueryString();

        return view('koordinator.catatan.panen', ['harvests' => $harvests, 'totalKg' => $totalKg, 'period' => $period, 'f' => $f] + $this->options());
    }

    public function waters(Request $request)
    {
        $period = Period::optional($request);
        $f = ['tambak' => $request->query('tambak'), 'anggota' => $request->query('anggota')];

        $query = WaterObservation::with(['pond.member', 'recorder'])
            ->when(ctype_digit((string) $f['tambak']), fn ($q) => $q->where('pond_id', $f['tambak']))
            ->when(ctype_digit((string) $f['anggota']), fn ($q) => $q->whereHas('pond', fn ($p) => $p->where('member_id', $f['anggota'])));
        $observations = $period->whereDateTime($query, 'observed_at')
            ->orderByDesc('observed_at')->orderByDesc('observation_id')->paginate(15)->withQueryString();

        return view('koordinator.catatan.air', ['observations' => $observations, 'period' => $period, 'f' => $f] + $this->options());
    }

    private function options(): array
    {
        return [
            'ponds' => Pond::orderBy('pond_code')->get(['pond_id', 'pond_code', 'pond_name']),
            'members' => Member::where('role', Member::ROLE_PETAMBAK)->orderBy('name')->get(['member_id', 'name']),
        ];
    }
}
