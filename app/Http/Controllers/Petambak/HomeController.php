<?php

namespace App\Http\Controllers\Petambak;

use App\Models\Pond;
use App\Support\Label;
use App\Support\RecordFeed;

class HomeController extends PetambakController
{
    public function __invoke()
    {
        $ponds = $this->myPonds()
            ->withCount(['issues as open_issues_count' => fn ($q) => $q->whereIn('status', ['terbuka', 'diproses'])])
            ->orderBy('pond_code')->get();

        // Setiap tambak masuk tepat satu kelompok: Tidak Aktif, Perlu Perhatian (ada kendala belum selesai), atau Aktif.
        $groups = ['aktif' => 0, 'perhatian' => 0, 'tidak_aktif' => 0];
        foreach ($ponds as $p) {
            if (in_array($p->operational_status, Label::INACTIVE_PONDS, true)) {
                $groups['tidak_aktif']++;
            } elseif ($p->open_issues_count > 0) {
                $groups['perhatian']++;
            } else {
                $groups['aktif']++;
            }
        }

        return view('petambak.beranda', [
            'ponds' => $ponds,
            'groups' => $groups,
            'recent' => RecordFeed::latest($this->me(), 8),
        ]);
    }
}
