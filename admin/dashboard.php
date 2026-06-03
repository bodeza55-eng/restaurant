<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* =======================
   DASHBOARD STATS
======================= */
$count_foods = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total FROM foods"
    )
)['total'];

$count_orders = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT COUNT(*) total FROM orders"
    )
)['total'];

$today_sales = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT SUM(total) total 
         FROM orders 
         WHERE DATE(created_at)=CURDATE() 
         AND status='served'"
    )
)['total'] ?? 0;

// รายได้รวมทั้งหมด
$total_sales = mysqli_fetch_assoc(
    mysqli_query(
        $conn,
        "SELECT SUM(total) total 
         FROM orders 
         WHERE status='served'"
    )
)['total'] ?? 0;

/* =======================
   BEST SELLER (TOP 5)
======================= */
$bestLabels = [];
$bestValues = [];

$best = mysqli_query(
    $conn,
    "
    SELECT f.food_name, SUM(oi.qty) total_qty
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    JOIN foods f ON oi.food_id = f.food_id
    WHERE o.status='served'
    GROUP BY oi.food_id
    ORDER BY total_qty DESC
    LIMIT 5
    "
);

while ($row = mysqli_fetch_assoc($best)) {

    $bestLabels[] = $row['food_name'];
    $bestValues[] = (int)$row['total_qty'];

}
?>
<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<title>Advanced Dashboard | MyFoodie</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

/* =======================
   COLOR VARIABLE
======================= */
:root{
    --primary:#38bdf8;
    --secondary:#0ea5e9;
    --dark:#0f172a;
    --glass:rgba(255,255,255,.04);
    --border:rgba(255,255,255,.12);
}

/* =======================
   BASE
======================= */
*{
    box-sizing:border-box;
}

body{
    margin:0;
    font-family:'Kanit',sans-serif;
    background:
        radial-gradient(1200px 600px at top left, #1e293b 0%, transparent 60%),
        radial-gradient(1000px 500px at bottom right, #020617 0%, transparent 65%),
        #020617;
    color:#f8fafc;
}

.container{
    max-width:1100px;
    margin:auto;
    padding:50px 20px;
}

/* =======================
   HEADER
======================= */
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:40px;
    padding:24px 32px;
    background:var(--glass);
    border:1px solid var(--border);
    border-radius:22px;
}

.header h1{
    margin:0;
    font-size:28px;
}

.badge{
    padding:6px 18px;
    border-radius:50px;
    background:rgba(56,189,248,.2);
    border:1px solid var(--primary);
}

/* =======================
   STATS
======================= */
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
    margin-bottom:40px;
}

.stat{
    background:var(--glass);
    border:1px solid var(--border);
    border-radius:22px;
    padding:26px;
}

.stat i{
    font-size:28px;
    color:var(--primary);
}

.stat .val{
    font-size:30px;
    font-weight:600;
}

.stat .label{
    opacity:.6;
}

/* =======================
   NAVIGATION
======================= */
.nav{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:24px;
    margin-bottom:50px;
}

.nav a{
    text-decoration:none;
    color:#fff;
    background:var(--glass);
    border:1px solid var(--border);
    border-radius:26px;
    padding:36px 20px;
    text-align:center;
    transition:.35s;
}

.nav a i{
    font-size:28px;
    margin-bottom:12px;
    color:var(--primary);
}

.nav a:hover{
    transform:translateY(-8px);
    box-shadow:0 20px 40px rgba(56,189,248,.25);
}

/* =======================
   CHART
======================= */
.chart-card{
    background:var(--glass);
    border:1px solid var(--border);
    border-radius:26px;
    padding:30px;
}

.chart-title{
    font-size:18px;
    margin-bottom:20px;
    display:flex;
    gap:10px;
    align-items:center;
}

/* =======================
   RESPONSIVE
======================= */
@media(max-width:900px){
    .nav{
        grid-template-columns:repeat(2,1fr);
    }
}

</style>
</head>

<body>

<div class="container">

<!-- HEADER -->
<div class="header">

    <h1>
        ยินดีต้อนรับ,
        <?=htmlspecialchars($_SESSION['admin'])?>
    </h1>

    <div class="badge">
        <i class="fas fa-user-shield"></i>
        ผู้ดูแลระบบ
    </div>

</div>

<!-- STATS -->
<div class="stats">

    <div class="stat">
        <i class="fas fa-hamburger"></i>
        <div class="val"><?=$count_foods?></div>
        <div class="label">เมนูทั้งหมด</div>
    </div>

    <div class="stat">
        <i class="fas fa-receipt"></i>
        <div class="val"><?=$count_orders?></div>
        <div class="label">ออเดอร์ทั้งหมด</div>
    </div>

    <div class="stat">
        <i class="fas fa-wallet"></i>
        <div class="val">฿<?=number_format($total_sales,2)?></div>
        <div class="label">รายได้รวมทั้งหมด</div>

    </div>

</div>

<!-- NAV -->
<div class="nav">

    <a href="food_manage.php">
        <i class="fas fa-utensils"></i>
        <div>จัดการอาหาร</div>
    </a>

    <a href="admin_manage.php">
        <i class="fas fa-users-cog"></i>
        <div>จัดการแอดมิน</div>
    </a>

    <a href="order_manage.php">
        <i class="fas fa-receipt"></i>
        <div>รายการสั่งซื้อ</div>
    </a>

    <a href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        <div>ออกจากระบบ</div>
    </a>

</div>

<!-- BEST SELLER CHART -->
<div class="chart-card">

    <div class="chart-title">
        <i class="fas fa-chart-bar"></i>
        เมนูขายดีที่สุด
    </div>

    <canvas id="bestChart" height="120"></canvas>

</div>

</div>

<script>

new Chart(
    document.getElementById('bestChart'),
    {
        type: 'bar',
        data: {
            labels: <?=json_encode($bestLabels)?>,
            datasets: [{
                data: <?=json_encode($bestValues)?>,
                backgroundColor: '#38bdf8',
                borderRadius: 12
            }]
        },
        options: {
            plugins:{
                legend:{display:false}
            },
            scales:{
                y:{
                    beginAtZero:true,
                    ticks:{color:'#e5e7eb'},
                    grid:{color:'rgba(255,255,255,.05)'}
                },
                x:{
                    ticks:{color:'#e5e7eb'},
                    grid:{display:false}
                }
            }
        }
    }
);

</script>

</body>
</html>
