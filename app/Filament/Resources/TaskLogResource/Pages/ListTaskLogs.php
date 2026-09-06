<?php

namespace App\Filament\Resources\TaskLogResource\Pages;

use App\Filament\Resources\TaskLogResource;
use Filament\Resources\Pages\ListRecords;

class ListTaskLogs extends ListRecords
{
    protected static string $resource = TaskLogResource::class;
}
