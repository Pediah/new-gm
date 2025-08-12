<?php

require 'config.php';      // Defines SMTP_HOST, SMTP_USERNAME, etc.
require 'connection.php';

// Manually require PHPMailer classes
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Now you can create and use PHPMailer objects
$mail = new PHPMailer(true);

// ... rest of your mail sending code

// Get form data safely
$names      = htmlspecialchars(trim($_POST['Names'] ?? ''));
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone      = htmlspecialchars(trim($_POST['phone'] ?? ''));
$group_type = htmlspecialchars(trim($_POST['group_type'] ?? ''));
$comments   = htmlspecialchars(trim($_POST['comments'] ?? ''));

// Honeypot anti-spam
if (!empty($_POST['website'])) {
    header("Location: thank_you.html");
    exit;
}

try {
    // Email to Practice
    $mailPractice = new PHPMailer(true);
    $mailPractice->isSMTP();
    $mailPractice->Host       = SMTP_HOST;
    $mailPractice->SMTPAuth   = true;
    $mailPractice->Username   = SMTP_USERNAME;
    $mailPractice->Password   = SMTP_PASSWORD;
    $mailPractice->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailPractice->Port       = SMTP_PORT;

    $mailPractice->setFrom(SMTP_FROM_EMAIL, 'GM Gamoo Psychometrists');
    $mailPractice->addAddress(ERROR_REPORT_EMAIL, ERROR_REPORT_NAME);
    $mailPractice->addReplyTo($email, $names);
    $mailPractice->Subject = "New Appointment Request from $names";
    $mailPractice->Body    = "New appointment request:\n\n"
                           . "Name: $names\nEmail: $email\nPhone: $phone\nGroup: $group_type\nComments:\n$comments";
    $mailPractice->send();

    // Email to Client
    $mailClient = new PHPMailer(true);
    $mailClient->isSMTP();
    $mailClient->Host       = SMTP_HOST;
    $mailClient->SMTPAuth   = true;
    $mailClient->Username   = SMTP_USERNAME;
    $mailClient->Password   = SMTP_PASSWORD;
    $mailClient->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mailClient->Port       = SMTP_PORT;

    $mailClient->setFrom(SMTP_FROM_EMAIL, 'GM Gamoo Psychometrists');
    $mailClient->addAddress($email, $names);
    $mailClient->Subject = "Your Appointment Request - GM Gamoo Psychometrists";
    $mailClient->Body    = "Hello $names,\n\nThank you for contacting us. We received your request and will follow up shortly.\n\nRegards,\nGM Gamoo Psychometrists";
    $mailClient->send();

    // Save booking to database
    $stmt = $conn->prepare("INSERT INTO bookings (names, email, phone, group_type, comments) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$names, $email, $phone, $group_type, $comments]);

    header("Location: thank_you.html");
    exit;

} catch (Exception $e) {
    // Log error booking to error_bookings table
    $stmt = $conn->prepare("INSERT INTO error_bookings (names, email, phone, group_type, comments, error_message) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$names, $email, $phone, $group_type, $comments, $e->getMessage()]);

    header("Location: thank_you.html");
    exit;
}
