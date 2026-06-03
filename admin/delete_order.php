<?php
require_once __DIR__ . '/../config/db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

// ตรวจสอบสิทธิ์ admin
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

// อ่าน input JSON
$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing order_id']);
    exit;
}

$id = intval($data['order_id']);

// เริ่มลบ
try {
    mysqli_query($conn, "DELETE FROM order_items WHERE order_id=$id");
    mysqli_query($conn, "DELETE FROM orders WHERE order_id=$id");
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

exit;
