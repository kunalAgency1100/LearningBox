<?php
// api/contact.php
// Handles form submissions from contact.html

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Include database configuration
// We use a try-catch in db.php, but let's suppress immediate die to properly return JSON if db fails.
// Oh wait, db.php already outputs JSON and dies on failure. That's fine.
require_once dirname(__DIR__) . '/config/db.php';

// Try to get JSON or form-data
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, true);

if ($input) {
    // Received JSON
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $phone = $input['phone'] ?? '';
    $message = $input['message'] ?? '';
} else {
    // Received Form Data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $message = $_POST['message'] ?? '';
}

// Sanitize and Validate
$name = trim(filter_var($name, FILTER_SANITIZE_STRING));
$email = trim(filter_var($email, FILTER_SANITIZE_EMAIL));
$phone = trim(filter_var($phone, FILTER_SANITIZE_STRING));
$message = trim(filter_var($message, FILTER_SANITIZE_STRING));

if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name, Email, and Message are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

try {
    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO contact_submissions (name, email, phone, message) VALUES (:name, :email, :phone, :message)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':message' => $message
    ]);

    // Send beautiful email notification
    //
    $to = "info@learningbox.in";
    $subject = "New Contact Inquiry from " . $name;
    
    $htmlContent = "
    <html>
    <head>
        <style>
            body { font-family: 'Arial', sans-serif; background-color: #f3f4f6; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .header { background-color: #000976; color: #ffffff; padding: 20px; text-align: center; border-bottom: 4px solid #F4BD18; }
            .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
            .content { padding: 30px; color: #333333; line-height: 1.6; }
            .field { margin-bottom: 20px; }
            .field strong { color: #000976; display: block; margin-bottom: 5px; text-transform: uppercase; font-size: 12px; font-weight: bold; }
            .value { background-color: #f9fafb; padding: 15px; border-radius: 6px; border: 1px solid #e5e7eb; font-size: 14px; }
            .footer { background-color: #f9fafb; padding: 15px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #e5e7eb; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>LearningBox Inquiry</h1>
            </div>
            <div class='content'>
                <p style='font-size: 16px;'>Hello Team,</p>
                <p style='font-size: 15px;'>You have received a new message through the website contact form.</p>
                
                <div class='field'>
                    <strong>Full Name</strong>
                    <div class='value'>{$name}</div>
                </div>
                
                <div class='field'>
                    <strong>Email Address</strong>
                    <div class='value'><a href='mailto:{$email}' style='color: #26AADC; text-decoration: none;'>{$email}</a></div>
                </div>
                
                <div class='field'>
                    <strong>Phone Number</strong>
                    <div class='value'>" . ($phone ? $phone : 'Not provided') . "</div>
                </div>
                
                <div class='field'>
                    <strong>Message</strong>
                    <div class='value'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>
            </div>
            <div class='footer'>
                This is an automated notification from the LearningBox system.
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'learningbox.in';
    $headers .= "From: LearningBox Website <noreply@" . $domain . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";

    // Suppress errors to prevent breaking the JSON response if mail server isn't setup
    @mail($to, $subject, $htmlContent, $headers);

    // Return success
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
} catch (PDOException $e) {
    // Log error internally, do not expose to client
    error_log("DB Query Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred while passing your request. Please try again later.']);
}
?>
