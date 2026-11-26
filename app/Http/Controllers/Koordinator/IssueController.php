<?php

namespace App\Http\Controllers\Koordinator;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Member;
use App\Models\Pond;
use App\Support\Label;
use App\Support\Period;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** FR-10 (sisi koordinator) dan FR-11: tindak lanjut kendala, dengan pencarian dan filter. */
class IssueController extends Controller
{
    public function index(Request $request)
    {
        $period = Period::optional($request);
        $f = [
            'tambak' => $request->query('tambak'),
            'anggota' => $request->query('anggota'),
            'status' => $request->query('status'),
            'kategori' => $request->query('kategori'),
        ];
        // Tanpa pilihan status: kendala yang belum selesai tampil di atas.
        $issues = Issue::with(['pond.member', 'reporter'])
            ->when(ctype_digit((string) $f['tambak']), fn ($q) => $q->where('pond_id', $f['tambak']))
            ->when(ctype_digit((string) $f['anggota']), fn ($q) => $q->whereHas('pond', fn ($p) => $p->where('member_id', $f['anggota'])))
            ->when(array_key_exists((string) $f['status'], Label::ISSUES), fn ($q) => $q->where('status', $f['status']))
            ->when(array_key_exists((string) $f['kategori'], Label::CATEGORIES), fn ($q) => $q->where('category', $f['kategori']));
        $issues = $period->whereDateTime($issues, 'reported_at')
            ->orderByRaw("FIELD(status, 'terbuka', 'diproses', 'selesai')")
            ->orderByRaw("FIELD(priority, 'tinggi', 'sedang', 'rendah')")
            ->orderByDesc('reported_at')
            ->paginate(15)->withQueryString();

        return view('koordinator.kendala.index', [
            'issues' => $issues, 'period' => $period, 'f' => $f,
            'ponds' => Pond::orderBy('pond_code')->get(['pond_id', 'pond_code', 'pond_name']),
            'members' => Member::where('role', Member::ROLE_PETAMBAK)->orderBy('name')->get(['member_id', 'name']),
        ]);
    }

    public function show(Issue $issue)
    {
        $issue->load(['pond.member', 'reporter']);

        return view('koordinator.kendala.show', compact('issue'));
    }

    public function update(Request $request, Issue $issue)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Label::ISSUES))],
            'priority' => ['required', Rule::in(array_keys(Label::PRIORITIES))],
            'follow_up_notes' => ['nullable', 'string', 'max:2000'],
        ], [], ['status' => 'Status tindak lanjut', 'priority' => 'Tingkat mendesak', 'follow_up_notes' => 'Catatan tindak lanjut']);

        $data['follow_up_notes'] = filled($data['follow_up_notes'] ?? null) ? $data['follow_up_notes'] : null;
        // Waktu selesai dicatat saat pertama kali ditandai selesai, dan dikosongkan bila dibuka lagi.
        $data['resolved_at'] = $data['status'] === 'selesai' ? ($issue->resolved_at ?? now()) : null;

        $issue->update($data);

        return redirect()->route('k.issues.show', $issue)->with('success', 'Tindak lanjut kendala berhasil disimpan.');
    }
}
