<?php
session_start();

require_once '/var/www/html/aiui/backend/config.php';


class OpenAIHandler {
    private $apiKey;

    public function __construct() {
        $this->apiKey = 'OPENAI_API_KEY';
        if (!$this->apiKey) {
            die('API key not set. Please configure OPENAI_API_KEY.');
        }
    }

    public function callOpenAI($userMessage, $parameters) {
        $url = 'https://api.openai.com/v1/chat/completions';
        $parametersString = implode(', ', array_column($parameters, 'parameter_name')); // Combine parameter names

        $data = [
            "model" => "gpt-4o-mini", // Replace with the desired model
            "messages" => [
                ["role" => "system", "content" => "Use these parameters in your response: {$parametersString}."],
                ["role" => "user", "content" => $userMessage]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => curl_error($ch)];
        }

        curl_close($ch);
        return json_decode($response, true);
    }

    public function saveToDatabase($conn, $userId, $userMessage, $parameters, $aiResponse) {

        // Step 1: Save message and response in record_history
        $stmt = $conn->prepare("INSERT INTO record_history (user_id, sent_message, aiResponse) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $userMessage, $aiResponse);
        $stmt->execute();
        $recordId = $conn->insert_id;

        // Step 2: Save parameters in the parameters table
        $stmt = $conn->prepare("INSERT INTO parameters (record_id, parameter_value) VALUES (?, ?)");
        foreach ($parameters as $param) {
            $stmt->bind_param("is", $recordId, $param['parameter_name']);
            $stmt->execute();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$_SESSION['logged_in']) {
        exit;
    }

    $userMessage = $_POST['message'] ?? null;
    $parameters = $_SESSION['active_parameters'] ?? [];
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userMessage) {
        exit;
    }

    $openAI = new OpenAIHandler();
    $response = $openAI->callOpenAI($userMessage, $parameters);

    if (isset($response['choices'][0]['message']['content'])) {
        $aiResponse = $response['choices'][0]['message']['content'];

        // Save data to the database
        $openAI->saveToDatabase($conn, $userId, $userMessage, $parameters, $aiResponse);

        echo json_encode([
            "aiResponse" => '<div class="text-success">' . htmlspecialchars($aiResponse) . '</div>'
        ]);
    } else {
        echo json_encode(["aiResponse" => "<div class='text-danger'>Error: Invalid response from OpenAI API.</div>"]);
    }
}
?>