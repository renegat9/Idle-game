<?php

namespace App\Console\Commands;

use App\Jobs\GenerateLootImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueMissingItemImagesCommand extends Command
{
    protected $signature   = 'items:queue-missing-images
                              {--rarity= : Filtrer par rareté (rare, epique, legendaire, wtf)}
                              {--slot=   : Filtrer par slot (arme, armure, casque, bottes, accessoire, truc_bizarre)}
                              {--list    : Afficher les items sans générer}';
    protected $description = 'Dispatche GenerateLootImage pour les items Rare+ sans image_url.';

    private const IMAGE_RARITIES = ['rare', 'epique', 'legendaire', 'wtf'];

    public function handle(): int
    {
        $rarities = $this->option('rarity')
            ? [$this->option('rarity')]
            : self::IMAGE_RARITIES;

        $query = DB::table('items')
            ->whereNull('image_url')
            ->whereIn('rarity', $rarities);

        if ($slot = $this->option('slot')) {
            $query->where('slot', $slot);
        }

        $items = $query->select('id', 'name', 'rarity', 'slot')->get();

        if ($items->isEmpty()) {
            $this->info('Aucun item sans image trouvé.');
            return self::SUCCESS;
        }

        if ($this->option('list')) {
            $this->table(['ID', 'Nom', 'Rareté', 'Slot'], $items->map(fn($i) => [
                $i->id, $i->name, $i->rarity, $i->slot,
            ])->toArray());
            $this->info("{$items->count()} item(s) sans image.");
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($items->count());
        $bar->start();

        foreach ($items as $item) {
            GenerateLootImage::dispatch($item->id, $item->slot, $item->rarity);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("{$items->count()} job(s) dispatchés. Ils seront traités par queue:work dans les 5 prochaines minutes.");

        return self::SUCCESS;
    }
}
