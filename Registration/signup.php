<?php
    /* Ouvrir une session pour enregistrer les informations de l'utilisateur afin de les afficher 
     * sur la profile plus tard 
     */
    session_start();
    
    // Charger les utilisateurs depuis le fichier JSON
    $file = 'users.json';
    $contenu = json_decode(file_get_contents($file), true);

    //Si les formulaire ont été rempli
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        $age = $_POST['age'];

        // Vérifier si l'utilisateur existe déjà, si oui quitter la fonction et afficher message d'erreur
        foreach ($contenu as $user) {
            if ($user['username'] === $username || $user['email'] === $email ) {
                echo "Utilisateur ou email déjà utilisé";
                exit(); 
            }
        }
        // Stoquer les informations de l'utilisateur s'il n'existe pas déjà dans la liste d'utilisateur
        $contenu[] = [
            'username' => $username,
             /* fonction password_hash:
              * Utiliser un algorithme de hachage pour masquer le password car l'administrateur 
              * ne doit le savoir aussi
              */
            'password' => password_hash($password, PASSWORD_DEFAULT), 
            
            //Le role va etre choisi par l'utilisateur après inscription dans la page role
            'role' => null, 
            'age' => $age,
            'email' => $email
        ];

        file_put_contents($file, json_encode($contenu, JSON_PRETTY_PRINT));
        
        /* Enregistrer les donnés de l'utilisateur dans une session pour l'utiliser dans la page 
         * role, la page de profile et dans la page de recette pour commenter et liker
         */
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['age'] = $age;

        //Cet echo permettra à la fonction d'ajax de savoir que tout est bon
        echo "success";
        exit(); 
    }
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Sign Up</title>
        <link rel="stylesheet" href="registration.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </head>

    <body>
        <div class="container">
            <div class="box form-box">
                <form method="post">
                    <header>Sign Up</header>
                    
                    <!-- On choisi input pour pouvoir entrer les donné de l'utilisateur qui seront ensuite enregistrer dans 
                      -- le fichier json et un bouton pour valider les informations tapés
                      -->
                    <div class="field input">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username">
                    </div>

                    <div class="field input">
                        <label for="email">E-mail</label>
                        <input type="email" name="email" id="email">
                    </div>

                    <div class="field input password-container">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <!-- Le type password permet de masquer le champs -->
                            <input type="password" name="password" id="password">
                            <span class="toggle-password" onclick="togglePassword()">
                                👁️
                            </span>
                        </div>
                        <!-- Le message d'indication dans le cas ou l'utilisateur ne respecte pas les restrictions
                          -- Ce message n'est affiché que si le formulaire a été traité et la fonction appelé
                          -->
                        <p id="passwordMessage" style="color: red; display: none;">
                            Password must contain at least 6 characters:<br>
                            - one number <br>
                            - one lowercase letter <br>
                            - one uppercase letter
                        </p>
                    </div>
                    
                    <!-- Age de l'utilisateur-->
                    <div class="field input">
                        <label for="age">Age</label>
                        <input type="number" name="age" id="age">
                    </div>

                    <div id="error-message" style="color:red; margin-bottom:10px;">
                        <!-- Affiche un message d'erreur s'il y en a sur l'age invalide ou le username/email deja pris-->
                    </div>

                    <!-- Bouton d'envoie-->
                    <div class="field">
                        <input type="button" name="submit" class="btn" value="Sign Up" id="signupButton">
                    </div>

                    <div class="links">Already have an account? <a href="login.php">Log In Now</a> </div>
                </form>
            </div>
        </div>
        <script>
            /* Fonction appeler quand le bouton est déclanché de champs 'signupButton' 
             * Elle appelle une auttre fonction connexion() qui traite ajax 
             */
            $(document).ready(function() {
                $("#signupButton").click(function(e) {
                    if (validatePassEmail() && validateForm()) {
                        connexion();
                    }   
                });

                /* Fonction:validePassEmail qui vérifie l'entré correcte de l'email et du password
                * On défini une expression régulière que l'utilisateur doit respecté pour 
                * le password (appris en pil)           
                * La restriction est: 
                *  - Au moins un chiffre entre 0-9 (?=.*\d),
                *  - Au moins une lettre minuscule entre a-z (?=.*[a-z]),
                *  - Au moins une majuscule entre A-Z (?=.*[A-Z]),
                *  - Au moins un caractère spécial et un longeur de texte minimal de 6  
                *  ! On utilise les lookaheads (?=) pour vérifier les plusieurs règles indépendament sur la même chaîne,
                *  ! sans que les vérifications s’interfèrent
                *  @return {boolean} renvoie vrai si l'email et le password valident leur restrictions 
                * (expressions régulières), faux sinon et affiche un message d'erreur
                */
                function validatePassEmail() {
                    var password = $("#password").val();
                    var email = $("#email").val();
                    
                    // L'expression régulière pour valider le mot de passe
                    var passwordPattern = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,}$/;
                    var message = $("#passwordMessage");
                    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    
                    // Initialisation de la variable bool à true
                    var bool = true;

                    // Vérification du mot de passe
                    if (!passwordPattern.test(password)) {
                        // Afficher le message d'erreur si le mot de passe ne respecte pas le pattern
                        message.show();
                        // Le mot de passe n'est pas valide, donc bool devient false
                        bool = false;
                    } else {
                        // Masquer le message si le mot de passe est valide
                        message.hide();
                    }

                    // Vérification de l'email
                    if (!emailPattern.test(email)) {
                        $("#error-message").text("Adresse email doit être de la forme: user.name@domain.com");
                        // L'email n'est pas valide, donc bool devient false dans le cas ou il n'etait pas
                        bool = false;
                    } else {
                        // Effacer le message d'erreur si l'email est valide
                        $("#error-message").text("");
                    }
                    // Retourner la valeur de bool, qui est false si une validation échoue
                    return bool;
                }

                /* Fonction: validateForm: Verifier si les donné ont bien été entré manuellemnt 
                 * car le type button ne déclenche pas la soumission automatique du formulaire HTML
                 * @return {boolean} renvoie vrai si les champs ne sont pas vide, 
                 *                           faux sinon et affiche un message d'erreur
                 */
                function validateForm() {
                    var username = $("#username").val();
                    var email = $("#email").val();
                    var age = $("#age").val();

                    if (username === "" || age === "") {
                        $("#error-message").text("Remplir tout les champs !");
                        return false;
                    }
                    return true;
                }

                /* Fonction connexion: Gère l'appel ajax pour la vérification des informations tapé dans le php
                 * @ajax défini le type d'état, le nom de fichier appeler et récupère les données du html
                 * @done si tout est bon cette fonction est appeler pour afficher un message de succès et 
                 * redirige l'utilisateur vers la page role
                 * @fail en cas d'erreur cette fonction est appelée, un message d'erreur sera affiché dans la balise div de class error-message
                 */
                function connexion() {
                    $.ajax({
                        method: "POST",
                        url: "signup.php",
                        data: {
                            username: $("#username").val(),
                            password: $("#password").val(),
                            age: $("#age").val(),
                            email: $("#email").val()
                        }
                    }).done(function(response) {
                        console.log(response);
                        /* Si la réponse est "success" on redirige vers role.php sinon on affiche 
                         * l'erreur dans le DOM.
                         */
                        if (response === 'success') {
                            window.location.href = "role.php";
                        } else {
                            $("#error-message").text(response);
                        }
                    }).fail(function(error) {
                        console.log("AJAX request failed!",error);
                        $("#error-message").html("<span class='ko'> Error: network problem </span>");
                    });
                }
            });
            
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