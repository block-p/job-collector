<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskLogResource\Pages;
use App\Models\CrawlLog;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class TaskLogResource extends Resource
{
    protected static ?string $model = CrawlLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Task Logs';
    protected static ?string $modelLabel = 'Task Log';
    protected static ?string $pluralModelLabel = 'Task Logs';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('3s')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('platform_title')
                    ->label('Source')
                    ->weight('bold')
                    ->searchable(),

                TextColumn::make('filters')
                    ->label('Filters / Tags')
                    ->badge()
                    ->color('primary')
                    ->state(function ($record) {
                        $filters = $record->filters ?? [];
                        if (is_string($filters)) {
                            $filters = json_decode($filters, true) ?? [];
                        }

                        $flatten = function ($val) use (&$flatten): array {
                            $result = [];
                            if (is_string($val) && (str_starts_with(trim($val), '[') || str_starts_with(trim($val), '{'))) {
                                $decoded = json_decode($val, true);
                                if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                                    $val = $decoded;
                                }
                            }

                            if (is_array($val)) {
                                foreach ($val as $k => $v) {
                                    if (is_array($v)) {
                                        $result = array_merge($result, $flatten($v));
                                    } elseif (!is_bool($v) && $v !== null && $v !== '') {
                                        $result[] = is_string($k) && !is_numeric($k) ? "{$k}: {$v}" : "{$v}";
                                    }
                                }
                            } elseif (!is_bool($val) && $val !== null && $val !== '') {
                                $result[] = "{$val}";
                            }
                            return $result;
                        };

                        $tags = [];
                        foreach ($filters as $key => $value) {
                            $tags = array_merge($tags, $flatten($value));
                        }

                        return !empty($tags) ? array_values(array_unique($tags)) : ['All'];
                    }),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed'  => 'danger',
                        'running' => 'warning',
                        default   => 'gray',
                    }),

                TextColumn::make('jobs_count')
                    ->label('Jobs Saved')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => "{$state} jobs"),

                TextColumn::make('error')
                    ->label('Error')
                    ->limit(40)
                    ->color('danger')
                    ->tooltip(fn ($record) => $record->error)
                    ->placeholder('None'),
            ])
            ->emptyStateHeading('No Task Logs Yet')
            ->emptyStateDescription('When tasks finish, their results will be archived here.')
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('clear_logs')
                    ->label('Clear Logs')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        CrawlLog::truncate();
                        Notification::make()->title('Logs cleared')->danger()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTaskLogs::route('/'),
        ];
    }
}
