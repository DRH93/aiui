<?php
session_start();

// Redirect to main interface if logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@1.9.2"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Simpler AI Bot <small class="text-muted">Userverifizierung</small></h1>
        <div id="authSection" class="border p-3 rounded bg-light">
            <!-- Login Form -->
            <?php include '../forms/login_form.php'; ?>
        </div>
    </div>
</body>
</html>
