<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Issue;
use App\Models\Member;
use App\Models\Pond;
use App\Support\Period;
use App\Support\RecordFeed;
use App\Support\Stats;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** F-03 / FR-03: urutan komponen mengikuti PRD 7.4. */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        // 1. Filter periode
        $period = Period::required($request, 90);

        // 2. Total anggota dan tambak
        $memberTotal = Member::where('role', Member::ROLE_PETAMBAK)->count();
        $memberActive = Member::where('role', Member::ROLE_PETAMBAK)->where('status', 'aktif')->count();
        $pondTotal = Pond::count();

        // 3. Tambak per status operasional
        $byStatus = Pond::select('operational_status', DB::raw('COUNT(*) AS total'))
            ->groupBy('operational_status')->pluck('total', 'operational_status');

        // 4. Total panen pada periode (konversi ke kg saat rekap)
        $harvests = fn () => $period->whereDate(Harvest::query(), 'harvest_date');
        $harvestKg = (float) $harvests()->sum(DB::raw(Stats::HARVEST_KG));

        // 5. Kendala belum selesai: paling mendesak dulu, lalu yang paling lama menunggu
        $openIssues = Issue::with('pond')->whereIn('status', ['terbuka', 'diproses'])
            ->orderByRaw(Stats::orderBy('priority', ['tinggi', 'sedang', 'rendah']))->orderBy('reported_at')
            ->limit(5)->get();
        $openIssueTotal = Issue::whereIn('status', ['terbuka', 'diproses'])->count();

        // 6. Grafik produksi per bulan
        $perMonth = $harvests()
            ->selectRaw(Stats::yearMonth('harvest_date').' AS ym, SUM('.Stats::HARVEST_KG.') AS kg')
            ->groupBy('ym')->pluck('kg', 'ym');
        $chart = collect($period->months())->map(fn (string $ym) => [
            'label' => Carbon::createFromFormat('Y-m-d', $ym.'-01')->translatedFormat('M Y'),
            'kg' => round((float) ($perMonth[$ym] ?? 0), 2),
        ])->values();

        // 7. Aktivitas terbaru seluruh tambak
        $recent = RecordFeed::latest($request->user(), 8);

        return view('koordinator.dashboard', compact(
            'period', 'memberTotal', 'memberActive', 'pondTotal', 'byStatus', 'harvestKg',
            'openIssues', 'openIssueTotal', 'chart', 'recent'
        ));
    }
}
