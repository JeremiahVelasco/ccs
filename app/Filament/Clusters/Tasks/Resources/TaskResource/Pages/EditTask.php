<?php

namespace App\Filament\Clusters\Tasks\Resources\TaskResource\Pages;

use App\Filament\Clusters\Tasks\Resources\TaskResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTask extends EditRecord
{
    protected static string $resource = TaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
