<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../Registration/registration.css">
        <title> Admin </title>

        <!-- Fonction js faisant appel à recipes.php pour afficher la liste des reccetes-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
            // Fonction Ajax qui appelle recipes.php avec le filtre correspondant
            function getUsers() {
                $.ajax({
                    method: "GET",
                    url: "users.php",
                    dataType: "html"
                }).done(function(e){
                    console.log("Utilisateurs affichés", e);
                    $("#liste_users").html(e);
                }).fail(function(e){
                    console.log("Erreur d'affichage", e);
                    $("#liste_users").html("<p>Erreur d'affichage des users</p>");
                });
            }

            function demande(role, statut, user) {
                $.ajax({
                    method: "GET",
                    url: "users.php",
                    data: {"role" : role, "statut" : statut, "user" : user},
                    dataType: "html"
                }).done(function(e){
                    console.log("Demande de change de role pris en compte", e);
                    $("#liste_users").html(e);
                }).fail(function(e){
                    console.log("Erreur d'affichage", e);
                    $("#liste_users").html("<p>Erreur de demande </p>");
                });
            }

            function getTermine() {
                $.ajax({
                    method: "GET",
                    url: "termine.php",
                    dataType: "html"
                }).done(function(e){
                    console.log("Recettes affichées", e);
                    $("#liste_users").html(e);
                }).fail(function(e){
                    console.log("Erreur d'affichage", e);
                    $("#liste_users").html("<p>Erreur d'affichage des recettes</p>");
                });
            }

            function getDemandes(demande) {
                $.ajax({
                    method: "GET",
                    url: "users.php",
                    data: {"demande" : demande},
                    dataType: "html"
                }).done(function(e){
                    console.log("demandes affichées", e);
                    $("#liste_users").html(e);
                }).fail(function(e){
                    console.log("Erreur d'affichage", e);
                    $("#liste_users").html("<p>Erreur d'affichage des demandes</p>");
                });
            }
            function goToRecipe(id) {
                window.location.href = 'recipe.php?id='+ id;
            }
            

        </script>

    </head>

    <home>
         <!-- Section Header contenant:
           - le logo en tant que lien à la page home
           - une barre de recherche
           - un lien vers la page profile -->
           <!-- Entête de la page contenant le logo qui est un lien vers la page d'accueille, une bare de recherche et un lien vers la page de profile -->
        <div class="entete">
            <a href="home.php">
                <img src="../Registration/Images/homeIcon.png " width="150" height="100">   
            </a>
            
            <div class="entete_droite">
                <?php 
                    session_start();
                    if ($_SESSION['username'] === "Admin" && (!in_array ("Admin", $_SESSION['role']))) {
                        $_SESSION['role'][] = "Admin";
                    }

                    // Chef
                    if (in_array("Chef", $_SESSION['role']) || in_array ("Admin", $_SESSION['role']) ) {   
                        
                        echo 
                            ('<div class="Chef">
                                
                                <a href = "chef.php" class= "boutonChef">
                                    <img class = "imgChef" src="../Registration/Images/chef.png" alt="Chef" width = "75" height = "75">
                                    
                                </a>
                            </div> ');
                    }
                    //Admin
                    if (in_array("Admin", $_SESSION['role']) ) {   
                        
                        echo 
                            ('<div class="Chef">
                                
                                <a href = "admin.php" class= "boutonAdmin">
                                    <img class = "imgAdmin" src="../Registration/Images/admin.jpg" alt="Chef" width = "75" height = "75">
                                    
                                </a>
                            </div> ');
                    }
                ?>

                <a href="profile.php">
                    <img src="../Registration/Images/profilePink.jpeg" alt = "profile" width = 100 height = 100 alt="profile" width="100" height="100">
                </a>
                <div class="recherche-container">
                    <input type="text" id="search-bar" placeholder="Rechercher" onkeyup="searchRecipe()">
                    <ul id="suggestions" class="suggestions-container"></ul>
                </div>
            </div>
        </div>

        <!-- Haut de tete de la page -->
        <div class="trad-header">
            <a href="home.php">
                <img src="../Registration/Images/backbtn.png" width="40" height="40">
            </a>
            <div class="center-content-translate">
                <img src="../Registration/Images/admin.jpg" width="25" height="25">
                <div class="profile-title"> Admin </div>
            </div>
        </div>
        

 
        <!-- Bouton non terminées -->
        <button type = "button" class = "bouton" onclick = "getTermine()"> Recettes non terminées </button>

        <!-- Bouton utilisateurs -->
        <button type = "button" class = "bouton" onclick = "getUsers()"> Utilisateurs </button>
        
        <!-- Bouton demandes -->
        <button type = "button" class = "bouton" onclick = "getDemandes('demande')"> Demandes de roles </button>

        <!-- Liste de d'utilisateurs-->
        <div id="liste_users">
            <!-- appelle la fonction dès que la page est prête-->
            <script> $(document).ready(getUsers()); </script>
        </div>

    </home>
</html>