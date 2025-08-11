<?php
include('header.php');

header('Content-Type: application/json'); // Send JSON header

// Connect to DB
$mysqli = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);

if ($mysqli->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'DB connection failed']);
    exit;
}

session_start();

if (!empty($_POST['username']) && !empty($_POST['password'])) {
    $username = $mysqli->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Fetch user by username
    $result = $mysqli->query("SELECT * FROM `users` WHERE username='$username' LIMIT 1");

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if password is hashed with password_hash
        if (password_verify($password, $user['password'])) {
            $_SESSION['login_username'] = $user['username'];
            echo json_encode(['status' => 'success']);
        } else {
            // If password stored as MD5 (legacy)
            if (md5($password) === $user['password']) {
                $_SESSION['login_username'] = $user['username'];
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'fail', 'message' => 'Invalid password']);
            }
        }
    } else {
        echo json_encode(['status' => 'fail', 'message' => 'User not found']);
    }
} else {
    echo json_encode(['status' => 'fail', 'message' => 'Username and password required']);
}

include('footer.php');
?>
