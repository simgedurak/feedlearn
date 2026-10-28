<?php
require_once __DIR__ . '/../auth/_guard.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_login();

// NOTE: Logic is handled by assets/js/resume.js on the client side
// to allow resuming unfinished (unsaved) work.

require_once __DIR__ . '/../templates/header.php';
?>

<section class="section">
  <div class="card" style="text-align:center; padding: 40px;">
    <h2>Checking Progress...</h2>
    <div style="font-size: 3rem; margin: 20px 0;">⏳</div>
    <p class="muted">Looking for unfinished activities on this device.</p>
    <!-- JS will replace this content or redirect -->
  </div>
</section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
