<?php
// templates/header.php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/auth.php';

$user = current_user();
?>
<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?php echo APP_NAME; ?></title>

  <!-- New Design Assets -->
  <!-- Theme: FeedLearn Premium (Indigo/Pink) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css?v=<?= time() ?>">
  <script src="<?= BASE_URL ?>/assets/js/app.js?v=20260123_fixed" defer></script>
  <script src="<?= BASE_URL ?>/assets/js/resume.js?v=<?= time() ?>" defer></script>
</head>

<body>



  <header class="site-header">
    <div class="header-inner">

      <!-- Mobile Menu Toggle -->
      <button class="mobile-toggle" aria-label="Menu">
        <div class="burger-icon">
          <span></span>
          <span></span>
          <span></span>
        </div>
      </button>

      <!-- LOGO -->
      <div class="brand">
        <a href="<?= BASE_URL ?>/dashboard.php" class="brand-link">
          <?= APP_NAME ?>
        </a>
      </div>

      <!-- NAV -->
      <nav class="site-nav">
        <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
        <a href="<?= BASE_URL ?>/reports/weekly.php">Weekly</a>
        <a href="<?= BASE_URL ?>/reports/history.php">History</a>
        <a href="<?= BASE_URL ?>/recommendations">Recommendations</a>

        <!-- Mobile only user links could go here if needed, but handled globally via CSS for now -->
      </nav>

      <!-- LAMP TOGGLE -->
      <button class="lamp-toggle" id="lampToggle" aria-label="Toggle Dark Mode" title="Switch Theme">
        <div class="lamp">
          <div class="lamp-shade"></div>
          <div class="lamp-base"></div>
          <div class="lamp-stand"></div>
        </div>
      </button>

      <!-- USER -->
      <?php if ($user): ?>
        <div class="user-box">
          <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
          <a href="<?= BASE_URL ?>/auth/logout.php" class="logout-link">
            Çıkış
          </a>
        </div>
      <?php endif; ?>

    </div>
  </header>

  <!-- DRAWER OVERLAY -->
  <div class="drawer-overlay"></div>

  <!-- SETTINGS DRAWER -->
  <aside class="settings-drawer">
    <div class="drawer-header">
      <h2>Settings</h2>
      <button class="drawer-close">&times;</button>
    </div>

    <div class="drawer-content">
      <!-- Appearance Section -->
      <div class="drawer-section">
        <h3>Appearance</h3>
        <div class="drawer-row">
          <span>Dark Mode</span>
          <label class="toggle-switch">
            <input type="checkbox" id="darkModeToggle">
            <span class="slider"></span>
          </label>
        </div>
      </div>

      <!-- Goals Section (Replaced with Dynamic Gamification) -->
      <?php
      require_once __DIR__ . '/../services/gamification.php';
      $gameStats = get_user_gamification($user['id']);
      $gameGoals = db_get_todays_goals($user['id']);
      ?>

      <!-- 1. BADGE PROGRESS CARD -->
      <div class="drawer-section">
        <h3>Level Progress</h3>
        <div class="drawer-goal-card badge-card">
          <div class="goal-header">
            <span class="goal-title"
              style="color:var(--secondary);"><?= htmlspecialchars($gameStats['badge_name'] ?? 'Learner') ?></span>
            <span class="goal-count">Lvl <?= $gameStats['level'] ?? 1 ?></span>
          </div>
          <div class="goal-bar-bg">
            <div class="goal-bar-fill" style="width: <?= $gameStats['progress_percent'] ?? 0 ?>%;"></div>
          </div>
          <p class="goal-text">
            <?= $gameStats['progress_xp'] ?? 0 ?> / <?= $gameStats['next_badge_xp'] ?? 100 ?> XP to
            <strong><?= htmlspecialchars($gameStats['next_badge_name'] ?? 'Next Level') ?></strong>
          </p>
        </div>
      </div>

      <!-- 2. MY LEARNING LINKS -->
      <div class="drawer-section">
        <h3>My Learning</h3>
        <ul class="drawer-goals-list"> <!-- Reusing existing list style -->
          <li>
            <a href="<?= BASE_URL ?>/achievements.php"
              style="display:flex; align-items:center; gap:12px; color:var(--text-main); width:100%;">
              <span style="font-size:1.2rem;">🏆</span>
              <span>Achievements</span>
            </a>
          </li>
          <li>
            <a href="<?= BASE_URL ?>/vocabulary.php"
              style="display:flex; align-items:center; gap:12px; color:var(--text-main); width:100%;">
              <span style="font-size:1.2rem;">📚</span>
              <span>My Vocabulary</span>
            </a>
          </li>
        </ul>
      </div>

      <!-- 3. TODAY'S GOALS -->
      <div class="drawer-section">
        <h3>Today's Goals</h3>
        <ul class="drawer-goals-list">
          <?php foreach ($gameGoals as $goal): ?>
            <li class="<?= $goal['is_done'] ? 'done' : '' ?>" data-goal-key="<?= $goal['key'] ?>">
              <span class="goal-icon"><?= $goal['icon'] ?></span>
              <span class="goal-label"><?= htmlspecialchars($goal['title']) ?></span>
              <span class="goal-check"><?= $goal['is_done'] ? '✔' : '' ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <!-- 3. CURRENT LEVEL PILL -->
      <div class="drawer-section"
        style="margin-top:auto; padding-top:20px; border-top:1px solid var(--border-light);">
        <div class="drawer-row">
          <span style="font-size:0.9rem; color:var(--text-muted);">Current Level</span>
          <span class="level-pill"><?= htmlspecialchars($gameStats['current_level']) ?></span>
        </div>
      </div>
    </div>
  </aside>
  <main id="content" class="site-main">