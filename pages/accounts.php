
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../index.php");
    exit;
}

require_once "../config/database.php";

$user_id = (int) $_SESSION["user_id"];
$activePage = "accounts";

// Ambil rekening aktif milik nasabah
$query = "SELECT id, nomor_rekening, jenis_akad, saldo, status
          FROM accounts
          WHERE user_id = ? AND status = 'Aktif'
          ORDER BY id ASC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$rekening = [];

while ($row = mysqli_fetch_assoc($result)) {
    $rekening[] = $row;
}

mysqli_stmt_close($stmt);

// Notifikasi tutup rekening
$pesanTutup = "";
$tipeTutup = "";

if (isset($_GET["tutup"]) && $_GET["tutup"] === "berhasil") {
    $pesanTutup = "Pengajuan penutupan rekening berhasil dikirim dan sedang menunggu persetujuan admin.";
    $tipeTutup = "success";
} elseif (isset($_GET["tutup"]) && $_GET["tutup"] === "gagal") {
    $pesanTutup = $_SESSION["pesan_tutup_rekening"] ?? "Pengajuan penutupan rekening gagal.";
    $tipeTutup = "error";
    unset($_SESSION["pesan_tutup_rekening"]);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekening - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .account-cash-actions form {
            grid-column: 1 / -1;
            width: 100%;
            display: flex;
            flex-direction: column;
            margin-top: 0;
        }

        .account-cash-actions form .btn-tutup-rekening {
            width: 100%;
            padding: 13px;
            background: #dc3545;
            color: #ffffff;
            border: none;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .account-cash-actions form .btn-tutup-rekening:hover {
            background: #bb2d3b;
        }

        /* MODAL KONFIRMASI */
        .modal-tutup-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            z-index: 99999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-tutup-overlay.show {
            display: flex;
        }

        .modal-tutup-box {
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            border-radius: 16px;
            padding: 26px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
            animation: modalTutupMasuk 0.2s ease;

            /* SELURUH ISI MODAL DI TENGAH */
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        /* LINGKARAN ICON DI TENGAH */
        .modal-tutup-icon {
            width: 60px;
            height: 60px;
            min-width: 60px;
            background: #fff0f0;
            color: #dc3545;
            border-radius: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        /* TANDA SERU */
        .modal-tutup-icon::after {
            content: "!";
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            font-size: 28px;
            font-weight: 400;
            line-height: 1;
            padding-bottom: 2px;
        }

        /* JUDUL DI TENGAH */
        .modal-tutup-box h3 {
            color: #203b35;
            font-size: 21px;
            width: 100%;
            text-align: center;
            margin: 0 0 10px;
        }

        /* DESKRIPSI DI TENGAH */
        .modal-tutup-box p {
            color: #687873;
            font-size: 14px;
            line-height: 1.6;
            width: 100%;
            text-align: center;
            margin: 0 0 22px;
        }

        /* TOMBOL */
        .modal-tutup-actions {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            gap: 10px;
        }

        .modal-tutup-actions button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 9px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-modal-batal {
            background: #eef2f1;
            color: #40534d;
        }

        .btn-modal-batal:hover {
            background: #e0e7e5;
        }

        .btn-modal-lanjut {
            background: #dc3545;
            color: white;
        }

        .btn-modal-lanjut:hover {
            background: #bb2d3b;
        }

        /* NOTIFIKASI CUSTOM */
        .rekening-toast {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 100000;
            width: calc(100% - 40px);
            max-width: 380px;
            padding: 16px 20px;
            border-radius: 12px;
            background: white;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            animation: toastMasuk 0.3s ease;
        }

        .rekening-toast.success {
            border-left: 5px solid #16a34a;
        }

        .rekening-toast.error {
            border-left: 5px solid #dc3545;
        }

        .rekening-toast-icon {
            font-size: 20px;
            font-weight: bold;
        }

        .rekening-toast.success .rekening-toast-icon {
            color: #16a34a;
        }

        .rekening-toast.error .rekening-toast-icon {
            color: #dc3545;
        }

        .rekening-toast-text {
            color: #33443e;
            font-size: 14px;
            line-height: 1.5;
        }

        .rekening-toast-text strong {
            display: block;
            margin-bottom: 3px;
        }

        @keyframes modalTutupMasuk {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toastMasuk {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 480px) {
            .rekening-toast {
                top: 15px;
                right: 15px;
            }

            .modal-tutup-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="app-layout">

    <?php include "../includes/sidebar.php"; ?>

    <main class="main-content accounts-page">

        <header class="page-header">
            <div>
                <h1>Rekening</h1>
                <p>Kelola rekening tabungan syariah Anda.</p>
            </div>

            <button class="btn-primary" type="button"
                    onclick="bukaFormRekening()">
                + Buka rekening
            </button>
        </header>

        <?php if (isset($_GET["diajukan"])): ?>
            <div class="account-alert success">
                Pengajuan pembukaan rekening berhasil dikirim dan sedang menunggu persetujuan admin.
            </div>
        <?php elseif (isset($_GET["pending"])): ?>
            <div class="account-alert error">
                Anda masih memiliki pengajuan yang menunggu persetujuan admin.
            </div>
        <?php elseif (isset($_GET["success"])): ?>
            <div class="account-alert success">
                Rekening berhasil dibuka!
            </div>
        <?php elseif (isset($_GET["error"])): ?>
            <div class="account-alert error">
                Pembukaan rekening gagal. Silakan coba lagi.
            </div>
        <?php endif; ?>

        <!-- NOTIFIKASI TUTUP REKENING -->
        <?php if (!empty($pesanTutup)): ?>
            <div class="rekening-toast <?= htmlspecialchars($tipeTutup); ?>"
                 id="rekeningToast">

                <div class="rekening-toast-icon">
                    <?= $tipeTutup === "success" ? "✓" : "!"; ?>
                </div>

                <div class="rekening-toast-text">
                    <strong>
                        <?= $tipeTutup === "success" ? "Berhasil" : "Gagal"; ?>
                    </strong>

                    <?= htmlspecialchars($pesanTutup); ?>
                </div>
            </div>
        <?php endif; ?>

        <div id="formRekening" class="form-rekening" style="display:none;">
            <h2>Form Pembukaan Rekening</h2>

            <form action="proses_rekening.php" method="POST">
                <label for="jenis_akad">Jenis Akad</label>

                <select name="jenis_akad" id="jenis_akad" required>
                    <option value="">Pilih jenis akad</option>
                    <option value="Wadiah">Wadiah</option>
                    <option value="Mudharabah">Mudharabah</option>
                </select>

                <div class="form-rekening-actions">
                    <button type="submit" class="btn-primary">
                        Ajukan Pembukaan
                    </button>

                    <button type="button" class="btn-cancel"
                            onclick="tutupFormRekening()">
                        Batal
                    </button>
                </div>
            </form>
        </div>

        <section class="accounts-section">
            <h2>Rekening saya</h2>
            <p>Jenis akad dan saldo rekening Anda.</p>

            <div class="account-grid">

                <?php if (count($rekening) > 0): ?>

                    <?php foreach ($rekening as $r): ?>

                        <div class="account-card">

                            <div class="account-top">
                                <h3>
                                    Tabungan iB <?= htmlspecialchars($r["jenis_akad"]); ?>
                                </h3>

                                <span class="akad-badge">
                                    <?= htmlspecialchars($r["jenis_akad"]); ?>
                                </span>
                            </div>

                            <p class="account-label">Saldo</p>

                            <h2 class="account-balance">
                                Rp <?= number_format($r["saldo"], 0, ',', '.'); ?>
                            </h2>

                            <div class="account-detail">

                                <div>
                                    <small>No. rekening</small>
                                    <strong>
                                        <?= htmlspecialchars($r["nomor_rekening"]); ?>
                                    </strong>
                                </div>

                                <div>
                                    <small>Jenis akad</small>
                                    <strong>
                                        <?= htmlspecialchars($r["jenis_akad"]); ?>
                                    </strong>
                                </div>

                                <div>
                                    <small>ID rekening</small>
                                    <strong>
                                        <?= (int) $r["id"]; ?>
                                    </strong>
                                </div>

                                <div>
                                    <small>Status</small>
                                    <strong class="status-active">
                                        <?= htmlspecialchars($r["status"]); ?>
                                    </strong>
                                </div>

                            </div>

                            <div class="account-actions">
                                <a href="transactions.php">Lihat mutasi</a>

                                <a href="detail_rekening.php?id=<?= (int) $r["id"]; ?>">
                                    Lihat detail
                                </a>
                            </div>

                            <div class="account-cash-actions">

                                <a href="setor_tunai.php">Setor tunai</a>

                                <a href="tarik_tunai.php">Tarik tunai</a>

                                <a href="transfer.php?rekening_id=<?= (int) $r["id"]; ?>"
                                   class="transfer-account-button">
                                    Transfer
                                </a>

                                <form action="proses_tutup_rekening.php"
                                      method="POST"
                                      class="form-tutup-rekening">

                                    <input type="hidden"
                                           name="account_id"
                                           value="<?= (int) $r["id"]; ?>">

                                    <button type="submit"
                                            class="btn-tutup-rekening">
                                        Tutup Rekening
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <div class="empty-account">
                        Anda belum memiliki rekening aktif.
                    </div>

                <?php endif; ?>

            </div>
        </section>

        <section class="akad-section">
            <h2>Mengenal akad rekening</h2>
            <p>Perbedaan akad pada tabungan syariah.</p>

            <div class="akad-grid">

                <div class="akad-info">
                    <span class="akad-badge">Wadiah</span>
                    <p>
                        Titipan dana yang aman dan dapat diambil
                        kapan saja. Bank boleh memberikan bonus
                        secara sukarela, tanpa dijanjikan di awal.
                    </p>
                </div>

                <div class="akad-info">
                    <span class="akad-badge">Mudharabah</span>
                    <p>
                        Kerja sama pengelolaan dana. Keuntungan
                        dibagi sesuai nisbah yang disepakati
                        di awal.
                    </p>
                </div>

            </div>
        </section>

    </main>
</div>

<!-- MODAL KONFIRMASI TUTUP REKENING -->
<div class="modal-tutup-overlay" id="modalTutupRekening">
    <div class="modal-tutup-box">

        <div class="modal-tutup-icon"></div>

        <h3>Konfirmasi Tutup Rekening</h3>

        <p>
            Apakah Anda yakin ingin mengajukan penutupan rekening?
            Pengajuan akan dikirim kepada admin untuk diproses.
        </p>

        <div class="modal-tutup-actions">
            <button type="button"
                    class="btn-modal-batal"
                    onclick="batalTutupRekening()">
                Batal
            </button>

            <button type="button"
                    class="btn-modal-lanjut"
                    id="btnKonfirmasiTutup">
                Ya, Ajukan
            </button>
        </div>

    </div>
</div>

<script>
function bukaFormRekening() {
    document.getElementById("formRekening").style.display = "block";
}

function tutupFormRekening() {
    document.getElementById("formRekening").style.display = "none";
}

// MODAL KONFIRMASI TUTUP REKENING
let formTutupAktif = null;

document.querySelectorAll(".form-tutup-rekening").forEach(function(form) {
    form.addEventListener("submit", function(event) {
        event.preventDefault();

        formTutupAktif = form;

        document.getElementById("modalTutupRekening").classList.add("show");
    });
});

function batalTutupRekening() {
    formTutupAktif = null;

    document.getElementById("modalTutupRekening").classList.remove("show");
}

document.getElementById("btnKonfirmasiTutup").addEventListener("click", function() {
    if (formTutupAktif) {
        HTMLFormElement.prototype.submit.call(formTutupAktif);
    }
});

// Klik area luar modal untuk menutup
document.getElementById("modalTutupRekening").addEventListener("click", function(event) {
    if (event.target === this) {
        batalTutupRekening();
    }
});

// Toast hilang otomatis
const rekeningToast = document.getElementById("rekeningToast");

if (rekeningToast) {
    setTimeout(function() {
        rekeningToast.style.transition = "opacity 0.4s ease";
        rekeningToast.style.opacity = "0";

        setTimeout(function() {
            rekeningToast.remove();
        }, 400);
    }, 5000);
}
</script>

</body>
</html>
