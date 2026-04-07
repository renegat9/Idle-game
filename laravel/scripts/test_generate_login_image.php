<?php
/**
 * Script de test — génère une image de fond pour la page de login via Imagen.
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

// ─── Config ──────────────────────────────────────────────────────────────────

$apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/imagen-3.0-generate-002:predict';
$outputDir  = __DIR__ . '/../storage/app/public/ui';
$outputFile = $outputDir . '/login_bg.png';
$publicPath = 'storage/ui/login_bg.png';

// ─── Prompt ───────────────────────────────────────────────────────────────────

$prompt = 'Dark fantasy dungeon entrance at night, torchlight flickering on stone walls, '
    . 'mysterious fog at ground level, ancient runes glowing purple, '
    . 'cinematic wide shot, dramatic lighting, painterly illustration style, '
    . 'no text, no characters, atmospheric, medieval fantasy RPG game background';

// ─── Vérifications ───────────────────────────────────────────────────────────

if (empty($apiKey)) {
    echo "[ERREUR] GEMINI_API_KEY manquant dans .env\n";
    exit(1);
}

echo "[DEBUG] API URL  : {$apiUrl}\n";
echo "[DEBUG] API Key  : " . substr($apiKey, 0, 8) . "...\n";
echo "[DEBUG] Output   : {$outputFile}\n";
echo "[INFO]  Prompt   : {$prompt}\n";
echo "[INFO]  Appel Imagen API...\n";

// ─── Appel API ────────────────────────────────────────────────────────────────

$payload = json_encode([
    'instances'  => [['prompt' => $prompt]],
    'parameters' => ['sampleCount' => 1, 'aspectRatio' => '16:9'],
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

echo "[DEBUG] Réponse brute (500 premiers chars) : " . substr($raw, 0, 500) . "\n";

// ─── Traitement de la réponse ─────────────────────────────────────────────────

$data = json_decode($raw, true);
echo "[DEBUG] Clés réponse : " . implode(', ', array_keys($data ?? [])) . "\n";

$b64 = $data['predictions'][0]['bytesBase64Encoded'] ?? null;

if (empty($b64)) {
    echo "[ERREUR] bytesBase64Encoded absent. Réponse complète :\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    exit(1);
}

echo "[DEBUG] Taille base64 : " . strlen($b64) . " chars\n";

// ─── Sauvegarde ───────────────────────────────────────────────────────────────

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

file_put_contents($outputFile, base64_decode($b64));

echo "[OK] Image sauvegardée : {$outputFile}\n";
echo "[OK] Chemin public     : {$publicPath}\n";
echo "[OK] Taille            : " . number_format(filesize($outputFile) / 1024, 1) . " Ko\n";
