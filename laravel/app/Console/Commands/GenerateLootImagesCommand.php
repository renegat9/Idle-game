<?php

namespace App\Console\Commands;

use App\Services\GeminiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateLootImagesCommand extends Command
{
    protected $signature = 'images:generate
                            {--slot= : Slot spécifique (arme, armure, casque, bottes, accessoire, truc_bizarre)}
                            {--rarity= : Rareté spécifique (commun, peu_commun, rare, epique, legendaire, wtf)}
                            {--force : Régénérer même si une image existe déjà}
                            {--delay=4 : Délai en secondes entre chaque génération (défaut: 4s)}';

    protected $description = 'Génère les illustrations des objets loot via Imagen (Gemini).';

    private const SLOTS    = ['arme', 'armure', 'casque', 'bottes', 'accessoire', 'truc_bizarre'];
    private const RARITIES = ['commun', 'peu_commun', 'rare', 'epique', 'legendaire', 'wtf'];

    public function handle(GeminiService $gemini): int
    {
        $slots    = $this->option('slot')   ? [$this->option('slot')]   : self::SLOTS;
        $rarities = $this->option('rarity') ? [$this->option('rarity')] : self::RARITIES;
        $force    = (bool) $this->option('force');
        $delay    = max(0, (int) $this->option('delay'));

        // Validate inputs
        foreach ($slots as $slot) {
            if (!in_array($slot, self::SLOTS)) {
                $this->error("Slot invalide : {$slot}. Valeurs : " . implode(', ', self::SLOTS));
                return self::FAILURE;
            }
        }
        foreach ($rarities as $rarity) {
            if (!in_array($rarity, self::RARITIES)) {
                $this->error("Rareté invalide : {$rarity}. Valeurs : " . implode(', ', self::RARITIES));
                return self::FAILURE;
            }
        }

        if (!$gemini->canCall('loot_image')) {
            $this->error('Génération d\'image désactivée (AI_ENABLED=0 ou budget dépassé ou clé manquante).');
            return self::FAILURE;
        }

        $budget  = $gemini->budgetStatus();
        $this->info("Budget IA : {$budget['used']}/{$budget['limit']} ({$budget['percent']}%)");

        $total   = count($slots) * count($rarities);
        $done    = 0;
        $skipped = 0;
        $failed  = 0;

        // Fetch all item_templates that need images
        $query = DB::table('item_templates')
            ->whereIn('slot', $slots)
            ->whereIn('rarity', $rarities);

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('image_path')
                  ->orWhere('image_path', 'like', 'images/placeholders/%');
            });
        }

        $items = $query->get(['id', 'slot', 'rarity', 'name']);

        if ($items->isEmpty()) {
            $this->info($force ? 'Aucun objet trouvé pour ces critères.' : 'Tous les objets ont déjà une image. Utilisez --force pour régénérer.');
            return self::SUCCESS;
        }

        $total = $items->count();
        $this->info("Objets à traiter : {$total} (délai : {$delay}s entre chaque)");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($items as $i => $item) {
            if (!$gemini->canCall('loot_image')) {
                $bar->finish();
                $this->newLine();
                $this->warn('Budget IA dépassé, arrêt anticipé.');
                break;
            }

            $path = $gemini->generateLootImage($item->id, $item->slot, $item->rarity);

            // Toujours sauvegarder le chemin (même placeholder) pour ne pas retenter sans --force
            DB::table('item_templates')
                ->where('id', $item->id)
                ->update(['image_path' => $path]);

            if (str_starts_with($path, 'images/placeholders/')) {
                $failed++;
            } else {
                $done++;
            }

            $bar->advance();

            // Délai entre les appels (sauf après le dernier)
            if ($delay > 0 && $i < $total - 1) {
                sleep($delay);
            }
        }

        $bar->finish();
        $this->newLine();

        $budget = $gemini->budgetStatus();
        $this->info("Résultat : {$done} générée(s), {$failed} échouée(s).");
        $this->info("Budget IA après : {$budget['used']}/{$budget['limit']} ({$budget['percent']}%)");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
