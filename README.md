# Project_BasisData-A_Kelompok_B.I.S

# Bank Iman Syariah – Aplikasi Mobile Banking

Bank Iman Syariah adalah aplikasi mobile banking yang memungkinkan nasabah mengelola rekening, bertransaksi, membayar zakat/sedekah, dan mengajukan pembiayaan. Seluruh layanan berjalan sesuai prinsip syariah (**tanpa riba, gharar, dan maysir**).

---

## Daftar Isi

1. [Tujuan Bisnis](#tujuan-bisnis)
2. [Ruang Lingkup](#ruang-lingkup)
3. [Pengguna Sasaran](#pengguna-sasaran)
4. [Daftar Fitur](#daftar-fitur-functional-requirements)
5. [Kebutuhan Non-Fungsional](#kebutuhan-non-fungsional)
6. [Batasan](#batasan-constraints)
7. [Model Data](#model-data)
8. [Kriteria Penerimaan](#kriteria-penerimaan)
9. [Risiko dan Mitigasi](#risiko-dan-mitigasi)
10. [Jadwal Pengembangan](#jadwal-pengembangan-usulan)

---

## Tujuan Bisnis

1. Memberi nasabah layanan perbankan syariah 24 jam lewat ponsel.
2. Mengurangi antrean dan beban transaksi di kantor cabang.
3. Meningkatkan penghimpunan dana (tabungan) dan penyaluran pembiayaan syariah.
4. Memudahkan pembayaran zakat, infak, dan sedekah secara digital.

## Ruang Lingkup

**Termasuk (In Scope)**
- Registrasi dan login
- Informasi rekening
- Transfer
- Pembayaran
- Zakat dan sedekah
- Simulasi dan pengajuan pembiayaan
- Notifikasi

**Tidak termasuk (Out of Scope)**
- Investasi / pasar modal
- Kartu kredit
- Versi web
- Layanan di luar negeri

## Pengguna Sasaran

| Pengguna | Kebutuhan utama |
|---|---|
| Nasabah individu | Cek saldo, transfer, bayar zakat, ajukan pembiayaan |
| Admin bank | Kelola data nasabah, verifikasi pembiayaan, laporan |
| Petugas syariah | Memastikan akad dan produk sesuai fatwa |

## Daftar Fitur (Functional Requirements)

| ID | Fitur | Deskripsi | Prioritas |
|---|---|---|---|
| F-01 | Registrasi dan verifikasi | Daftar dengan NIK, nomor HP, OTP, dan foto KTP/selfie | Tinggi |
| F-02 | Login | Nama pengguna + kata sandi, opsi sidik jari/Face ID | Tinggi |
| F-03 | Beranda | Sapaan, saldo, menu utama, jadwal sholat | Tinggi |
| F-04 | Informasi rekening dan mutasi | Lihat saldo Tabungan Wadiah/Mudharabah dan riwayat transaksi | Tinggi |
| F-05 | Transfer | Antar rekening dan antar bank, menampilkan akad Wakalah bil Ujrah dan biaya admin | Tinggi |
| F-06 | Pembayaran | Listrik, air, pulsa, dan QRIS | Sedang |
| F-07 | Zakat, infak, sedekah | Kalkulator zakat (maal/penghasilan/emas) dan pembayaran ke lembaga amil | Tinggi |
| F-08 | Pembiayaan | Simulasi cicilan Murabahah, pengajuan Mudharabah/Musyarakah, pantau status | Sedang |
| F-09 | Notifikasi | Push notifikasi transaksi dan pengingat sholat/zakat | Sedang |
| F-10 | Profil dan keamanan | Ubah PIN/kata sandi, kelola perangkat | Sedang |
| F-11 | Tabungan Haji | Pantau setoran dan estimasi keberangkatan | Rendah |

## Kebutuhan Non-Fungsional

- **Keamanan:** enkripsi data (TLS), PIN transaksi, OTP, autentikasi biometrik, sesi otomatis berakhir setelah 5 menit tidak aktif.
- **Performa:** halaman utama dimuat maksimal 3 detik; transaksi diproses maksimal 5 detik.
- **Ketersediaan:** uptime minimal 99,5%.
- **Kompatibilitas:** Android 9+ dan iOS 14+.
- **Kepatuhan:** sesuai fatwa DSN-MUI dan regulasi OJK/Bank Indonesia; perlindungan data pribadi.

## Batasan (Constraints)

- Setiap produk dan akad harus disetujui Dewan Pengawas Syariah.
- Tidak ada bunga; biaya hanya berupa akad yang sah (margin, ujrah, bagi hasil).
- Transfer antar bank bergantung pada layanan switching pihak ketiga.
- Anggaran dan waktu pengembangan mengikuti kesepakatan dengan klien.

### Use Case

| Aktor | Use case |
|---|---|
| Nasabah | Registrasi, login, cek saldo, transfer, bayar tagihan, bayar zakat, ajukan pembiayaan, lihat mutasi |
| Admin bank | Verifikasi nasabah, setujui/tolak pembiayaan, kelola data, lihat laporan |
| Petugas syariah | Tinjau akad dan produk |
| Sistem eksternal | Switching antar bank, lembaga amil zakat, layanan OTP |

## Model Data

Entitas utama: **Nasabah, Rekening, Transaksi, Pembiayaan, Zakat**
(detail relasi ada di file ERD terpisah).

## Kriteria Penerimaan

- Nasabah dapat registrasi, login, dan transfer tanpa error pada skenario normal.
- Setiap transaksi tercatat di mutasi dan menghasilkan bukti.
- Perhitungan zakat dan simulasi cicilan sesuai rumus yang disepakati.
- Seluruh akad tampil jelas sebelum nasabah mengonfirmasi.

## Risiko dan Mitigasi

| Risiko | Mitigasi |
|---|---|
| Kebocoran data nasabah | Enkripsi, audit keamanan, pembatasan akses |
| Akad tidak sesuai syariah | Review rutin oleh Dewan Pengawas Syariah |
| Gangguan layanan pihak ketiga | Jalur cadangan dan pesan error yang jelas |

## Jadwal Pengembangan

| Fase | Kegiatan | Durasi |
|---|---|---|
| 1 | Analisis PRD dan ERD | 1 minggu |


## Kelompok 
| No | Nama | NPM |
|---|---|---|
| 1 | Muchamad Rava Alvriansyah | 4525210040 |
| 2 | Muhammad Rian Ramadhan | 4525210047 |
| 3 | Muhammad Ryza Mahameru | 4525210048 |
| 4 | ⁠Muhammad Faisal Athallah | 4525210120 |
| 5 | Delia Veris Maureta | 4525210119 |
| 6 | Azzahra Navisha | 4525210118 |

## Link ERD:
https://drive.google.com/file/d/1_jWPjOJSGutoS29IDWKx_6t16L9iJijR/view?usp=sharing
