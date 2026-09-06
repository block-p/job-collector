<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RunningTaskResource\Pages;
use App\Models\JobQueue;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Filament\Notifications\Notification;

class RunningTaskResource extends Resource
{
    protected static ?string $model = JobQueue::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';
    protected static ?string $navigationLabel = 'Running Tasks';
    protected static ?string $modelLabel = 'Active Task';
    protected static ?string $pluralModelLabel = 'Running Tasks';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('2s')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('task_name')
                    ->label('Task Name')
                    ->state(function ($record) {
                        $payload = json_decode($record->payload, true);
                        return class_basename($payload['displayName'] ?? 'Unknown Task');
                    })
                    ->weight('bold'),

                TextColumn::make('filters')
                    ->label('Filters / Tags')
                    ->badge()
                    ->color('primary')
                    ->state(function ($record) {
                        $payload = json_decode($record->payload, true);
                        $command = @unserialize($payload['data']['command'] ?? '');
                        $options = array_merge(
                            $command->source->body_template ?? [],
                            $command->source->query_params ?? [],
                            $command->options['body'] ?? [],
                            $command->options['query_params'] ?? []
                        );

                        $flatten = function ($val) use (&$flatten): array {
                            if (is_string($val) && (str_starts_with(trim($val), '[') || str_starts_with(trim($val), '{'))) {
                                $decoded = json_decode($val, true);
                                if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
                                    $val = $decoded;
                                }
                            }

                            $result = [];
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
                        foreach ($options as $key => $value) {
                            $tags = array_merge($tags, $flatten($value));
                        }

                        return !empty($tags) ? array_values(array_unique($tags)) : ['Default'];
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->state(fn ($record) => $record->reserved_at ? 'Running' : 'Queued')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Running' => 'warning',
                        'Queued'  => 'info',
                        default   => 'gray',
                    }),

                TextColumn::make('queue')
                    ->label('Queue')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('attempts')
                    ->label('Attempts')
                    ->badge(),
            ])
            ->emptyStateHeading('No Tasks Currently Running')
            ->emptyStateDescription('The queue is idle. When a crawl job starts, it will appear here.')
            ->actions([
                Tables\Actions\DeleteAction::make()->label('Cancel'),
            ])
            ->headerActions([
                Action::make('clear_queue')
                    ->label('Cancel All Tasks')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function () {
                        Artisan::call('queue:clear');
                        Notification::make()->title('All queued tasks cancelled')->danger()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRunningTasks::route('/'),
        ];
    }
}
