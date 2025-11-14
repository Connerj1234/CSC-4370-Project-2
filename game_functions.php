<?php

function get_base_values() {
    return [
        1, 5, 10, 25, 50, 75, 100, 200,
        300, 400, 500, 750, 1000, 5000, 10000, 25000
    ];
}

function initialize_game() {
    $base = get_base_values();
    shuffle($base);

    $_SESSION['cases'] = [];
    $i = 0;
    foreach (range(1, 16) as $caseNum) {
        $_SESSION['cases'][$caseNum] = $base[$i];
        $i++;
    }

    $_SESSION['player_case'] = null;
    $_SESSION['opened_cases'] = [];
    $_SESSION['round'] = 1;
    $_SESSION['cases_opened_this_round'] = 0;
    $_SESSION['offer_history'] = [];
    $_SESSION['current_offer'] = null;
    $_SESSION['last_opened_value'] = null;
    $_SESSION['last_event'] = "";
    $_SESSION['value_shuffle_done'] = false;
    $_SESSION['shuffle_message'] = "";
    $_SESSION['deal_taken'] = false;
    $_SESSION['final_payout'] = null;
}

function get_remaining_values() {
    $values = [];
    foreach ($_SESSION['cases'] as $num => $value) {
        if (!in_array($num, $_SESSION['opened_cases'])) {
            $values[] = $value;
        }
    }
    return $values;
}

function avg($arr) {
    if (empty($arr)) {
        return 0;
    }
    return array_sum($arr) / count($arr);
}

/**
 * Volatile Market Events:
 * Random surge or crash that scales unopened case values.
 */
function apply_market_event() {
    $chance = rand(1, 100);

    if ($chance <= 35) {
        $type = rand(0, 1) === 0 ? 'surge' : 'crash';
        $factor = ($type === 'surge') ? 1.15 : 0.85;

        foreach ($_SESSION['cases'] as $num => $value) {
            if ($num === $_SESSION['player_case']) {
                continue;
            }
            if (in_array($num, $_SESSION['opened_cases'])) {
                continue;
            }
            $newValue = max(1, round($value * $factor));
            $_SESSION['cases'][$num] = $newValue;
        }

        if ($type === 'surge') {
            $_SESSION['last_event'] =
                "Volatile market surge: remaining briefcases quietly increased in value.";
        } else {
            $_SESSION['last_event'] =
                "Market correction: remaining briefcases lost some value.";
        }
    } else {
        $_SESSION['last_event'] =
            "Calm market: no major changes to briefcase values this round.";
    }
}

/**
 * Dynamic Round Structure:
 * At round 3 we introduce a twist that swaps two unopened briefcase values.
 */
function maybe_reassign_values() {
    if ($_SESSION['round'] === 3 && !$_SESSION['value_shuffle_done']) {
        $remainingCases = [];
        foreach ($_SESSION['cases'] as $num => $val) {
            if ($num !== $_SESSION['player_case'] &&
                !in_array($num, $_SESSION['opened_cases'])) {
                $remainingCases[] = $num;
            }
        }

        if (count($remainingCases) >= 2) {
            $a = $remainingCases[array_rand($remainingCases)];
            do {
                $b = $remainingCases[array_rand($remainingCases)];
            } while ($b === $a);

            $temp = $_SESSION['cases'][$a];
            $_SESSION['cases'][$a] = $_SESSION['cases'][$b];
            $_SESSION['cases'][$b] = $temp;

            $_SESSION['value_shuffle_done'] = true;
            $_SESSION['shuffle_message'] =
                "Mid game twist: the Banker secretly reassigned values between two unopened briefcases.";
        }
    }
}

/**
 * Banker's Strategic Offers with bluff and pressure behavior.
 */
function calculate_banker_offer() {
    $remaining = get_remaining_values();
    if (empty($remaining)) {
        return null;
    }

    $average = avg($remaining);

    // Volatility from last market event
    $volatility = 0.0;
    if (strpos($_SESSION['last_event'], 'surge') !== false) {
        $volatility = 0.1;
    } elseif (strpos($_SESSION['last_event'], 'correction') !== false) {
        $volatility = -0.1;
    }

    $roll = rand(1, 100);
    if ($roll <= 25) {
        $type = 'pressure';
        $multiplier = 0.55 + $volatility;
        $description = "A cautious pressure offer designed to push you into taking a safe payout.";
    } elseif ($roll <= 70) {
        $type = 'fair';
        $multiplier = 0.8 + $volatility;
        $description = "A balanced offer that reflects the current risk and reward on the board.";
    } else {
        $type = 'bluff';
        $multiplier = 1.05 + $volatility;
        $description = "A generous bluff offer from a nervous Banker who hopes you will cash out.";
    }

    $amount = max(1, round($average * $multiplier));
    $amount = floor($amount / 10) * 10;

    $offer = [
        'round' => $_SESSION['round'],
        'amount' => $amount,
        'type' => $type,
        'description' => $description
    ];

    $_SESSION['current_offer'] = $offer;
    $_SESSION['offer_history'][] = $offer;

    return $offer;
}

/**
 * Progressive Value Revelation:
 * Opening cases one by one and tracking reveal history.
 */
function open_case($caseNum, $roundStructure) {
    if (!array_key_exists($caseNum, $_SESSION['cases'])) {
        return;
    }
    if (in_array($caseNum, $_SESSION['opened_cases'])) {
        return;
    }
    if ($caseNum === $_SESSION['player_case']) {
        return;
    }

    $_SESSION['opened_cases'][] = $caseNum;
    $_SESSION['last_opened_value'] = $_SESSION['cases'][$caseNum];
    $_SESSION['cases_opened_this_round']++;

    $currentRound = $_SESSION['round'];
    $casesRequired = isset($roundStructure[$currentRound])
        ? $roundStructure[$currentRound] : 1;

    if ($_SESSION['cases_opened_this_round'] >= $casesRequired || game_should_end()) {
        calculate_banker_offer();
        $_SESSION['awaiting_decision'] = true;
    }
}

/**
 * Checks if the game should end because only one case remains unopened.
 */
function game_should_end() {
    $totalCases = count($_SESSION['cases']);
    $openedCount = count($_SESSION['opened_cases']);
    return ($totalCases - $openedCount) <= 1;
}
