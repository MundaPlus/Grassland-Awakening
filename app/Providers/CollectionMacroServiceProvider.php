<?php

namespace App\Providers;

use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class CollectionMacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Collection::macro('randomWeighted', function ($weightField = 'weight') {
            if ($this->isEmpty()) {
                return null;
            }

            $totalWeight = $this->sum($weightField);
            if ($totalWeight <= 0) {
                return $this->random();
            }

            $random = rand(1, $totalWeight);
            $currentWeight = 0;

            foreach ($this as $item) {
                $currentWeight += $item->$weightField ?? 0;
                if ($random <= $currentWeight) {
                    return $item;
                }
            }

            return $this->last();
        });
    }
}