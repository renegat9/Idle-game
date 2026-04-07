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
    private const TEXT_API_URL  = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';
    private const IMAGE_API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-preview-image-generation:generateContent';

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
            $path = $this->callImageApi($prompt, 'loot_image', $filename);
            if ($path !== null) {
                return $path;
            }
        } catch (\Throwable $e) {
            Log::warning('GeminiService::generateLootImage failed', ['error' => $e->getMessage()]);
        }

        return $this->fallbackLootImage($slot, $rarity);
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
     * Generate a music prompt for the tavern ambiance.
     * Returns a music style label + prompt for MusicFX (or placeholder if unavailable).
     * @return array{style: string, prompt: string, file_path: string}
     */
    public function generateTavernMusic(string $style): array
    {
        // Music generation uses MusicFX which isn't publicly available via standard API
        // Always use fallback music from the static library
        $this->logGeneration('music', "style={$style}", null, null, false);

        return $this->fallbackTavernMusic($style);
    }

    // ─── Budget check ────────────────────────────────────────────────────────

    /**
     * Returns true if an AI call of the given type is allowed.
     */
    public function canCall(string $type): bool
    {
        if (!$this->settings->get('AI_ENABLED', 0)) {
            return false;
        }

        $apiKey = config('services.gemini.api_key');
        if (empty($apiKey)) {
            return false;
        }

        $dailyLimit = $this->settings->get('AI_DAILY_BUDGET_LIMIT', 1000);
        $todayUsage = DB::table('ai_generation_log')
            ->whereDate('created_at', today())
            ->sum('cost_estimate');

        return $todayUsage < $dailyLimit;
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

    private function callImageApi(string $prompt, string $type, string $filename): ?string
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

        $dir = storage_path('app/public/loot_images');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $fullPath = $dir . '/' . $filename;
        file_put_contents($fullPath, base64_decode($b64));

        return 'storage/loot_images/' . $filename;
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
        // Static placeholder images by slot + rarity tier
        $rarityGroup = match ($rarity) {
            'legendaire', 'wtf' => 'legendary',
            'epique'            => 'epic',
            'rare'              => 'rare',
            default             => 'common',
        };

        return "images/placeholders/loot/{$slot}_{$rarityGroup}.png";
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
            'victoire_epique'  => 'music/fallback/victory.mp3',
            'defaite'          => 'music/fallback/defeat.mp3',
            'exploration'      => 'music/fallback/exploration.mp3',
            'taverne'          => 'music/fallback/tavern.mp3',
            'boss'             => 'music/fallback/boss.mp3',
            'repos'            => 'music/fallback/rest.mp3',
        ];

        return [
            'style'     => $style,
            'prompt'    => "fallback:{$style}",
            'file_path' => $tracks[$style] ?? 'music/fallback/tavern.mp3',
        ];
    }
}
