<?php
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "❌ Email invalide.";
    } elseif (!is_numeric($age) || intval($age) <= 0) {
        $error = "❌ Âge invalide.";
    } elseif (strlen($new_password) < 6) {
        $error = "❌ Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $file = 'users.json';

        if (!file_exists($file)) {
            $error = "❌ Fichier utilisateur introuvable.";
        } else {
            $users = json_decode(file_get_contents($file), true);
            $user_found = false;

            foreach ($users as &$user) {
                if ($user['email'] === $email && $user['age'] == $age) {
                    $user['password'] = password_hash($new_password, PASSWORD_DEFAULT);
                    $user_found = true;
                    break;
                }
            }

            if ($user_found) {
                file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
                $success = "✅ Mot de passe mis à jour avec succès. <a href='login.php'>Se connecter</a>";
            } else {
                $error = "❌ Email ou âge incorrect.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="registration.css">
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Réinitialiser le mot de passe</header>
            <form method="POST" action="">
                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="field input">
                    <label for="age">Âge</label>
                    <input type="number" name="age" id="age" required>
                </div>

                <div class="field input">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>

                <div class="field">
                    <input type="submit" class="btn" value="Mettre à jour le mot de passe">
                </div>
            </form>

            <?php if (!empty($error)): ?>
                <p style="color: red; margin-top: 10px;"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <p style="color: green; margin-top: 10px;"><?= $success ?></p>
            <?php endif; ?>

            <div class="links">
                <a href="login.php">Retour à la page de connexion</a>
            </div>
        </div>
    </div>
</body>
</html>
