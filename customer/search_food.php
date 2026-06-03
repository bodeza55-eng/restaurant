<?php
include("../config/db.php");

$q = trim($_GET['q'] ?? '');
$data = [];

if ($q !== '') {
    $stmt = $conn->prepare("
        SELECT food_id, food_name, price, image
        FROM foods
        WHERE food_name LIKE ?
        AND status='available'
        LIMIT 8
    ");
    $like = "%{$q}%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $data[] = $row;
    }
}

header("Content-Type: application/json; charset=UTF-8");
echo json_encode($data, JSON_UNESCAPED_UNICODE);
