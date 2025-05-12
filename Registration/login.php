<?php
    /* Ouvrir une session pour enregistrer les informations de l'utilisateur
     * afin de les afficher sur le profile plus tard 
     */
    session_start();

    // Charger les utilisateurs depuis le fichier JSON
    $file = 'users.json';
    $users = json_decode(file_get_contents($file), true);

    //Si les formulaire ont été rempli
    if (isset($_POST['username']) && $_POST['password']) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $user_found = false;
        
        // Vérifier si l'utilisateur existe et le mot de passe est correct
        foreach ($users as $user) {
            if ($user['username'] === $username && password_verify($password, $user['password'])) {
                $user_found = true;
                $_SESSION['username'] = $user['username'];
                $_SESSION['age'] = $user['age'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                break;
            }
        }
        // Si l'utilisateur est trouvé on afiche un message de succès ou d'erreur
        echo $user_found ? 'success' : 'Mot de passe ou utilisateur incorrect.';
        exit;
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" href="registration.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>
    <body>
        <div class="container">
            <div class="box form-box">
                <header>Login</header>
                
                <!-- On choisi input pour pouvoir entrer les donné de l'utilisateur 
                  -- et un bouton pour ensuite traité le php et validé les informations tapés
                  -->
                <form id="loginForm" action="login.php" method="post">
                    <div class="field input">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" required>
                    </div>

                    <div class="field input">
                        <label for="password">Password</label>
                            <div class="password-wrapper">
                                <!-- Le type password permet de masquer le champs -->
                                <input type="password" name="password" id="password">
                                <span class="toggle-password" onclick="togglePassword()">
                                    👁️
                                </span>
                            </div>
                            <div id="error-message" style="color:red; margin-bottom:10px;">
                                <!-- Un message d'erreur s'affichera ici s'il y en a au cas ou le mdp ou username est faux-->
                            </div>
                    </div>

                    <div class="field">
                        <input type="button" name="submit" class="btn" value="Login" id="loginButton">
                    </div>
                    
                    <!-- Passer à la page signup si l'utilisateur n'a pas de compte crée encore-->
                    <div class="links">
                        Don't have an account? <a href="signup.php">Sign Up Now</a>
                    </div>
                </form>
            </div>
        </div>

        <script>
        /* Dès que le champs loginButton a été activé c'est à dire que le bouton a été déclanché
         * on appelle la fonction d'ajax
         */
        $(document).ready(function() {
            // Ajouter un gestionnaire d'événements sur le bouton de connexion
            $("#loginButton").click(function(e) {
                connexion();
            });
        });

        /* Fonction qui gère l'appel au php pour la vérification des informations tapé
         * @ajax défini le type d'état, le nom de fichier appeler et récupère les données du html
         * @done si tout est bon cette fonction est appeler pour afficher un message de succès et 
         * redirige l'utilisateur vers la page home
         * @fail en cas d'erreur cette fonction est appelée, un message d'erreur sera affiché
         */
         function connexion() {
            $.ajax({
                method: "POST",
                url: "login.php",
                data: { 
                    // Récupère la valeur directement du champ du formulaire
                    username: $("#username").val(),
                    password: $("#password").val()  
                }
            }).done(function(response) {
                // Affiche la réponse du serveur: juste pour vérifier si l'appel ajax a été fait
                console.log("Réponse reçue : " + response); 
                // Si la réponse est "success" on redirige sinon on affiche l'erreur
                if (response === 'success') {
                    // Redirige vers la page d'accueil
                    window.location.href = "../Homepage/home.php";
                } else {
                    // Affiche l'erreur sur la page dans le div error-message sous les formulaires
                    $("#error-message").text(response); 
                }
            }).fail(function(error) {
                /* Ici l'erreur peut etre déclancher si on aver mal gérer les donnés et non pas 
                 * d'utilisateur ou de password incorrecte. 
                 */
                console.log(error); 
                $("#error-message").html("<span class='ko'> Erreur:  Informations mal chargés </span>");
            });
        }

        /* Procédure togglePassword qui affiche/cache le mot de passe quand l'icon de text a été cliqué
             * Si le type du champs est password (caché) on le change en texte et on modifie l'icon de texte
             * Sinon on le rend en type password pour le caché avec un changement d'icon de texte
             */
            function togglePassword() {
                let passwordInput = document.getElementById("password");
                let toggleIcon = document.querySelector(".toggle-password");

                //On verifie si le type du password est password pour le rend texte et le faire apparaitre
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    toggleIcon.textContent = "🙈"; // Rendre le password visible
                
                //Sinon on le définit en temps que password pour le cacher
                } else {
                    passwordInput.type = "password";
                    toggleIcon.textContent = "👁️"; // Cacher le password
                }
            }
        </script>
    </body>
</html>
