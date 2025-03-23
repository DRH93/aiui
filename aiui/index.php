<?php
session_start();

// Enhance session security
ini_set('session.cookie_secure', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

// Authentication check
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: public/authentication.php');
    exit;
}

// Regenerate session ID on login
if (!isset($_SESSION['session_initialized'])) {
    session_regenerate_id(true);
    $_SESSION['session_initialized'] = true;
}

$email = htmlspecialchars($_SESSION['loginEmail'], ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homemade AI Chatbot</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.2.4/purify.js" integrity="sha512-gz6WNAiOHmX2hYyqxZLXxWn/tnyWxrvDa8v820IMtAJf5vAvVkbEP6zRxgSab0oID9rMxqzZdAjbFpZrNRaBUw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</head>
<body>
    <div class="container mt-5">

        <!-- Main Content Section -->
        <main id="Content">
            <h1 class="display-4">AI Bot</h1><hr>

            <div class="row mb-4">
                <div class="col-12 col-md-6 d-flex flex-column">
                    <!-- Message Input -->
                    <div class="mb-4">
                        <h2>Nachricht</h2>
                        <h5 class="mb-2 text-black-50">Was willst du wissen?</h5>
                        <textarea class="form-control" id="messageInput" rows="4" placeholder="Nachricht hier eintippen"></textarea>
                    </div>
                    <!-- Input for Parameters -->
                    <div class="mb-4">
                        <h2 >Parameters</h2>
                        <h5 class="mb-2 text-black-50"> Was soll der AI Bot in seiner Antwort beachten?</h5>
                        <div class="row mb-4">
                            <div class="col-10">
                            <input class="form-control" id="parameterInput" name="parameterInput" placeholder="Parameter hier eintippen" 
                                   hx-post="/aiui/backend/api/manage_parameters.php" 
                                   hx-trigger="keyup[target.value.length > 0 && event.key === 'Enter']"
                                   hx-swap="beforeend" 
                                   hx-target="#parameterList">
                            </div>
                            <button 
                            class="btn btn-outline-primary col-2" 
                            type="button" 
                            id="parameterButton" 
                            name="parameterButton"
                            hx-post="/aiui/backend/api/manage_parameters.php" 
                            hx-swap="beforeend" 
                            hx-target="#parameterList"
                            >
                            Hinzufügen
                            </button>
                        </div>
                        <div class="form-text mb-2">"Ich bin <?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>", "Sei immer höflich", etc.</div>
                             <!-- Display Added Parameters -->
                        <div id="parameterList" 
                                 hx-get="/aiui/backend/api/manage_parameters.php" 
                                 hx-swap="innerHTML" 
                                 hx-trigger="load">
                                <!-- Parameters will be dynamically fetched here -->
                        </div>
                    </div>
                </div>

                <!-- Right Column: Response History -->
                <div class="col-12 col-md-6 order-1 order-md-2">
                    <h2>Meine Fragen</h2>
                    <h5 class="mb-2 text-black-50">Bisherige Fragen an den Bot. Klick darauf für Details...</h5>
                    <div id="responseHistory" 
                         hx-get="/aiui/backend/api/manage_history.php" 
                         hx-swap="innerHTML" 
                         hx-trigger="load, after:htmx:afterSwap"
                         class="border p-3 rounded bg-light" 
                         style="min-height: 100px; max-height: 300px; overflow-y: auto;">
                        <!-- History will be dynamically loaded here -->
                    </div>
                </div>
            </div>

            <!-- Button to Trigger OpenAI Call -->
            <div class="row mt-4">
                <div class="col-12 col-md-6">
                    <button id="aiRequestButton" class="btn btn-primary"
                            hx-post="/aiui/backend/api/create_response.php"
                            hx-on="click: this.setAttribute('hx-vals', JSON.stringify({ 
                                            message: document.getElementById('messageInput').value,
                                            identity: ''
                                        }))"
                            hx-target="#aiResponse">
                        Fragen
                    </button>
                </div>
            </div>

            <!-- AI Response Section -->
            <div id="aiResponse" class="mt-4 border p-3 rounded bg-light">
                <h5>AI Bot:</h5>
                <div id="responseContent">Die Antwort erscheint hier.</div>
            </div>
        </main>
    </div>
    

    <!-- HTMX and JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@1.9.2"></script>
    <script>

        function sanitizeInput(input) {
            return input.replace(/[^a-zA-Z0-9 ]/g, ''); // Remove any special characters
        }

        document.getElementById('parameterInput').addEventListener('keyup', function (event) {
            const sanitizedValue = sanitizeInput(this.value.trim());
            if (event.key === 'Enter' && sanitizedValue !== '') {
                this.setAttribute('hx-vals', JSON.stringify({
                    action: 'add',
                    parameterInput: sanitizedValue
                }));
                document.getElementById('parameterButton').setAttribute('hx-vals', JSON.stringify({
                    action: 'add',
                    parameterInput: sanitizedValue
                }));
            }
        });


        // Add event listener for parameterButton click
        document.getElementById('parameterButton').addEventListener('click', function () {
            const parameterInputValue = document.getElementById('parameterInput').value.trim();
            if (parameterInputValue !== '') {
                // Dynamically set hx-vals before the HTMX request
                this.setAttribute('hx-vals', JSON.stringify({
                    action: 'add',
                    parameterInput: parameterInputValue
                }));
            }
        });

        document.addEventListener("htmx:configRequest", function (event) {
            const button = document.getElementById("aiRequestButton");
            const aiResponseDiv = document.getElementById("aiResponse");
            if (event.target === button && aiResponseDiv) {
                button.disabled = true;

                // Create and append loading animation
                aiResponseDiv.textContent = "";
                const loadingDiv = document.createElement("div");
                loadingDiv.className = "d-flex align-items-center";
                loadingDiv.innerHTML = `
                    <strong>Einen Moment Bitte. Das sollte ganz zackig gehen...</strong>
                    <div class="spinner-border ms-3" role="status" aria-hidden="true"></div>
                `;
                aiResponseDiv.appendChild(loadingDiv);
            }
        });


        function sanitizeHTML(str) {
            const temp = document.createElement('div');
            temp.textContent = str; // Automatically escapes potentially dangerous content
            return temp.innerHTML;
        }

        document.addEventListener("htmx:afterRequest", function (event) {
            // Check if the event target is the Get AI Response button
            const button = document.getElementById("aiRequestButton");
            if (event.target === button) {
            // Parse the JSON response
                        const response = JSON.parse(event.detail.xhr.responseText);

                        // Update the AI response field
                        if (response.aiResponse) {
                            const cleanHTML = DOMPurify.sanitize(response.aiResponse);
                            document.getElementById("aiResponse").innerHTML = cleanHTML;
                        }

                        // Update the response history
                        if (response.historyHTML) {
                            document.getElementById("responseHistory").innerHTML = response.historyHTML;
                        }

                // Re-enable the button after the request completes
                button.disabled = false;

                htmx.ajax('GET', '/aiui/backend/api/manage_history.php', { target: '#responseHistory' });

            }

            // Clear the parameter input field
            const inputField = document.getElementById('parameterInput');
            if (inputField) {
                inputField.value = '';
            }
        });

        document.addEventListener("htmx:afterSwap", function (event) {
            // Check if the event target is a list-group-item response (fetch_record_details.php response)
            if (event.detail.requestConfig && event.detail.requestConfig.path.includes('fetch_record_details.php')) {
                const data = JSON.parse(event.detail.xhr.responseText);

                // Update the input field with the sent message
                document.getElementById("messageInput").value = data.sent_message;

                // Update the AI response
                const aiResponse = document.getElementById("aiResponse");
                aiResponse.innerHTML = `<h5>AI Bot:</h5><div id="responseContent" class="mt-2">${data.ai_response}</div>`;

                htmx.ajax('GET', '/aiui/backend/api/manage_parameters.php', { target: '#parameterList' });

            }
        });

        // Highlight the selected list item
        document.addEventListener("click", function (event) {
            if (event.target.classList.contains("list-group-item")) {
                // Remove highlight from all items
                document.querySelectorAll(".list-group-item").forEach(item => {
                    item.classList.remove("bg-dark", "text-white");
                });

                // Add highlight to the selected item
                event.target.classList.add("bg-dark", "text-white");
            }
        });

    </script>
</body>
</html>
