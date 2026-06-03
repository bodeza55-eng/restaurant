<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   ADD FOOD
========================= */
if (isset($_POST['add_food'])) {

    $name  = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $image = mysqli_real_escape_string($conn, $_POST['image']);
    $discount = intval($_POST['discount']);

    if($discount < 0) $discount = 0;
    if($discount > 100) $discount = 100;

    mysqli_query($conn,
        "INSERT INTO foods (food_name,image,price,discount,status)
         VALUES ('$name','$image','$price','$discount','available')"
    );

    header("Location: food_manage.php");
    exit();
}

/* =========================
   UPDATE DISCOUNT
========================= */
if (isset($_POST['update_discount'])) {

    $id = $_POST['food_id'];
    $discount = intval($_POST['discount']);

    if($discount < 0) $discount = 0;
    if($discount > 100) $discount = 100;

    mysqli_query($conn,
        "UPDATE foods SET discount=$discount WHERE food_id=$id"
    );

    header("Location: food_manage.php");
    exit();
}

/* =========================
   TOGGLE STATUS
========================= */
if (isset($_GET['toggle_status'])) {

    $id = $_GET['toggle_status'];

    $q = mysqli_query($conn,
        "SELECT status FROM foods WHERE food_id=$id"
    );

    $row = mysqli_fetch_assoc($q);

    $newStatus = ($row['status']=='available')
        ? 'out'
        : 'available';

    mysqli_query($conn,
        "UPDATE foods SET status='$newStatus' WHERE food_id=$id"
    );

    header("Location: food_manage.php");
    exit();
}

/* =========================
   DELETE FOOD
========================= */
if (isset($_GET['delete_food'])) {

    $id = $_GET['delete_food'];

    mysqli_query($conn,
        "DELETE FROM foods WHERE food_id=$id"
    );

    header("Location: food_manage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Menu Management | MyFoodie</title>

<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

/* =========================
   DASHBOARD BUTTON
========================= */
.dashboard-btn{
    display:inline-flex;
    align-items:center;
    gap:8px;
    color:#fff;
    text-decoration:none;
    padding:12px 22px;
    background:rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.15);
    border-radius:14px;
    font-weight:600;
    transition:.25s;
}

.dashboard-btn:hover{
    color:#38bdf8;
    border-color:#38bdf8;
    box-shadow:0 0 20px rgba(56,189,248,.45);
    transform:translateY(-2px);
}

/* =========================
   COLOR VARIABLE
========================= */
:root{
    --bg-body:#0b1120;
    --bg-card:#1e293b;
    --accent:#6366f1;
    --accent-light:#818cf8;
    --text-main:#f1f5f9;
    --text-muted:#94a3b8;
    --danger:#ef4444;
    --success:#22c55e;
}

/* =========================
   BASE
========================= */
body{
    background-color:var(--bg-body);
    color:var(--text-main);
    font-family:'Kanit',sans-serif;
    margin:0;
}

/* =========================
   LAYOUT
========================= */
.wrapper{
    max-width:1100px;
    margin:0 auto;
    padding:70px 20px 40px;
}

/* =========================
   HEADER
========================= */
.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:30px;
}

.page-header h1{
    font-size:28px;
    margin:0;
}

/* =========================
   CARD
========================= */
.add-card,
.table-card{
    background:var(--bg-card);
    border-radius:24px;
    padding:30px;
    border:1px solid rgba(255,255,255,0.05);
    box-shadow:0 20px 40px rgba(0,0,0,0.3);
    margin-bottom:30px;
}

.card-title{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:25px;
    font-size:20px;
    color:var(--accent-light);
}

/* =========================
   FORM
========================= */
.form-grid{
    display:grid;
    grid-template-columns:1fr 1fr 1fr;
    gap:20px;
}

.input-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.input-group label{
    font-size:14px;
    color:var(--text-muted);
}

.modern-input{
    background:rgba(15,23,42,0.6);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:12px;
    padding:12px 15px;
    color:white;
    font-family:'Kanit';
    outline:none;
    transition:0.3s;
}

.modern-input:focus{
    border-color:var(--accent);
}

.btn-save{
    grid-column:span 3;
    background:linear-gradient(135deg,var(--accent),#4f46e5);
    color:white;
    border:none;
    border-radius:12px;
    padding:15px;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
    margin-top:10px;
}

.btn-save:hover{
    transform:translateY(-2px);
    box-shadow:0 5px 15px rgba(99,102,241,0.4);
}

/* =========================
   TABLE
========================= */
table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    padding:15px;
    color:var(--text-muted);
    border-bottom:1px solid rgba(255,255,255,0.1);
    font-weight:400;
}

td{
    padding:15px;
    border-bottom:1px solid rgba(255,255,255,0.05);
}

.food-info{
    display:flex;
    align-items:center;
    gap:12px;
}

.food-thumb{
    width:50px;
    height:50px;
    border-radius:10px;
    object-fit:cover;
}

/* =========================
   STATUS
========================= */
.status-badge{
    padding:5px 12px;
    border-radius:8px;
    font-size:12px;
}

.status-ready{
    background:rgba(34,197,94,0.1);
    color:var(--success);
}

.status-out{
    background:rgba(239,68,68,0.1);
    color:var(--danger);
}

/* =========================
   ACTION BUTTON
========================= */
.action-btns{
    display:flex;
    gap:8px;
    justify-content:flex-end;
}

.btn-tool{
    width:35px;
    height:35px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:10px;
    text-decoration:none;
    transition:0.2s;
}

.btn-toggle{
    background:rgba(255,255,255,0.05);
    color:var(--text-muted);
}

.btn-delete{
    background:rgba(239,68,68,0.1);
    color:var(--danger);
}

.btn-delete:hover{
    background:var(--danger);
    color:white;
}

/* =========================
   RESPONSIVE
========================= */
@media (max-width:768px){

    .form-grid{
        grid-template-columns:1fr;
    }

    .btn-save{
        grid-column:span 1;
    }

}

</style>
</head>

<body>

<div class="wrapper">

<!-- HEADER -->
<div class="page-header">
    <h1>🍱 จัดการเมนูอาหาร</h1>

    <a href="dashboard.php" class="dashboard-btn">
        <i class="fas fa-arrow-left"></i> Dashboard
    </a>
</div>

<!-- ADD FOOD -->
<div class="add-card">

<div class="card-title">
    <i class="fas fa-plus-circle"></i>
    เพิ่มเมนูอาหารใหม่
</div>

<form method="POST">

<div class="form-grid">

<div class="input-group">
<label>ชื่ออาหาร</label>
<input type="text" name="name" class="modern-input" required>
</div>

<div class="input-group">
<label>ราคา (บาท)</label>
<input type="number" name="price" step="0.01" class="modern-input" required>
</div>

<div class="input-group">
<label>ส่วนลด (%)</label>
<input type="number" name="discount" min="0" max="100" value="0" class="modern-input">
</div>

<div class="input-group">
<label>URL รูปภาพ</label>
<input type="text" name="image" class="modern-input" required>
</div>

<button type="submit" name="add_food" class="btn-save">
<i class="fas fa-save"></i> บันทึกรายการอาหาร
</button>

</div>
</form>

</div>

<!-- FOOD TABLE -->
<div class="table-card">

<table>

<thead>
<tr>
<th>รายการอาหาร</th>
<th>ราคา</th>
<th>ส่วนลด</th>
<th>สถานะ</th>
<th style="text-align:right;">จัดการ</th>
</tr>
</thead>

<tbody>

<?php
$result = mysqli_query($conn,"SELECT * FROM foods ORDER BY food_id DESC");
while($row=mysqli_fetch_assoc($result)):
?>

<tr>

<td>
<div class="food-info">
<img src="<?= $row['image'] ?>" class="food-thumb">
<?= htmlspecialchars($row['food_name']) ?>
</div>
</td>

<td style="color:#818cf8;font-weight:600;">
฿<?= number_format($row['price'],2) ?>
</td>

<td>
<form method="POST" style="display:flex;gap:6px;">
<input type="hidden" name="food_id" value="<?= $row['food_id'] ?>">
<input type="number" name="discount"
value="<?= $row['discount'] ?>"
min="0" max="100"
style="width:60px"
class="modern-input">
<button name="update_discount" class="btn-tool btn-toggle">
<i class="fas fa-check"></i>
</button>
</form>
</td>

<td>
<?php if($row['status']=='available'): ?>
<span class="status-badge status-ready">พร้อมขาย</span>
<?php else: ?>
<span class="status-badge status-out">ของหมด</span>
<?php endif; ?>
</td>

<td>
<div class="action-btns">

<a href="?toggle_status=<?= $row['food_id'] ?>"
class="btn-tool btn-toggle">
<i class="fas fa-sync-alt"></i>
</a>

<a href="javascript:void(0)"
onclick="confirmDelete(<?= $row['food_id'] ?>)"
class="btn-tool btn-delete">
<i class="fas fa-trash-alt"></i>
</a>

</div>
</td>

</tr>

<?php endwhile; ?>

</tbody>
</table>

</div>

</div>

<script>
function confirmDelete(id){
Swal.fire({
title:'ยืนยันการลบ?',
icon:'warning',
showCancelButton:true,
confirmButtonColor:'#ef4444',
confirmButtonText:'ลบ',
cancelButtonText:'ยกเลิก'
}).then((result)=>{
if(result.isConfirmed){
window.location='?delete_food='+id;
}
});
}
</script>

</body>
</html>