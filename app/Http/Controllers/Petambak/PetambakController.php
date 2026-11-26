<?php

namespace App\Http\Controllers\Petambak;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Pond;
use App\Models\ProductionCycle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Dasar semua controller area petambak. Seluruh akses data lewat tambak milik
 * pengguna yang login (ponds.member_id), sehingga data anggota lain tidak terjangkau.
 */
abstract class PetambakController extends Controller
{
    protected function me(): Member
    {
        return request()->user();
    }

    /** Tambak milik pengguna. */
    protected function myPonds(): Builder
    {
        return Pond::where('member_id', $this->me()->member_id);
    }

    /** Tambak milik pengguna atau 404 (keberadaan tambak orang lain tidak dibocorkan). */
    protected function myPond(int|string $id): Pond
    {
        return $this->myPonds()->where('pond_id', $id)->firstOrFail();
    }

    /** Siklus pada tambak milik pengguna atau 404. */
    protected function myCycle(int|string $id): ProductionCycle
    {
        return ProductionCycle::with('pond')
            ->whereIn('pond_id', $this->myPonds()->select('pond_id'))
            ->where('cycle_id', $id)
            ->firstOrFail();
    }

    /** Siklus yang boleh dipakai untuk mencatat (bukan dibatalkan), untuk pilihan di formulir. */
    protected function usableCycles(): Collection
    {
        return ProductionCycle::with('pond')
            ->whereIn('pond_id', $this->myPonds()->select('pond_id'))
            ->where('cycle_status', '!=', 'dibatalkan')
            ->orderByDesc('start_date')
            ->get();
    }

    /** Aturan validasi: tambak harus milik pengguna. */
    protected function pondRule(): array
    {
        return ['required', Rule::exists('ponds', 'pond_id')->where('member_id', $this->me()->member_id)];
    }

    /** Aturan validasi: siklus harus milik tambak yang dipilih dan tidak dibatalkan. */
    protected function cycleRule(Request $request): array
    {
        return ['required', Rule::exists('production_cycles', 'cycle_id')
            ->where('pond_id', $request->input('pond_id'))
            ->whereNot('cycle_status', 'dibatalkan')];
    }

    /** Angka dengan koma desimal ("1,5") diterima sebagai 1.5. */
    protected function normalizeNumbers(Request $request, array $fields): void
    {
        foreach ($fields as $f) {
            $v = $request->input($f);
            if (is_string($v) && $v !== '') {
                $request->merge([$f => str_replace(',', '.', trim($v))]);
            }
        }
    }
}
