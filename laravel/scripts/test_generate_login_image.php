<?php
/**
 * Script de test — génère une image de fond pour la page de login.
 * Utilise gemini-2.0-flash-preview-image-generation via generateContent.
 *
 * Usage :
 *   php scripts/test_generate_login_image.php
 *
 * Résultat : storage/app/public/ui/login_bg.png
 * URL publique : /storage/ui/login_bg.png
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// ─── Config ───────────────────────────────────────────────────────────────────

$apiKey     = $_ENV['GEMINI_API_KEY'] ?? '';
$model      = 'gemini-2.5-flash-image';
$apiUrl     = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
$outputDir  = __DIR__ . '/../storage/app/public/ui';
$outputFile = $outputDir . '/login_bg.png';
$publicPath = 'storage/ui/login_bg.png';

// ─── Prompt ───────────────────────────────────────────────────────────────────

$prompt = 'Dark fantasy dungeon entrance at night, torchlight flickering on stone walls, '
    . 'mysterious fog at ground level, ancient runes glowing purple, '
    . 'cinematic wide shot, dramatic lighting, painterly illustration style, '
    . 'no text, no characters, atmospheric, medieval fantasy RPG game background';

// ─── Vérifications ────────────────────────────────────────────────────────────

if (empty($apiKey)) {
    echo "[ERREUR] GEMINI_API_KEY manquant dans .env\n";
    exit(1);
}

echo "[DEBUG] Modèle   : {$model}\n";
echo "[DEBUG] API Key  : " . substr($apiKey, 0, 8) . "...\n";
echo "[DEBUG] Output   : {$outputFile}\n";
echo "[INFO]  Prompt   : {$prompt}\n";
echo "[INFO]  Appel API...\n";

// ─── Appel API ────────────────────────────────────────────────────────────────

$payload = json_encode([
    'contents' => [
        ['parts' => [['text' => $prompt]]],
    ],
    'generationConfig' => [
        'responseModalities' => ['IMAGE', 'TEXT'],
    ],
]);

$ch = curl_init("{$apiUrl}?key={$apiKey}");
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
]);

$raw      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo "[ERREUR] cURL : {$curlErr}\n";
    exit(1);
}

echo "[DEBUG] HTTP {$httpCode}\n";
echo "[DEBUG] Taille réponse : " . strlen($raw) . " octets\n";

if ($httpCode !== 200) {
    echo "[ERREUR] Réponse API :\n{$raw}\n";
    exit(1);
}

// ─── Traitement de la réponse ─────────────────────────────────────────────────

$data  = json_decode($raw, true);
$parts = $data['candidates'][0]['content']['parts'] ?? [];
$b64   = null;
$mime  = 'image/png';

foreach ($parts as $part) {
    if (isset($part['inlineData']['data'])) {
        $b64  = $part['inlineData']['data'];
        $mime = $part['inlineData']['mimeType'] ?? 'image/png';
        break;
    }
}

if (empty($b64)) {
    echo "[ERREUR] Aucune image dans la réponse. Contenu :\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

echo "[DEBUG] MIME     : {$mime}\n";
echo "[DEBUG] Base64   : " . strlen($b64) . " chars\n";

// ─── Sauvegarde + compression JPEG ───────────────────────────────────────────

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$rawBytes = base64_decode($b64);

// Convertir en JPEG 85% pour réduire le poids (fond de page)
if (function_exists('imagecreatefromstring')) {
    $img = imagecreatefromstring($rawBytes);
    if ($img !== false) {
        $outputFile = $outputDir . '/login_bg.jpg';
        imagejpeg($img, $outputFile, 85);
        imagedestroy($img);
        echo "[OK] Image sauvegardée (JPEG 85%) : {$outputFile}\n";
        echo "[OK] Chemin public : storage/ui/login_bg.jpg\n";
    } else {
        $outputFile = $outputDir . '/login_bg.png';
        file_put_contents($outputFile, $rawBytes);
        echo "[WARN] Conversion JPEG échouée, sauvegardé en PNG\n";
        echo "[OK] Chemin public : storage/ui/login_bg.png\n";
    }
} else {
    $outputFile = $outputDir . '/login_bg.png';
    file_put_contents($outputFile, $rawBytes);
    echo "[WARN] Extension GD non disponible, sauvegardé en PNG\n";
    echo "[OK] Chemin public : storage/ui/login_bg.png\n";
}

echo "[OK] Taille : " . number_format(filesize($outputFile) / 1024, 1) . " Ko\n";
