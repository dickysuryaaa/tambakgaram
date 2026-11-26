<?php

namespace App\Http\Controllers\Petambak;

use App\Models\Harvest;
use App\Models\Issue;
use App\Models\ProductionActivity;
use App\Models\WaterObservation;
use App\Support\Label;
use App\Support\RecordFeed;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/** Formulir pencatatan petambak: panen, pengamatan air, kendala, kegiatan (F-08, F-09, F-11, F-12). */
class RecordController extends PetambakController
{
    public function menu()
    {
        return view('petambak.catat.index', ['hasPonds' => $this->myPonds()->exists()]);
    }

    private function formData(Request $request, bool $withCycles = false): array
    {
        $data = [
            'ponds' => $this->myPonds()->orderBy('pond_code')->get(),
            'selected' => $request->query('tambak'),
        ];
        if ($withCycles) {
            $data['cycles'] = $this->usableCycles();
        }

        return $data;
    }

    // ---- F-09 Panen ----

    public function harvestForm(Request $request)
    {
        return view('petambak.catat.panen', $this->formData($request, true));
    }

    public function harvestStore(Request $request)
    {
        $this->normalizeNumbers($request, ['quantity']);

        $data = $request->validate([
            'pond_id' => $this->pondRule(),
            'cycle_id' => $this->cycleRule($request),
            'harvest_date' => ['required', 'date', 'before_or_equal:today'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'unit' => ['required', Rule::in(['kg', 'ton'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $cycle = $this->myCycle($data['cycle_id']);
        if (Carbon::parse($data['harvest_date'])->lt($cycle->start_date)) {
            return back()->withInput()->withErrors([
                'harvest_date' => 'Tanggal panen tidak boleh sebelum siklus dimulai ('.$cycle->start_date->translatedFormat('j F Y').').',
            ]);
        }

        // Nilai disimpan apa adanya (kg atau ton); konversi ke kg dilakukan saat rekap.
        $harvest = Harvest::create($data + ['recorded_by' => $this->me()->member_id]);

        $info = $harvest->unit === 'ton'
            ? ' ('.RecordFeed::num($harvest->quantity).' ton = '.number_format($harvest->quantity_kg, 0, ',', '.').' kg)'
            : '';

        return redirect()->route('beranda')->with('success', 'Catatan panen berhasil disimpan'.$info.'.');
    }

    // ---- F-11 Pengamatan air ----

    public function waterForm(Request $request)
    {
        return view('petambak.catat.air', $this->formData($request));
    }

    public function waterStore(Request $request)
    {
        $this->normalizeNumbers($request, ['salinity_value', 'temperature_c', 'ph_value']);

        $data = $request->validate([
            'pond_id' => $this->pondRule(),
            'observed_at' => ['required', 'date', 'before_or_equal:now'],
            // Setiap angka boleh kosong (disimpan NULL). Nol yang diketik tetap disimpan sebagai 0.
            'salinity_value' => ['nullable', 'numeric', 'between:0,50'],
            'temperature_c' => ['nullable', 'numeric', 'between:0,60'],
            'ph_value' => ['nullable', 'numeric', 'between:0,14'],
            'instrument' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (! isset($data['salinity_value']) && ! isset($data['temperature_c']) && ! isset($data['ph_value'])) {
            return back()->withInput()->withErrors([
                'salinity_value' => 'Isi minimal satu hasil pengukuran. Yang tidak diukur boleh dikosongkan.',
            ]);
        }

        WaterObservation::create($data + ['recorded_by' => $this->me()->member_id]);

        return redirect()->route('beranda')->with('success', 'Catatan pengamatan air berhasil disimpan.');
    }

    // ---- F-12 Kendala ----

    public function issueForm(Request $request)
    {
        return view('petambak.catat.kendala', $this->formData($request));
    }

    public function issueStore(Request $request)
    {
        $data = $request->validate([
            'pond_id' => $this->pondRule(),
            'category' => ['required', Rule::in(array_keys(Label::CATEGORIES))],
            'priority' => ['required', Rule::in(array_keys(Label::PRIORITIES))],
            'description' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        Issue::create($data + [
            'reported_by' => $this->me()->member_id,
            'reported_at' => now(),
            'status' => 'terbuka',
        ]);

        return redirect()->route('beranda')->with('success', 'Laporan kendala berhasil dikirim. Koordinator akan menindaklanjuti.');
    }

    // ---- F-08 Kegiatan ----

    public function activityForm(Request $request)
    {
        return view('petambak.catat.kegiatan', $this->formData($request, true));
    }

    public function activityStore(Request $request)
    {
        $data = $request->validate([
            'pond_id' => $this->pondRule(),
            'cycle_id' => $this->cycleRule($request),
            'activity_type' => ['required', Rule::in(array_keys(Label::ACTIVITIES))],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        unset($data['pond_id']);

        ProductionActivity::create($data + ['recorded_by' => $this->me()->member_id]);

        return redirect()->route('beranda')->with('success', 'Catatan kegiatan berhasil disimpan.');
    }
}
