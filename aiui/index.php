<?php
session_start();

// var_dump($_SESSION['active_parameters']);
if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
   header('Location: public/authentication.php');
   exit;
}

$email = $_SESSION['loginEmail'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homemade AI Chatbot</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">

        <!-- Main Content Section -->
        <div id="Content">
            <h1 class="display-4">AI Bot</h1><hr>

            <div class="row mb-4">
                <div class="col-12 col-md-6 d-flex flex-column">
                    <!-- Message Input -->
                    <div class="mb-4">
                        <h2>Nachricht</h2>
                        <h5 class="mb-2" style="color:lightgrey;">Was willst du wissen?</h5>
                        <textarea class="form-control" id="messageInput" rows="4" placeholder="Nachricht hier eintippen"></textarea>
                    </div>
                    <!-- Input for Parameters -->
                    <div class="mb-4">
                        <h2 >Parameters</h2>
                        <h5 class="mb-2" style="color:lightgrey;"> Was soll der AI Bot in seiner Antwort beachten?</h5>
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
                        <div class="form-text mb-2">"Ich bin <?php echo $email ?>", "Sei immer höflich", etc.</div>
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
                    <h5 class="mb-2" style="color:lightgrey">Bisherige Fragen an den Bot. Klick darauf für Details...</h5>
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
        </div>
    </div>
    

    <!-- HTMX and JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@1.9.2"></script>
    <script>

    /*
        document.addEventListener("keyup", function (event) {

            if (event.key === "Enter") {
                console.log("first function");
                const input = document.getElementById("parameterInput");
                const vals = JSON.stringify({ action: "add", parameterInput: input.value });
                console.log("hx-vals:", vals); // Check the JSON structure here
            }
        });
    */

        // Add event listeners for dynamically setting hx-vals
        document.getElementById('parameterInput').addEventListener('keyup', function (event) {
            console.log("Second function triggered by Enter key");
            if (event.key === 'Enter' && this.value.trim() !== '') {
                // Dynamically set hx-vals before the HTMX request
                this.setAttribute('hx-vals', JSON.stringify({
                    action: 'add',
                    parameterInput: this.value
                }));
                document.getElementById('parameterButton').setAttribute('hx-vals', JSON.stringify({
                    action: 'add',
                    parameterInput: this.value
                }));
            }
        });

        // Add event listener for parameterButton click
        document.getElementById('parameterButton').addEventListener('click', function () {
            console.log("Parameter button clicked");
            const parameterInputValue = document.getElementById('parameterInput').value.trim();
            if (parameterInputValue !== '') {
                // Dynamically set hx-vals before the HTMX request
                this.setAttribute('hx-vals', JSON.stringify({
                    action: 'add',
                    parameterInput: parameterInputValue
                }));
            }
        });

        // Add HTMX event listeners for button control and loading animation
        document.addEventListener("htmx:configRequest", function (event) {
           // Check if the event target is the Get AI Response button
            const button = document.getElementById("aiRequestButton");
            if (event.target === button) {
                // Disable the button when the request starts
                button.disabled = true;
                // Add loading animation to the AI response section
                const aiResponseDiv = document.getElementById("aiResponse");
                aiResponseDiv.innerHTML = `
                    <h5>AI Response:</h5>
                    <div class="d-flex align-items-center">
                        <strong>Laden! Das sollte ganz zackig gehen...</strong>
                        <div class="spinner-border ms-3" role="status" aria-hidden="true"></div>
                    </div>
                `;
            }
        });

        document.addEventListener("htmx:afterRequest", function (event) {

            // Check if the event target is the Get AI Response button
            const button = document.getElementById("aiRequestButton");
            if (event.target === button) {
            // Parse the JSON response
                        const response = JSON.parse(event.detail.xhr.responseText);

                        // Update the AI response field
                        if (response.aiResponse) {
                            document.getElementById("aiResponse").innerHTML = response.aiResponse;
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

                /* Populate the parameters list -> active parameters stored in session
                const parameterList = document.getElementById("parameterList");
                parameterList.innerHTML = ""; // Clear the current list
                data.parameters.forEach(parameter => {
                    const paramDiv = document.createElement("div");
                    paramDiv.className = "badge bg-primary text-white me-2";
                    paramDiv.innerText = parameter;
                    parameterList.appendChild(paramDiv);
                });
                */

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
