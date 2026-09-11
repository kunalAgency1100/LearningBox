<?php
// api/register.php
// Handles form submissions from register.html

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Get JSON input
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if ($input) {
    $fullName = $input['fullName'] ?? '';
    $email = $input['email'] ?? '';
    $phone = $input['phone'] ?? '';
    $role = $input['role'] ?? '';
} else {
    $fullName = $_POST['fullName'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
}

// Sanitize and Validate
$fullName = trim(filter_var($fullName, FILTER_SANITIZE_STRING));
$email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
$phone = trim(filter_var($phone, FILTER_SANITIZE_STRING));
$role = trim(filter_var($role, FILTER_SANITIZE_STRING));

if (empty($fullName) || empty($email) || empty($phone) || empty($role)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Insert into database
require_once dirname(__DIR__) . '/config/db.php';
try {
    $stmt = $pdo->prepare("INSERT INTO registrations (full_name, email, phone, role, status) VALUES (:fullName, :email, :phone, :role, 'Pending')");
    $stmt->execute([
        ':fullName' => $fullName, 
        ':email' => $email, 
        ':phone' => $phone, 
        ':role' => $role
    ]);
} catch (PDOException $e) {
    error_log("DB Query Error: " . $e->getMessage());
    // Proceeding to send email even if DB fails, or we could return error
}

// Extract First Name
$nameParts = explode(' ', $fullName);
$firstName = $nameParts[0];

// Send Auto-Mailer to User
$to = $email;
$subject = "Thank You for Registering with Learning Box";

$htmlContent = "
<html>
<head>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .header { background-color: #000976; color: #ffffff; padding: 20px; text-align: center; border-bottom: 4px solid #F4BD18; }
        .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
        .content { padding: 30px; color: #333333; line-height: 1.6; font-size: 15px; }
        .details-box { background-color: #f9fafb; padding: 15px; border-radius: 6px; border: 1px solid #e5e7eb; margin: 20px 0; }
        .details-box ul { list-style: none; padding: 0; margin: 0; }
        .details-box li { margin-bottom: 8px; }
        .details-box strong { color: #000976; }
        .checklist { list-style: none; padding: 0; margin: 20px 0; }
        .checklist li { margin-bottom: 10px; padding-left: 24px; position: relative; }
        .checklist li::before { content: '✅'; position: absolute; left: 0; top: 0; font-size: 14px; }
        .footer { background-color: #f9fafb; padding: 20px; font-size: 14px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        .footer p { margin: 5px 0; }
        .contact-links a { color: #26AADC; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Learning Box</h1>
        </div>
        <div class='content'>
            <p>Hi {$firstName},</p>
            <p>Thank you for registering with Learning Box.</p>
            <p>We’re excited to have you as part of our growing learning community.</p>
            <p>Your registration has been received successfully, and we’ll notify you as soon as our learning sessions and programs go live.</p>
            
            <div class='details-box'>
                <p style='margin-top: 0; font-weight: bold; color: #000976;'>Registered Details</p>
                <ul>
                    <li><strong>Name:</strong> {$fullName}</li>
                    <li><strong>Email:</strong> {$email}</li>
                    <li><strong>Role:</strong> {$role}</li>
                </ul>
            </div>
            
            <p style='font-weight: bold; color: #000976; font-size: 16px;'>What happens next?</p>
            <ul class='checklist'>
                <li>You’ll receive early updates about upcoming sessions</li>
                <li>Priority access to new learning programs</li>
                <li>Announcements on workshops, certifications, and training opportunities</li>
            </ul>
            
            <p>We’re working on creating an engaging and valuable learning experience for you, and we can’t wait to share it soon.</p>
        </div>
        <div class='footer'>
            <p>If you have any questions, feel free to reach out to us anytime.</p>
            <div class='contact-links'>
                <p>📧 <a href='mailto:info@learningbox.in'>info@learningbox.in</a></p>
                <p>🌐 <a href='https://www.learningbox.in'>www.learningbox.in</a></p>
            </div>
            <p style='margin-top: 20px; font-weight: bold; color: #333;'>Regards,<br>Learning Box Team</p>
        </div>
    </div>
</body>
</html>
";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: Learning Box Team <info@learningbox.in>\r\n";
$headers .= "Reply-To: info@learningbox.in\r\n";

// Use the -f flag to set the envelope sender, which is often required by cPanel/Exim
@mail($to, $subject, $htmlContent, $headers, "-finfo@learningbox.in");

// Also send a notification to the admin
$adminTo = "info@learningbox.in";
$adminSubject = "New Registration: " . $fullName;
$adminContent = "A new user has registered. Name: $fullName, Email: $email, Phone: $phone, Role: $role";
$adminHeaders = "From: Learning Box System <info@learningbox.in>\r\n";
@mail($adminTo, $adminSubject, $adminContent, $adminHeaders, "-finfo@learningbox.in");

// Return success
echo json_encode(['success' => true, 'message' => 'Thank you! Your registration has been received.']);
?>
