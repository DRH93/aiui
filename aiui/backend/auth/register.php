<?php
session_start();

require_once '/var/www/html/aiui/backend/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['registerEmail'] ?? null;
    $password = $_POST['registerPassword'] ?? null;

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

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<div class="text-danger">Username ist schon vergeben.<Bitte Seite neu laden</div><a 
                            class="btn btn-primary" 
                            type="button" 
                            href="/aiui"
                            >
                            Refresh
                            </a>';
        exit;
    }

    // Insert new user into the database
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $passwordHash);

    if ($stmt->execute()) {
        // Fetch the last inserted user ID
        $userId = $conn->insert_id;

        // Login the user by setting session variables
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $userId;

        echo '<div class="text-success">Registrierung erfolgreich! Jetzt geht es zum AI Bot...</div>';
        echo '<script>setTimeout(() => window.location.href = "../", 1000);</script>';
        exit;
    } else {
        echo '<div class="text-danger">An error occurred. Please try again later.</div>';
        exit;
    }
}
?>
