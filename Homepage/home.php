<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="homeStyle.css">

        <title>Home</title>

        <!-- Fonction js faisant appel à recipes.php pour afficher la liste des reccetes-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
            // Fonction Ajax qui appelle recipes.php avec le filtre correspondant
            function getRecipes(nomfiltre) {
                $.ajax({
                    method: "GET",
                    url: "recipes.php",
                    data: {"nomfiltre" : nomfiltre},
                    dataType: "html"
                }).done(function(e){
                    console.log("Filtre affiché", e);
                    $("#liste_recettes").html(e);
                }).fail(function(e){
                    console.log("Erreur d'affichage", e);
                    $("#liste_recettes").html("<p>Erreur d'affichage</p>");
                });
            }
            function showRecipe(id) {
                $.ajax({
                    method: "GET",
                    url: "recipe.php",
                    data: {"id" : id},
                    dataType: "html"
                }).done(function(e){
                    console.log("went to the recipe", e);
                    $("#liste_recettes").html(e);
                }).fail(function(e){
                    console.log("Erreur d'affichage", e);
                    $("#liste_recettes").html("<p>Erreur recette </p>");
                });
            }
            function goToRecipe(id) {
                window.location.href = 'recipe.php?id='+ id;
            }
        </script>
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
                    if (in_array("Chef", $_SESSION['role']) || in_array ("Admin", $_SESSION['role'])) {   
                        
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
                <!-- Recherche -->
                <div class="recherche-container">
                    <input type="text" id="search-bar" placeholder="Rechercher" onkeyup="searchRecipe()">
                    <ul id="suggestions" class="suggestions-container"></ul>
                </div>
            </div>
        </div>
        
        <h1 id = "titre_recettes"> Recipes </h1> <br>
        <div class = "main">
        <!-- Filtres des recettes-->
        <div class = "filtres">
            <div class = "f" onclick="getRecipes('tout')">
                <img src = "https://cdn-icons-png.flaticon.com/128/1691/1691114.png" alt = "tout"  width = 50 height = 50 "> 
                <p class = "commentaire" > All the recipes </p>
            </div>
            <div class = "f" onclick="getRecipes('NoMilk')">
                <img src = "https://cdn-icons-png.freepik.com/256/5291/5291032.png?ga=GA1.1.527913643.1741253595&semt=ais_hybrid" alt = "NoMilk"  width = 50 height = 50> 
                <p class = "commentaire" > No Milk </p>
            </div>
            <div class = "f" onclick="getRecipes('Vegetarian')">
                <img src = "https://icons.veryicon.com/png/o/construction-tools/function-icon-1/no-19.png" alt = "non term" width = 50 height = 50 > 
                <p class = "commentaire" > Vegetarian </p>
            </div>
            <div class = "f" onclick="getRecipes('NoGluten')">
                <img src = "https://cdn-icons-png.freepik.com/128/4807/4807774.png" alt = "NoGluten" width = 50 height = 50 > 
                <p class = "commentaire" > No Gluten </p>
            </div>
            <div class = "f" onclick="getRecipes('Vegan')">
                <img src = "https://icons.veryicon.com/png/System/iOS%207/Food%20Vegan%20Food.png" alt = "Vegan" width = 50 height = 50 >
                <p class = "commentaire" > Vegan </p>
            </div>
            <!-- <div class = "f" onclick="getRecipes('Likes')">
                <a href = "login.html"> <img src = "https://cdn-icons-png.freepik.com/256/10350/10350805.png?ga=GA1.1.527913643.1741253595&semt=ais_hybrid" alt = "likes" width = 50 height = 50> </a>
                <p class = "commentaire" > Likes </p>
            </div> -->
        </div>

        <!-- Liste de recettes-->
        <div id="liste_recettes">
            <!-- appelle la fonction dès que la page est prête-->
            <script> $(document).ready(getRecipes()); </script>
        </div>
    </div>
    </body>
    <script>
        let data = [];
        /* Procedure searchRecipe
         * Transforme le mot tapé en minuscule et vérifie s'il est inclue dans l'une des recettes
         * du fichier recipes.json et appelle la fonction li.onclick() qui redirige l'utilisateur
         * vers la recette cliqué
         */

        function searchRecipe() {
            const input = document.getElementById("search-bar").value.toLowerCase();

            //Il faut reinitialisé à vide la chaine dans le cas ou on tape, on efface puis retape un mot
            const suggestions = document.getElementById("suggestions");
            suggestions.innerHTML = "";

            for (let i = 0; i < data.length; i++) {
                const recipe = data[i];
                //Si le nom tapé est inclu dans le nom de la recette parcourue
                if (recipe.name.toLowerCase().includes(input)) {
                    //On crée une liste d'affichage
                    const li = document.createElement("li");
                    li.textContent = recipe.name;
                    // Quand on clique sur le nom de la recette on est dirigé vers ça page
                    li.onclick = function() {
                        //On appelle encode pour correctement copié le nom cliqué car il existe 
                        //des espace qui se transforme en %20 
                        const recipeName = encodeURIComponent(recipe.name);
                        window.location.href = `recipe.php?id=${recipeName}`;
                    };
                    //On fait apparaitre les suggestions en dessus de la barre de recherche dynamiquement
                    suggestions.appendChild(li);
                }
            }
        }
        
        $(document).ready(function () {
            getRecipes();
        });
            // 2eme Fonction Ajax qui appelle recipes.json pour chercher une recette
            $.ajax({
                url: "recipes.json",
                method: "GET",
                dataType: "json"
            }).done(function(json) {
                data = json;
                //console.log("Data loaded:", data);
            }).fail(function(error) {
                console.log("Erreur lors du chargement de recipes.json :", error);
            });  
    </script>