<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

/* =========================
   ADD ADMIN
========================= */
if (isset($_POST['add'])) {

    $u = mysqli_real_escape_string($conn, $_POST['username']);
    $p = md5($_POST['password']);

    $check = mysqli_query(
        $conn,
        "SELECT username FROM admins WHERE username='$u'"
    );

    if (mysqli_num_rows($check) == 0) {

        mysqli_query(
            $conn,
            "INSERT INTO admins (username,password)
             VALUES ('$u','$p')"
        );

        $_SESSION['msg'] = "success_add";

    } else {
        $_SESSION['msg'] = "error_exist";
    }

    header("Location: admin_manage.php");
    exit();
}

/* =========================
   DELETE ADMIN
========================= */
if (isset($_GET['del'])) {

    $id = intval($_GET['del']);

    $target = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT username FROM admins WHERE admin_id=$id"
        )
    );

    $count = mysqli_fetch_assoc(
        mysqli_query(
            $conn,
            "SELECT COUNT(*) total FROM admins"
        )
    );

    if (!$target) {
        $_SESSION['msg'] = "error_notfound";
    }
    elseif ($target['username'] === $_SESSION['admin']) {
        $_SESSION['msg'] = "error_self";
    }
    elseif ($count['total'] <= 1) {
        $_SESSION['msg'] = "error_last_one";
    }
    else {
        mysqli_query(
            $conn,
            "DELETE FROM admins WHERE admin_id=$id"
        );
        $_SESSION['msg'] = "success_del";
    }

    header("Location: admin_manage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>

<meta charset="UTF-8">
<title>Admin Management | MyFoodie</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../assets/style.css">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>

/* =========================
   COLOR VARIABLE
========================= */
:root{
    --card-dark:#1e293b;
    --accent:#6366f1;
    --primary:#38bdf8;
    --text-dim:#94a3b8;
    --danger:#ef4444;
    --success:#22c55e;
}

/* =========================
   LAYOUT
========================= */
.container{
    max-width:900px;
    margin:auto;
    padding:40px 20px;
}

/* =========================
   HEADER
========================= */
.top-nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:30px;
    margin-bottom:40px;
}

/* =========================
   CARD
========================= */
.admin-card{
    background:var(--card-dark);
    border-radius:24px;
    padding:30px;
    border:1px solid rgba(255,255,255,.06);
    margin-bottom:40px;
    position:relative;
}

.admin-card::before{
    content:'';
    position:absolute;
    left:0;
    top:0;
    width:4px;
    height:100%;
    background:var(--accent);
}

.card-header{
    display:flex;
    gap:12px;
    align-items:center;
    margin-bottom:25px;
}

.card-header i{
    color:var(--accent);
    font-size:22px;
}

/* =========================
   FORM
========================= */
.form-row{
    display:grid;
    grid-template-columns:1fr 1fr auto;
    gap:15px;
}

.modern-input{
    background:#020617;
    border:1px solid rgba(255,255,255,.15);
    padding:12px 16px;
    border-radius:12px;
    color:#fff;
}

.btn-add-admin{
    background:var(--accent);
    border:none;
    color:#fff;
    padding:12px 26px;
    border-radius:12px;
    font-weight:600;
    cursor:pointer;
}

/* =========================
   TABLE
========================= */
.list-card{
    background:var(--card-dark);
    border-radius:24px;
    overflow:hidden;
    border:1px solid rgba(255,255,255,.05);
}

.table-title{
    padding:20px 30px;
    display:flex;
    justify-content:space-between;
    background:rgba(255,255,255,.03);
}

table{
    width:100%;
    border-collapse:collapse;
}

th,td{
    padding:16px 30px;
}

th{
    font-size:13px;
    color:var(--text-dim);
}

td{
    border-bottom:1px solid rgba(255,255,255,.05);
}

/* =========================
   BUTTON
========================= */
.btn-del-admin{
    width:36px;
    height:36px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:10px;
    color:#94a3b8;
    background:rgba(239,68,68,.12);
    border:1px solid rgba(239,68,68,.25);
    text-decoration:none;
}

.btn-del-admin:hover{
    background:#ef4444;
    color:#fff;
}

.btn-locked{
    opacity:.4;
    cursor:not-allowed;
}

</style>
</head>

<body>

<div class="page-container">

    <div class="page-header">

        <div>
            <h1>🔐 ทีมผู้ดูแลระบบ</h1>
            <p style="color:#94a3b8;margin-top:6px;">
                จัดการสิทธิ์การเข้าถึงหลังบ้าน
            </p>
        </div>

        <a href="dashboard.php" class="back">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>

    </div>

    <!-- ADD ADMIN -->
    <div class="admin-card">

        <div class="card-header">
            <i class="fas fa-user-plus"></i>
            <h2>เพิ่มสิทธิ์ผู้ใช้งานใหม่</h2>
        </div>

        <form method="post">

            <div class="form-row">

                <input
                    class="modern-input"
                    name="username"
                    placeholder="Username"
                    required
                >

                <input
                    class="modern-input"
                    type="password"
                    name="password"
                    placeholder="Password"
                    required
                >

                <button
                    class="btn-add-admin"
                    name="add"
                >
                    <i class="fas fa-save"></i>
                    เพิ่มแอดมิน
                </button>

            </div>

        </form>

    </div>

    <!-- LIST ADMIN -->
    <div class="list-card">

        <?php $res = mysqli_query($conn,"SELECT * FROM admins"); ?>

        <div class="table-title">
            <b>รายชื่อแอดมิน</b>
            <span style="color:var(--primary)">
                <?=mysqli_num_rows($res)?> บัญชี
            </span>
        </div>

        <table>

            <thead>
                <tr>
                    <th>ผู้ใช้</th>
                    <th>ID</th>
                    <th></th>
                </tr>
            </thead>

            <tbody>

            <?php while($r=mysqli_fetch_assoc($res)): ?>

                <tr>

                    <td><?=htmlspecialchars($r['username'])?></td>

                    <td>
                        #<?=str_pad($r['admin_id'],3,'0',STR_PAD_LEFT)?>
                    </td>

                    <td align="right">

                    <?php if($r['username']!=$_SESSION['admin']): ?>

                        <a
                            class="btn-del-admin"
                            onclick="confirmDel(<?=$r['admin_id']?>,'<?=$r['username']?>')"
                        >
                            <i class="fas fa-trash"></i>
                        </a>

                    <?php else: ?>

                        <div class="btn-del-admin btn-locked">
                            <i class="fas fa-lock"></i>
                        </div>

                    <?php endif ?>

                    </td>

                </tr>

            <?php endwhile ?>

            </tbody>

        </table>

    </div>

</div>

<script>

function confirmDel(id,name){

    Swal.fire({
        title:'ลบผู้ใช้?',
        text:`ลบ ${name} ออกจากระบบ`,
        icon:'warning',
        showCancelButton:true,
        confirmButtonColor:'#ef4444'
    }).then(r=>{
        if(r.isConfirmed){
            location='?del='+id;
        }
    });

}

</script>

</body>
</html>
