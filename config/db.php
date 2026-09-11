<?php
// config/db.php
// Database configuration for Learning Box cPanel Deployment

// Update these variables with your actual cPanel MySQL credentials
$db_host = 'localhost';
$db_name = 'backend_lb';
$db_user = 'user_lb';
$db_pass = '^m^(UT120pFG';

try {
    // Create new PDO instance
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);

    $pdo->exec("SET time_zone = '+00:00'");

    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Use associative arrays for fetching
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // For security, don't expose db errors to the user in a production environment
    // Instead, you'd log $e->getMessage() to a file.
    http_response_code(503);
    die(json_encode(['success' => false, 'message' => 'Database connection failed.']));
}
?>