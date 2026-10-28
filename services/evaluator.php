<?php
// services/evaluator.php

require_once __DIR__ . '/gemini_client.php';

function evaluate_writing(string $userText): array
{
    $client = new GeminiClient();

    // Prompt: Yapay zekayı çok sıkı uyarıyoruz
    $prompt = "
    Sen bir İngilizce öğretmenisin. Öğrenci yazısı:
    \"$userText\"

    Görevin:
    1. Puan ver (0-100)
    2. Seviye (A1-C2)
    3. Eksik 2 konu
    4. Türkçe geri bildirim

    KURALLAR:
    - SADECE SAF JSON FORMATI VER.
    - Markdown (```json) kullanma.
    - JSON standartlarına uy: Anahtarlar ve stringler için MUTLAKA çift tırnak (\") kullan.
    - Kesinlikle tek tırnak (') ile JSON oluşturma.
    
    İSTENEN FORMAT:
    {
        \"score_percent\": 80,
        \"cefr\": \"B1\",
        \"weak_topics\": [\"Grammar\", \"Vocabulary\"],
        \"feedback\": \"Guzel bir yazi.\"
    }
    ";

    try {
        $rawResponse = $client->generateResponse($prompt, true);
        
        // --- 1. Temizlik ---
        $clean = preg_replace('/```json|```/', '', $rawResponse);
        
        // --- 2. Cımbızlama: İlk { ile son } arasını al ---
        if (preg_match('/\{[\s\S]*\}/', $clean, $matches)) {
            $clean = $matches[0];
        }

        // --- 3. Görünmez Karakter Temizliği (BOM, Null vb.) ---
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean);

        // --- 2. JSON Çözümleme ---
        $result = json_decode($clean, true);

        // --- 3. Hata Yönetimi ---
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Son çare: Yaygın hataları düzeltmeyi dene
            // Tek tırnakları çift tırnağa çevir (Bunu sadece basit yapı bozuksa yap)
            $fixed = str_replace("'", '"', $clean);
            $result = json_decode($fixed, true); // Tekrar dene
            
            if (!$result) {
               error_log("Writing JSON Parse Error. Raw: " . $rawResponse);
               // Fallback manuel parse (Regex ile basit veri çekme)
               preg_match('/"score_percent":\s*(\d+)/', $clean, $sM);
               preg_match('/"cefr":\s*"([^"]+)"/', $clean, $cM);
               
               if (!empty($sM[1])) {
                   $result = [
                       'score_percent' => (int)$sM[1],
                       'cefr' => $cM[1] ?? 'B1',
                       'weak_topics' => ['Genel'],
                       'feedback' => strip_tags($clean) // Düz metin olarak kurtar
                   ];
               }
            }
        }

        if (!$result) {
             throw new Exception("JSON formatı algılanamadı.");
        }

        return [
            'score_percent' => $result['score_percent'] ?? 0,
            'cefr' => $result['cefr'] ?? 'A1',
            'weak_topics' => $result['weak_topics'] ?? [],
            'feedback' => $result['feedback'] ?? 'Geri bildirim oluşturuldu.'
        ];

    } catch (Exception $e) {
        return [
            'score_percent' => 0,
            'cefr' => 'N/A',
            'weak_topics' => ['Sistem'],
            'feedback' => 'Hata: ' . $e->getMessage()
        ];
    }
}

function evaluate_speaking(string $filePath): array
{
    $client = new GeminiClient();

    if (!file_exists($filePath)) {
         return [
            'score_percent' => 0,
            'cefr' => 'N/A',
            'weak_topics' => ['Dosya Hatası'],
            'feedback' => 'Ses dosyası bulunamadı.'
        ];
    }

    // Dosyayı oku ve base64 yap
    $audioData = file_get_contents($filePath);
    $base64Data = base64_encode($audioData);
    $mimeType = mime_content_type($filePath);

    // Prompt
    $prompt = "
    Sen bir İngilizce konuşma (speaking) öğretmenisin. Bu ses kaydını dinle ve değerlendir.
    
    Görevin:
    1. Puan ver (0-100) - Telaffuz, Akıcılık, Kelime Bilgisi ve Dilbilgisine göre.
    2. Seviye (A1-C2)
    3. Zayıf yönler (En az 1, en çok 3 konu: Örn: Pronunciation, Fluency, Grammar)
    4. Türkçe geri bildirim (Kısa ve yapıcı, telaffuz ve akıcılık odaklı)

    KURALLAR:
    - SADECE SAF JSON FORMATI VER.
    - Markdown (```json) KULLANMA.
    - Sessizlik veya gürültü varsa puanı düşük tut ve geri bildirimde belirt.
    
    İSTENEN FORMAT:
    {
        \"score_percent\": 75,
        \"cefr\": \"B1\",
        \"weak_topics\": [\"Pronunciation\", \"Fluency\"],
        \"feedback\": \"Genel olarak anlaşılır konuşuyorsunuz ancak 'th' seslerinde zorlanıyorsunuz.\"
    }
    ";

    try {
        // Multimodal istek gönder
        $rawResponse = $client->generateResponse($prompt, true, [
            'mimeType' => $mimeType,
            'data' => $base64Data
        ]);
        
        // --- Temizlik ve JSON Parse (Writing ile aynı mantık) ---
        $clean = preg_replace('/```json|```/', '', $rawResponse);
        if (preg_match('/\{[\s\S]*\}/', $clean, $matches)) {
            $clean = $matches[0];
        }
        $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $clean); // Görünmez karakter temizliği

        $result = json_decode($clean, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
             $clean = str_replace(["\r", "\n"], " ", $clean); 
             $result = json_decode($clean, true);
        }

        if (!$result) {
            error_log("Speaking JSON Parse Hatası. Gelen: " . $rawResponse);
            return [
                'score_percent' => 50,
                'cefr' => 'A2',
                'weak_topics' => ['Analiz Hatası'],
                'feedback' => 'Yapay zeka yanıtı işlenemedi, ancak kaydınız alındı.'
            ];
        }

        return [
            'score_percent' => $result['score_percent'] ?? 0,
            'cefr' => $result['cefr'] ?? 'A1',
            'weak_topics' => $result['weak_topics'] ?? [],
            'feedback' => $result['feedback'] ?? 'Geri bildirim oluşturuldu.'
        ];

    } catch (Exception $e) {
        $msg = $e->getMessage();
        $friendlyError = 'System is temporarily busy. Please try again.';
        
        if (strpos($msg, '429') !== false || strpos($msg, 'Quota') !== false) {
             $friendlyError = 'Daily usage limit reached. Please try again in a few minutes.';
        } elseif (strpos($msg, '404') !== false) {
             $friendlyError = 'AI Model temporarily unavailable.';
        } elseif (strpos($msg, '500') !== false || strpos($msg, '503') !== false) {
             $friendlyError = 'AI Service connection error.';
        }

        return [
            'score_percent' => 0,
            'cefr' => 'N/A',
            'weak_topics' => ['System Error'],
            'feedback' => $friendlyError . " (Debug: " . substr($msg, 0, 20) . "...)"
        ];
    }
}