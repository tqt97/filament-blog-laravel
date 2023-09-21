<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestArticles extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 4;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ArticleResource::getEloquentQuery()
            )
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->sortable()->searchable(),
                Tables\Columns\TextColumn::make(('title'))->label(__('Article Title'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('author.name')->label(__('Author'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('feature_image_url'),
            ]);
    }
}
