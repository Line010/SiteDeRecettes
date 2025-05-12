<?php
// deleteCookies.php

if (isset($_POST['action']) && $_POST['action'] === 'expire') {
    // Suppression des cookies
    setcookie('username', '', time() - 3600, '/'); // Expire le cookie 'username'
    setcookie('time', '', time() - 3600, '/'); // Expire le cookie 'time'
    setcookie('role', '', time() - 3600, '/'); // Expire le cookie 'role'
    // Tu peux ajouter d'autres cookies à supprimer si nécessaire

    // Affichage pour débogage
    echo "Cookies deleted successfully.";

    // Optionnel : Vérifier si les cookies ont bien été supprimés
    if (!isset($_COOKIE['username'])) {
        echo "username cookie is deleted.";
    } else {
        echo "username cookie is still present.";
    }
}
?>
