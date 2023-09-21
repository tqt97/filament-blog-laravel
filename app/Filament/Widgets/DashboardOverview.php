<?php

namespace App\Filament\Widgets;

use App\Models\Article;
use App\Models\Category;
use App\Models\Tag;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make(
                'Total Articles',
                Article::all()->count()
            )
                ->description('Articles')
                ->descriptionIcon('heroicon-m-document-text')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make(
                'Total Categories',
                Category::all()->count()
            )
                ->description('Categories')
                ->descriptionIcon('heroicon-m-square-3-stack-3d')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),
            Stat::make(
                'Total Tag',
                Tag::all()->count()
            )
                ->description('Tag')
                ->descriptionIcon('heroicon-m-tag')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
        ];
    }
}
