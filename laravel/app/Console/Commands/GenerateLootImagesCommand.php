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
                            {--delay=4 : Délai en secondes entre chaque génération (défaut: 4s)}
                            {--list : Afficher les combinaisons sans générer}';

    protected $description = 'Génère une image par combinaison slot+rareté via Gemini et l\'assigne à tous les items concernés.';

    private const SLOTS    = ['arme', 'armure', 'casque', 'bottes', 'accessoire', 'truc_bizarre'];
    private const RARITIES = ['commun', 'peu_commun', 'rare', 'epique', 'legendaire', 'wtf'];

    public function handle(GeminiService $gemini): int
    {
        $slots    = $this->option('slot')   ? [$this->option('slot')]   : self::SLOTS;
        $rarities = $this->option('rarity') ? [$this->option('rarity')] : self::RARITIES;
        $force    = (bool) $this->option('force');
        $delay    = max(0, (int) $this->option('delay'));
        $listOnly = (bool) $this->option('list');

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

        // ── Mode --list : afficher l'état de toutes les combinaisons ──────────
        if ($listOnly) {
            $all = DB::table('item_templates')
                ->whereIn('slot', $slots)
                ->whereIn('rarity', $rarities)
                ->select('slot', 'rarity', 'image_path')
                ->distinct()
                ->orderBy('slot')->orderBy('rarity')
                ->get();

            $rows = $all->map(fn($r) => [
                $r->slot,
                $r->rarity,
                match (true) {
                    empty($r->image_path)                              => '❌ manquante',
                    str_starts_with($r->image_path, 'images/placeholders/') => '⚠️  placeholder',
                    default                                            => '✅ ' . $r->image_path,
                },
            ])->toArray();

            $this->table(['Slot', 'Rareté', 'Image'], $rows);
            $missing = $all->filter(fn($r) =>
                empty($r->image_path) || str_starts_with($r->image_path, 'images/placeholders/')
            )->count();
            $this->info("{$missing} combinaison(s) sans image réelle sur {$all->count()} totales.");
            return self::SUCCESS;
        }

        // ── Vérification budget ───────────────────────────────────────────────
        if (!$gemini->canCall('loot_image')) {
            $this->error('Génération d\'image désactivée (AI_ENABLED=0 ou budget dépassé ou clé manquante).');
            return self::FAILURE;
        }

        $budget = $gemini->budgetStatus();
        $this->info("Budget IA : {$budget['used']}/{$budget['limit']} ({$budget['percent']}%)");

        // ── Combinaisons à traiter ────────────────────────────────────────────
        $query = DB::table('item_templates')
            ->whereIn('slot', $slots)
            ->whereIn('rarity', $rarities)
            ->select('slot', 'rarity')
            ->distinct()
            ->orderBy('slot')->orderBy('rarity');

        if (!$force) {
            $query->where(function ($q) {
                $q->whereNull('image_path')
                  ->orWhere('image_path', 'like', 'images/placeholders/%');
            });
        }

        $combinations = $query->get();

        if ($combinations->isEmpty()) {
            $this->info($force
                ? 'Aucune combinaison trouvée.'
                : 'Toutes les combinaisons ont déjà une image. Utilisez --force pour régénérer ou --list pour voir l\'état.'
            );
            return self::SUCCESS;
        }

        $total = $combinations->count();
        $this->info("Combinaisons à générer : {$total} (délai : {$delay}s entre chaque)");

        $done   = 0;
        $failed = 0;
        $bar    = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($combinations as $i => $combo) {
            if (!$gemini->canCall('loot_image')) {
                $bar->finish();
                $this->newLine();
                $this->warn("Budget IA dépassé après {$done} générée(s). Relancez demain ou augmentez AI_DAILY_BUDGET_LIMIT.");
                break;
            }

            $path = $gemini->generateLootImage(0, $combo->slot, $combo->rarity);

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
