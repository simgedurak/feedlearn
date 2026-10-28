<?php
require_once __DIR__ . '/auth/_guard.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_login();

$activities = db_get_activities();
$user = current_user();

// FEATURE 1: Onboarding Hook
$pdo = db_connect();
// Check if onboarding is skipped or done in session to avoid DB hit every time? 
// For now, DB hit is safer and fast enough.
$stmtOnb = $pdo->prepare("SELECT id FROM onboarding_attempts WHERE user_id = :uid LIMIT 1");
$stmtOnb->execute(['uid' => $user['id']]);
if (!$stmtOnb->fetch()) {
  redirect('/onboarding_test.php');
}

// Defensive coding for stats
$stats = db_get_report_stats($user['id']) ?? [];
$streak = $stats['study_streak'] ?? 0;

// Safe weak topic extraction
$weakTopic = 'None yet';
if (!empty($stats['weak_topics']) && is_array($stats['weak_topics'])) {
  $weakTopic = array_key_first($stats['weak_topics']);
}
// Ensure string for display usage
$weakTopic = (string) $weakTopic;

require_once __DIR__ . '/templates/header.php';
?>

<!-- Onboarding / Welcome Toast -->
<?php if (isset($_GET['onboarding_complete'])): ?>
  <div id="welcome-toast" class="theme-toast visible" style="background: var(--primary); color: white;">
    🎉 <strong>Welcome aboard!</strong> Your starting level is
    <strong><?= htmlspecialchars($_GET['level'] ?? 'A1') ?></strong>.
  </div>
  <script>
    setTimeout(() => {
      const t = document.getElementById('welcome-toast');
      if (t) {
        t.style.opacity = '0';
        setTimeout(() => t.remove(), 500);
      }
    }, 5000);
  </script>
<?php endif; ?>

<!-- HERO SECTION -->
<div class="section">
  <div style="margin-bottom: 24px;">
    <?php
    $quotes = [
      "Small steps every day lead to big results 🚀",
      "You’re only one quiz away from your next badge 💎",
      "Consistency beats intensity. Keep going 💪",
      "Learning is a marathon, not a sprint 🏃‍♂️",
      "Mistakes are proof that you are trying 🧠"
    ];
    $quote = $quotes[array_rand($quotes)];
    ?>
    <h2>Running Dashboard 🏃‍♂️</h2>
    <p class="muted">Welcome back, <?= htmlspecialchars($user['name']) ?>. <?= $quote ?></p>
  </div>

  <div class="grid">
    <!-- Streak Card -->
    <div class="stat-card">
      <div class="stat-icon">🔥</div>
      <div class="stat-info">
        <div class="label">Day Streak</div>
        <div class="value"><?= $streak ?></div>
      </div>
    </div>

    <!-- Today's Goal -->
    <?php
    require_once __DIR__ . '/services/gamification.php';
    $goalProgress = db_get_todays_goal_progress($user['id']);
    $gDone = $goalProgress['done'];
    $gTotal = $goalProgress['total'];
    $gPercent = ($gTotal > 0) ? ($gDone / $gTotal) * 100 : 0;
    $gRemaining = $gTotal - $gDone;
    ?>
    <div class="card">
      <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
        <span class="muted" style="font-size:0.85rem; text-transform:uppercase; font-weight:700;">Today's Goal</span>
        <span style="font-weight:700; color:var(--secondary-dark);"><?= $gDone ?> / <?= $gTotal ?> Tasks</span>
      </div>
      <div class="progress-track" style="margin-left: 0; background: var(--bg-body);">
        <div class="progress-fill" style="width:<?= $gPercent ?>%; background:var(--secondary);"></div>
      </div>
      <p style="font-size:0.85rem; margin-top:12px; margin-bottom:0;" class="muted">
        <?php if ($gRemaining > 0): ?>
          Complete <?= $gRemaining ?> more activit<?= $gRemaining !== 1 ? 'ies' : 'y' ?> to reach your daily goal.
        <?php else: ?>
          🎉 All goals completed for today! Great job.
        <?php endif; ?>
      </p>
    </div>

    <!-- Weakest Topic & Resume -->
    <div class="card" style="display:flex; flex-direction:column; gap:20px; padding:20px;">

      <!-- 1. Resume Panel (Top) -->
      <div class="card-resume-gradient"
        style="padding:24px; border-radius:12px; display:flex; flex-direction:column; justify-content:center; align-items:start; width:100%; background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3); color: white;">
        <div style="font-weight:700; font-size:1.1rem; margin-bottom:4px; color:#fff;">Ready to learn?</div>
        <div style="opacity:0.9; font-size:0.9rem; margin-bottom:16px; color:#fff;">Continue where you left off.</div>
        <a href="<?= BASE_URL ?>/activity/quiz.php" class="btn resume-quiz-btn">
          Resume Quiz <i>→</i>
        </a>
      </div>

      <!-- 2. Focus Area (Bottom) -->
      <div style="display:flex; align-items:center; gap:20px;">
        <div class="stat-icon" style="background:var(--secondary-soft); color:var(--secondary);">🎯</div>
        <div class="stat-info">
          <div class="label">Focus Area</div>
          <div class="value" style="font-size:1.2rem; text-transform:capitalize;">
            <?= htmlspecialchars(str_replace('_', ' ', (string) $weakTopic)) ?>
          </div>
        </div>
      </div>

    </div>
  </div>

  <section class="section">
    <h3>Activity Selection</h3>

    <?php if (empty($activities)): ?>
      <div class="card">
        <p class="muted">No activities available yet.</p>
      </div>
    <?php else: ?>
      <div class="grid">
        <?php foreach ($activities as $act): ?>
          <?php
          $actId = (int) ($act['id'] ?? 0);
          $actType = $act['activity_type'] ?? 'unknown';
          $actTitle = $act['title'] ?? 'Activity';
          $actDesc = $act['description'] ?? '';
          $actEnabled = (bool) ($act['is_enabled'] ?? false);
          $actLink = BASE_URL . '/activity/' . $actType . '.php?activity_id=' . $actId;
          ?>
          <div class="card">
            <div class="card-title"><?php echo htmlspecialchars($actTitle); ?></div>
            <div class="card-desc"><?php echo htmlspecialchars($actDesc); ?></div>
            <?php if ($actEnabled): ?>
              <a class="btn btn-primary" href="<?php echo htmlspecialchars($actLink); ?>">
                Start <?php echo htmlspecialchars(ucfirst($actType)); ?>
              </a>
            <?php else: ?>
              <span class="btn" aria-disabled="true" style="opacity:0.6; cursor:not-allowed;">Yakında</span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="section">
    <h3>Reports</h3>
    <div class="card">
      <ul class="dashboard-action-list">
        <li><a href="<?php echo BASE_URL; ?>/reports/weekly.php">Weekly Report</a></li>
        <li><a href="<?php echo BASE_URL; ?>/reports/history.php">History</a></li>
        <li><a href="<?php echo BASE_URL; ?>/results/report.php">Detailed Report 📊</a></li>
      </ul>
    </div>
  </section>
</div>

<section class="section">
  <h3>Tools</h3>
  <div class="card">
    <ul class="dashboard-action-list">
      <li><a href="<?php echo BASE_URL; ?>/recommendations.php">Recommended Next Activity</a></li>
      <li><a href="<?php echo BASE_URL; ?>/help/chatbot.php">Help Chatbot</a></li>
      <li><a href="<?php echo BASE_URL; ?>/activity/resume.php">Resume Unfinished Activity</a></li>
      <li><a href="<?php echo BASE_URL; ?>/reports/preferences.php">Notification Preferences</a></li>
    </ul>
  </div>
</section>

<?php if ((current_user()['role'] ?? '') === ROLE_ADMIN): ?>
  <section class="section">
    <h3>Admin Panel</h3>
    <div class="card">
      <ul class="dashboard-action-list">
        <li><a href="<?php echo BASE_URL; ?>/admin/users.php">Manage Users</a></li>
        <li><a href="<?php echo BASE_URL; ?>/admin/ai_logs.php">AI Feedback Logs</a></li>
        <li><a href="<?php echo BASE_URL; ?>/admin/notifications.php">Notification Defaults</a></li>
      </ul>
    </div>
  </section>
<?php endif; ?>

<?php require_once __DIR__ . '/templates/footer.php'; ?>