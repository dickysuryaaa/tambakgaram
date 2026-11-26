# PRODUCT REQUIREMENTS DOCUMENT (PRD)

# SIMTAG --- Sistem Informasi Manajemen Tambak Garam

**Mata kuliah:** Praktikum Dasar Komputasi Awan\
**Tugas:** UAS Kelompok 5 --- Pemulihan Kubernetes\
**Status:** Final (Baseline Implementasi & Eksperimen)\
**Versi:** 1.0\
**Tanggal:** 24 November 2026

> Dokumen ini adalah spesifikasi produk dan *case study* untuk UAS. Skenario kelompok petambak di bawah merupakan skenario rancangan/simulasi, bukan hasil wawancara atau klaim kondisi lapangan yang sudah diverifikasi.

------------------------------------------------------------------------

## 1. Ringkasan Produk

**SIMTAG (Sistem Informasi Manajemen Tambak Garam)** adalah aplikasi web untuk membantu kelompok petambak, koordinator, dan petugas pendamping mengelola informasi tambak dalam satu tempat. Sistem menggabungkan data profil tambak, siklus produksi, hasil panen, pengamatan kondisi air, kendala operasional, dan laporan kelompok.

SIMTAG dirancang agar informasi yang biasanya dicatat terpisah dapat dibaca sebagai gambaran operasional kelompok. Petambak dapat melihat catatan tambaknya sendiri; koordinator dapat melihat ringkasan seluruh tambak yang menjadi tanggung jawab kelompok; administrator dapat mengelola data dan akun.

Aplikasi ini juga menjadi *workload* untuk penelitian UAS Kelompok 5, yaitu mengukur pemulihan layanan Kubernetes saat terjadi kegagalan proses aplikasi, *pod*, dan *worker node*. Karena itu, aplikasi harus **stateless pada setiap pod aplikasi** dan semua replika harus menggunakan sumber data bersama.

### 1.1 Tujuan Produk

1. Memusatkan informasi tambak dan petambak.
2. Membantu pencatatan siklus produksi dan hasil panen.
3. Memudahkan pemantauan status operasional tambak.
4. Menyediakan catatan pengamatan air sebagai konteks produksi.
5. Menampilkan ringkasan kondisi operasional kelompok secara terpusat.
6. Menyediakan laporan periode yang mudah dibaca petambak dan koordinator.
7. Menjadi aplikasi web yang *stateless*, stabil, dan terukur untuk eksperimen pemulihan Kubernetes.

### 1.2 Bukan Tujuan Produk

SIMTAG bukan sistem kendali tambak otomatis, bukan alat ukur kualitas air, bukan aplikasi jual-beli garam, dan bukan sistem prediksi hasil panen yang menjamin akurasi. Versi UAS tidak memerlukan sensor IoT fisik serta tidak menyimpan file unggahan pada *filesystem* lokal *pod*. Data yang tidak berasal dari pengukuran lapangan harus diberi label simulasi/contoh.

------------------------------------------------------------------------

## 2. Case Study Kelompok 5

### 2.1 Judul Case

**Digitalisasi Pendataan dan Pemantauan Operasional Kelompok Petambak Garam**

### 2.2 Latar Skenario

Dalam skenario ini, sebuah kelompok petambak garam mengelola sejumlah petak tambak yang dimiliki atau dikelola oleh beberapa anggota. Setiap petambak menjalankan kegiatan produksi dan membuat catatan masing-masing. Koordinator kelompok perlu mengumpulkan informasi untuk mengetahui perkembangan produksi dan menentukan pendampingan.

Saat informasi tersimpan dalam buku catatan, pesan WhatsApp, atau *spreadsheet* yang berbeda-beda, koordinator harus meminta dan menggabungkan data secara manual. Informasi bisa terlambat diperbarui, format pencatatan tidak seragam, dan riwayat produksi sulit dibandingkan antarperiode.

Skenario ini digunakan sebagai konteks desain SIMTAG. Nama desa, kelompok, jumlah petambak, luas lahan, hasil produksi, dan koordinat tidak ditetapkan sebagai fakta. Untuk demo, kelompok menggunakan data simulasi yang diberi label dengan jelas.

### 2.3 Permasalahan yang Ingin Diselesaikan

| Permasalahan dalam skenario | Dampak operasional | Solusi SIMTAG |
| :--- | :--- | :--- |
| Data petambak dan tambak tersebar | Koordinator kesulitan melihat daftar tambak yang dikelola | Profil petambak dan profil tambak terpusat |
| Status kegiatan tambak tidak diperbarui di satu tempat | Sulit mengetahui tambak yang sedang persiapan, produksi, atau panen | Status operasional dan siklus produksi terstruktur |
| Catatan panen berbeda format | Perbandingan hasil antarperiode memerlukan rekap manual | Form pencatatan panen dengan satuan dan periode konsisten |
| Pengamatan kondisi air tersimpan terpisah | Riwayat pengamatan tidak mudah dikaitkan dengan siklus produksi | Log pengamatan air per tambak dan tanggal |
| Kendala tambak dilaporkan lewat pesan pribadi | Tindak lanjut dan status penyelesaian sulit dipantau | Catatan kendala dan status tindak lanjut terpusat |
| Informasi lokasi dan data produksi tidak terhubung | Koordinator sulit melihat persebaran informasi tambak | Data koordinat tambak terhubung ke profil (serta peta pada tahap lanjut) |
| Rekap kelompok dibuat manual | Laporan membutuhkan waktu dan rawan salah salin | Ringkasan dan laporan periode otomatis |

### 2.4 Aktor dalam Case

1. **Petambak/anggota:** melihat tambak yang menjadi tanggung jawabnya, mencatat kegiatan, panen, pengamatan air, dan kendala.
2. **Koordinator kelompok:** memantau semua tambak anggota, memeriksa catatan, melihat ringkasan kelompok, mengelola tindak lanjut kendala, dan menyiapkan laporan.
3. **Administrator sistem:** mengelola akun, data master, wilayah, dan konfigurasi sistem.

*(Catatan MVP: Sesuai kesepakatan tim pada Bagian 17, peran Koordinator dan Administrator digabung dalam satu akun pengelola untuk efisiensi pembangunan MVP, sedangkan peran Petambak dipisah ketat dan hanya dapat mengakses data miliknya).*

### 2.5 Contoh Alur Kasus

1. Koordinator/Admin membuat data anggota dan mendaftarkan tambak yang dikelola.
2. Petambak membuka SIMTAG melalui telepon genggam dan memilih tambaknya.
3. Petambak memperbarui status kegiatan, misalnya persiapan lahan atau proses produksi.
4. Petambak mencatat pengamatan air berdasarkan alat ukur yang digunakan (atau data simulasi berlabel).
5. Setelah panen, petambak memasukkan tanggal dan jumlah hasil panen.
6. Koordinator memeriksa catatan dan melihat ringkasan produksi kelompok.
7. Jika ada kendala, petambak membuat laporan kendala; koordinator memperbarui status tindak lanjut.
8. Pada akhir periode, koordinator memilih rentang tanggal dan melihat rekap produksi serta catatan operasional.

### 2.6 Pertanyaan yang Harus Bisa Dijawab oleh SIMTAG

- Berapa tambak yang terdaftar dan berapa yang sedang aktif?
- Tambak mana yang sedang persiapan, produksi, panen, atau tidak beroperasi?
- Berapa hasil panen tiap tambak pada periode tertentu?
- Bagaimana perbandingan hasil panen antarperiode?
- Kapan pengamatan air terakhir dicatat dan apa hasilnya?
- Kendala apa yang masih belum ditindaklanjuti?
- Berapa total hasil produksi kelompok pada periode yang dipilih?

------------------------------------------------------------------------

## 3. Dasar Penelitian dan Hubungannya dengan Produk

Tiga artikel ilmiah menjadi dasar konsep perancangan fitur SIMTAG:

1. **Wiyanto dan Sulistiorini (2018)** membahas SIG untuk pemetaan tambak garam di Sidoarjo. Artikel mendukung penggunaan data koordinat, batas administrasi, luas tambak, kepemilikan, dan data produksi.
2. **Ula et al. (2024)** membahas pendampingan penerapan alat monitoring kualitas air/kadar garam berbasis IoT di Aceh Utara. Artikel menginspirasi struktur pencatatan log pengamatan air, tetapi diimplementasikan sebagai input manual pada SIMTAG (tanpa sensor fisik).
3. **Romli, Habibi, dan Arman (2025)** membahas SIPETA, sistem web pemetaan produksi tambak dengan *K-Means* menggunakan luas lahan dan volume produksi di Kabupaten Sampang.

**Arah pengembangan:** Paper 3 menjadi referensi utama sistem web dan analisis produktivitas; Paper 1 memperkuat struktur data lokasi dan tambak; Paper 2 menjadi inspirasi fitur log pengamatan air manual.

------------------------------------------------------------------------

## 4. Pengguna dan Hak Akses

| Role | Kebutuhan | Hak akses |
| :--- | :--- | :--- |
| **Petambak** | Mencatat kegiatan tambaknya dengan mudah | Melihat tambak miliknya, memperbarui status, memasukkan log pengamatan air, panen, dan laporan kendala |
| **Koordinator / Admin** | Melihat kondisi kelompok, mengelola data master, dan menyusun laporan | Mengelola akun & data tambak, melihat semua tambak anggota, memeriksa catatan, mengelola tindak lanjut kendala, melihat *dashboard* dan laporan kelompok |

**Aturan privasi:** Petambak tidak boleh melihat catatan pribadi maupun data tambak milik anggota lain. Informasi yang ditampilkan pada halaman publik (jika diaktifkan) hanya berupa statistik agregat tanpa nama atau nomor kontak pribadi.

------------------------------------------------------------------------

## 5. Rekomendasi Struktur Website

### 5.1 Area Petambak --- Tampilan Mobile Sederhana

Navigasi utama menggunakan maksimal lima menu yang mudah dikenali:

- **Beranda:** ringkasan tambak milik pengguna dan kegiatan terbaru.
- **Tambak Saya:** daftar tambak dan status masing-masing.
- **Catat:** tombol utama untuk mencatat panen, pengamatan air, kegiatan, atau kendala.
- **Riwayat:** daftar catatan sebelumnya.
- **Akun:** profil dan bantuan.

Beranda petambak langsung menjawab tiga hal: status tambak saat ini, catatan terakhir, dan tindakan berikutnya tanpa grafik yang rumit.

### 5.2 Area Koordinator / Admin --- Dashboard dan Pengelolaan

Menu aktif pada versi MVP:

1. Dashboard kelompok.
2. Data anggota & pengaturan akun.
3. Data tambak.
4. Siklus produksi & kegiatan.
5. Hasil panen.
6. Pengamatan air.
7. Kendala dan tindak lanjut.
8. Laporan periode.

*(Catatan Implementasi: Menu fitur prioritas lanjutan seperti Cuaca, Peta Tambak, dan Analisis Produktivitas K-Means disembunyikan dari bilah navigasi selama tahap MVP agar antarmuka bersih dan tidak menampilkan halaman kosong saat demo).*

### 5.3 Halaman Publik (Opsional)

Jika dibutuhkan pada tahap lanjut, halaman publik hanya menampilkan statistik agregat kelompok yang sudah disetujui tanpa membuka identitas atau catatan pribadi petambak.

------------------------------------------------------------------------

## 6. Rekomendasi Fitur Website

Fitur dibagi berdasarkan prioritas dan diselaraskan dengan tabel kebutuhan fungsional pada Bagian 10 agar implementasi MVP realistis sebelum eksperimen Kubernetes.

### 6.1 Fitur MVP --- Wajib Dibangun (Prioritas: Must)

| ID | Fitur | Deskripsi dan nilai guna |
| :--- | :--- | :--- |
| **F-01** | Login dan role | Login petambak dan koordinator/admin menggunakan *session* berbasis database atau token *stateless*; akses dibatasi sesuai *role* |
| **F-02** | Dashboard petambak | Menampilkan tambak milik pengguna, status, panen terakhir, dan catatan terbaru |
| **F-03** | Dashboard koordinator | Ringkasan jumlah anggota, tambak aktif, status kegiatan, total panen periode terpilih, dan kendala terbuka |
| **F-04** | Profil anggota | Data dasar anggota dan akun login; data pribadi dibatasi sesuai hak akses |
| **F-05** | Profil tambak | Kode/nama tambak, pemilik/pengelola, wilayah, koordinat teks, luas, status, dan keterangan |
| **F-06** | Status operasional | Status sederhana: persiapan, pengisian air, penguapan, kristalisasi/panen, jeda, tidak aktif |
| **F-07** | Siklus produksi | Membuat siklus berdasarkan tanggal mulai, tanggal selesai, status, dan tambak terkait |
| **F-08** | Catatan kegiatan | Catatan tanggal, jenis kegiatan, deskripsi singkat, dan pengguna pencatat |
| **F-09** | Catatan hasil panen | Tanggal panen, volume, satuan (`kg` atau `ton`), siklus, dan catatan; validasi angka dan konversi satuan konsisten |
| **F-10** | Riwayat produksi | Riwayat hasil panen per tambak dan per periode |
| **F-11** | Log pengamatan air | Catatan manual salinitas/Baumé, suhu, pH, waktu pengukuran, alat ukur, dan catatan; parameter kosong disimpan sebagai `NULL` |
| **F-12** | Laporan kendala | Petambak mencatat kategori masalah, deskripsi, tanggal, dan status tindak lanjut oleh koordinator (tanpa upload file pada MVP) |
| **F-13** | Pencarian dan filter | Cari berdasarkan kode/nama tambak, anggota, wilayah, status, dan rentang periode |
| **F-14** | Laporan periode | Rekap produksi, status tambak, pengamatan air, dan kendala berdasarkan rentang tanggal |
| **F-15** | Health check & test endpoint | *Endpoint* *liveness*, *readiness*, serta *endpoint* uji beban *read-only* untuk eksperimen Kubernetes |

### 6.2 Fitur Prioritas Berikutnya --- Jika Waktu Memungkinkan (Prioritas: Should / Could)

| Fitur | Prioritas | Deskripsi |
| :--- | :--- | :--- |
| **Catatan cuaca manual** | Should | Pencatatan manual kondisi cuaca harian, hujan/tidak, dan dampak lapangan |
| **Peta lokasi tambak** | Should | Visualisasi peta interaktif menggunakan Leaflet berdasarkan koordinat tambak |
| **Ekspor CSV / Cetak PDF** | Should | Mengunduh rekap periode dalam format CSV atau tampilan cetak PDF |
| **Audit log sederhana** | Should | Mencatat waktu dan pengguna yang membuat atau mengubah catatan |
| **Lampiran foto kendala** | Should | Unggah foto bukti kendala menggunakan *object storage* (MinIO/S3) atau *shared storage* (`ReadWriteMany`), bukan *filesystem* lokal *pod* |
| **Analisis K-Means** | Could | Mengelompokkan tambak berdasarkan luas lahan dan volume produksi pada periode terpilih |
| **Notifikasi & persetujuan** | Could | Pemberitahuan kendala terbuka dan persetujuan catatan panen oleh koordinator |
| **PWA sederhana** | Could | Dukungan instalasi dari *browser* untuk akses cepat di ponsel |

### 6.3 Fitur di Luar Ruang Lingkup UAS

- Integrasi sensor IoT fisik dan *streaming* data *real-time*.
- Kontrol pompa, pintu air, atau otomasi proses produksi.
- Prediksi panen atau rekomendasi agronomi otomatis.
- *Marketplace*, pembayaran, logistik, dan *chat real-time*.
- Aplikasi *native* Android/iOS.
- Penyimpanan file unggahan di dalam *container filesystem* (untuk menjaga sifat *stateless* *pod*).
- *High availability* atau *failover* database (fokus eksperimen adalah pemulihan *pod* aplikasi Kubernetes).

------------------------------------------------------------------------

## 7. Prinsip UI/UX: Harus Mudah Dibaca Petambak

SIMTAG tidak boleh terlihat seperti *dashboard* teknis untuk *developer*. Pengguna utama harus dapat memahami informasi tanpa harus mengerti istilah teknis seperti *throughput*, *cluster*, *API*, atau *database*.

### 7.1 Prinsip Visual

1. **Mobile-first:** utamakan tampilan telepon genggam karena petambak mengakses dari area tambak.
2. **Bahasa Indonesia sederhana:** gunakan "Hasil Panen", bukan *"Production Output"*; "Catat Pengamatan Air", bukan *"Water Quality Telemetry"*.
3. **Huruf besar dan jelas:** ukuran teks isi disarankan $16\text{--}18\text{ px}$; judul dan angka ringkasan lebih besar.
4. **Kontras kuat:** teks gelap pada latar terang; hindari teks abu-abu muda.
5. **Tombol besar:** aksi utama seperti "Catat Panen" dan "Laporkan Kendala" mudah disentuh.
6. **Satu tugas per layar:** formulir dibagi menjadi langkah pendek, bukan satu formulir panjang.
7. **Ikon selalu disertai teks:** jangan mengandalkan ikon saja.
8. **Warna bukan satu-satunya penanda:** status selalu disertai label teks (misalnya "Aktif", "Panen", atau "Perlu Ditindaklanjuti").
9. **Minim dekorasi:** hindari animasi dan grafik berlebihan.
10. **Responsif:** *layout* tetap terbaca pada ponsel kecil dan desktop.

### 7.2 Rekomendasi Warna dan Status

Gunakan latar putih atau krem sangat muda, teks arang/hitam, dan satu warna utama hijau tua atau biru tua. Warna status harus konsisten dan selalu disertai teks:

- **Hijau:** Aktif / Selesai.
- **Kuning:** Perlu Perhatian / Dalam Proses.
- **Merah:** Kendala / Perlu Tindakan.
- **Abu-abu:** Belum Diisi / Tidak Aktif.

### 7.3 Contoh Beranda Petambak

Urutan komponen pada layar pertama:

1. Sapaan dan nama pengguna.
2. Kartu "Tambak Saya" dengan jumlah tambak.
3. Kartu status tambak (misalnya "2 Aktif" dan "1 Perlu Perhatian").
4. Tombol besar **Catat Panen**.
5. Tombol **Catat Pengamatan Air**.
6. Tombol **Laporkan Kendala**.
7. Daftar "Catatan Terakhir".

### 7.4 Contoh Beranda Koordinator

Urutan komponen:

1. Filter periode.
2. Kartu total anggota dan total tambak.
3. Kartu tambak berdasarkan status.
4. Total hasil panen pada periode terpilih.
5. Daftar kendala yang belum selesai.
6. Grafik produksi sederhana.
7. Aktivitas terbaru.

### 7.5 Formulir yang Ramah Pengguna

- Label *field* selalu terlihat; jangan hanya memakai *placeholder*.
- Berikan contoh format dan satuan yang eksplisit, misalnya "Volume panen (kg)".
- Validasi langsung dengan pesan yang mudah dimengerti.
- Konfirmasi sebelum menghapus data.
- Tampilkan pesan konfirmasi setelah menyimpan, misalnya "Catatan panen berhasil disimpan".
- Jangan memaksa pengisian parameter yang tidak diukur.

### 7.6 Aksesibilitas dan Kondisi Lapangan

- Penuhi kontras minimal WCAG 2.2 AA (rasio minimal $4.5:1$ untuk teks normal).
- Pengguna dapat memperbesar teks tanpa merusak tata letak.
- Hindari informasi penting yang hanya muncul saat *hover*.
- Area sentuh tombol utama minimal $44 \times 44\text{ CSS px}$.
- Uji keterbacaan pada kecerahan layar tinggi di luar ruangan dan gunakan aset halaman yang ringan.

Rujukan aksesibilitas: W3C, *Web Content Accessibility Guidelines (WCAG) 2.2*, https://www.w3.org/TR/WCAG22/.

------------------------------------------------------------------------

## 8. Kebutuhan Data

### 8.1 Data Anggota (`members`)

| Field | Tipe / Keterangan |
| :--- | :--- |
| `member_id` | Primary Key (ID anggota) |
| `name` | Nama lengkap anggota |
| `username` | Username atau email unik untuk login |
| `password_hash` | Hash kata sandi terenkripsi |
| `phone` | Nomor kontak (akses terbatas hanya untuk Koordinator/Admin) |
| `role` | `petambak` atau `koordinator_admin` |
| `status` | `aktif` / `nonaktif` |

### 8.2 Data Tambak (`ponds`)

| Field | Tipe / Keterangan |
| :--- | :--- |
| `pond_id` | Primary Key (ID tambak) |
| `pond_code` | Kode unik tambak |
| `pond_name` | Nama tambak (opsional) |
| `member_id` | Foreign Key ke `members` (pemilik/pengelola) |
| `village` | Desa/kelurahan |
| `district` | Kecamatan |
| `regency` | Kabupaten/kota |
| `latitude` | Koordinat lintang (nullable, diberi label simulasi jika data contoh) |
| `longitude` | Koordinat bujur (nullable, diberi label simulasi jika data contoh) |
| `area_m2` | Luas lahan dalam satuan $\text{m}^2$ |
| `operational_status` | Persiapan, pengisian air, penguapan, kristalisasi/panen, jeda, tidak aktif |
| `notes` | Keterangan tambahan |

### 8.3 Siklus dan Kegiatan Produksi (`production_cycles` & `pond_activities`)

| Field | Tipe / Keterangan |
| :--- | :--- |
| `cycle_id` | Primary Key (ID siklus produksi) |
| `pond_id` | Foreign Key ke `ponds` |
| `start_date` | Tanggal mulai siklus |
| `end_date` | Tanggal selesai siklus (nullable jika masih berjalan) |
| `cycle_status` | `persiapan`, `berjalan`, `selesai`, `dibatalkan` |
| `activity_id` | Primary Key catatan kegiatan |
| `activity_type` | Jenis kegiatan operasional |
| `activity_date` | Tanggal kegiatan dilakukan |
| `description` | Catatan singkat kegiatan |
| `recorded_by` | Foreign Key ke `members` (pengguna pencatat) |

### 8.4 Data Panen (`harvests`)

| Field | Tipe / Keterangan |
| :--- | :--- |
| `harvest_id` | Primary Key (ID panen) |
| `cycle_id` | Foreign Key ke `production_cycles` |
| `pond_id` | Foreign Key ke `ponds` |
| `harvest_date` | Tanggal panen |
| `quantity` | Jumlah hasil panen (numerik) |
| `unit` | `kg` atau `ton` (sistem mengonversi standar ke `kg` saat rekapitulasi) |
| `notes` | Catatan panen |
| `recorded_by` | Foreign Key ke `members` |

### 8.5 Log Pengamatan Air (`water_observations`)

| Field | Tipe / Keterangan |
| :--- | :--- |
| `observation_id` | Primary Key (ID pengamatan) |
| `pond_id` | Foreign Key ke `ponds` |
| `observed_at` | Tanggal dan waktu pengamatan |
| `salinity_value` | Nilai salinitas / Baumé (nullable) |
| `temperature_c` | Suhu air dalam $^{\circ}\text{C}$ (nullable) |
| `ph_value` | Nilai pH air (nullable) |
| `instrument` | Alat ukur yang digunakan (misal: *Baumé meter*, termometer, atau kosong) |
| `notes` | Kondisi atau catatan tambahan |
| `recorded_by` | Foreign Key ke `members` |

*(Nilai yang tidak diukur wajib disimpan sebagai `NULL`, bukan angka nol atau angka perkiraan).*

### 8.6 Data Cuaca (`weather_logs` --- Prioritas: Should)

| Field | Tipe / Keterangan |
| :--- | :--- |
| `weather_id` | Primary Key (ID catatan cuaca) |
| `pond_id` | Foreign Key ke `ponds` (atau wilayah kelompok) |
| `observed_date` | Tanggal pengamatan cuaca |
| `condition` | Cerah, berawan, mendung, hujan ringan, hujan lebat |
| `is_raining` | Boolean (`ya` / `tidak`) |
| `impact_notes` | Dampak yang diamati terhadap tambak |
| `recorded_by` | Foreign Key ke `members` |

### 8.7 Data Kendala (`issue_reports`)

| Field | Tipe / Keterangan |
| :--- | :--- |
| `issue_id` | Primary Key (ID laporan kendala) |
| `pond_id` | Foreign Key ke `ponds` |
| `reported_by` | Foreign Key ke `members` (pelapor) |
| `reported_at` | Waktu laporan dibuat |
| `category` | Kategori kendala (tanggul bocor, pompa/saluran, cuaca/hujan, lainnya) |
| `description` | Deskripsi rinci kendala |
| `priority` | `rendah`, `sedang`, `tinggi` |
| `status` | `terbuka`, `diproses`, `selesai` |
| `follow_up_notes` | Catatan tindak lanjut oleh koordinator |
| `resolved_at` | Waktu kendala selesai ditangani (nullable) |

### 8.8 Kejujuran dan Sumber Data

- Dataset SIPETA yang tersedia di artikel tidak menyertakan seluruh data mentah dan koordinat.
- Jangan mengklaim data simulasi sebagai data lapangan.
- Koordinat dan angka produksi simulasi wajib diberi label `[Data Simulasi]`.
- Jangan menampilkan nama dan nomor kontak nyata tanpa izin.
- Setiap laporan harus mencantumkan periode waktu dan sumber data.

------------------------------------------------------------------------

## 9. Aturan Analisis Produktivitas (Prioritas: Could)

Jika fitur *K-Means* dikerjakan pada tahap lanjutan, analisis mengikuti inspirasi artikel SIPETA (Romli et al., 2025):

- **Variabel utama:** luas lahan ($\text{m}^2$) dan volume produksi ($\text{kg}$).
- **Jumlah cluster:** $K = 3$.
- **Label cluster:** produktivitas relatif rendah, sedang, dan tinggi.
- **Pra-pemrosesan:** data dibersihkan dan dinormalisasi (*Min-Max* atau *Z-Score*) karena satuan luas dan volume produksi berbeda.
- **Keterbatasan:** *K-Means* mengelompokkan produktivitas kuantitatif berdasarkan luas lahan dan hasil panen, bukan pengukuran kadar $\text{NaCl}$ atau kualitas kimia garam.

------------------------------------------------------------------------

## 10. Kebutuhan Fungsional

Tabel berikut diselaraskan sepenuhnya dengan cakupan fitur pada Bagian 6:

| ID | Ref Fitur | Kebutuhan Fungsional | Prioritas |
| :--- | :--- | :--- | :--- |
| **FR-01** | F-01 | Login dan pembatasan akses berdasarkan *role* (`petambak` vs `koordinator_admin`) | **Must** |
| **FR-02** | F-02 | Petambak hanya dapat melihat dan mengelola catatan tambak yang menjadi tanggung jawabnya | **Must** |
| **FR-03** | F-03 | Koordinator melihat ringkasan operasional seluruh tambak dalam kelompok | **Must** |
| **FR-04** | F-04, F-05 | CRUD data anggota dan profil tambak | **Must** |
| **FR-05** | F-06 | Memperbarui status operasional tambak | **Must** |
| **FR-06** | F-07, F-08 | Membuat siklus produksi dan mencatat kegiatan operasional | **Must** |
| **FR-07** | F-09 | Mencatat hasil panen dengan tanggal, kuantitas, dan konversi satuan (`kg`/`ton`) | **Must** |
| **FR-08** | F-10 | Melihat riwayat panen per tambak dan per periode | **Must** |
| **FR-09** | F-11 | Mencatat log pengamatan air secara manual (mendukung nilai kosong/`NULL`) | **Must** |
| **FR-10** | F-12 | Membuat laporan kendala oleh petambak dan memperbarui status tindak lanjut oleh koordinator | **Must** |
| **FR-11** | F-13 | Pencarian dan filter data berdasarkan tambak, anggota, status, dan periode | **Must** |
| **FR-12** | F-14 | Menghasilkan rekap laporan berdasarkan rentang periode terpilih | **Must** |
| **FR-13** | F-15 | Menyediakan *endpoint* *liveness*, *readiness*, dan pengujian beban untuk Kubernetes | **Must** |
| **FR-14** | Bagian 6.2 | Mencatat kondisi cuaca harian secara manual | **Should** |
| **FR-15** | Bagian 6.2 | Menampilkan peta lokasi tambak interaktif | **Should** |
| **FR-16** | Bagian 6.2 | Ekspor rekap laporan ke CSV atau format cetak PDF | **Should** |
| **FR-17** | Bagian 6.2 | Mencatat jejak waktu dan pengguna pembuat/pengubah data (*audit log*) | **Should** |
| **FR-18** | Bagian 6.2 | Menampilkan analisis pengelompokan produktivitas *K-Means* ($K=3$) | **Could** |

------------------------------------------------------------------------

## 11. Arsitektur Aplikasi dan Prinsip Stateless

Arsitektur yang digunakan adalah **monolit web stateless** agar seluruh replika aplikasi menjalankan *container image* yang identik dan dapat digantikan kapan saja oleh Kubernetes tanpa kehilangan state pengguna.

```text
Browser Petambak / Koordinator / Load Generator (k6 / hey)
                            |
                            v
                   Kubernetes Service
                            |
              +-------------+-------------+
              |             |             |
              v             v             v
          App Pod 1     App Pod 2     App Pod 3
              |             |             |
              +-------------+-------------+
                            |
                            v
               Shared MySQL/MariaDB Database
             (Berada di luar target worker node)
```

### 11.1 Prinsip Stateless Wajib

- **Penyimpanan Data:** Seluruh data transaksi disimpan pada database bersama (MySQL/MariaDB), bukan di memori atau *filesystem* *pod*.
- **Manajemen Session:** *Session* login wajib disimpan di database (`SESSION_DRIVER=database` pada Laravel) atau menggunakan *stateless* JWT pada *HTTP-Only Cookie*. Dilarang menggunakan *file session* lokal di dalam *pod*.
- **Isolasi Database:** Database ditempatkan di luar *worker node* yang menjadi target simulasi pematian *node* agar kegagalan *worker* hanya menguji pemulihan *pod* aplikasi.
- **Identifikasi Instance:** Setiap respons HTTP menyertakan header `X-App-Instance` berisi nilai `HOSTNAME` *pod* untuk memverifikasi distribusi beban (*load balancing*) dan perpindahan trafik saat terjadi gangguan.

### 11.2 Teknologi Usulan

| Lapisan | Teknologi |
| :--- | :--- |
| **Backend** | PHP (Laravel dengan `SESSION_DRIVER=database`) atau Python (FastAPI/Flask) |
| **Frontend** | Server-side templates (Blade / Jinja2), HTML5, CSS3, JavaScript |
| **UI Framework** | Bootstrap 5 (komponen tombol besar & kontras tinggi) |
| **Grafik & Peta** | Chart.js (MVP) & Leaflet.js (tahap *Should*) |
| **Database** | MySQL 8.0 / MariaDB |
| **Container & Orchestration** | Docker & Kubernetes (1 Control Plane, minimal 2 Worker Node) |
| **Load Testing** | `k6` atau `hey` |
| **Observability Eksperimen** | `kubectl get pods -w`, `kubectl get events`, logs container, dan metrik *load generator* |

------------------------------------------------------------------------

## 12. Kesesuaian dengan UAS Kelompok 5

### 12.1 Ketentuan Asli UAS yang Relevan

- **Topik umum:** Tambak Garam.
- **Fokus Kelompok 5:** Pemulihan Kubernetes (*Kubernetes Recovery*).
- **Syarat aplikasi:** *Stateless* pada lapisan aplikasi.
- **Topologi cluster:** 1 *control plane* dan minimal 2 *worker node*.
- **Pola beban:** *Request* dikirim terus-menerus selama injeksi gangguan berlangsung.
- **Variabel eksperimen:** Jumlah replika (1 vs 3) serta 3 jenis gangguan (proses aplikasi, *pod*, dan *worker node*).
- **Metrik utama:** Durasi gangguan yang dirasakan *client*, jumlah *request* gagal, waktu *pod* pengganti berstatus `Ready`, dan *latency* selama gangguan.
- **Dokumentasi penempatan:** Distribusi penempatan *pod* antar-*worker node* dicatat sebelum dan sesudah gangguan.
- **Pengulangan:** Minimal 10 pengulangan per skenario.

### 12.2 Pertanyaan Penelitian

**Bagaimana jumlah replika dan penempatan pod memengaruhi dampak gangguan serta waktu pemulihan layanan SIMTAG pada Kubernetes?**

### 12.3 Variabel Eksperimen

- **Variabel bebas:** Jumlah replika (`1` dan `3`), jenis kegagalan (`proses aplikasi`, `pod`, `worker node`).
- **Variabel terukur:** Durasi gangguan *client* (detik), jumlah/persentase *request* gagal, waktu pemulihan komponen hingga status `Ready` (detik), dan *latency* ($p_{95}$ / rata-rata) selama gangguan.
- **Variabel kontrol:** Spesifikasi *container image*, konfigurasi database, laju *request* (*RPS*), durasi pengujian, serta `resources.requests` dan `resources.limits` pada manifest Kubernetes.

### 12.4 Matriks Pengujian

| Skenario Gangguan | 1 Replika | 3 Replika |
| :--- | :--- | :--- |
| **1. Proses aplikasi dihentikan** | 10 pengulangan | 10 pengulangan |
| **2. Satu pod dihapus** | 10 pengulangan | 10 pengulangan |
| **3. Satu worker node dihentikan** | 10 pengulangan | 10 pengulangan |
| **Total Pengujian** | **30 run** | **30 run** |

Total keseluruhan eksperimen adalah $3 \times 2 \times 10 = 60\text{ run}$.

### 12.5 Prosedur Eksperimen & Simulasi Gangguan

1. Siapkan *cluster* Kubernetes (1 *control plane* dan minimal 2 *worker node*) dan pastikan database bersama berjalan di luar *worker node* target gangguan.
2. Deploy SIMTAG dengan **1 replika**, atur `readinessProbe` dan `livenessProbe` dengan `periodSeconds` yang responsif (misal $2\text{--}3\text{ detik}$).
3. Jalankan *load generator* (`k6` atau `hey`) dari luar *pod* aplikasi secara kontinu.
4. Lakukan injeksi gangguan sesuai prosedur baku berikut agar konsisten pada setiap pengulangan:
   - **Skenario 1 (Proses aplikasi dihentikan):** Menghentikan proses utama (PID 1) di dalam *container* menggunakan perintah `kubectl exec <nama-pod> -- kill -9 1` (atau memanggil *endpoint* terproteksi `POST /test/crash` yang mengeksekusi terminasi proses) untuk memicu *container restart* oleh Kubelet.
   - **Skenario 2 (Satu pod dihapus):** Menghapus *pod* secara paksa menggunakan perintah `kubectl delete pod <nama-pod> --grace-period=0 --force` untuk memicu pembuatan *pod* pengganti oleh ReplicaSet.
   - **Skenario 3 (Satu worker node dihentikan):** Mematikan mesin/VM *worker node* tempat *pod* berada (atau simulasi penghentian layanan `kubelet` / mematikan *instance* worker) untuk mengamati deteksi *node NotReady* dan penjadwalan ulang *pod* ke *worker* yang sehat.
5. Catat *timestamp* injeksi gangguan, log *request* gagal, *latency*, waktu *pod* baru mencapai status `Ready`, serta `kubectl get events`.
6. Ulangi masing-masing skenario sebanyak 10 kali.
7. Ubah konfigurasi menjadi **3 replika** (gunakan `podAntiAffinity` atau `topologySpreadConstraints` agar *pod* tersebar di beberapa *worker node*), dokumentasikan distribusi penempatannya, lalu ulangi seluruh langkah pengujian.
8. Bandingkan waktu pemulihan komponen Kubernetes dengan durasi gangguan aktual yang dirasakan oleh *client*.

### 12.6 Endpoint Pengujian & Beban Kerja

- `GET /health/live`: *Liveness probe* Kubernetes (mengembalikan HTTP `200 OK` selama proses aplikasi hidup).
- `GET /health/ready`: *Readiness probe* Kubernetes (melakukan kueri ringan `SELECT 1` ke database; mengembalikan HTTP `200 OK` hanya jika koneksi database siap).
- `GET /test/read-only/ponds`: *Endpoint* uji beban untuk mengambil daftar tambak dan status dari database.
- `GET /test/read-only/dashboard`: *Endpoint* uji beban untuk mengambil ringkasan agregat produksi kelompok dari database.
- `POST /test/crash` *(Opsional)*: *Endpoint* terkontrol (wajib menyertakan header rahasia `X-Test-Key`) untuk menyimulasikan kegagalan proses aplikasi secara presisi.

**Catatan Teknis Pengujian:**
1. *Endpoint* `/test/read-only/*` **tidak menggunakan autentikasi sesi browser** (dibuat terbuka khusus data agregat/simulasi non-sensitif atau divalidasi dengan header statis `X-Test-Key`). Hal ini mencegah *load generator* (`k6`/`hey`) sekadar menerima respons *redirect* ke halaman `/login` tanpa benar-benar menguji kueri database pada setiap *pod*.
2. Pengujian beban difokuskan pada operasi pembacaan database (*read-only*) untuk menghindari anomali duplikasi data (*duplicate write*) saat *load generator* melakukan percobaan ulang (*retry*) ketika *pod* gagal, sekaligus tetap menguji stabilitas *connection pool* database saat *pod* dimatikan dan dinyalakan ulang.

------------------------------------------------------------------------

## 13. Kriteria Penerimaan (Acceptance Criteria)

MVP dinyatakan selesai dan siap diuji apabila:

1. Petambak dapat login dan melihat daftar tambak miliknya sendiri.
2. Petambak dapat mencatat kegiatan, hasil panen, pengamatan air, dan laporan kendala.
3. Koordinator/Admin dapat mengelola data anggota/tambak, melihat ringkasan kelompok, serta menindaklanjuti kendala.
4. Riwayat panen dan laporan periode dapat difilter berdasarkan tambak dan rentang tanggal.
5. Antarmuka mudah dibaca pada layar ponsel dengan teks jelas, kontras tinggi, dan tombol utama minimal $44 \times 44\text{ px}$.
6. Seluruh data tersimpan pada database bersama dan *session* login tidak bergantung pada *filesystem* lokal *pod*.
7. Aplikasi berjalan normal pada konfigurasi 1 replika maupun 3 replika dengan header `X-App-Instance` yang berbeda antar-*pod*.
8. *Load generator* eksternal dapat menembak `/test/read-only/ponds` dan `/test/read-only/dashboard` tanpa terhalang halaman login.
9. Seluruh bukti pengujian (60 *run*), log `kubectl events`, status penempatan *node*, dan grafik hasil tercatat lengkap.

------------------------------------------------------------------------

## 14. Risiko dan Mitigasi

| Risiko | Mitigasi |
| :--- | :--- |
| Data lapangan tidak tersedia | Menggunakan data simulasi yang diberi label `[Data Simulasi]` secara transparan |
| Ruang lingkup terlalu besar untuk waktu UAS | Mengunci 15 fitur inti pada MVP (Bagian 6.1) dan menunda fitur *Should/Could* |
| *Pod* gagal bersifat *stateless* akibat file unggahan | Fitur upload foto dinonaktifkan pada MVP; *session* menggunakan database/JWT |
| *Load test* hanya mengukur halaman redirect login | *Endpoint* `/test/read-only/*` dibebaskan dari *session* login browser |
| Database ikut mati saat *worker node* dihentikan | Database ditempatkan di luar *worker node* target gangguan |
| Tiga replika menumpuk pada satu *worker node* | Menggunakan `podAntiAffinity` / `topologySpreadConstraints` dan memverifikasi lewat `kubectl get pods -o wide` |

------------------------------------------------------------------------

## 15. Referensi Penelitian

1. Wiyanto, D. B., & Sulistiorini, D. A. (2018). Aplikasi Sistem Informasi Geografis untuk Pemetaan Tambak Garam di Kabupaten Sidoarjo. *Rekayasa*, 11(1), 1--10. https://doi.org/10.21107/rekayasa.v11i1.4119
2. Ula, M., Salamah, Fajri, R., Fadillah, T. F., & Juandana, R. A. (2024). Implementasi Alat Monitoring Kualitas Garam Berbasis Ramah Lingkungan pada Petani Tambak Ujung Pusong Jaya, Gampong Lancang Barat, Kecamatan Dewantara, Kabupaten Aceh Utara. *AJAD: Jurnal Pengabdian kepada Masyarakat*, 4(3), 562--569. https://doi.org/10.59431/ajad.v4i3.393
3. Romli, M., Habibi, A., & Arman, A. (2025). Pemetaan Hasil Produksi Tambak Garam di Kabupaten Sampang Menggunakan K-Means Clustering. *Science Electro*, 20(1), 32--36. https://jim.unisma.ac.id/index.php/jte/article/view/29764

------------------------------------------------------------------------

## 16. Checklist Dokumen Laporan UAS

- [ ] Cover: judul dan identitas anggota Kelompok 5.
- [ ] Pendahuluan & latar belakang *case study*.
- [ ] Tinjauan penelitian terkait (3 jurnal referensi).
- [ ] Metode penelitian: diagram arsitektur *stateless* Kubernetes, spesifikasi *cluster*, dan *flowchart* pengujian.
- [ ] Hasil dan analisis: tabel hasil 60 *run*, grafik perbandingan 1 vs 3 replika, penempatan *pod* antar-*node*, serta analisis perbedaan waktu pemulihan komponen vs gangguan *client*.
- [ ] Kesimpulan dan saran.
- [ ] Daftar pustaka.
- [ ] Lampiran: manifest YAML Kubernetes (`Deployment`, `Service`, `Probes`), *script* *load test*, log `kubectl`, dan tangkapan layar aplikasi.
- [ ] Slide PowerPoint presentasi.

------------------------------------------------------------------------

## 17. Keputusan Final Kelompok (Baseline v1.0)

| No | Aspek Keputusan | Ketetapan Final untuk Eksekusi |
| :--- | :--- | :--- |
| 1 | **Nama Produk** | Resmi menggunakan nama **SIMTAG (Sistem Informasi Manajemen Tambak Garam)**. |
| 2 | **Pemisahan Role** | Untuk MVP, peran **Koordinator dan Administrator digabung** (`koordinator_admin`), sedangkan peran **Petambak** dipisah ketat. |
| 3 | **Sumber Data Demo** | Menggunakan **data simulasi terstruktur** dengan label `[Data Simulasi]` yang jelas pada antarmuka dan laporan. |
| 4 | **Cakupan Fitur MVP** | Mengunci 15 fitur pada Bagian 6.1 (tanpa upload foto lokal, tanpa cuaca, dan tanpa peta interaktif pada tahap MVP). |
| 5 | **Strategi Stateless** | Menggunakan database bersama di luar *worker node* target gangguan, serta *session* berbasis database/token. |