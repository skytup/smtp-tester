<?php
header('Content-Type: application/json');

// Enable error reporting for development
ini_set('display_errors', 0);
error_reporting(0);

// Allow CORS for your domain only
header("Access-Control-Allow-Origin: *");//https://www.skytup.com
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// If it's a preflight request, exit
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Ensure request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

// Validate required fields based on action
function validateFields($input, $required) {
    foreach ($required as $field) {
        if (empty($input[$field])) {
            return false;
        }
    }
    return true;
}

// Sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Function to test SMTP connection
function testSMTPConnection($host, $username, $password, $port) {
    try {
        $smtp = fsockopen($host, $port, $errno, $errstr, 30);
        if (!$smtp) {
            throw new Exception("Could not connect to SMTP server");
        }
        fclose($smtp);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Function to send email using PHPMailer
function sendEmail($config) {
    require 'vendor/autoload.php'; // Make sure PHPMailer is installed

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['username'];
        $mail->Password = $config['password'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $config['port'];

        // Recipients
        $mail->setFrom($config['username']);
        $mail->addAddress($config['to']);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $config['subject'];
        $mail->Body = $config['message'];
        $mail->AltBody = strip_tags($config['message']);

        $mail->send();
        return true;
    } catch (Exception $e) {
        throw new Exception($mail->ErrorInfo);
    }
}

// Handle different actions
try {
    switch ($input['action']) {
        case 'test_connection':
            $required = ['host', 'username', 'password', 'port'];
            if (!validateFields($input, $required)) {
                throw new Exception('Missing required fields');
            }

            $host = sanitizeInput($input['host']);
            $username = sanitizeInput($input['username']);
            $password = $input['password']; // Don't sanitize password
            $port = (int)$input['port'];

            if (!testSMTPConnection($host, $username, $password, $port)) {
                throw new Exception('Failed to connect to SMTP server');
            }

            echo json_encode(['success' => true, 'message' => 'Connection successful']);
            break;

        case 'send_email':
            $required = ['host', 'username', 'password', 'port', 'to', 'subject', 'message'];
            if (!validateFields($input, $required)) {
                throw new Exception('Missing required fields');
            }

            $config = [
                'host' => sanitizeInput($input['host']),
                'username' => sanitizeInput($input['username']),
                'password' => $input['password'],
                'port' => (int)$input['port'],
                'to' => sanitizeInput($input['to']),
                'subject' => sanitizeInput($input['subject']),
                'message' => sanitizeInput($input['message'])
            ];

            if (!filter_var($config['to'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid recipient email address');
            }

            if (!sendEmail($config)) {
                throw new Exception('Failed to send email');
            }

            echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>