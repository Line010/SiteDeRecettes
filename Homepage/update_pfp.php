<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Utilisateur non connecté']);
    exit();
}

// Vérifier si les données sont envoyées par POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = '../Registration/users.json';
    
    // Charger les utilisateurs depuis le fichier JSON
    $json_object = file_get_contents($file);
    $users = json_decode($json_object, true);
    
    // Récupérer les données du formulaire
    $updated_user = [
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'age' => $_POST['age'],
        'role' => is_array($_POST['role']) ? $_POST['role'] : [$_POST['role']]

    ];
    // Mettre à jour les informations de l'utilisateur dans le tableau
    foreach ($users as &$user) {
        //Puisque le nom de l'utilisateur pourrait etre changé on vérifie avec celui de la session
        if ($user['username'] === $_SESSION['username']) {
            echo $_SESSION['username'];
            // On met à jour les données de l'utilisateur
            $user['username'] = $updated_user['username'];
            $user['email'] = $updated_user['email'];
            $user['age'] = $updated_user['age'];
            $user['role'] = $updated_user['role'];
            
            // Mettre à jour les données de la session pour l'affichage des donnés changés
            $_SESSION['username'] = $updated_user['username'];
            $_SESSION['email'] = $updated_user['email'];
            $_SESSION['age'] = $updated_user['age'];
            $_SESSION['role'] = $updated_user['role'];  
        }
    }
    file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));
    
    // On est redirigé vers la page profile mais cette fois ci mis à jour
    header("Location: profile.php");
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Modifier le Profil</title>
        <link rel="stylesheet" href="../Registration/registration.css">
    </head>
    <body>

        <div id="background-music-container"></div>
        <script>
            if (!document.getElementById('backgroundMusic')) {
                fetch('background-music.html')
                    .then(response => response.text())
                    .then(data => {
                        document.getElementById('background-music-container').innerHTML = data;

                        const music = document.getElementById("backgroundMusic");

                        const savedTime = localStorage.getItem("musicTime");
                        if (savedTime) {
                            music.currentTime = parseFloat(savedTime);
                        }
                        music.play().catch(err => {
                            console.warn("Autoplay might be blocked:", err);
                        });
                        setInterval(() => {
                            if (!music.paused) {
                                localStorage.setItem("musicTime", music.currentTime);
                            }
                        }, 1000);
                    })
                    .catch(error => {
                        console.error('Error loading background music:', error);
                    });
            }
        </script>

        <div class="container">
            <div class="box">
                <div class="profile-header">
                    <!-- Image "Back" à gauche -->
                    <a href = "profile.php"> 
                        <img src="../Registration/Images/backbtn.png" alt="Back" width="40" height="40">
                    </a>
                    <!-- Centré l'image de profil et titre -->
                    <div class="center-content">
                        <img src="../Registration/Images/profilee.png" alt="Profile" width="25" height="25">
                        <div class="profile-title">Modifier le profil</div>  
                    </div>
                </div>
                
                <form method="POST" action="update_pfp.php">
                    <div class="profile">Nom d'utilisateur:
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>

                    <div class="profile">E-mail:
                        <!-- Pour forcer l'utilisateur a tapé un email correcte on ajoute un id email -->
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" required>
                    </div>

                    <div class="profile">Âge:
                        <!-- Pour forcer l'utilisateur a tapé un age correcte on ajoute un id age -->
                        <input type="number" id="age" name="age" value="<?php echo htmlspecialchars($_SESSION['age']); ?>" 
                        required>
                    </div>

                    <div class="profile">Rôle:
                        <!-- Le role cuisinier est toujours attribué à l'utilisateur mais caché -->
                        <input type="hidden" name="role[]" value="Cuisinier"<?php echo (isset($_SESSION['role']) && in_array("DemandeChef", $_SESSION['role'])) ? 
                                    "checked" : ""; ?>>
                        <div>
                            <label>
                                <input type="checkbox" name="role[]" value="DemandeChef"
                                    <?php echo (isset($_SESSION['role']) && in_array("DemandeChef", $_SESSION['role'])) ? 
                                    "checked" : ""; ?>>
                                DemandeChef
                            </label>
                        </div>
                        <div>
                            <label>
                                <input type="checkbox" name="role[]" value="DemandeTraducteur"
                                    <?php echo (isset($_SESSION['role']) && in_array("DemandeTraducteur", $_SESSION['role'])) ? 
                                    "checked" : ""; ?>>
                                DemandeTraducteur
                            </label>
                        </div>
                    </div>
                    <!-- Bouton de soumission -->
                    <div class="field2">
                        <input type="submit" class="btnprof" value="Enregistrer">
                    </div> 
                </form>
            </div>
        </div>
    </body>
</html>
