<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlatformPostingResource\Pages;
use App\Filament\Resources\PlatformPostingResource\RelationManagers;
use App\Models\PlatformPosting;
use App\Jobs\FetchJobsFromApi;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TagsInput;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;

class PlatformPostingResource extends Resource
{
    protected static ?string $model = PlatformPosting::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';
    protected static ?string $navigationLabel = 'Sources';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Configuration')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->placeholder('e.g. E-Estekhdam'),
                        Select::make('method')
                            ->options([
                                'GET'  => 'GET',
                                'POST' => 'POST',
                            ])
                            ->default('POST')
                            ->required(),
                        TextInput::make('url')
                            ->url()
                            ->required()
                            ->placeholder('https://www.e-estekhdam.com')
                            ->suffixAction(
                                \Filament\Forms\Components\Actions\Action::make('open_url')
                                    ->icon('heroicon-o-globe-alt')
                                    ->tooltip('Open Website')
                                    ->url(fn ($state) => $state, shouldOpenInNewTab: true)
                            ),
                        TextInput::make('endpoint')
                            ->required()
                            ->placeholder('/search-api/search'),
                        TextInput::make('delay_ms')
                            ->numeric()
                            ->default(1000)
                            ->suffix('ms'),
                    ]),

                Section::make('Payload & Headers')
                    ->columns(2)
                    ->schema([
                        KeyValue::make('headers')
                            ->default([
                                'Content-Type' => 'application/json',
                                'User-Agent'   => 'Mozilla/5.0',
                            ]),
                        KeyValue::make('query_params'),
                        KeyValue::make('body_template')
                            ->columnSpanFull(),
                    ]),

                    Section::make('Engine Rules')
                        ->columns(2)
                        ->schema([
                            KeyValue::make('pagination')
                                ->default([
                                    'type'       => 'query',
                                    'page_key'   => 'page',
                                    'start_page' => '1',
                                    'max_pages'  => '1',
                                ]),
                            Group::make([
                                TextInput::make('list_path')
                                    ->label('List Path')
                                    ->default('data')
                                    ->required(),
                                KeyValue::make('fields')
                                    ->label('Fields Mapping')
                                    ->default([
                                        'title'    => 'title',
                                        'company'  => 'brand_name',
                                        'location' => 'location',
                                        'salary'   => 'salary',
                                        'url'      => 'url',
                                        'contract' => 'contract.0',
                                        'skills'   => 'skills.*.title',
                                    ]),
                            ])
                            ->statePath('response_mapping'),
                        ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('url')
                    ->label('Web')
                    ->icon('heroicon-o-globe-alt')
                    ->iconColor('primary')
                    ->formatStateUsing(fn () => 'Visit')
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('method')->badge(),
                Tables\Columns\TextColumn::make('last_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('last_crawled_at')->dateTime()->since(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('run_crawler')
                    ->label('Run')
                    ->icon('heroicon-o-play')
                    ->color('success')
                    ->form(function (PlatformPosting $record) {
                        $components = [];

                        // 1. Query Params (for GET or query-based filtering)
                        if (!empty($record->query_params)) {
                            foreach ($record->query_params as $key => $value) {
                                $label = 'Query: ' . ucfirst(str_replace(['_', '[', ']'], [' ', ' ', ''], $key));
                                $isArray = is_array($value) || (is_string($value) && str_starts_with(trim($value), '['));
                                $defaultVal = is_string($value) && str_starts_with(trim($value), '[') 
                                    ? (json_decode($value, true) ?? [$value]) 
                                    : $value;

                                if ($isArray) {
                                    $components[] = TagsInput::make("query_params.{$key}")
                                        ->label($label)
                                        ->default((array) $defaultVal);
                                } elseif (is_bool($value)) {
                                    $components[] = Toggle::make("query_params.{$key}")
                                        ->label($label)
                                        ->default($value);
                                } elseif (is_numeric($value)) {
                                    $components[] = TextInput::make("query_params.{$key}")
                                        ->numeric()
                                        ->label($label)
                                        ->default($value);
                                } else {
                                    $components[] = TextInput::make("query_params.{$key}")
                                        ->label($label)
                                        ->default($value);
                                }
                            }
                        }

                        // 2. Body Template (for POST / Payload filtering)
                        if (!empty($record->body_template)) {
                            foreach ($record->body_template as $key => $value) {
                                $label = ucfirst(str_replace('_', ' ', $key));
                                $isArray = is_array($value) || (is_string($value) && str_starts_with(trim($value), '['));
                                $defaultVal = is_string($value) && str_starts_with(trim($value), '[') 
                                    ? (json_decode($value, true) ?? [$value]) 
                                    : $value;

                                if ($isArray) {
                                    $components[] = TagsInput::make("body.{$key}")
                                        ->label($label)
                                        ->default((array) $defaultVal);
                                } elseif (is_bool($value)) {
                                    $components[] = Toggle::make("body.{$key}")
                                        ->label($label)
                                        ->default($value);
                                } elseif (is_numeric($value)) {
                                    $components[] = TextInput::make("body.{$key}")
                                        ->numeric()
                                        ->label($label)
                                        ->default($value);
                                } else {
                                    $components[] = TextInput::make("body.{$key}")
                                        ->label($label)
                                        ->default($value);
                                }
                            }
                        }

                        $components[] = TextInput::make('max_pages')->numeric()->default(1)->required();
                        return $components;
                    })
                    ->action(function (PlatformPosting $record, array $data) {
                        FetchJobsFromApi::dispatch($record, [
                            'body'         => $data['body'] ?? [],
                            'query_params' => $data['query_params'] ?? [],
                            'max_pages'    => (int) ($data['max_pages'] ?? 1),
                        ]);
                        Notification::make()->title('Crawler started in background')->success()->send();
                    }),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPlatformPostings::route('/'),
            'create' => Pages\CreatePlatformPosting::route('/create'),
            'edit' => Pages\EditPlatformPosting::route('/{record}/edit'),
        ];
    }
}
