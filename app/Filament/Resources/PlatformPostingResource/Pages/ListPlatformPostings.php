<?php

namespace App\Filament\Resources\PlatformPostingResource\Pages;

use App\Filament\Resources\PlatformPostingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlatformPostings extends ListRecords
{
    protected static string $resource = PlatformPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
