
<?php
//session_start();

if (!isset($_COOKIE['username']) || !isset($_COOKIE['time'])) {
    expireMessage();
    exit();
}

// Vérifie le temps restant
$remainingTime = intval($_COOKIE['time']) / 1000 - time();

if ($remainingTime <= 0) {
    expireMessage();
    exit();
}

$years = floor($remainingTime / (365 * 24 * 60 * 60));
$remainingTime %= (365 * 24 * 60 * 60);

$days = floor($remainingTime / (24 * 60 * 60));
$remainingTime %= (24 * 60 * 60);

$hours = floor($remainingTime / (60 * 60));
$remainingTime %= (60 * 60);

$minutes = floor($remainingTime / 60);

echo "Remaining time: $years year(s), $days day(s), $hours hour(s), $minutes minute(s).";

function expireMessage() {
    echo '<div class="container">
            <div class="box form-box">
                <div style="font-weight: bold; font-size: 25px; color:red; display: flex; justify-content: center; align-items: center;">
                    Votre session a expiré :( <br>Veuillez vous reconnecter.
                </div>
                <input id="expireButton" type="button" name="expire" class="btn" value="Se reconnecter"
                    onclick="window.location.href=\'../Registration/login.php\';">
            </div>
        </div>';
}
?>
