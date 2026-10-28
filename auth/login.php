<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $csrf = $_POST['csrf_token'] ?? '';
  $email = trim($_POST['email'] ?? '');
  $pass = $_POST['password'] ?? '';

  if (!csrf_validate($csrf)) {
    $errors[] = 'Oturum doğrulama hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
  }

  if ($email === '' || $pass === '') {
    $errors[] = 'E-posta ve şifre zorunludur.';
  } elseif (!$errors) {
    $user = db_find_user_by_email($email);

    if (!$user || !password_verify($pass, $user['password_hash'])) {
      $errors[] = 'E-posta veya şifre hatalı.';
    } else {
      login_user($user);
      redirect('/dashboard.php');
    }
  }
}

require_once __DIR__ . '/../templates/header.php';
?>

<section class="section" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
  <div class="card" style="width: 100%; max-width: 440px; padding: 40px; border: 1px solid var(--border-light); box-shadow: var(--shadow-float);">
    <div style="text-align: center; margin-bottom: 32px;">
      <h2 style="font-size: 2rem; margin-bottom: 8px; color: var(--primary);">Giriş Yap</h2>
      <p class="muted">Macerana kaldığın yerden devam et.</p>
    </div>

    <?php if ($errors): ?>
      <div class="error" role="alert" aria-live="polite" style="margin-bottom: 24px; padding: 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-sm); color: #b91c1c;">
        <ul class="list" style="margin: 0; padding-left: 16px; list-style-type: disc;">
          <?php foreach ($errors as $e): ?>
            <li><?php echo htmlspecialchars($e); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" aria-label="Giriş formu">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      
      <div style="margin-bottom: 20px;">
        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">E-posta</label>
        <input id="email" name="email" type="email" autocomplete="email" required
          value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="ornek@email.com" />
      </div>

      <div style="margin-bottom: 24px;">
        <label for="password" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">Şifre</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="••••••••" />
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 16px; height: 48px; font-size: 1.1rem;">Giriş Yap</button>
      
      <div style="text-align: center;">
        <span class="muted" style="font-size: 0.9rem;">Hesabın yok mu?</span>
        <a href="<?php echo BASE_URL; ?>/auth/register.php" style="font-weight: 600; margin-left: 4px;">Hemen Kayıt Ol</a>
      </div>
    </form>

    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--border-light); text-align: center; font-size: 0.85rem; color: var(--text-muted);">
      <p style="margin-bottom: 4px;">Geliştirici Girişi:</p>
      <code style="background:var(--bg-body); padding: 4px 8px; border-radius: 4px; color: var(--primary-dark);">admin@feedlearn.local</code>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>