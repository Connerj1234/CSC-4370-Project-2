<?php
session_start();
require_once 'game_functions.php';

if (!isset($_SESSION['cases']) || !isset($_SESSION['final_payout'])) {
    header("Location: index.php");
    exit;
}

$cases = $_SESSION['cases'];
$playerCase = $_SESSION['player_case'];
$playerValue = $cases[$playerCase];
$dealTaken = isset($_SESSION['deal_taken']) ? $_SESSION['deal_taken'] : false;
$finalPayout = $_SESSION['final_payout'];
$offerHistory = isset($_SESSION['offer_history']) ? $_SESSION['offer_history'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Game Summary</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page">
<div class="container">
    <header class="header">
        <h1>Game Summary</h1>
    </header>

    <main class="panel">
        <section class="section">
            <h2>Your Result</h2>
            <p class="summary-line">
                Your briefcase was <strong>#<?php echo $playerCase; ?></strong>
                with a value of
                <strong>$<?php echo number_format($playerValue); ?></strong>.
            </p>

            <?php if ($dealTaken): ?>
                <p class="summary-line">
                    You accepted a banker offer of
                    <strong>$<?php echo number_format($finalPayout); ?></strong>.
                </p>
                <p class="summary-line">
                    Difference between your deal and your case value:
                    <strong>$<?php echo number_format($finalPayout - $playerValue); ?></strong>.
                </p>
            <?php else: ?>
                <p class="summary-line">
                    You never took a deal. Your final winnings come directly from
                    your briefcase:
                    <strong>$<?php echo number_format($finalPayout); ?></strong>.
                </p>
            <?php endif; ?>
        </section>

        <section class="section">
            <h2>Banker Offer History</h2>
            <?php if (empty($offerHistory)): ?>
                <p>No offers were generated in this game.</p>
            <?php else: ?>
                <table class="history-table">
                    <tr>
                        <th>Round</th>
                        <th>Offer Amount</th>
                        <th>Offer Type</th>
                        <th>Description</th>
                    </tr>
                    <?php foreach ($offerHistory as $offer): ?>
                        <tr>
                            <td><?php echo $offer['round']; ?></td>
                            <td>$<?php echo number_format($offer['amount']); ?></td>
                            <td><?php echo ucfirst($offer['type']); ?></td>
                            <td><?php echo htmlspecialchars($offer['description']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </section>

        <section class="section">
            <h2>Play Again</h2>
            <p>
                Want to try a different negotiation path or see how market events
                can change your fate?
            </p>
            <form action="index.php" method="get" class="center">
                <button type="submit" class="btn-primary">Return to Homepage</button>
            </form>
        </section>
    </main>
</div>
</body>
</html>
