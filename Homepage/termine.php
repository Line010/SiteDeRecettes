<?php
    $file = file_get_contents('recipes.json');
    $contenu = json_decode($file, true);
    
    //On affiche les recettes non terminé que seul l'admin peut voir
    echo ('<div class = "recettes">
            <h2> Recettes non terminées </h2>');
        
    foreach ($contenu as $v) {
        $name = $v['name'] ?? null;
        $nameFR = $v['nameFR'] ?? null;
        $ingredients = $v['ingredients'] ?? null;
        $ingredientsFR = $v['ingredientsFR'] ?? null;
        $steps = $v['steps'] ?? null;
        $stepsFR = $v['stepsFR'] ?? null;
        $timers = $v['timers'] ?? null;
        $imageURL = $v['imageURL'] ?? null;
        $originalURL = $v['originalURL'] ?? null;
        $auteur = $v['Author'] ?? "Unknown";

        // On considère une recette complète si ces champs dans une langue sont complète
        // Pour ça on vérifie par langue les champs

        // Si le nom existe en anglais
        if ($name != null) {
            //Si l'un des champs en anglais est vide on affiche la recette non terminé
            if (
                $ingredients == null ||
                $steps == null ||
                $timers == null||
                $imageURL == null ||
                $originalURL == null)
                {
                    echo ( '
                            <div class = "recette" onclick = "goToRecipe(\''.$name.'\')"> 
                            
                            <div class = "recette_img">
                            
                                    <img src = "' . $imageURL .'" alt = "' . $name . '" width = "75" height = "75" onerror="this.onerror=null;this.src=\'../Registration/Images/noImage.jpeg\';"> 
                                
                            </div>
                            <div class = "recette_text">
                                <h3>' . $name . ' </h3>
                                <p> By ' . $auteur . ' </p>
                            </div>
                            
                    </div>  <br>');
                    continue;
            } else{
                continue;
            }
        } 
        //Si le nom en français est pas vide et que n'importe lequel des champs en français est vide on affiche la recette
        if ($nameFR != null) {
            if (
                $ingredientsFR == null ||
                $stepsFR == null ||
                $timers == null||
                $imageURL == null ||
                $originalURL == null)
                {
                    echo ( '<div class = "recette" onclick = "goToRecipe(\''.$nameFR.'\')"> 
                            
                            <div class = "recette_img">
                            
                                    <img src = "' . $imageURL .'" alt = "' . $nameFR . '" width = "75" height = "75" > 
                                
                            </div>
                            <div class = "recette_text">
                                <h3>' . $nameFR . ' </h3>
                                <p> By ' . $auteur . ' </p>
                            </div>
                    </div> <br>');
                }
        } 
    }
    echo ('</div>');              
?>