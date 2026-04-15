<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Client Gemini API avec fallbacks statiques obligatoires.
 *
 * Règles :
 *  - Toujours retourner un résultat (jamais null / exception propagée)
 *  - Vérifier AI_ENABLED et AI_DAILY_BUDGET_LIMIT avant chaque appel
 *  - Logger chaque tentative dans ai_generation_log
 *  - Valider la structure des réponses avant utilisation
 */
class GeminiService
{
    private const TEXT_API_URL       = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private const IMAGE_API_URL      = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-image:generateContent';
    private const MUSIC_GEMINI_URL   = 'https://generativelanguage.googleapis.com/v1beta/models/lyria-3-clip-preview:generateContent';
    private const MUSIC_VERTEX_URL   = 'https://%s-aiplatform.googleapis.com/v1/projects/%s/locations/%s/publishers/google/models/lyria-002:predict';

    // Coût approximatif par appel (micro-centimes)
    private const COST_TEXT  = 10;
    private const COST_IMAGE = 50;
    private const COST_MUSIC = 30;

    public function __construct(private readonly SettingsService $settings) {}

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Generate a narration text for a game event.
     * Returns fallback text if AI is disabled or budget exceeded.
     */
    public function generateNarration(string $eventType, array $context = []): string
    {
        if (!$this->canCall('narration')) {
            return $this->fallbackNarration($eventType, $context);
        }

        $prompt = $this->buildNarrationPrompt($eventType, $context);

        try {
            $text = $this->callTextApi($prompt, 'narration', 200);
            return $text ?? $this->fallbackNarration($eventType, $context);
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateNarration failed', ['error' => $e->getMessage()]);
            return $this->fallbackNarration($eventType, $context);
        }
    }

    /**
     * Generate a humorous item name and description.
     * Returns fallback if AI is disabled or budget exceeded.
     * @return array{name: string, description: string}
     */
    public function generateLootText(string $itemType, string $rarity, int $level): array
    {
        if (!$this->canCall('loot_text')) {
            return $this->fallbackLootText($itemType, $rarity, $level);
        }

        $prompt = $this->buildLootTextPrompt($itemType, $rarity, $level);

        try {
            $raw = $this->callTextApi($prompt, 'loot_text', 150);
            if ($raw !== null) {
                $parsed = $this->parseLootTextResponse($raw);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateLootText failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackLootText($itemType, $rarity, $level);
    }

    /**
     * Generate a quest title and description.
     * Returns fallback if AI is disabled or budget exceeded.
     * @return array{title: string, description: string, flavor: string}
     */
    public function generateQuestText(string $zoneSlug, int $zoneLevel): array
    {
        if (!$this->canCall('quest')) {
            return $this->fallbackQuestText($zoneSlug, $zoneLevel);
        }

        $prompt = $this->buildQuestPrompt($zoneSlug, $zoneLevel);

        try {
            $raw = $this->callTextApi($prompt, 'quest', 200);
            if ($raw !== null) {
                $parsed = $this->parseQuestTextResponse($raw);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateQuestText failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackQuestText($zoneSlug, $zoneLevel);
    }

    /**
     * Generate a new zone definition (name, slug, description, element, monsters theme).
     * Used by ZoneGeneratorService for procedural zone creation (zone 9+).
     * @return array{name: string, slug: string, description: string, element: string, monster_theme: string, flavor: string}
     */
    public function generateZone(int $zoneIndex, int $levelMin, int $levelMax): array
    {
        if (!$this->canCall('zone')) {
            return $this->fallbackZone($zoneIndex, $levelMin, $levelMax);
        }

        $prompt = "Tu es le Narrateur sarcastique du Donjon des Incompétents. "
            . "Crée une nouvelle zone de donjon absurde et humoristique (zone n°{$zoneIndex}, niveaux {$levelMin}-{$levelMax}). "
            . "Nom court (max 50 chars), slug unique en snake_case, description drôle (max 200 chars), "
            . "élément dominant parmi: physique/feu/glace/foudre/poison/sacre/ombre, "
            . "thème de monstres (1 phrase), texte de saveur sarcastique (max 100 chars). "
            . "Format JSON strict : {\"name\": \"...\", \"slug\": \"...\", \"description\": \"...\", "
            . "\"element\": \"...\", \"monster_theme\": \"...\", \"flavor\": \"...\"}";

        try {
            $raw = $this->callTextApi($prompt, 'zone', 300);
            if ($raw !== null) {
                $parsed = $this->parseZoneResponse($raw);
                if ($parsed !== null) {
                    return $parsed;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateZone failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackZone($zoneIndex, $levelMin, $levelMax);
    }

    /**
     * Generate a zone background illustration.
     */
    public function generateZoneBackground(int $zoneId, string $zoneSlug, string $element, string $description): string
    {
        if (!$this->canCall('zone_bg')) {
            return $this->fallbackZoneBackground($element);
        }

        $prompt = $this->buildZoneBackgroundPrompt($zoneSlug, $element, $description);

        try {
            $filename = "zone_{$zoneSlug}_" . time() . '.jpg';
            $path = $this->callImageApi($prompt, 'zone_bg', $filename, false);
            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateZoneBackground failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackZoneBackground($element);
    }

    /**
     * Generate an elite version of a monster using its base image as visual reference.
     * If baseImagePath is null, generates from scratch with an elite prompt.
     */
    public function generateEliteMonsterImage(int $monsterId, string $monsterName, string $element, ?string $baseImagePath): string
    {
        if (!$this->canCall('elite_monster')) {
            return $this->fallbackMonsterImage($element);
        }

        try {
            $filename = "monster_{$monsterId}_elite_" . time() . '.png';

            if ($baseImagePath !== null && file_exists(storage_path('app/public/' . str_replace('storage/', '', $baseImagePath)))) {
                $path = $this->callImageApiWithReference(
                    $baseImagePath,
                    "Transform this monster into an elite version: more imposing, add golden or dark purple aura, "
                    . "glowing eyes, more detailed armor or scales, same creature type but visibly more powerful and menacing. "
                    . "Keep the same visual style. Solid #00FF00 bright green background, no shadows on background.",
                    'elite_monster',
                    $filename
                );
            } else {
                $prompt = "Fantasy RPG elite monster portrait, {$monsterName}, {$element} element type, "
                    . "imposing and menacing, golden crown or dark aura indicating elite status, glowing eyes, "
                    . "detailed fantasy art style, pixel art inspired, solid #00FF00 bright green background, no shadows on background.";
                $path = $this->callImageApi($prompt, 'elite_monster', $filename);
            }

            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateEliteMonsterImage failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackMonsterImage($element);
    }

    /**
     * Generate a base image for a monster.
     */
    public function generateMonsterImage(int $monsterId, string $monsterName, string $element): string
    {
        if (!$this->canCall('monster_image')) {
            return $this->fallbackMonsterImage($element);
        }

        $prompt = "Fantasy RPG monster portrait, {$monsterName}, {$element} element creature, "
            . "menacing expression, detailed fantasy art style, pixel art inspired, "
            . "solid #00FF00 bright green background, no shadows on background, centered, square format.";

        try {
            $filename = "monster_{$monsterId}_" . time() . '.png';
            $path = $this->callImageApi($prompt, 'monster_image', $filename);
            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateMonsterImage failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackMonsterImage($element);
    }

    /**
     * Generate an illustration for a loot item using Imagen.
     * Saves the image to storage and returns the public path.
     * Falls back to a static placeholder if AI is unavailable.
     */
    public function generateLootImage(int $itemId, string $slot, string $rarity): string
    {
        if (!$this->canCall('loot_image')) {
            return $this->fallbackLootImage($slot, $rarity);
        }

        $prompt = $this->buildLootImagePrompt($slot, $rarity);

        try {
            $filename = ($itemId > 0 ? "item_{$itemId}" : "{$slot}_{$rarity}") . '_' . time() . '.png';
            $path = $this->callImageApi($prompt, 'loot_image', $filename, false);
            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateLootImage failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackLootImage($slot, $rarity);
    }

    /**
     * Generate a full-page background illustration for a given UI page.
     * Landscape format, dark atmospheric fantasy art, no transparency needed.
     * Stored in storage/app/public/pages/{slug}.jpg
     */
    public function generatePageBackground(string $slug): string
    {
        if (!$this->canCall('page_bg')) {
            return $this->fallbackPageBackground($slug);
        }

        $prompt = $this->buildPageBackgroundPrompt($slug);

        try {
            $filename = "page_{$slug}_" . time() . '.jpg';
            $path = $this->callImageApi($prompt, 'page_bg', $filename, false);
            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generatePageBackground failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackPageBackground($slug);
    }

    /**
     * Generate a hero portrait via Imagen with chroma-key transparency.
     * Saves to storage/app/public/hero_images/ and returns the public path.
     */
    public function generateHeroImage(int $targetId, string $raceName, string $classSlug, ?string $traitSlug = null): string
    {
        if (!$this->canCall('hero_image')) {
            return $this->fallbackHeroImage($classSlug);
        }

        $prompt   = $this->buildHeroImagePrompt($raceName, $classSlug, $traitSlug);
        $filename = "hero_{$targetId}_" . time() . '.png';

        try {
            $path = $this->callImageApi($prompt, 'hero_image', $filename, true);
            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateHeroImage failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackHeroImage($classSlug);
    }

    /**
     * Generate a boss description and special mechanic text.
     * @return array{description: string, mechanic: string}
     */
    public function generateBossText(string $bossName): array
    {
        if (!$this->canCall('boss')) {
            return $this->fallbackBossText($bossName);
        }

        $prompt = "Tu es le Narrateur sarcastique du jeu Le Donjon des Incompétents. "
            . "Décris le boss mondial \"{$bossName}\" en 2 phrases humoristiques (en français). "
            . "Puis décris sa mécanique spéciale en 1 phrase. "
            . "Format JSON strict : {\"description\": \"...\", \"mechanic\": \"...\"}";

        try {
            $raw = $this->callTextApi($prompt, 'boss', 150);
            if ($raw !== null) {
                $data = json_decode($raw, true);
                if (isset($data['description'], $data['mechanic'])) {
                    return ['description' => $data['description'], 'mechanic' => $data['mechanic']];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateBossText failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackBossText($bossName);
    }

    /**
     * Return the music track for the given style.
     * Priority: tavern_music cache → Vertex AI Lyria → Gemini Lyria → static fallback.
     * Every resolved track is persisted to tavern_music for future cache hits.
     * @return array{style: string, prompt: string, file_path: string}
     */
    public function generateTavernMusic(string $style, bool $forceGemini = false): array
    {
        // 1. Return cached track if it exists
        $cached = DB::table('tavern_music')->where('style', $style)->latest('created_at')->first();
        if ($cached) {
            DB::table('tavern_music')->where('id', $cached->id)->increment('play_count');
            return [
                'style'     => $cached->style,
                'prompt'    => $cached->prompt_used,
                'file_path' => $cached->file_path,
            ];
        }

        // 2. Try AI generation if configured
        $filePath = null;
        $prompt   = '';
        if ($this->canCall('music')) {
            $prompt    = $this->buildMusicPrompt($style);
            $useVertex = !empty(config('services.vertex_ai.api_key')) && !$forceGemini;
            $filePath  = $useVertex
                ? $this->callMusicVertexAI($prompt, $style)
                : $this->callMusicGemini($prompt, $style);
        }

        // 3. Fall back to static placeholder
        if ($filePath === null) {
            $fallback = $this->fallbackTavernMusic($style);
            $filePath = $fallback['file_path'];
            $prompt   = $fallback['prompt'];
            $this->logGeneration('music', "style={$style}", null, null, false);
        }

        // 4. Persist to cache
        try {
            DB::table('tavern_music')->insert([
                'style'       => $style,
                'prompt_used' => $prompt,
                'file_path'   => $filePath,
                'play_count'  => 1,
                'created_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('GeminiService: failed to persist tavern music', ['error' => $e->getMessage()]);
        }

        return ['style' => $style, 'prompt' => $prompt, 'file_path' => $filePath];
    }

    /**
     * Lyria 2 via Vertex AI (clé API + project_id requis).
     * Retourne le chemin relatif storage/ ou null en cas d'échec.
     */
    private function callMusicVertexAI(string $prompt, string $style): ?string
    {
        $apiKey    = config('services.vertex_ai.api_key');
        $projectId = config('services.vertex_ai.project_id');
        $location  = config('services.vertex_ai.location', 'us-central1');

        if (!$projectId) {
            Log::warning('Vertex AI : VERTEX_AI_PROJECT_ID manquant');
            return null;
        }

        $url = sprintf(self::MUSIC_VERTEX_URL, $location, $projectId, $location)
             . "?key={$apiKey}";

        $response = Http::timeout(90)
            ->post($url, [
                'instances'  => [['prompt' => $prompt]],
                'parameters' => ['sample_count' => 1],
            ]);

        $success = $response->successful();
        $this->logGeneration('music', substr($prompt, 0, 200), null, self::COST_MUSIC, $success);

        if (!$success) {
            Log::warning('Vertex AI Lyria error', [
                'status' => $response->status(),
                'body'   => substr($response->body(), 0, 1000),
                'url'    => $url,
            ]);
            return null;
        }

        $json       = $response->json();
        $prediction = data_get($json, 'predictions.0', []);
        $b64Audio   = $prediction['bytesBase64Encoded'] ?? $prediction['audioContent'] ?? null;
        unset($json, $response);

        if (empty($b64Audio)) {
            Log::warning('Vertex AI Lyria : audio absent', ['prediction_keys' => array_keys($prediction)]);
            return null;
        }

        return $this->saveMusicBytes(base64_decode($b64Audio), $style, 'wav');
    }

    /**
     * Lyria 3 via Gemini API (même clé que le reste).
     * Retourne le chemin relatif storage/ ou null en cas d'échec.
     */
    private function callMusicGemini(string $prompt, string $style): ?string
    {
        $apiKey = config('services.gemini.api_key');

        $response = Http::timeout(90)
            ->withHeader('x-goog-api-key', $apiKey)
            ->post(self::MUSIC_GEMINI_URL, [
                'contents'        => [['parts' => [['text' => $prompt]]]],
                'generationConfig' => [
                    'responseModalities' => ['AUDIO', 'TEXT'],
                ],
            ]);

        $success = $response->successful();
        $this->logGeneration('music', substr($prompt, 0, 200), null, self::COST_MUSIC, $success);

        if (!$success) {
            Log::warning('Lyria 3 Gemini error', ['status' => $response->status(), 'body' => substr($response->body(), 0, 500)]);
            return null;
        }

        $b64Audio = null;
        foreach (data_get($response->json(), 'candidates.0.content.parts', []) as $part) {
            if (isset($part['inlineData']['data'])) {
                $b64Audio = $part['inlineData']['data'];
                break;
            }
        }
        unset($response);

        if ($b64Audio === null) {
            return null;
        }

        return $this->saveMusicBytes(base64_decode($b64Audio), $style, 'mp3');
    }

    private function saveMusicBytes(string $bytes, string $style, string $ext): string
    {
        $dir = storage_path('app/public/music/generated');

        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new \RuntimeException("Impossible de créer le dossier : {$dir}");
        }

        $filename = "music_{$style}_" . time() . ".{$ext}";
        $fullPath = "{$dir}/{$filename}";

        $written = file_put_contents($fullPath, $bytes);
        if ($written === false) {
            throw new \RuntimeException("Impossible d'écrire le fichier : {$fullPath}");
        }

        return "storage/music/generated/{$filename}";
    }

    private function buildMusicPrompt(string $style): string
    {
        $prompts = [
            'victoire_epique' => 'Epic victorious orchestral fanfare, heroic brass section, triumphant drums, soaring strings, 30 seconds, fantasy RPG style, grand and uplifting',
            'defaite'         => 'Somber melancholic medieval lute melody, slow and mournful, minor key, quiet sadness, 30 seconds, fantasy game defeat music',
            'exploration'     => 'Adventurous lighthearted fantasy exploration music, flute and strings, moderate tempo, curious and whimsical, dungeon RPG atmosphere, 30 seconds',
            'taverne'         => 'Lively medieval tavern music, upbeat folk fiddle and acoustic guitar, cheerful and energetic, slightly comedic, fantasy RPG inn ambiance, 30 seconds',
            'boss'            => 'Intense dark fantasy boss battle music, heavy drums, dramatic choir, ominous brass, fast paced and threatening, 30 seconds',
            'repos'           => 'Peaceful calm fantasy camp music, soft acoustic guitar, gentle ambient sounds, relaxing and cozy, RPG rest theme, 30 seconds',
        ];

        return $prompts[$style] ?? 'Medieval fantasy RPG background music, atmospheric and adventurous, 30 seconds';
    }

    // ─── Budget check ────────────────────────────────────────────────────────

    /**
     * Returns true if an AI call of the given type is allowed.
     */
    public function canCall(string $type): bool
    {
        if (!$this->settings->get('AI_ENABLED', 0)) {
            Log::warning("GeminiService::canCall({$type}) bloqué : AI_ENABLED=0 dans game_settings");
            return false;
        }

        $geminiKey = config('services.gemini.api_key');
        $vertexKey = config('services.vertex_ai.api_key');
        $hasKey    = ($type === 'music')
            ? (!empty($vertexKey) || !empty($geminiKey))
            : !empty($geminiKey);

        if (!$hasKey) {
            Log::warning("GeminiService::canCall({$type}) bloqué : clé API manquante (GEMINI_API_KEY / VERTEX_AI_API_KEY)");
            return false;
        }

        $dailyLimit = $this->settings->get('AI_DAILY_BUDGET_LIMIT', 1000);
        $todayUsage = DB::table('ai_generation_log')
            ->whereDate('created_at', today())
            ->sum('cost_estimate');

        if ($todayUsage >= $dailyLimit) {
            Log::warning("GeminiService::canCall({$type}) bloqué : budget journalier dépassé ({$todayUsage}/{$dailyLimit})");
            return false;
        }

        return true;
    }

    /**
     * Returns today's AI usage vs budget.
     * @return array{used: int, limit: int, percent: int}
     */
    public function budgetStatus(): array
    {
        $limit = $this->settings->get('AI_DAILY_BUDGET_LIMIT', 1000);
        $used  = (int) DB::table('ai_generation_log')
            ->whereDate('created_at', today())
            ->sum('cost_estimate');

        return [
            'used'    => $used,
            'limit'   => $limit,
            'percent' => $limit > 0 ? intdiv($used * 100, $limit) : 0,
        ];
    }

    // ─── Core HTTP ───────────────────────────────────────────────────────────

    /**
     * @param bool $removeGreenBg  Si true, supprime le fond #00FF00 et sauvegarde en PNG transparent.
     *                             Si false, sauvegarde tel quel (pour les backgrounds de zone).
     */
    private function callImageApi(string $prompt, string $type, string $filename, bool $removeGreenBg = true): ?string
    {
        $apiKey = config('services.gemini.api_key');

        $response = Http::timeout(60)
            ->post(self::IMAGE_API_URL . "?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE', 'TEXT'],
                ],
            ]);

        $success = $response->successful();
        $this->logGeneration($type, substr($prompt, 0, 200), null, self::COST_IMAGE, $success);

        if (!$success) {
            Log::warning('Gemini image API error', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        }

        $parts = data_get($response->json(), 'candidates.0.content.parts', []);
        $b64   = null;
        foreach ($parts as $part) {
            if (isset($part['inlineData']['data'])) {
                $b64 = $part['inlineData']['data'];
                break;
            }
        }

        if (empty($b64)) {
            Log::warning('Gemini image: aucune inlineData dans la réponse', ['body' => $response->body()]);
            return null;
        }

        return $this->saveImageData(base64_decode($b64), $filename, $removeGreenBg);
    }

    /**
     * Appel image avec une image de référence (multimodal) — pour les élites.
     */
    private function callImageApiWithReference(string $referenceImagePath, string $prompt, string $type, string $filename): ?string
    {
        // Monter la limite mémoire tôt : on va encoder l'image de référence + décoder la réponse + GD
        $prevMemLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        $apiKey  = config('services.gemini.api_key');
        $absPath = storage_path('app/public/' . str_replace('storage/', '', $referenceImagePath));
        $mime    = str_ends_with($absPath, '.png') ? 'image/png' : 'image/jpeg';

        // Encoder la référence et libérer les bytes bruts immédiatement
        $refRaw = file_get_contents($absPath);
        $b64Ref = base64_encode($refRaw);
        unset($refRaw);

        $response = Http::timeout(60)
            ->post(self::IMAGE_API_URL . "?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['inline_data' => ['mime_type' => $mime, 'data' => $b64Ref]],
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'responseModalities' => ['IMAGE', 'TEXT'],
                ],
            ]);

        unset($b64Ref); // Libérer le base64 de référence après envoi

        $success = $response->successful();
        $this->logGeneration($type, substr($prompt, 0, 200), null, self::COST_IMAGE, $success);

        if (!$success) {
            Log::warning('Gemini image ref API error', ['status' => $response->status(), 'body' => $response->body()]);
            ini_set('memory_limit', $prevMemLimit);
            return null;
        }

        // Extraire le base64 de l'image retournée, puis libérer la réponse complète
        $b64Image = null;
        foreach (data_get($response->json(), 'candidates.0.content.parts', []) as $part) {
            if (isset($part['inlineData']['data'])) {
                $b64Image = $part['inlineData']['data'];
                break;
            }
        }
        unset($response);

        if ($b64Image === null) {
            ini_set('memory_limit', $prevMemLimit);
            return null;
        }

        $bytes = base64_decode($b64Image);
        unset($b64Image);

        // saveImageData va faire le traitement GD (memory_limit déjà à 512M)
        $result = $this->saveImageData($bytes, $filename, true);
        ini_set('memory_limit', $prevMemLimit);
        return $result;
    }

    /**
     * Sauvegarde les bytes d'image, avec suppression optionnelle du fond vert chroma (#00FF00).
     */
    private function buildPageBackgroundPrompt(string $slug): string
    {
        $themes = [
            'dashboard'    => 'Epic dark fantasy RPG command center, heroes gathered around a glowing map table in a torchlit castle war room, dramatic cinematic lighting, deep shadows, high fantasy atmosphere, wide landscape',
            'team'         => 'Fantasy heroes assembled in a grand hall, stone pillars with banners, dramatic side lighting from tall windows, diverse warriors mages and rogues, epic fantasy art, wide landscape',
            'map'          => 'Ancient fantasy world map room, enormous glowing atlas on a stone table, magical runes floating, candles and crystal orbs, dark arcane library atmosphere, wide landscape',
            'inventory'    => 'Fantasy treasure vault chamber, glowing magical weapons and armor on stone shelves, ancient chests, mystical blue and gold light, deep shadows, atmospheric fantasy art, wide landscape',
            'forge'        => 'Roaring medieval forge workshop, massive glowing anvil, sparks flying dramatically, fire and embers, dark stone walls, master blacksmith atmosphere, cinematic fantasy art, wide landscape',
            'quests'       => 'Ancient dungeon study room, scrolls and parchments pinned to stone walls, candles dripping wax, mystical quest map, dark fantasy atmosphere, warm amber lighting, wide landscape',
            'dungeon'      => 'Long dark dungeon corridor, flickering torches on stone walls, misty depths ahead, ancient carved archways, eerie green glow from crystals, ominous fantasy atmosphere, wide landscape',
            'tavern'       => 'Cozy fantasy medieval tavern interior at night, roaring fireplace, wooden beams and barrels, warm amber light, tankards on rough-hewn tables, atmospheric fantasy art, wide landscape',
            'shop'         => 'Fantasy bazaar merchant hall, colorful potions and weapons displayed on wooden stalls, lanterns hanging overhead, mystical artifacts glowing, vibrant dark fantasy market, wide landscape',
            'consumables'  => 'Alchemist workshop laboratory, shelves of glowing colored potions, bubbling cauldrons, mystical ingredients, arcane symbols, warm magical glow, dark fantasy art, wide landscape',
            'world-boss'   => 'Epic boss arena, enormous shadowy dragon creature emerging from dark clouds, magical lightning storm, tiny brave warriors below, cinematic dark fantasy battle scene, wide landscape',
            'talents'      => 'Mystical arcane library, floating magical books and scrolls, golden skill runes in the air, swirling arcane energy, ancient stone walls with carved symbols, wide landscape',
            'profile'      => 'Hero hall of fame chamber, stone walls with carved achievement plaques and trophies, firelit torches, grand fantasy interior, introspective atmosphere, wide landscape',
        ];

        $theme = $themes[$slug] ?? "Dark fantasy RPG game screen, atmospheric dungeon environment, torchlight, stone walls, cinematic art, wide landscape";

        return "{$theme}. Style: dark moody digital painting, concept art, high detail, no text, no UI elements, no characters in foreground, suitable as background wallpaper, aspect ratio 16:9.";
    }

    private function fallbackPageBackground(string $slug): string
    {
        return "images/placeholders/page_bg_{$slug}.jpg";
    }

    private function saveImageData(string $bytes, string $filename, bool $removeGreenBg): ?string
    {
        // Déduire le sous-dossier depuis le préfixe du nom de fichier
        $subdir = match (true) {
            str_starts_with($filename, 'hero_')    => 'heroes',
            str_starts_with($filename, 'monster_') => 'monsters',
            str_starts_with($filename, 'zone_')    => 'zones',
            str_starts_with($filename, 'page_')    => 'pages',
            default                                => 'loot_images',
        };

        $dir = storage_path("app/public/{$subdir}");
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fullPath = "{$dir}/{$filename}";

        if ($removeGreenBg && function_exists('imagecreatefromstring')) {
            // Augmenter la limite mémoire temporairement pour GD (images HD)
            $prevMemLimit = ini_get('memory_limit');
            ini_set('memory_limit', '512M');

            $img = imagecreatefromstring($bytes);
            unset($bytes); // Libérer les bytes bruts immédiatement

            if ($img !== false) {
                $w = imagesx($img);
                $h = imagesy($img);
                $out = imagecreatetruecolor($w, $h);
                imagealphablending($out, false);
                imagesavealpha($out, true);
                $transparent = imagecolorallocatealpha($out, 0, 0, 0, 127);
                imagefill($out, 0, 0, $transparent);

                // Détection par ratio : le vert doit dominer nettement sur R et B.
                // Plus robuste que les seuils absolus face aux artefacts JPEG.
                for ($x = 0; $x < $w; $x++) {
                    for ($y = 0; $y < $h; $y++) {
                        $rgb = imagecolorsforindex($img, imagecolorat($img, $x, $y));
                        $r = $rgb['red'];
                        $g = $rgb['green'];
                        $b = $rgb['blue'];

                        // Pixel considéré "vert chroma" si :
                        //  - vert > 100 (lumineux)
                        //  - vert > rouge * 1.4  ET  vert > bleu * 1.4  (dominant)
                        //  - rouge < 160  ET  bleu < 160  (pas blanc/jaune)
                        if ($g > 100 && $g > $r * 1.4 && $g > $b * 1.4 && $r < 160 && $b < 160) {
                            // Antialiasing : proportionnel à combien le pixel est "vert"
                            $greenness = min(127, (int)(($g - max($r, $b)) / 2));
                            $alpha = $greenness + $rgb['alpha'];
                            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, min(127, $alpha)));
                        } else {
                            imagesetpixel($out, $x, $y, imagecolorallocatealpha($out, $r, $g, $b, $rgb['alpha']));
                        }
                    }
                }

                $pngFile = preg_replace('/\.(jpg|jpeg)$/i', '.png', $fullPath);
                imagepng($out, $pngFile);
                imagedestroy($img);
                imagedestroy($out);
                ini_set('memory_limit', $prevMemLimit);
                return "storage/{$subdir}/" . basename($pngFile);
            }

            ini_set('memory_limit', $prevMemLimit);
        }

        file_put_contents($fullPath, $bytes);
        return "storage/{$subdir}/{$filename}";
    }

    private function callTextApi(string $prompt, string $type, int $maxTokens): ?string
    {
        $apiKey = config('services.gemini.api_key');

        $response = Http::timeout(15)
            ->post(self::TEXT_API_URL . "?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => $maxTokens,
                    'temperature'     => 0.9,
                ],
            ]);

        $tokens = null;
        $success = false;
        $text = null;

        if ($response->successful()) {
            $body   = $response->json();
            $text   = data_get($body, 'candidates.0.content.parts.0.text');
            $tokens = data_get($body, 'usageMetadata.totalTokenCount');
            $success = $text !== null;
        } else {
            Log::warning('Gemini API error', ['status' => $response->status(), 'body' => $response->body()]);
        }

        $this->logGeneration($type, substr($prompt, 0, 200), $tokens, self::COST_TEXT, $success);

        return $text ? trim($text) : null;
    }

    // ─── Logging ─────────────────────────────────────────────────────────────

    private function logGeneration(string $type, string $promptSummary, ?int $tokens, ?int $cost, bool $success): void
    {
        try {
            DB::table('ai_generation_log')->insert([
                'type'           => $type,
                'prompt_summary' => substr($promptSummary, 0, 255),
                'tokens_used'    => $tokens,
                'cost_estimate'  => $cost,
                'success'        => $success ? 1 : 0,
                'created_at'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log AI generation', ['error' => $e->getMessage()]);
        }
    }

    // ─── Prompt builders ─────────────────────────────────────────────────────

    private function buildHeroImagePrompt(string $raceName, string $classSlug, ?string $traitSlug): string
    {
        $classDesc = match ($classSlug) {
            'guerrier'     => 'warrior in heavy armor, sword and shield',
            'barbare'      => 'barbarian with war axe, fur cloak, muscular',
            'mage'         => 'mage with magic staff, robes, glowing runes',
            'necromancien' => 'necromancer in dark robes, skull motifs, purple energy',
            'barde'        => 'bard with lute, colorful clothes, charismatic pose',
            'pretre'       => 'priest with holy symbol, white and gold robes',
            'voleur'       => 'rogue in dark leather armor, daggers, hood',
            'ranger'       => 'ranger with bow, green cloak, forest attire',
            default        => 'adventurer in fantasy attire',
        };

        $traitDesc = match ($traitSlug) {
            'couard'        => 'slightly nervous expression, looking over shoulder',
            'narcoleptique' => 'drowsy half-closed eyes, pillow tucked under arm',
            'pyromane'      => 'singed eyebrows, manic grin, small flame in hand',
            'kleptomane'    => 'pockets bulging, shifty eyes, coin purse in hand',
            'maladroit'     => 'bandaged hands and knees, surprised expression',
            default         => '',
        };

        return "Fantasy RPG character portrait, {$raceName} {$classDesc}"
            . ($traitDesc ? ", {$traitDesc}" : '')
            . ", humorous medieval fantasy style, pixel art inspired, detailed, "
            . "isolated on pure solid #00FF00 green background, no gradients, no shadows, no vignette on background, "
            . "character centered, square format.";
    }

    private function buildZoneBackgroundPrompt(string $zoneSlug, string $element, string $description): string
    {
        $elementDesc = match ($element) {
            'feu'      => 'volcanic, lava rivers, fire and embers',
            'glace'    => 'frozen, ice crystals, blizzard atmosphere',
            'foudre'   => 'stormy, lightning strikes, electric atmosphere',
            'poison'   => 'toxic swamp, purple fog, corrupted vegetation',
            'ombre'    => 'dark shadow realm, void darkness, ghostly mist',
            'sacre'    => 'holy light rays, golden ambiance, divine atmosphere',
            'physique' => 'stone dungeon, torchlight, medieval architecture',
            default    => 'dark fantasy dungeon',
        };

        return "Dark fantasy RPG zone background illustration, {$elementDesc}, "
            . "atmospheric and immersive, no characters or text, cinematic wide angle, "
            . "painterly illustration style, moody lighting, {$description}, "
            . "game background art, highly detailed environment.";
    }

    private function buildNarrationPrompt(string $eventType, array $context): string
    {
        $hero    = $context['hero_name'] ?? 'le héros';
        $zone    = $context['zone_name'] ?? 'le donjon';
        $enemy   = $context['enemy_name'] ?? 'le monstre';

        return match ($eventType) {
            'combat_win'   => "Tu es le Narrateur sarcastique du jeu Le Donjon des Incompétents. {$hero} vient de vaincre {$enemy} dans {$zone}. Donne un commentaire sarcastique de 1-2 phrases en français.",
            'combat_defeat' => "Tu es le Narrateur sarcastique. {$hero} vient de perdre contre {$enemy}. Commentaire sarcastique de 1-2 phrases en français.",
            'level_up'     => "Tu es le Narrateur sarcastique. {$hero} vient de gagner un niveau. Commentaire de 1-2 phrases, enthousiasmé à contrecœur.",
            'loot_found'   => "Tu es le Narrateur sarcastique. {$hero} a trouvé un objet. Commentaire de 1-2 phrases.",
            'dungeon_complete' => "Tu es le Narrateur sarcastique. {$hero} vient de compléter un donjon dans {$zone}. Commentaire de 1-2 phrases, impressionné malgré lui.",
            default        => "Tu es le Narrateur sarcastique du Donjon des Incompétents. Commente l'action \"{$eventType}\" de {$hero} en 1-2 phrases sarcastiques.",
        };
    }

    private function buildLootTextPrompt(string $itemType, string $rarity, int $level): string
    {
        $rarityFr = match ($rarity) {
            'commun'      => 'commun et ennuyeux',
            'peu_commun'  => 'légèrement intéressant',
            'rare'        => 'rare et impressionnant',
            'epique'      => 'épique et excessif',
            'legendaire'  => 'légendaire et absurde',
            'wtf'         => 'tellement bizarre que ça en devient fascinant',
            default       => $rarity,
        };

        return "Tu es le Narrateur sarcastique du Donjon des Incompétents. "
            . "Crée un objet de type \"{$itemType}\", niveau {$level}, de rareté {$rarityFr}. "
            . "Le nom doit être humoristique et en français. La description en 1 phrase sarcastique. "
            . "Format JSON strict : {\"name\": \"...\", \"description\": \"...\"}";
    }

    private function buildLootImagePrompt(string $slot, string $rarity): string
    {
        $slotFr = match ($slot) {
            'arme'        => 'weapon sword or staff',
            'armure'      => 'armor breastplate',
            'casque'      => 'helmet or hood',
            'bottes'      => 'boots or greaves',
            'accessoire'  => 'amulet or ring',
            'truc_bizarre'=> 'mysterious glowing artifact',
            default       => 'fantasy item',
        };

        $rarityStyle = match ($rarity) {
            'commun'      => 'simple, worn, grey tones',
            'peu_commun'  => 'slightly glowing, green tones',
            'rare'        => 'magical glow, blue tones',
            'epique'      => 'powerful aura, purple tones, ornate',
            'legendaire'  => 'legendary golden glow, ornate engravings',
            'wtf'         => 'absurd, rainbow glowing, bizarre shapes, surreal',
            default       => 'fantasy style',
        };

        return "Fantasy RPG item illustration, {$slotFr}, {$rarityStyle}, "
            . "pixel art style, dark background, centered, no text, square format, "
            . "humorous medieval fantasy game style";
    }

    private function buildQuestPrompt(string $zoneSlug, int $zoneLevel): string
    {
        return "Tu es le Narrateur sarcastique du Donjon des Incompétents. "
            . "Crée une quête absurde et humoristique pour la zone \"{$zoneSlug}\" (niveau {$zoneLevel}). "
            . "Titre court (max 60 chars), description drôle (max 200 chars), texte de saveur sarcastique (max 100 chars). "
            . "Format JSON strict : {\"title\": \"...\", \"description\": \"...\", \"flavor\": \"...\"}";
    }

    // ─── Response parsers ────────────────────────────────────────────────────

    /** @return array{name: string, description: string}|null */
    private function parseLootTextResponse(string $raw): ?array
    {
        // Try to extract JSON from response (model might wrap it in markdown)
        $json = $this->extractJson($raw);
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['name']) || empty($data['description'])) {
            return null;
        }

        return [
            'name'        => substr((string) $data['name'], 0, 100),
            'description' => substr((string) $data['description'], 0, 255),
        ];
    }

    /** @return array{title: string, description: string, flavor: string}|null */
    private function parseQuestTextResponse(string $raw): ?array
    {
        $json = $this->extractJson($raw);
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['title']) || empty($data['description'])) {
            return null;
        }

        return [
            'title'       => substr((string) $data['title'], 0, 60),
            'description' => substr((string) $data['description'], 0, 200),
            'flavor'      => substr((string) ($data['flavor'] ?? ''), 0, 100),
        ];
    }

    private const VALID_ELEMENTS = ['physique', 'feu', 'glace', 'foudre', 'poison', 'sacre', 'ombre'];

    /** @return array{name: string, slug: string, description: string, element: string, monster_theme: string, flavor: string}|null */
    private function parseZoneResponse(string $raw): ?array
    {
        $json = $this->extractJson($raw);
        if ($json === null) {
            return null;
        }

        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['name']) || empty($data['slug']) || empty($data['element'])) {
            return null;
        }

        // Validate element
        if (!in_array($data['element'], self::VALID_ELEMENTS)) {
            $data['element'] = 'physique';
        }

        // Sanitize slug: only lowercase letters, digits, underscores
        $slug = preg_replace('/[^a-z0-9_]/', '_', strtolower((string) $data['slug']));
        $slug = preg_replace('/_+/', '_', trim($slug, '_'));

        return [
            'name'          => substr((string) $data['name'], 0, 50),
            'slug'          => substr($slug, 0, 50),
            'description'   => substr((string) ($data['description'] ?? ''), 0, 200),
            'element'       => $data['element'],
            'monster_theme' => substr((string) ($data['monster_theme'] ?? ''), 0, 200),
            'flavor'        => substr((string) ($data['flavor'] ?? ''), 0, 100),
        ];
    }

    /** @return array{name: string, slug: string, description: string, element: string, monster_theme: string, flavor: string} */
    private function fallbackZone(int $zoneIndex, int $levelMin, int $levelMax): array
    {
        $themes = [
            ['name' => 'Les Archives Abandonnées',     'element' => 'ombre',    'monster_theme' => 'Bureaucrates zombifiés et fantômes de formulaires non remplis'],
            ['name' => 'Le Laboratoire du Savant Fou', 'element' => 'foudre',   'monster_theme' => 'Expériences ratées et assistants incompétents'],
            ['name' => 'La Cuisine Maudite',           'element' => 'feu',      'monster_theme' => 'Ingrédients animés et recettes qui se défendent'],
            ['name' => 'La Bibliothèque Interdite',    'element' => 'sacre',    'monster_theme' => 'Livres sentients et bibliothécaires zélés'],
            ['name' => 'Les Égouts Enchantés',         'element' => 'poison',   'monster_theme' => 'Créatures aquatiques et champignons mutants'],
            ['name' => 'La Salle des Trophées',        'element' => 'physique', 'monster_theme' => 'Armures animées et souvenirs de batailles oubliées'],
        ];

        $theme = $themes[$zoneIndex % count($themes)];
        $slug  = 'zone_ia_' . $zoneIndex;

        return [
            'name'          => $theme['name'] . " (Zone {$zoneIndex})",
            'slug'          => $slug,
            'description'   => "Zone générée pour les niveaux {$levelMin}-{$levelMax}. {$theme['monster_theme']}.",
            'element'       => $theme['element'],
            'monster_theme' => $theme['monster_theme'],
            'flavor'        => "Le Narrateur a généré ça à {$zoneIndex}h du matin. Ça se voit.",
        ];
    }

    /** Extract first JSON object from a string (handles markdown code blocks) */
    private function extractJson(string $text): ?string
    {
        // Remove markdown code fences
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/\s*```\s*$/m', '', $text);

        // Find first { ... }
        $start = strpos($text, '{');
        $end   = strrpos($text, '}');

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return substr($text, $start, $end - $start + 1);
    }

    // ─── Fallbacks (toujours disponibles, jamais null) ───────────────────────

    private function fallbackNarration(string $eventType, array $context): string
    {
        $hero = $context['hero_name'] ?? 'le héros';

        $templates = [
            'combat_win' => [
                "Victoire ! Enfin, \"victoire\"... Remarquable dans sa médiocrité.",
                "L'ennemi est vaincu. Le Narrateur note que ce n'était pas particulièrement impressionnant.",
                "Succès ! {$hero} triomphe. Le Narrateur est presque fier.",
                "Vous avez gagné. Ne vous y habituez pas.",
            ],
            'combat_defeat' => [
                "Défaite. Remarquable dans sa constance.",
                "{$hero} gît sur le sol. Le Narrateur prend des notes.",
                "Battus. Par ça. Chapeau.",
                "La défaite est consommée. Le Narrateur a cessé d'être surpris.",
            ],
            'level_up' => [
                "Niveau supérieur ! {$hero} est maintenant légèrement moins incompétent.",
                "Montée de niveau ! Continuez comme ça et vous deviendrez... correct.",
                "Niveau suivant atteint. Le Narrateur acquiesce avec modération.",
            ],
            'loot_found' => [
                "Un objet trouvé ! Il est probablement nul, mais c'est le vôtre.",
                "Du loot ! Gérard en serait jaloux. Peut-être.",
                "Trouvé quelque chose. Ça ne rendra pas {$hero} moins incompétent.",
            ],
            'dungeon_complete' => [
                "Donjon complété ! {$hero} ressort vivant. Le Narrateur est déçu.",
                "Victoire dans le donjon. Statistiquement improbable.",
                "Le donjon est vaincu. Le Narrateur note que les monstres étaient distraits.",
            ],
        ];

        $list = $templates[$eventType] ?? [
            "Événement noté. Le Narrateur bâille.",
            "Quelque chose s'est passé. Le Narrateur est indifférent.",
            "Action enregistrée. Ça ne compte probablement pas.",
        ];

        return $list[array_rand($list)];
    }

    /** @return array{name: string, description: string} */
    private function fallbackLootText(string $itemType, string $rarity, int $level): array
    {
        $adjectives = ['Miteux', 'Improbable', 'Bancal', 'Légendairement Nul', 'Surprenant', 'Douteux'];
        $suffixes   = ['de la Honte', 'du Désespoir', 'de l\'Incompétent', 'du Pauvre', 'Sans Nom'];

        $adj = $adjectives[array_rand($adjectives)];
        $suf = $suffixes[array_rand($suffixes)];

        return [
            'name'        => "{$itemType} {$adj} {$suf}",
            'description' => "Un objet de niveau {$level}. Le Narrateur n'a pas grand-chose à dire dessus.",
        ];
    }

    /** @return array{title: string, description: string, flavor: string} */
    private function fallbackQuestText(string $zoneSlug, int $zoneLevel): array
    {
        $titles = [
            'Récupérer le Machin Perdu',
            'Tuer des Trucs dans la Zone',
            'Livrer Quelque Chose à Quelqu\'un',
            'Enquêter sur des Bruits Suspects',
            'Retrouver le Chapeau du Mage',
            'Compter les Pierres du Donjon',
            'Vérifier que les Monstres Sont Bien Là',
        ];

        $descriptions = [
            "Une quête d'une médiocrité remarquable dans {$zoneSlug}.",
            "Quelqu'un a besoin d'aide. Ce quelqu'un n'est pas très exigeant.",
            "Mission d'une simplicité désarmante pour un groupe de niveau {$zoneLevel}.",
        ];

        $flavors = [
            "Le Narrateur bâille poliment.",
            "Même les monstres ne comprennent pas pourquoi.",
            "Récompense généreuse pour un travail... présent.",
        ];

        return [
            'title'       => $titles[array_rand($titles)],
            'description' => $descriptions[array_rand($descriptions)],
            'flavor'      => $flavors[array_rand($flavors)],
        ];
    }

    /** @return array{description: string, mechanic: string} */
    private function fallbackBossText(string $bossName): array
    {
        return [
            'description' => "{$bossName} est là. Il a l'air mécontent. Le Narrateur suggère de courir.",
            'mechanic'    => "Attaque très fort. Régulièrement. Sans s'arrêter.",
        ];
    }

    private function fallbackLootImage(string $slot, string $rarity): string
    {
        $rarityGroup = match ($rarity) {
            'legendaire', 'wtf' => 'legendary',
            'epique'            => 'epic',
            'rare'              => 'rare',
            default             => 'common',
        };

        return "images/placeholders/loot/{$slot}_{$rarityGroup}.png";
    }

    private function fallbackHeroImage(string $classSlug): string
    {
        return "images/placeholders/heroes/{$classSlug}.png";
    }

    private function fallbackMonsterImage(string $element): string
    {
        return "images/placeholders/monsters/{$element}.png";
    }

    private function fallbackZoneBackground(string $element): string
    {
        return "images/placeholders/zones/{$element}_bg.jpg";
    }

    /**
     * Generates a legendary hero epithet and backstory.
     * @return array{epithet: string, backstory: string}
     */
    public function generateLegendaryHero(string $heroName, string $className, string $traitName): array
    {
        if (!$this->settings->get('AI_ENABLED', 0)) {
            return $this->fallbackLegendaryHero($heroName);
        }

        try {
            $prompt = "Tu es le Narrateur sarcastique du jeu 'Le Donjon des Incompétents'. "
                . "Génère pour un héros légendaire (mais quand même incompétent) appelé '{$heroName}', "
                . "classe '{$className}', avec le défaut '{$traitName}' :\n"
                . "1. Un épithète court (5-8 mots, style 'le Presque-Grand', 'de la Catastrophe Évitée')\n"
                . "2. Une biographie absurde de 1-2 phrases expliquant pourquoi il est 'légendaire' malgré son incompétence.\n"
                . "Réponds UNIQUEMENT avec du JSON valide : {\"epithet\": \"...\", \"backstory\": \"...\"}";

            $raw = $this->callTextApi($prompt, 'legendary_hero', 200);
            if ($raw === null) {
                return $this->fallbackLegendaryHero($heroName);
            }
            $data = json_decode($this->extractJson($raw), true);

            if (isset($data['epithet'], $data['backstory'])) {
                return [
                    'epithet'   => mb_substr($data['epithet'], 0, 100),
                    'backstory' => mb_substr($data['backstory'], 0, 300),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateLegendaryHero failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackLegendaryHero($heroName);
    }

    /** @return array{epithet: string, backstory: string} */
    private function fallbackLegendaryHero(string $heroName): array
    {
        $epithets = [
            'le Presque-Légendaire',
            'de la Renommée Discutable',
            'qui Faillit Sauver le Monde',
            'l\'À-peu-près Glorieux',
            'du Passé Douteux',
            'le Survivant Accidentel',
        ];

        $backstories = [
            "{$heroName} a vaincu un dragon une fois. Le dragon dormait.",
            "Les bardes chantent ses exploits. Ils ont exagéré BEAUCOUP.",
            "{$heroName} a sauvé un village. Par erreur. En fuyant.",
            "Autrefois célèbre, aujourd'hui dans votre taverne. C'est la vie.",
            "Légendaire dans trois villages. Recherché dans quatre autres.",
        ];

        return [
            'epithet'   => $epithets[array_rand($epithets)],
            'backstory' => $backstories[array_rand($backstories)],
        ];
    }

    /** @return array{style: string, prompt: string, file_path: string} */
    private function fallbackTavernMusic(string $style): array
    {
        $tracks = [
            'victoire_epique'  => 'music/fallback/victory.ogg',
            'defaite'          => 'music/fallback/defeat.ogg',
            'exploration'      => 'music/fallback/exploration.ogg',
            'taverne'          => 'music/fallback/tavern.ogg',
            'boss'             => 'music/fallback/boss.ogg',
            'repos'            => 'music/fallback/rest.ogg',
        ];

        return [
            'style'     => $style,
            'prompt'    => "fallback:{$style}",
            'file_path' => $tracks[$style] ?? 'music/fallback/tavern.ogg',
        ];
    }

}
