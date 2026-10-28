<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../auth/_guard.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_login();

/* =====================================================
   DATA: QUIZ SETS
   ===================================================== */
require_once __DIR__ . '/../config/quiz_data.php';
// $quiz_sets is now available from the included file

/* =====================================================
   LOGIC: DETERMINE STATE
   ===================================================== */
$setId = isset($_GET['set']) ? (int) $_GET['set'] : 0;
$currentSet = $quiz_sets[$setId] ?? null;

// Helper: Scoring
if (!function_exists('evaluate_quiz')) {
    function evaluate_quiz(array $questions, array $answers): array
    {
        $correct = 0;
        $weak = [];
        foreach ($questions as $q) {
            $ans = isset($answers[$q['id']]) ? (int) $answers[$q['id']] : -1;
            if ($ans === (int) $q['c']) {
                $correct++;
            } else {
                $weak[] = $q['t'];
            }
        }
        $score = $correct * (100 / count($questions)); // Dynamic scoring

        if ($score < 40)
            $cefr = 'A1';
        elseif ($score < 55)
            $cefr = 'A2';
        elseif ($score < 70)
            $cefr = 'B1';
        elseif ($score < 85)
            $cefr = 'B2';
        else
            $cefr = 'C1';

        return [
            'score' => $score,
            'cefr' => $cefr,
            'weak_topics' => array_values(array_unique($weak)),
            'correct' => $correct,
            'total' => count($questions)
        ];
    }
}

// Helper: AI Feedback
if (!function_exists('quiz_feedback_ai_assisted')) {
    function quiz_feedback_ai_assisted($score, $cefr, $weak, $correct, $total): string
    {
        $band = ($score >= 85) ? 'high' : (($score >= 60) ? 'medium' : 'low');
        require_once __DIR__ . '/../services/gemini_client.php';
        if (defined('GEMINI_API_KEY') && GEMINI_API_KEY !== '') {
            $topics = $weak ? implode(', ', $weak) : 'general accuracy';
            $prompt = "You are an English instructor. Student score: $score ($correct/$total). Level: $cefr. Weaknesses: $topics. Give short, constructive feedback (2 sent). Plain text.";
            try {
                $client = new GeminiClient();
                return $client->generateResponse($prompt, false);
            } catch (Exception $e) {
            }
        }
        if ($band === 'high')
            return "Excellent work! Your grasp of the material is strong.";
        if ($band === 'medium')
            return "Good effort. Review the missed topics to improve.";
        return "Keep practicing. Focus on the basics and try again.";
    }
}

/* =====================================================
   POST HANDLER
   ===================================================== */
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $currentSet) {
    $questions = $currentSet['questions'];
    $answers = [];
    foreach ($questions as $q) {
        if (!isset($_POST['q_' . $q['id']])) {
            $errors[] = 'Please answer all questions.';
            break;
        }
        $answers[$q['id']] = $_POST['q_' . $q['id']];
    }

    if (!$errors) {
        $base = evaluate_quiz($questions, $answers);
        $feedback = quiz_feedback_ai_assisted($base['score'], $base['cefr'], $base['weak_topics'], $base['correct'], $base['total']);

        // Find Activity ID for 'quiz'
        $activity = db_get_activity_by_type('quiz');

        $evalId = db_create_evaluation(
            current_user()['id'],
            $activity['id'],
            $base['score'],
            $base['cefr'],
            $feedback,
            $base['weak_topics'],
            'quiz',
            json_encode($answers)
        );

        // We might want to store WHICH set was taken. 
        // For now, we store answers which implies it, or we could append to note.
        // Simplified for this task.

        redirect('/results/feedback.php?id=' . $evalId);
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<!-- VIEW: SELECTION MENU -->
<?php if (!$currentSet): ?>
    <section class="section">
        <div class="card" style="margin-bottom: 20px;">
            <h2>Select a Quiz</h2>
            <p class="muted">Choose a topic to practice based on your level.</p>

            <!-- Tabs -->
            <div class="quiz-tabs"
                style="margin-top: 20px; display: flex; gap: 10px; overflow-x: auto; padding-bottom: 4px;">
                <button class="tab-pill active" data-filter="recommended">Recommended</button>
                <button class="tab-pill" data-filter="easy">Easy</button>
                <button class="tab-pill" data-filter="medium">Medium</button>
                <button class="tab-pill" data-filter="hard">Hard</button>
            </div>
        </div>

        <div class="grid" id="quiz-grid">
            <?php foreach ($quiz_sets as $id => $set):
                $diff = $set['difficulty'] ?? 'medium';
                $rec = !empty($set['recommended']) ? 'true' : 'false';
                ?>
                <div class="card quiz-card-item" data-difficulty="<?= $diff ?>" data-recommended="<?= $rec ?>">
                    <!-- Optional Badge for Recommended -->
                    <?php if ($rec === 'true'): ?>
                        <span
                            style="background:var(--secondary); color:#fff; font-size:0.7rem; padding:2px 8px; border-radius:99px; margin-bottom:8px; display:inline-block; font-weight:600;">RECOMMENDED</span>
                    <?php endif; ?>

                    <h3 class="card-title"><?= htmlspecialchars($set['title']) ?></h3>
                    <p class="card-desc"><?= htmlspecialchars($set['desc']) ?></p>
                    <div style="margin-top: auto; display:flex; justify-content:space-between; align-items:center;">
                        <span class="muted"
                            style="font-size:0.85rem; text-transform:uppercase; font-weight:600;"><?= $diff ?></span>
                        <a href="?set=<?= $id ?>" class="btn btn-primary">Start Quiz</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="margin-top:20px; text-align:center;">
            <a class="btn" href="<?= BASE_URL ?>/dashboard.php">Back to Dashboard</a>
        </div>

        <!-- Tab Logic -->
        <style>
            .tab-pill {
                padding: 8px 20px;
                border-radius: 99px;
                border: 1px solid var(--border-light);
                background: #fff;
                color: var(--text-main);
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                font-family: 'Outfit', sans-serif;
            }

            .tab-pill.active,
            .tab-pill:hover {
                background: var(--primary);
                color: #fff;
                border-color: var(--primary);
                box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
            }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const tabs = document.querySelectorAll('.tab-pill');
                const cards = document.querySelectorAll('.quiz-card-item');

                function filterCards(filter) {
                    cards.forEach(card => {
                        const diff = card.dataset.difficulty;
                        const rec = card.dataset.recommended;

                        let show = false;
                        if (filter === 'recommended') {
                            if (rec === 'true') show = true;
                        } else {
                            if (diff === filter) show = true;
                        }

                        // CRITICAL FIX: Use setProperty to override global .card { display: block !important; }
                        if (show) {
                            card.style.setProperty('display', 'block', 'important');
                        } else {
                            card.style.setProperty('display', 'none', 'important');
                        }
                    });
                }

                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        // Remove active from all
                        tabs.forEach(t => t.classList.remove('active'));
                        // Add to clicked
                        tab.classList.add('active');

                        const filter = tab.dataset.filter;
                        filterCards(filter);
                    });
                });

                // Initial Filter
                filterCards('recommended');
            });
        </script>
    </section>

    <!-- VIEW: QUIZ FORM -->
<?php else: ?>
    <!-- QUIZ FORM WITH MOdern UI -->
    <section class="section" style="margin-top:20px;">
        <form method="POST" class="card" id="quiz-form" style="position:relative;">

            <!-- Header Row with Title & Pill -->
            <div class="quiz-header-row">
                <h2><?= htmlspecialchars($currentSet['title']) ?></h2>
                <span class="quiz-progress-pill">
                    Question <span id="q-current">0</span> / <?= count($currentSet['questions']) ?>
                </span>
            </div>

            <!-- Progress Bar (Thin line below header) -->
            <!-- Progress Bar -->
            <div class="progress-track" style="background: var(--bg-body); height: 8px; border-radius: 99px; margin-bottom: 32px;">
                <div class="progress-fill" id="q-progress" style="width: 0%; border-radius: 99px;"></div>
            </div>
            <p class="muted" style="margin-bottom: 2rem;">Answer the following questions to test your knowledge.</p>

            <?php if ($errors): ?>
                <div class="error">
                    <?php foreach ($errors as $e)
                        echo "<p>$e</p>"; ?>
                </div>
            <?php endif; ?>

            <?php foreach ($currentSet['questions'] as $i => $q): ?>
                <div class="quiz-question" data-index="<?= $i + 1 ?>" style="margin-bottom: 40px;">
                    <p class="quiz-title" style="font-size:1.1rem; font-weight:600; margin-bottom:16px;">
                        <?= ($i + 1) ?>. <?= htmlspecialchars($q['q']) ?>
                    </p>

                    <div class="options-group" data-correct="<?= $q['c'] ?>">
                        <?php foreach ($q['o'] as $idx => $opt): ?>
                            <label class="quiz-option" data-idx="<?= $idx ?>">
                                <input type="radio" name="q_<?= $q['id'] ?>" value="<?= $idx ?>" required style="display:none;">
                                <span
                                    style="font-weight:600; color:var(--text-muted); margin-right:8px;"><?= chr(65 + $idx) ?>.</span>
                                <span><?= htmlspecialchars($opt) ?></span>
                                <span class="feedback-icon" style="float:right;"></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div style="margin-top: 32px; border-top:1px solid var(--border-light); padding-top:24px; text-align:right;">
                <button class="btn btn-primary" style="font-size:1.1rem; padding:16px 48px;">
                    Submit Assessment <i style="margin-left:8px;">➤</i>
                </button>
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('quiz-form');
            const total = <?= count($currentSet['questions']) ?>;
            const progressFill = document.getElementById('q-progress');
            const currentCounter = document.getElementById('q-current');

            // Interaction Logic
            const questions = document.querySelectorAll('.quiz-question');

            questions.forEach(qDiv => {
                const correctIdx = parseInt(qDiv.querySelector('.options-group').dataset.correct);
                const options = qDiv.querySelectorAll('.quiz-option');

                options.forEach(opt => {
                    opt.addEventListener('click', (e) => {
                        // Prevent changing if already answered (optional, strict mode)
                        // but user might want to change answer. Let's allow change but update visuals.

                        // Reset siblings
                        options.forEach(o => {
                            o.classList.remove('selected', 'correct', 'incorrect');
                            o.querySelector('.feedback-icon').innerText = '';
                        });

                        opt.classList.add('selected');
                        const myIdx = parseInt(opt.dataset.idx);

                        // Visual Feedback (Immediate)
                        if (myIdx === correctIdx) {
                            opt.classList.add('correct');
                            opt.querySelector('.feedback-icon').innerText = '✔';
                        } else {
                            opt.classList.add('incorrect');
                            opt.querySelector('.feedback-icon').innerText = '✖';

                            // Show hint for correct
                            /* 
                            options.forEach(o => {
                                if (parseInt(o.dataset.idx) === correctIdx) {
                                    o.style.border = '2px dashed var(--success)';
                                }
                            });
                            */
                        }

                        // Check inputs
                        opt.querySelector('input').checked = true;

                        updateProgress();
                    });
                });
            });

            function updateProgress() {
                const answered = document.querySelectorAll('input[type="radio"]:checked').length;
                const pct = (answered / total) * 100;
                progressFill.style.width = pct + '%';
                currentCounter.innerText = answered;
            }
        });
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>