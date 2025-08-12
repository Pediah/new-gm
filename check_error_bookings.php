<?php
require_once 'connection.php';

$practice_email = "paulpmeck@gmail.com";

// Get error bookings from last 24 hours
$stmt = $conn->prepare("SELECT * FROM error_bookings WHERE created_at >= NOW() - INTERVAL 1 DAY");
$stmt->execute();
$errors = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($errors) {
    $message = "The following bookings failed in the last 24 hours:\n\n";
    foreach ($errors as $row) {
        $message .= "Name: {$row['names']}\nEmail: {$row['email']}\nPhone: {$row['phone']}\nGroup: {$row['group_type']}\nError: {$row['error_message']}\nDate: {$row['created_at']}\n\n";
    }

    $subject = "Daily Error Bookings Report - GM Gamoo Psychometrists";
    $headers = "From: GM Gamoo Psychometrists <$practice_email>\r\nContent-Type: text/plain; charset=UTF-8";

    mail($practice_email, $subject, $message, $headers);
}
?>
use sm