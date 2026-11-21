<?php
session_start();
require_once __DIR__ . "/config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nama = trim($_POST["nama"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $nik = trim($_POST["nik"] ?? "");
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $konfirmasi = $_POST["konfirmasi"] ?? "";
    $no_hp = trim($_POST["no_hp"] ?? "");
    $alamat = trim($_POST["alamat"] ?? "");

    if (
        $nama === "" || $email === "" || $nik === "" ||
        $username === "" || $password === "" ||
        $no_hp === "" || $alamat === ""
    ) {
        $error = "Semua kolom wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (strlen($nik) !== 16 || !ctype_digit($nik)) {
        $error = "NIK harus terdiri dari 16 digit angka.";
    } elseif (strlen($username) < 4) {
        $error = "Username minimal 4 karakter.";
    } elseif (strlen($password) < 8) {
        $error = "Password minimal 8 karakter.";
    } elseif ($password !== $konfirmasi) {
        $error = "Konfirmasi password tidak cocok.";
    } else {

        // Periksa NIK, email, dan username
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE nik = ? OR email = ? OR username = ?"
        );

        mysqli_stmt_bind_param($stmt, "sss", $nik, $email, $username);
        mysqli_stmt_execute($stmt);

        $hasil = mysqli_stmt_get_result($stmt);
        $akunAda = mysqli_fetch_assoc($hasil);

        mysqli_stmt_close($stmt);

        if ($akunAda) {

            $error = "NIK, email, atau username sudah terdaftar.";

        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $role = "nasabah";

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO users
                (nama, email, nik, username, password, no_hp, alamat, role, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssss",
                $nama,
                $email,
                $nik,
                $username,
                $passwordHash,
                $no_hp,
                $alamat,
                $role
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: index.php?registered=1");
                exit;
            } else {
                $error = "Pendaftaran gagal. Silakan coba lagi.";
                mysqli_stmt_close($stmt);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - Bank Iman Syariah</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>

.register-page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 30px 15px;
    background: linear-gradient(
        135deg,
        #064e3b,
        #087653,
        #065b40
    );
}


/* LINGKARAN DEKORASI HALAMAN DAFTAR AKUN */

.register-page {
    position: relative;
    overflow: hidden;
    isolation: isolate;
}

.register-page::before {
    content: "";
    position: absolute;
    width: 600px;
    height: 600px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.13);
    top: -250px;
    right: -280px;
    z-index: 0;
    pointer-events: none;
}

.register-page::after {
    content: "";
    position: absolute;
    width: 400px;
    height: 400px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.13);
    bottom: -70px;
    left: -130px;
    z-index: 0;
    pointer-events: none;
}

/* Form tetap berada di depan lingkaran */
.register-page > * {
    position: relative;
    z-index: 1;
}


        .register-box {
            width: 100%;
            max-width: 460px;
            background: white;
            padding: 35px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,.07);
        }

        .register-box h1 {
            color: #176b59;
            font-size: 25px;
            margin-bottom: 8px;
        }

        .register-box p {
            color: #71817b;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .register-box label {
            display: block;
            font-size: 13px;
            font-weight: bold;
            margin: 14px 0 7px;
            color: #263b36;
        }

        .register-box input,
        .register-box textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #dce5e1;
            border-radius: 7px;
            outline: none;
            font-size: 14px;
        }

        .register-box input:focus,
        .register-box textarea:focus {
            border-color: #176b59;
        }

        .register-box button {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #176b59;
            color: white;
            font-weight: bold;
            margin-top: 22px;
            cursor: pointer;
        }

        .register-box button:hover {
            background: #123d35;
        }

        .register-error {
            background: #fdeaea;
            color: #a33b3b;
            padding: 12px;
            border-radius: 7px;
            font-size: 13px;
            margin-bottom: 15px;
        }

        .register-login {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }

        .register-login a {
            color: #176b59;
            font-weight: bold;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="register-page">
    <div class="register-box">

        <h1>Daftar Akun</h1>
        <p>Buat akun nasabah Bank Iman Syariah.</p>

        <?php if ($error !== ""): ?>
            <div class="register-error">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST">

            <label>Nama Lengkap</label>
            <input
                type="text"
                name="nama"
                value="<?= htmlspecialchars($_POST["nama"] ?? ""); ?>"
                required
            >

            <label>Email</label>
            <input
                type="email"
                name="email"
                placeholder="contoh@email.com"
                value="<?= htmlspecialchars($_POST["email"] ?? ""); ?>"
                required
            >

            <label>NIK</label>
            <input
                type="text"
                name="nik"
                inputmode="numeric"
                maxlength="16"
                minlength="16"
                pattern="[0-9]{16}"
                placeholder="Masukkan 16 digit NIK"
                value="<?= htmlspecialchars($_POST["nik"] ?? ""); ?>"
                required
            >

            <label>Username</label>
            <input
                type="text"
                name="username"
                value="<?= htmlspecialchars($_POST["username"] ?? ""); ?>"
                minlength="4"
                required
            >

            <label>Password</label>
            <input
                type="password"
                name="password"
                minlength="8"
                required
            >

            <label>Konfirmasi Password</label>
            <input
                type="password"
                name="konfirmasi"
                minlength="8"
                required
            >

            <label>Nomor HP</label>
            <input
                type="tel"
                name="no_hp"
                value="<?= htmlspecialchars($_POST["no_hp"] ?? ""); ?>"
                required
            >

            <label>Alamat</label>
            <textarea name="alamat" rows="3" required><?= htmlspecialchars($_POST["alamat"] ?? ""); ?></textarea>

            <button type="submit">Daftar Sekarang</button>

        </form>

        <div class="register-login">
            Sudah punya akun?
            <a href="index.php">Login di sini</a>
        </div>

    </div>
</div>

</body>
</html>