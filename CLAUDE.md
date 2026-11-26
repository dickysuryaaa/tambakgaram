# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

# Project: SIMTAG (Sistem Informasi Manajemen Tambak Garam)

Spesifikasi lengkap (sumber kebenaran): `prd_simtag_kelompok_5_v1_0.md` di root repo. Baca bagian terkait sebelum membangun fitur; keputusan final ada di Bagian 17.

Status repo: scaffold Laravel 13 + skema database + seeder simulasi sudah ada (belum ada route, controller, view, auth). Auth model: `App\Models\Member` (login via `username`). Perintah: `php artisan migrate:fresh --seed` (DB `simtag_db`, MySQL Laragon), `php artisan test`. Akun demo (kata sandi `password`): `koordinator`, `petambak1`..`petambak3`. Nama tabel mengikuti perintah pengguna: `production_activities` (PRD: `pond_activities`) dan `issues` (PRD: `issue_reports`). Stack yang dituju (PRD 11.2): Laravel + Blade + Bootstrap 5 + Chart.js, MySQL 8/MariaDB, dijalankan lokal via Laragon lalu di-deploy ke Kubernetes.

Aplikasi ini adalah workload untuk eksperimen pemulihan Kubernetes (1 vs 3 replika; gangguan proses, pod, worker node). Aturan stateless di bawah bukan preferensi, tapi syarat agar eksperimen valid.

## Aturan Arsitektur Wajib (Stateless Kubernetes)
1. Aplikasi wajib 100% STATELESS pada level pod/container.
2. Wajib menggunakan shared MySQL untuk data bisnis serta `SESSION_DRIVER=database` dan `CACHE_STORE=database`.
3. Dilarang menyimpan file upload atau cache lokal di filesystem pod (fitur upload foto dinonaktifkan pada MVP).
4. Tambahkan middleware global yang menyisipkan header `X-App-Instance` berisi hostname pod (`gethostname()`) pada setiap HTTP response.
5. Sediakan endpoint pengujian tanpa middleware login web:
   - `GET /health/live`: 200 selama proses hidup, tanpa akses DB.
   - `GET /health/ready`: jalankan `SELECT 1`; 200 hanya jika DB siap.
   - `GET /test/read-only/ponds` dan `GET /test/read-only/dashboard`: hanya baca DB (jangan menulis), data agregat/non-sensitif. Load generator (k6/hey) harus mendapat data asli, bukan redirect ke `/login`.
   - `POST /test/crash` (opsional): wajib header rahasia `X-Test-Key`.

## Aturan Domain dan Data
- Dua role saja di MVP: `petambak` dan `koordinator_admin` (koordinator dan admin digabung). Petambak hanya boleh melihat/mengelola tambak miliknya (`ponds.member_id`) dan catatan terkait; terapkan di query/policy, bukan hanya di UI. Nomor telepon anggota hanya untuk `koordinator_admin`.
- Skema mengikuti PRD Bagian 8 (nama tabel dan kolom bahasa Inggris, primary key bernama `<entitas>_id`, mis. `pond_id`, `member_id`; nilai enum bahasa Indonesia, mis. `terbuka`/`diproses`/`selesai`).
- Pengamatan air: parameter yang tidak diukur disimpan `NULL`, bukan 0 atau perkiraan; jangan wajibkan di form.
- Panen: input `kg` atau `ton`, simpan apa adanya, konversi ke `kg` saat rekap/laporan.
- Data demo/seeder adalah data simulasi dan wajib berlabel di antarmuka dan laporan dengan teks halus "Mode uji coba: data yang tampil adalah data contoh" (diubah atas permintaan pengguna dari `[Data Simulasi]`; label tidak boleh dihapus selama data masih simulasi). Setiap laporan mencantumkan periode dan sumber data.
- Cakupan MVP terkunci pada F-01 s.d. F-15 (PRD 6.1). Cuaca, peta Leaflet, K-Means, ekspor, upload foto tidak dibangun dan tidak ditampilkan di navigasi kecuali diminta.

## Standar UI & Bahasa
- Gunakan Bahasa Indonesia sederhana dan hindari istilah teknis (seperti "telemetry" atau "cluster") di antarmuka pengguna.
- Desain mobile-first, ukuran teks isi 16-18px, kontras tinggi (WCAG 2.2 AA), dan tombol aksi utama minimal 44x44px.
- Status selalu berupa label teks, warna hanya pendukung (hijau aktif/selesai, kuning perlu perhatian, merah kendala, abu-abu tidak aktif). Ikon selalu disertai teks. Label field selalu terlihat, bukan hanya placeholder.
- Navigasi petambak maksimal 5 menu (Beranda, Tambak Saya, Catat, Riwayat, Akun); urutan komponen beranda ada di PRD 7.3 dan 7.4.
