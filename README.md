# SiTIO — Sistem Terpusat International Office

SiTIO (Sistem Terpusat International Office) adalah proyek yang diinisiasi untuk
pemenuhan proyek Ujian Akhir Semester. Proyek ini menyediakan satu platform
terpusat untuk mengelola proses administrasi internasional sebuah perguruan
tinggi: pengajuan PDLN (Perjalanan Dinas Luar Negeri), pembuatan paspor dinas
beserta exit permit, outbound (delegasi ke luar negeri), dan inbound (kunjungan
dari luar negeri).

Repositori ini berisi kerangka aplikasi (boilerplate) PHP tanpa dependensi yang
menjadi fondasi SiTIO: router, lapisan controller/service/repository, akses
database PDO, validasi, penyimpanan berkas, dan rendering view. Fokus tahap ini
adalah menyediakan struktur yang konsisten sebelum fitur domain SiTIO dibangun
di atasnya.

---

## Daftar Isi

1. [Ruang Lingkup](#ruang-lingkup)
2. [Fitur Kerangka](#fitur-kerangka)
3. [Kebutuhan Sistem](#kebutuhan-sistem)
4. [Memulai Cepat](#memulai-cepat)
5. [Konfigurasi Database](#konfigurasi-database)
6. [Struktur Proyek](#struktur-proyek)
7. [Arsitektur Aplikasi](#arsitektur-aplikasi)
8. [Menambah Fitur Baru](#menambah-fitur-baru)
9. [Routing](#routing)
10. [Konvensi Response](#konvensi-response)
11. [Kontribusi](#kontribusi)
12. [Lisensi](#lisensi)

---

## Ruang Lingkup

SiTIO dirancang untuk menangani empat alur utama:

| Alur | Deskripsi |
|---|---|
| **PDLN** | Pengajuan dan pemrosesan Perjalanan Dinas Luar Negeri, termasuk berkas pendukung dan status persetujuan. |
| **Paspor Dinas & Exit Permit** | Pengelolaan permohonan paspor dinas serta penerbitan exit permit. |
| **Outbound** | Pendataan delegasi (mahasiswa/dosen/staf) yang dikirim ke luar negeri. |
| **Inbound** | Pendataan kunjungan delegasi asing ke institusi. |

> Catatan: fitur domain di atas adalah target pengembangan. Kode saat ini masih
> berupa kerangka umum beserta contoh (`SampleController`, route `/sample`, dan
> view `tes.view.php`) sebagai acuan penulisan fitur.

---

## Fitur Kerangka

- **Router** — `GET`/`POST`/`PUT`/`PATCH`/`DELETE`, parameter `:nama`, wildcard `*`, dukungan subdirektori, override `_method` pada form, banyak handler per route, dan 404 otomatis.
- **View** — `Response::render("nama")` memuat `app/views/nama.view.php` (titik sebagai pemisah subdirektori).
- **Database** — singleton PDO *lazy* dengan `DB::query()` (prepared statement) dan transaksi.
- **Repository** — basis statis `Repository` (`all`, `find`, `findOne`, `findOneById`, `insert`, `update`, `delete`) yang dikonfigurasi lewat `$table`, `$idColumn`, dan `$fillable`.
- **Lapisan Service & Controller** — kerangka yang mendorong alur `Controller → Service → Repository`.
- **Request / Response** — akses query, body, JSON, header, dan upload; `Response::status()`, `json()`, `redirect()`, `render()`.
- **Validator** — aturan `required`, tipe, `email`, `enum`, `pattern`, serta validasi berkas (`image`, `pdf`, `mime:`, ukuran/jumlah).
- **Storage** — helper berkas statis: `exists`, `get`, `put`, `save` (upload), `unlink`.
- **Tanpa dependensi** — PHP >= 8.1 murni, `declare(strict_types=1)`, namespace global, tanpa Composer dan tanpa build step.

---

## Kebutuhan Sistem

| Kebutuhan | Versi / Catatan |
|---|---|
| PHP | **>= 8.1** |
| Ekstensi | `pdo_mysql` (atau driver PDO lain yang sesuai), `filter` |
| Web server | Apache dengan `mod_rewrite` (`.htaccess` sudah disediakan), atau PHP built-in server untuk pengembangan |
| Database | MySQL/MariaDB (skema dikelola manual, tanpa migrasi) |

---

## Memulai Cepat

```bash
git clone https://github.com/navetacandra/php_template.git SiTIO
cd SiTIO
```

1. Buat database dan tabel yang dibutuhkan (lewat klien SQL apa pun).
2. Atur koneksi database melalui environment variable (lihat bagian berikut).
3. Arahkan document root web server ke folder `public/` (atau jalankan server bawaan PHP).
4. Tambahkan route di `index.php` dan kelas-kelas Anda di dalam `app/`.

### Menjalankan dengan server bawaan PHP

Jalankan dari root proyek, dengan `public/` sebagai document root:

```bash
php -S localhost:5000 -t public
```

Buka http://localhost:5000. Untuk mengatur database sekaligus:

```bash
# Linux / macOS
DB_NAME=sitio DB_USER=root DB_PASS=secret php -S localhost:5000 -t public

# Windows (PowerShell)
$env:DB_NAME="sitio"; $env:DB_USER="root"; $env:DB_PASS="secret"; php -S localhost:5000 -t public
```

`public/index.php` hanya meneruskan request ke `index.php` di root proyek,
sementara `public/` menjadi satu-satunya folder yang dapat diakses langsung dari
web (aset publik dan berkas upload disimpan di sini).

---

## Konfigurasi Database

`DB` (`app/core/Database.php`) membaca konfigurasi dari environment variable
melalui `getenv()`, dengan nilai default sebagai berikut:

| Variabel | Default | Keterangan |
|---|---|---|
| `DB_DRIVER` | `mysql` | Nama driver PDO (prefiks DSN) |
| `DB_HOST` | `localhost` | Host database |
| `DB_PORT` | `3306` | Port database |
| `DB_NAME` | `PBL_TI_2025_B_NavetaCandra` | Nama database |
| `DB_USER` | `root` | Username |
| `DB_PASS` | `root` | Password |

Contoh konfigurasi Apache:

```apache
SetEnv DB_HOST localhost
SetEnv DB_PORT 3306
SetEnv DB_NAME sitio
SetEnv DB_USER root
SetEnv DB_PASS secret
```

Perilaku koneksi:

- `PDO::ERRMODE_EXCEPTION`; kegagalan koneksi akan memanggil `die($message)`.
- `PDO::FETCH_ASSOC` — hasil query berupa array asosiatif.
- Tidak ada tool migrasi/seed — skema dibuat manual.

---

## Struktur Proyek

```
.
├── index.php                  # Front controller: require core, definisi route, run router
├── public/
│   ├── index.php              # Entry point web (meneruskan ke ../index.php)
│   └── storage/               # Berkas publik/upload (dapat diakses web)
├── app/
│   ├── .htaccess              # Deny from all (kode internal tidak dapat diakses web)
│   ├── controllers/           # Controller (lapisan HTTP)
│   ├── services/              # Service (logika bisnis)
│   ├── repositories/          # Repository (akses data)
│   ├── views/                 # View (*.view.php)
│   └── core/                  # Inti kerangka
│       ├── Database.php       # DB — wrapper PDO, transaksi, DB::query
│       ├── Repository.php     # Repository — basis CRUD statis
│       ├── Service.php        # Service — basis service (menyimpan Repository)
│       ├── Controller.php     # Controller — basis controller (menyimpan Service)
│       ├── Request.php        # Request — akses input HTTP
│       ├── Response.php       # Response — status/json/redirect/render
│       ├── Router.php         # Router — route, parameter, wildcard
│       ├── Storage.php        # Storage — helper berkas
│       └── Validator.php      # Validator — aturan validasi
├── storage/                   # Direktori penyimpanan aplikasi
├── DOCS.md                    # Dokumentasi teknis penggunaan kerangka
├── LICENSE                    # Lisensi MIT
└── README.md                  # Ikhtisar proyek (berkas ini)
```

---

## Arsitektur Aplikasi

Alur permintaan mengikuti lapisan berikut:

```
Route → Controller → Service → Repository → DB (PDO)
```

- **Route** dideklarasikan di `index.php` dan dapat berupa closure atau pasangan `[ClassName, "method"]`.
- **Controller** menangani validasi input dan menyusun response HTTP.
- **Service** menyimpan logika bisnis dan memanggil repository.
- **Repository** menjadi satu-satunya lapisan yang mengetahui nama tabel dan kolom.
- **DB** mengeksekusi prepared statement.

Tidak ada autoloader: semua kelas berada di namespace global. Berkas core wajib
di-`require_once` di `index.php` dengan urutan dependensi, sedangkan repository
dan service yang Anda buat juga harus di-`require_once` di sana. Controller yang
dirujuk lewat route dimuat otomatis oleh router dari `app/controllers/`.

---

## Menambah Fitur Baru

Langkah standar untuk satu fitur (misalnya `User`):

1. **Repository** — `app/repositories/UserRepository.php`, `extends Repository`, isi `$table`, `$idColumn`, `$fillable`; daftarkan di `index.php`.
2. **Service** — `app/services/UserService.php`, `extends Service`, tetapkan `$this->repository`; daftarkan di `index.php`.
3. **Controller** — `app/controllers/UserController.php`, `extends Controller`, tetapkan `$this->service`; dimuat otomatis oleh router.
4. **Route** — tambahkan di `index.php` sebelum `$router->run()`.

Setiap handler (termasuk method controller) menerima signature
`(Request $request, array $params)`.

---

## Routing

Contoh dasar:

```php
$router->get("/", fn () => Response::json([
    "status" => "success",
    "data"   => ["app" => "SiTIO"],
]));

$router->get("/users/:id", ["UserController", "show"]);
$router->post("/users",    ["UserController", "store"]);
$router->get("/files/*",   fn (Request $r, array $p) => Response::json(["status" => "success"]));
```

Aturan penting:

| Aturan | Keterangan |
|---|---|
| Verb | `GET`, `POST`, `PUT`, `PATCH`, `DELETE` |
| Method override | `POST` dengan `_method=PUT\|PATCH\|DELETE` dikirim sebagai verb tersebut |
| Urutan pencocokan | Sesuai urutan registrasi — **yang pertama cocok dipakai**; daftarkan path statis sebelum path berparameter |
| Placeholder | `:nama` mencocokkan satu segmen; `*` mencocokkan apa pun |
| Subdirektori | Ditangani otomatis; gunakan konstanta `BASE_URL` untuk tautan/redirect |
| Tidak cocok | HTTP `404` dikembalikan otomatis |

---

## Konvensi Response

Gunakan envelope JSON yang konsisten:

```php
Response::json(["status" => "success", "data" => $payload], 200);
Response::json(["status" => "success", "data" => ["id" => $id]], 201);
Response::json(["status" => "error", "errors" => $errors], 422);
Response::json(["status" => "error", "message" => "Terjadi kesalahan"], 500);
```

Kode status yang umum dipakai: `200` (OK), `201` (Created), `302` (Redirect),
`400` (input tidak valid), `401` (belum terautentikasi), `403` (terlarang),
`404` (tidak ditemukan), `422` (validasi gagal), `500` (kesalahan server).

Perlu diingat: `json()` dan `redirect()` langsung mengirim output dan `exit`,
sedangkan `render()` mengembalikan kendali ke handler berikutnya.

---

## Kontribusi

Proyek ini dikembangkan untuk keperluan akademik. Untuk perubahan kode:

1. Buat branch fitur dari `master`.
2. Ikuti konvensi yang sudah ada (penamaan berkas = nama kelas, `strict_types`, tanpa dependensi baru).
3. Sertakan deskripsi perubahan yang jelas pada pull request.

---

## Lisensi

Dirilis di bawah [MIT License](LICENSE).
