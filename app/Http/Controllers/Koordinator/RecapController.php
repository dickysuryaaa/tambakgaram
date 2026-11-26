<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Member;
use App\Models\Pond;
use App\Support\Period;
use App\Support\Stats;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** F-14 / FR-12: Rekap Periode. Selalu menampilkan periode dan sumber data. */
class RecapController extends Controller
{
    public function __invoke(Request $request)
    {
        $period = Period::required($request, 90);
        $memberId = ctype_digit((string) $request->query('anggota')) ? (int) $request->query('anggota') : null;
        $pondScope = fn ($q, string $col = 'pond_id') => $q->when($memberId, fn ($w) => $w->whereIn($col, Pond::where('member_id', $memberId)->select('pond_id')));

        // Produksi per tambak: tambak tanpa panen pada periode tetap tampil dengan 0 panen tercatat.
        $harvestRows = $pondScope($period->whereDate(DB::table('harvests'), 'harvests.harvest_date'), 'harvests.pond_id')
            ->selectRaw('harvests.pond_id, COUNT(*) AS jumlah, SUM('.Stats::HARVEST_KG.') AS kg')
            ->groupBy('harvests.pond_id')->get()->keyBy('pond_id');

        // Pengamatan air: AVG mengabaikan NULL, jadi hanya angka yang benar-benar diukur yang dirata-rata.
        $waterRows = $pondScope($period->whereDateTime(DB::table('water_observations'), 'observed_at'))
            ->selectRaw('pond_id, COUNT(*) AS jumlah, AVG(salinity_value) AS salinitas, AVG(temperature_c) AS suhu, AVG(ph_value) AS ph')
            ->groupBy('pond_id')->get()->keyBy('pond_id');

        $issueQuery = fn () => $pondScope($period->whereDateTime(Issue::query(), 'reported_at'));
        $issueByStatus = $issueQuery()->select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->pluck('total', 'status');
        $issueByCategory = $issueQuery()->select('category', DB::raw('COUNT(*) AS total'))->groupBy('category')->pluck('total', 'category');
        $issues = $issueQuery()->with('pond')->orderByDesc('reported_at')->limit(50)->get();

        $ponds = Pond::with('member')->when($memberId, fn ($q) => $q->where('member_id', $memberId))->orderBy('pond_code')->get();
        $statusCount = $ponds->countBy('operational_status');

        $totalKg = (float) $harvestRows->sum('kg');
        $totalHarvests = (int) $harvestRows->sum('jumlah');

        return view('koordinator.rekap', [
            'period' => $period, 'memberId' => $memberId,
            'members' => Member::where('role', Member::ROLE_PETAMBAK)->orderBy('name')->get(['member_id', 'name']),
            'ponds' => $ponds, 'statusCount' => $statusCount,
            'harvestRows' => $harvestRows, 'totalKg' => $totalKg, 'totalHarvests' => $totalHarvests,
            'waterRows' => $waterRows,
            'issueByStatus' => $issueByStatus, 'issueByCategory' => $issueByCategory, 'issues' => $issues,
            'issueTotal' => $issueByStatus->sum(),
        ]);
    }
}
