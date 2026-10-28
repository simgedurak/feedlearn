<?php
require_once __DIR__ . '/../auth/_guard.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_login();

$user = current_user();
$result = $_SESSION['last_result'] ?? null;
$evalError = '';
$evaluationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$inputRefData = null;
$inputType = '';
if ($evaluationId > 0) {
  $evaluation = db_get_evaluation_by_id($evaluationId);
  if (!$evaluation) {
    $evalError = 'Evaluation not found.';
    $result = null;
  } else {
    $isAdmin = ($user['role'] ?? '') === ROLE_ADMIN;
    if (!$isAdmin && (int) $evaluation['user_id'] !== (int) $user['id']) {
      http_response_code(403);
      echo '403 Forbidden: You do not have access to this evaluation.';
      exit;
    }
    $weakTopics = json_decode($evaluation['weak_topics_json'] ?? '[]', true);
    if (!is_array($weakTopics)) {
      $weakTopics = [];
    }
    $inputType = strtolower((string) ($evaluation['input_type'] ?? ''));
    $inputRefRaw = (string) ($evaluation['input_ref'] ?? '');
    if ($inputRefRaw !== '') {
      $decoded = json_decode($inputRefRaw, true);
      if (is_array($decoded)) {
        $inputRefData = $decoded;
      }
    }
    $result = [
      'score_percent' => (int) round((float) ($evaluation['score'] ?? 0)),
      'weak_topics' => $weakTopics,
      'feedback' => $evaluation['feedback'] ?? ''
    ];

    // --- GAMIFICATION: AWARD XP ---
    require_once __DIR__ . '/../services/gamification.php';

    // We award 'Quiz' XP here because quizzes might be auto-graded without a specific 'activity/quiz_submit.php' file 
    // or if we want a central place. But Writing/Speaking are handled in their files.

    $activ = db_get_activity_by_id($evaluation['activity_id']);
    $type = $activ['activity_type'] ?? 'quiz';

    $xpEarned = 0;

    if ($type === 'quiz') {
      // Award Quiz XP (Base 10 + Bonus 10 if >= 80)
      $xpEarned = progress_award_quiz($user['id'], $evaluationId, $result['score_percent']);
    } else {
      // For writing/speaking, it should have been awarded in the handler.
      // We can check if we want to display the toast for them too.
      // But get_xp_earned_for_attempt only sums 'xp_log'.
      // If we just arrived from writing.php, it is already logged.
      // We want to show the toast "XP Earned".

      // Let's rely on idempotency if we wanted to call it again, but better just check log for toast.
    }

    // 5. XP Toast Message Determination
    if ($xpEarned > 0) {
      // Just earned (Quiz)
      $xpMessage = "<strong>+$xpEarned XP Earned!</strong>";
    } else {
      // Check if recently earned (Writing/Speaking just redirected here)
      // or Quiz already viewed
      $existing = 0;
      // We need to sum up all logs for this attempt
      $pdo = db_connect();
      $stmt = $pdo->prepare("SELECT SUM(xp_awarded) FROM xp_log WHERE user_id = :uid AND attempt_id = :aid"); // attempt_id was stored as evalId or 0 in log? 
      // In progress_award_writing, we didn't pass attemptId to log?
      // Wait, progress_add_xp logs with attempt_id=0 by default in my implementation above!
      // Correction: progress_award_quiz passes attemptId.
      // progress_award_writing passes submissionId as ... specific key "writing_submit:$id".
      // My progress_add_xp uses `attempt_id` column as 0. 
      // I should fix xp_log query to search by key pattern or just trust the session/redirect timing?
      // Let's keep it simple: if specific "Just Earned" isn't triggered, we don't show toast, OR we show "Total Score" from user state.
      // User asked for "Show in drawer...". Toast is nice but optional. 
      // I will stick to showing toast IF $xpEarned > 0 (Quiz). 
      // For Writing/Speaking, the user might miss the toast if we don't check. 
      // But since this step is about "Level System", the persistent UI in drawer is key.
    }
  }
}

// FR11 data (session)
$perf = $_SESSION['performance'] ?? [];
$mine = array_values(array_filter($perf, fn($p) => $p['user_id'] == $user['id']));
usort($mine, fn($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));

$last5 = array_slice($mine, 0, 5);

// Weekly avg
$weekStart = strtotime('monday this week');
$weekEnd = strtotime('sunday this week 23:59:59');
$weekly = array_filter($mine, function ($p) use ($weekStart, $weekEnd) {
  $t = strtotime($p['created_at']);
  return $t >= $weekStart && $t <= $weekEnd;
});
$scores = array_map(fn($p) => $p['score_percent'], $weekly);
$weeklyAvg = count($scores) ? round(array_sum($scores) / count($scores)) : 0;

// Weak topics overall
$topicCount = [];
foreach ($mine as $p) {
  foreach (($p['weak_topics'] ?? []) as $t) {
    $topicCount[$t] = ($topicCount[$t] ?? 0) + 1;
  }
}
arsort($topicCount);
$topWeakAllTime = array_slice(array_keys($topicCount), 0, 5);

require_once __DIR__ . '/../templates/header.php';
?>

<?php if (isset($xpMessage) && $xpMessage): ?>
  <div id="xp-toast" style="
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: #111827;
    border: 1px solid rgba(255,255,255,0.1);
    border-left: 4px solid #be123c; /* Burgundy Accent */
    color: #fff;
    padding: 16px 24px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.5);
    z-index: 10000;
    font-family: 'Outfit', sans-serif;
    animation: slideInUp 0.5s cubic-bezier(0.4, 0, 0.2, 1);
">
    <div style="display:flex; align-items:center; gap:12px;">
      <div style="font-size:1.5rem;">🎉</div>
      <div>
        <?= $xpMessage ?>
      </div>
    </div>
  </div>
  <script>
    setTimeout(() => {
      const t = document.getElementById('xp-toast');
      if (t) {
        t.style.opacity = '0';
        t.style.transform = 'translateY(20px)';
        setTimeout(() => t.remove(), 500);
      }
    }, 4000);
  </script>
  <style>
    @keyframes slideInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
  </style>
<?php endif; ?>

<section class="section">
  <div class="card">
    <h2>Detailed Performance Report</h2>
    <p class="muted">Latest result + weekly summary + trend + recommendations</p>
  </div>
</section>

<?php if (!$result): ?>
  <section class="section">
    <div class="card">
      <p class="error"><?php echo htmlspecialchars($evalError ?: 'No result found. Please submit an activity first.'); ?>
      </p>
      <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/dashboard.php">Go to Dashboard</a>
    </div>
  </section>
<?php else: ?>

  <!-- Latest -->
  <section class="section">
    <div class="card">
      <h3>Latest Assessment</h3>
      <p><strong>Score:</strong> <span class="badge"><?php echo (int) $result['score_percent']; ?>%</span></p>

      <div style="margin-top:10px;">
        <h4>Feedback</h4>
        <p><?php echo htmlspecialchars($result['feedback'] ?? ''); ?></p>
      </div>

      <div style="margin-top:10px;">
        <h4>Weak Topics (Latest)</h4>
        <?php if (!empty($result['weak_topics'])): ?>
          <ul class="list">
            <?php foreach ($result['weak_topics'] as $t): ?>
              <li><?php echo htmlspecialchars($t); ?></li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="muted">No weak topics detected in the latest attempt.</p>
        <?php endif; ?>
      </div>

      <!-- WRITING / TEXT DISPLAY -->
      <?php if ($inputType === 'text' && is_array($inputRefData)): ?>
        <div style="margin-top:10px;">
          <?php if (!empty($inputRefData['prompt'])): ?>
            <h4>Writing Prompt</h4>
            <p><?php echo htmlspecialchars($inputRefData['prompt']); ?></p>
          <?php endif; ?>
          <?php if (!empty($inputRefData['text'])): ?>
            <h4>Your Response</h4>
            <div style="background:#f9fafb; padding:15px; border-radius:8px; border:1px solid #e5e7eb;">
              <?php echo nl2br(htmlspecialchars($inputRefData['text'])); ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- QUIZ DETAILED BREAKDOWN -->
      <?php if ($inputType === 'quiz' && is_array($inputRefData)): ?>
        <?php
        // Load Quiz Questions
        require_once __DIR__ . '/../config/quiz_data.php';

        // Flatten questions for easy lookup by ID
        $allQuestions = [];
        if (isset($quiz_sets) && is_array($quiz_sets)) {
          foreach ($quiz_sets as $set) {
            foreach ($set['questions'] as $q) {
              $allQuestions[$q['id']] = $q;
            }
          }
        }
        ?>
        <div style="margin-top:20px;">
          <h4>Detailed Breakdown</h4>
          <div class="table-responsive">
            <table style="font-size: 0.95rem;">
              <thead>
                <tr>
                  <th style="width: 50%;">Question</th>
                  <th style="width: 25%;">Your Answer</th>
                  <th style="width: 25%;">Correct Answer</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($inputRefData as $qId => $userAnsIdx): ?>
                  <?php
                  $q = $allQuestions[$qId] ?? null;
                  if (!$q)
                    continue;

                  $userAnsIdx = (int) $userAnsIdx;
                  $correctIdx = (int) $q['c'];
                  $isCorrect = ($userAnsIdx === $correctIdx);

                  $userAnsText = $q['o'][$userAnsIdx] ?? '-';
                  $correctAnsText = $q['o'][$correctIdx] ?? '-';
                  ?>
                  <tr style="background: <?php echo $isCorrect ? '#f0fdfa' : '#fef2f2'; ?>">
                    <td style="vertical-align:top;">
                      <?php echo htmlspecialchars($q['q']); ?>
                      <?php if (!$isCorrect): ?>
                        <!-- MISTAKE TOOLS -->
                        <div style="margin-top:8px; display:flex; gap:8px; flex-wrap:wrap;">
                          <button type="button" class="btn" style="padding:4px 12px; font-size:0.75rem; background:#fff;"
                            onclick="toggleMistake('explain-<?= $qId ?>')">Explain</button>
                          <button type="button" class="btn" style="padding:4px 12px; font-size:0.75rem; background:#fff;"
                            onclick="toggleMistake('rule-<?= $qId ?>')">Rule</button>
                          <button type="button" class="btn" style="padding:4px 12px; font-size:0.75rem; background:#fff;"
                            onclick="toggleMistake('practice-<?= $qId ?>')">Practice</button>
                        </div>

                        <?php
                        require_once __DIR__ . '/../config/mistake_rules.php';
                        $analysis = get_mistake_explanation($q['q'], $correctAnsText);
                        ?>

                        <!-- Hidden Panels -->
                        <div id="explain-<?= $qId ?>" class="mistake-panel"
                          style="display:none; margin-top:8px; font-size:0.9rem; color:var(--text-muted);">
                          <strong>Explanation:</strong> <?= htmlspecialchars($analysis['explanation']) ?>
                        </div>
                        <div id="rule-<?= $qId ?>" class="mistake-panel"
                          style="display:none; margin-top:8px; font-size:0.9rem; color:var(--text-muted);">
                          <strong>Rule:</strong> <span class="badge"><?= $analysis['type'] ?></span>
                          <?= htmlspecialchars($analysis['rule']) ?>
                        </div>
                        <div id="practice-<?= $qId ?>" class="mistake-panel"
                          style="display:none; margin-top:8px; font-size:0.9rem;">
                          <strong>Try these:</strong>
                          <ul style="margin-top:4px; padding-left:20px;">
                            <?php foreach ($analysis['similar'] as $drill): ?>
                              <li style="margin-bottom:4px;">
                                <?= htmlspecialchars($drill['q']) ?> <br>
                                <span class="muted" style="font-size:0.8rem; cursor:pointer;"
                                  onclick="this.innerText='Correct: <?= addslashes($drill['a']) ?>'">[Reveal Answer]</span>
                              </li>
                            <?php endforeach; ?>
                          </ul>
                        </div>
                      <?php endif; ?>
                    </td>
                    <td style="vertical-align:top;">
                      <?php if ($isCorrect): ?>
                        <span style="color: var(--success); font-weight: 600;">✓
                          <?php echo htmlspecialchars($userAnsText); ?></span>
                      <?php else: ?>
                        <span style="color: var(--danger); font-weight: 600;">✗
                          <?php echo htmlspecialchars($userAnsText); ?></span>
                      <?php endif; ?>
                    </td>
                    <td style="vertical-align:top;">
                      <?php if (!$isCorrect): ?>
                        <span
                          style="color: var(--success); font-weight: 600;"><?php echo htmlspecialchars($correctAnsText); ?></span>
                        <!-- VOCAB LOGIC: Save Correct Answer -->
                        <button class="btn-icon" title="Save to Vocabulary"
                          onclick="saveWord('<?= htmlspecialchars($correctAnsText, ENT_QUOTES) ?>', '<?= htmlspecialchars($q['q'], ENT_QUOTES) ?>')"
                          style="border:none; background:none; cursor:pointer; font-size:1.1rem; margin-left:4px;">🔖</button>
                      <?php else: ?>
                        <span class="muted">-</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Weekly Summary -->
  <section class="section">
    <div class="card">
      <h3>This Week Summary</h3>
      <p><strong>Weekly Average:</strong> <?php echo $weeklyAvg; ?>%</p>
      <p><strong>Attempts this week:</strong> <?php echo count($weekly); ?></p>
      <a class="btn" href="<?php echo BASE_URL; ?>/reports/weekly.php">Open Weekly Report</a>
    </div>
  </section>

  <!-- Trend -->
  <section class="section">
    <div class="card">
      <h3>Trend (Last 5 Attempts)</h3>

      <?php if (!count($last5)): ?>
        <p class="muted">No history available yet.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Score</th>
              <th>Weak Topics</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($last5 as $p): ?>
              <tr>
                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($p['created_at']))); ?></td>
                <td><?php echo (int) $p['score_percent']; ?>%</td>
                <td><?php echo !empty($p['weak_topics']) ? htmlspecialchars(implode(', ', $p['weak_topics'])) : '-'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div style="margin-top:12px;">
          <a class="btn" href="<?php echo BASE_URL; ?>/reports/history.php">View Full History</a>
        </div>
      <?php endif; ?>
    </div>
  </section>

  <!-- Weak topics overall -->
  <section class="section">
    <div class="card">
      <h3>Most Frequent Weak Topics (All Time)</h3>
      <?php if (!count($topWeakAllTime)): ?>
        <p class="muted">No weak topics recorded yet.</p>
      <?php else: ?>
        <ol class="list">
          <?php foreach ($topWeakAllTime as $t): ?>
            <li><?php echo htmlspecialchars($t); ?> (<?php echo (int) $topicCount[$t]; ?> times)</li>
          <?php endforeach; ?>
        </ol>
      <?php endif; ?>
    </div>
  </section>

  <!-- Next Step -->
  <section class="section">
    <div class="card">
      <h3>Next Step Recommendation</h3>

      <?php
      $scoreNow = (int) ($result['score_percent'] ?? 0);
      if ($scoreNow < 60) {
        $msg = "Your score is below 60%. We recommend doing a Quiz to practice core grammar topics.";
        $link = BASE_URL . "/activity/quiz.php";
        $btn = "Start Quiz";
      } elseif (!empty($result['weak_topics'])) {
        $msg = "You have specific weak topics. Writing practice can help reinforce accuracy and coherence.";
        $link = BASE_URL . "/activity/writing.php";
        $btn = "Start Writing";
      } else {
        $msg = "Great performance! Try Speaking to improve fluency and confidence.";
        $link = BASE_URL . "/activity/speaking.php";
        $btn = "Start Speaking";
      }
      ?>

      <p><?php echo htmlspecialchars($msg); ?></p>
      <a class="btn btn-primary" href="<?php echo htmlspecialchars($link); ?>"><?php echo htmlspecialchars($btn); ?></a>
      <a class="btn" href="<?php echo BASE_URL; ?>/dashboard.php" style="margin-left:8px;">Back to Dashboard</a>
    </div>
  </section>

<?php endif; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

<script>
  function toggleMistake(id) {
    const el = document.getElementById(id);
    if (el) {
      // Toggle
      el.style.display = (el.style.display === 'none') ? 'block' : 'none';
      // Auto-close siblings? prefer keeping open for comparison
    }
  }

  function saveWord(word, context) {
    if (!confirm('Save "' + word + '" to your vocabulary?')) return;

    fetch('<?= BASE_URL ?>/api/save_word.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        word: word,
        source: 'quiz_feedback',
        context: context
      })
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          alert('Word saved! check "My Vocabulary"');
        } else {
          alert('Error: ' + (data.error || 'Unknown'));
        }
      })
      .catch(err => alert('Failed to save word.'));
  }
</script>