<?php

namespace App\Livewire;

use App\Models\Article;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ArticleOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total',
                Article::all()->count()
            ),
            Stat::make(
                'Today',
                Article::whereDate('created_at', date('Y-m-d'))->get()->count()
            ),
            Stat::make(
                'Last 7 Days',
                Article::where('created_at', '>=', now()->subDays(7)->startOfDay())->get()->count()
            ),
            Stat::make(
                'Last 30 Days',
                Article::where('created_at', '>=', now()->subDays(30)->startOfDay())->get()->count()
            )
        ];

    }
}
