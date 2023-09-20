<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    // public static function getGlobalSearchResultUrl(Product $record): string
    // {
    //     return self::getUrl('view', ['record' => $record]);
    // }
    protected static ?string $recordTitleAttribute = 'name';


    public static function getNavigationLabel(): string
    {
        return __('Products');
    }

    protected static int $globalSearchResultsLimit = 3;

    protected static ?string $modelLabel = 'Products';

    public static function getPluralModelLabel(): string
    {
        return __('Products');
    }

    protected static ?int $navigationSort = 1;

    protected static array $statuses = [
        'in stock' => 'in stock',
        'sold out' => 'sold out',
        'coming soon' => 'coming soon',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Main data')
                    ->description('What users totally need to fill in')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->rule('numeric')
                            ->prefix('$'),
                        Forms\Components\Select::make('status')
                            ->options(self::$statuses),
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name'),
                        Forms\Components\Select::make('tags')
                            ->live(debounce: 300)
                            ->preload()
                            ->relationship('tags', 'name')
                            ->multiple(),
                    ])->columns(1),
                Forms\Components\Tabs::make()->tabs([
                    Forms\Components\Tabs\Tab::make('Main data')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->unique(ignoreRecord: true),
                            Forms\Components\TextInput::make('price')
                                ->required(),
                        ]),
                    Forms\Components\Tabs\Tab::make('Additional data')
                        ->schema([
                            Forms\Components\Radio::make('status')
                                ->options(self::$statuses),
                            Forms\Components\Select::make('category_id')
                                ->relationship('category', 'name'),
                        ]),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextInputColumn::make('name')->sortable()
                    ->searchable()->rules(['required', 'min:3']),
                Tables\Columns\TextColumn::make('price')->sortable()
                    ->money('usd')
                    ->getStateUsing(function (Product $record): float {
                        return $record->price / 100;
                    })->alignEnd()
                    ->sortable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->onColor('success') // default value: "primary"
                    ->offColor('danger'), // default value: "gray",
                Tables\Columns\SelectColumn::make('status')->sortable()
                    ->searchable()->options(self::$statuses),
                // ->badge()
                // ->color(fn (string $state): string => match ($state) {
                //     'in stock' => 'primary',
                //     'sold out' => 'danger',
                //     'coming soon' => 'info',
                // }),
                Tables\Columns\TextColumn::make('category.name')->label('Category name')
                    ->badge()
                    ->url(function (Product $product): string {
                        return CategoryResource::getUrl('edit', [
                            'record' => $product->category_id
                        ]);
                    }),

                Tables\Columns\TextColumn::make('tags.name')->badge()->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('m/d/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_stock')
                    ->query(fn (Builder $query): Builder => $query->where('status', '=', 'in stock')),
                Tables\Filters\SelectFilter::make('status')
                    ->options(self::$statuses),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('created_from'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),
                Tables\Filters\Filter::make('created_until')
                    ->form([
                        Forms\Components\DatePicker::make('created_until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ], Tables\Enums\FiltersLayout::AboveContent)->filtersFormColumns(4)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\TagsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'price'];
    }
}
