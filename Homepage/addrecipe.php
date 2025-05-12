<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <title>Traduction de la recette</title>
        <link rel="stylesheet" href="../Registration/registration.css">
    </head>
    <body>
        <!-- Haut de tete de la page -->
        <div class="trad-header">
            <a href="chef.php">
                <img src="../Registration/Images/backbtn.png" width="40" height="40">
            </a>
            <div class="center-content-translate">
                <img src="../Registration/Images/profilee.png" width="25" height="25">
                <div class="profile-title">Nouvelle Recette</div>
            </div>
        </div>
        <div class="translate-page-container"> 
        
        <?php
        session_start();

        // Charger le contenu du fichier JSON des recettes
        $file = file_get_contents('recipes.json');
        $contenu = json_decode($file, true);

        //Compteurs pour les tableaux dynamiques (ingrédients, étapes, etc.)
            // Restrictions (without)
        $countW = isset($_GET['countW']) ? intval($_GET['countW']) : 1;
        if (isset ($_GET['addW'])) $countW ++;
            // Ingredients
        $countI = isset($_GET['countI']) ? intval($_GET['countI']) : 1;
        if (isset ($_GET['addI'])) $countI ++;
            // IngredientsFR
        $countIFR = isset($_GET['countIFR']) ? intval($_GET['countIFR']) : 1;
        if (isset ($_GET['addIFR'])) $countIFR ++;
            // Steps
        $countS = isset($_GET['countS']) ? intval($_GET['countS']) : 1;
        if (isset ($_GET['addS'])) $countS ++;
            // StepsFR
        $countSFR = isset($_GET['countSFR']) ? intval($_GET['countSFR']) : 1;
        if (isset ($_GET['addSFR'])) $countSFR ++;
        
        //Pour savoir quand enregistrer la recette(Vérifie si le formulaire de recette a été soumis)
        $done = isset ($_GET['poster']) ?? false;

        // Récupération des données envoyées par le formulaire
        $name = $_GET["name"] ?? '';
        $nameFR = $_GET["nameFR"]?? '';


        $nameI = $_GET["nameI"] ?? [];
        $quantity = $_GET["quantity"] ?? [];
        $type = $_GET["type"] ?? [];

        $ingredients = []; // Initialisation du tableau des ingrédients anglais
        
        // Remplissage du tableau d'ingrédients anglais si les champs sont non vides
        for ($i = 0; $i < $countI; $i++){
            if ((isset($quantity[$i]) && $quantity[$i] != '') || 
                (isset($nameI[$i]) && $nameI[$i] != '') || 
                (isset($type[$i]) && $type[$i] != '') ){
                $ingredients[$i] = [
                    'quantity' => $quantity[$i], 
                    'name' => $nameI[$i], 
                    'type' => $type[$i]] ;

            }
        }

        $nameIFR = $_GET["nameIFR"] ?? [];
        $quantityFR = $_GET["quantityFR"] ?? [];
        $typeFR = $_GET["typeFR"] ?? [];

        $ingredientsFR = []; // Initialisation du tableau des ingrédients français
        
        // Remplissage du tableau d'ingrédients français si les champs sont non vides
        for ($i = 0; $i < $countIFR; $i++){
            if ((isset($quantityFR[$i]) && $quantityFR[$i] != '') || 
                (isset($nameIFR[$i]) && $nameIFR[$i] != '') || 
                (isset($typeFR[$i]) && $typeFR[$i] != '') ){
                $ingredientsFR[$i] = [
                    'quantity' => $quantityFR[$i], 
                    'name' => $nameIFR[$i], 
                    'type' => $typeFR[$i]] ;

            }
        }

        // Récupération des étapes et timers pour les deux langues
        $steps = $_GET["steps"] ?? [];
        $stepsFR = $_GET["stepsFR"] ?? [];

        $timers = $_GET["timers"] ?? [];

        $without = $_GET["without"]?? []; // Récupère les restrictions alimentaires

        $Author = $_SESSION["username"]; // Nom de l’auteur récupéré depuis la session

        $found = false; // Sert à vérifier si la recette existe déjà

        $message = '';

        // Traitement de la soumission du formulaire
        if ($done){
            if ((isset($name) && $name != '' ) || (isset($nameFR) && $nameFR != '' )) {
                // Vérifie si une recette avec ce nom existe déjà
                foreach ($contenu as $c){
                    if (isset($c['name']) && $c['name'] == $name && $name != '') {
                        $found = true;
                        break;
                    } else if (isset($c['nameFR']) && $c['nameFR'] == $nameFR && $nameFR != '') {
                        $found = true;
                        break;
                    } 
                }
                // Si la recette n'existe pas, on l'ajoute
                if (!$found) {
                    $contenu[]  = [
                        'name' => $name, 
                        'nameFR' => $nameFR, 
                        'Author' => $Author,
                        'Without' => $without, 
                        'ingredients' => $ingredients , 
                        'ingredientsFR' => $ingredientsFR , 
                        'steps' => $steps, 
                        'stepsFR' => $stepsFR, 
                        'timers' => $timers,
                        'imageURL' => null]; // image laissée vide ici
                }  
            } else {
                $message = "Please enter a name"; // Nom manquant
            }

        }
        // Ce bloc génère dynamiquement un formulaire HTML bilingue (anglais/français) pour saisir une nouvelle recette 
        // avec ses ingrédients, étapes et restrictions. Il permet d'ajouter dynamiquement des champs (quantité, nom, 
        // type, étapes, minuteurs) et vérifie si la recette existe déjà pour éviter les doublons.
        // Une fois le formulaire soumis, les données sont sauvegardées dans le fichier recipes.json
        echo ('<form method="GET" class="translation-container">

                            <div class="boxleft">
                                <h2>English Version</h2>
                                ');
                            if ($found){
                                echo('<p id= "erreur"> Recette existante </p>');
                            }
                            
                    echo('   <strong>Recipe Name:</strong> <input type = "text"  name = "name" value = "'.$name.'"><br>
                                    <br><br>

                                    <strong>Without:</strong> <br>
                                    <input type="checkbox" id="w1" name="without[]" value="Vegan" >
                                    <label for="w1"> Vegan</label><br>
                                    <input type="checkbox" id="w2" name="without[]" value="Vegetarian">
                                    <label for="w2"> Vegetarian </label><br>
                                    <input type="checkbox" id="w3" name="without[]" value="NoMilk">
                                    <label for="w3"> NoMilk </label><br>
                                    <input type="checkbox" id="w4" name="without[]" value="NoGluten">
                                    <label for="w4"> NoGluten </label><br>
                                    <br><br>

                                    <strong>Ingredients:</strong> 
                                    ');
                                    for ($i = 0; $i < $countI; $i++):
                                    echo('<br><br> Quantity <input type = "text"  name = "quantity[]" value = "'.($quantity[$i] ?? '').'"> 
                                            <br> Name <input type = "text"  name = "nameI[]" value = "'.($nameI[$i] ?? '').'">

                                            <br> Type <input type = "text"  name = "type[]" value = "'.($type[$i] ?? '').'">
                                            ');

                                    endfor; echo('
                                    <input type= "hidden" name="countI" value="'.$countI.'">
                                    <br><button  class = "bouton" name = "addI" > + </button>
                                    <br><br>

                                    <strong>Steps:</strong> ');
                                    for ($i = 0; $i < $countS; $i++):
                                        echo('<br><input type = "text"  name = "steps[]" value = "'.($steps[$i] ?? '').'"> ');
                                    endfor; echo('
                                    <input type= "hidden" name="countS" value="'.$countS.'">
                                    <br><button  class = "bouton" name = "addS" > + </button>
                                    <br><br>

                                    <strong>Timers:</strong> ');

                                    
                                        $t = $countS;
                                    
                                    for ($i = 0; $i < $t; $i++):
                                        echo('<br><input type = "text"  name = "timers[]" value = "'.($timers[$i] ?? '').'"> ');
                                    endfor; echo('
                                    <br><br>
                                

                                <div class = "boutons">
                                    <input type = "submit" class = "bouton" name = "poster" value = "Poster">
                                </div>

                            </div>

                            <div class="boxright">
                                <h2>Version Française</h2> ');
                            if ($found){
                                echo('<p id= "erreur"> Recette existante </p>');
                            }
                            
                    echo('   <strong>Nom de la recette:</strong> <input type = "text"  name = "nameFR" value = "'.$nameFR.'"><br>
                                    <br><br>

                                    <strong>Sans:</strong> <br>
                                    <input type="checkbox" id="w1" name="without[]" value="Vegan">
                                    <label for="w1"> Vegan</label><br>
                                    <input type="checkbox" id="w2" name="without[]" value="Vegetarian">
                                    <label for="w2"> Vegetarian </label><br>
                                    <input type="checkbox" id="w3" name="without[]" value="NoMilk">
                                    <label for="w3"> NoMilk </label><br>
                                    <input type="checkbox" id="w4" name="without[]" value="NoGluten">
                                    <label for="w4"> NoGluten </label><br>
                                    <br><br>

                                    <strong>Ingrédients:</strong> 
                                    ');
                                    for ($i = 0; $i < $countIFR; $i++):
                                    echo('<br><br> Quantité <input type = "text"  name = "quantityFR[]" value = "'.($quantityFR[$i] ?? '').'"> 
                                            <br> Nom <input type = "text"  name = "nameIFR[]" value = "'.($nameIFR[$i] ?? '').'">

                                            <br> Type <input type = "text"  name = "typeFR[]" value = "'.($typeFR[$i] ?? '').'">
                                            ');

                                    endfor; echo('
                                    <input type= "hidden" name="countIFR" value="'.$countIFR.'">
                                    <br><button  class = "bouton" name = "addIFR" > + </button>
                                    <br><br>

                                    <strong>Etapes:</strong> ');
                                    for ($i = 0; $i < $countSFR; $i++):
                                        echo('<br><input type = "text"  name = "stepsFR[]" value = "'.($stepsFR[$i] ?? '').'"> ');
                                    endfor; echo('
                                    <input type= "hidden" name="countSFR" value="'.$countSFR.'">
                                    <br><button  class = "bouton" name = "addSFR" > + </button>
                                    <br><br>

                                    <strong>Temps:</strong>  ');

                                    
                                        $tfr = $countSFR;
                                    
                                    for ($i = 0; $i < $tfr; $i++):
                                        echo('<br><input type = "text"  name = "timers[]" value = "'.($timers[$i] ?? '').'"> ');
                                    endfor; echo('
                                    <br><br>
                                

                                <div class = "boutons">
                                    <input type = "submit" class = "bouton" name = "poster" value = "Poster">
                                </div>

                            </div>
                            <br>
                        </form>');
        $file = json_encode($contenu, JSON_PRETTY_PRINT);
        file_put_contents('recipes.json', $file);
        ?>
    </body>
</html>