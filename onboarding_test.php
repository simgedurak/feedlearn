<?php
// onboarding_test.php
require_once __DIR__ . '/auth/_guard.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/services/gamification.php'; // To sync level

require_login();
$user = current_user();
$pdo = db_connect();

// 1. Check if already taken
$stmt = $pdo->prepare("SELECT id FROM onboarding_attempts WHERE user_id = :uid");
$stmt->execute(['uid' => $user['id']]);
if ($stmt->fetch()) {
    redirect('/dashboard.php');
}

// 2. Questions Data (Mixed Difficulty)
// 0-3: A1/A2, 4-6: B1/B2, 7-9: C1
$questions = [
    [
        'q' => "I ______ from France.",
        'o' => ['am', 'is', 'are', 'be'],
        'c' => 0 // am
    ],
    [
        'q' => "They ______ football every Sunday.",
        'o' => ['play', 'plays', 'playing', 'played'],
        'c' => 0 // play
    ],
    [
        'q' => "She ______ to the cinema yesterday.",
        'o' => ['go', 'goes', 'went', 'gone'],
        'c' => 2 // went
    ],
    [
        'q' => "If I ______ you, I would study harder.",
        'o' => ['was', 'am', 'were', 'be'],
        'c' => 2 // were (Conditionals)
    ],
    [
        'q' => "I have been living here ______ 2010.",
        'o' => ['for', 'since', 'from', 'during'],
        'c' => 1 // since
    ],
    [
        'q' => "The book ______ by J.K. Rowling.",
        'o' => ['wrote', 'was written', 'was writing', 'writes'],
        'c' => 1 // was written (Passive)
    ],
    [
        'q' => "I look forward ______ you soon.",
        'o' => ['to see', 'see', 'to seeing', 'seeing'],
        'c' => 2 // to seeing
    ],
    [
        'q' => "______ giving up, he continued the race.",
        'o' => ['Despite', 'Although', 'Even', 'In spite'],
        'c' => 0 // Despite
    ],
    [
        'q' => "It's high time we ______ home.",
        'o' => ['go', 'went', 'gone', 'going'],
        'c' => 1 // went (Subjunctive-like)
    ],
    [
        'q' => "Scarcely had he entered the room ______ the phone rang.",
        'o' => ['than', 'when', 'then', 'after'],
        'c' => 1 // when (Inversion)
    ]
];

// 3. Handle Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = 0;
    $total = count($questions);

    foreach ($questions as $idx => $q) {
        $userAns = $_POST["q_$idx"] ?? null;
        if ($userAns !== null && (int) $userAns === $q['c']) {
            $score++;
        }
    }

    $percent = ($score / $total) * 100;

    // Estimate Level
    /*
      0–39%   → A1  
      40–59%  → A2  
      60–74%  → B1  
      75–89%  → B2  
      90%+    → C1
    */
    if ($percent >= 90)
        $level = 'C1';
    elseif ($percent >= 75)
        $level = 'B2';
    elseif ($percent >= 60)
        $level = 'B1';
    elseif ($percent >= 40)
        $level = 'A2';
    else
        $level = 'A1';

    // Initialize Start XP based on Level
    // A1=0, A2=150, B1=300, B2=500, C1=700
    $startXp = 0;
    switch ($level) {
        case 'A2':
            $startXp = 150;
            break;
        case 'B1':
            $startXp = 300;
            break;
        case 'B2':
            $startXp = 500;
            break;
        case 'C1':
            $startXp = 700;
            break;
    }
    // Give a small bonus for taking the test to ensure they feel progress
    $startXp += 10;

    // SAVE
    try {
        $pdo->beginTransaction();

        // 1. Save Attempt
        $stmt = $pdo->prepare("INSERT INTO onboarding_attempts (user_id, score_percent, estimated_level) VALUES (:uid, :score, :lvl)");
        $stmt->execute(['uid' => $user['id'], 'score' => $percent, 'lvl' => $level]);

        // 2. Update Progress (Force set level)
        // Check if user_gamification exists
        $stmtG = $pdo->prepare("SELECT xp FROM user_gamification WHERE user_id = :uid");
        $stmtG->execute(['uid' => $user['id']]);
        if ($stmtG->fetch()) {
            // Update Existing
            // Only update if current XP is lower than placement (don't downgrade active users)
            $pdo->prepare("UPDATE user_gamification SET xp = MAX(xp, :xp), current_level = :lvl WHERE user_id = :uid")
                ->execute(['xp' => $startXp, 'lvl' => $level, 'uid' => $user['id']]);
        } else {
            // Insert New
            $pdo->prepare("INSERT INTO user_gamification (user_id, xp, level, current_level) VALUES (:uid, :xp, 1, :lvl)")
                ->execute(['uid' => $user['id'], 'xp' => $startXp, 'lvl' => $level]);
        }

        $pdo->commit();

        // Redirect with toast flag
        redirect('/dashboard.php?onboarding_complete=1&level=' . $level);

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error saving result: " . $e->getMessage());
    }
}

// 4. Render View
require_once __DIR__ . '/templates/header.php';
?>

<div class="section">
    <div class="card" style="max-width: 800px; margin: 0 auto; text-align: center;">
        <h2>Welcome to FeedLearn! 👋</h2>
        <p class="muted">Let's find your starting level. This will take about 1 minute.</p>
    </div>

    <form method="POST" class="card" style="max-width: 800px; margin: 24px auto;">
        <?php foreach ($questions as $idx => $q): ?>
            <div class="question-block"
                style="margin-bottom: 32px; text-align: left; border-bottom: 1px solid var(--border-light); padding-bottom: 24px;">
                <p style="font-weight: 600; font-size: 1.1rem; margin-bottom: 16px;">
                    <span style="color:var(--primary); margin-right:8px;">
                        <?= $idx + 1 ?>.
                    </span>
                    <?= htmlspecialchars($q['q']) ?>
                </p>
                <div class="options-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
                    <?php foreach ($q['o'] as $optIdx => $opt): ?>
                        <label class="quiz-option" style="margin:0; padding: 12px 16px; font-size: 1rem;">
                            <input type="radio" name="q_<?= $idx ?>" value="<?= $optIdx ?>" required
                                style="width: auto; margin-right: 8px;">
                            <?= htmlspecialchars($opt) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="text-align: center; padding-top: 10px;">
            <button type="submit" class="btn btn-primary" style="font-size: 1.2rem; padding: 16px 48px;">
                Finish Test & See My Level 🚀
            </button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>