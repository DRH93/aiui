<?php
session_start();
require_once '/var/www/html/aiui/backend/config.php';

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401); // Unauthorized
    exit("User not logged in.");
}

$userId = $_SESSION['user_id']; // Get logged-in user's ID

// Fetch the user's response history from the database
$stmt = $conn->prepare("
    SELECT id, sent_message, aiResponse, created_at
    FROM record_history
    WHERE user_id = ?
    ORDER BY created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Build the response history as HTML
$historyHTML = '<ul class="list-group">';
while ($row = $result->fetch_assoc()) {
    $sentMessage = htmlspecialchars(substr($row['sent_message'], 0, 60));
    $response = htmlspecialchars(substr($row['aiResponse'], 0, 60));
    $timestamp = date('j.n.Y, g:i A', strtotime($row['created_at']));
    $recordId = $row['id']; // Assume this is fetched from the database

    $historyHTML .= "
        <li class='list-group-item'
            data-id='$recordId'
            hx-get='/aiui/backend/api/fetch_record_details.php'
            hx-vals='{\"record_id\": \"" . htmlspecialchars($recordId, ENT_QUOTES) . "\"}'
            hx-swap='none'
            hx-trigger='click'
            style='cursor: pointer;'>
            <strong>Nachricht:</strong> $sentMessage...<br>
            <strong>Antwort:</strong> $response...<br>
            <span class='text-muted'>$timestamp</span>
        </li>
    ";

}
$historyHTML .= '</ul>';

echo $historyHTML;
?>
