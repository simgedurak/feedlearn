<?php
// api/save_word.php
require_once __DIR__ . '/../auth/_guard.php';
require_once __DIR__ . '/../config/db.php';
require_login(); // content-type json automatically? wrapper might output html if error but require_login handles redirect.

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$word = trim($input['word'] ?? '');
$meaning = trim($input['meaning'] ?? ''); // Optional
$context = trim($input['context'] ?? ''); // Optional
$source = trim($input['source'] ?? 'manual');

if ($word === '') {
    echo json_encode(['success' => false, 'error' => 'Word is required']);
    exit;
}

$user = current_user();
$pdo = db_connect();

try {
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO user_vocabulary (user_id, word, meaning, example_sentence, source) VALUES (:uid, :word, :mean, :ex, :src)");
    $stmt->execute([
        'uid' => $user['id'],
        'word' => $word,
        'mean' => $meaning,
        'ex' => $context,
        'src' => $source
    ]);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
