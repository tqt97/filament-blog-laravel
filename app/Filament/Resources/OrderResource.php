<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Shop';

    public static function getNavigationBadge(): ?string
    {
        // return Order::whereDate('created_at', today())->count();
        return Order::whereDate('created_at', today())->count() ? 'NEW' : '';
    }

    protected static array $statuses = [
        'in stock' => 'in stock',
        'sold out' => 'sold out',
        'coming soon' => 'coming soon',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Main data')
                        ->schema([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->unique(ignoreRecord: true),
                            Forms\Components\TextInput::make('price')
                                ->required(),
                        ]),
                    Forms\Components\Wizard\Step::make('Additional data')
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
                Tables\Columns\TextColumn::make('user.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money('usd')
                    ->getStateUsing(function (Order $record): float {
                        return $record->price / 100;
                    })
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->formatStateUsing(fn ($state) => '$' . number_format($state / 100, 2))),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            // ->defaultSort('created_at', 'desc')
            ->defaultGroup('product.name')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('Mark Completed')
                        ->requiresConfirmation()
                        ->icon('heroicon-o-check-badge')
                        ->hidden(fn (Order $record) => $record->is_completed)
                        ->action(fn (Order $record) => $record->update(['is_completed' => true])),
                    Tables\Actions\Action::make('Change is completed')
                        ->icon('heroicon-o-check-badge')
                        ->fillForm(function (Order $order) {
                            return ['is_completed' => $order->is_completed];
                        })
                        ->form([
                            Forms\Components\Checkbox::make('is_completed'),
                        ])
                        ->action(function (Order $order, array $data): void {
                            $order->update(['is_completed' => $data['is_completed']]);
                        }),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('Mark as Completed')
                        ->icon('heroicon-o-check-badge')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['is_completed' => true]))
                        ->deselectRecordsAfterCompletion()
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('New Order')
                    ->url(fn (): string => OrderResource::getUrl('create')),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
