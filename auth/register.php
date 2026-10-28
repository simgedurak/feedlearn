<?php
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf  = $_POST['csrf_token'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!csrf_validate($csrf)) $errors[] = 'Oturum doğrulama hatası. Lütfen sayfayı yenileyip tekrar deneyin.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Geçerli e-posta giriniz.';
    if (strlen($pass) < 6) $errors[] = 'Şifre en az 6 karakter olmalıdır.';

    if (!$errors) {
        if (db_find_user_by_email($email)) {
            $errors[] = 'Bu e-posta zaten kayıtlı.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            if (!db_create_user($email, $hash, ROLE_STUDENT)) {
                $errors[] = 'Kayıt oluşturulamadı. Lütfen tekrar deneyin.';
            } else {
                $success = "Kayıt başarılı. Şimdi giriş yapabilirsin.";
            }
        }
    }
}

require_once __DIR__ . '/../templates/header.php';
?>

<section class="section" style="min-height: 80vh; display: flex; align-items: center; justify-content: center;">
  <div class="card" style="width: 100%; max-width: 440px; padding: 40px; border: 1px solid var(--border-light); box-shadow: var(--shadow-float);">
    <div style="text-align: center; margin-bottom: 32px;">
      <h2 style="font-size: 2rem; margin-bottom: 8px; color: var(--primary);">Kayıt Ol</h2>
      <p class="muted">Hemen başla, İngilizi geliştir.</p>
    </div>

    <?php if ($success): ?>
      <div style="background: #ecfdf5; border: 1px solid #d1fae5; color: #047857; padding: 16px; border-radius: var(--radius-sm); margin-bottom: 24px; text-align: center;">
        <p style="margin-bottom: 12px; font-weight: 600;"><?php echo htmlspecialchars($success); ?></p>
        <a class="btn btn-primary" href="<?php echo BASE_URL; ?>/auth/login.php" style="width: 100%;">Giriş Yap</a>
      </div>
    <?php else: ?>

      <?php if ($errors): ?>
        <div class="error" role="alert" aria-live="polite" style="margin-bottom: 24px; padding: 16px; background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius-sm); color: #b91c1c;">
          <ul class="list" style="margin: 0; padding-left: 16px; list-style-type: disc;">
            <?php foreach ($errors as $e): ?>
              <li><?php echo htmlspecialchars($e); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="POST" aria-label="Kayıt formu">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">

        <div style="margin-bottom: 20px;">
          <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">E-posta</label>
          <input id="email" name="email" type="email" autocomplete="email" required
            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="ornek@email.com" />
        </div>

        <div style="margin-bottom: 24px;">
          <label for="password" style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--text-main);">Şifre</label>
          <input id="password" name="password" type="password" autocomplete="new-password" required placeholder="En az 6 karakter" />
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 16px; height: 48px; font-size: 1.1rem;">Kaydı Tamamla</button>
        
        <div style="text-align: center;">
          <span class="muted" style="font-size: 0.9rem;">Zaten hesabın var mı?</span>
          <a href="<?php echo BASE_URL; ?>/auth/login.php" style="font-weight: 600; margin-left: 4px;">Giriş Yap</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../templates/footer.php'; ?>
