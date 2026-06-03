<?php
include("../config/db.php");

if(isset($_POST['reset'])){

    $username = mysqli_real_escape_string($conn,$_POST['username']);
    $newpass  = md5($_POST['new_password']);

    $check = mysqli_query($conn,"SELECT * FROM admins WHERE username='$username'");

    if(mysqli_num_rows($check)==1){

        mysqli_query($conn,"
            UPDATE admins 
            SET password='$newpass' 
            WHERE username='$username'
        ");

        $success = "ตั้งรหัสผ่านใหม่สำเร็จแล้ว";

    }else{
        $error = "ไม่พบ Username นี้ในระบบ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Reset Password | MyFoodie</title>

<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;600&display=swap" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>

:root{
    --accent:#00f2fe;
    --accent2:#4facfe;
    --glass-bg:rgba(255,255,255,0.12);
    --glass-border:rgba(255,255,255,0.2);
}

/* BODY */
body{
    margin:0;
    height:100vh;
    background:#0f172a;
    font-family:'Kanit',sans-serif;
    display:flex;
    justify-content:center;
    align-items:center;
    overflow:hidden;
}

/* BACKGROUND */
.bg{
    position:absolute;
    inset:0;
    background:linear-gradient(45deg,#0f172a,#1e293b);
    z-index:-1;
}

.blob{
    position:absolute;
    width:380px;
    height:380px;
    background:linear-gradient(to right,var(--accent),var(--accent2));
    filter:blur(100px);
    border-radius:50%;
    opacity:.4;
    animation:move 18s infinite alternate;
}

@keyframes move{
    from{transform:translate(-20%,-20%);}
    to{transform:translate(120%,80%);}
}

/* CARD */
.box{
    width:380px;
    padding:40px;
    background:var(--glass-bg);
    backdrop-filter:blur(22px);
    border:1px solid var(--glass-border);
    border-radius:26px;
    color:white;
    box-shadow:
        0 30px 60px rgba(0,0,0,.6),
        inset 0 0 0 1px rgba(255,255,255,.05);
}

/* LOGO */
.logo{
    text-align:center;
    font-size:50px;
    margin-bottom:10px;
    background:linear-gradient(to bottom right,#fff,#94a3b8);
    background-clip:text;
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
}

h2{
    text-align:center;
    margin-bottom:25px;
    letter-spacing:1px;
}

/* INPUT GROUP */
.input-group{
    position:relative;
    margin-bottom:20px;
}

.input-group i.fa-user,
.input-group i.fa-lock{
    position:absolute;
    left:14px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
    pointer-events:none;
}

/* INPUT */
.input-group input{
    width:100%;
    padding:14px 44px 14px 42px;
    background:rgba(255,255,255,.06);
    border:1px solid var(--glass-border);
    border-radius:14px;
    color:white;
    outline:none;
    font-size:15px;
    transition:.3s;
}

.input-group input:focus{
    background:rgba(255,255,255,.1);
    border-color:var(--accent);
    box-shadow:0 0 18px rgba(0,242,254,.45);
}

/* EYE */
.toggle-pass{
    position:absolute;
    right:14px;
    top:50%;
    transform:translateY(-50%);
    color:#94a3b8;
    cursor:pointer;
    transition:.3s;
}
input{
    width:100%;
    padding:14px 14px 14px 44px;
    background:rgba(255,255,255,.05);
    border:1px solid var(--glass-border);
    border-radius:12px;
    color:white;
    outline:none;
    font-size:15px;
    box-sizing:border-box;
}
.toggle-pass:hover{
    color:var(--accent);
    transform:translateY(-50%) scale(1.15);
}

/* BUTTON */
button{
    width:100%;
    padding:14px;
    border:none;
    border-radius:14px;
    background:linear-gradient(to right,var(--accent2),var(--accent));
    color:#0f172a;
    font-size:16px;
    font-weight:600;
    cursor:pointer;
    transition:.3s;
}

button:hover{
    transform:translateY(-2px);
    box-shadow:0 15px 30px rgba(0,242,254,.45);
}

/* ALERT */
.success{
    background:rgba(34,197,94,.2);
    border-left:4px solid #22c55e;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
}

.error{
    background:rgba(239,68,68,.2);
    border-left:4px solid #ef4444;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
}

/* BACK LINK */
.back{
    text-align:center;
    margin-top:20px;
}

.back a{
    color:#60a5fa;
    text-decoration:none;
    display:inline-flex;
    align-items:center;
    gap:6px;
    padding:6px 16px;
    border-radius:999px;
    transition:.3s;
}

.back a:hover{
    color:var(--accent);
    background:rgba(255,255,255,.08);
}

</style>
</head>

<body>

<div class="bg">
    <div class="blob"></div>
</div>

<div class="box animate__animated animate__fadeInDown">

    <div class="logo">
        <i class="fas fa-key"></i>
    </div>

    <h2>Reset Password</h2>

    <?php if(isset($success)) echo "<div class='success'>$success</div>"; ?>
    <?php if(isset($error))   echo "<div class='error'>$error</div>"; ?>

    <form method="post">

        <div class="input-group">
            <i class="fas fa-user"></i>
            <input type="text"
                   name="username"
                   placeholder="Username"
                   required>
        </div>

        <div class="input-group">
            <i class="fas fa-lock"></i>

            <input  type="password"
                    name="new_password"
                    id="new_password"
                    placeholder="New Password"
                    required>

            <i class="fas fa-eye toggle-pass" id="toggleNewPass"></i>
        </div>

        <button name="reset">
            <i class="fas fa-sync-alt"></i>
            ตั้งรหัสผ่านใหม่
        </button>

    </form>

    <div class="back">
        <a href="login.php">
            <i class="fas fa-arrow-left"></i>
            กลับหน้าเข้าสู่ระบบ
        </a>
    </div>

</div>

<script>
const toggleNewPass = document.getElementById("toggleNewPass");
const newPassInput  = document.getElementById("new_password");

toggleNewPass.addEventListener("click", function(){

    const type =
        newPassInput.getAttribute("type") === "password"
        ? "text"
        : "password";

    newPassInput.setAttribute("type", type);
    this.classList.toggle("fa-eye-slash");

});
</script>

</body>
</html>
