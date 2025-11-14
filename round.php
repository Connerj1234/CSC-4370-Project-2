<?php
session_start();
require_once 'game_functions.php';

if (!isset($_SESSION['cases'])) {
    header("Location: index.php");
    exit;
}

if (is_null($_SESSION['player_case'])) {
    header("Location: briefcase.php");
    exit;
}

$roundStructure = [
    1 => 4,
    2 => 3,
    3 => 2,
    4 => 2,
    5 => 1,
    6 => 1
];

if (!isset($_SESSION['cases_opened_this_round'])) {
    $_SESSION['cases_opened_this_round'] = 0;
}
if (!isset($_SESSION['awaiting_decision'])) {
    $_SESSION['awaiting_decision'] = false;
}

if (game_should_end()) {
    $_SESSION['deal_taken'] = false;
    $_SESSION['final_payout'] = $_SESSION['cases'][$_SESSION['player_case']];
    header("Location: summary.php");
    exit;
}

// Handle posted actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['open_case']) && !$_SESSION['awaiting_decision']) {
        $caseToOpen = (int) $_POST['open_case'];
        open_case($caseToOpen, $roundStructure);
    } elseif (isset($_POST['decision']) && $_SESSION['awaiting_decision']) {
        if ($_POST['decision'] === 'deal') {
            $_SESSION['deal_taken'] = true;
            $_SESSION['final_payout'] = $_SESSION['current_offer']['amount'];
            header("Location: summary.php");
            exit;
        } elseif ($_POST['decision'] === 'nodeal') {
            $_SESSION['round']++;
            $_SESSION['cases_opened_this_round'] = 0;
            $_SESSION['awaiting_decision'] = false;
            $_SESSION['current_offer'] = null;
            $_SESSION['last_opened_value'] = null;
        }
    }
}

// At start of a fresh round
if (!$_SESSION['awaiting_decision'] && $_SESSION['cases_opened_this_round'] === 0) {
    apply_market_event();
    maybe_reassign_values();
}

$round = $_SESSION['round'];
$cases = $_SESSION['cases'];
$playerCase = $_SESSION['player_case'];
$opened = $_SESSION['opened_cases'];
$remainingValues = get_remaining_values();
$boardValues = get_base_values();

$currentOffer = $_SESSION['current_offer'];
$lastOpenedValue = $_SESSION['last_opened_value'];
$lastEvent = $_SESSION['last_event'];
$shuffleMessage = isset($_SESSION['shuffle_message']) ? $_SESSION['shuffle_message'] : "";
$casesToOpenThisRound = isset($roundStructure[$round]) ? $roundStructure[$round] : 1;
$casesLeftToOpen = max(0, $casesToOpenThisRound - $_SESSION['cases_opened_this_round']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deal or No Deal - Round <?php echo $round; ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="page">
<div class="container">
    <header class="header">
        <h1>Round <?php echo $round; ?></h1>
        <p class="tagline">
            Player case: <strong>#<?php echo $playerCase; ?></strong> (value hidden)
        </p>
    </header>

    <main class="panel panel-flex">
        <section class="section flex-main">
            <?php if ($lastOpenedValue !== null): ?>
                <div class="flash-message">
                    You opened a case worth <strong>$<?php echo number_format($lastOpenedValue); ?></strong>.
                </div>
            <?php endif; ?>

            <?php if ($lastEvent): ?>
                <div class="event-message">
                    <?php echo htmlspecialchars($lastEvent); ?>
                </div>
            <?php endif; ?>

            <?php if ($shuffleMessage): ?>
                <div class="event-message twist">
                    <?php echo htmlspecialchars($shuffleMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['awaiting_decision'] && $currentOffer): ?>
                <h2>Banker's Offer</h2>
                <p class="offer-text">
                    The Banker studies the remaining values and sends you a
                    <strong><?php echo ucfirst($currentOffer['type']); ?> offer</strong>:
                </p>
                <p class="offer-amount">
                    $<?php echo number_format($currentOffer['amount']); ?>
                </p>
                <p class="offer-description">
                    <?php echo htmlspecialchars($currentOffer['description']); ?>
                    This offer is recorded in your session history for this round
                    and expires once you choose an option.
                </p>

                <form method="post" class="decision-form">
                    <button type="submit" name="decision" value="deal" class="btn-deal">
                        Deal
                    </button>
                    <button type="submit" name="decision" value="nodeal" class="btn-nodeal">
                        No Deal
                    </button>
                </form>
            <?php else: ?>
                <h2>Open Briefcases</h2>
                <p class="instructions">
                    Open <?php echo $casesLeftToOpen; ?>
                    <?php echo $casesLeftToOpen === 1 ? "briefcase" : "briefcases"; ?> this round.
                </p>

                <form method="post" class="briefcase-grid">
                    <?php foreach ($cases as $num => $value): ?>
                        <?php
                        $isOpened = in_array($num, $opened);
                        $isPlayer = ($num === $playerCase);
                        $classes = "briefcase";
                        if ($isOpened) {
                            $classes .= " opened";
                        } elseif ($isPlayer) {
                            $classes .= " player-case";
                        }
                        ?>
                        <?php if ($isOpened): ?>
                            <div class="<?php echo $classes; ?>">
                                <span class="briefcase-label"><?php echo $num; ?></span>
                                <span class="briefcase-value">$<?php echo number_format($value); ?></span>
                            </div>
                        <?php elseif ($isPlayer): ?>
                            <div class="<?php echo $classes; ?>">
                                <span class="briefcase-label"><?php echo $num; ?></span>
                                <span class="briefcase-tag">Your Case</span>
                            </div>
                        <?php else: ?>
                            <button type="submit"
                                    name="open_case"
                                    value="<?php echo $num; ?>"
                                    class="<?php echo $classes; ?>">
                                <span class="briefcase-label"><?php echo $num; ?></span>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </form>
            <?php endif; ?>
        </section>

        <aside class="section sidebar">
            <h2>Value Board</h2>
            <ul class="value-board">
                <?php
                $remainingCopy = $remainingValues;
                foreach ($boardValues as $v):
                    $stillInPlayIndex = array_search($v, $remainingCopy);
                    $inPlay = ($stillInPlayIndex !== false);
                    if ($inPlay) {
                        unset($remainingCopy[$stillInPlayIndex]);
                    }
                    ?>
                    <li class="<?php echo $inPlay ? 'value-live' : 'value-gone'; ?>">
                        $<?php echo number_format($v); ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <h3>Round Status</h3>
            <ul class="status-list">
                <li>Round: <?php echo $round; ?></li>
                <li>Briefcases remaining (including yours):
                    <?php echo count($cases) - count($opened); ?>
                </li>
                <li>Cases opened this round:
                    <?php echo $_SESSION['cases_opened_this_round']; ?>
                    / <?php echo $casesToOpenThisRound; ?>
                </li>
                <li>Average remaining value:
                    $<?php echo number_format(avg($remainingValues)); ?>
                </li>
            </ul>
        </aside>
    </main>
</div>
</body>
</html>
