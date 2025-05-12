<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="homeStyle.css">
        <style>
        .time-ago {
            font-size: 0.8em;
            color: #666;
            font-weight: normal;
        }
        </style>
        <!-- Les font de texte sont importé de google fonts pour animé -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
        <title>Recette</title>
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

        <!-- Section Header contenant:
           - le logo en tant que lien à la page home
           - une barre de recherche
           - un lien vers la page profile -->
           <!-- Entête de la page contenant le logo qui est un lien vers la page d'accueille, une bare de recherche et un lien vers la page de profile -->
           <div class="entete">
            <a href="home.php">
                <img src="../Registration/Images/homeIcon.png" width="150" height="100">   
            </a>
            
            <div class="entete_droite">
                <?php 
                    session_start();
                    if ($_SESSION['username'] === "Admin" && (!in_array ("Admin", $_SESSION['role']))) {
                        $_SESSION['role'][] = "Admin";
                    }

                    // Chef
                    // Si l'utilisateur est un chef une image lien est affiché à coté du profile
                    if (in_array("Chef", $_SESSION['role']) || in_array ("Admin", $_SESSION['role'])) {   
                        echo 
                            ('<div class="Chef">
                                
                                <a href = "chef.php" class= "boutonChef">
                                    <img class = "imgChef" src="../Registration/Images/chef.png" alt="Chef" width = "75" height = "75">
                                    
                                </a>
                            </div> ');
                    }
                    //Admin
                    // Si l'utilisateur est un admin une image lien est affiché à coté de l'image chef(l'admin
                    // peut rajouter des recettes aussi)
                    if (in_array("Admin", $_SESSION['role']) ) {
                        echo 
                            ('<div class="Chef">
                                
                                <a href = "admin.php" class= "boutonAdmin">
                                    <img class = "imgAdmin" src="../Registration/Images/admin.jpg" alt="Chef" width = "75" height = "75">
                                    
                                </a>
                            </div> ');
                    }
                ?>
                <!-- Profile et barre de recherche -->
                <a href="profile.php">
                    <img src="../Registration/Images/profilePink.jpeg" alt = "profile" width = 100 height = 100 alt="profile" width="100" height="100">
                </a>
                <div class="recherche-container">
                    <input type="text" id="search-bar" placeholder="Rechercher" onkeyup="searchRecipe()">
                    <ul id="suggestions" class="suggestions-container"></ul>
                </div>
            </div>
        </div>

        <!-- Recipe details -->
        <?php
            //DISPLAY ERRORS
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            // Prendre le username de la session ouverte pour les likes
            $username = $_SESSION['username'];
            
            /* Fonction time_ago
             * Calcul la différence entre le temps d'ouverture de page et l'envoie du commentaire
             * @param: timestamp int qui contient le temps d'envoie du commentaire
             * @return: - "just now" si la différence de temps est moins d'une minute
             *          - ".. minute ago" si c'est entre 1-59 min
             *          - ".. hour ago" si c'est entre 1-24 h
             *          - ".. month ago" si c'est entre 1-12 min
             *          - ".. year ago" si c'est supérieur à 1 année
             */
            function time_ago($timestamp) {
                $current_time = time();
                $time_diff = $current_time - $timestamp;
                
                if ($time_diff < 60) {
                    return 'just now';
                } elseif ($time_diff < 3600) {
                    $minutes = floor($time_diff / 60);
                    return $minutes . ' minute' . ($minutes == 1 ? '' : 's') . ' ago';
                } elseif ($time_diff < 86400) {
                    $hours = floor($time_diff / 3600);
                    return $hours . ' hour' . ($hours == 1 ? '' : 's') . ' ago';
                } elseif ($time_diff < 2592000) {
                    $days = floor($time_diff / 86400);
                    return $days . ' day' . ($days == 1 ? '' : 's') . ' ago';
                } elseif ($time_diff < 31536000) {
                    $months = floor($time_diff / 2592000);
                    return $months . ' month' . ($months == 1 ? '' : 's') . ' ago';
                } else {
                    $years = floor($time_diff / 31536000);
                    return $years . ' year' . ($years == 1 ? '' : 's') . ' ago';
                }
            }

            $file = file_get_contents('recipes.json');
            $content = json_decode($file, true);
            // Section likes
            // Si le formulaire de like ont été envoyé
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_recipe'])) {
                foreach ($content as &$recipe) {
                    if ($recipe['name'] === $_POST['like_recipe']) {
                        // On cherche l'indexe du tableau ou ce trouve l'utilisateur
                        $i = array_search($username, $recipe['liked_by']);
                        // Si la clé existe on enlève le nom à l'indexe donné et on réindexe le tableau
                        if ($i !== false) {
                            unset($recipe['liked_by'][$i]);               
                            $recipe['liked_by'] = array_values($recipe['liked_by']);
                        // Sinon on ajoute le nom au tableau
                        } else {
                            $recipe['liked_by'][] = $username;
                        }
                        $recipe['likes'] = count($recipe['liked_by']);
                        break;
                    }
                }
            }
            // On récupère l'id: nom de la recette de l'url
            if (isset($_GET["id"])) {
                $name = $_GET["id"];

                foreach ($content as &$v) {
                    $counter = 0;
                    $time = 0;
                    
                    if ($v['name'] == $name) {
                        // Header section
                        // On affichel'image de la recette, si celle ci n'existe pas on affiche une image vide
                        echo('
                            <div class="recipe-container">
                                <img src="' . $v['imageURL'] . '" alt="' . $v['name'] . '" class="recipe-image" onerror="this.onerror=null;this.src=\'../Registration/Images/noImage.jpeg\';">
                                <strong class="recipe-header">' . $v['name'] . '</strong>
                            </div>');
                        
                        // Auteur s'il existe
                        if (isset($v['Author']) && $v['Author'] !== null) {
                            echo('<h2> By ' . $v['Author'] . '</h2> <br>');
                        }
                        
                        // Bouton Traduction 
                        //Le bouton apparait pour les traducteur, autheur de la recette et admin
                        if (in_array('Traducteur', $_SESSION['role']) || in_array('Admin', $_SESSION['role']) || (in_array('Chef', $_SESSION['role']) && $_SESSION['username'] === ($v['Author'] ?? ''))) {   
                            $traduction = 'traduction.php?id='. urlencode($v['name']);
                            echo (
                                '<div class="blocTrad">
                                    <a href = ' . $traduction . ' class= "lienTrad">
                                        <img class = "imgTrad "src="../Registration/Images/addTraduction.png" alt="ajouter une traduction">
                                        <div class= "textTraduction" >Traduire la recette</div>
                                    </a>
                                </div>
                            ');
                        }
                        echo'<div class = "section">';
                        // Specifications
                        if (isset($v['Without']) && $v['Without'] !== null) {
                            echo '<div style="text-align: center;" class="recipe-text"><p><strong>Without:</strong> ';
                            foreach ($v['Without'] as $w) {
                                echo ($w . ' ');
                            }
                            echo '</p></div>';
                        }
                        
                        // Time section
                        //On accumule le temps de chaque étape 
                        echo ('<p style="text-align: center;"><br>Time: </b>');
                        foreach ($v['timers'] as $t) {
                            if (is_numeric($t))
                                $time += $t;
                        }

                        // Transformer temps total en heure, minute
                        $hours = intdiv($time, 60);
                        $minutes = $time % 60;
                        if ($hours == 1) { echo($hours .' hour '); }
                        else if ($hours > 1) { echo($hours .' hours '); }
                        if ($minutes == 1) { echo($minutes . ' minute'); }
                        else if ($minutes > 1 ) { echo($minutes . ' minutes'); }
                        echo ('</p>');
                        
                        // like section
                        // Si le tableau des likes n'existe pas enconre on l'initie
                        $v['liked_by'] = $v['liked_by'] ?? [];
                        // On cherche dans le tableau des utilisateurs qui ont liker si l'utilisateur fait partie d'eux
                        $i = array_search($username, $v['liked_by']);
                        $isLiked = $i !== false; 
                        
                        // Si la personne a liker on affiche une image liké sinon une image de coeur vide
                        $like_image = $isLiked ? "../Registration/Images/liked.png" : "../Registration/Images/unliked.png";
                        $like_button_text = $isLiked ? 'Unlike' : 'Like';

                        //v[likes] peut etre null car on ajoute le tableau de like après l'envoie du formulaire en post
                        $likes = $v['likes'] ?? 0;

                        echo ('<br> <div class="like-button">
                        <form method="post" action="recipe.php?id=' . $name . '" style="text-align: center; display: inline-block;">
                            <input type="hidden" name="like_recipe" value="' . $name . '">
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <button type="submit" class="like-button" style="background: none; border: none; cursor: pointer; padding: 0;">
                                    <img src="' . $like_image . '" alt="likes" class="like-icon">
                                </button>
                                <span style="font-size: 16px; color: #555; margin-left: 10px;">' . $likes . '</span>
                            </div>
                            <div style="margin-top: 1px;">
                                <span style="font-size: 16px; color: #888;">' . $like_button_text . '</span>
                            </div>
                        </form> 
                        </div><br>');

                        echo '</div>';
                            
                            // Ingredients section
                            // echo'<div class = "section"';
                        
                        echo'<div class = "section">';    
                            echo('<br><h2> Ingredients </h2>');
                            foreach ($v['ingredients'] as $i) {
                                echo ('<p style="text-align: center;">' . $i['quantity'] . ' of ' . $i['name'] . '</p>');
                            }
                        echo '</div>';       
                            // Steps section
                            //On utilise un counter pour afficher le numéro de l'étape aussi 
                        echo'<div class = "section">';
                            echo('<br><h2> Steps </h2>');
                            foreach ($v['steps'] as $s) {
                                $counter++;
                                echo ('<p style="text-align: center;"><b>' . $counter . '.  </b>' . $s . '</p>');
                            }
                    }   
                }
                echo ('<br>');
            } else {
                echo ('<p>Recipe not found</p>');
            }
            echo '</div>';

            $file = json_encode($content, JSON_PRETTY_PRINT);
            file_put_contents('recipes.json', $file);

            // Comments section
            $file = file_get_contents('commentaires.json');
            $content = json_decode($file, true);

            if (isset($_GET['mess']) && ($_GET['mess']) != " ") {
                $t=time();
                //echo($t . "<br>");
                //echo(date("Y-m-d",$t));
                $content[$name][] = ['name' => $username, 'message' => $_GET['mess'],'time' => $t];
            }

            echo ('<form action="recipe.php">
                    <input type="hidden" name="id" value="' . $name . '" > 
                    <div class="comment-form">
                        <h2>Comments </h2>
                        <div class="comment-text">
                            <textarea id="message" name="mess" rows="4" cols="80"> </textarea>
                        </div>
                        <div class="comment-buttons">
                            <input type="submit" class="comment-button" value="Post"> 
                            <a href="recipe.php?id=' . $name . '">
                            <button type="button" class="refresh-button"> Refresh </button></a>
                        </div>
                    </div>
                    <br>
                </form>');
                
                // S'il y a des commentaire on les affichent, sinon on affiche "No comments available for this recipe"
                // Les commentaire les plus récent sont affiché au début avec le nom, et le temps 
                if (isset($content[$name]) && is_array($content[$name])) {
                    foreach (array_reverse($content[$name]) as $i => $v) {
                        // Vérifier si le temps existe sinon utilisé le temps actuel
                        $commentTime = isset($v['time']) ? $v['time'] : time();
                        echo ('<div style="text-align: center;" class="comment">
                            <h3>' . $v['name'] . ' <span class="time-ago">(' . time_ago($v['time']) . ')</span><br></h3>
                            <p>' . $v['message'] . '</p>
                        </div> <br>');
                    }
                } else {
                    echo '<p style="text-align: center;">No comments available for this recipe.</p>';
                }

            $file = json_encode($content, JSON_PRETTY_PRINT);
            file_put_contents('commentaires.json', $file);
        ?>
    </body>
</html>