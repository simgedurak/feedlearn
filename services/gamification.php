<?php
// services/gamification.php

require_once __DIR__ . '/../config/db.php';

// --- PROGRESS & GAMIFICATION SERVICE ---

/**
 * Returns user's current progress: level_score, current_level, badge_name, etc.
 * Creates a record if it doesn't exist.
 */
function progress_get($userId)
{
    if (!$userId)
        return null;
    $pdo = db_connect();

    // 1. Fetch current state (using user_gamification table as user_progress)
    $stmt = $pdo->prepare("SELECT * FROM user_gamification WHERE user_id = :uid");
    $stmt->execute(['uid' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        // Safe default init
        $pdo->prepare("INSERT OR IGNORE INTO user_gamification (user_id, xp, level, current_level) VALUES (:uid, 0, 1, 'A1')")
            ->execute(['uid' => $userId]);
        $row = ['user_id' => $userId, 'xp' => 0, 'level' => 1, 'current_level' => 'A1'];
    }

    // Map DB columns to logical names if needed, but we'll stick to 'xp' as 'level_score'
    // Calculate derived fields for UI
    $score = (int) $row['xp'];

    // Determine thresholds
    // 0–149 => A1, 150–299 => A2, 300–499 => B1, 500–699 => B2, 700–849 => C1, 850+ => C2
    $levels = [
        ['name' => 'A1', 'min' => 0, 'max' => 149],
        ['name' => 'A2', 'min' => 150, 'max' => 299],
        ['name' => 'B1', 'min' => 300, 'max' => 499],
        ['name' => 'B2', 'min' => 500, 'max' => 699],
        ['name' => 'C1', 'min' => 700, 'max' => 849],
        ['name' => 'C2', 'min' => 850, 'max' => 999999]
    ];

    $currentLevel = 'A1';
    $nextLevel = 'A2';
    $nextThreshold = 150;

    foreach ($levels as $i => $lvl) {
        if ($score >= $lvl['min'] && $score <= $lvl['max']) {
            $currentLevel = $lvl['name'];
            $next = $levels[$i + 1] ?? null;
            if ($next) {
                $nextLevel = $next['name'];
                $nextThreshold = $next['min']; // Start of next level
            } else {
                $nextLevel = 'Max';
                $nextThreshold = $score; // Cap
            }
            break;
        }
    }

    // Backward Compatibility & UI Props
    $row['current_level'] = $currentLevel;
    $row['level_score'] = $score;
    $row['next_level'] = $nextLevel;
    $row['next_threshold'] = $nextThreshold;

    // Legacy keys expected by header.php
    $row['progress_xp'] = $score; // Show total score
    $row['next_badge_name'] = ($nextLevel === 'Max') ? 'Max Level' : "Level $nextLevel";
    $row['next_badge_xp'] = $nextThreshold; // New key to help UI
    $row['badge_name'] = "Level $currentLevel";

    // Percent calculation
    if ($nextLevel === 'Max') {
        $row['progress_percent'] = 100;
    } else {
        // Calculate percent within the level range? Or total?
        // UI bar expects 0-100.
        // If score is 50/150 (A1), percent is 33%.
        // If score is 200/300 (A2, starts at 150), range is 150..300.
        // Let's do range-based percent for the bar:
        $currentMin = $lvl['min'] ?? 0;
        $range = $nextThreshold - $currentMin;
        $inLevelScore = $score - $currentMin;
        $row['progress_percent'] = ($range > 0) ? round(($inLevelScore / $range) * 100) : 100;

        // For 'progress_xp' display, user might prefer "50 / 150".
        // Let's keep 'progress_xp' as TOTAL score for now, but UI might say "X / Y". 
        // We will fix UI to say "X / Y".
    }

    return $row;
}

/**
 * Core function to add XP safely (Idempotent via eventKey)
 */
function progress_add_xp($userId, $xp, $eventKey)
{
    if (!$userId || $xp <= 0)
        return 0;
    $pdo = db_connect();

    try {
        $pdo->beginTransaction();

        // 1. Check Idempotency (log table)
        // Note: We use 'reason' column to store 'eventKey' for compatibility if not altering schema,
        // OR we assume reason is the key. User asked for event_key.
        // We reused 'reason' in previous step as unique key. We will use that.
        $stmt = $pdo->prepare("SELECT id FROM xp_log WHERE user_id = :uid AND reason = :key");
        $stmt->execute(['uid' => $userId, 'key' => $eventKey]);
        if ($stmt->fetch()) {
            $pdo->commit();
            return 0; // Already awarded
        }

        // 2. Log it
        $stmtLog = $pdo->prepare("INSERT INTO xp_log (user_id, attempt_id, reason, xp_awarded, created_at) VALUES (:uid, 0, :key, :xp, CURRENT_TIMESTAMP)");
        $stmtLog->execute(['uid' => $userId, 'key' => $eventKey, 'xp' => $xp]);

        // 3. Update User Score
        $stmtUpd = $pdo->prepare("UPDATE user_gamification SET xp = xp + :xp, updated_at = CURRENT_TIMESTAMP WHERE user_id = :uid");
        $stmtUpd->execute(['xp' => $xp, 'uid' => $userId]);

        // 4. Update Level Label in DB based on new score
        // We fetch new score to compute level
        $stmtGet = $pdo->prepare("SELECT xp FROM user_gamification WHERE user_id = :uid");
        $stmtGet->execute(['uid' => $userId]);
        $newScore = (int) $stmtGet->fetchColumn();

        // Compute Level
        $lvlName = 'A1';
        if ($newScore >= 850)
            $lvlName = 'C2';
        elseif ($newScore >= 700)
            $lvlName = 'C1';
        elseif ($newScore >= 500)
            $lvlName = 'B2';
        elseif ($newScore >= 300)
            $lvlName = 'B1';
        elseif ($newScore >= 150)
            $lvlName = 'A2';

        // Store
        $pdo->prepare("UPDATE user_gamification SET current_level = :lvl, level = :l_int WHERE user_id = :uid")
            ->execute(['lvl' => $lvlName, 'l_int' => 1, 'uid' => $userId]); // level int is less relevant now, keeping 1 or deriving

        $pdo->commit();

        // CHECK DAILY GOAL BONUS AFTERWARD
        _check_daily_goal_bonus($userId);

        return $xp;

    } catch (Exception $e) {
        if ($pdo->inTransaction())
            $pdo->rollBack();
        return 0;
    }
}

// Helpers for specific events

function progress_award_quiz($userId, $attemptId, $accuracy)
{
    $xp = 10;
    $evKey = "quiz_complete:$attemptId";
    $total = progress_add_xp($userId, $xp, $evKey);

    if ($accuracy >= 80) {
        $bonusKey = "quiz_bonus80:$attemptId";
        $total += progress_add_xp($userId, 10, $bonusKey);
    }
    return $total;
}

function progress_award_writing($userId, $submissionId)
{
    return progress_add_xp($userId, 20, "writing_submit:$submissionId");
}

function progress_award_speaking($userId, $submissionId)
{
    return progress_add_xp($userId, 20, "speaking_submit:$submissionId");
}

function progress_award_daily_goal($userId, $dateYmd)
{
    return progress_add_xp($userId, 15, "daily_goal:$dateYmd");
}

function _check_daily_goal_bonus($userId)
{
    // Auto-check if goals are met
    $progress = db_get_todays_goal_progress($userId);
    if ($progress['done'] >= 3) { // Assuming 3 is the target
        progress_award_daily_goal($userId, date('Y-m-d'));
    }
}

// Keeping getters for UI compatibility
function get_user_gamification($userId)
{
    return progress_get($userId);
}
function award_xp($userId, $amount, $reason, $attemptId)
{
    return progress_add_xp($userId, $amount, $reason . ":$attemptId");
}

function get_xp_earned_for_attempt($userId, $attemptId)
{
    if (!$userId || !$attemptId)
        return 0;
    $pdo = db_connect();
    $stmt = $pdo->prepare("SELECT SUM(xp_awarded) as total FROM xp_log WHERE user_id = :uid AND attempt_id = :aid");
    $stmt->execute(['uid' => $userId, 'aid' => $attemptId]);
    return (int) $stmt->fetchColumn();
}

// 2. Get List of Today's Goals
function db_get_todays_goals($userId)
{
    if (!$userId)
        return [];
    $pdo = db_connect();
    $today = date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT a.activity_type, COUNT(*) as count 
        FROM evaluations e 
        JOIN activities a ON e.activity_id = a.id 
        WHERE e.user_id = :uid 
        AND date(e.created_at) = :today 
        GROUP BY a.activity_type
    ");
    $stmt->execute(['uid' => $userId, 'today' => $today]);
    $counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    $definitions = [
        'complete_quiz' => ['type' => 'quiz', 'title' => 'Complete a Quiz', 'icon' => '⚡'],
        'submit_writing' => ['type' => 'writing', 'title' => 'Submit Writing', 'icon' => '✍️'],
        'practice_speaking' => ['type' => 'speaking', 'title' => 'Practice Speaking', 'icon' => '🎤']
    ];

    $goals = [];
    foreach ($definitions as $key => $def) {
        $count = $counts[$def['type']] ?? 0;
        $isDone = $count > 0;
        $goals[] = [
            'key' => $key,
            'title' => $def['title'],
            'icon' => $def['icon'],
            'is_done' => $isDone
        ];
    }
    return $goals;
}

function db_get_todays_goal_progress($userId)
{
    $goals = db_get_todays_goals($userId);
    return ['done' => count(array_filter($goals, fn($g) => $g['is_done'])), 'total' => count($goals)];
}

/**
 * Returns detailed achievement stats for the Achievements Page
 */
function achievements_get($userId)
{
    if (!$userId)
        return null;
    $progress = progress_get($userId);
    $xp = (int) ($progress['xp'] ?? 0);

    // Badge Tiers
    $tiers = [
        ['name' => 'Bronze Learner', 'min' => 0, 'max' => 99, 'icon' => '🥉', 'color' => '#cd7f32'],
        ['name' => 'Silver Learner', 'min' => 100, 'max' => 199, 'icon' => '🥈', 'color' => '#c0c0c0'],
        ['name' => 'Gold Learner', 'min' => 200, 'max' => 299, 'icon' => '🥇', 'color' => '#ffd700'],
        ['name' => 'Platinum Learner', 'min' => 300, 'max' => 499, 'icon' => '💠', 'color' => '#e5e4e2'],
        ['name' => 'Diamond Learner', 'min' => 500, 'max' => 999999, 'icon' => '💎', 'color' => '#b9f2ff']
    ];

    $currentBadge = $tiers[0];
    $nextBadge = null;

    // Determine Current & Next
    foreach ($tiers as $i => $tier) {
        if ($xp >= $tier['min']) {
            $currentBadge = $tier;
            $nextBadge = $tiers[$i + 1] ?? null;
        }
    }

    // Calculate Progress to next
    $progressToNext = 100;
    $xpToNext = 0;

    if ($nextBadge) {
        $range = $nextBadge['min'] - $currentBadge['min'];
        $earned = $xp - $currentBadge['min'];
        $progressToNext = min(100, round(($earned / $range) * 100));
        $xpToNext = $nextBadge['min'] - $xp;
    }

    // Build Badge List with Unlock State
    $allBadges = [];
    foreach ($tiers as $tier) {
        $isUnlocked = $xp >= $tier['min'];
        $isCurrent = $currentBadge['name'] === $tier['name'];
        $allBadges[] = array_merge($tier, [
            'unlocked' => $isUnlocked,
            'current' => $isCurrent
        ]);
    }

    return [
        'xp' => $xp,
        'current_badge' => $currentBadge,
        'next_badge' => $nextBadge,
        'xp_to_next' => $xpToNext,
        'progress_percent' => $progressToNext,
        'all_badges' => $allBadges
    ];
}
?>