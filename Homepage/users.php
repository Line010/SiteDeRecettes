<?php
    // Démarre la session et charge le fichier JSON des utilisateurs
    session_start();
    $file = file_get_contents('../Registration/users.json');
    $contenu = json_decode($file, true);

    // Traitement des demandes de rôle (acceptation/refus)
    if (isset($_GET["statut"])){
        if (isset($_GET["role"]) && $username = $_GET["user"]){
            $statut = $_GET["statut"];
            $role = $_GET["role"];
            $username = $_GET["user"];
            
            // Parcours tous les utilisateurs pour trouver celui concerné
            foreach ($contenu as &$c){
                $countR = count($c['role']);
                if ($c['username'] === $username){

                    // Parcours les rôles de l'utilisateur
                    for ($i = 0; $i < $countR; $i++):
                        // Gestion des demandes Chef
                        if ($role == "DemandeChef" && $c['role'][$i] == "DemandeChef"){
                            if ($statut === "accepter"){
                                $c['role'][$i] = "Chef";
                                
                            } else {
                                array_splice($c['role'], $i, 1);
                            }
                        }

                        // Gestion des demandes Traducteur
                        if ($role === "DemandeTraducteur" && $c['role'][$i] == "DemandeTraducteur"){
                            if ($statut === "accepter"){
                                $c['role'][$i] = "Traducteur";
                                
                            } else {
                                array_splice($c['role'], $i, 1);
                            }
                        } 
                    endfor;
                }
            }
        }
    }
    // Affichage de l'interface utilisateur
    echo ('<div class = "users">');
    if (isset($_GET["demande"])){
        echo('<h2> Demandes de rôle </h2>');
    } else {
        echo('<h2> Utilisateurs </h2>');
    }
    // Parcours et affiche chaque utilisateur
    foreach ($contenu as $v) {
        $username = $v['username'];
        $role = $v['role'];
        $img = "https://wallpapers.com/images/hd/round-cartoon-cute-cat-pfp-1pckh30lkr0ilv7m.jpg";
        
        // Ne pas afficher l'admin
        if (($role != "Admin" && $username != "Admin")){
            if (isset($_GET["demande"])){

                // Affichage différent selon si on veut voir les demandes ou tous les utilisateurs
                if(in_array("DemandeChef", $role) ||in_array("DemandeTraducteur", $role))
                {
                    // Affiche la carte utilisateur avec boutons d'action
                    echo ( '<div class = "user">  
                                <div class = "user_pic">
                                    <img src = "' . $img .'" alt = "pfp" wildth = 75 height = 75 > 
                                </div>
                                <div class = "user_text">
                                    <h3>'.$username.'</h3> ');
                    foreach ($role as $r) {
                        echo ($r);
                            if ($r === "DemandeChef" || $r === 'DemandeTraducteur'){
                                echo('<button type = "button" class = "boutonAcc" onclick = "demande(\''.$r.'\', \'accepter\', \''.$username.'\')" > Accepter </button>
                                        <button type = "button" class = "boutonRef" onclick = "demande(\''.$r.'\',\'refuser\', \''.$username.'\')" > Refuser </button>
                                        ');
                            } echo('<br>');
                    }
                    echo('      </div>
                            </div> <br>');
                }
            } else {
                // Affiche tous les utilisateurs (version complète)
                echo ( '<div class = "user">  
                            <div class = "user_pic">
                                <img src = "' . $img .'" alt = "pfp" wildth = 75 height = 75 > 
                            </div>
                            <div class = "user_text">
                                <h3>'.$username.'</h3> ');
                foreach ($role as $r) {
                    echo ($r);
                        if ($r === "DemandeChef" || $r === 'DemandeTraducteur'){
                            echo('<button type = "button" class = "boutonAcc" onclick = "demande(\''.$r.'\', \'accepter\', \''.$username.'\')" > Accepter </button>
                                    <button type = "button" class = "boutonRef" onclick = "demande(\''.$r.'\',\'refuser\', \''.$username.'\')" > Refuser </button>
                                    ');
                        } echo('<br>');
                }
                echo('      </div>
                        </div> <br>');
            }
        }
        
        
    }
    // Sauvegarde les modifications dans le fichier JSON
    $file = json_encode($contenu, JSON_PRETTY_PRINT);
    file_put_contents('../Registration/users.json', $file);              
?>