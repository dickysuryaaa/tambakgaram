<?php

return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'integer' => ':attribute harus berupa angka bulat.',
    'numeric' => ':attribute harus berupa angka. Contoh: 12,5',
    'date' => ':attribute bukan tanggal yang benar.',
    'in' => ':attribute yang dipilih tidak sesuai.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'before_or_equal' => ':attribute tidak boleh melewati :date.',
    'after_or_equal' => ':attribute tidak boleh sebelum :date.',
    'gt' => [
        'numeric' => ':attribute harus lebih besar dari :value.',
    ],
    'between' => [
        'numeric' => ':attribute harus antara :min dan :max.',
    ],
    'min' => [
        'string' => ':attribute minimal :min huruf.',
        'numeric' => ':attribute minimal :min.',
    ],
    'max' => [
        'string' => ':attribute maksimal :max huruf.',
        'numeric' => ':attribute terlalu besar (maksimal :max).',
    ],

    'custom' => [],

    'attributes' => [
        'username' => 'Nama pengguna',
        'password' => 'Kata sandi',
        'pond_id' => 'Tambak',
        'cycle_id' => 'Siklus',
        'harvest_date' => 'Tanggal panen',
        'quantity' => 'Jumlah panen',
        'unit' => 'Satuan',
        'notes' => 'Catatan',
        'observed_at' => 'Waktu pengamatan',
        'salinity_value' => 'Salinitas',
        'temperature_c' => 'Suhu',
        'ph_value' => 'pH',
        'instrument' => 'Alat ukur',
        'category' => 'Jenis kendala',
        'priority' => 'Tingkat mendesak',
        'description' => 'Keterangan',
        'activity_type' => 'Jenis kegiatan',
        'activity_date' => 'Tanggal kegiatan',
        'start_date' => 'Tanggal mulai',
        'end_date' => 'Tanggal selesai',
        'cycle_status' => 'Keadaan siklus',
        'operational_status' => 'Status baru',
        'dari' => 'Tanggal awal',
        'sampai' => 'Tanggal akhir',
        'jenis' => 'Jenis catatan',
        'tambak' => 'Tambak',
    ],
];
