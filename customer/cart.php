<?php
session_start();
include("../config/db.php");
/* =========================
    PHP LOGIC
========================= */
/* ======================================================
   🔒 SANITIZE & VALIDATION LOGIC
   ====================================================== */
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

// ❌ Remove Item
if (isset($_GET['remove'])) {
    $index = (int)$_GET['remove'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }
    header("Location: cart.php?removed=1");
    exit();
}

// ➕ ➖ Update Quantity
if (isset($_GET['update'], $_GET['type'])) {
    $index = (int)$_GET['update'];
    $type  = $_GET['type'];
    if (!isset($_SESSION['cart'][$index])) {
        header("Location: cart.php");
        exit();
    }
    
    $food_id = (int)$_SESSION['cart'][$index]['food_id'];
    $check = mysqli_query($conn, "SELECT status FROM foods WHERE food_id = $food_id");
    $food  = mysqli_fetch_assoc($check);

    if ($food && $food['status'] === 'available') {
        if ($type === 'plus') $_SESSION['cart'][$index]['qty']++;
        if ($type === 'minus') {
            $_SESSION['cart'][$index]['qty']--;
            if ($_SESSION['cart'][$index]['qty'] <= 0) {
                unset($_SESSION['cart'][$index]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
            }
        }
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Basket | MyFoodie Premium</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
/* =========================
    CSS STYLES
========================= */        
        :root {
            --brand: #ff4757;
            --dark: #2f3542;
            --soft-gray: #f8f9fa;
            --border: #edf0f2;
            --text-muted: #a4b0be;
            --shadow: 0 20px 40px rgba(0,0,0,0.06);
        }

        body {
            background-color: #fcfcfc;
            font-family: 'Kanit', sans-serif;
            margin: 0;
            color: var(--dark);
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 0 25px;
        }

        /* Progress Steps */
        .steps {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 50px;
            gap: 20px;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            color: var(--text-muted);
        }
        .step.active { color: var(--brand); }
        .step-num {
            width: 28px; height: 28px;
            border: 2px solid var(--text-muted);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .step.active .step-num { border-color: var(--brand); background: var(--brand); color: white; }
        .step-line { width: 50px; height: 2px; background: var(--border); }

        /* Header */
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--dark);
            padding-bottom: 15px;
        }
        .cart-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 36px; margin: 0;
        }
        .btn-back {
            text-decoration: none;
            color: var(--dark);
            font-weight: 500;
            display: flex; align-items: center; gap: 8px;
            transition: 0.3s;
        }
        .btn-back:hover { color: var(--brand); transform: translateX(-5px); }

        /* Table Design */
        .cart-wrapper {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 40px;
            align-items: start;
        }

        .cart-items {
            background: white;
            border-radius: 30px;
            box-shadow: var(--shadow);
            padding: 20px;
        }

        .item-card {
            display: flex;
            gap: 20px;
            padding: 25px 15px;
            border-bottom: 1px solid var(--border);
            position: relative;
        }
        .item-card:last-child { border-bottom: none; }

        .item-img {
            width: 110px; height: 110px;
            border-radius: 20px;
            object-fit: cover;
            background: var(--soft-gray);
        }

        .item-details { flex-grow: 1; }
        .item-name {
            font-size: 20px; font-weight: 600;
            margin: 0 0 5px;
        }
        .item-note {
            font-size: 13px; color: var(--text-muted);
            background: var(--soft-gray);
            padding: 4px 12px; border-radius: 8px;
            display: inline-block; margin-bottom: 15px;
        }

        .item-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* Qty Control */
        .qty-box {
            display: flex; align-items: center;
            background: var(--soft-gray);
            border-radius: 12px;
            padding: 4px;
        }
        .qty-btn {
            width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            background: white; border-radius: 10px;
            color: var(--dark); text-decoration: none;
            font-weight: bold; transition: 0.2s;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .qty-btn:hover { background: var(--brand); color: white; }
        .qty-val { padding: 0 15px; font-weight: 600; }

        .item-price { font-size: 18px; font-weight: 700; color: var(--brand); }

        .btn-remove {
            position: absolute; top: 25px; right: 15px;
            color: #d1d8e0; text-decoration: none;
            font-size: 18px; transition: 0.3s;
        }
        .btn-remove:hover { color: var(--brand); transform: rotate(90deg); }

        /* Summary Sidebar */
        .summary-box {
            background: var(--dark);
            color: white;
            border-radius: 35px;
            padding: 35px;
            position: sticky; top: 120px;
            box-shadow: 0 30px 60px rgba(47, 53, 66, 0.2);
        }
        .summary-title {
            font-size: 24px; font-weight: 600; margin-bottom: 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding-bottom: 15px;
        }
        .summary-row {
            display: flex; justify-content: space-between;
            margin-bottom: 15px; font-weight: 300; opacity: 0.8;
        }
        .summary-total {
            margin-top: 25px; padding-top: 20px;
            border-top: 1px dashed rgba(255,255,255,0.3);
            display: flex; justify-content: space-between; align-items: flex-end;
        }
        .total-price { font-size: 36px; font-weight: 700; color: white; }

        .btn-checkout {
            width: 100%;
            background: var(--brand);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 20px;
            font-size: 18px; font-weight: 600;
            margin-top: 30px; cursor: pointer;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-checkout:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 15px 30px rgba(255, 71, 87, 0.4);
        }
        .btn-checkout:disabled { background: #4b5563; cursor: not-allowed; opacity: 0.5; }

        /* Status & Alerts */
        .alert-out {
            background: #fff5f5; color: #ff4757;
            padding: 15px; border-radius: 15px;
            margin-bottom: 20px; font-size: 14px;
            border-left: 5px solid var(--brand);
        }

        .empty-basket {
            text-align: center; padding: 100px 20px;
            background: white; border-radius: 40px; box-shadow: var(--shadow);
        }
        .empty-basket i { font-size: 80px; color: var(--soft-gray); margin-bottom: 20px; }

        @media (max-width: 850px) {
            .cart-wrapper { grid-template-columns: 1fr; }
            .summary-box { position: static; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="steps">
        <div class="step active"><div class="step-num">1</div> Basket</div>
        <div class="step-line"></div>
        <div class="step"><div class="step-num">2</div> Checkout</div>
        <div class="step-line"></div>
        <div class="step"><div class="step-num">3</div> Success</div>
    </div>

    <div class="cart-header">
        <h1>Your Basket.</h1>
        <a href="index.php" class="btn-back">
            <i class="fa-solid fa-arrow-left"></i> Continue Shopping
        </a>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="empty-basket">
            <i class="fa-solid fa-cart-shopping"></i>
            <h2 style="font-family:'Playfair Display'; font-size: 32px;">Your basket is empty</h2>
            <p style="color:var(--text-muted); margin-bottom: 30px;">Looks like you haven't added anything yet.</p>
            <a href="index.php" class="btn-checkout" style="display:inline-flex; width:auto; padding: 15px 40px;">
                Browse our menu
            </a>
        </div>
    <?php else: 
        $subtotal = 0;
        $has_out_item = false;
    ?>
        <div class="cart-wrapper">
            <div class="cart-items-container">
                <?php if (isset($_GET['error_out'])): ?>
                    <div class="alert-out">
                        <i class="fa-solid fa-circle-exclamation"></i> Some items are no longer available. Please remove them before checkout.
                    </div>
                <?php endif; ?>

                <div class="cart-items">
                    <?php foreach ($_SESSION['cart'] as $index => $item): 
                        $fid = (int)$item['food_id'];
                        $res = mysqli_query($conn, "SELECT * FROM foods WHERE food_id = $fid");
                        $food = mysqli_fetch_assoc($res);
                        if (!$food) continue;

                        $price = ($food['discount'] > 0) ? ($food['price'] * (100 - $food['discount']) / 100) : $food['price'];
                        $line_total = $price * $item['qty'];

                        if ($food['status'] === 'available') {
                            $subtotal += $line_total;
                        } else {
                            $has_out_item = true;
                        }
                    ?>
                        <div class="item-card">
                            <img src="<?= htmlspecialchars($food['image']) ?>" class="item-img" alt="food">
                            <div class="item-details">
                                <h3 class="item-name" style="<?= $food['status'] === 'out' ? 'color:#ccc; text-decoration:line-through;' : '' ?>">
                                    <?= htmlspecialchars($food['food_name']) ?>
                                </h3>
                                <div class="item-note">
                                    <i class="fa-regular fa-comment-dots"></i> 
                                    <?= $item['note'] !== '' ? htmlspecialchars($item['note']) : 'No special instructions' ?>
                                </div>

                                <?php if($food['status'] === 'out'): ?>
                                    <div style="color:var(--brand); font-size:12px; margin-bottom:10px; font-weight:600;">
                                        <i class="fa-solid fa-ban"></i> THIS MENU IS SOLD OUT
                                    </div>
                                <?php endif; ?>

                                <div class="item-actions">
                                    <?php if($food['status'] === 'available'): ?>
                                        <div class="qty-box">
                                            <a href="cart.php?update=<?= $index ?>&type=minus" class="qty-btn"><i class="fa-solid fa-minus"></i></a>
                                            <span class="qty-val"><?= $item['qty'] ?></span>
                                            <a href="cart.php?update=<?= $index ?>&type=plus" class="qty-btn"><i class="fa-solid fa-plus"></i></a>
                                        </div>
                                    <?php endif; ?>
                                    <div class="item-price">฿<?= number_format($line_total, 0) ?></div>
                                </div>
                            </div>
                            <a href="javascript:void(0)" onclick="confirmRemove(<?= $index ?>)" class="btn-remove">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="summary-box">
                <div class="summary-title">Summary</div>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>฿<?= number_format($subtotal, 0) ?></span>
                </div>
                <div class="summary-row">
                    <span>Delivery Fee</span>
                    <span style="color:var(--brand); font-weight:600;">FREE</span>
                </div>
                <div class="summary-row" style="margin-bottom: 0;">
                    <span>Taxes (Included)</span>
                    <span>฿0</span>
                </div>

                <div class="summary-total">
                    <div>
                        <div style="font-size: 14px; opacity: 0.6;">Total</div>
                        <div class="total-price">฿<?= number_format($subtotal, 0) ?></div>
                    </div>
                </div>

                <?php if ($has_out_item): ?>
                    <button class="btn-checkout" disabled>
                        <i class="fa-solid fa-lock"></i> Check Items
                    </button>
                    <p style="color:#ff7675; font-size:11px; text-align:center; margin-top:15px;">
                        Please remove sold out items
                    </p>
                <?php else: ?>
                    <form action="checkout.php" method="post">
                        <button type="submit" class="btn-checkout">
                            Place Order <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
/* =========================
    JAVASCRIPT LOGIC
========================= */    
    function confirmRemove(index) {
        Swal.fire({
            title: 'Remove item?',
            text: "Do you want to remove this dish from basket?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#2f3542',
            confirmButtonText: 'Yes, remove it',
            background: '#fff',
            color: '#2f3542'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '?remove=' + index;
            }
        })
    }

    // Success notification after removal
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('removed')) {
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 2500,
            background: '#2f3542',
            color: '#fff'
        });
        Toast.fire({
            icon: 'success',
            title: 'Basket updated'
        });
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
<script>
function checkFoodStatusRealtime() {
    fetch('check_food_status.php')
        .then(res => res.json())
        .then(data => {
            if (data.has_out) {
                // ถ้ายังไม่เคยแจ้งเตือน
                if (!document.body.classList.contains('has-out-alert')) {
                    document.body.classList.add('has-out-alert');

                    Swal.fire({
                        icon: 'warning',
                        title: 'Some items are sold out',
                        text: 'Some food items in your basket are no longer available.',
                        confirmButtonColor: '#ff4757'
                    }).then(() => {
                        location.reload(); // reload เพื่อ sync UI
                    });
                }
            }
        })
        .catch(err => console.error(err));
}

// ตรวจทุก 3 วินาที
setInterval(checkFoodStatusRealtime, 3000);
</script>

</body>
</html>