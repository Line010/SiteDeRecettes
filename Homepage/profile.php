<?php
    session_start(); 

    $file = '../Registration/users.json';
    $contenu = json_decode(file_get_contents($file), true) ?? [];
    // Vérifier que la connexion est toujours établit
    if (!isset($_SESSION['username'])) {
        echo json_encode(['error' => 'User not logged in']);
        exit();
    }

    // On extrait les donné de l'utilisateur de la session de connexion
    $user_data = [
        'username' => $_SESSION['username'],
        'email' => $_SESSION['email'],
        'age' => $_SESSION['age'],
        'role' => $_SESSION['role']
    ];
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Profile</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <link rel="stylesheet" href="../Registration/registration.css">
    </head>
    <body>
        <div class="container">
            <div class="box">
                <div class="profile-header">
                    <!-- Image "Back" à gauche -->
                    <a href = "home.php"> 
                        <img src="../Registration/Images/backbtn.png" alt="Back" width="40" height="40">
                    </a>
                    <!-- Centré l'image de profil et titre -->
                    <div class="center-content">
                        <img src="../Registration/Images/profilee.png" alt="Profile" width="25" height="25">
                        <div class="profile-title">Profile</div>  
                    </div>
                </div>
                <!-- Les donnés de la session sont affiché ici -->
                <div class="profile">User: 
                    <span class="profile-value" id="username">
                        <?php echo $user_data["username"]; ?>
                    </span>
                </div>
                <div id="username" style="color:red; margin-bottom:10px;"></div>

                <div class="profile">E-mail:
                    <span class="profile-value" id="email">
                        <?php echo $user_data["email"]; ?>
                    </span>
                </div>
                
                <div id="email" style="color:red; margin-bottom:10px;">
                    <!-- Le message d'erreur s'affichera ici s'il y en a-->
                </div>

                <div class="profile">Age:
                    <span class="profile-value" id="age">
                            <?php echo $user_data["age"]; ?>
                    </span>
                </div>
                <div id="age" style="color:red; margin-bottom:10px;"></div>

                <div class="profile">Role: 
                    <span class="profile-value" id="role">
                        <!-- Il faut convertir le tableau de role en une chaine de texte -->
                    <?php echo implode(", ", $user_data["role"]); ?>
                    </span>
                </div>
                <div id="role" style="color:blue; margin-bottom:10px;"></div>
                <!-- Bouton pour modifer le profile -->
                <div class="button-container">
                    <div class="fields2">
                        <a href="../Registration/login.php">
                            <button class="btnprof">Log Out</button>
                        </a>
                    </div>    

                    <div class="field2">
                        <a href="update_pfp.php">
                            <input type="button" class="btnprof" value="Modifier mon profil">
                        </a>
                    </div>
                </div>
                </div>
                    <div id="error-message"></div>  
                </div>
            </div> 
        </div>
    </body>
</html> 
