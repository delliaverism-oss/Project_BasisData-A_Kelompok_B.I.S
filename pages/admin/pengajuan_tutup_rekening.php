<?php
session_start();

require_once __DIR__ . "/../../config/database.php";

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../../index.php");
    exit;
}

$namaAdmin = $_SESSION["nama"] ?? "Administrator";
$activePage = "pengajuan_tutup_rekening";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$sql = "SELECT
            aca.id,
            aca.account_id,
            aca.alasan,
            aca.status,
            aca.tanggal_pengajuan,
            a.nomor_rekening,
            a.jenis_akad,
            a.saldo,
            u.nama,
            u.username
        FROM account_closure_applications aca
        INNER JOIN accounts a ON aca.account_id = a.id
        INNER JOIN users u ON a.user_id = u.id
        ORDER BY aca.tanggal_pengajuan DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

$totalPengajuan = mysqli_num_rows($result);

$pesan = $_SESSION["pesan_tutup_admin"] ?? "";
unset($_SESSION["pesan_tutup_admin"]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Tutup Rekening - Bank Iman Syariah</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">

    <style>
        /* TOMBOL SETUJUI DAN TOLAK */
        .aksi-buttons {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 8px;
            white-space: nowrap;
        }

        .aksi-buttons form {
            display: inline-flex !important;
            margin: 0 !important;
            padding: 0 !important;
            width: auto !important;
            flex: 0 0 auto !important;
        }

        .aksi-buttons button {
            min-width: 75px;
            height: 35px;
            margin: 0 !important;
            cursor: pointer;
        }

        /* MODAL KONFIRMASI */
        .custom-confirm-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 99999;
        }

        .custom-confirm-box {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.2);
        }

        .custom-confirm-icon {
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

        .custom-confirm-box h3 {
            margin: 0 0 10px;
            color: #1f2937;
        }

        .custom-confirm-box p {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 22px;
        }

        .custom-confirm-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
        }

        .custom-confirm-buttons button {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .custom-confirm-cancel {
            background: #e5e7eb;
            color: #374151;
        }

        .custom-confirm-yes {
            background: #198754;
            color: #ffffff;
        }

        .custom-confirm-cancel:hover {
            background: #d1d5db;
        }

        .custom-confirm-yes:hover {
            background: #157347;
        }
    </style>
</head>
<body>

<div class="admin-layout">

    <?php include __DIR__ . "/../../includes/admin_sidebar.php"; ?>

    <main class="main-content">

        <header class="topbar">
            <div>
                <h1>Pengajuan Tutup Rekening</h1>
                <p>Kelola permohonan penutupan rekening dari nasabah.</p>
            </div>

            <span class="admin-badge">ADMIN</span>
        </header>

        <section class="nasabah-summary">
            <div>
                <span>Total Pengajuan</span>
                <h2><?= number_format($totalPengajuan, 0, ',', '.'); ?></h2>
                <small>Seluruh pengajuan penutupan rekening</small>
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
                    <h2>Daftar Pengajuan Tutup Rekening</h2>
                    <p>Periksa data dan alasan penutupan rekening nasabah.</p>
                </div>
            </div>

            <div class="nasabah-table-wrapper">

                <table class="nasabah-table">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nasabah</th>
                            <th>Nomor Rekening</th>
                            <th>Jenis Akad</th>
                            <th>Alasan</th>
                            <th>Tanggal</th>
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
                                        <strong><?= htmlspecialchars($data["nama"]); ?></strong>
                                        <br>
                                        <small>@<?= htmlspecialchars($data["username"]); ?></small>
                                    </td>

                                    <td><?= htmlspecialchars($data["nomor_rekening"]); ?></td>

                                    <td><?= htmlspecialchars($data["jenis_akad"]); ?></td>

                                    <td>
                                        <?= htmlspecialchars($data["alasan"] ?: "-"); ?>
                                    </td>

                                    <td>
                                        <?= date(
                                            "d-m-Y H:i",
                                            strtotime($data["tanggal_pengajuan"])
                                        ); ?>
                                    </td>

                                    <td><?= htmlspecialchars($data["status"]); ?></td>

                                    <td>
                                        <?php if ($data["status"] === "Menunggu Persetujuan"): ?>

                                            <div class="aksi-buttons">

                                                <form method="POST"
                                                      action="proses_tutup_rekening_admin.php"
                                                      class="custom-confirm-form"
                                                      data-confirm="Yakin ingin menyetujui penutupan rekening ini?"
                                                      data-confirm-title="Persetujuan Tutup Rekening">

                                                    <input type="hidden" name="csrf_token"
                                                           value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                                    <input type="hidden" name="pengajuan_id"
                                                           value="<?= (int) $data["id"]; ?>">

                                                    <input type="hidden" name="aksi"
                                                           value="setujui">

                                                    <button type="submit" class="btn-setujui">
                                                        Setujui
                                                    </button>
                                                </form>

                                                <form method="POST"
                                                      action="proses_tutup_rekening_admin.php"
                                                      class="custom-confirm-form"
                                                      data-confirm="Yakin ingin menolak pengajuan ini?"
                                                      data-confirm-title="Penolakan Tutup Rekening">

                                                    <input type="hidden" name="csrf_token"
                                                           value="<?= htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                                    <input type="hidden" name="pengajuan_id"
                                                           value="<?= (int) $data["id"]; ?>">

                                                    <input type="hidden" name="aksi"
                                                           value="tolak">

                                                    <button type="submit" class="btn-tolak">
                                                        Tolak
                                                    </button>
                                                </form>

                                            </div>

                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="8" class="empty-data">
                                    Belum ada pengajuan penutupan rekening.
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
<div id="customConfirmOverlay" class="custom-confirm-overlay">
    <div class="custom-confirm-box">

        <div class="custom-confirm-icon">!</div>

        <h3 id="customConfirmTitle">Konfirmasi</h3>

        <p id="customConfirmMessage"></p>

        <div class="custom-confirm-buttons">
            <button type="button"
                    id="customConfirmCancel"
                    class="custom-confirm-cancel">
                Batal
            </button>

            <button type="button"
                    id="customConfirmYes"
                    class="custom-confirm-yes">
                Ya, Lanjutkan
            </button>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const overlay = document.getElementById("customConfirmOverlay");
    const title = document.getElementById("customConfirmTitle");
    const message = document.getElementById("customConfirmMessage");
    const cancelButton = document.getElementById("customConfirmCancel");
    const yesButton = document.getElementById("customConfirmYes");

    let formPending = null;

    document.querySelectorAll(".custom-confirm-form").forEach(function (form) {

        form.addEventListener("submit", function (event) {

            if (form.dataset.confirmed === "true") {
                return;
            }

            event.preventDefault();

            formPending = form;

            title.textContent = form.dataset.confirmTitle || "Konfirmasi";
            message.textContent = form.dataset.confirm || "Apakah Anda yakin ingin melanjutkan?";

            overlay.style.display = "flex";
        });

    });

    cancelButton.addEventListener("click", function () {
        overlay.style.display = "none";
        formPending = null;
    });

    yesButton.addEventListener("click", function () {

        if (!formPending) {
            return;
        }

        const form = formPending;
        formPending = null;

        form.dataset.confirmed = "true";
        overlay.style.display = "none";

        HTMLFormElement.prototype.submit.call(form);
    });

    overlay.addEventListener("click", function (event) {

        if (event.target === overlay) {
            overlay.style.display = "none";
            formPending = null;
        }

    });

});
</script>

</body>
</html>

<?php
mysqli_free_result($result);
?>
```