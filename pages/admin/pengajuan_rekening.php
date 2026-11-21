
<?php
session_start();

require_once __DIR__ . "/../../config/database.php";

// Proteksi halaman admin
if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../../index.php");
    exit;
}

$namaAdmin = $_SESSION["nama"] ?? "Administrator";
$activePage = "pengajuan_rekening";

// Token keamanan
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Ambil data pengajuan rekening
$sql = "SELECT
            aa.id,
            aa.user_id,
            aa.jenis_akad,
            aa.status,
            aa.tanggal_pengajuan,
            u.nama,
            u.username
        FROM account_applications aa
        INNER JOIN users u ON aa.user_id = u.id
        ORDER BY aa.tanggal_pengajuan DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

$totalPengajuan = mysqli_num_rows($result);

$pesan = $_SESSION["pesan_rekening"] ?? "";
unset($_SESSION["pesan_rekening"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Pembukaan Rekening - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">

    <style>
        /* MODAL KONFIRMASI */
        .rekening-confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 99999;
        }

        .rekening-confirm-box {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
        }

        .rekening-confirm-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e8f5ee;
            color: #198754;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0 auto 14px;
        }

        .rekening-confirm-box h3 {
            margin: 0 0 10px;
            color: #1f2937;
        }

        .rekening-confirm-box p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 22px;
        }

        .rekening-confirm-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .rekening-confirm-buttons button {
            flex: 1;
            border: none;
            border-radius: 8px;
            padding: 11px 15px;
            cursor: pointer;
            font-weight: 600;
        }

        .rekening-confirm-cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .rekening-confirm-yes {
            background: #198754;
            color: #ffffff;
        }

        .rekening-confirm-cancel:hover {
            background: #d1d5db;
        }

        .rekening-confirm-yes:hover {
            background: #157347;
        }
        /* Tombol aksi pengajuan rekening */
.rekening-confirm-form {
    display: inline-flex !important;
    margin: 0 !important;
    vertical-align: middle;
}

.rekening-confirm-form button {
    min-width: 75px;
    height: 34px;
    padding: 0 12px;
}

td:has(.rekening-confirm-form) {
    white-space: nowrap;
}
    </style>
</head>
<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Pengajuan Pembukaan Rekening</h1>
                <p>Kelola permohonan pembukaan rekening dari nasabah.</p>
            </div>

            <span class="admin-badge">ADMIN</span>
        </header>

        <section class="nasabah-summary">
            <div>
                <span>Total Pengajuan</span>
                <h2><?= number_format($totalPengajuan, 0, ',', '.'); ?></h2>
                <small>Seluruh pengajuan pembukaan rekening</small>
            </div>
        </section>

        <?php if ($pesan !== ""): ?>
            <div class="pengajuan-alert">
                <?= htmlspecialchars($pesan); ?>
            </div>
        <?php endif; ?>

        <section class="nasabah-table-card">

            <div class="nasabah-table-header">
                <div>
                    <h2>Daftar Pengajuan Rekening</h2>
                    <p>Periksa permohonan sebelum memberikan keputusan.</p>
                </div>
            </div>

            <div class="nasabah-table-wrapper">

                <table class="nasabah-table">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nasabah</th>
                            <th>Jenis Akad</th>
                            <th>Tanggal Pengajuan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if ($totalPengajuan > 0): ?>

                            <?php $no = 1; ?>

                            <?php while ($data = mysqli_fetch_assoc($result)): ?>

                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <strong>
                                            <?= htmlspecialchars($data["nama"]); ?>
                                        </strong>
                                        <br>
                                        <small>
                                            @<?= htmlspecialchars($data["username"]); ?>
                                        </small>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["jenis_akad"]); ?>
                                    </td>

                                    <td>
                                        <?= date(
                                            "d-m-Y H:i",
                                            strtotime($data["tanggal_pengajuan"])
                                        ); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($data["status"]); ?>
                                    </td>

                                    <td>

                                        <?php if ($data["status"] === "Menunggu Persetujuan"): ?>

                                            <form
                                                method="POST"
                                                action="proses_pengajuan_rekening.php"
                                                class="rekening-confirm-form"
                                                data-confirm="Yakin ingin menyetujui pembukaan rekening ini?"
                                                data-title="Persetujuan Rekening"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                    value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                                <input type="hidden" name="pengajuan_id"
                                                    value="<?= (int) $data["id"]; ?>">

                                                <input type="hidden" name="aksi" value="setujui">

                                                <button type="submit" class="btn-setujui">
                                                    Setujui
                                                </button>
                                            </form>

                                            <form
                                                method="POST"
                                                action="proses_pengajuan_rekening.php"
                                                class="rekening-confirm-form"
                                                data-confirm="Yakin ingin menolak pengajuan ini?"
                                                data-title="Penolakan Rekening"
                                            >
                                                <input type="hidden" name="csrf_token"
                                                    value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                                <input type="hidden" name="pengajuan_id"
                                                    value="<?= (int) $data["id"]; ?>">

                                                <input type="hidden" name="aksi" value="tolak">

                                                <button type="submit" class="btn-tolak">
                                                    Tolak
                                                </button>
                                            </form>

                                        <?php else: ?>

                                            <span>-</span>

                                        <?php endif; ?>

                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="6" class="empty-data">
                                    Belum ada pengajuan pembukaan rekening.
                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

<!-- MODAL KONFIRMASI -->
<div id="rekeningConfirmOverlay" class="rekening-confirm-overlay">
    <div class="rekening-confirm-box">

        <div class="rekening-confirm-icon">!</div>

        <h3 id="rekeningConfirmTitle">Konfirmasi</h3>

        <p id="rekeningConfirmMessage"></p>

        <div class="rekening-confirm-buttons">
            <button type="button"
                    id="rekeningConfirmCancel"
                    class="rekening-confirm-cancel">
                Batal
            </button>

            <button type="button"
                    id="rekeningConfirmYes"
                    class="rekening-confirm-yes">
                Ya, Lanjutkan
            </button>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const overlay = document.getElementById("rekeningConfirmOverlay");
    const title = document.getElementById("rekeningConfirmTitle");
    const message = document.getElementById("rekeningConfirmMessage");
    const cancelButton = document.getElementById("rekeningConfirmCancel");
    const yesButton = document.getElementById("rekeningConfirmYes");

    let formTarget = null;

    document.querySelectorAll(".rekening-confirm-form").forEach(function (form) {

        form.addEventListener("submit", function (event) {

            if (formTarget === form) {
                return;
            }

            event.preventDefault();

            formTarget = form;

            title.textContent = form.dataset.title || "Konfirmasi";
            message.textContent = form.dataset.confirm || "Apakah Anda yakin ingin melanjutkan?";

            overlay.style.display = "flex";

        });

    });

    cancelButton.addEventListener("click", function () {
        overlay.style.display = "none";
        formTarget = null;
    });

    yesButton.addEventListener("click", function () {

        if (!formTarget) {
            return;
        }

        const form = formTarget;
        formTarget = null;

        overlay.style.display = "none";

        // Kirim form langsung ke PHP
        HTMLFormElement.prototype.submit.call(form);

    });

    overlay.addEventListener("click", function (event) {

        if (event.target === overlay) {
            overlay.style.display = "none";
            formTarget = null;
        }

    });

});
</script>

</body>
</html>

<?php
mysqli_free_result($result);
?>
