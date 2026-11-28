<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/** Potongan SQL bersama untuk rekap. Panen disimpan apa adanya (kg/ton), dikonversi ke kg hanya saat rekap. */
class Stats
{
    public const HARVEST_KG = "CASE WHEN harvests.unit = 'ton' THEN harvests.quantity * 1000 ELSE harvests.quantity END";

    /** Urutan kustom yang jalan di MySQL dan SQLite (pengganti FIELD() milik MySQL). */
    public static function orderBy(string $column, array $values): string
    {
        $cases = '';
        foreach (array_values($values) as $i => $v) {
            $cases .= " WHEN '{$v}' THEN {$i}";
        }

        return "CASE {$column}{$cases} ELSE ".count($values).' END';
    }

    /** Ekspresi tahun-bulan (YYYY-MM) dari kolom tanggal, sesuai driver DB. */
    public static function yearMonth(string $column): string
    {
        return DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$column})"
            : "DATE_FORMAT({$column}, '%Y-%m')";
    }

    public static function sourceNote(): string
    {
        return config('simtag.demo_banner')
            ? 'Data contoh untuk uji coba aplikasi. Angka belum berasal dari pencatatan lapangan.'
            : 'Catatan anggota kelompok yang tersimpan di SIMTAG.';
    }
}
