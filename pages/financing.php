
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];
$activePage = "financing";

// Ambil semua rekening aktif milik nasabah
$rekening = [];

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, nomor_rekening, jenis_akad, saldo
     FROM accounts
     WHERE user_id = ? AND status = 'Aktif'
     ORDER BY id ASC"
);

mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$resultRekening = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($resultRekening)) {
    $rekening[] = $row;
}

mysqli_stmt_close($stmt);

// Ambil pembiayaan terbaru milik nasabah
$query = "SELECT * FROM financings
          WHERE user_id = ?
          ORDER BY id DESC
          LIMIT 1";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$pembiayaan = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Data awal pembayaran
$terbayar = 0;
$tenor = 0;
$persentase = 0;
$sisaKewajiban = 0;
$riwayat = [];

// Jika pembiayaan tersedia, ambil data pembayaran
if ($pembiayaan) {
    $financing_id = (int) $pembiayaan["id"];

    $tenor = max((int) $pembiayaan["tenor"], 1);

    // Hitung angsuran yang sudah dibayar
    $stmt = mysqli_prepare(
        $conn,
        "SELECT COUNT(*) AS total
         FROM financing_payments
         WHERE financing_id = ? AND user_id = ?"
    );

    mysqli_stmt_bind_param($stmt, "ii", $financing_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $dataBayar = mysqli_fetch_assoc($result);

    $terbayar = (int) $dataBayar["total"];

    mysqli_stmt_close($stmt);

    $persentase = min(100, ($terbayar / $tenor) * 100);

    $sisaKewajiban = max(
        0,
        ((float) $pembiayaan["angsuran"] * $tenor) -
        ((float) $pembiayaan["angsuran"] * $terbayar)
    );

    // Ambil riwayat pembayaran asli
    $stmt = mysqli_prepare(
        $conn,
        "SELECT angsuran_ke, nominal, tanggal_bayar
         FROM financing_payments
         WHERE financing_id = ? AND user_id = ?
         ORDER BY angsuran_ke DESC"
    );

    mysqli_stmt_bind_param($stmt, "ii", $financing_id, $user_id);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $riwayat[] = $row;
    }

    mysqli_stmt_close($stmt);
}

// Ambil pengajuan terbaru
$queryPengajuan = "SELECT * FROM financing_applications
                   WHERE user_id = ?
                   ORDER BY tanggal_pengajuan DESC";

$stmtPengajuan = mysqli_prepare($conn, $queryPengajuan);
mysqli_stmt_bind_param($stmtPengajuan, "i", $user_id);
mysqli_stmt_execute($stmtPengajuan);

$resultPengajuan = mysqli_stmt_get_result($stmtPengajuan);
$pengajuan = [];

while ($row = mysqli_fetch_assoc($resultPengajuan)) {
    $pengajuan[] = $row;
}

mysqli_stmt_close($stmtPengajuan);

// Pesan pembayaran atau pengajuan
$pesan = $_SESSION["pesan_pembiayaan"] ?? "";
unset($_SESSION["pesan_pembiayaan"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Pembiayaan - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .payment-status.disetujui {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .payment-status.menunggu {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .payment-status.ditolak {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* Modal konfirmasi pembayaran */
        .modal-konfirmasi {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-konfirmasi.aktif {
            display: flex;
        }

        .modal-konfirmasi-box {
            background: #fff;
            width: 100%;
            max-width: 400px;
            padding: 28px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.18);
        }

        .modal-konfirmasi-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 16px;
            border-radius: 50%;
            background: #e8f5ef;
            color: #176b59;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
        }

        .modal-konfirmasi-box h3 {
            margin-bottom: 10px;
            color: #1f2937;
        }

        .modal-konfirmasi-box p {
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .modal-konfirmasi-actions {
            display: flex;
            gap: 10px;
        }

        .modal-konfirmasi-actions button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-batal-modal {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-lanjut-modal {
            background: #176b59;
            color: white;
        }
    </style>
</head>

<body>

<div class="app-layout financing-container">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content financing-content">

        <header class="financing-header">
            <div>
                <h1>Pembiayaan</h1>
                <p>Pantau pembiayaan dan jadwal angsuran Anda.</p>
            </div>

            <a href="#form-pengajuan" class="btn-ajukan-top">
                Ajukan pembiayaan
            </a>
        </header>

        <?php if ($pesan): ?>
            <div class="financing-alert">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>

        <?php if ($pembiayaan): ?>

            <?php
            $tenor = max((int) $pembiayaan["tenor"], 1);
            ?>

            <section class="financing-card">

                <div class="financing-card-heading">
                    <div>
                        <h2><?= htmlspecialchars($pembiayaan["jenis_akad"]); ?></h2>
                        <p>
                            ID pembiayaan PBY-<?= str_pad($pembiayaan["id"], 4, "0", STR_PAD_LEFT); ?>
                        </p>
                    </div>

                    <span class="financing-status">
                        <?= htmlspecialchars($pembiayaan["status"]); ?>
                    </span>
                </div>

                <div class="financing-summary">

                    <div class="financing-box">
                        <span>Plafon</span>
                        <h3>Rp <?= number_format($pembiayaan["plafon"], 0, ',', '.'); ?></h3>
                    </div>

                    <div class="financing-box">
                        <span>Margin</span>
                        <h3><?= htmlspecialchars($pembiayaan["margin"]); ?>% per tahun</h3>
                    </div>

                    <div class="financing-box">
                        <span>Tenor</span>
                        <h3><?= $tenor; ?> bulan</h3>
                    </div>

                    <div class="financing-box">
                        <span>Angsuran per bulan</span>
                        <h3>Rp <?= number_format($pembiayaan["angsuran"], 0, ',', '.'); ?></h3>
                    </div>

                </div>

                <div class="financing-progress">
                    <div class="progress-track">
                        <div class="progress-fill"
                             style="width: <?= $persentase; ?>%;">
                        </div>
                    </div>

                    <div class="progress-info">
                        <span><?= $terbayar; ?> dari <?= $tenor; ?> angsuran terbayar</span>
                        <strong>
                            Sisa kewajiban Rp <?= number_format($sisaKewajiban, 0, ',', '.'); ?>
                        </strong>
                    </div>
                </div>

                <?php if ($pembiayaan["status"] === "Aktif" && $terbayar < $tenor): ?>

                    <div class="financing-payment-action">
                        <h3>Bayar Angsuran</h3>

                        <p>
                            Pembayaran angsuran ke-<?= $terbayar + 1; ?>
                            sebesar
                            <strong>
                                Rp <?= number_format($pembiayaan["angsuran"], 0, ',', '.'); ?>
                            </strong>
                        </p>

                        <?php if (count($rekening) > 0): ?>

                            <form action="proses_bayar_angsuran.php"
                                  method="POST"
                                  onsubmit="return bukaKonfirmasiPembayaran(this);">

                                <input type="hidden"
                                       name="financing_id"
                                       value="<?= (int) $pembiayaan["id"]; ?>">

                                <label for="account_id_bayar">
                                    Pilih rekening pembayaran
                                </label>

                                <select name="account_id"
                                        id="account_id_bayar"
                                        required>

                                    <option value="">-- Pilih rekening --</option>

                                    <?php foreach ($rekening as $akun): ?>
                                        <option value="<?= (int) $akun["id"]; ?>">
                                            <?= htmlspecialchars($akun["nomor_rekening"]); ?>
                                            - <?= htmlspecialchars($akun["jenis_akad"]); ?>
                                            (Saldo: Rp <?= number_format($akun["saldo"], 0, ',', '.'); ?>)
                                        </option>
                                    <?php endforeach; ?>

                                </select>

                                <button type="submit" class="btn-submit-financing">
                                    Bayar Angsuran
                                </button>

                            </form>

                        <?php else: ?>

                            <p class="empty-text">
                                Anda belum memiliki rekening aktif untuk pembayaran.
                            </p>

                        <?php endif; ?>
                    </div>

                <?php elseif ($pembiayaan["status"] === "Lunas"): ?>

                    <div class="financing-payment-action">
                        <h3>Pembiayaan Telah Lunas</h3>
                        <p>Seluruh angsuran pembiayaan Anda sudah dibayarkan.</p>
                    </div>

                <?php endif; ?>

            </section>

        <?php else: ?>

            <section class="financing-empty">
                <h2>Belum Ada Pembiayaan Aktif</h2>
                <p>Silakan ajukan pembiayaan melalui formulir di bawah.</p>
            </section>

        <?php endif; ?>


        <div class="financing-columns">

            <!-- Jadwal angsuran -->
            <section class="payment-history">
                <h2>Jadwal angsuran</h2>
                <p>Angsuran terdekat dan yang sudah dibayar.</p>

                <?php if ($pembiayaan): ?>

                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Angsuran ke-</th>
                                    <th>Tanggal bayar</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php if (count($riwayat) > 0): ?>

                                    <?php foreach ($riwayat as $data): ?>
                                        <tr>
                                            <td><?= (int) $data["angsuran_ke"]; ?></td>

                                            <td>
                                                <?= date("d M Y", strtotime($data["tanggal_bayar"])); ?>
                                            </td>

                                            <td>
                                                Rp <?= number_format($data["nominal"], 0, ',', '.'); ?>
                                            </td>

                                            <td>
                                                <span class="payment-status lunas">
                                                    Lunas
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php else: ?>

                                    <tr>
                                        <td colspan="4" class="empty-text">
                                            Belum ada pembayaran angsuran.
                                        </td>
                                    </tr>

                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <small class="financing-note">
                        Riwayat pembayaran angsuran hanya menampilkan angsuran yang sudah dibayar.
                        Angsuran yang belum dibayar akan muncul di jadwal berikutnya.
                    </small>

                <?php else: ?>

                    <p class="empty-text">Belum ada jadwal angsuran.</p>

                <?php endif; ?>
            </section>


            <!-- Form pengajuan -->
            <section class="application-card" id="form-pengajuan">

                <h2>Ajukan pembiayaan baru</h2>
                <p>Simulasi sebelum mengajukan.</p>

                <form action="proses_pengajuan_pembiayaan.php" method="POST">

                    <label for="account_id_pengajuan">
                        Rekening tujuan pencairan
                    </label>

                    <select name="account_id"
                            id="account_id_pengajuan"
                            required>

                        <option value="">-- Pilih rekening tujuan --</option>

                        <?php foreach ($rekening as $akun): ?>
                            <option value="<?= (int) $akun["id"]; ?>">
                                <?= htmlspecialchars($akun["nomor_rekening"]); ?>
                                - <?= htmlspecialchars($akun["jenis_akad"]); ?>
                                (Saldo: Rp <?= number_format($akun["saldo"], 0, ',', '.'); ?>)
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <?php if (count($rekening) === 0): ?>
                        <p class="empty-text">
                            Anda belum memiliki rekening aktif untuk pengajuan pembiayaan.
                        </p>
                    <?php endif; ?>

                    <label for="jenis_akad">Jenis akad</label>

                    <select name="jenis_akad" id="jenis_akad" required>
                        <option value="Murabahah">Murabahah</option>
                        <option value="Mudharabah">Mudharabah</option>
                        <option value="Musyarakah">Musyarakah</option>
                    </select>

                    <label for="plafon">Plafon</label>

                    <input
                        type="number"
                        name="plafon"
                        id="plafon"
                        min="1000000"
                        max="100000000"
                        step="100000"
                        placeholder="Contoh: 25000000"
                        required
                    >

                    <label for="tenor">Tenor</label>

                    <select name="tenor" id="tenor" required>
                        <option value="12">12 bulan</option>
                        <option value="24">24 bulan</option>
                        <option value="36">36 bulan</option>
                        <option value="48">48 bulan</option>
                        <option value="60">60 bulan</option>
                    </select>

                    <div class="estimate-box">
                        <span>Estimasi angsuran</span>
                        <h3 id="estimasi">Rp 0 / bulan</h3>
                        <small>Indikatif, dengan margin 8% per tahun (flat).</small>
                    </div>

                    <button type="submit"
                            class="btn-submit-financing"
                            <?= count($rekening) === 0 ? "disabled" : ""; ?>>
                        Ajukan
                    </button>

                </form>

            </section>

        </div>


        <!-- Daftar pengajuan pengguna -->
        <section class="application-history">

            <h2>Pengajuan pembiayaan saya</h2>
            <p>Daftar pengajuan yang sudah tersimpan.</p>

            <?php if (count($pengajuan) > 0): ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Jenis akad</th>
                                <th>Plafon</th>
                                <th>Tenor</th>
                                <th>Estimasi angsuran</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pengajuan as $item): ?>

                                <?php
                                $status = $item["status"];

                                if ($status === "Disetujui") {
                                    $statusClass = "disetujui";
                                } elseif ($status === "Ditolak") {
                                    $statusClass = "ditolak";
                                } elseif ($status === "Menunggu Persetujuan") {
                                    $statusClass = "menunggu";
                                } else {
                                    $statusClass = "akan-datang";
                                }
                                ?>

                                <tr>
                                    <td>
                                        PPA-<?= str_pad($item["id"], 4, "0", STR_PAD_LEFT); ?>
                                    </td>

                                    <td><?= htmlspecialchars($item["jenis_akad"]); ?></td>

                                    <td>
                                        Rp <?= number_format($item["plafon"], 0, ',', '.'); ?>
                                    </td>

                                    <td><?= (int) $item["tenor"]; ?> bulan</td>

                                    <td>
                                        Rp <?= number_format($item["estimasi_angsuran"], 0, ',', '.'); ?>
                                    </td>

                                    <td>
                                        <span class="payment-status <?= $statusClass; ?>">
                                            <?= htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>

                <p class="empty-text">Belum ada pengajuan pembiayaan.</p>

            <?php endif; ?>

        </section>

    </main>
</div>


<!-- Modal konfirmasi pembayaran -->
<div class="modal-konfirmasi" id="modalKonfirmasiPembayaran">
    <div class="modal-konfirmasi-box">
        <div class="modal-konfirmasi-icon">?</div>

        <h3>Konfirmasi Pembayaran</h3>

        <p>
            Yakin ingin membayar angsuran ini?
            Saldo rekening Anda akan dikurangi.
        </p>

        <div class="modal-konfirmasi-actions">
            <button type="button"
                    class="btn-batal-modal"
                    onclick="tutupKonfirmasiPembayaran()">
                Batal
            </button>

            <button type="button"
                    class="btn-lanjut-modal"
                    onclick="lanjutkanPembayaran()">
                Ya, Bayar
            </button>
        </div>
    </div>
</div>

<script>
let formPembayaranAktif = null;

function bukaKonfirmasiPembayaran(form) {
    formPembayaranAktif = form;

    document
        .getElementById("modalKonfirmasiPembayaran")
        .classList.add("aktif");

    return false;
}

function tutupKonfirmasiPembayaran() {
    document
        .getElementById("modalKonfirmasiPembayaran")
        .classList.remove("aktif");

    formPembayaranAktif = null;
}

function lanjutkanPembayaran() {
    if (formPembayaranAktif) {
        HTMLFormElement.prototype.submit.call(formPembayaranAktif);
    }
}

// Perhitungan estimasi pembiayaan
const plafonInput = document.getElementById("plafon");
const tenorInput = document.getElementById("tenor");
const estimasiText = document.getElementById("estimasi");

function hitungEstimasi() {
    const plafon = Number(plafonInput.value);
    const tenor = Number(tenorInput.value);

    const margin = 8;
    const total = plafon * (1 + (margin / 100 * tenor / 12));
    const angsuran = tenor > 0 ? total / tenor : 0;

    estimasiText.textContent = "Rp " +
        Math.round(angsuran).toLocaleString("id-ID") +
        " / bulan";
}

plafonInput.addEventListener("input", hitungEstimasi);
tenorInput.addEventListener("change", hitungEstimasi);
</script>

</body>
</html>
