<?php
// api/schedule_consultation.php
// Handles form submissions from schedule-consultation.html

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
    $workEmail = $input['workEmail'] ?? '';
    $phone = $input['phone'] ?? '';
    $companyName = $input['companyName'] ?? '';
    $jobTitle = $input['jobTitle'] ?? '';
    $location = $input['location'] ?? '';
    $description = $input['description'] ?? '';
    $teamSize = $input['teamSize'] ?? '';
    $lookingFor = $input['lookingFor'] ?? '';
    $challenge = $input['challenge'] ?? '';
} else {
    $fullName = $_POST['fullName'] ?? '';
    $workEmail = $_POST['workEmail'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $companyName = $_POST['companyName'] ?? '';
    $jobTitle = $_POST['jobTitle'] ?? '';
    $location = $_POST['location'] ?? '';
    $description = $_POST['description'] ?? '';
    $teamSize = $_POST['teamSize'] ?? '';
    $lookingFor = $_POST['lookingFor'] ?? '';
    $challenge = $_POST['challenge'] ?? '';
}

// Sanitize and Validate
$fullName = trim(filter_var($fullName, FILTER_SANITIZE_STRING));
$workEmail = trim(filter_var($workEmail, FILTER_SANITIZE_EMAIL));
$phone = trim(filter_var($phone, FILTER_SANITIZE_STRING));
$companyName = trim(filter_var($companyName, FILTER_SANITIZE_STRING));
$jobTitle = trim(filter_var($jobTitle, FILTER_SANITIZE_STRING));
$location = trim(filter_var($location, FILTER_SANITIZE_STRING));
$description = trim(filter_var($description, FILTER_SANITIZE_STRING));
$teamSize = trim(filter_var($teamSize, FILTER_SANITIZE_STRING));
$lookingFor = trim(filter_var($lookingFor, FILTER_SANITIZE_STRING));
$challenge = trim(filter_var($challenge, FILTER_SANITIZE_STRING));

if (empty($fullName) || empty($workEmail) || empty($phone) || empty($companyName) || empty($description) || empty($teamSize) || empty($lookingFor) || empty($challenge)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all the required fields.']);
    exit;
}

if (!filter_var($workEmail, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

// Insert into database
require_once dirname(__DIR__) . '/config/db.php';
try {
    $stmt = $pdo->prepare("INSERT INTO consultations (full_name, work_email, phone, company_name, job_title, location, description, team_size, looking_for, challenge, status) VALUES (:fullName, :workEmail, :phone, :companyName, :jobTitle, :location, :description, :teamSize, :lookingFor, :challenge, 'Pending')");
    $stmt->execute([
        ':fullName' => $fullName, 
        ':workEmail' => $workEmail, 
        ':phone' => $phone, 
        ':companyName' => $companyName,
        ':jobTitle' => $jobTitle,
        ':location' => $location,
        ':description' => $description,
        ':teamSize' => $teamSize,
        ':lookingFor' => $lookingFor,
        ':challenge' => $challenge
    ]);
} catch (PDOException $e) {
    error_log("DB Query Error: " . $e->getMessage());
    // Proceeding to send email even if DB fails
}

// Extract First Name
$nameParts = explode(' ', $fullName);
$firstName = $nameParts[0];

// Send Auto-Mailer to User
$to = $workEmail;
$subject = "Consultation Request Received — We’ll connect with You Soon";

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
        .list-items { list-style: none; padding: 0; margin: 20px 0; }
        .list-items li { margin-bottom: 10px; padding-left: 20px; position: relative; }
        .list-items li::before { content: '•'; position: absolute; left: 0; top: 0; font-size: 16px; color: #F4BD18; font-weight: bold; }
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
            <p>Thank you for reaching out to Learning Box.</p>
            <p>We’ve received your request for a consultation, and our team is reviewing your requirements.</p>
            
            <div class='details-box'>
                <p style='margin-top: 0; font-weight: bold; color: #000976;'>Your Submitted Details</p>
                <ul>
                    <li><strong>Name:</strong> {$fullName}</li>
                    <li><strong>Organization:</strong> {$companyName}</li>
                    <li><strong>Consultation Topic:</strong> {$lookingFor}</li>
                    <li><strong>Preferred Meeting Mode:</strong> To be discussed</li>
                </ul>
            </div>
            
            <p>Our team will get in touch with you shortly to confirm the consultation schedule and discuss how we can support your learning and training goals.</p>
            
            <p style='font-weight: bold; color: #000976; font-size: 16px; margin-top: 20px;'>During the consultation, we can help you explore:</p>
            <ul class='list-items'>
                <li>Online Courses</li>
                <li>Employee Training</li>
                <li>Certification Programs</li>
                <li>Custom Development</li>
                <li>Other</li>
            </ul>
            
            <p>We look forward to connecting with you and understanding your requirements better.</p>
        </div>
        <div class='footer'>
            <p>If you need immediate assistance, feel free to contact us.</p>
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
$adminSubject = "New Consultation Request: " . $fullName;
$adminContent = "A new consultation request has been submitted.
Name: $fullName
Email: $workEmail
Phone: $phone
Company: $companyName
Job Title: $jobTitle
Location: $location
Description: $description
Team Size: $teamSize
Looking For: $lookingFor
Challenge: $challenge";
$adminHeaders = "From: Learning Box System <info@learningbox.in>\r\n";
@mail($adminTo, $adminSubject, $adminContent, $adminHeaders, "-finfo@learningbox.in");

// Return success
echo json_encode(['success' => true, 'message' => 'Thank you! Your request has been received. We will contact you soon.']);
?>
