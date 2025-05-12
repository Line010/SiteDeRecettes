<?php
    // Afficher les erreurs
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();
    //var_dump($_SESSION['role']);
    //$username = $_SESSION['username'] ?? '';

    // Charger les recettes depuis le fichier JSON
    $recipes = json_decode(file_get_contents('recipes.json'), true);
    
    // On stoquera les informations de la recette à afficher dans cette variable
    $recipeData = null;
    $nameRecipe = $_GET["id"] ?? null;

    // Si la recette existe dans le json et si elle n'est pas vide
    if ($nameRecipe && is_array($recipes)) {
        //On utilise & pour passer par référence pour modifier recipe dans json directement car nous allons ajouter la traduction
        foreach ($recipes as &$recipe) {
            if ($recipe['name'] === $nameRecipe || $recipe['nameFR'] === $nameRecipe) {
                $recipeData = &$recipe;

                $recipeData['name'] = $recipeData['name'] ?? '';
                $recipeData['nameFR'] = $recipeData['nameFR'] ?? '';
                
                //On crée des sous variable pour chaque ingrédient ou un array vide si la section ingredient est vide dans les deux langues
                $recipeData['ingredients'] = $recipeData['ingredients'] ?? array_fill(0, count($recipeData['ingredientsFR']), ['quantity' => '', 'type' => '', 'name' => '']);
                $recipeData['ingredientsFR'] = $recipeData['ingredientsFR'] ?? array_fill(0, count($recipeData['ingredients']), ['quantity' => '', 'type' => '', 'name' => '']);
                
                //On force que steps soit un tableau associatif pour un meileur affichage
                $recipeData['steps'] = isset($recipeData['steps']) && is_array($recipeData['steps']) ? array_combine(array_keys($recipeData['steps']), array_values($recipeData['steps'])) : [];
                $recipeData['stepsFR'] = isset($recipeData['stepsFR']) && is_array($recipeData['stepsFR']) ? array_combine(array_keys($recipeData['stepsFR']), array_values($recipeData['stepsFR'])) : [];

                if (empty($recipeData['steps']) && !empty($recipeData['stepsFR'])) {
                    $recipeData['steps'] = array_fill(0, count($recipeData['stepsFR']), '');
                }
                if (empty($recipeData['stepsFR']) && !empty($recipeData['steps'])) {
                    $recipeData['stepsFR'] = array_fill(0, count($recipeData['steps']), '');
                }
                break;
            }
        }
    }

    // Vérifier si c’est l’auteur
    $isAuthor = $recipeData && $_SESSION['username'] === ($recipeData['Author'] ?? '');
    $isAdmin = in_array("Admin", $_SESSION['role']);
    // Traitement du formulaire
    // On vérifie si l'utilisateur a envoyer le formulaire et que la recette a été trouvé
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($recipeData)) {
        
        // Traduction EN -> FR (si le bouton a été cliqué)
        if (isset($_POST['translateFR'])) {
            if(!empty ($_POST['nameFR'])){
                $recipeData['nameFR'] = $_POST['nameFR'];
            }

            foreach ($recipeData['ingredients'] as $i => $ing) {
                //Si une traductiona été fournite pour les ingredients on associe a chaque quantité type et nom leur valeur
                if (!empty($_POST['ingredientsFR'][$i]['name'])) {
                    $recipeData['ingredientsFR'][$i] = [
                        'quantity' => $_POST['ingredientsFR'][$i]['quantity'] ?? '',
                        'type' => $_POST['ingredientsFR'][$i]['type'] ?? '',
                        'name' => $_POST['ingredientsFR'][$i]['name'] ?? ''
                    ];
                } 
            }
            //Si une traductiona été fournite pour les étapes on les enregistre dans recipeData
            //On utilise un indice pour chaque valeur pour un meilleur affichage et association entre l'étape et sa traduction
            foreach ($recipeData['steps'] as $i => $step) {
                if (!empty($_POST['stepsFR'][$i])) {
                    $recipeData['stepsFR'][$i] = $_POST['stepsFR'][$i];
                }
            }
        }
        
        // Traduction FR -> EN
        if (isset($_POST['translateEN'])) {
            // Les variables du tableau $recipeData sont modifiées localement car on utilise directement leur index
            if(!empty ($_POST['name'])){
                $recipeData['name'] = $_POST['name'];
            }
            // On met à jour les ingrédients à partir des données envoyées par le formulaire
            foreach ($recipeData['ingredientsFR'] as $i => $ingFR) {
                if (!empty($_POST['ingredients'][$i])) {
                    $recipeData['ingredients'][$i] = [
                        'quantity' => $_POST['ingredients'][$i]['quantity'] ?? '',
                        'type' => $_POST['ingredients'][$i]['type'] ?? '',
                        'name' => $_POST['ingredients'][$i]['name'] ?? ''
                    ];
                }
            }
        
            // On met à jour les étapes envoyées par le formulaire
            foreach ($recipeData['stepsFR'] as $i => $stepFR) {
                if (!empty($_POST['steps'][$i])) {
                    $recipeData['steps'][$i] = $_POST['steps'][$i];
                }
            }
        }
        //Modifier le Without anglais
        if (isset($_POST['Without'])){
            //Si le tableau de Filtre n'existe pas on l'ajoute 
            if (!isset($recipeData['Without'])) {
                $recipeData['Without'] = [];
            }
            if (!isset($recipeData['WithoutFR'])) {
                $recipeData['WithoutFR'] = [];
            }
            foreach ($_POST['Without'] as $value) {
                if (!in_array($value, $recipeData['Without'])) {
                    $recipeData['Without'][] = $value;
                }
                if (!in_array($value, $recipeData['WithoutFR'])) {
                    $recipeData['WithoutFR'][] = $value;
                }
            }
        }

        //Ajouter un ingrédient
        if(isset($_POST['addIngr'])){
            $newIngr = [
                "quantity" => "",
                "name" => "",
                "type" => ""
            ];
            $recipeData['ingredients'][] = $newIngr;
            $recipeData['ingredientsFR'][] = $newIngr;
        }
        //Ajouter une étape
        if(isset($_POST['addStep'])){
            $recipeData['steps'][] = "";
            $recipeData['stepsFR'][] = "";
        }
        
        // Supprimer un ingrédient anglais et sa traduction française
        if (isset($_POST['deleteIngr'])) {
            $i = (int)$_POST['deleteIngr'];

            if (isset($recipeData['ingredients'][$i]) || isset($recipeData['ingredientsFR'][$i])) {
                unset($recipeData['ingredients'][$i]);
                unset($recipeData['ingredientsFR'][$i]);
            }

            // Réindexer les deux tableaux pour éviter les trous
            $recipeData['ingredients'] = array_values($recipeData['ingredients']);
            $recipeData['ingredientsFR'] = array_values($recipeData['ingredientsFR']);
        }

        // Supprimer un ingrédient anglais et sa traduction française
        if (isset($_POST['deleteStep'])) {
            $i = (int)$_POST['deleteStep'];

            if (isset($recipeData['steps'][$i]) || isset($recipeData['stepsFR'][$i])) {
                unset($recipeData['steps'][$i]);
                unset($recipeData['stepsFR'][$i]);
            }

            // Réindexer les deux tableaux pour éviter les trous
            $recipeData['steps'] = array_values($recipeData['steps']);
            $recipeData['stepsFR'] = array_values($recipeData['stepsFR']);
        }

        file_put_contents('recipes.json', json_encode($recipes, JSON_PRETTY_PRINT));
    
    }
?>
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
            <a href="recipe.php?id=<?php echo urlencode($nameRecipe); ?>">
                <img src="../Registration/Images/backbtn.png" width="40" height="40">
            </a>
            <div class="center-content-translate">
                <img src="../Registration/Images/profilee.png" width="25" height="25">
                <div class="profile-title">Traduction</div>
            </div>
        </div>
        <div class="translate-page-container">
            <?php if ($recipeData): ?>
                <?php
                    //Variables qui renvoient vrai si les champs existent
                    $hasNameEN = !empty($recipeData['name']);
                    $hasNameFR = !empty($recipeData['nameFR']);
                ?>
                <form method="POST" class="translation-container">
                    <!------------------ Version anglaise ------------------------------------------------------------>
                    <div class="boxleft">
                        <h2>English Version</h2>

                        <!-- Affichage du nom de la recette en anglais  -->
                        <div class="traductiontxt">
                            <strong>Recipe Name:</strong>
                            <!-- Le chef peut mettre à jour toute ca recette -->
                            <?php if ($isAuthor || $isAdmin): ?>
                                <input type="text" style = "width: 250px;" name="name" value= "<?php echo$recipeData['name']; ?>" placeholder="Name Recipe" style="width: 200px;">
                                <!-- <button type="submit" name="translateEN">Traduire Nom</button>  -->
                            <!-- Si la recette existe en anglais on l'affiche -->
                            <?php elseif ($hasNameEN): ?>
                                <span class="contenuTrad"><?php echo $recipeData['name']; ?></span>
                            <!-- Sinon on affiche un champs à saisir pour traduire -->
                            <?php elseif ($hasNameFR): ?>
                                <input type="text" name="name" placeholder="Name in English" style="width: 200px;">
                                <!-- <button type="submit" name="translateEN">Traduire Nom</button>  -->
                            <?php endif; ?>
                        </div>
                        
                        <div class="traductiontxt">
                            <?php if ($isAdmin || $isAuthor): ?>
                                <?php $selectedWithout = $recipeData['Without'] ?? []; ?>

                                <input class="checkbox" type="checkbox" id="w1" name="Without[]" value="Vegan"
                                    <?php if (in_array('Vegan', $selectedWithout)) echo 'checked'; ?>>
                                <label for="w1">Vegan</label><br>

                                <input class="checkbox" type="checkbox" id="w2" name="Without[]" value="Vegetarian"
                                    <?php if (in_array('Vegetarian', $selectedWithout)) echo 'checked'; ?>>
                                <label for="w2">Vegetarian</label><br>

                                <input class="checkbox" type="checkbox" id="w3" name="Without[]" value="NoMilk"
                                    <?php if (in_array('NoMilk', $selectedWithout)) echo 'checked'; ?>>
                                <label for="w3">NoMilk</label><br>

                                <input class="checkbox" type="checkbox" id="w4" name="Without[]" value="NoGluten"
                                    <?php if (in_array('NoGluten', $selectedWithout)) echo 'checked'; ?>>
                                <label for="w4">NoGluten</label><br>
                            <?php endif; ?>

                        </div>

                        <div class="traductiontxt">
                        <strong>Ingredients:</strong>
                            <ul class="contenuTrad">
                                <?php foreach ($recipeData['ingredients'] as $i => $ingEN): ?>
                                    <li>
                                        <!-- Si l'utilisateur est l'auteur et les ingrédient en anglais existent il peut les voir et les modifier -->
                                        <?php if ($isAuthor || $isAdmin): ?>
                                            <input style = "width: 60px;" type="text" name="ingredients[<?php echo $i; ?>][quantity]" value= "<?php echo $ingEN['quantity']; ?>" placeholder="Quantity">
                                            <input style = "width: 100px;" type="text" name="ingredients[<?php echo $i; ?>][type]" value= "<?php echo $ingEN['type']; ?>" placeholder="Type">
                                            <input style = "width: 150px;" type="text" name="ingredients[<?php echo $i; ?>][name]" value= "<?php echo $ingEN['name']; ?>" placeholder="Name">
                                            
                                            <!-- Boutons translate et delete -->
                                            <!-- <button type="submit" name="translateEN">Translate ingr</button> -->
                                            <button type="submit" name="deleteIngr" value="<?php echo $i; ?>" style="background: none; border: none; padding: 0; cursor: pointer;">
                                                <img src="../Registration/Images/delete.png" width="20" height="20" style="vertical-align: middle;">
                                            </button>

                                        <!-- Si les ingrédient en anglais existent on les affichent -->
                                        <?php elseif (!empty($ingEN['quantity']) &&  !empty($ingEN['type']) && !empty($ingEN['name']) ): ?>
                                            <div class = "ingredient-line">
                                                <align style="color: #007bff;"><strong>Quantity:</strong></align> <?php echo $ingEN['quantity']; ?>
                                                <align style="color: #28a745;"><strong>Type:</strong></align> <?php echo $ingEN['type']; ?>
                                                <align style="color:rgb(214, 3, 246);"><strong>Name:</strong></align> <?php echo $ingEN['name']; ?>
                                            </div>
                                        <!-- Si une version en francais existe on ajoute des formulaire de saisie pour traduire -->
                                        <?php elseif (!empty($recipeData['ingredientsFR'][$i]['name'])): ?>
                                            <div class = "ingredient-line">
                                                <input style = "width: 60px;" type="text" name="ingredients[<?php echo $i; ?>][quantity]"  value= "<?php echo $ingEN['quantity']; ?>" placeholder="Quantity">
                                                <input style = "width: 100px;" type="text" name="ingredients[<?php echo $i; ?>][type]"  value= "<?php echo $ingEN['type']; ?>" placeholder="Type">
                                                <input style = "width: 150px;" type="text" name="ingredients[<?php echo $i; ?>][name]"  value= "<?php echo $ingEN['name']; ?>" placeholder="Name">
                                                <!-- <button type="submit" name="translateEN">Translate ingredient</button> -->
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($isAdmin || $isAuthor): ?>
                                    <button type="submit" name="addIngr" value="addIngr" style="background: none; border: none; padding: 0; cursor: pointer;">
                                    <img src="../Registration/Images/addRecipe.png" width="30" height="30" style="vertical-align: middle;">
                                </button>
                                <?php endif;?>
                            </ul>
                        </div>

                        <div class="traductiontxt">
                            <strong>Steps:</strong>
                            <ol class="contenuTrad">
                                <?php 
                                    $maxSteps = max(count($recipeData['steps']), count($recipeData['stepsFR']));
    
                                    for ($i = 0; $i < $maxSteps; $i++): 
                                        $stepFR = $recipeData['stepsFR'][$i] ?? '';
                                        $stepEN = $recipeData['steps'][$i] ?? '';
                                ?>
                                    <li>
                                        <!-- Si l'utilisateur est l'auteur il peut les voir et les modifier -->
                                        <?php if ($isAuthor || $isAdmin): ?>
                                            <input type="text" style="width:325px;" name="steps[<?php echo $i; ?>]" value= "<?php echo $stepEN; ?>" placeholder="Traduction de l'étape">
                                            <!-- <button type="submit" name="translateEN">Translate step</button> -->
                                            <button type="submit" name="deleteStep" value="<?php echo $i; ?>" style="background: none; border: none; padding: 0; cursor: pointer;">
                                                <img src="../Registration/Images/delete.png" width="20" height="20" style="vertical-align: middle;">
                                            </button>

                                        <!-- Si les étape en français existent on les affichent -->
                                        <?php elseif (!empty($stepEN)): ?>
                                            <?php echo $stepEN; ?>

                                        <!-- Si une version en francais existe on ajoute des formulaire de saisie pour traduire -->
                                        <?php elseif (!empty($recipeData['stepsFR'][$i])): ?>
                                            <input type="text" style="width:325px;" name="steps[<?php echo $i; ?>]" placeholder="Traduction de l'étape">
                                            <!-- <button type="submit" name="translateEN">Translate step</button>  -->
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($isAdmin || $isAuthor): ?>
                                    <button type="submit" name="addStep" value="addStep" style="background: none; border: none; padding: 0; cursor: pointer;">
                                    <img src="../Registration/Images/addRecipe.png" width="30" height="30" style="vertical-align: middle;">
                                </button>
                                <?php endif;?>
                                <div style="text-align: right;">
                                    <button type="submit" name="translateEN">Traduire</button>
                                </div>
                            </ol>
                        </div>
                    </div>

                    <!---------------------- Version Française ------------------------------------------------------------------->
                    <div class="boxright">
                        <h2>Version Française</h2>
                        <!-- Affichage du nom de la recette en français -->
                        <div class="traductiontxt">
                            <strong>Nom de la recette:</strong>
                            
                            <!-- L'autheur peut mettre à jour toute ca recette -->
                            <?php if ($isAuthor || $isAdmin):?>
                                <input type="text" name="nameFR" value= "<?php echo$recipeData['nameFR']; ?>" placeholder="Nom" style="width: 200px;">
                                <!-- <button type="submit" name="translateEN">Traduire Nom</button>  -->

                            <!-- Si la recette existe en français on l'affiche -->
                            <?php elseif ($hasNameFR):?>
                                <span class="contenuTrad"><?php echo $recipeData['nameFR']; ?></span>
                            
                            <!-- Si une version en anglais existe on ajoute des formulaire de saisie pour traduire -->
                            <?php elseif($hasNameEN):?>
                                <input type="text" name="nameFR" placeholder="Nom français" style="width: 200px;">
                                <!-- <button type="submit" name="translateEN">Traduire Nom</button>  -->
                            <?php endif; ?>
                        </div>

                        <div class="traductiontxt">
                            <strong>Ingrédients:</strong>
                            <ul class="contenuTrad">
                                <?php foreach ($recipeData['ingredientsFR'] as $i => $ingFR): ?>
                                    <li>
                                        <?php if ($isAuthor || $isAdmin): ?>
                                            <div class = "ingredient-line">
                                                <input style = "width: 60px;" type="text" name="ingredientsFR[<?php echo $i; ?>][quantity]" value= "<?php echo $ingFR['quantity']; ?>" placeholder="Quantité">
                                                <input style = "width: 100px;" type="text" name="ingredientsFR[<?php echo $i; ?>][type]" value= "<?php echo $ingFR['type']; ?>" placeholder="Type">
                                                <input style = "width: 150px;" type="text" name="ingredientsFR[<?php echo $i; ?>][name]" value= "<?php echo $ingFR['name']; ?>" placeholder="Nom">
                                                <!-- Boutton de traduction et delete -->
                                                <!-- <button type="submit" name="translateFR">Traduire ingr</button> -->
                                                <button type="submit" name="deleteStep" value="<?php echo $i; ?>" style="background: none; border: none; padding: 0; cursor: pointer;">
                                                <img src="../Registration/Images/delete.png" width="20" height="20" style="vertical-align: middle;">
                                            </button>
                                            </div>
                                        <!-- Si les ingrédient en français existent, on les affichent -->
                                        <?php elseif (!empty($ingFR['quantity']) && !empty($ingFR['type']) && !empty($ingFR['name'])): ?>
                                            <div class = "ingredient-line">
                                                <align style="color: #007bff;"><strong>Quantité:</strong></align> <?php echo ($ingFR['quantity']); ?>
                                                <align style="color: #28a745;"><strong>Type:</strong></align> <?php echo ($ingFR['type']); ?>
                                                <align style="color: rgb(214, 3, 246);"><strong>Nom:</strong></align> <?php echo ($ingFR['name']); ?>
                                            </div>
                                        <?php elseif (!empty($recipeData['ingredients'][$i]['name'])): ?>
                                            <!-- Si une version en anglais existe, on ajoute des formulaire pour traduire -->
                                            <div class = "ingredient-line">
                                                <input type="text" style = "width: 60px;" name="ingredientsFR[<?php echo $i; ?>][quantity]" value= "<?php echo $ingFR['quantity']; ?>" placeholder="Quantité" class="inline-input">
                                                <input type="text" style = "width: 100px;" name="ingredientsFR[<?php echo $i; ?>][type]" value= "<?php echo $ingFR['type']; ?>" placeholder="Type" class="inline-input">
                                                <input type="text" style = "width: 150px;" name="ingredientsFR[<?php echo $i; ?>][name]" value= "<?php echo $ingFR['name']; ?>" placeholder="Nom" class="wider-input">
                                                <!-- Un bouton pour traduire partiellement si souhaité -->
                                                <!-- <button type="submit" name="translateFR">Traduire ingrédient</button>  -->
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                                
                                <?php if ($isAdmin || $isAuthor): ?>
                                    <button type="submit" name="addIngr" value="addIngr" style="background: none; border: none; padding: 0; cursor: pointer;">
                                    <img src="../Registration/Images/addRecipe.png" width="30" height="30" style="vertical-align: middle;">
                                </button>
                                <?php endif?>
                                <!-- <button type="submit" name="translateFR">Traduire ingr</button> -->
                            </ul>
                        </div>

                        <div class="traductiontxt">
                            <strong>Étapes:</strong>
                            <ol class="contenuTrad">
                                <?php 
                                    $maxSteps = max(count($recipeData['steps']), count($recipeData['stepsFR']));
    
                                    for ($i = 0; $i < $maxSteps; $i++): 
                                        $stepFR = $recipeData['stepsFR'][$i] ?? '';
                                        $stepEN = $recipeData['steps'][$i] ?? '';
                                ?>
                                    <li>
                                        <!-- Si l'utilisateur est l'auteur il peut les voir et les modifier -->
                                        <?php if ($isAuthor || $isAdmin): ?>
                                            <input type="text" style="width:325px;" name="stepsFR[<?php echo $i; ?>]" value= "<?php echo $stepFR; ?>" placeholder="Traduction de l'étape">
                                            <!-- <button type="submit" name="translateFR">Traduire l'étape</button> -->

                                        <!-- Si les ingrédient en français existent, on les affichent -->
                                        <?php elseif (!empty($stepFR)): ?>
                                            <?php echo htmlspecialchars($stepFR); ?>
                                        <?php elseif (!empty($recipeData['steps'][$i])): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="text" style="width:325px;" name="stepsFR[<?php echo $i; ?>]" placeholder="Traduction de l'étape">
                                                <!-- <button type="submit" name="translateFR">Traduire étape</button> -->
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($isAdmin || $isAuthor): ?>
                                    <button type="submit" name="addStep" value="addStep" style="background: none; border: none; padding: 0; cursor: pointer;">
                                    <img src="../Registration/Images/addRecipe.png" width="30" height="30" style="vertical-align: middle;">
                                </button>
                                <?php endif?>
                                <!-- On ajoute un boutton de soumission pour toute les traduction française-->
                                <div style="text-align: right;">
                                    <button type="submit" name="translateFR">Traduire</button>
                                </div>
                            </ol>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <p>Recette non trouvée.</p>
            <?php endif; ?>
        </div>
    </body>
</html>
