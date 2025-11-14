<?php
session_start();
require_once 'game_functions.php';

if (!isset($_SESSION['cases'])) {
    header("Location: index.php");
    exit;
}

if (!is_null($_SESSION['player_case'])) {
    header("Location: round.php");
    exit;
}

$cases = $_SESSION['cases'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Your Briefcase</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page">
<div class="container">
    <header class="header">
        <h1>Select Your Briefcase</h1>
        <p class="tagline">
            Choose carefully. The hidden value inside this case will stay with you
            for the rest of the game.
        </p>
    </header>

    <main class="panel">
        <p class="instructions">
            Click a briefcase number to claim it as your own.
        </p>

        <form action="choose_case.php" method="post" class="briefcase-grid">
            <?php foreach ($cases as $num => $value): ?>
                <button type="submit"
                        name="case"
                        value="<?php echo $num; ?>"
                        class="briefcase">
                    <span class="briefcase-label"><?php echo $num; ?></span>
                </button>
            <?php endforeach; ?>
        </form>
    </main>
</div>
</body>
</html>
