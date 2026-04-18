<?php
// login.php
session_start();
require 'connexion.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    if (!empty($email) && !empty($password)) {
        $stmt = $bdd->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'blocked') {
                $error = "Votre compte a été bloqué.<br>
                 <a href='mailto:alves@gmail.com' class='alert-link' style='color: inherit; text-decoration: underline;'>Contactez l'administrateur</a> pour en savoir plus";
            } else {
                $_SESSION['id'] = $user['id_users'];
                $_SESSION['nom'] = $user['nom'];
                $_SESSION['prenom'] = $user['prenom'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['status'] = $user['status'];

                header("Location: home.php");
                exit();
            }
        } else {
            $error = "Email ou mot de passe incorrect.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Chat App</title>
    <!-- Google Fonts -->
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/auth.css">
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="auth-container">
            <div class="auth-header">
                <h2><i class="bi bi-shield-lock-fill me-2"></i>Connexion</h2>
                <p>Heureux de vous revoir !</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form action="" method="POST" id="loginForm" autocomplete="off">
                <div class="form-floating mb-4">
                    <input type="email" name="email" class="form-control" id="emailInput" placeholder="name@example.com"
                        required>
                    <label for="emailInput"><i class="bi bi-envelope me-2"></i>Email</label>
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input type="password" name="password" class="form-control" id="passwordInput"
                        placeholder="Mot de passe" required>
                    <label for="passwordInput"><i class="bi bi-key me-2"></i>Mot de passe</label>
                    <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                        <label class="form-check-label text-white-50 small" for="rememberMe">
                            Se souvenir de moi
                        </label>
                    </div>
                    <a href="#" class="small text-white-50">Mot de passe oublié ?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">
                    <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
                    Se connecter
                </button>
            </form>

            <div class="auth-footer">
                Pas encore de compte ? <a href="register.php">Créer un compte</a>
            </div>
        </div>
    </div>

    <script src="../JS/auth.js"></script>
</body>

</html>