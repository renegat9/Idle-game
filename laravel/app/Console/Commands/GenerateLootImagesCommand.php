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

    protected $description = 'Génère une image par combinaison slot+rareté via Imagen et l\'assigne à tous les items concernés.';

    private const SLOTS    = ['arme', 'armure', 'casque', 'bottes', 'accessoire', 'truc_bizarre'];
    private const RARITIES = ['commun', 'peu_commun', 'rare', 'epique', 'legendaire', 'wtf'];

    public function handle(GeminiService $gemini): int
    {
        $slots    = $this->option('slot')   ? [$this->option('slot')]   : self::SLOTS;
        $rarities = $this->option('rarity') ? [$this->option('rarity')] : self::RARITIES;
        $force    = (bool) $this->option('force');
        $delay    = max(0, (int) $this->option('delay'));

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

        $budget = $gemini->budgetStatus();
        $this->info("Budget IA : {$budget['used']}/{$budget['limit']} ({$budget['percent']}%)");

        // Trouver les combinaisons slot+rareté distinctes qui ont besoin d'une image
        $query = DB::table('item_templates')
            ->whereIn('slot', $slots)
            ->whereIn('rarity', $rarities)
            ->select('slot', 'rarity')
            ->distinct();

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('image_path')
                  ->orWhere('image_path', 'like', 'images/placeholders/%');
            });
        }

        $combinations = $query->get();

        if ($combinations->isEmpty()) {
            $this->info($force ? 'Aucune combinaison trouvée.' : 'Toutes les combinaisons ont déjà une image. Utilisez --force pour régénérer.');
            return self::SUCCESS;
        }

        $total = $combinations->count();
        $this->info("Combinaisons slot+rareté à générer : {$total} (délai : {$delay}s entre chaque)");

        $done   = 0;
        $failed = 0;
        $bar    = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($combinations as $i => $combo) {
            if (!$gemini->canCall('loot_image')) {
                $bar->finish();
                $this->newLine();
                $this->warn('Budget IA dépassé, arrêt anticipé.');
                break;
            }

            // Utiliser un id=0 car l'image est partagée (nom basé sur slot+rarity)
            $path = $gemini->generateLootImage(0, $combo->slot, $combo->rarity);

            // Assigner cette image à TOUS les items de cette combinaison
            DB::table('item_templates')
                ->where('slot', $combo->slot)
                ->where('rarity', $combo->rarity)
                ->update(['image_path' => $path]);

            if (str_starts_with($path, 'images/placeholders/')) {
                $failed++;
            } else {
                $done++;
            }

            $bar->advance();

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
