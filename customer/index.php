<?php
include("../config/db.php");
session_start();
/* =========================
    PHP LOGIC
========================= */    
$cart_count = 0;
if(isset($_SESSION['cart'])){
   foreach($_SESSION['cart'] as $item){
       if(isset($item['qty'])){
           $cart_count += (int)$item['qty'];
       }
   }
}

$bestSeller = [];
//  BEST SELLER (TOP 3)
$bestQuery = mysqli_query($conn, "
    SELECT oi.food_id
    FROM order_items oi
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.status = 'served'
    GROUP BY oi.food_id
    ORDER BY SUM(oi.qty) DESC
    LIMIT 3
");

while ($b = mysqli_fetch_assoc($bestQuery)) {
    $bestSeller[] = $b['food_id'];
}

//  จัดการตะกร้า (Clean & Validation)
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function ($item) {
        return is_array($item)
            && isset($item['food_id'], $item['qty'], $item['note'])
            && is_numeric($item['food_id'])
            && is_numeric($item['qty']);
    }));
} else {
    $_SESSION['cart'] = [];
}

//   เพิ่มลงตะกร้า (Ajax Friendly Logic)
if (isset($_POST['food_id'])) {

    $food_id = (int)$_POST['food_id'];
    $note = trim($_POST['note'] ?? '');

    $check = mysqli_query($conn,"SELECT status FROM foods WHERE food_id=$food_id");
    $food = mysqli_fetch_assoc($check);

    if($food && $food['status']!="out"){

        $found = false;

        foreach($_SESSION['cart'] as &$item){
            if($item['food_id']==$food_id && $item['note']===$note){
                $item['qty']++;
                $found = true;
                break;
            }
        }

        if(!$found){
            $_SESSION['cart'][] = [
                "food_id"=>$food_id,
                "qty"=>1,
                "note"=>$note
            ];
        }
    }

    echo json_encode(["status"=>"success"]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyFoodie | Premium Food Delivery</title>

    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
/* =========================
    CSS STYLES
========================= */            
        /* ===== SEARCH (ADD ONLY) ===== */
.search-wrapper{
    max-width:520px;
    margin:30px auto 0;
    position:relative;
}
.search-wrapper input{
    width:100%;
    padding:16px 22px;
    border-radius:50px;
    border:1.5px solid #eee;
    font-size:16px;
    outline:none;
}
.search-result{
    position:absolute;
    top:65px;
    width:100%;
    background:#fff;
    border-radius:20px;
    box-shadow:0 20px 40px rgba(0,0,0,.15);
    display:none;
    z-index:2000;
    overflow:hidden;
}
.search-item{
    display:flex;
    align-items:center;
    gap:15px;
    padding:14px 20px;
    cursor:pointer;
}
.search-item:hover{background:#f8f9fa;}
.search-item img{
    width:50px;
    height:50px;
    border-radius:12px;
    object-fit:cover;
}
.search-name{font-weight:500;}
.search-price{font-size:14px;color:#ff4757;}

        :root {
            --brand-color: #ff4757; /* Rose Red */
            --gold: #d4af37;
            --dark: #2f3542;
            --soft-bg: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 15px 35px rgba(0,0,0,0.05);
            --transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

        body {
            background-color: var(--soft-bg);
            margin: 0;
            font-family: 'Kanit', sans-serif;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* ===== Navbar (Glassmorphism) ===== */
        .header {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 15px 8%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--brand-color);
            text-decoration: none;
            letter-spacing: -0.5px;
        }

        .cart-trigger {
            position: relative;
            background: var(--dark);
            color: white;
            padding: 12px 24px;
            border-radius: 100px;
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            box-shadow: 0 10px 20px rgba(47, 53, 66, 0.2);
        }

        .cart-trigger:hover {
            transform: translateY(-3px);
            background: var(--brand-color);
        }

        .cart-badge {
            background: var(--white);
            color: var(--brand-color);
            min-width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 700;
        }

        /* ===== Hero Section ===== */
        .hero {
            padding: 60px 8% 40px;
            text-align: center;
            background: var(--white);
            background-image: radial-gradient(#eee 1px, transparent 1px);
            background-size: 20px 20px;
        }

        .hero h2 {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            margin: 0 0 15px;
            background: linear-gradient(45deg, var(--dark), var(--brand-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero p { color: #747d8c; font-size: 18px; margin: 0; }

        /* ===== Main Content ===== */
        .container {
            max-width: 1300px;
            margin: auto;
            padding: 40px 20px;
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 35px;
        }

        /* ===== Food Card (Premium Look) ===== */
        .food-card {
            background: var(--white);
            border-radius: 30px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(0,0,0,0.02);
        }

        .food-card:hover {
            transform: translateY(-15px);
            box-shadow: 0 30px 60px rgba(0,0,0,0.1);
        }

        .img-container {
            width: 100%;
            height: 220px;
            position: relative;
            overflow: hidden;
        }

        .img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .food-card:hover .img-container img {
            transform: scale(1.1);
        }

        .promo-tag {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 71, 87, 0.95);
            color: white;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            backdrop-filter: blur(5px);
            z-index: 5;
        }

        .food-body {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .food-body h3 {
            margin: 0 0 8px;
            font-size: 22px;
            font-weight: 600;
            color: var(--dark);
        }

        .price-row {
            display: flex;
            align-items: baseline;
            gap: 10px;
            margin-bottom: 20px;
        }

        .current-price {
            font-size: 26px;
            font-weight: 700;
            color: var(--brand-color);
        }

        .old-price {
            color: #ced4da;
            text-decoration: line-through;
            font-size: 16px;
        }

        /* ===== Inputs & Form ===== */
        .note-input {
            width: 100%;
            border: 1.5px solid #f1f2f6;
            border-radius: 15px;
            padding: 12px 15px;
            font-size: 14px;
            resize: none;
            margin-bottom: 15px;
            background: #f8f9fa;
            transition: var(--transition);
            outline: none;
        }

        .note-input:focus {
            border-color: var(--brand-color);
            background: white;
            box-shadow: 0 0 0 4px rgba(255, 71, 87, 0.1);
        }

        .btn-group {
            margin-top: auto;
        }

        .btn-add {
            width: 100%;
            background: var(--dark);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 18px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: var(--transition);
        }

        .btn-add:hover {
            background: var(--brand-color);
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(255, 71, 87, 0.3);
        }

        .btn-out {
            width: 100%;
            background: #f1f2f6;
            color: #a4b0be;
            border: none;
            padding: 16px;
            border-radius: 18px;
            font-weight: 600;
            cursor: not-allowed;
        }

        /* ===== Animations ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .food-card {
            animation: fadeInUp 0.8s ease backwards;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero h2 { font-size: 32px; }
            .header { padding: 15px 5%; }
            .menu-grid { grid-template-columns: 1fr; }
        }
        .best-badge{
    position:absolute;
    top:20px;
    right:20px;
    background:linear-gradient(135deg,#facc15,#eab308);
    color:#3b2f00;
    padding:6px 14px;
    border-radius:50px;
    font-size:13px;
    font-weight:700;
    display:flex;
    align-items:center;
    gap:6px;
    box-shadow:0 8px 20px rgba(234,179,8,.45);
    z-index:6;
}
    </style>
</head>

<body>

<div class="header">
    <a href="index.php" class="logo">MyFoodie.</a>
    <a href="cart.php" class="cart-trigger">
    <i class="fa-solid fa-bag-shopping"></i>
    <span>My Basket</span>

    <span class="cart-badge" id="cartCount"
          style="<?= ($cart_count > 0 ? '' : 'display:none;') ?>">
        <?= $cart_count ?>
    </span>
</a>
</div>

<div class="hero">
    <h2>Discover Deliciousness</h2>
    <p>Premium ingredients, delivered to your doorstep.</p>
</div>
<div class="search-wrapper">
    <input type="text" id="foodSearch" placeholder="Search menu เช่น ตำ...">
    <div class="search-result" id="searchResult"></div>
</div>
<div class="container">
    <div class="menu-grid">

<?php
$result = mysqli_query($conn, "SELECT * FROM foods ORDER BY food_id DESC");
$delay = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $isOut = ($row['status'] == 'out');
    $hasDiscount = ($row['discount'] > 0);
    $newPrice = $row['price'] * (100 - $row['discount']) / 100;
    $delay += 0.1;
?>
    <div class="food-card" style="animation-delay: <?= $delay ?>s">
        
        <div class="img-container">

    <?php if(in_array($row['food_id'], $bestSeller)): ?>
        <div class="best-badge">
            <i class="fa-solid fa-crown"></i> Best Seller
        </div>
    <?php endif; ?>

    <?php if($hasDiscount && !$isOut): ?>
        <div class="promo-tag">Special Offer -<?= $row['discount'] ?>%</div>
    <?php endif; ?>

    <img src="<?= htmlspecialchars($row['image']) ?>" 
         alt="<?= htmlspecialchars($row['food_name']) ?>" 
         loading="lazy">
</div>


        <div class="food-body">
            <h3><?= htmlspecialchars($row['food_name']) ?></h3>

            <div class="price-row">
                <?php if ($hasDiscount): ?>
                    <span class="current-price">฿<?= number_format($newPrice, 0) ?></span>
                    <span class="old-price">฿<?= number_format($row['price'], 0) ?></span>
                <?php else: ?>
                    <span class="current-price">฿<?= number_format($row['price'], 0) ?></span>
                <?php endif; ?>
            </div>

            <?php if ($isOut): ?>
                <div style="text-align: center; color: #ff6b6b; margin-bottom: 15px; font-weight: 500;">
                    <i class="fa-solid fa-circle-exclamation"></i> Sold Out Temporarily
                </div>
                <button class="btn-out" disabled>Not Available</button>
            <?php else: ?>
<form method="post" class="add-form">
    <input type="hidden" name="food_id" value="<?= $row['food_id'] ?>">

    <textarea name="note" class="note-input" rows="2"></textarea>


    <div class="btn-group">
        <button type="submit" class="btn-add">
            <i class="fa-solid fa-plus"></i> Add to Basket
        </button>
    </div>
</form>
            <?php endif; ?>
        </div>
    </div>
<?php } ?>

    </div>
</div>

<script>
/* =========================
    JAVASCRIPT LOGIC
========================= */        
const input = document.getElementById("foodSearch");
const box = document.getElementById("searchResult");
let timer = null;

input.addEventListener("keyup", () => {
    clearTimeout(timer);
    const q = input.value.trim();
    if (q.length === 0) {
        box.style.display = "none";
        return;
    }

    timer = setTimeout(() => {
        fetch("search_food.php?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                box.innerHTML = "";
                if (data.length === 0) {
                    box.innerHTML = "<div class='search-item'>ไม่พบเมนู</div>";
                } else {
                    data.forEach(f => {
                        box.innerHTML += `
                        <div class="search-item" onclick="addFromSearch(${f.food_id})">
                            <img src="${f.image}">
                            <div style="flex:1">
                                <div class="search-name">${f.food_name}</div>
                                <div class="search-price">฿${f.price}</div>
                            </div>
                            <i class="fa-solid fa-plus" style="color:#ff4757;"></i>
                        </div>`;
                    });
                }
                box.style.display = "block";
            });
    }, 250);
});
function updateCartBadge(){
    fetch("get_cart_count.php")
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById("cartCount");

            if(data.count > 0){
                badge.style.display = "flex";
                badge.innerText = data.count;
            }else{
                badge.style.display = "none";
            }
        });
}
function addFromSearch(food_id){

    const data = new FormData();
    data.append("food_id", food_id);
    data.append("note", "");

    fetch("index.php", {
        method: "POST",
        body: data
    })
    .then(res => res.text())
    .then(res => {
        console.log(res);

        Swal.fire({
            toast:true,
            position:'top-end',
            icon:'success',
            title:'เพิ่มลงตะกร้าแล้ว',
            showConfirmButton:false,
            timer:1500
        });

        updateCartBadge();
    });
}
document.addEventListener("click", e => {
    if (!e.target.closest(".search-wrapper")) {
        box.style.display = "none";
    }
});
document.querySelectorAll(".add-form").forEach(form => {
    form.addEventListener("submit", function(e){
        e.preventDefault();

        const data = new FormData(this);

       fetch("", {
    method: "POST",
    body: data
})
.then(res => res.text())
.then(data => {
    console.log(data);

    Swal.fire({
        toast:true,
        position:'top-end',
        icon:'success',
        title:'เพิ่มลงตะกร้าแล้ว',
        showConfirmButton:false,
        timer:1500
    });

    updateCartBadge();
});
    });
});
updateCartBadge();
</script>


</body>
</html>