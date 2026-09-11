<?php
// admin/update_record.php
session_start();

// Enforce authentication
if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once dirname(__DIR__) . '/config/db.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if (!$input) {
    $input = $_POST;
}

$table = $input['table'] ?? '';
$id = $input['id'] ?? '';
$status = $input['status'] ?? '';
$admin_notes = $input['admin_notes'] ?? '';

// Validate table
$allowed_tables = ['registrations', 'consultations'];
if (!in_array($table, $allowed_tables)) {
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit;
}

if (!is_numeric($id) || empty($id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    // We update both status and admin_notes
    $stmt = $pdo->prepare("UPDATE `$table` SET status = :status, admin_notes = :admin_notes WHERE id = :id");
    $stmt->execute([
        ':status' => $status,
        ':admin_notes' => $admin_notes,
        ':id' => $id
    ]);

    echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
} catch (PDOException $e) {
    error_log("DB Update Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>
