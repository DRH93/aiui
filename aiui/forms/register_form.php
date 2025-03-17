<div id="registerForm">
    <h2>Registrieren</h2>
    <div class="mb-3">
        <label for="registerEmail" class="form-label">Username</label>
        <input type="text" class="form-control" id="registerEmail" name="registerEmail"  placeholder="Username eingeben...">
    </div>
    <div class="mb-3">
        <label for="registerPassword" class="form-label">Passwort</label>
        <input type="password" class="form-control" id="registerPassword" name="registerPassword" placeholder="Passwort eingeben...">
    </div>
    <button class="btn btn-primary"
            hx-post="../backend/auth/register.php"
            hx-target="#authSection"
            hx-include="#registerEmail,#registerPassword">
        Registrieren
    </button>
    <div class="form-text mt-3">Hast du bereits einen Account? <a href="#" hx-get="../forms/login_form.php" hx-target="#authSection">Hier einloggen</a></div>
</div>
