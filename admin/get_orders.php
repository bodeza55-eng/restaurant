<?php
require_once __DIR__ . '/../config/db.php';
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo 'Database connection not available.';
    exit;
}

$orders = mysqli_query($conn,"SELECT * FROM orders ORDER BY created_at DESC");

while($o=mysqli_fetch_assoc($orders)){

    echo "<tr>";

    echo "<td class='order-id'>#".str_pad($o['order_id'],4,'0',STR_PAD_LEFT)."</td>";
    echo "<td>".date("d M H:i",strtotime($o['created_at']))."</td>";

    // =======================
    // ITEMS
    // =======================
    echo "<td>";

    $items=mysqli_query($conn,"
        SELECT oi.*,f.food_name 
        FROM order_items oi
        JOIN foods f ON oi.food_id=f.food_id
        WHERE oi.order_id={$o['order_id']}
    ");

    while($i=mysqli_fetch_assoc($items)){
        echo "
        <div class='items'>
            <b>{$i['food_name']}</b> x {$i['qty']}<br>
            <small>โน้ต: ".($i['note'] ?: '-')."</small>
        </div>";
    }

    echo "</td>";

    // =======================
    // TOTAL + SLIP
    // =======================
    echo "<td>
            ฿ ".number_format($o['total'],2)."<br>";

    if(!empty($o['slip_image'])){
        echo "
            <a href='../uploads/slips/{$o['slip_image']}'
               target='_blank'
               style='color:#60a5fa;font-size:12px'>
               ดูสลิป
            </a>";
    }else{
        echo "
            <small style='color:#94a3b8'>ไม่มีสลิป</small>";
    }

    echo "</td>";

    // =======================
    // STATUS
    // =======================
    if($o['status'] == 'waiting_verify'){
    echo "<td><span class='status waiting_verify'>Prepare</span></td>";
    }else{
    echo "<td><span class='status {$o['status']}'>{$o['status']}</span></td>";
}
    // =======================
    // ACTION
    // =======================
    echo "<td>";
    if($o['status']!='served'){
        echo "<a href='?serve={$o['order_id']}'><button class='btn'>เสิร์ฟ</button></a>";
    }else{
        echo "<i class='fas fa-check-double' style='opacity:.4'></i>";
    }
    // ปุ่มลบคำสั่งซื้อ (JS จะจัดการเรียก AJAX)
    echo " <button class='btn-delete' data-order-id='{$o['order_id']}'>ลบ</button>";
    
    echo "</td>";
    echo "</td>";

    echo "</tr>";
}
?>
