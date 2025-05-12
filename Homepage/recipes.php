<?php
    $file = file_get_contents('recipes.json');
    $contenu = json_decode($file, true);
    // On récupère le nom de l'autheur par l'url
    if (isset($_GET['author'])){
        echo ('<div class = "recettes">');
        foreach ($contenu as $v) {
            // On récupère les donnés de la recette par le nom de l'autheur
            $imageURL = $v['imageURL'];
            $name = $v['name'];
            $auteur = $v['Author'] ?? "Unknown";
            $author = $_GET['author'];
            //Si l'auteur est l'auteur donné dans la fonction ajax, j'affiche ca recette
            if ($author === $auteur){
                echo ( '<div class = "recette" onclick = "goToRecipe(\''.$name.'\')"> 
                                
                                <div class = "recette_img">
                                
                                        <img src="' . $imageURL .'" alt="' . $name . '" width="250" height="250" onerror="this.onerror=null;this.src=\'../Registration/Images/noImage.jpeg\';">
                                    
                                </div>
                                <div class = "recette_text">
                                    <h3>' . $name . ' </h3>
                                    <p> By ' . $auteur . ' </p>
                                </div>
                        </div> <br>');
            }
        }
        echo ('</div>');
    } else {
        // Enregistrer le filtre selectionné s'il y en a sinon on choisi tout
        if (isset($_GET["nomfiltre"])){
            $nomfiltre = $_GET["nomfiltre"];
        }else {
            $nomfiltre = "tout";
        }

    echo ('<div class = "recettes">');
        foreach ($contenu as $v) {
            $imageURL = $v['imageURL'];
            $name = $v['name'];
            $auteur = $v['Author'] ?? "Unknown";
            //S'il n'y a pas de filtre choisi on affiche la liste de tout les recettes avec leur image cliquable
            if ($nomfiltre == "tout"){
                echo ( '<div class = "recette" onclick = "goToRecipe(\''.$name.'\')"> 
                            <div class = "recette_img">
                                
                                <img src="' . $imageURL .'" alt="' . $name . '" width="250" height="250" onerror="this.onerror=null;this.src=\'../Registration/Images/noImage.jpeg\';"> 
                                    
                            </div>
                            <div class = "recette_text">
                                <h3>' . $name . ' </h3>
                                <p> By ' . $auteur . ' </p>
                            </div>
                        </div> <br>');
            } else {
            // Si un filtre a été choisi
            if (isset ($v['Without']) && $v['Without'] !== null){

                if (in_array($nomfiltre, $v['Without'])) {
                    
                    
                    echo ( 
                        '<div class = "recette" onclick = "goToRecipe(\''.$name.'\')">     
                            <div class = "recette_img">   
                                <img src="' . $imageURL .'" alt="' . $name . '" width="250" height="250" onerror="this.onerror=null;this.src=\'../Registration/Images/noImage.jpeg\';">       
                            </div>
                            <div class = "recette_text">
                                <h3>' . $name . ' </h3>
                                <p> By ' . $auteur . ' </p>
                            </div>
                        </div> 
                    <br>');
                }
            }
        }
    }
    echo ('</div>');
    
    $file = json_encode($contenu, JSON_PRETTY_PRINT);
    file_put_contents('recipes.json', $file);
}                 
?>