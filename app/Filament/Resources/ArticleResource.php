<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArticleResource\Pages;
use App\Filament\Resources\ArticleResource\RelationManagers;
use App\Models\Article;
use App\Models\Tag;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

class ArticleResource extends Resource
{
    protected static ?string $model = Article::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Content')
                    ->description('The content of the article')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->live(false,300)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->helperText('Slug will auto generate when typing title')
                            ->required()
                            ->unique('articles','slug')
                            ->maxLength(255),
                        Forms\Components\MarkdownEditor::make('body')
                            ->required(),
                    ])
                    ->columnSpan(8),
                Forms\Components\Section::make('Properties')
                    ->description('Properties of article')
                    ->icon('heroicon-m-adjustments-horizontal')
                    ->schema([
                        Forms\Components\FileUpload::make('feature_image_url'),
                        Forms\Components\Select::make('category')
                            ->preload()
                            ->searchable()
                            ->relationship('category', 'name'),
                        Forms\Components\Select::make('tags')
                            ->preload()
                            ->searchable()
                            ->multiple()
                            ->relationship('tags', 'name'),
                        Forms\Components\Select::make('status')
                            ->searchable()
                            ->options([
                                'draft' => 'Draft',
                                'preview' => 'Preview',
                                'publish' => 'Publish',
                                'private' => 'Private'
                            ]),
                        Forms\Components\Select::make('user_id')->label("Author")
                            ->preload()
                            ->searchable()
                            ->relationship('author', 'name'),
                        Forms\Components\DateTimePicker::make('published_at'),
                        Forms\Components\DateTimePicker::make('scheduled_for'),
                    ])->columnSpan(4),
                Forms\Components\Section::make('Meta SEO')
                    ->description('Fill the information for SEO')
                    ->icon('heroicon-m-rss')
                    ->schema([
                        Forms\Components\Textarea::make('meta_description')
                            ->maxLength(255),
                    ])->columnSpan(12)
            ])->columns(12);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('published_at')
                    ->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('scheduled_for')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('status')->badge()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'preview' => 'Preview',
                        'publish' => 'Publish',
                        'private' => 'Private'
                    ]),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable(),
                Tables\Filters\SelectFilter::make('tags')
                    ->relationship('tags', 'name')
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListArticles::route('/'),
            'create' => Pages\CreateArticle::route('/create'),
            'edit' => Pages\EditArticle::route('/{record}/edit'),
        ];
    }
}
