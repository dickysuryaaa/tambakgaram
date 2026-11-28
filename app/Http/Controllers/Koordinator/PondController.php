<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Member;
use App\Models\Pond;
use App\Support\Label;
use App\Support\Stats;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-04 / FR-05: CRUD profil tambak dan status operasional. */
class PondController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $memberId = $request->query('anggota');

        $ponds = Pond::with('member')
            ->withCount(['issues as open_issues_count' => fn ($i) => $i->whereIn('status', ['terbuka', 'diproses'])])
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('pond_code', 'like', "%$q%")->orWhere('pond_name', 'like', "%$q%")
                ->orWhere('village', 'like', "%$q%")->orWhere('district', 'like', "%$q%")->orWhere('regency', 'like', "%$q%")))
            ->when(array_key_exists((string) $status, Label::PONDS), fn ($query) => $query->where('operational_status', $status))
            ->when(ctype_digit((string) $memberId), fn ($query) => $query->where('member_id', $memberId))
            ->orderBy('pond_code')->paginate(15)->withQueryString();

        return view('koordinator.tambak.index', [
            'ponds' => $ponds, 'q' => $q, 'status' => $status, 'memberId' => $memberId,
            'members' => Member::where('role', Member::ROLE_PETAMBAK)->orderBy('name')->get(['member_id', 'name']),
        ]);
    }

    public function show(Pond $pond)
    {
        $pond->load('member');
        $cycles = $pond->productionCycles()->withCount('activities')->orderByDesc('start_date')->get();
        $harvestKg = Harvest::where('pond_id', $pond->pond_id)->get()->sum->quantity_kg;
        $latestObs = $pond->waterObservations()->latest('observed_at')->first();
        $issues = $pond->issues()->orderByRaw(Stats::orderBy('status', ['terbuka', 'diproses', 'selesai']))->latest('reported_at')->limit(10)->get();

        return view('koordinator.tambak.show', compact('pond', 'cycles', 'harvestKg', 'latestObs', 'issues'));
    }

    public function create()
    {
        return view('koordinator.tambak.form', ['pond' => new Pond(['operational_status' => 'persiapan']), 'members' => $this->owners()]);
    }

    public function store(Request $request)
    {
        $pond = Pond::create($this->validated($request, null));

        return redirect()->route('k.ponds.show', $pond)->with('success', 'Tambak "'.$pond->pond_code.'" berhasil ditambahkan.');
    }

    public function edit(Pond $pond)
    {
        return view('koordinator.tambak.form', ['pond' => $pond, 'members' => $this->owners($pond->member_id)]);
    }

    public function update(Request $request, Pond $pond)
    {
        $pond->update($this->validated($request, $pond));

        return redirect()->route('k.ponds.show', $pond)->with('success', 'Data tambak berhasil diperbarui.');
    }

    public function destroy(Pond $pond)
    {
        // Menghapus tambak ikut menghapus semua catatannya, jadi hanya boleh bila belum ada catatan.
        $hasRecords = $pond->productionCycles()->exists() || $pond->harvests()->exists()
            || $pond->waterObservations()->exists() || $pond->issues()->exists();

        if ($hasRecords) {
            return back()->with('error', 'Tambak ini sudah punya catatan, jadi tidak dapat dihapus. Ubah statusnya menjadi Tidak Aktif.');
        }

        $pond->delete();

        return redirect()->route('k.ponds.index')->with('success', 'Tambak berhasil dihapus.');
    }

    /** Pemilik yang bisa dipilih: petambak aktif, ditambah pemilik saat ini bila sudah nonaktif. */
    private function owners(?int $currentId = null)
    {
        return Member::where('role', Member::ROLE_PETAMBAK)
            ->where(fn ($q) => $q->where('status', 'aktif')->when($currentId, fn ($w) => $w->orWhere('member_id', $currentId)))
            ->orderBy('name')->get(['member_id', 'name', 'status']);
    }

    private function validated(Request $request, ?Pond $pond): array
    {
        foreach (['latitude', 'longitude'] as $f) {
            $v = $request->input($f);
            if (is_string($v) && $v !== '') {
                $request->merge([$f => str_replace(',', '.', trim($v))]);
            }
        }

        // Luas boleh ditulis dengan titik ribuan ("5.000").
        if (is_string($request->input('area_m2')) && preg_match('/^\d{1,3}(\.\d{3})+$/', $request->input('area_m2'))) {
            $request->merge(['area_m2' => str_replace('.', '', $request->input('area_m2'))]);
        }

        $data = $request->validate([
            'pond_code' => ['required', 'string', 'max:30', Rule::unique('ponds', 'pond_code')->ignore($pond?->pond_id, 'pond_id')],
            'pond_name' => ['nullable', 'string', 'max:255'],
            'member_id' => ['required', Rule::exists('members', 'member_id')->where('role', Member::ROLE_PETAMBAK)],
            'village' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'regency' => ['required', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'area_m2' => ['required', 'integer', 'min:1', 'max:100000000'],
            'operational_status' => ['required', Rule::in(array_keys(Label::PONDS))],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'pond_code.unique' => 'Kode tambak ini sudah dipakai tambak lain.',
            'member_id.required' => 'Pilih pemilik tambak.',
            'member_id.exists' => 'Pemilik harus berupa anggota dengan peran petambak.',
            'area_m2.integer' => 'Luas harus berupa bilangan bulat dalam meter persegi.',
        ], [
            'pond_code' => 'Kode tambak', 'pond_name' => 'Nama tambak', 'member_id' => 'Pemilik', 'village' => 'Desa atau kelurahan',
            'district' => 'Kecamatan', 'regency' => 'Kabupaten atau kota', 'latitude' => 'Lintang', 'longitude' => 'Bujur',
            'area_m2' => 'Luas', 'operational_status' => 'Status', 'notes' => 'Keterangan',
        ]);

        // Koordinat kosong disimpan NULL, bukan 0.
        foreach (['pond_name', 'latitude', 'longitude', 'notes'] as $f) {
            $data[$f] = filled($data[$f] ?? null) ? $data[$f] : null;
        }

        return $data;
    }
}
