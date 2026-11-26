<?php

namespace App\Support;

/** Teks Bahasa Indonesia dan warna status. Warna hanya pendukung; label teks selalu tampil. */
class Label
{
    public const PONDS = [
        'persiapan' => ['Persiapan', 'success'],
        'pengisian_air' => ['Pengisian Air', 'success'],
        'penguapan' => ['Penguapan', 'success'],
        'kristalisasi_panen' => ['Kristalisasi / Panen', 'success'],
        'jeda' => ['Jeda', 'warning'],
        'tidak_aktif' => ['Tidak Aktif', 'secondary'],
    ];

    public const INACTIVE_PONDS = ['jeda', 'tidak_aktif'];

    public const CYCLES = [
        'persiapan' => ['Persiapan', 'warning'],
        'berjalan' => ['Berjalan', 'success'],
        'selesai' => ['Selesai', 'success'],
        'dibatalkan' => ['Dibatalkan', 'secondary'],
    ];

    public const ISSUES = [
        'terbuka' => ['Perlu Ditindaklanjuti', 'danger'],
        'diproses' => ['Sedang Diproses', 'warning'],
        'selesai' => ['Selesai', 'success'],
    ];

    public const CATEGORIES = [
        'tanggul_bocor' => 'Tanggul bocor',
        'pompa_saluran' => 'Pompa atau saluran air',
        'cuaca_hujan' => 'Cuaca atau hujan',
        'lainnya' => 'Lainnya',
    ];

    public const PRIORITIES = [
        'rendah' => 'Tidak mendesak',
        'sedang' => 'Cukup mendesak',
        'tinggi' => 'Sangat mendesak',
    ];

    public const ACTIVITIES = [
        'pembersihan_lahan' => 'Membersihkan lahan',
        'pengisian_air' => 'Mengisi air laut',
        'pengaturan_air' => 'Mengatur air antar petak',
        'perawatan_tanggul' => 'Merawat tanggul',
        'perbaikan_alat' => 'Memperbaiki pompa atau alat',
        'lainnya' => 'Kegiatan lain',
    ];

    public static function pond(string $key): array
    {
        return self::PONDS[$key] ?? [$key, 'secondary'];
    }

    public static function cycle(string $key): array
    {
        return self::CYCLES[$key] ?? [$key, 'secondary'];
    }

    public static function issue(string $key): array
    {
        return self::ISSUES[$key] ?? [$key, 'secondary'];
    }

    public static function activity(string $key): string
    {
        return self::ACTIVITIES[$key] ?? ucfirst(str_replace('_', ' ', $key));
    }

    public static function pondNames(): array
    {
        return array_map(fn ($v) => $v[0], self::PONDS);
    }

    public static function cycleNames(): array
    {
        return array_map(fn ($v) => $v[0], self::CYCLES);
    }
}
