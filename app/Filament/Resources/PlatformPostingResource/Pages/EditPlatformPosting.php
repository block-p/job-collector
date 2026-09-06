<?php

namespace App\Filament\Resources\PlatformPostingResource\Pages;

use App\Filament\Resources\PlatformPostingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlatformPosting extends EditRecord
{
    protected static string $resource = PlatformPostingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
