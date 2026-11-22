# Aplikasi Akademik Sekolah

Aplikasi manajemen akademik untuk **satu sekolah**, dibangun dengan **Laravel 13 + Filament 5**. Aplikasi ini membantu admin, guru, staf, dan kepala sekolah mengelola data master, kelas, penugasan mengajar, jadwal, kehadiran, tugas, komponen penilaian, nilai, rekap, serta cetak/export laporan.

> Dokumen ini menjelaskan kondisi proyek saat ini. Untuk riwayat keputusan dan handoff pengembangan, lihat juga [`PANDUAN_PROYEK.md`](PANDUAN_PROYEK.md) dan [`AGENTS.md`](AGENTS.md).

## Ringkasan

- **Satu sekolah**: profil sekolah dikelola melalui data `SchoolProfile`.
- **Riwayat akademik dipertahankan**: tahun ajaran, semester, kelas, penempatan siswa, jadwal, absensi, tugas, dan nilai tersimpan per konteks akademik.
- **Master data lintas tahun tidak diduplikasi**: tingkat kelas dan jurusan dikelola sebagai master data.
- **Kelas per tahun ajaran**: kelas dibuat berdasarkan tahun ajaran, tingkat, jurusan, dan nomor rombel.
- **Siswa tidak menyimpan `class_id` langsung**: riwayat penempatan siswa dikelola lewat `class_enrollments`.
- **Guru dibatasi ke data miliknya**: guru hanya melihat/mengelola data yang terkait penugasan mengajarnya sendiri, sesuai kebijakan akses.

## Fitur Utama

### 1. Pengaturan & Akses
- **Profil Sekolah**: NPSN, alamat, kontak, website, logo. Logo dipakai pada kop cetak.
- **Tahun Ajaran**: CRUD + aksi **Aktifkan**. Aktivasi menonaktifkan tahun ajaran lain (admin only).
- **Semester**: CRUD + aksi **Aktifkan**. Hanya semester pada tahun ajaran aktif yang dapat diaktifkan (admin only).
- **Status Kehadiran**: kode, nama, nilai numerik, status aktif. Mendukung nilai negatif (mis. Bolos = -10).
- **Peran & Izin**: Filament Shield + Spatie Permission. Role bawaan: `admin`, `guru`, `staf`, `kepala sekolah`.

### 2. Data Master Akademik
- **Tingkat Kelas**: contoh X, XI, XII.
- **Jurusan**: kode dan nama jurusan.
- **Mata Pelajaran**: kode, nama, dan cakupan (umum / khusus tingkat / khusus jurusan).
- **Guru**: data guru terpisah dari akun user login. NIP disimpan di profil guru, bukan di `users`.
- **Siswa**: data siswa master; status aktif/nonaktif.

### 3. Kelas & Penugasan
- **Kelas/Rombel**: dibuat per tahun ajaran; nama kelas dapat dibentuk dari tingkat + kode jurusan + nomor rombel.
- **Penempatan Siswa / Class Enrollment**: riwayat siswa di kelas dengan status `active`, `completed`, `transferred`, `cancelled`. Mencegah dua penempatan aktif bertabrakan dalam tahun ajaran yang sama.
- **Penugasan Mengajar**: menghubungkan guru, mapel, kelas, dan semester. Satu mapel/kelas/semester bisa memiliki beberapa guru.
- **Wali Kelas / Homeroom Assignment**: penugasan wali kelas.
- **Jadwal**: jadwal mingguan per penugasan mengajar (hari, jam mulai, jam selesai, ruang).

### 4. Kehadiran
- **Sesi Absensi**: sesi kehadiran berdasarkan jadwal/pertemuan.
- **Absensi**: pencatatan status kehadiran siswa per sesi.
- **Riwayat Kehadiran Siswa**: halaman filter siswa + semester, menampilkan riwayat absensi, rekap jumlah pertemuan, total poin, rata-rata poin, persentase kehadiran, dan rincian status. Mendukung **Cetak** dan **Export CSV**.

### 5. Penilaian
- **Komponen Penilaian**: komponen bobot per penugasan mengajar (mis. Tugas Harian, Ulangan Harian). Bobot aktif total per penugasan mengajar **<= 100%**. Nama unik per penugasan mengajar.
- **Tugas**: tugas terkait penugasan mengajar; bisa dikaitkan ke komponen penilaian; punya tenggat dan nilai maksimal.
- **Nilai Tugas**: nilai per siswa per tugas, unik per tugas+siswa, nilai 0..max_score.
- **Input Nilai Rombel**: halaman input massal nilai satu rombel berdasarkan tugas yang dipilih. Simpan semua nilai/catatan sekaligus (upsert).
- **Rekap Nilai Rombel**: rekap nilai per komponen aktif dan nilai akhir berdasarkan bobot.
  - Nilai komponen = rata-rata `(nilai / max_score * 100)` dari tugas pada komponen.
  - Nilai akhir = jumlah `(nilai komponen * bobot / 100)`.
  - Mendukung **Cetak** dan **Export CSV**.
- **Riwayat Nilai Tugas Siswa**: filter siswa + semester, menampilkan daftar nilai tugas dan rekap jumlah/rata-rata/tertinggi/terendah (skala 100). Mendukung **Cetak** dan **Export CSV**.

### 6. Cetak & Export
- Halaman cetak menggunakan CSS `@media print`, menyembunyikan sidebar/topbar/filter/tombol aksi saat print.
- Kop cetak mengambil data dari **Profil Sekolah** (logo, nama, NPSN, alamat, kontak).
- Export CSV menggunakan UTF-8 BOM agar nyaman dibuka di Excel.

## Stack Teknologi

| Komponen | Versi / Keterangan |
|---|---|
| PHP | 8.5+ (proyek saat ini berjalan di PHP 8.5) |
| Laravel | 13.x |
| Filament | 5.x |
| Livewire | 4.x (dependensi Filament) |
| Database | MariaDB (disarankan/yang dipakai proyek ini) |
| AuthZ | spatie/laravel-permission + bezhansalleh/filament-shield |
| Testing | PHPUnit 12 |
| Format kode | Laravel Pint |
| Frontend build | Vite + Tailwind CSS 4 |

## Persyaratan

- PHP 8.3+ sesuai `composer.json`, disarankan PHP 8.5 sesuai environment proyek.
- Composer 2.x
- Node.js + npm
- MariaDB/MySQL-compatible server
- Ekstensi PHP umum Laravel: `mbstring`, `openssl`, `pdo_mysql`, `ctype`, `json`, `tokenizer`, `xml`, `fileinfo`, dll.

## Instalasi

### 1. Clone repository

```bash
git clone https://github.com/hex4coder/akademik.git
cd akademik
```

### 2. Install dependency

```bash
composer install
npm install --ignore-scripts
```

### 3. Siapkan environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` untuk MariaDB:

```env
APP_NAME="Akademik Sekolah"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost:8000

APP_LOCALE=id
APP_FALLBACK_LOCALE=id
APP_FAKER_LOCALE=id_ID

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=akademik_db
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```

> Jangan commit `.env`. Jangan gunakan `migrate:fresh` pada database yang berisi data penting tanpa izin eksplisit.

### 4. Migrasi dan seeding

```bash
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
```

Seeder lain tersedia sesuai kebutuhan pengembangan/data contoh, misalnya `TeacherSeeder`, `AssessmentComponentSeeder`, dll. Periksa `database/seeders/` sebelum menjalankan seeder data contoh di lingkungan nyata.

### 5. Storage link (untuk logo profil sekolah)

```bash
php artisan storage:link
```

### 6. Build asset / jalankan dev

```bash
npm run build
# atau saat pengembangan
npm run dev
```

### 7. Jalankan aplikasi

```bash
php artisan serve
```

Buka: `http://localhost:8000/admin`

## Akun & Role

### Role bawaan
- `admin`
- `guru`
- `staf`
- `kepala sekolah`

### Prinsip akses
- **Admin**: akses penuh.
- **Guru**: hanya melihat data yang terkait penugasan mengajarnya sendiri (kelas, jadwal, absensi, tugas, nilai, komponen penilaian, riwayat terkait). Guru dapat mengelola tugas/nilai/komponen penilaian miliknya sendiri sesuai izin.
- **Staf / Kepala Sekolah**: akses lihat sesuai izin view; tidak mengelola data operasional guru tertentu kecuali diberi izin.
- **Aksi sensitif** seperti “Aktifkan” Tahun Ajaran/Semester dibatasi admin.

### Membuat user admin Filament

```bash
php artisan make:filament-user
```

Lalu assign role admin (contoh via tinker):

```bash
php artisan tinker --execute="App\Models\User::where('email','admin@example.com')->first()?->assignRole('admin');"
```

Sesuaikan email dengan user yang dibuat.

## Struktur Modul

### Resource Filament
- `AcademicYears` — Tahun Ajaran
- `Semesters` — Semester
- `GradeLevels` — Tingkat Kelas
- `Majors` — Jurusan
- `Subjects` — Mata Pelajaran
- `SchoolClasses` — Kelas/Rombel
- `ClassEnrollments` — Penempatan Siswa
- `Teachers` — Guru
- `Students` — Siswa
- `TeachingAssignments` — Penugasan Mengajar
- `HomeroomAssignments` — Wali Kelas
- `Schedules` — Jadwal
- `AttendanceStatuses` — Status Kehadiran
- `AttendanceSessions` — Sesi Absensi
- `Attendances` — Absensi
- `AssessmentComponents` — Komponen Penilaian
- `Assignments` — Tugas
- `AssignmentScores` — Nilai Tugas
- `SchoolProfiles` — Profil Sekolah
- `Roles` — Peran & Izin (Shield)

### Halaman Khusus
- `InputNilaiRombel` — input nilai massal per tugas/rombel.
- `RekapNilaiRombel` — rekap nilai akhir berdasarkan bobot komponen aktif.
- `RiwayatNilaiTugasSiswa` — riwayat nilai tugas siswa per semester.
- `RiwayatKehadiranSiswa` — riwayat dan rekap kehadiran siswa per semester.

## Aturan Bisnis Penting

### Tahun Ajaran & Semester
- Hanya satu tahun ajaran aktif yang diharapkan pada satu waktu.
- Hanya satu semester aktif yang diharapkan pada satu waktu.
- Aktivasi menggunakan transaksi database.
- Tombol aktivasi hanya untuk admin.

### Kelas & Penempatan Siswa
- Kelas unik berdasarkan tahun ajaran, tingkat, jurusan, dan nomor rombel.
- Siswa dapat memiliki riwayat penempatan di `class_enrollments`.
- Tidak boleh ada dua enrollment aktif bertabrakan dalam tahun ajaran yang sama.

### Kehadiran
- Status kehadiran memiliki nilai numerik; bisa negatif.
- Riwayat/rekap dihitung berdasarkan sesi absensi dalam semester terpilih.

### Penilaian
- Komponen penilaian melekat pada `teaching_assignment`.
- Total bobot komponen **aktif** dalam satu penugasan mengajar tidak boleh lebih dari 100%.
- Tugas dapat memiliki `assessment_component_id` nullable.
- Komponen tugas harus berasal dari penugasan mengajar yang sama.
- Nilai tugas unik per `assignment_id + student_id`.
- Nilai harus dalam rentang `0..max_score`.
- Siswa yang dinilai harus aktif di kelas tugas.

### Pembatasan Data Guru
- `OwnTeachingDataScope` adalah helper pusat untuk membatasi data guru.
- Admin/staf/kepala sekolah tidak dibatasi oleh scope guru.
- Guru tanpa profil `Teacher` yang terhubung akan melihat data kosong pada area yang dibatasi.

## Perintah Berguna

```bash
# Jalankan aplikasi
php artisan serve

# Jalankan test
php artisan test --compact

# Jalankan test tertentu
php artisan test --compact tests/Feature/AssignmentTest.php

# Format kode PHP yang berubah
vendor/bin/pint --dirty --format agent

# Bersihkan cache
php artisan optimize:clear

# Generate permission/policy Shield (hati-hati: bisa menimpa policy custom)
php artisan shield:generate --all --panel=admin --option=policies_and_permissions --no-interaction
php artisan db:seed --class=RolesAndPermissionsSeeder
```

> **Catatan Shield:** setelah `shield:generate --all`, periksa diff policy. Beberapa policy custom (mis. ownership guru pada Assignment/AssessmentComponent/AssignmentScore) tidak boleh tertimpa tanpa review. Jalankan ulang `RolesAndPermissionsSeeder` setelah generate.

## Testing

Proyek menggunakan PHPUnit. Tes fitur berada di `tests/Feature`.

Contoh:

```bash
php artisan test --compact
php artisan test --compact tests/Feature/AssessmentComponentTest.php
php artisan test --compact tests/Feature/RiwayatKehadiranSiswaTest.php
```

Cakupan tes saat ini mencakup antara lain: enrollment, penugasan mengajar, jadwal, absensi, riwayat kehadiran, tugas, nilai tugas, komponen penilaian, input nilai rombel, rekap nilai, riwayat nilai, dan pembatasan akses data guru.

## Struktur Direktori Penting

```text
app/
  Filament/
    Pages/                 # Halaman khusus: input/rekap/riwayat
    Resources/             # Resource CRUD Filament
  Models/                  # Eloquent models
  Policies/                # Policy akses, termasuk ownership guru
database/
  migrations/
  seeders/
resources/
  views/
    components/print-kop.blade.php
    filament/pages/        # Blade halaman khusus Filament
tests/
  Feature/
PANDUAN_PROYEK.md          # Dokumen handoff/progres proyek
```

## Catatan Pengembangan

- Gunakan bahasa Indonesia untuk komunikasi dan dokumentasi proyek.
- Kerjakan bertahap; uji sebelum melanjutkan modul besar.
- Jangan minta/commit kredensial sensitif.
- Database proyek menggunakan MariaDB; hindari asumsi SQLite/PostgreSQL.
- Jangan jalankan `migrate:fresh` tanpa izin eksplisit.
- Setiap perubahan penting sebaiknya dicatat ke Obsidian, di-commit, dan di-push.
- `PANDUAN_PROYEK.md` berisi konteks historis; beberapa bagian bisa lebih lama dari kondisi kode terbaru. Jika berbeda, **kode dan tes saat ini** adalah sumber kebenaran utama.

## Lisensi

Proyek ini saat ini menggunakan lisensi MIT pada metadata Composer (`composer.json`). Sesuaikan bagian ini jika kebijakan lisensi repository berubah.
