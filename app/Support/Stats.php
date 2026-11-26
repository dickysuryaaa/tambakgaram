<?php

namespace App\Support;

/** Potongan SQL bersama untuk rekap. Panen disimpan apa adanya (kg/ton), dikonversi ke kg hanya saat rekap. */
class Stats
{
    public const HARVEST_KG = "CASE WHEN harvests.unit = 'ton' THEN harvests.quantity * 1000 ELSE harvests.quantity END";

    public static function sourceNote(): string
    {
        return config('simtag.demo_banner')
            ? 'Data contoh untuk uji coba aplikasi. Angka belum berasal dari pencatatan lapangan.'
            : 'Catatan anggota kelompok yang tersimpan di SIMTAG.';
    }
}
