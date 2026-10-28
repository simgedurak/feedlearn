<?php
// vocabulary.php
require_once __DIR__ . '/auth/_guard.php';
require_once __DIR__ . '/config/constants.php';
require_once __DIR__ . '/config/db.php';
require_login();

$user = current_user();
$pdo = db_connect();

// Handle Delete
if (isset($_POST['delete_id'])) {
    $delStmt = $pdo->prepare("DELETE FROM user_vocabulary WHERE id = :id AND user_id = :uid");
    $delStmt->execute(['id' => $_POST['delete_id'], 'uid' => $user['id']]);
    redirect('vocabulary.php?deleted=1');
}

// Fetch Items
$search = $_GET['q'] ?? '';
$sql = "SELECT * FROM user_vocabulary WHERE user_id = :uid";
$params = ['uid' => $user['id']];

if ($search) {
    $sql .= " AND word LIKE :q";
    $params['q'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vocab = $stmt->fetchAll();

require_once __DIR__ . '/templates/header.php';
?>

<div class="section">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
        <div>
            <h2>My Vocabulary 📚</h2>
            <p class="muted">Review and practice your saved words.</p>
        </div>
        <form method="GET" style="display: flex; gap: 8px;">
            <input type="text" name="q" placeholder="Search words..." value="<?= htmlspecialchars($search) ?>"
                style="width: 200px;">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</div>

<div class="section">
    <?php if (empty($vocab)): ?>
        <div class="card">
            <p class="muted">No words saved yet. Save words from your quiz results to see them here!</p>
            <a href="dashboard.php" class="btn">Go to Dashboard</a>
        </div>
    <?php else: ?>
        <div class="grid">
            <?php foreach ($vocab as $item): ?>
                <div class="card vocab-card" style="position: relative;">
                    <form method="POST" style="position: absolute; top: 16px; right: 16px;">
                        <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                        <button type="submit" class="btn"
                            style="padding: 4px 8px; font-size: 0.8rem; color: var(--danger); border: none;"
                            onclick="return confirm('Remove this word?');">
                            🗑️
                        </button>
                    </form>

                    <h3 style="color: var(--primary); margin-bottom: 8px; font-size: 1.5rem;">
                        <?= htmlspecialchars($item['word']) ?>
                    </h3>

                    <div style="margin-bottom: 12px;">
                        <span class="badge" style="background: var(--pill-bg); color: var(--pill-text);">
                            <?= htmlspecialchars($item['source'] ?? 'manual') ?>
                        </span>
                    </div>

                    <?php if ($item['meaning']): ?>
                        <p><strong>Meaning:</strong>
                            <?= htmlspecialchars($item['meaning']) ?>
                        </p>
                    <?php else: ?>
                        <p class="muted"><em>No definition available</em></p>
                    <?php endif; ?>

                    <?php if ($item['example_sentence']): ?>
                        <div
                            style="background: var(--bg-body); padding: 12px; border-radius: 8px; border: 1px solid var(--border-light); font-style: italic; font-size: 0.9rem;">
                            "
                            <?= htmlspecialchars($item['example_sentence']) ?>"
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/templates/footer.php'; ?>