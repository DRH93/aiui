<div id="loginForm">
    <h2>Login</h2>
    <div class="mb-3">
       <label for="loginUsername" class="form-label">Username</label>
       <input type="text" class="form-control" id="loginEmail" name="loginEmail" placeholder="Username eingeben...">
    </div>
    <div class="mb-3">
        <label for="loginPassword" class="form-label">Passwort</label>
        <input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="Passwort eingeben...">
    </div>
    <button class="btn btn-primary"
        hx-post="../backend/auth/login.php"
        hx-target="#authSection"
        hx-include="#loginEmail,#loginPassword">
        Einloggen
    </button>
    <div class="form-text mt-3">Noch kein Account? <a href="#" hx-get="../forms/register_form.php" hx-target="#authSection">Registriere dich hier</a></div>
</div>