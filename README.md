
# 🏦 BANK IMAN SYARIAH
## Aplikasi Internet Banking Berbasis Web

Bank Iman Syariah merupakan aplikasi internet banking berbasis web yang dikembangkan dengan konsep layanan perbankan syariah. Aplikasi ini dibuat untuk mempermudah pengguna dalam mengakses layanan rekening, transaksi, pembiayaan, dan pembayaran zakat secara digital.

Aplikasi memiliki dua jenis pengguna, yaitu **Nasabah** dan **Admin**. Nasabah dapat mengelola rekening dan melakukan transaksi, sedangkan Admin bertugas mengelola data serta memproses pengajuan nasabah.

Project ini dikembangkan menggunakan PHP Native, MySQL, HTML, CSS, dan JavaScript serta dilengkapi REST API untuk pertukaran data.

---

## 📌 Fitur Aplikasi

### 1. Fitur Nasabah

- Registrasi akun.
- Login pengguna.
- Verifikasi OTP 6 digit.
- Pengiriman ulang OTP.
- Dashboard informasi saldo dan transaksi terbaru.
- Melihat data rekening.
- Melihat detail rekening.
- Mengajukan pembukaan rekening.
- Memilih jenis akad rekening Wadiah atau Mudharabah.
- Melihat status pengajuan pembukaan rekening.
- Mengajukan penutupan rekening.
- Melihat status pengajuan penutupan rekening.
- Transfer antar rekening.
- Melihat riwayat transaksi.
- Mengajukan setor tunai.
- Melakukan penarikan tunai.
- Mengajukan pembiayaan.
- Melihat status pengajuan pembiayaan.
- Melihat informasi pembiayaan.
- Membayar angsuran pembiayaan.
- Melihat informasi dan riwayat pembayaran zakat.
- Mengelola profil.
- Mengubah foto profil.
- Mengubah password.
- Mendapatkan notifikasi aktivitas dan status proses.

### 2. Fitur Admin

- Dashboard administrator.
- Mengelola data nasabah.
- Mengelola data rekening.
- Melihat pengajuan pembukaan rekening.
- Menyetujui pengajuan pembukaan rekening.
- Menolak pengajuan pembukaan rekening.
- Melihat pengajuan penutupan rekening.
- Memproses pengajuan penutupan rekening.
- Mengelola setor tunai.
- Melihat data transfer nasabah.
- Melihat data transaksi.
- Melihat laporan transaksi.
- Melihat pengajuan pembiayaan.
- Menyetujui pengajuan pembiayaan.
- Menolak pengajuan pembiayaan.

### 3. Sistem Autentikasi

- Login menggunakan username dan password.
- Verifikasi login menggunakan OTP.
- OTP terdiri dari 6 digit angka.
- OTP memiliki masa berlaku 5 menit.
- Pengiriman ulang OTP menggunakan cooldown.
- Pembatasan percobaan verifikasi OTP.
- Session login pengguna.
- Pembatasan akses berdasarkan role.
- Role pengguna terdiri dari `nasabah` dan `admin`.

### 4. REST API

REST API digunakan untuk pertukaran data antara aplikasi dengan database menggunakan format JSON.

Endpoint yang tersedia:

| No | Endpoint | Method | Fungsi |
|---|---|---|---|
| 1 | `test.php` | GET | Menguji koneksi API dan database |
| 2 | `login.php` | POST | Login API dan mendapatkan token |
| 3 | `dashboard.php` | GET | Mengambil data dashboard |
| 4 | `accounts.php` | GET | Mengambil data rekening |
| 5 | `transactions.php` | GET | Mengambil riwayat transaksi |
| 6 | `financing.php` | GET | Mengambil data pembiayaan |
| 7 | `zakat.php` | GET | Mengambil data zakat |
| 8 | `profile.php` | GET | Mengambil data profil |

API menggunakan format JSON dan autentikasi Bearer Token untuk endpoint yang membutuhkan autentikasi.

**Catatan:** Fitur website seperti transfer, setor tunai, penarikan tunai, pengajuan rekening, OTP, pembayaran angsuran, dan proses administrasi admin masih menggunakan proses PHP website dan bukan endpoint REST API pada versi ini.

Dokumentasi API tersedia pada:

`docs/API_DOCUMENTATION.md`

---

## 🛠️ Teknologi yang Digunakan

| Teknologi | Kegunaan |
|---|---|
| PHP Native | Backend dan proses aplikasi |
| MySQL | Pengelolaan database |
| HTML | Struktur halaman |
| CSS | Desain dan tampilan aplikasi |
| JavaScript | Interaksi halaman |
| REST API | Pertukaran data |
| JSON | Format data API |
| XAMPP | Server lokal |
| Postman | Pengujian API |
| Visual Studio Code | Editor kode |
| Git | Version control |
| GitHub | Penyimpanan repository dan kolaborasi |

---

## 📂 Struktur Folder Proyek

```text
bank_iman_syariah/
│
├── api/
│   ├── accounts.php
│   ├── auth.php
│   ├── config.php
│   ├── dashboard.php
│   ├── financing.php
│   ├── login.php
│   ├── profile.php
│   ├── test.php
│   ├── transactions.php
│   └── zakat.php
│
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── style.css
│   │
│   ├── images/
│   │   └── moon.svg
│   │
│   └── uploads/
│       └── profiles/
│
├── auth/
│   ├── login.php
│   ├── otp.php
│   ├── resend_otp.php
│   └── verify_otp.php
│
├── config/
│   ├── config.php
│   └── database.php
│
├── database/
│   └── bank_iman_syariah.sql
│
├── docs/
│   └── API_DOCUMENTATION.md
│
├── includes/
│   ├── admin_sidebar.php
│   └── sidebar.php
│
├── pages/
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── laporan.php
│   │   ├── nasabah.php
│   │   ├── pengajuan_pembiayaan.php
│   │   ├── pengajuan_rekening.php
│   │   ├── pengajuan_tutup_rekening.php
│   │   ├── proses_pengajuan_pembiayaan.php
│   │   ├── proses_pengajuan_rekening.php
│   │   ├── proses_tutup_rekening_admin.php
│   │   ├── rekening.php
│   │   ├── setor_tunai.php
│   │   ├── transaksi.php
│   │   └── transfer.php
│   │
│   ├── accounts.php
│   ├── dashboard.php
│   ├── detail_rekening.php
│   ├── financing.php
│   ├── profile.php
│   ├── proses_bayar_angsuran.php
│   ├── proses_pengajuan_pembiayaan.php
│   ├── proses_rekening.php
│   ├── proses_setor.php
│   ├── proses_tarik.php
│   ├── proses_transfer.php
│   ├── proses_tutup_rekening.php
│   ├── setor_tunai.php
│   ├── tarik_tunai.php
│   ├── transactions.php
│   ├── transfer.php
│   ├── ubah_password.php
│   └── zakat.php
│
├── buat_akun.php
├── daftar.php
├── index.php
└── README.md
```

---

## ⚙️ Persyaratan Sistem

Untuk menjalankan aplikasi, perangkat memerlukan:

1. XAMPP.
2. Apache.
3. MySQL atau MariaDB.
4. PHP.
5. Web Browser.
6. Visual Studio Code (opsional).
7. Postman (opsional untuk pengujian API).

---

## 🚀 Cara Instalasi dan Menjalankan Aplikasi

### 1. Siapkan Folder Project

Letakkan folder `bank_iman_syariah` ke dalam direktori:

```text
C:\xampp\htdocs\
```

### 2. Jalankan XAMPP

Buka XAMPP Control Panel, kemudian aktifkan:

- Apache
- MySQL

### 3. Import Database

1. Buka browser.
2. Akses `http://localhost/phpmyadmin`.
3. Buat database bernama `bank_iman_syariah` apabila belum tersedia.
4. Pilih database tersebut.
5. Klik menu Import.
6. Pilih file:

   `database/bank_iman_syariah.sql`

7. Klik Import atau Go untuk menjalankan proses import.

### 4. Konfigurasi Database

Buka file:

```text
config/database.php
```

Sesuaikan konfigurasi database dengan pengaturan MySQL di perangkat.

Contoh konfigurasi XAMPP default:

```php
<?php

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "bank_iman_syariah";

$conn = mysqli_connect(
    $host,
    $db_user,
    $db_pass,
    $db_name
);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

?>
```

### 5. Jalankan Aplikasi

Buka browser dan akses:

```text
http://localhost/bank_iman_syariah/
```

### 6. Pengujian REST API

Base URL API:

```text
http://localhost/bank_iman_syariah/api/
```

Tes koneksi API:

```text
http://localhost/bank_iman_syariah/api/test.php
```

Pengujian endpoint lainnya dapat menggunakan Postman.

---

## 🗄️ Database

Nama database:

```text
bank_iman_syariah
```

Database menggunakan tabel berikut:

| No | Nama Tabel | Fungsi |
|---|---|---|
| 1 | `users` | Menyimpan data pengguna |
| 2 | `accounts` | Menyimpan data rekening |
| 3 | `account_applications` | Menyimpan pengajuan pembukaan rekening |
| 4 | `account_closure_applications` | Menyimpan pengajuan penutupan rekening |
| 5 | `transactions` | Menyimpan data transaksi |
| 6 | `financings` | Menyimpan data pembiayaan |
| 7 | `financing_applications` | Menyimpan pengajuan pembiayaan |
| 8 | `financing_payments` | Menyimpan data pembayaran angsuran |
| 9 | `zakat_payments` | Menyimpan data pembayaran zakat |
| 10 | `api_tokens` | Menyimpan token autentikasi REST API |

File database:

```text
database/bank_iman_syariah.sql
```

---

## 🔐 Keamanan Aplikasi

Beberapa mekanisme keamanan yang diterapkan:

- Password diverifikasi menggunakan `password_verify()`.
- OTP terdiri dari 6 digit angka.
- OTP disimpan sementara dalam session menggunakan hash.
- OTP memiliki batas waktu 5 menit.
- Terdapat batas percobaan OTP.
- Terdapat cooldown pengiriman ulang OTP.
- Session diregenerasi setelah proses login berhasil.
- API menggunakan Bearer Token.
- Token API dibuat menggunakan `random_bytes()`.
- Token API disimpan dalam database menggunakan hash SHA-256.
- Token API memiliki masa berlaku 24 jam.
- Endpoint API yang membutuhkan autentikasi memeriksa token.
- Data API dibatasi berdasarkan pengguna yang terautentikasi.
- Beberapa query database menggunakan prepared statements.
- Halaman admin dibatasi berdasarkan role.

**Catatan keamanan:** OTP pada versi simulasi ditampilkan melalui notifikasi aplikasi dan belum terhubung dengan layanan SMS, WhatsApp, atau email.

Jangan memasukkan data nasabah asli, password, token, atau informasi rahasia ke repository publik.

---

## 📖 Dokumentasi

Dokumentasi yang tersedia:

| File | Keterangan |
|---|---|
| `README.md` | Informasi proyek, fitur, instalasi, dan penggunaan |
| `docs/API_DOCUMENTATION.md` | Dokumentasi REST API |
| `database/bank_iman_syariah.sql` | Struktur dan data database |

---

## 👥 Tim Pengembang

**Nama Proyek:** Bank Iman Syariah  
**Jenis Proyek:** Aplikasi Internet Banking Berbasis Web  
**Mata Kuliah:** Basis Data  
**Dosen Pengampu:** Dr. Ir. Iman Paryudi, M.Sc.

### Anggota Kelompok

| No. | Nama Anggota | NIM |
|---|---|---|
| 1 | Muhammad Ryza Mahameru | 4525210048 |
| 2 | Azzahra Navisha | 4525210118 |
| 3 | Muhammad Rian Ramadhan | 4525210047 |
| 4 | Muhammad Rava Alvriansyah | 4525210040 |
| 5 | Muhammad Faisal Athallah | 4525210120 |
| 6 | Delia Veris Maureta | 45252100119 |
| 7 | Naufalino Ahmad Saputra | 4525210139 |

Kontribusi setiap anggota dapat dilihat melalui commit dan Pull Request pada repository GitHub.

---

## 📌 Catatan Pengembangan

Bank Iman Syariah dikembangkan sebagai proyek pembelajaran untuk menerapkan:

- Pemrograman web menggunakan PHP.
- Pengelolaan database MySQL.
- Autentikasi dan session.
- Penggunaan OTP.
- Pengelolaan transaksi.
- REST API.
- Penggunaan Git dan GitHub.
- Kolaborasi pengembangan aplikasi.

Aplikasi ini merupakan simulasi pembelajaran dan bukan sistem perbankan yang digunakan untuk transaksi keuangan sungguhan.

---

**BANK IMAN SYARIAH**

*Digital Banking with Sharia Principles.*
