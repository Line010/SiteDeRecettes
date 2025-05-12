<?php
session_start();
// Vérifier que la connexion est toujours établit
if (!isset($_SESSION['username'])) {
    echo "Erreur : aucun utilisateur enregistré.";
    exit();
}

$username = $_SESSION['username'];
$file = 'users.json';
$contenu = json_decode(file_get_contents($file), true) ?? [];

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //On récupère la liste des role selectionné
    if (!empty($_POST['selected_role']) && is_array($_POST['selected_role'])) {
        $selected_roles = $_POST['selected_role'];
        foreach ($contenu as &$user) {
            if ($user['username'] === $username) {
                $user['role'] = $selected_roles;
                $_SESSION['role'] = $selected_roles;
                break;
            }
        }
        file_put_contents($file, json_encode($contenu, JSON_PRETTY_PRINT));
        header("Location: ../Homepage/home.php");
        exit();
    } else {
        $error_message = "Veuillez sélectionner au moins un rôle.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Choisir un Rôle</title>
        <link rel="stylesheet" href="registration.css"> <!-- Ton fichier CSS existant -->
    </head>
    <body>
        <div class="container-role">
            <div class="form-box-role">
                <header><h2>Choisir un rôle</h2></header>

                <form method="post">
                <!-- Le role cuisinier est attribué à l'utilisateur mais caché -->
                <input type="hidden" class="role-checkbox" id="role-cuisinier" name="selected_role[]" value="Cuisinier">
                    
                    <div class="image-selection">
                        <!-- On appel la fonction toggleRole pour montrer que le role a été séléctionner -->
                        <label class="image-item" onclick="toggleRole(this, 'role-demandechef')">
                            <input type="checkbox" class="role-checkbox" id="role-demandechef" name="selected_role[]" value="DemandeChef">
                            <img src="Images/chef.png" alt="DemandeChef">
                            <div>DemandeChef</div>
                        </label>

                        <label class="image-item" onclick="toggleRole(this, 'role-demandetraducteur')">
                            <input type="checkbox" class="role-checkbox" id="role-demandetraducteur" name="selected_role[]" value="DemandeTraducteur">
                            <img src="Images/translator.png" alt="DemandeTraducteur">
                            <div>DemandeTraducteur</div>
                        </label>
                    </div>
                    <div id="error-message" class="error"></div>
                    <input type="submit" class="role-btn" value="Valider">
                </form>
            </div>
        </div>

        <script>
            /**
             * Active ou désactive un rôle visuellement et logiquement
             * @param {HTMLElement} label Le container .image-item cliqué
             * @param {string} checkboxId L’ID de la checkbox à cocher
             */
            function toggleRole(label, checkboxId) {
                const checkbox = document.getElementById(checkboxId);
                checkbox.checked = !checkbox.checked;
                label.classList.toggle('selected', checkbox.checked);
            }
        </script>
    </body>
</html>
