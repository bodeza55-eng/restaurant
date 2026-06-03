<?php
require_once __DIR__ . '/../config/db.php';
// ตรวจสอบว่าการเชื่อมต่อฐานข้อมูลถูกตั้งค่า
if (!isset($conn) || !$conn) {
    die('Database connection not available. Check config/db.php');
}
/* =========================
    PHP LOGIC
========================= */    
/* =========================
   ACTIONS
========================= */

if (isset($_GET['serve'])) {

    $id = intval($_GET['serve']);
    mysqli_query(
        $conn,
        "UPDATE orders SET status='served' WHERE order_id=$id"
    );

    header("Location: order_manage.php");
    exit;

}

if (isset($_GET['clear'])) {

    mysqli_query(
        $conn,
        "
        DELETE FROM order_items 
        WHERE order_id IN (SELECT order_id FROM orders WHERE status='served')
        "
    );

    mysqli_query(
        $conn,
        "DELETE FROM orders WHERE status='served'"
    );

    header("Location: order_manage.php");
    exit;

}

// ลบคำสั่งซื้อเฉพาะรายการ
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    // ลบ order items ก่อน
    mysqli_query(
        $conn,
        "DELETE FROM order_items WHERE order_id=$id"
    );

    // ลบ order
    mysqli_query(
        $conn,
        "DELETE FROM orders WHERE order_id=$id"
    );

    header("Location: order_manage.php");
    exit;

}

/* =========================
   STATS
========================= */

$res = mysqli_query(
    $conn,
    "SELECT SUM(total) AS sum FROM orders WHERE status='served'"
);
$row = mysqli_fetch_assoc($res);
$total_income = $row['sum'] ?? 0;

$res = mysqli_query(
    $conn,
    "SELECT COUNT(*) c FROM orders WHERE status='pending'"
);
$row = mysqli_fetch_assoc($res);
$pending = $row['c'] ?? 0;

$res = mysqli_query(
    $conn,
    "SELECT COUNT(*) c FROM orders WHERE status='served'"
);
$row = mysqli_fetch_assoc($res);
$served = $row['c'] ?? 0;

$orders = mysqli_query(
    $conn,
    "SELECT * FROM orders ORDER BY created_at DESC"
);
?>

<!DOCTYPE html>
<html lang="th">

<head>

    <meta charset="UTF-8">
    <title>Order Management | MyFoodie</title>

    <link rel="stylesheet" href="../assets/style.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600;700&display=swap"
          rel="stylesheet">

    <style>
/* =========================
    CSS STYLES
========================= */  
        .status.waiting_verify{
            background:#f59e0b22;
            color:#facc15;
        }        
        :root {
            --glass: rgba(255,255,255,.05);
            --border: rgba(255,255,255,.12);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        * {
            box-sizing: border-box;
        }

        /* =========================
           CONTAINER
        ========================== */

        .container {
            max-width: 1400px;
            margin: auto;
            padding: 40px 20px;
        }

        /* =========================
           HEADER
        ========================== */

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 32px;
            display: flex;
            gap: 12px;
        }

        /* =========================
           STATS
        ========================== */

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat {
            padding: 30px;
            border-radius: 22px;
            background: var(--glass);
            border: 1px solid var(--border);
        }

        .stat .icon {
            font-size: 36px;
        }

        .stat .label {
            color: #94a3b8;
            font-size: 14px;
            margin-top: 6px;
        }

        .stat .value {
            font-size: 30px;
            font-weight: 700;
            margin-top: 10px;
        }

        .income { color: var(--success); }
        .pending { color: var(--warning); }
        .served  { color: var(--success); }

        /* =========================
           ACTIONS
        ========================== */

        .actions {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .clear-btn {
            display: flex;
            align-items: center;
            gap: 8px;

            padding: 12px 18px;
            border-radius: 14px;

            color: #ef4444;
            background: rgba(239,68,68,.15);
            border: 1px solid rgba(239,68,68,.35);

            text-decoration: none;
            font-weight: 600;

            transition: .25s;
        }

        .clear-btn:hover {
            color: #fff;
            background: #ef4444;
            box-shadow: 0 0 25px rgba(239,68,68,.6);
            transform: translateY(-2px);
        }

        /* =========================
           TABLE
        ========================== */

        .card {
            background: var(--glass);
            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            padding: 18px;
            background: rgba(255,255,255,.06);
            font-size: 13px;
            color: #94a3b8;
            text-align: left;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid var(--border);
        }

        .order-id {
            color: #a5b4fc;
            font-family: monospace;
        }

        .status {
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }

        .status.pending {
            background: #f59e0b22;
            color: #facc15;
        }

        .status.served {
            background: #10b98122;
            color: #6ee7b7;
        }

        .btn {
            background: var(--success);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
        }
        .btn {
            background: var(--success);
            color: #fff;
            border: none;
            padding: 10px 18px;
            border-radius: 12px;
        cursor: pointer;
}
        .btn-delete {
            background: transparent;
            color: #ef4444;
            border: 1px solid rgba(239,68,68,.18);
            padding: 8px 12px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
        }
        .btn-delete:hover {
            background: rgba(239,68,68,.12);
            color: #fff;
            border-color: rgba(239,68,68,.35);
        }
        .items {
            background: rgba(255,255,255,.04);
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 8px;
        }

    </style>

</head>

<body>

<div class="page-container">

    <div class="page-header"></div>

    <div class="page-container">

        <div class="order-table"></div>

        <div class="page-header">
            <h1>🍽️ Order Management</h1>

            <a href="dashboard.php" class="back">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>

        <div class="stats">

            <div class="stat">
                <div class="icon">💰</div>
                <div class="label">รายได้รวม (เสิร์ฟแล้ว)</div>
                <div class="value income" data-count data-value="<?=$total_income?>">0.00</div>
            </div>

            <div class="stat">
                <div class="icon">⏳</div>
                <div class="label">รอเสิร์ฟ</div>
                <div class="value pending"><?=$pending?></div>
            </div>

            <div class="stat">
                <div class="icon">✅</div>
                <div class="label">เสิร์ฟแล้ว</div>
                <div class="value served"><?=$served?></div>
            </div>

        </div>

        <div class="actions">

            <a href="?clear=1"
               class="clear-btn"
               onclick="return confirm('⚠️ การล้างออเดอร์จะลบข้อมูลถาวร\nคุณแน่ใจหรือไม่?')">

                <i class="fas fa-trash"></i>
                ล้างออเดอร์ที่เสิร์ฟแล้ว

            </a>

        </div>

        <div class="card">

            <table>

                <thead>
                    <tr>
                        <th>ORDER</th>
                        <th>เวลา</th>
                        <th>เมนู</th>
                        <th>รวม</th>
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>

                <tbody id="orderBody"></tbody>

            </table>

        </div>

    </div>

</div>

<script>
/* =========================
    JAVASCRIPT LOGIC
========================= */    
/* COUNT UP */

document.querySelectorAll('[data-count]').forEach(el => {

    let target = parseFloat(el.dataset.value) || 0,
        start  = 0;

    const step = target / 60;

    const run = () => {

        start += step;

        if (start < target) {

            el.innerText =
                start.toLocaleString(undefined,{minimumFractionDigits:2});

            requestAnimationFrame(run);

        } else {

            el.innerText =
                target.toLocaleString(undefined,{minimumFractionDigits:2});

        }

    };

    run();

});

/* ROW HOVER */

document.querySelectorAll('tbody tr').forEach(r => {

    r.onmouseenter = () =>
        r.style.background = 'rgba(255,255,255,.05)';

    r.onmouseleave = () =>
        r.style.background = '';

});

function loadOrders() {

    fetch("get_orders.php")
        .then(res => res.text())
        .then(html => {
            document.getElementById("orderBody").innerHTML = html;
            attachDeleteButtons();
        });

}

function attachDeleteButtons(){
    document.querySelectorAll('.btn-delete[data-order-id]').forEach(btn => {
        btn.onclick = async (e) => {
            const id = btn.dataset.orderId;
            if(!confirm(`ต้องการลบคำสั่งซื้อ #${String(id).padStart(4,'0')} ?`)) return;

            try{
                const res = await fetch('delete_order.php', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify({order_id: id})
                });

                const data = await res.json();

                if(data.success){
                    // เอาแถวออกจากตาราง
                    const tr = btn.closest('tr');
                    if(tr) tr.remove();
                } else {
                    alert('ไม่สามารถลบคำสั่งซื้อได้: ' + (data.error||'unknown'));
                }

            } catch(err){
                alert('เกิดข้อผิดพลาดขณะลบคำสั่งซื้อ');
                console.error(err);
            }
        };
    });
}

// โหลดทันทีตอนเปิดหน้า
loadOrders();

// โหลดซ้ำทุก 3 วินาที
setInterval(loadOrders, 3000);

</script>

</body>
</html>
