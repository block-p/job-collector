<?php

namespace App\Filament\Resources\JobPostingResource\Pages;

use App\Filament\Resources\JobPostingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListJobPostings extends ListRecords
{
    protected static string $resource = JobPostingResource::class;

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
