# Dokumen Serah Terima Proyek Aplikasi Akademik Sekolah

**Tujuan dokumen:** Menjadi konteks awal (handoff) agar model AI/asisten lain dapat melanjutkan pengembangan proyek tanpa mengulang pembahasan dari awal.

**Bahasa kerja:** Bahasa Indonesia  
**Status:** Dalam tahap pembangunan modul data master dan administrasi akademik  
**Terakhir diperbarui:** 22 November 2026

---

## 1. Ringkasan proyek

Pengguna sedang membangun aplikasi akademik untuk **satu sekolah**. Aplikasi ditujukan untuk membantu pengelolaan:

- Profil sekolah.
- Tahun ajaran dan semester.
- Tingkat kelas, jurusan, dan kelas.
- Data guru dan siswa.
- Riwayat penempatan siswa di kelas.
- Pembagian tugas mengajar dan jadwal mingguan.
- Kehadiran siswa.
- Tugas, komponen penilaian, nilai semester, dan laporan akademik.
- Hak akses pengguna sesuai peran.

Pengembangan dilakukan bertahap. Berikan instruksi dalam bahasa Indonesia, sederhana, dan satu langkah kecil setiap kali. Jangan memberikan terlalu banyak perubahan sekaligus. Setelah satu langkah, minta pengguna menguji atau mengonfirmasi hasilnya sebelum melanjutkan.

## 2. Lingkungan teknis yang sudah dipastikan

- Perangkat pengembangan: Xiaomi Pad 8 Pro, RAM 8 GB.
- Lingkungan Linux: Debian XFCE di Android melalui container.
- Lokasi proyek: `~/devs/akademik` (berdasarkan path Composer yang terakhir terlihat; pastikan kembali dengan `pwd` jika diperlukan).
- Framework: Laravel `13.40.0`.
- PHP: `8.5.12`.
- Filament: `5.11.2`.
- Database: MariaDB; database proyek `akademik_db`.
- Composer dan Node.js/npm sudah tersedia.
- UI admin utama menggunakan Filament.
- Database sudah dikonfigurasi dan koneksi Laravel telah diuji.
- Migrasi awal Laravel sudah berhasil dijalankan.

**Keputusan penting:** PostgreSQL pernah dicoba tetapi menemui kendala di lingkungan container Android. Proyek beralih ke MariaDB dan harus tetap menggunakan MariaDB kecuali pengguna sendiri mengubah keputusan.

Jangan meminta kata sandi database kepada pengguna.

## 3. Tujuan desain dan prinsip data

1. Aplikasi hanya mengelola satu sekolah.
2. Data akademik harus mempertahankan riwayat tahun ajaran dan semester lama.
3. Data master yang berlaku lintas tahun ajaran (misalnya tingkat kelas dan jurusan) tidak perlu diduplikasi per tahun.
4. Kelas dibuat per tahun ajaran.
5. Siswa tidak langsung menyimpan `class_id`; riwayat penempatan siswa dikelola melalui tabel `class_enrollments`.
6. Guru dapat mengajar lebih dari satu mata pelajaran dan kelas.
7. Jadwal mingguan menyimpan hari, waktu mulai, waktu selesai, dan ruang; tidak perlu tabel periode/jam pelajaran terpisah sesuai keputusan awal.
8. Pengguna menginginkan sistem yang sederhana dan dapat dioperasikan serta disesuaikan operator sekolah.

## 4. Progress yang telah dikerjakan

### 4.1 Setup proyek

- Laravel berhasil dibuat.
- Filament berhasil dipasang dan panel admin berhasil diinstal.
- Akun admin Filament sudah dibuat.
- Login ke `/admin` berhasil.
- MariaDB sudah terhubung ke Laravel.
- Migrasi bawaan Laravel berhasil dijalankan.

### 4.2 Profil sekolah

Model dan tabel `school_profiles` sudah dibuat.

Kolom yang telah dirancang:
- `id`
- `npsn` (nullable, unique)
- `address` (nullable)
- `phone` (nullable)
- `email` (nullable)
- `website` (nullable)
- `logo_path` (nullable)
- timestamps

Fitur Filament yang telah dikerjakan:
- Form profil sekolah.
- Upload logo dengan `FileUpload`, disk `public`, direktori `school-logos`, visibility `public`, image editor, batas ukuran 2 MB.
- `php artisan storage:link` digunakan untuk membuat symbolic link storage.
- Logo ditampilkan pada table dengan `ImageColumn` dan pada view/infolist dengan `ImageEntry`.
- Pernah terjadi error `Cannot use empty array elements in arrays` karena ada koma ganda `,,` setelah `->height(150)`. Sudah diperbaiki.
- Aturan satu sekolah dibahas: tombol Create seharusnya hanya tampil saat belum ada data. Implementasi `canCreate()` sempat tidak bekerja sebagaimana diharapkan pada Filament 5.11.2. Pembahasan berhenti pada pemeriksaan `SchoolProfileResource.php` dan `ListSchoolProfiles.php`; pastikan status implementasi terakhir jika perlu.
- Pernah dibahas menambahkan constraint database agar hanya ada satu profil, tetapi belum dipastikan apakah migration constraint tersebut benar-benar dibuat. Jangan mengasumsikan sudah diterapkan.

File yang diketahui:
- `app/Models/SchoolProfile.php`
- `app/Filament/Resources/SchoolProfiles/SchoolProfileResource.php`
- `app/Filament/Resources/SchoolProfiles/Schemas/SchoolProfileForm.php`
- `app/Filament/Resources/SchoolProfiles/Schemas/SchoolProfileInfolist.php`
- `app/Filament/Resources/SchoolProfiles/Tables/SchoolProfilesTable.php`
- pages `ListSchoolProfiles.php`, `CreateSchoolProfile.php`, `EditSchoolProfile.php`, `ViewSchoolProfile.php`

### 4.3 Tahun ajaran (`academic_years`)

Model dan migration telah dibuat dan pengguna menyatakan modul Tahun Ajaran selesai.

Rancangan kolom:
- `id`
- `name` (contoh `2026/2027`, unique)
- `start_date`
- `end_date`
- `is_active` (boolean, default false)
- timestamps

Fitur/ketentuan:
- Pengguna membutuhkan Action **Aktifkan**.
- Perilaku yang diinginkan: saat satu tahun ajaran diaktifkan, tahun ajaran lain otomatis menjadi tidak aktif.
- Proses sebaiknya menggunakan `DB::transaction()`.
- Pengguna menyatakan tahun ajaran sudah selesai dan tombol aktivasi telah dibuat. Namun, kode aktual Action belum pernah ditinjau dalam percakapan ini. Verifikasi kode dan uji perilakunya sebelum menganggap seluruh aturan sudah aman.

Resource:
- `app/Filament/Resources/AcademicYears/AcademicYearResource.php`
- `app/Filament/Resources/AcademicYears/Tables/AcademicYearsTable.php`
- `app/Filament/Resources/AcademicYears/Schemas/AcademicYearForm.php`
- pages `ListAcademicYears.php`, `CreateAcademicYear.php`, `EditAcademicYear.php`

### 4.4 Semester (`semesters`)

Model dan migration telah dibuat. Pengguna menyatakan sudah membuat tombol Action Aktifkan.

Rancangan kolom:
- `id`
- `academic_year_id` (FK ke `academic_years`, `restrictOnDelete`)
- `name` (Ganjil/Genap)
- `start_date`
- `end_date`
- `is_active` (boolean, default false)
- timestamps
- unique gabungan `academic_year_id` dan `name`

Relasi:
- `AcademicYear::semesters()` menggunakan `hasMany(Semester::class)`.
- `Semester::academicYear()` menggunakan `belongsTo(AcademicYear::class)`.

Form:
- Select tahun ajaran menggunakan `relationship('academicYear', 'name')`.
- Select semester dengan pilihan `Ganjil` dan `Genap`.
- DatePicker `start_date` dan `end_date`; end date harus setelah atau sama dengan start date.
- `is_active` tidak diubah lewat form; aktivasi menggunakan Action.

Aturan bisnis yang direncanakan:
- Hanya semester dalam tahun ajaran aktif yang boleh diaktifkan.
- Mengaktifkan semester baru menonaktifkan semester aktif sebelumnya.
- Gunakan transaksi database.
- Perlu verifikasi kode Action aktual; pengguna menyatakan tombol sudah dibuat, tetapi kode belum diperiksa.

File yang diketahui:
- `app/Models/Semester.php`
- `app/Filament/Resources/Semesters/SemesterResource.php`
- `app/Filament/Resources/Semesters/Schemas/SemesterForm.php`
- `app/Filament/Resources/Semesters/Tables/SemestersTable.php`
- pages terkait.

### 4.5 Tingkat kelas (`grade_levels`)

Migration, model, dan Filament Resource sudah dibuat. Pengguna menguji modul dan menyatakan selesai.

Kolom:
- `id`
- `name` (varchar 10, unique; contoh X, XI, XII)
- timestamps

Model:
- `app/Models/GradeLevel.php`
- `$fillable = ['name']`
- `classes()` direncanakan/ditambahkan menggunakan `hasMany(SchoolClass::class)`.

Form:
- TextInput `name`, required, max length 10, `unique(ignoreRecord: true)`.

Table:
- menampilkan `name` dan `created_at`
- EditAction dan DeleteAction.

### 4.6 Jurusan (`majors`)

Migration, model, dan Filament Resource sudah dibuat. Pengguna menyatakan selesai.

Kolom:
- `id`
- `code` (varchar 20, unique; contoh DKV, TJKT, MPLB)
- `name` (varchar 150)
- timestamps

Model:
- `app/Models/Major.php`
- `$fillable = ['code', 'name']`
- `classes()` menggunakan `hasMany(SchoolClass::class)`.

Form:
- `code` dan `name`, keduanya required.
- `code` unique dengan `ignoreRecord: true`.

Table:
- `code`, `name`, `created_at`
- EditAction dan DeleteAction.

### 4.7 Kelas (`SchoolClass`)

Model dan migration sudah dibuat. Ada detail penting tentang nama tabel aktual:

- Model bernama `SchoolClass`.
- Perintah `php artisan make:model SchoolClass -m` menghasilkan migration dengan nama tabel **`school_classes`** secara default.
- Error migration yang dialami pengguna menyebut tabel `school_classes`. Jadi jangan menganggap nama tabel aktual `classes` tanpa memeriksa migration/model.
- Migration berhasil setelah nama unique index dipersingkat.

Rancangan kolom:
- `id`
- `academic_year_id` FK `academic_years`, `restrictOnDelete`
- `grade_level_id` FK `grade_levels`, `restrictOnDelete`
- `major_id` FK `majors`, `restrictOnDelete`
- `group_number` unsigned tiny integer, default 1
- `name` varchar 100
- timestamps
- unique gabungan keempat kolom: `academic_year_id`, `grade_level_id`, `major_id`, `group_number`.

Penting: nama indeks otomatis terlalu panjang dan menghasilkan error MariaDB 1059:
`Identifier name 'school_classes_academic_year_id_grade_level_id_major_id_group_number_unique' is too long`.
Sudah diperbaiki dengan nama pendek eksplisit:
```php
$table->unique(
    ['academic_year_id', 'grade_level_id', 'major_id', 'group_number'],
    'classes_academic_group_unique'
);
```
Setelah perubahan itu migration berhasil.

Model:
- `app/Models/SchoolClass.php`
- `$fillable`:
  - `academic_year_id`
  - `grade_level_id`
  - `major_id`
  - `group_number`
  - `name`
- relasi:
  - `academicYear()` -> `belongsTo(AcademicYear::class)`
  - `gradeLevel()` -> `belongsTo(GradeLevel::class)`
  - `major()` -> `belongsTo(Major::class)`

Model parent seharusnya memiliki:
- `AcademicYear::classes()` -> `hasMany(SchoolClass::class)`
- `GradeLevel::classes()` -> `hasMany(SchoolClass::class)`
- `Major::classes()` -> `hasMany(SchoolClass::class)`

Filament Resource:
- Perintah `php artisan make:filament-resource SchoolClass` sudah dijalankan.
- Form file `app/Filament/Resources/SchoolClasses/Schemas/SchoolClassForm.php`.
- Form telah diubah dari TextInput ID menjadi:
  - Select tahun ajaran dengan `relationship('academicYear', 'name')`
  - Select tingkat dengan `relationship('gradeLevel', 'name')`
  - Select jurusan dengan `relationship('major', 'name')`
  - Number input `group_number`
  - read-only `name` yang diisi otomatis.
- Logic nama kelas: `"{gradeLevel->name} {major->code} {groupNumber}"`, contoh `XI TJKT 1`.
- Form memakai `Filament\Forms\Components\Select`, `TextInput`, `Filament\Forms\Get`, `Filament\Forms\Set`, dan `Filament\Schemas\Schema`.
- Pernah error `The relationship [academicYear] does not exist on the model [App\Models\SchoolClass]`; penyebabnya relasi belum ada pada model. Pengguna memperbaiki model, lalu menyatakan sudah.
- Table file `app/Filament/Resources/SchoolClasses/Tables/SchoolClassesTable.php` telah diberikan kode untuk menampilkan:
  - `name`
  - `academicYear.name`
  - `gradeLevel.name`
  - `major.code`
  - `group_number`
  - `created_at`
  - EditAction dan DeleteAction.
- Pengguna belum memberi konfirmasi eksplisit bahwa tampilan table kelas berhasil diuji. Saat melanjutkan, tanyakan hasil uji secara singkat atau minta error bila ada.

## 5. Desain akademik yang sudah dibahas untuk modul selanjutnya

Jangan membuat semua tabel sekaligus. Lanjutkan bertahap setelah memeriksa hasil modul kelas.

### 5.1 Siswa dan riwayat kelas

Rencana:
- `students`: menyimpan identitas siswa. Tidak menyimpan `class_id` secara langsung.
- `class_enrollments`: mencatat siswa ditempatkan di kelas tertentu untuk tahun ajaran/masa tertentu.
- Riwayat penempatan harus dipertahankan saat siswa naik kelas atau pindah.
- Status enrollment yang pernah dibahas: active, completed, transferred, cancelled.
- Status siswa dapat mencatat aktif/lulus sesuai kebutuhan.
- `class_enrollments` berhubungan dengan `students` dan `school_classes` (pastikan nama tabel aktual `school_classes`).
- Rancang constraint agar satu siswa tidak memiliki dua penempatan aktif yang bertabrakan dalam tahun ajaran yang sama.

### 5.2 Guru dan pengguna

Entitas yang telah disepakati secara konseptual:
- `users`
- `profiles`
- `teachers` terpisah dari users
- Role/permission: `roles`, `permissions`, `user_roles`, `role_permissions`.
- Contoh role: superadmin, kepala sekolah, guru, operator.
- Satu guru dapat mengajar beberapa mata pelajaran dan kelas.

### 5.3 Penugasan dan jadwal

- `teaching_assignments`: menghubungkan guru, mata pelajaran, kelas, dan semester.
- `schedules`: menghubungkan ke teaching assignment, menyimpan hari, waktu mulai, waktu selesai, dan ruang.
- Tidak menggunakan tabel period/jam pelajaran karena pengguna memilih jadwal langsung dengan start/end time.
- `homeroom_assignments` dapat digunakan untuk wali kelas.

### 5.4 Kehadiran dan penilaian

Rencana tabel:
- `attendance_sessions`: pertemuan, tanggal, topik, catatan.
- `attendances`: siswa, status, catatan.
- Status kehadiran memiliki nilai numerik yang dapat dikonfigurasi (hadir, sakit, izin, alpa, dan lain-lain sesuai keputusan sekolah).
- `assignments`, `assignment_scores`.
- `assessment_components`, `assessment_component_status_rules`, `semester_grades`, `grade_scales`.
- Komponen penilaian berbobot total 100%.
- Pengguna memilih kehadiran menjadi salah satu komponen nilai; contoh bobot 20%, dengan nilai status kehadiran yang dapat dikonfigurasi.
- Nilai akhir dihitung otomatis berdasarkan komponen dan bobot.
- Skala predikat dapat dikonfigurasi.
- Nilai semester dapat dikunci/finalized; perubahan setelah finalisasi sebaiknya tercatat sebagai revisi.

### 5.5 Laporan dan fitur lanjutan

- Laporan kehadiran, nilai, dan rekap per kelas/semester/tahun ajaran.
- Kepala sekolah memiliki akses lihat laporan.
- Fitur CP/TP/RPP atau modul pembelajaran dapat dipertimbangkan di tahap lanjutan, bukan prioritas saat ini.
- `audit_logs` untuk mencatat perubahan penting.

## 6. Urutan roadmap yang direkomendasikan

1. Pastikan tabel kelas dan relasinya berfungsi.
2. Buat tabel `students`.
3. Buat tabel `class_enrollments` untuk riwayat kelas.
4. Buat profil dan data guru (`profiles`, `teachers`) serta hubungan ke users.
5. Buat mata pelajaran (`subjects`).
6. Buat `teaching_assignments`.
7. Buat `homeroom_assignments` jika diperlukan.
8. Buat `schedules`.
9. Buat modul pertemuan dan kehadiran.
10. Buat tugas dan komponen penilaian.
11. Buat kalkulasi nilai semester dan predikat.
12. Buat laporan dan hak akses yang lebih lengkap.
13. Pertimbangkan fitur pembelajaran/CP/TP/RPP setelah modul inti stabil.

Urutan dapat disesuaikan, tetapi jangan melompat ke nilai sebelum struktur siswa, kelas, guru, mata pelajaran, dan penugasan mengajar sudah jelas.

## 7. Catatan khusus untuk Filament 5.11.2

- Proyek memakai Filament **v5.11.2**, bukan Filament 3.
- Struktur Resource yang terlihat menggunakan direktori terpisah:
  - `Resources/<Resource>/Schemas/`
  - `Resources/<Resource>/Tables/`
  - `Resources/<Resource>/Pages/`
- Schema menggunakan `Filament\Schemas\Schema`.
- Form components yang dihasilkan menggunakan namespace `Filament\Forms\Components\...`.
- Tabel menggunakan `Filament\Tables\Table`.
- Table row actions menggunakan namespace `Filament\Actions\...` dan method `recordActions([...])` pada kode yang telah dipakai.
- Jangan memberikan kode Filament 3 tanpa verifikasi.
- Jika perlu, minta pengguna mengirim isi file yang dihasilkan artisan sebelum menulis kode final. Struktur aktual proyek lebih penting daripada menebak.
- Perhatikan perbedaan `class` sebagai kata khusus PHP; gunakan Model `SchoolClass`.
- Setelah perubahan kode, umumnya jalankan `php artisan optimize:clear` untuk membersihkan cache.
- Jangan meminta pengguna menghapus database atau menjalankan `migrate:fresh` pada database yang sudah berisi data, kecuali ada alasan jelas dan persetujuan eksplisit.

## 8. Preferensi cara bekerja dengan pengguna

- Gunakan bahasa Indonesia.
- Pandu secara bertahap, satu pekerjaan kecil per giliran.
- Berikan perintah terminal yang siap disalin.
- Jangan meminta kata sandi.
- Hindari memberikan terlalu banyak file sekaligus.
- Untuk perubahan file yang sudah ada, minta isi file terlebih dahulu bila struktur atau API belum pasti.
- Setelah satu perubahan, minta pengguna menjalankan/test dan melaporkan hasil.
- Jelaskan alasan desain database secara ringkas saat diperlukan.
- Jangan mengulang seluruh roadmap di setiap balasan.

## 9. Instruksi untuk asisten/model berikutnya

Mulai dengan mengakui bahwa dokumen ini adalah konteks proyek. Jangan mengulang setup Laravel, Filament, atau MariaDB kecuali ada masalah.

Titik lanjut yang paling tepat:
1. Tanyakan apakah halaman daftar Kelas (`School Classes`) sudah berhasil menampilkan nama kelas, tahun ajaran, tingkat, jurusan, dan kelompok.
2. Jika berhasil, mulai rancang modul `students` dan `class_enrollments` secara hati-hati. Ingat keputusan penting: **tabel students tidak memiliki class_id langsung**; penempatan kelas dicatat dalam tabel riwayat `class_enrollments`.
3. Jika ada error pada tabel kelas, minta pesan error dan file terkait sebelum mengubah kode.

---

## 10. Pembaruan workspace 2026-11-22 (setelah dokumen diterima)

Bagian ini ditambahkan oleh asisten; bagian 1-9 tetap seperti dokumen asli.

- Laravel Boost 2.10.4 dipasang (`composer require laravel/boost --dev` + `php artisan boost:install`). `AGENTS.md`/`CLAUDE.md` sekarang berisi guideline Boost, dengan `.mcp.json`, `opencode.json`, `boost.json`, `.agents/`, dan `.claude/`.
- Modul yang sebelumnya belum ada di workspace sudah dibuat: `SchoolClass` (`school_classes`), `Student` (`students`), dan `ClassEnrollment` (`class_enrollments`). Masing-masing memiliki model, migrasi, factory, seeder, dan resource Filament lengkap (Schemas/Tables/Pages).
- Data contoh: 5 kelas, 6 siswa, 6 penempatan aktif untuk tahun ajaran 2026/2027.
- Aturan database: satu penempatan aktif per siswa per tahun ajaran dijamin oleh kolom turunan `active_student` dan indeks unik `enrollments_one_active_per_year`. Indeks unik `(student_id, school_class_id)` sengaja dihapus karena mencegah riwayat non-aktif pada kelas yang sama.
- Tes: `tests/Feature/ClassEnrollmentTest.php` (2 tes lulus). `vendor/bin/pint --dirty` bersih.
- Verifikasi: semua migrasi berstatus Ran; rute `/admin/school-classes`, `/admin/students`, `/admin/class-enrollments` terdaftar.
- Halaman UI belum diuji oleh pengguna.

---

## 11. Pembaruan sidebar Filament 2026-11-22

- Sidebar panel admin dikelompokkan menjadi 4 grup: Akademik, Kelas & Jurusan, Kesiswaan, Pengaturan. Dashboard tetap di luar grup dan muncul paling atas.
- Semua menu resource diberi ikon berbeda, label bahasa Indonesia, dan `navigationSort` 1-9. Ikon Dashboard diubah ke `o-home-modern` lewat `PanelsIconAlias`.
- Grup tidak diberi ikon supaya ikon tiap menu tetap tampil pada sidebar yang diperluas.
- `$navigationGroup` pada resource Filament 5 bertipe `string|UnitEnum|null` (bukan `?string`).
- Verifikasi: urutan grup, label, ikon, dan sort terbaca benar; `ClassEnrollmentTest` tetap 2/2 lulus; Pint bersih.
