<?php
session_start();

include("../config/db.php");

if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']);

    $sql = "SELECT * FROM admins WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $_SESSION['admin'] = $username;
        header("Location: dashboard.php");
        exit();

    } else {

        $error = "เข้าสู่ระบบไม่สำเร็จ กรุณาตรวจสอบข้อมูล";

    }

}
?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Authentication | MyFoodie</title>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Animate CSS -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <style>

        /* =========================
           VARIABLES
        ========================== */

        :root {
            --glass-bg: rgba(255, 255, 255, 0.15);
            --glass-border: rgba(255, 255, 255, 0.2);
            --accent: #00f2fe;
            --accent-2: #4facfe;
        }

        /* =========================
           BODY
        ========================== */

        body {
            margin: 0;
            padding: 0;
            font-family: 'Kanit', sans-serif;
            height: 100vh;

            display: flex;
            justify-content: center;
            align-items: center;

            background: #0f172a;
            overflow: hidden;
        }

        /* =========================
           BACKGROUND BLOBS
        ========================== */

        .background-blobs {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: -1;

            background: linear-gradient(45deg, #0f172a, #1e293b);
        }

        .blob {
            position: absolute;

            width: 300px;
            height: 300px;

            background: linear-gradient(to right, var(--accent), var(--accent-2));
            filter: blur(80px);
            border-radius: 50%;
            opacity: 0.4;

            animation: move 20s infinite alternate;
        }

        @keyframes move {
            from {
                transform: translate(-10%, -10%);
            }
            to {
                transform: translate(100%, 80%);
            }
        }

        /* =========================
           LOGIN CONTAINER
        ========================== */

        .login-container {
            width: 400px;
            padding: 40px;

            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);

            border-radius: 24px;
            border: 1px solid var(--glass-border);

            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);

            color: white;
            z-index: 10;
        }

        .brand-logo {
            font-size: 50px;
            margin-bottom: 10px;

            background: linear-gradient(to bottom right, #fff, #94a3b8);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;

            text-align: center;
        }

        h2 {
            text-align: center;
            font-weight: 600;
            margin-bottom: 30px;

            letter-spacing: 1px;
            text-transform: uppercase;
        }

        /* =========================
           INPUT
        ========================== */

        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;

            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);

            transition: 0.3s;
        }

        input {
            width: 100%;
            padding: 14px 15px 14px 45px;

            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 12px;

            color: white;
            font-size: 16px;
            outline: none;

            transition: 0.3s;
            box-sizing: border-box;
        }

        input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent);
            box-shadow: 0 0 15px rgba(0, 242, 254, 0.3);
        }

        input:focus + i {
            color: var(--accent);
        }

        /* =========================
           BUTTON
        ========================== */
        .forgot-box{
            text-align: center;
            margin-top: 18px;
        }
        
        .forgot-link{
            color: #60a5fa;
            font-size: 14px;
            text-decoration: none;
        
            display: inline-flex;
            align-items: center;
            gap: 6px;
        
            padding: 6px 14px;
            border-radius: 999px;
        
            transition: 0.3s ease;
        }
        
        .forgot-link:hover{
            color: #00f2fe;
            background: rgba(255,255,255,0.08);
            box-shadow: 0 0 10px rgba(0,242,254,0.4);
        }        
        button {
            width: 100%;
            padding: 14px;

            border: none;
            border-radius: 12px;

            background: linear-gradient(to right, #4facfe 0%, #00f2fe 100%);
            color: #0f172a;

            font-size: 18px;
            font-weight: 600;

            cursor: pointer;
            transition: 0.4s;

            box-shadow: 0 10px 15px -3px rgba(0, 242, 254, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 242, 254, 0.4);
            filter: brightness(1.1);
        }

        button:active {
            transform: scale(0.98);
        }

        /* =========================
           ERROR MESSAGE
        ========================== */

        .error-msg {
            background: rgba(239, 68, 68, 0.2);
            border-left: 4px solid #ef4444;

            color: #fecaca;
            padding: 12px;
            border-radius: 8px;

            margin-bottom: 20px;
            font-size: 14px;

            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* =========================
           FOOTER
        ========================== */

        .footer {
            margin-top: 30px;
            text-align: center;

            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 300;
        }

        /* =========================
           ANIMATION
        ========================== */

        .shake {
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

    </style>

</head>

<body>

    <!-- Background -->
    <div class="background-blobs">

        <div class="blob"></div>

        <div class="blob"
             style="right: 10%; top: 20%; animation-delay: -5s; width: 400px; height: 400px; background: #764ba2;">
        </div>

    </div>

    <!-- Login Box -->
    <div class="login-container animate__animated animate__fadeInDown">

        <div class="brand-logo">
            <i class="fas fa-utensils"></i>
        </div>

        <h2>Admin Access</h2>

        <?php if (isset($error)): ?>

            <div class="error-msg animate__animated animate__headShake">
                <i class="fas fa-exclamation-circle"></i>
                <?= $error ?>
            </div>

        <?php endif; ?>

        <form method="post" id="loginForm">

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text"
                       name="username"
                       placeholder="Username"
                       required
                       autocomplete="off">
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password"
                       name="password"
                       id="password"
                       placeholder="Password"
                       required>

                <i class="fas fa-eye"
                   id="togglePass"
                   style="left: auto; right: 15px; cursor: pointer;">
                </i>
            </div>

            <button type="submit"
                    name="login"
                    id="loginBtn">

                SIGN IN
                <i class="fas fa-arrow-right" style="margin-left: 8px;"></i>

            </button>
            <div style="text-align:center;margin-top:15px;">
        <a href="forgot_password.php"
       style="color:#60a5fa;text-decoration:none;font-size:14px;">
        ลืมรหัสผ่าน?
        </a>
            </div>

        </form>

        <div class="footer">
            &copy; ระบบร้านอาหาร <i class="fas fa-shield-alt"></i>
        </div>

    </div>

    <!-- SCRIPT -->
    <script>

        // Toggle Password
        const togglePass = document.querySelector('#togglePass');
        const passwordInput = document.querySelector('#password');

        togglePass.addEventListener('click', function () {

            const type =
                passwordInput.getAttribute('type') === 'password'
                    ? 'text'
                    : 'password';

            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');

        });

        // Button Loading
        const loginForm = document.querySelector('#loginForm');
        const loginBtn  = document.querySelector('#loginBtn');

        loginForm.addEventListener('submit', function () {

            loginBtn.innerHTML =
                '<i class="fas fa-circle-notch fa-spin"></i> Authenticating...';

            loginBtn.style.pointerEvents = 'none';
            loginBtn.style.opacity = '0.8';

        });

        // Blob Mouse Effect
        document.addEventListener('mousemove', (e) => {

            const moveX = (e.clientX * 0.05) / 8;
            const moveY = (e.clientY * 0.05) / 8;

            document.querySelectorAll('.blob').forEach(blob => {

                blob.style.transform =
                    `translate(${moveX}px, ${moveY}px)`;

            });

        });

    </script>

</body>
</html>
