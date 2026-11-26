<?php

namespace App\Http\Controllers\Petambak;

use App\Models\Harvest;
use App\Models\ProductionCycle;
use App\Support\Label;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PondController extends PetambakController
{
    public function index()
    {
        $ponds = $this->myPonds()
            ->withCount(['issues as open_issues_count' => fn ($q) => $q->whereIn('status', ['terbuka', 'diproses'])])
            ->orderBy('pond_code')->get();

        return view('petambak.tambak.index', ['ponds' => $ponds]);
    }

    public function show(string $pond)
    {
        $pond = $this->myPond($pond);

        $cycles = $pond->productionCycles()->withCount('activities')->orderByDesc('start_date')->get();
        $harvestKg = Harvest::where('pond_id', $pond->pond_id)->get()->sum->quantity_kg;
        $latestObs = $pond->waterObservations()->latest('observed_at')->first();
        $openIssues = $pond->issues()->whereIn('status', ['terbuka', 'diproses'])->latest('reported_at')->get();

        return view('petambak.tambak.show', compact('pond', 'cycles', 'harvestKg', 'latestObs', 'openIssues'));
    }

    /** F-06: halaman ubah status operasional. */
    public function statusForm(Request $request)
    {
        return view('petambak.catat.status', [
            'ponds' => $this->myPonds()->orderBy('pond_code')->get(),
            'selected' => $request->query('tambak'),
        ]);
    }

    public function updateStatus(Request $request)
    {
        $data = $request->validate([
            'pond_id' => $this->pondRule(),
            'operational_status' => ['required', Rule::in(array_keys(Label::PONDS))],
        ]);

        $pond = $this->myPond($data['pond_id']);
        $pond->update(['operational_status' => $data['operational_status']]);

        return redirect()->route('ponds.show', $pond)
            ->with('success', 'Status tambak berhasil diubah menjadi "'.Label::pond($pond->operational_status)[0].'".');
    }

    /** F-07: siklus baru. */
    public function cycleForm(Request $request)
    {
        return view('petambak.catat.siklus', [
            'ponds' => $this->myPonds()->orderBy('pond_code')->get(),
            'selected' => $request->query('tambak'),
        ]);
    }

    public function storeCycle(Request $request)
    {
        $data = $request->validate([
            'pond_id' => $this->pondRule(),
            'start_date' => ['required', 'date', 'before_or_equal:today'],
            'cycle_status' => ['required', Rule::in(['persiapan', 'berjalan'])],
        ]);

        $pond = $this->myPond($data['pond_id']);
        $pond->productionCycles()->create([
            'start_date' => $data['start_date'],
            'cycle_status' => $data['cycle_status'],
        ]);

        return redirect()->route('ponds.show', $pond)->with('success', 'Siklus produksi baru berhasil disimpan.');
    }

    public function cycleEdit(string $cycle)
    {
        return view('petambak.catat.siklus-ubah', ['cycle' => $this->myCycle($cycle)]);
    }

    public function cycleUpdate(Request $request, string $cycle)
    {
        $cycle = $this->myCycle($cycle);

        $data = $request->validate([
            'cycle_status' => ['required', Rule::in(array_keys(Label::CYCLES))],
            'end_date' => ['nullable', 'date', 'after_or_equal:'.$cycle->start_date->toDateString(), 'before_or_equal:today'],
        ]);

        $closing = in_array($data['cycle_status'], ['selesai', 'dibatalkan'], true);
        $cycle->update([
            'cycle_status' => $data['cycle_status'],
            // Siklus yang ditutup wajib bertanggal selesai; siklus yang berjalan tidak punya tanggal selesai.
            'end_date' => $closing ? ($data['end_date'] ?? now()->toDateString()) : null,
        ]);

        return redirect()->route('ponds.show', $cycle->pond_id)->with('success', 'Status siklus berhasil diubah.');
    }
}
