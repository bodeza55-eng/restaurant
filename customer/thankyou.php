<?php
session_start();
/* =========================
    PHP LOGIC
========================= */    
$order_id = $_SESSION['last_order_id'] ?? null;
$order_time = $_SESSION['order_time'] ?? null;

// กันเปิดหน้านี้ตรง ๆ (Logic เดิม)
if (!$order_id) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งอาหารสำเร็จ - MyFoodie</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
/* =========================
    CSS STYLES
========================= */            
        :root {
            --success: #2ed573;
            --primary: #3498db;
            --bg: #f7f9fc;
            --text-main: #2f3542;
            --text-muted: #a4b0be;
        }

        body {
            background-color: var(--bg);
            font-family: 'Kanit', sans-serif;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .thankyou-card {
            background: white;
            width: 100%;
            max-width: 480px;
            border-radius: 35px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: popIn 0.5s cubic-bezier(0.26, 0.53, 0.74, 1.48);
        }

        @keyframes popIn {
            0% { transform: scale(0.9); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Success Icon Animation */
        .success-checkmark {
            width: 100px;
            height: 100px;
            background: rgba(46, 213, 115, 0.1);
            color: var(--success);
            font-size: 50px;
            line-height: 100px;
            border-radius: 50%;
            margin: 0 auto 25px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-10px);}
            60% {transform: translateY(-5px);}
        }

        h1 {
            color: var(--text-main);
            font-size: 28px;
            margin: 0 0 10px;
        }

        .subtitle {
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        /* Order Info Box */
        .order-receipt {
            background: #f8fafc;
            border: 2px dashed #e2e8f0;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            text-align: left;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 15px;
        }

        .receipt-row:last-child { margin-bottom: 0; }

        .receipt-label { color: var(--text-muted); }
        .receipt-value { color: var(--text-main); font-weight: 500; }

        .order-id-badge {
            background: var(--text-main);
            color: white;
            padding: 2px 12px;
            border-radius: 50px;
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #f39c12;
            font-weight: 600;
        }

        /* Pulsing Dot for Status */
        .dot {
            height: 8px;
            width: 8px;
            background-color: #f39c12;
            border-radius: 50%;
            display: inline-block;
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(243, 156, 18, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(243, 156, 18, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(243, 156, 18, 0); }
        }

        .notice-text {
            background: #fff9eb;
            color: #d4a017;
            padding: 15px;
            border-radius: 12px;
            font-size: 14px;
            margin-bottom: 25px;
        }

        /* Buttons */
        .btn-home {
            background: var(--primary);
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 18px;
            border-radius: 18px;
            font-size: 17px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(52, 152, 219, 0.2);
        }

        .btn-home:hover {
            background: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(52, 152, 219, 0.3);
        }

        /* Confetti Effect */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--success);
            top: -10px;
            opacity: 0.7;
        }
    </style>
</head>
<body>

<div class="thankyou-card">
    <div class="success-checkmark">
        <i class="fa-solid fa-utensils"></i>
    </div>
    
    <h1>สั่งอาหารสำเร็จ!</h1>
    <p class="subtitle">เราได้รับออเดอร์แสนอร่อยของคุณแล้ว</p>

    <div class="order-receipt">
        <div class="receipt-row">
            <span class="receipt-label">หมายเลขออเดอร์</span>
            <span class="order-id-badge">#<?= $order_id ?></span>
        </div>
        <div class="receipt-row">
            <span class="receipt-label">เวลาที่ทำรายการ</span>
            <span class="receipt-value"><?= $order_time ?></span>
        </div>
        <div class="receipt-row">
            <span class="receipt-label">สถานะปัจจุบัน</span>
            <span class="receipt-value">
                <span class="status-pill">
                    <span class="dot"></span> กำลังเตรียมอาหาร
                </span>
            </span>
        </div>
    </div>

    <div class="notice-text">
        <i class="fa-solid fa-circle-exclamation"></i>
        กรุณาแสดงหน้านี้ให้พนักงานเมื่อคุณได้รับอาหาร
    </div>

    <a href="index.php" class="btn-home">
        <i class="fa-solid fa-house"></i> กลับหน้าเมนูหลัก
    </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
/* =========================
    JAVASCRIPT LOGIC
========================= */        
    // ยิงพลุฉลองตอนหน้าโหลดเสร็จ
    var duration = 3 * 1000;
    var animationEnd = Date.now() + duration;
    var defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

    function randomInRange(min, max) {
      return Math.random() * (max - min) + min;
    }

    var interval = setInterval(function() {
      var timeLeft = animationEnd - Date.now();

      if (timeLeft <= 0) {
        return clearInterval(interval);
      }

      var particleCount = 50 * (timeLeft / duration);
      confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 } }));
      confetti(Object.assign({}, defaults, { particleCount, origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 } }));
    }, 250);
</script>

</body>
</html>