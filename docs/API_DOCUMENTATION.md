
# 📘 REST API DOCUMENTATION
# BANK IMAN SYARIAH

Dokumentasi ini menjelaskan REST API yang tersedia pada aplikasi Bank Iman Syariah. API digunakan untuk pertukaran data antara aplikasi dengan database menggunakan format JSON.

REST API menyediakan endpoint untuk pengujian koneksi, autentikasi API, serta pengambilan data dashboard, rekening, transaksi, pembiayaan, zakat, dan profil pengguna.

> **Batasan:** API pada versi ini hanya menyediakan tes koneksi, login/token, serta endpoint GET untuk dashboard, rekening, transaksi, pembiayaan, zakat, dan profil. Transfer, OTP, setor/tarik tunai, pengajuan rekening, pembayaran angsuran, dan proses admin masih dijalankan melalui halaman/proses PHP website, bukan melalui endpoint API di bawah.

---

## 1. Informasi API

| Item | Keterangan |
|---|---|
| Nama API | Bank Iman Syariah REST API |
| Backend | PHP Native |
| Database | MySQL |
| Format Data | JSON |
| Autentikasi | Bearer Token |
| Base URL | `http://localhost/bank_iman_syariah/api/` |

---

## 2. Format Response

Response API menggunakan struktur JSON yang terdiri dari:

- `status`: kode HTTP.
- `message`: pesan hasil permintaan.
- `data`: data yang dikembalikan oleh API.

### Contoh Response Berhasil

```json
{
    "status": 200,
    "message": "Data berhasil diambil",
    "data": {}
}
```

### Contoh Response Gagal

```json
{
    "status": 401,
    "message": "Token tidak ditemukan",
    "data": null
}
```

---

## 3. Daftar Endpoint

| No | Endpoint | Method | Autentikasi | Fungsi |
|---|---|---|---|---|
| 1 | `test.php` | GET | Tidak | Menguji koneksi API dan database |
| 2 | `login.php` | POST | Tidak | Login dan mendapatkan token |
| 3 | `dashboard.php` | GET | Ya | Mengambil data dashboard |
| 4 | `accounts.php` | GET | Ya | Mengambil data rekening |
| 5 | `transactions.php` | GET | Ya | Mengambil riwayat transaksi |
| 6 | `financing.php` | GET | Ya | Mengambil data pembiayaan |
| 7 | `zakat.php` | GET | Ya | Mengambil data zakat |
| 8 | `profile.php` | GET | Ya | Mengambil data profil |

---

# 4. Detail Endpoint

## 4.1 Test API

Endpoint ini digunakan untuk menguji koneksi API dan database.

### Request

Method:

```http
GET
```

Endpoint:

```text
/api/test.php
```

Full URL:

```text
http://localhost/bank_iman_syariah/api/test.php
```

Autentikasi: Tidak diperlukan.

### Response Berhasil

```json
{
    "status": 200,
    "message": "API Bank Iman Syariah berhasil terhubung!",
    "data": {
        "database": "bank_iman_syariah",
        "status": "connected"
    }
}
```

---

## 4.2 Login API

Endpoint ini digunakan untuk melakukan login menggunakan username dan password serta menghasilkan token autentikasi.

### Request

Method:

```http
POST
```

Endpoint:

```text
/api/login.php
```

Full URL:

```text
http://localhost/bank_iman_syariah/api/login.php
```

Autentikasi: Tidak diperlukan.

Content-Type:

```http
Content-Type: application/json
```

### Request Body

```json
{
    "username": "username_pengguna",
    "password": "password_pengguna"
}
```

### Response Berhasil

```json
{
    "status": 200,
    "message": "Login berhasil",
    "data": {
        "id": 1,
        "nama": "Nama Pengguna",
        "username": "username_pengguna",
        "token": "TOKEN_DARI_RESPONSE",
        "berlaku_sampai": "2026-11-20 15:00:00"
    }
}
```

### Proses Login API

1. API menerima username dan password.
2. Username dan password wajib diisi.
3. API mencari pengguna berdasarkan username.
4. Password diverifikasi menggunakan `password_verify()`.
5. Jika login berhasil, API membuat token acak.
6. Token diubah menjadi hash SHA-256.
7. Hash token disimpan ke database.
8. Token asli dikirim melalui response.
9. Token berlaku selama 24 jam.

### Response Error

| HTTP | Pesan | Penyebab |
|---|---|---|
| 400 | Username dan password wajib diisi | Data login tidak lengkap |
| 401 | Username atau password salah | Username/password tidak valid |
| 405 | Method tidak diizinkan | Request bukan POST |
| 500 | Query database gagal | Kesalahan database |
| 500 | Gagal membuat token | Proses pembuatan token gagal |
| 500 | Gagal menyimpan token | Proses penyimpanan token gagal |

**Catatan:** Login REST API ini berbeda dengan login website yang menggunakan proses verifikasi OTP.

---

## 4.3 Dashboard

Endpoint ini digunakan untuk mengambil data dashboard pengguna yang sedang terautentikasi.

### Request

Method:

```http
GET
```

Endpoint:

```text
/api/dashboard.php
```

Header:

```http
Authorization: Bearer TOKEN
```

### Data yang Dikembalikan

- Nama pengguna.
- Total saldo rekening aktif.
- Tiga transaksi terbaru.

### Response Contoh

```json
{
    "status": 200,
    "message": "Data dashboard berhasil diambil",
    "data": {
        "nama": "Nama Pengguna",
        "total_saldo": "15000000.00",
        "transaksi_terbaru": [
            {
                "kode_transaksi": "TRX001",
                "keterangan": "Transfer Masuk",
                "jenis": "masuk",
                "nominal": "500000.00",
                "tanggal": "2026-11-18 10:00:00"
            }
        ]
    }
}
```

---

## 4.4 Data Rekening

Endpoint ini digunakan untuk mengambil daftar rekening milik pengguna yang sedang terautentikasi.

### Request

Method:

```http
GET
```

Endpoint:

```text
/api/accounts.php
```

Header:

```http
Authorization: Bearer TOKEN
```

### Data yang Dikembalikan

- Jumlah rekening.
- ID rekening.
- Nomor rekening.
- Jenis akad.
- Saldo.
- Status rekening.

### Response Contoh

```json
{
    "status": 200,
    "message": "Data rekening berhasil diambil",
    "data": {
        "jumlah_rekening": 1,
        "data_rekening": [
            {
                "id": 1,
                "nomor_rekening": "1234567890",
                "jenis_akad": "Wadiah",
                "saldo": "5000000.00",
                "status": "aktif"
            }
        ]
    }
}
```

---

## 4.5 Riwayat Transaksi

Endpoint ini digunakan untuk mengambil riwayat transaksi rekening milik pengguna.

### Request

Method:

```http
GET
```

Endpoint:

```text
/api/transactions.php
```

Header:

```http
Authorization: Bearer TOKEN
```

### Data yang Dikembalikan

- Jumlah transaksi.
- ID transaksi.
- Kode transaksi.
- Keterangan transaksi.
- Jenis transaksi.
- Nominal.
- Tanggal transaksi.
- Nomor rekening.

### Response Contoh

```json
{
    "status": 200,
    "message": "Data transaksi berhasil diambil",
    "data": {
        "jumlah_transaksi": 1,
        "riwayat": [
            {
                "id": 1,
                "kode_transaksi": "TRX001",
                "keterangan": "Setoran Tunai",
                "jenis": "masuk",
                "nominal": "500000.00",
                "tanggal": "2026-11-18 10:00:00",
                "nomor_rekening": "1234567890"
            }
        ]
    }
}
```

---

## 4.6 Data Pembiayaan

Endpoint ini digunakan untuk mengambil data pembiayaan milik pengguna yang sedang login melalui API.

### Request

Method:

```http
GET
```

Endpoint:

```text
/api/financing.php
```

Header:

```http
Authorization: Bearer TOKEN
```

### Data yang Dikembalikan

- Jumlah pembiayaan.
- ID pembiayaan.
- Jenis akad.
- Plafon.
- Margin.
- Tenor.
- Angsuran.
- Status pembiayaan.

### Response Contoh

```json
{
    "status": 200,
    "message": "Data pembiayaan berhasil diambil",
    "data": {
        "jumlah_pembiayaan": 1,
        "data_pembiayaan": [
            {
                "id": 1,
                "jenis_akad": "Murabahah",
                "plafon": "10000000.00",
                "margin": "500000.00",
                "tenor": 12,
                "angsuran": "875000.00",
                "status": "Aktif"
            }
        ]
    }
}
```

---

## 4.7 Riwayat Zakat

Endpoint ini digunakan untuk mengambil informasi pembayaran zakat pengguna.

### Request

Method:

```http
GET
```

Endpoint:

```text
/api/zakat.php
```

Header:

```http
Authorization: Bearer TOKEN
```

### Data yang Dikembalikan

- Jumlah pembayaran zakat.
- Total nominal zakat.
- Riwayat pembayaran.
- ID pembayaran.
- Jenis zakat.
- Nominal.
- Status pembayaran.
- Tanggal pembayaran.

### Response Contoh

```json
{
    "status": 200,
    "message": "Data zakat berhasil diambil",
    "data": {
        "jumlah_pembayaran": 1,
        "total_zakat": 100000,
        "riwayat_zakat": [
            {
                "id": 1,
                "jenis_zakat": "Zakat Maal",
                "nominal": "100000.00",
                "status": "Berhasil",
                "tanggal": "2026-11-18"
            }
        ]
    }
}
```

---

## 4.8 Profil Pengguna

Endpoint ini digunakan untuk mengambil data profil pengguna yang sedang terautentikasi.

### Request

Method:

```http
GET
```

Endpoint:

```text
/api/profile.php
```

Header:

```http
Authorization: Bearer TOKEN
```

### Data yang Dikembalikan

- ID pengguna.
- Nama.
- Username.
- Nomor HP.
- Alamat.

### Response Contoh

```json
{
    "status": 200,
    "message": "Data profil berhasil diambil",
    "data": {
        "id": 1,
        "nama": "Nama Pengguna",
        "username": "username_pengguna",
        "no_hp": "081234567890",
        "alamat": "Alamat Pengguna"
    }
}
```

---

# 5. Autentikasi Bearer Token

Endpoint dashboard, rekening, transaksi, pembiayaan, zakat, dan profil membutuhkan autentikasi Bearer Token.

Token diperoleh melalui endpoint login API.

### Format Header

```http
Authorization: Bearer TOKEN_DARI_RESPONSE_LOGIN
```

### Contoh Request

```http
GET /api/accounts.php
Authorization: Bearer TOKEN_DARI_RESPONSE_LOGIN
```

### Proses Autentikasi

1. Pengguna melakukan login melalui endpoint API.
2. Sistem memverifikasi username dan password.
3. Sistem membuat token acak.
4. Token diubah menjadi hash SHA-256.
5. Hash token disimpan dalam tabel `api_tokens`.
6. Token asli dikirimkan melalui response login.
7. Pengguna mengirim token pada header Authorization.
8. API memeriksa token.
9. API memeriksa masa berlaku token.
10. Jika token valid, permintaan dapat diproses.

Token memiliki masa berlaku selama **24 jam**.

---

# 6. Pengujian API Menggunakan Postman

## A. Menguji Koneksi API

1. Buka Postman.
2. Buat request baru.
3. Pilih method `GET`.
4. Masukkan URL:

```text
http://localhost/bank_iman_syariah/api/test.php
```

5. Klik Send.
6. Periksa response JSON.

## B. Login API

1. Buat request baru.
2. Pilih method `POST`.
3. Masukkan URL:

```text
http://localhost/bank_iman_syariah/api/login.php
```

4. Pilih tab Body.
5. Pilih `raw`.
6. Pilih format `JSON`.
7. Masukkan username dan password.

Contoh:

```json
{
    "username": "username_pengguna",
    "password": "password_pengguna"
}
```

8. Klik Send.
9. Jika berhasil, salin token dari response pada bagian `data.token`.

## C. Mengakses Endpoint dengan Token

1. Buat request GET.
2. Masukkan endpoint yang ingin diuji.
3. Buka tab Authorization.
4. Pilih tipe `Bearer Token`.
5. Masukkan token yang diperoleh dari login API.
6. Klik Send.
7. Periksa data JSON yang dikembalikan.

Contoh URL:

```text
http://localhost/bank_iman_syariah/api/accounts.php
```

---

# 7. HTTP Status Code

| Kode | Keterangan |
|---|---|
| 200 | Permintaan berhasil |
| 400 | Data permintaan tidak lengkap atau tidak valid |
| 401 | Token tidak ditemukan, tidak valid, kedaluwarsa, atau login gagal |
| 404 | Pengguna atau data tidak ditemukan |
| 405 | Metode HTTP tidak diizinkan |
| 500 | Kesalahan pemrosesan atau database |

---

# 8. Keamanan API

Mekanisme keamanan yang diterapkan:

- Password diverifikasi menggunakan `password_verify()`.
- Password tidak dikirim dalam response API.
- Token dibuat menggunakan `random_bytes()`.
- Token disimpan dalam database menggunakan hash SHA-256.
- Token API memiliki masa berlaku 24 jam.
- Endpoint terproteksi melakukan pemeriksaan token.
- Data API dibatasi berdasarkan ID pengguna dari token.
- Query database menggunakan prepared statements pada query yang menerapkannya.

---

# 9. Database yang Digunakan

Nama database:

```text
bank_iman_syariah
```

File database:

```text
database/bank_iman_syariah.sql
```

### Tabel yang Digunakan API

| No | Nama Tabel | Fungsi |
|---|---|---|
| 1 | `users` | Menyimpan data pengguna |
| 2 | `accounts` | Menyimpan data rekening |
| 3 | `transactions` | Menyimpan data transaksi |
| 4 | `financings` | Menyimpan data pembiayaan |
| 5 | `zakat_payments` | Menyimpan data pembayaran zakat |
| 6 | `api_tokens` | Menyimpan token autentikasi API |

---

# 10. Batasan REST API

REST API Bank Iman Syariah pada versi ini menyediakan endpoint untuk:

- Pengujian koneksi.
- Login API.
- Pengambilan data dashboard.
- Pengambilan data rekening.
- Pengambilan riwayat transaksi.
- Pengambilan data pembiayaan.
- Pengambilan data zakat.
- Pengambilan data profil.

Fitur website berikut belum tersedia sebagai endpoint REST API:

- Login OTP website.
- Pengiriman ulang OTP.
- Transfer.
- Setor tunai.
- Penarikan tunai.
- Pengajuan pembukaan rekening.
- Pengajuan penutupan rekening.
- Pembayaran angsuran.
- Pengajuan dan proses administrasi pembiayaan melalui website.
- Pengelolaan nasabah oleh admin.
- Pengelolaan rekening oleh admin.
- Pengelolaan transaksi oleh admin.
- Pengelolaan laporan admin.

Fitur-fitur tersebut dijalankan melalui halaman dan proses PHP website.

---

# 11. Penutup

REST API Bank Iman Syariah dikembangkan untuk mendukung pertukaran data antara aplikasi dan database secara terstruktur menggunakan format JSON.

Dokumentasi ini menjelaskan endpoint yang tersedia, metode HTTP, autentikasi, format request, format response, database, keamanan, serta cara pengujian API menggunakan Postman.

---

**BANK IMAN SYARIAH**

*Digital Banking with Sharia Principles.*
