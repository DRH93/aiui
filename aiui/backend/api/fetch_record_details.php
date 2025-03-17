<?php
session_start();
require_once '/var/www/html/aiui/backend/config.php';
// require_once '/var/www/html/AiUi/backend/api/manage_parameters.php'; // to add parameters directly

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    http_response_code(401); // Unauthorized
    exit("User not logged in.");
}

$recordId = $_GET['record_id'] ?? null;

if (!$recordId || !is_numeric($recordId)) {
    http_response_code(400);
    var_dump($recordId);
    exit("Invalid record ID.");
}

// Fetch the message and AI response
$stmt = $conn->prepare("
    SELECT rh.sent_message, rh.aiResponse, p.parameter_value
    FROM record_history rh
    LEFT JOIN parameters p ON rh.id = p.record_id
    WHERE rh.id = ?
");
$stmt->bind_param("i", $recordId);
$stmt->execute();
$result = $stmt->get_result();

// Build the response
$data = [
    'sent_message' => '',
    'ai_response' => '',
    'parameters' => []
];

while ($row = $result->fetch_assoc()) {
    $data['sent_message'] = $row['sent_message'];
    $data['ai_response'] = $row['aiResponse'];
    if ($row['parameter_value']) {
        $data['parameters'][] = $row['parameter_value'];
    }
}


// $parameterHandler = new ParameterHandler();

// Reset Active Parameters in the session
$_SESSION['active_parameters'] = [];
foreach ($data['parameters'] as $parameter) {
    $_SESSION['active_parameters'][] = [
        'parameter_name' => htmlspecialchars($parameter, ENT_QUOTES, 'UTF-8')
    ];
}

// Return the data as JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
