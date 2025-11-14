<?php
session_start();
require_once 'game_functions.php';

if (!isset($_SESSION['cases'])) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['case'])) {
    $chosen = intval($_POST['case']);
    if (array_key_exists($chosen, $_SESSION['cases'])) {
        $_SESSION['player_case'] = $chosen;
    }
}

header("Location: round.php");
exit;
