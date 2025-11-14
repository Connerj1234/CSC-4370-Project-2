<?php
session_start();
require_once 'game_functions.php';

/*
 * When the Start Game button is clicked, this same page
 * receives a POST. We initialize the game and send the
 * player straight to the briefcase selection screen.
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    initialize_game();
    header("Location: briefcase.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deal or No Deal - Homepage</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page">
<div class="container">
    <header class="header">
        <h1>Deal or No Deal: High Stakes Negotiation</h1>
        <p class="tagline">
            A PHP powered remake of the classic game with strategic banker offers,
            volatile market events, and dynamic rounds.
        </p>
    </header>

    <main class="panel">
        <section class="section">
            <h2>Game Overview</h2>
            <p>
                Select one briefcase to protect as your own. Open the remaining briefcases in
                suspenseful rounds while an algorithmic Banker watches the board and sends you
                calculated offers. Will you accept a guaranteed deal, or risk everything for
                what might be in your briefcase?
            </p>
        </section>

        <section class="section">
            <h2>How To Play</h2>
            <ol class="rules">
                <li>Choose a briefcase on the next screen to keep as your case.</li>
                <li>Open briefcases each round to reveal and remove values.</li>
                <li>After each round the Banker sends an offer based on remaining values.</li>
                <li>Market events and twists can boost or cut remaining values.</li>
                <li>Choose DEAL to take the offer or NO DEAL to continue playing.</li>
                <li>At the end, your final winnings and full statistics are revealed.</li>
            </ol>
        </section>

        <!-- Same page handles the POST -->
        <form method="post" class="center">
            <button type="submit" class="btn-primary">Start Game</button>
        </form>
    </main>
</div>
</body>
</html>
