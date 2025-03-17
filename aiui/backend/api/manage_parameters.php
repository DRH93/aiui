<?php
session_start();

class ParameterHandler {
    public function addParameter($input) {
        // Validate input length and characters
        if (empty($input)) {
            return 'Kein Parameter eingegeben';
        }

        if (strlen($input) > 255) {
            return 'Parameter is too long. Maximum 255 characters allowed.';
        }

        if (!preg_match("/^[\p{L}0-9 ]+$/u", $input)) {
            return 'Ungültiges Parameterformat. Nur Buchstaben, Zahlen und Leerzeichen sind erlaubt.';
        }

        // Store in session (active parameters)
        if (!isset($_SESSION['active_parameters'])) {
            $_SESSION['active_parameters'] = [];
        }

        $_SESSION['active_parameters'][] = [
            'parameter_name' => htmlspecialchars($input, ENT_QUOTES, 'UTF-8'),
        ];

        $index = count($_SESSION['active_parameters']) - 1;

        return '<div id="parameter-' . $index . '" class="badge bg-light text-primary me-2 d-inline-block">'
            . htmlspecialchars($input)
            . ' <button class="btn-close btn-close-primary ms-1" aria-label="Close" '
            . 'hx-post="/aiui/backend/api/manage_parameters.php" '
            . 'hx-vals=\'' . htmlspecialchars(json_encode(["action" => "remove", "index" => $index]), ENT_QUOTES) . '\''
            . 'hx-swap="delete" '
            . 'hx-target="#parameter-' . $index . '"></button></div>';
    }

    public function removeParameter($index) {
        if ($index !== null && isset($_SESSION['active_parameters'][$index])) {
            unset($_SESSION['active_parameters'][$index]);
            // Check if the session array is empty after removal
            if (empty($_SESSION['active_parameters'])) {
                $_SESSION['active_parameters'] = []; // Ensure it's explicitly an empty array
            }            
            var_dump($_SESSION['active_parameters']);
            http_response_code(200); // Return 200 OK
        } else {
            http_response_code(400);
            echo "An error occurred. Please try again."; // Generic error message
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Render all active parameters from session
    if (isset($_SESSION['active_parameters']) && !empty($_SESSION['active_parameters'])) {
        foreach ($_SESSION['active_parameters'] as $index => $parameter) {
            echo '<div id="parameter-' . $index . '" class="badge bg-light text-primary me-2 d-inline-block">'
                . htmlspecialchars($parameter['parameter_name'])
                . ' <button class="btn-close btn-close-primary ms-1" aria-label="Close" '
                . 'hx-post="/aiui/backend/api/manage_parameters.php" '
                . 'hx-vals=\'{"action": "remove", "index": ' . $index . '}\' '
                . 'hx-swap="delete" '
                . 'hx-target="#parameter-' . $index . '"></button></div>';
        }
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if (!$action) {
        http_response_code(400);
        echo "Invalid action.";
        exit;
    }

    $parameterHandler = new ParameterHandler();

    if ($action === 'add') {
        $parameterInput = $_POST['parameterInput'] ?? '';
        echo $parameterHandler->addParameter($parameterInput);
    } elseif ($action === 'remove') {
        $index = $_POST['index'] ?? null;
        if ($index === null || !is_numeric($index)) {
            http_response_code(400);
            echo "Invalid index.";
            exit;
        }
        $parameterHandler->removeParameter($index);
    } else {
        http_response_code(400);
        echo "Invalid action.";
    }
}
?>
