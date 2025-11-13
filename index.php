<?php
session_start();
session_unset();
session_destroy();
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
                Select one briefcase to protect as your own. Open the remaining
                briefcases in suspenseful rounds while an algorithmic Banker
                watches the board and sends you calculated offers. Will you
                accept a guaranteed deal, or risk everything for what might be
                in your briefcase?
            </p>
        </section>

        <section class="section">
            <h2>How To Play</h2>
            <ol class="rules">
                <li>Choose a briefcase on the next screen to keep as your case.</li>
                <li>Open briefcases each round to reveal and remove values.</li>
                <li>After each round the Banker sends an offer based on remaining values.</li>
                <li>Market events and mid game twists can boost or cut remaining values.</li>
                <li>Choose DEAL to take the offer or NO DEAL to continue playing.</li>
                <li>At the end, your final winnings and full statistics are revealed.</li>
            </ol>
        </section>

        <form action="setup.php" method="post" class="center">
            <button type="submit" class="btn-primary">Start Game</button>
        </form>
    </main>
</div>
</body>
</html>
