
<?php
session_start();

if (!isset($_SESSION["pending_user_id"])) {
    header("Location: ../index.php");
    exit;
}

$expired = time() >= ($_SESSION["otp_expiry"] ?? 0);

$remaining = $expired
    ? 0
    : max(0, (int)($_SESSION["otp_expiry"] - time()));

$error = $_GET["error"] ?? "";
$sent = isset($_GET["sent"]);
$resent = isset($_GET["resent"]);

$cooldown = max(
    0,
    (int)(($_SESSION["otp_resend_after"] ?? 0) - time())
);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Verifikasi OTP - Bank Iman Syariah</title>

    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .otp-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            margin: 15px 0;
            background: #fff1f0;
            border: 1px solid #f5c2c0;
            border-radius: 10px;
            color: #b42318;
            font-size: 13px;
            line-height: 1.5;
        }

        .otp-alert.success {
            background: #e9f7ef;
            border-color: #b9e3cc;
            color: #08764f;
        }

        .otp-alert-icon {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.2;
        }

        .otp-alert strong {
            display: block;
            margin-bottom: 2px;
        }

        .otp-resend {
            width: 100%;
            margin-top: 12px;
            padding: 11px;
            border: 1px solid #087957;
            background: #fff;
            color: #087957;
            border-radius: 9px;
            font-weight: 600;
            cursor: pointer;
        }

        .otp-resend:disabled {
            color: #87958e;
            border-color: #d7e2dc;
            background: #f5f8f6;
            cursor: not-allowed;
        }

        .otp-input {
            text-align: center;
            letter-spacing: 8px;
            font-weight: 700;
            font-size: 19px;
        }

        .otp-input:disabled {
            background: #f3f5f4;
            cursor: not-allowed;
        }

        .otp-timer.expired {
            background: #fff1f0;
        }

        .otp-timer.expired h2 {
            color: #b42318 !important;
        }

        /* NOTIFIKASI OTP */
        .otp-toast {
            position: fixed;
            top: 25px;
            right: 25px;
            z-index: 99999;

            width: 360px;
            max-width: calc(100vw - 32px);

            display: flex;
            align-items: flex-start;
            gap: 12px;

            padding: 18px;
            background: #ffffff;

            border-left: 5px solid #07845c;
            border-radius: 14px;

            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.16);

            animation: otpNotificationIn 0.35s ease;
        }

        .otp-toast-content {
            flex: 1;
        }

        .otp-toast-content strong {
            display: block;
            color: #174b3c;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .otp-toast-content p {
            font-size: 12px;
            color: #6b7280;
            margin: 0 0 10px;
        }

        .otp-toast-code {
            background: #e8f5ed;
            border-radius: 9px;
            padding: 12px;

            text-align: center;
            letter-spacing: 7px;

            color: #087a50;
            font-size: 23px;
            font-weight: 800;
        }

        .otp-toast-content small {
            display: block;
            margin-top: 8px;
            color: #89948e;
            font-size: 11px;
        }

        .otp-toast-close {
            border: none;
            background: transparent;
            color: #89948e;
            font-size: 22px;
            cursor: pointer;
            padding: 0;
        }

        @keyframes otpNotificationIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media(max-width:480px) {
            .otp-toast {
                right: 16px;
                left: 16px;
                top: 16px;
                width: auto;
            }
        }
    </style>
</head>

<body class="login-page otp-page">

<div class="container">

    <div class="left-panel">

        <div class="brand">
            <img
                src="../assets/images/moon.svg"
                alt="Bank Iman Syariah"
                class="logo-bank"
            >

            <h2>Bank Iman Syariah</h2>
        </div>

        <div class="welcome">
            <h1>
                Bertransaksi dengan tenang,
                sesuai prinsip syariah.
            </h1>

            <p>
                Kelola rekening, pembiayaan, dan zakat Anda
                dalam satu tempat yang aman dan transparan.
            </p>
        </div>

        <div class="features">
            <p>Akad jelas: Wadiah, Mudharabah, dan Murabahah</p>
            <p>Margin dan biaya ditampilkan transparan</p>
            <p>Tunaikan zakat langsung dari aplikasi</p>
        </div>

        <footer>© 2026 Bank Iman Syariah</footer>

    </div>

    <div class="right-panel">

        <div class="login-box otp-card">

            <div class="otp-icon">✓</div>

            <h2>Verifikasi OTP</h2>

            <p class="otp-description">
                Masukkan kode verifikasi 6 digit untuk melanjutkan.
            </p>

            <?php if ($expired): ?>

                <div class="otp-alert" role="alert">
                    <span class="otp-alert-icon">!</span>

                    <div>
                        <strong>Kode OTP Kedaluwarsa</strong>

                        Kode sudah tidak berlaku.
                        Kirim ulang kode untuk melanjutkan verifikasi.
                    </div>
                </div>

            <?php elseif ($error === "1"): ?>

                <div class="otp-alert" role="alert">
                    <span class="otp-alert-icon">!</span>

                    <div>
                        <strong>Verifikasi Gagal</strong>

                        Kode OTP tidak valid.
                        Periksa kembali 6 digit kode yang dimasukkan.
                    </div>
                </div>

            <?php endif; ?>

            <div class="otp-timer <?= $expired ? 'expired' : '' ?>">

                <p>Sisa waktu verifikasi</p>

                <h2 id="countdown">
                    <?= $expired
                        ? '00:00'
                        : sprintf(
                            '%02d:%02d',
                            floor($remaining / 60),
                            $remaining % 60
                        )
                    ?>
                </h2>

            </div>

            <?php if (!$expired): ?>

                <form
                    action="verify_otp.php"
                    method="POST"
                    id="otpForm"
                >

                    <label for="otpInput">Kode OTP</label>

                    <input
                        id="otpInput"
                        type="text"
                        name="otp"
                        class="otp-input"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        inputmode="numeric"
                        autocomplete="one-time-code"
                        placeholder="Masukkan 6 digit OTP"
                        required
                    >

                    <button
                        type="submit"
                        id="verifyButton"
                        disabled
                    >
                        Verifikasi
                    </button>

                </form>

            <?php endif; ?>

            <form
                action="resend_otp.php"
                method="POST"
                id="resendForm"
            >

                <button
                    class="otp-resend"
                    id="resendButton"
                    type="submit"
                    <?= $cooldown > 0 ? 'disabled' : '' ?>
                >
                    <?= $cooldown > 0
                        ? 'Kirim ulang dalam '.$cooldown.' detik'
                        : 'Kirim Ulang OTP'
                    ?>
                </button>

            </form>

        </div>

    </div>

</div>

<script>

/* NOTIFIKASI OTP */
function toastOTP(message, code) {

    const node = document.createElement('div');

    node.className = 'otp-toast';

    node.innerHTML = `
        <div class="otp-toast-content">
            <strong></strong>
            <p></p>
            <div class="otp-toast-code"></div>
            <small>Kode berlaku selama 5 menit</small>
        </div>

        <button class="otp-toast-close" type="button">
            &times;
        </button>
    `;

    node.querySelector('strong').textContent = message;

    node.querySelector('p').textContent =
        'Gunakan kode berikut untuk verifikasi login:';

    node.querySelector('.otp-toast-code').textContent = code;

    node.querySelector('.otp-toast-close').onclick = () => {
        node.remove();
    };

    document.body.appendChild(node);

    setTimeout(() => {
        node.remove();
    }, 8000);
}


/* OTP PERTAMA */
<?php if ($sent): ?>

toastOTP(
    'Kode OTP berhasil dibuat',
    <?= json_encode($_SESSION["demo_otp"] ?? "") ?>
);

<?php elseif ($resent): ?>

toastOTP(
    'Kode OTP baru berhasil dibuat',
    <?= json_encode($_SESSION["demo_otp"] ?? "") ?>
);

<?php endif; ?>


/* INPUT OTP HANYA ANGKA */
const otpInput = document.getElementById('otpInput');
const verifyButton = document.getElementById('verifyButton');

if (otpInput && verifyButton) {

    otpInput.addEventListener('input', function () {

        otpInput.value = otpInput.value
            .replace(/\D/g, '')
            .slice(0, 6);

        verifyButton.disabled =
            otpInput.value.length !== 6;

    });

}


/* TIMER OTP */
let remaining = <?= (int)$remaining ?>;

const countdown = document.getElementById('countdown');
const timerBox = document.querySelector('.otp-timer');

function updateTimer() {

    if (countdown) {

        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;

        countdown.textContent =
            String(minutes).padStart(2, '0') +
            ':' +
            String(seconds).padStart(2, '0');

    }

    if (remaining <= 0) {

        if (countdown) {
            countdown.textContent = '00:00';
        }

        if (timerBox) {
            timerBox.classList.add('expired');
        }

        if (otpInput) {
            otpInput.disabled = true;
        }

        if (verifyButton) {
            verifyButton.disabled = true;
        }

        return;
    }

    if (remaining <= 60 && countdown) {
        countdown.style.color = '#b42318';
    }

    remaining--;

    setTimeout(updateTimer, 1000);
}

if (remaining > 0) {
    updateTimer();
}


/* COOLDOWN KIRIM ULANG OTP */
let resendCooldown = <?= (int)$cooldown ?>;

const resendButton = document.getElementById('resendButton');

function updateResend() {

    if (!resendButton) {
        return;
    }

    if (resendCooldown > 0) {

        resendButton.disabled = true;

        resendButton.textContent =
            'Kirim ulang dalam ' +
            resendCooldown +
            ' detik';

        resendCooldown--;

        setTimeout(updateResend, 1000);

    } else {

        resendButton.disabled = false;

        resendButton.textContent =
            'Kirim Ulang OTP';

    }

}

if (resendCooldown > 0) {
    updateResend();
}

</script>

</body>
</html>
