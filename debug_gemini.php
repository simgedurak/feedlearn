<?php
require_once __DIR__ . '/config/constants.php';

echo "Listing Models for API Key...\n";

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . GEMINI_API_KEY;

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
$data = json_decode($response, true);

if (isset($data['models'])) {
    foreach ($data['models'] as $m) {
        if (strpos($m['name'], 'gemini') !== false) {
            echo "- " . $m['name'] . "\n";
            echo "  Supported Methods: " . implode(', ', $m['supportedGenerationMethods']) . "\n";
        }
    }
} else {
    echo "Raw Response: $response\n";
}
?>
