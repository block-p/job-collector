<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JobPostingResource\Pages;
use App\Filament\Resources\JobPostingResource\RelationManagers;
use App\Models\JobPosting;
use Filament\Forms;

use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Form;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class JobPostingResource extends Resource
{
    protected static ?string $model = JobPosting::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Job Postings';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Title')
                    ->required(),
                Select::make('platform_id')
                    ->label('Platform')
                    ->relationship('platform', 'title')
                    ->required(),
                TextInput::make('company')
                    ->label('Company')
                    ->required(),
                TextInput::make('url')
                    ->label('Source URL')
                    ->url()
                    ->required()
                    ->suffixAction(
                        \Filament\Forms\Components\Actions\Action::make('open_url')
                            ->icon('heroicon-o-globe-alt')
                            ->tooltip('Open in Browser')
                            ->url(fn ($state) => $state, shouldOpenInNewTab: true)
                    ),
                TextInput::make('salary')
                    ->label('Salary'),
                TextInput::make('location')
                    ->label('Location'),
                TextInput::make('contract')
                    ->label('Contract'),
                TagsInput::make('skills')
                    ->label('Skills')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->weight('bold')
                    ->limit(50),
                TextColumn::make('url')
                    ->label('Web')
                    ->icon('heroicon-o-globe-alt')
                    ->iconColor('primary')
                    ->formatStateUsing(fn () => 'View')
                    ->url(fn ($record) => $record->url, shouldOpenInNewTab: true),
                TextColumn::make('platform.title')
                    ->label('Platform')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('company')
                    ->label('Company')
                    ->searchable(),
                TextColumn::make('salary')
                    ->label('Salary')
                    ->badge(),
                TextColumn::make('skills')
                    ->label('Skills')
                    ->badge()
                    ->color('success')
                    ->separator(',')
                    ->limitList(3),
                TextColumn::make('contract')
                    ->label('Contract')
                    ->badge()
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Location')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('platform')
                    ->label('Platform')
                    ->relationship('platform', 'title'),
                Tables\Filters\SelectFilter::make('contract')
                    ->label('Contract')
                    ->options(fn () => JobPosting::query()->whereNotNull('contract')->where('contract', '!=', '')->distinct()->pluck('contract', 'contract')->toArray()),
                Tables\Filters\Filter::make('company')
                    ->form([
                        TextInput::make('company')->placeholder('Search company...'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['company'] ?? null,
                            fn (Builder $query, $company): Builder => $query->where('company', 'like', "%{$company}%"),
                        );
                    }),
            ], layout: FiltersLayout::Modal)
            ->filtersFormColumns(2)
            ->filtersFormSchema(fn (array $filters): array => [
                Section::make('Visibility')
                    ->description('These filters affect the visibility of the records in the table.')
                    ->schema([
                        $filters['platform'],
                        $filters['contract'],
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
                $filters['company'],
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('delete_all')
                    ->label('Delete All Records')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete All Job Postings?')
                    ->modalDescription('Are you sure you want to permanently delete all crawled jobs? This operation is instant.')
                    ->action(function () {
                        JobPosting::query()->delete();
                        \Filament\Notifications\Notification::make()
                            ->title('All jobs deleted successfully.')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('fast_delete')
                        ->label('Delete Selected')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $records->toQuery()->delete();
                            \Filament\Notifications\Notification::make()
                                ->title('Selected jobs deleted.')
                                ->danger()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListJobPostings::route('/'),
            'create' => Pages\CreateJobPosting::route('/create'),
            'edit' => Pages\EditJobPosting::route('/{record}/edit'),
        ];
    }
}
