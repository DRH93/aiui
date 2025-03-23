<?php
session_start();

require_once '/var/www/html/aiui/backend/config.php';
//require_once __DIR__ . '/config.php';

// Check if CSRF token is valid
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403); // Forbidden
    die("CSRF token validation failed.");
}

// Invalidate old CSRF token
unset($_SESSION['csrf_token']);

// Generate a new token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['loginEmail'] ?? null;
    $password = $_POST['loginPassword'] ?? null;

    if (!$email || !$password) {
        echo '<div class="text-danger mb-3">Ups! Password oder Username fehlt. Bitte Seite neu laden</div><a 
                            class="btn btn-primary" 
                            type="button" 
                            href="/aiui"
                            >
                            Refresh
                            </a>';
        exit;
    }

    // Fetch the user from the database
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$email || !password_verify($password, $user['password_hash'])) {
        echo '<div class="text-danger">Ungültiges Passwort oder Username. Bitte Seite neu laden</div>   <a 
                            class="btn btn-primary" 
                            type="button" 
                            href="/aiui"
                            >
                            Refresh
                            </a>';
        exit;
    }

    // Login successful
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['loginEmail'] = $email;
    echo '<div class="text-success">Login erfolgreich! Jetzt geht es zum AI Bot...</div>';
    echo '<script>setTimeout(() => window.location.href = "../", 1000);</script>';
    exit;
}
?>
