<?php
session_start();
include "../config/db.php"; 

/* =========================
    PHP LOGIC
========================= */

if (isset($_POST['confirm_payment'])) {

    if (empty($_SESSION['cart'])) {
        header("Location: cart.php");
        exit();
    }

    /* ---------- ตรวจสอบอัปโหลดสลิป ---------- */
    if (!isset($_FILES['slip']) || $_FILES['slip']['error'] != 0) {
        echo "<script>alert('กรุณาอัปโหลดสลิปก่อนยืนยันการชำระเงิน');history.back();</script>";
        exit();
    }

    $ext = pathinfo($_FILES['slip']['name'], PATHINFO_EXTENSION);
    $allow = ['jpg','jpeg','png'];

    if (!in_array(strtolower($ext), $allow)) {
        echo "<script>alert('รองรับเฉพาะไฟล์ JPG, PNG');history.back();</script>";
        exit();
    }

    if (!is_dir("../uploads/slips")) {
        mkdir("../uploads/slips", 0777, true);
    }

    $slip_name = "slip_" . time() . "_" . rand(1000,9999) . "." . $ext;
    move_uploaded_file($_FILES['slip']['tmp_name'], "../uploads/slips/".$slip_name);

    /* ---------- คำนวณยอด ---------- */
    $total = 0;

    foreach ($_SESSION['cart'] as $item) {
        $food_id = (int)$item['food_id'];
        $qty = (int)$item['qty'];

        $q = mysqli_query($conn,"
            SELECT price, discount, status 
            FROM foods 
            WHERE food_id = $food_id
        ");
        $f = mysqli_fetch_assoc($q);

        if (!$f || $f['status'] === 'out') continue;

        $price = ($f['discount'] > 0)
            ? $f['price'] * (100 - $f['discount']) / 100
            : $f['price'];

        $total += $price * $qty;
    }

    /* ---------- สร้าง order ---------- */
    mysqli_query($conn,"
        INSERT INTO orders (total, status, slip_image)
        VALUES ($total, 'waiting_verify', '$slip_name')
    ");

    $order_id = mysqli_insert_id($conn);

    /* ---------- บันทึกรายการ ---------- */
    foreach ($_SESSION['cart'] as $item) {

        $food_id = (int)$item['food_id'];
        $qty = (int)$item['qty'];
        $note = mysqli_real_escape_string($conn, $item['note']);

        $q = mysqli_query($conn,"
            SELECT price, discount, status 
            FROM foods 
            WHERE food_id = $food_id
        ");
        $f = mysqli_fetch_assoc($q);

        if (!$f || $f['status'] === 'out') continue;

        $price = ($f['discount'] > 0)
            ? $f['price'] * (100 - $f['discount']) / 100
            : $f['price'];

        mysqli_query($conn,"
            INSERT INTO order_items (order_id, food_id, qty, note, price)
            VALUES ($order_id, $food_id, $qty, '$note', $price)
        ");
    }

    $_SESSION['last_order_id'] = $order_id;
    $_SESSION['order_time'] = date("d/m/Y H:i");
    unset($_SESSION['cart']);

    header("Location: thankyou.php");
    exit();
}

/* =========================
   แสดงผลหน้า Checkout
========================= */

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$total = 0;
foreach ($_SESSION['cart'] as $item) {

    $food_id = (int)$item['food_id'];
    $qty = (int)$item['qty'];

    $q = mysqli_query($conn,"
        SELECT price, discount, status 
        FROM foods 
        WHERE food_id = $food_id
    ");
    $f = mysqli_fetch_assoc($q);

    if (!$f || $f['status'] === 'out') continue;

    $price = ($f['discount'] > 0)
        ? $f['price'] * (100 - $f['discount']) / 100
        : $f['price'];

    $total += $price * $qty;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>ชำระเงิน - MyFoodie</title>
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
/* =========================
    CSS STYLES
========================= */            
        :root {
            --promptpay: #003764;
            --promptpay-light: #005696;
            --success: #2ed573;
            --secondary: #2f3542;
            --bg: #f8fafc;
            --shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        body {
            background-color: var(--bg);
            background-image: radial-gradient(#cbd5e1 0.5px, transparent 0.5px);
            background-size: 20px 20px;
            font-family: 'Kanit', sans-serif;
            margin: 0; padding: 20px;
            display: flex; justify-content: center; align-items: center; min-height: 100vh;
        }

        .checkout-card {
            background: white;
            width: 100%;
            max-width: 450px;
            border-radius: 35px;
            box-shadow: var(--shadow);
            overflow: hidden;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-header {
            background: linear-gradient(135deg, var(--promptpay), var(--promptpay-light));
            color: white;
            padding: 40px 30px;
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 20px;
            background: white;
            clip-path: polygon(0% 100%, 5% 0%, 10% 100%, 15% 0%, 20% 100%, 25% 0%, 30% 100%, 35% 0%, 40% 100%, 45% 0%, 50% 100%, 55% 0%, 60% 100%, 65% 0%, 70% 100%, 75% 0%, 80% 100%, 85% 0%, 90% 100%, 95% 0%, 100% 100%);
        }

        .total-box {
            background: #f1f5f9;
            margin: 40px 30px 20px;
            padding: 25px;
            border-radius: 24px;
            transition: transform 0.3s;
        }
        
        .total-box:hover { transform: scale(1.02); }

        .total-box .label { font-size: 14px; color: #64748b; letter-spacing: 1px; margin-bottom: 5px; }
        .total-box .amount { font-size: 42px; font-weight: 600; color: var(--promptpay); }
        .total-box .currency { font-size: 18px; margin-left: 5px; color: #64748b; }

        .qr-section {
            padding: 20px;
            position: relative;
        }

        .qr-wrapper {
            display: inline-block;
            padding: 15px;
            background: white;
            border: 2px solid #f1f5f9;
            border-radius: 25px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        }

        .qr-image {
            width: 220px;
            display: block;
            border-radius: 10px;
        }

        .instruction {
            padding: 10px 40px 30px;
            color: #475569;
            font-size: 15px;
            line-height: 1.6;
        }

        .btn-confirm {
            background: var(--success);
            color: white;
            border: none;
            width: 85%;
            padding: 20px;
            border-radius: 20px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 20px -5px rgba(46, 213, 115, 0.4);
        }

        .btn-confirm:hover {
            background: #26c167;
            transform: translateY(-5px);
            box-shadow: 0 15px 25px -5px rgba(46, 213, 115, 0.5);
        }

        .btn-confirm:active { transform: translateY(0); }

        .btn-back {
            display: inline-block;
            text-decoration: none;
            color: #94a3b8;
            font-size: 14px;
            margin-bottom: 35px;
            transition: color 0.2s;
        }
        .btn-back:hover { color: var(--secondary); }

        /* Scanning Animation Line */
        .qr-wrapper { position: relative; overflow: hidden; }
        .scan-line {
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: rgba(46, 213, 115, 0.5);
            box-shadow: 0 0 15px 2px var(--success);
            animation: scan 3s infinite linear;
        }
        @keyframes scan {
            0% { top: 0; }
            50% { top: 100%; }
            100% { top: 0; }
        }
        .upload-box{
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:10px;
    border:2px dashed #cbd5e1;
    padding:25px;
    margin:0 40px 20px;
    border-radius:18px;
    cursor:pointer;
    color:#64748b;
    transition:0.3s;
}

.upload-box:hover{
    background:#f1f5f9;
    color:#003764;
}

.upload-box input{
    display:none;
}

#preview{
    max-width:200px;
    margin:15px auto;
    display:none;
    border-radius:12px;
    box-shadow:0 8px 15px rgba(0,0,0,0.15);
}

    </style>
</head>
<body>

<div class="checkout-card">

    <!-- Header -->
    <div class="card-header">
        <img src="https://upload.wikimedia.org/wikipedia/commons/c/c5/PromptPay-logo.png"
             height="40" style="filter: brightness(0) invert(1);">
        <h2>Thai QR Payment</h2>
    </div>

    <!-- Total -->
    <div class="total-box">
        <div class="label">ยอดชำระที่ต้องโอน</div>
        <div class="amount">
            <?= number_format($total,2) ?>
            <span class="currency">บาท</span>
        </div>
    </div>

    <!-- QR -->
    <div class="qr-section">
        <div class="qr-wrapper">
            <div class="scan-line"></div>
            <img src="../assets/qr/QR_Payment.jpg" class="qr-image">
        </div>
    </div>

    <!-- Instruction -->
    <div class="instruction">
        สแกน QR Code ด้วยแอปธนาคาร <br>
        <b>อัปโหลดสลิป และกดยืนยัน</b>
    </div>

    <!-- Form -->
    <form method="post" enctype="multipart/form-data">

        <label class="upload-box">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            เลือกรูปสลิป
            <input type="file" name="slip" required onchange="previewSlip(this)">
        </label>

        <img id="preview">

        <button type="submit" name="confirm_payment" class="btn-confirm">
            <i class="fa-solid fa-check-double"></i> ยืนยันการชำระเงิน
        </button>

    </form>

    <!-- Back -->
    <a href="cart.php" class="btn-back">
        <i class="fa-solid fa-arrow-left"></i> ย้อนกลับไปแก้ไขตะกร้า
    </a>

</div>

<script>
document.querySelector('form').addEventListener('submit', function() {
    const btn = this.querySelector('button');
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> กำลังดำเนินการ...';
    btn.style.pointerEvents = 'none';
});

function previewSlip(input){
    const img = document.getElementById('preview');
    img.src = URL.createObjectURL(input.files[0]);
    img.style.display = 'block';
}
</script>

</body>
</html>