<?php
// register.php
session_start();
require 'connexion.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $bio = ''; // Removed
    $pseudo = htmlspecialchars($_POST['pseudo']);

    // Photo upload
    $photo = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['photo']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        if (in_array(strtolower($filetype), $allowed)) {
            $newFilename = uniqid() . '.' . $filetype;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], '../images/' . $newFilename)) {
                $photo = $newFilename;
            } else {
                $error = "Erreur lors de l'upload de l'image.";
            }
        } else {
            $error = "Type de fichier non autorisé.";
        }
    }

    if (empty($error)) {
        // Check if email exists
        $check = $bdd->prepare("SELECT id_users FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert = $bdd->prepare("INSERT INTO users (nom, prenom, email, password, pseudo, photo) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insert->execute([$nom, $prenom, $email, $hashed_password, $pseudo, $photo])) {
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            } else {
                $error = "Une erreur est survenue lors de l'inscription.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Chat App</title>
    <link rel="stylesheet" href="../CSS/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/auth.css">
</head>

<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100 py-5">
        <div class="auth-container register-landscape">
            <div class="auth-header">
                <h2><i class="bi bi-person-plus-fill me-2"></i>Inscription</h2>
                <p>Créez votre compte pour commencer à chater</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                        <?= $success ?> <br>
                        <a href="login.php" class="text-success fw-bold">Se connecter maintenant</a>
                    </div>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" id="registerForm" autocomplete="off">
                <div class="row">
                    <!-- Column 1 -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" name="nom" class="form-control" id="nomInput" placeholder="Nom" required>
                            <label for="nomInput"><i class="bi bi-person me-2"></i>Nom</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="prenom" class="form-control" id="prenomInput" placeholder="Prénom"
                                required>
                            <label for="prenomInput"><i class="bi bi-person me-2"></i>Prénom</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="text" name="pseudo" class="form-control" id="pseudoInput" placeholder="Pseudo"
                                required>
                            <label for="pseudoInput"><i class="bi bi-at me-2"></i>Pseudo</label>
                        </div>
                    </div>

                    <!-- Column 2 -->
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" name="email" class="form-control" id="emailInput"
                                placeholder="name@example.com" required>
                            <label for="emailInput"><i class="bi bi-envelope me-2"></i>Email</label>
                        </div>

                        <div class="form-floating mb-3 position-relative">
                            <input type="password" name="password" class="form-control" id="passwordInput"
                                placeholder="Mot de passe" required>
                            <label for="passwordInput"><i class="bi bi-key me-2"></i>Mot de passe</label>
                            <i class="bi bi-eye password-toggle" id="togglePassword"></i>
                        </div>

                        <div class="mb-3">
                            <div class="file-input-wrapper">
                                <input type="file" name="photo" id="photoInput" class="d-none">
                                <label for="photoInput" class="w-100 mb-0" style="cursor: pointer;">
                                    <div class="text-center py-1" id="fileNameDisplay">
                                        <i class="bi bi-cloud-arrow-up fs-5 mb-1"></i>
                                        <p class="small mb-0">Choisir une photo (Optionnel)</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-2">
                    <button type="submit" class="btn btn-primary btn-lg" style="max-width: 300px;">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"
                            aria-hidden="true"></span>
                        S'inscrire
                    </button>
                </div>
            </form>

            <div class="auth-footer">
                Déjà inscrit ? <a href="login.php">Se connecter</a>
            </div>
        </div>
    </div>

    <script src="../JS/auth.js"></script>
</body>

</html>