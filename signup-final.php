<?php
    require_once 'modules/header.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            $_SESSION['alert'] = "Session expired or invalid request. Please try again.";
            header("Location: /ProiectDaw/signup-final.php");
            exit();
        }

        // Sanitize inputs
        $code = sanitize_input($_POST['code']);
        $password = trim($_POST['password']);
        $confirmPassword = trim($_POST['confirm_password']);

        // Validam codul de verificare
        if (!isset($_SESSION['security_code']) || $_SESSION['security_code'] != $code || time() > $_SESSION['security_code_expiry']) {
            $_SESSION['alert'] = "Cod de verificare invalid sau expirat.";
            header("Location: /ProiectDaw/signup-final.php");
            exit();
        }

        // Validam parolele
        if ($password !== $confirmPassword) {
            $_SESSION['alert'] = "Parolele nu se potrivesc.";
            header("Location: /ProiectDaw/signup-final.php");
            exit();
        }

        if (!validate_password($password)) {
            $_SESSION['alert'] = "Parola este prea simpla (1 litera mica, 1 litera mare, 1 cifra si un alt semn).";
            header("Location: /ProiectDaw/signup-final.php");
            exit();
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Cream utilizatorul
        $db = DB::getInstance();
        $userId = $db->createUser($_SESSION['name'], $_SESSION['email'], $hashedPassword);
        
        if ($userId) {
            // Logam userul
            $session->new_session($userId);

            // Redirect -> user-login.php
            header("Location: /ProiectDaw/user-login.php");
            exit();
        } else {
            $_SESSION['alert'] = "Nu s-a putut crea utilizatorul. Va rugm reincercati.";
            header("Location: /ProiectDaw/signup-final.php");
            exit();
        }
    }
?>

<div class="login-form">
    
    <h3><?= $alert ?></h3>

    <form method="POST" action="signup-final.php">
        <table>
            <tbody>
                <tr>
                    <td>
                        <label for="code">Cod de verificare:</label>
                    </td>
                    <td>
                        <input type="text" id="code" name="code" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="password">Parola:</label>
                    </td>
                    <td>
                        <input type="password" id="password" name="password" required>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="confirm_password">Confirma parla:</label>
                    </td>
                    <td>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

        <button type="submit" class="event-buton-rezervare">Create Account</button>
    </form>
</div>


<?php
    include_once 'modules/footer.php';     
?>