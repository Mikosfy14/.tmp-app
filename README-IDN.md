# .tmp Project Management

**Sistem Pemantauan Proyek SDLC & Tata Kelola Katalog Aplikasi Departemen**

---

> **Bahasa Indonesia** | [English Version](README.md)

---

## Ringkasan Eksekutif

**.tmp Project Management** adalah platform berbasis web yang dirancang untuk mengotomatisasi pengawasan siklus pengembangan perangkat lunak (*Software Development Life Cycle* / SDLC), menyentralisasi inventaris katalog aplikasi, memantau batas waktu kepatuhan kerja (*SLA compliance*), serta mengevaluasi produktivitas personil departemen teknologi informasi secara terintegrasi.

Sistem ini dibangun menggunakan arsitektur **CodeIgniter 4**, terhubung langsung dengan basis data **Microsoft SQL Server (MSSQL)** dengan pola penyimpanan dokumen biner `VARBINARY(MAX)`, serta dibalut oleh antarmuka modern **Mazer Admin Dashboard (Bootstrap 5)** yang mendukung mode tampilan terang dan gelap (*Light & Dark Mode*).

---

## Fitur Utama

### 1. Dual-Layer Analytics Dashboard
* **Personal Dashboard (`/dashboard`):** Menampilkan ringkasan metrik pribadi (Proyek Aktif, Selesai, Tepat Waktu, Terlambat, Overdue, dan Risk/Urgent), daftar tugas prioritas (*Priority Tasks*), grafik tren penyelesaian bulanan, serta indikator batas waktu visual.
* **Executive Team Performance Dashboard (`/kinerja-tim`):** Dasbor makro khusus Kepala Departemen untuk mengukur kepatuhan batas waktu (*SLA compliance*), diagram distribusi fase SDLC tim, pintasan filter kuartal (Q1–Q4) & kalender rentang tanggal bebas, serta penelusuran mendalam (*drill-down*) proyek spesifik per anggota tim (`/projects/user/{id}`).

### 2. Project Tracker & SDLC Management (`/projects`)
* **Penguncian Akuntabilitas PIC:** Pengguna pembuat proyek otomatis dikunci oleh sistem sebagai *Primary PIC* (*Creator Lock*) yang tidak dapat dihapus, dengan opsi penugasan kolaborator tambahan (*Multi-PIC*).
* **Kalkulasi Otomatis Deadline Status:** Sistem mengevaluasi selisih tanggal secara real-time ke dalam status visual:
  * *On Track* (Sisa waktu > 7 hari)
  * *Risk* (Sisa waktu 4–7 hari)
  * *Urgent* (Sisa waktu 2–3 hari)
  * *Critical / Besok* (Sisa waktu <= 1 hari)
  * *Overdue* (Melewati target tanggal tanpa promote)
* **Penyimpanan Dokumen Biner di SQL Server:** Berkas lampiran spesifikasi teknis (PDF, Word, Excel maks 5 MB) dikonversi dan disimpan langsung di dalam basis data MSSQL menggunakan tipe data `VARBINARY(MAX)`.
* **Penyaringan Lanjutan:** Mendukung filter status SDLC, status penyelesaian (*Completed / Not Completed / All*), kata kunci pencarian, serta ekspor laporan resmi ke format **Excel (.xlsx)** dan **PDF**.

### 3. Katalog Tata Kelola Aplikasi (`/aplikasi`)
* **Inventaris 18 Parameter Teknis:** Mendokumentasikan identitas sistem, model arsitektur (*Monolith / Microservices*), platform bahasa, metode autentikasi, URL lingkungan (*Production, UAT, Development*), pemilik bisnis (*Business Owner*), pemilik sistem (*System Owner*), dan skema lisensi.
* **Tingkat Kekritisan Pemulihan (Criticality Tiers):** Pengelompokan sistem berdasarkan klasifikasi mitigasi bencana:
  * **C1:** *Mission Critical* (Toleransi padam terendah)
  * **C2:** *Business Critical*
  * **C3:** *Business Operational*
  * **C4:** *Non-Critical*
* **Dukungan Ekspor:** Rekapitulasi katalog dapat diunduh instan ke format Excel dan PDF.

### 4. Manajemen Pengguna & Kepegawaian (`/users` - Khusus Kadept)
* **Manajemen Akun Terpusat:** Penambahan pengguna baru, pembaruan profil, aktivasi/deaktivasi akun, dan penyetelan ulang sandi (*reset password*).
* **Sinkronisasi Kategori Otomatis:** Sistem secara inheren menyinkronkan kategori karyawan dari role yang dipilih (Organik untuk Kadept & Staff, NonOrganik untuk Manmonth).
* **Proteksi Swa-Deaktivasi (*Self-Deactivation Protection*):** Sistem secara ketat mengunci akun Kepala Departemen yang sedang login agar tidak dapat dinonaktifkan sendiri, mencegah terjadinya *lockout* administratif.
* **Analitik Kinerja Individual Staf:** Halaman detail pengguna (`/users/detail/{id}`) menyajikan rekapitulasi performa individual dan daftar seluruh proyek yang ditugaskan kepada staf terkait.

### 5. Keamanan & Ketahanan Sistem (*Security & Resilience*)
* **Session Idle Timeout 15 Menit:** Pengguna yang tidak aktif selama 15 menit otomatis dikeluarkan demi keselamatan data, disinkronkan berkala melalui request latar belakang (*heartbeat*).
* **Proteksi Aksi Berbahaya:** Tindakan berisiko tinggi (Hapus Proyek, Hapus Aplikasi, Nonaktifkan Akun, Reset Password) diwajibkan melalui modal konfirmasi resmi dengan penyebutan nama entitas target secara dinamis.
* **Halaman Galat Kustom Mandiri:** Tampilan error terisolasi dan mandiri (*resilient*) untuk status HTTP **403 (Forbidden)**, **404 (Not Found)**, dan **500 (Internal Server Error)** dengan navigasi kembali cerdas (*smart history fallback*).

---

## Matriks Hak Akses Pengguna (User Access Matrix)

| Modul / Fitur | Kepala Departemen (`role_id: 1`) | Staff (`role_id: 2`) | Manmonth (`role_id: 3`) |
| :--- | :---: | :---: | :---: |
| **Kategori Kepegawaian** | Organik | Organik | NonOrganik |
| **Personal Dashboard (`/dashboard`)** | Akses Penuh | Akses Penuh | Akses Penuh |
| **Project Tracker (Lihat & Tambah)** | Akses Penuh | Akses Penuh | Akses Penuh |
| **Project Tracker (Edit Proyek)** | Semua Proyek | Hanya Proyek Milik Sendiri | Hanya Proyek Milik Sendiri |
| **Pengelolaan Aplikasi (`/aplikasi`)** | Akses Penuh | Akses Penuh | Akses Penuh |
| **Kinerja Tim (`/kinerja-tim`)** | Akses Penuh | Terblokir (HTTP 403) | Terblokir (HTTP 403) |
| **Manajemen Pengguna (`/users`)** | Akses Penuh | Terblokir (HTTP 403) | Terblokir (HTTP 403) |
| **Profil Mandiri (`/profile`)** | Akses Penuh | Akses Penuh | Akses Penuh |

---

## Teknologi yang Digunakan

* **Backend:** PHP 8.2+ dengan CodeIgniter 4 (MVC Framework)
* **Basis Data:** Microsoft SQL Server (MSSQL) melalui ODBC Driver / `sqlsrv`
* **Frontend:** Mazer Admin Dashboard, Bootstrap 5.3, Bootstrap Icons, FontAwesome 6
* **Visualisasi & Analitik:** ApexCharts, Chart.js
* **Komponen Antarmuka:** Flatpickr (Pemilih Rentang Tanggal), SweetAlert2
* **Ekspor Berkas:** DOMPDF (Cetak PDF), PhpSpreadsheet (Cetak Excel)

---

## Persyaratan Sistem

Sebelum menjalankan aplikasi, pastikan lingkungan server lokal memenuhi spesifikasi berikut:
* **PHP:** Versi 8.2 atau lebih baru
* **Ekstensi PHP Aktif:**
  * `php_sqlsrv` dan `php_pdo_sqlsrv` (Driver koneksi Microsoft SQL Server)
  * `php_intl`
  * `php_mbstring`
  * `php_fileinfo`
  * `php_gd`
* **Web Server:** Apache / Nginx (Direkomendasikan via Laragon atau XAMPP)
* **Database Engine:** Microsoft SQL Server 2016 atau versi lebih baru

---

## Panduan Instalasi & Menjalankan Aplikasi

### 1. Kloning Repositori
```bash
git clone https://github.com/username/.tmp-dashboard-app.git
cd .tmp-dashboard-app
```

### 2. Instalasi Dependensi Composer
```bash
composer install
```

### 3. Konfigurasi Lingkungan (.env)
Salin file `env` menjadi `.env`:
```bash
cp env .env
```
Buka file `.env`, sesuaikan pengaturan basis data Microsoft SQL Server Anda:
```ini
CI_ENVIRONMENT = development

app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_username
database.default.password = your_password
database.default.DBDriver = SQLSRV
database.default.port     = 1433
```

### 4. Menjalankan Server Lokal
Jalankan perintah Spark bawaan CodeIgniter:
```bash
php spark serve
```
Akses aplikasi melalui peramban pada alamat:
```
http://localhost:8080
```

---

## Akun Pengujian Default

Untuk keperluan pengujian lokal, sistem telah dilengkapi akun contoh berikut:

| Peran | Username | Password Default | Kategori |
| :--- | :--- | :--- | :--- |
| **Kepala Departemen** | `kadept` | `user123` | Organik |
| **Staff** | `shafiq` | `user123` | Organik |
| **Manmonth** | `manmonth` | `user123` | NonOrganik |

---

## Struktur Direktori Utama

```
.tmp-dashboard-app/
├── app/
│   ├── Config/          # Konfigurasi aplikasi, routing, dan filter
│   ├── Controllers/     # Controller logika modul (Auth, Projects, Users, dll.)
│   ├── Filters/         # Guard keamanan (AuthFilter, NoCacheFilter)
│   ├── Helpers/         # Helper kalkulasi deadline dan tanggal kuartal
│   ├── Models/          # Model query basis data SQL Server
│   └── Views/           # Template tampilan antarmuka (Mazer UI)
│       ├── application/ # Halaman modul katalog aplikasi
│       ├── auth/        # Halaman login
│       ├── dashboard/   # Halaman personal dashboard
│       ├── errors/      # Template custom error 403, 404, 500
│       ├── layouts/     # Header, navbar, sidebar, dan footer
│       ├── profile/     # Halaman profil mandiri & ganti sandi
│       ├── projects/    # Halaman modul project tracker
│       ├── team_perf/   # Halaman modul kinerja tim
│       └── users/       # Halaman modul manajemen pengguna
├── public/              # Titik masuk web (index.php), aset CSS/JS, dan gambar
├── tests/               # Berkas pengujian unit & integrasi
└── README.md            # Dokumentasi utama proyek
```

---

## Lisensi & Tata Kelola

Proyek ini dikembangkan dan dikelola untuk kebutuhan operasional internal departemen. Seluruh hak cipta dilindungi.
