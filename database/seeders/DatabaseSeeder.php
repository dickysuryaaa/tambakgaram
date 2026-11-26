<?php

namespace Database\Seeders;

use App\Models\Harvest;
use App\Models\Issue;
use App\Models\Member;
use App\Models\Pond;
use App\Models\ProductionActivity;
use App\Models\ProductionCycle;
use App\Models\WaterObservation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeder data SIMULASI. Seluruh nama, nomor telepon, koordinat, dan angka
 * produksi di sini fiktif dan diberi label [SIMULASI]. Bukan data lapangan.
 */
class DatabaseSeeder extends Seeder
{
    public const LABEL = '[SIMULASI]';

    // Kata sandi demo untuk semua akun simulasi. Ganti sebelum dipakai di luar lokal.
    private const DEMO_PASSWORD = 'password';

    public function run(): void
    {
        $label = self::LABEL;
        $today = Carbon::today();

        $admin = Member::create([
            'name' => "Koordinator Simulasi $label",
            'username' => 'koordinator',
            'password' => self::DEMO_PASSWORD,
            'phone' => '0800-0000-0001',
            'role' => Member::ROLE_KOORDINATOR_ADMIN,
            'status' => 'aktif',
        ]);

        $petambak = [];
        foreach ([['Petambak Satu', 'petambak1'], ['Petambak Dua', 'petambak2'], ['Petambak Tiga', 'petambak3']] as $i => [$name, $user]) {
            $petambak[] = Member::create([
                'name' => "$name $label",
                'username' => $user,
                'password' => self::DEMO_PASSWORD,
                'phone' => sprintf('0800-0000-%04d', $i + 2),
                'role' => Member::ROLE_PETAMBAK,
                'status' => 'aktif',
            ]);
        }

        // [kode, nama, indeks pemilik, luas m2, status operasional, lat, lng]
        $pondSpecs = [
            ['TG-001', 'Tambak Barat', 0, 5000, 'penguapan', -7.0100000, 113.8600000],
            ['TG-002', 'Tambak Timur', 0, 3500, 'kristalisasi_panen', -7.0120000, 113.8650000],
            ['TG-003', 'Tambak Utara', 1, 8000, 'pengisian_air', -7.0150000, 113.8700000],
            ['TG-004', 'Tambak Selatan', 1, 4200, 'jeda', -7.0180000, 113.8720000],
            ['TG-005', 'Tambak Tengah', 2, 2500, 'tidak_aktif', -7.0200000, 113.8750000],
        ];

        $ponds = [];
        foreach ($pondSpecs as [$code, $name, $owner, $area, $status, $lat, $lng]) {
            $ponds[$code] = Pond::create([
                'pond_code' => $code,
                'pond_name' => "$name $label",
                'member_id' => $petambak[$owner]->member_id,
                'village' => "Desa Contoh $label",
                'district' => "Kecamatan Contoh $label",
                'regency' => "Kabupaten Contoh $label",
                'latitude' => $lat,
                'longitude' => $lng,
                'area_m2' => $area,
                'operational_status' => $status,
                'notes' => "$label Koordinat dan luas lahan adalah data simulasi.",
            ]);
        }

        // Siklus: [kode tambak, mulai (hari lalu), selesai (hari lalu|null), status]
        $cycleSpecs = [
            ['TG-001', 150, 60, 'selesai'],
            ['TG-001', 40, null, 'berjalan'],
            ['TG-002', 120, 20, 'selesai'],
            ['TG-002', 15, null, 'berjalan'],
            ['TG-003', 5, null, 'persiapan'],
            ['TG-004', 200, 90, 'selesai'],
            ['TG-005', 180, 150, 'dibatalkan'],
        ];

        $cycles = [];
        foreach ($cycleSpecs as [$code, $startAgo, $endAgo, $status]) {
            $cycles[] = ProductionCycle::create([
                'pond_id' => $ponds[$code]->pond_id,
                'start_date' => $today->copy()->subDays($startAgo),
                'end_date' => $endAgo === null ? null : $today->copy()->subDays($endAgo),
                'cycle_status' => $status,
            ]);
        }

        // Kegiatan produksi
        $activityTypes = [
            ['pembersihan_lahan', 'Membersihkan lahan dan memperbaiki pematang'],
            ['pengisian_air', 'Mengisi air laut ke petak'],
            ['pengaturan_air', 'Memindahkan air antar petak penguapan'],
            ['perawatan_tanggul', 'Memperbaiki tanggul yang tergerus'],
        ];
        foreach ($cycles as $cycle) {
            if ($cycle->cycle_status === 'dibatalkan') {
                continue;
            }
            $ownerId = $cycle->pond->member_id;
            foreach ($activityTypes as $n => [$type, $desc]) {
                $date = $cycle->start_date->copy()->addDays($n * 5);
                if ($date->isFuture()) {
                    continue;
                }
                ProductionActivity::create([
                    'cycle_id' => $cycle->cycle_id,
                    'activity_type' => $type,
                    'activity_date' => $date,
                    'description' => "$label $desc",
                    'recorded_by' => $ownerId,
                ]);
            }
        }

        // Panen pada siklus selesai (campuran satuan kg dan ton)
        $harvestSpecs = [
            [0, 55, 4200, 'kg'],
            [2, 25, 3.1, 'ton'],
            [5, 95, 5800, 'kg'],
        ];
        foreach ($harvestSpecs as [$idx, $ago, $qty, $unit]) {
            $cycle = $cycles[$idx];
            Harvest::create([
                'cycle_id' => $cycle->cycle_id,
                'pond_id' => $cycle->pond_id,
                'harvest_date' => $today->copy()->subDays($ago),
                'quantity' => $qty,
                'unit' => $unit,
                'notes' => "$label Angka panen simulasi.",
                'recorded_by' => $cycle->pond->member_id,
            ]);
        }

        // Pengamatan air. Parameter yang tidak diukur sengaja NULL.
        $obsSpecs = [
            ['TG-001', 10, 18.0, 33.5, 7.8, 'Baume meter dan termometer'],
            ['TG-001', 6, 21.5, 34.0, null, 'Baume meter dan termometer'],
            ['TG-001', 2, 24.0, null, null, 'Baume meter'],
            ['TG-002', 8, 25.0, 32.0, 8.0, 'Baume meter, termometer, dan kertas pH'],
            ['TG-002', 1, 26.5, null, null, 'Baume meter'],
            ['TG-003', 3, 3.0, 31.0, 7.5, 'Baume meter dan termometer'],
            ['TG-004', 12, null, null, null, null],
        ];
        foreach ($obsSpecs as [$code, $ago, $sal, $temp, $ph, $instrument]) {
            WaterObservation::create([
                'pond_id' => $ponds[$code]->pond_id,
                'observed_at' => $today->copy()->subDays($ago)->setTime(8, 0),
                'salinity_value' => $sal,
                'temperature_c' => $temp,
                'ph_value' => $ph,
                'instrument' => $instrument,
                'notes' => "$label Pengamatan simulasi.",
                'recorded_by' => $ponds[$code]->member_id,
            ]);
        }

        // Kendala dengan status berbeda
        $issueSpecs = [
            ['TG-001', 9, 'tanggul_bocor', 'tinggi', 'selesai', 'Sudah ditambal bersama kelompok.', 7],
            ['TG-002', 4, 'pompa_saluran', 'sedang', 'diproses', 'Menunggu suku cadang pompa.', null],
            ['TG-003', 1, 'cuaca_hujan', 'rendah', 'terbuka', null, null],
        ];
        foreach ($issueSpecs as [$code, $ago, $cat, $prio, $status, $follow, $doneAgo]) {
            Issue::create([
                'pond_id' => $ponds[$code]->pond_id,
                'reported_by' => $ponds[$code]->member_id,
                'reported_at' => $today->copy()->subDays($ago)->setTime(9, 30),
                'category' => $cat,
                'description' => "$label Laporan kendala simulasi.",
                'priority' => $prio,
                'status' => $status,
                'follow_up_notes' => $follow ? "$label $follow" : null,
                'resolved_at' => $doneAgo === null ? null : $today->copy()->subDays($doneAgo)->setTime(15, 0),
            ]);
        }
    }
}
